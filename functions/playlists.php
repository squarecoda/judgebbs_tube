<?php
	function single_playlist_content($content) {
		global $post;

		if(!empty($post->post_type) && $post->post_type == 'bbs-playlist') {
			return '<!-- wp:scfe/playlist-detail-layout /-->';
		}

		return $content;
	}
	add_filter('the_content', 'single_playlist_content', 5);

	function get_assigned_playlists($judge_id = '') {
		if(empty($judge_id)) $judge_id = get_current_judge_id();

		$category = get_post_meta($judge_id, 'judge_category', true);
		$status = get_post_meta($judge_id, 'status', true);

		$individual_assignemnts = get_posts([
			'post_type' => 'bbs-playlist',
			'posts_per_page' => -1,
			'meta_query' => [
				[
					'key' => 'assigned_judges',
					'value' => '"' . $judge_id . '"',
					'compare' => 'LIKE',
				],
			],
			'fields' => 'ids',
		]);

		$group_assignemnts = get_posts([
			'post_type' => 'bbs-playlist',
			'posts_per_page' => -1,
			'meta_query' => [
				[
					'key' => 'assigned_categories',
					'value' => '"' . $category . '"',
					'compare' => 'LIKE',
				],
				[
					'key' => 'assigned_statuses',
					'value' => '"' . $status . '"',
					'compare' => 'LIKE',
				],
			],
			'fields' => 'ids',
		]);

		$all_assignments = array_merge($individual_assignemnts, $group_assignemnts);

		foreach($all_assignments as $index => $playlist_id) {
			$all_assignments[$index] = get_playlist_details($playlist_id, $judge_id);
		}

		return $all_assignments;
	}

	function get_playlist_details($playlist_id, $judge_id) {
		$transient_name = sprintf('playlist_details__%s__%s', $playlist_id, $judge_id);
		$transient = get_transient($transient_name);
		if(!empty($transient) && empty($_GET['refresh_transient'])) {
			return json_decode($transient, true);
		}

		$videos_obj = new SquareCoda\Theme\Videos(false);

		$videos = get_post_meta($playlist_id, 'videos', true);
		if(!empty($videos)) $videos = $videos_obj->decode_json($videos);

		$score_number = 0;
		if(!empty($videos)) {
			foreach($videos as $video) {
				$video_array = get_video_array($videos_obj->get_int_from_typeahead_field($video['video']), $judge_id);
				if(!empty($video_array['score'])) $score_number++;
			}
		}

		$details = [
			'id' => $playlist_id,
			'judge_id' => $judge_id,
			'score_number' => $score_number,
			'video_number' => !empty($videos) ? count($videos) : 0,
		];

		set_transient($transient_name, json_encode($details), MINUTE_IN_SECONDS * 60);

		return $details;
	}

	function get_video_count($playlist_id) {
		$videos_obj = new SquareCoda\Theme\Videos(false);
		$videos = get_post_meta($playlist_id, 'videos', true);
		if(!empty($videos)) $videos = $videos_obj->decode_json($videos);
		return !empty($videos) ? count($videos) : 0;
	}

	function get_playlists($type = 'personal', $judge_id = '', $limit = -1) {
		if(empty($judge_id)) $judge_id = get_current_judge_id();

		$meta_query = [];
		if($type == 'personal') {
			$meta_query[] = [
				'key' => 'judge',
				'value' => $judge_id,
			];
			$results = get_posts([
				'post_type' => 'bbs-playlist',
				'posts_per_page' => $limit,
				'meta_query' => $meta_query,
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
				'meta_key' => 'playlist_sort_order',
				'fields' => 'ids',
			]);
		} elseif($type == 'public') {
			$meta_query[] = [
				'key' => 'public',
				'value' => 'on',
			];
			$results = get_posts([
				'post_type' => 'bbs-playlist',
				'posts_per_page' => -1,
				'meta_query' => $meta_query,
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
				'meta_key' => 'playlist_sort_order',
				'fields' => 'ids',
			]);
		} elseif($type == 'assigned') {
			$category = get_post_meta($judge_id, 'judge_category', true);
			$status = get_post_meta($judge_id, 'status', true);

			$individual_assignemnts = get_posts([
				'post_type' => 'bbs-playlist',
				'posts_per_page' => -1,
				'meta_query' => [
					[
						'key' => 'assigned_judges',
						'value' => '"' . $judge_id . '"',
						'compare' => 'LIKE',
					],
				],
				'fields' => 'ids',
			]);

			$group_assignemnts = get_posts([
				'post_type' => 'bbs-playlist',
				'posts_per_page' => -1,
				'meta_query' => [
					[
						'key' => 'assigned_categories',
						'value' => '"' . $category . '"',
						'compare' => 'LIKE',
					],
					[
						'key' => 'assigned_statuses',
						'value' => '"' . $status . '"',
						'compare' => 'LIKE',
					],
				],
				'fields' => 'ids',
			]);

			$results = array_merge($individual_assignemnts, $group_assignemnts);
		}

		if(in_array($type, ['assigned'])) {
			$i = 0;
			foreach($results as $index => $result_id) {
				if($i < 10) {
					$results[$index] = get_playlist_details($result_id, $judge_id);
				} else {
					$results[$index] = [
						'id' => $result_id
					];
				}
				$i++;
			}
		} else {
			foreach($results as $index => $result_id) {
				$results[$index] = [
					'id' => $result_id
				];
			}			
		}

		return $results;
	}

	function get_playlist_creator($playlist_id) {
		$creator = get_post_meta($playlist_id, 'judge', true);
		return $creator ? get_the_title($creator) : '';
	} 

	function get_playlist_due_date($playlist_id) {
		$assigned = get_post_meta($playlist_id, 'scoring', true);
		$due_date = get_post_meta($playlist_id, 'assigned_due_date', true);
		return !empty($assigned) && !empty($due_date) ? date('n/j/Y', strtotime($due_date)) : '';
	} 

	function bbs_load_personal_playlists() {
		$playlists = get_playlists('personal', $_POST['judge_id']);
		$judge_id = $_POST['judge_id'];

		$html = Timber::compile('modal-playlists.twig', compact('playlists', 'judge_id'));

		$postfields = $_POST;
		echo json_encode(compact('html', 'playlists', 'postfields'));
		exit;
	}
	$ajax_action = 'bbs_load_personal_playlists';
	add_action(sprintf('wp_ajax_%s', $ajax_action), $ajax_action);

	function bbs_update_playlist_videos() {
		$playlists_obj = new SquareCoda\Theme\Playlists(false);

		$postfields = $_POST;
		if(!empty($_POST['playlist_id']) && !empty($_POST['video_id']) && !empty($_POST['direction'])) {
			$playlist_videos = $playlists_obj->decode_json($playlists_obj->get_field('videos', $_POST['playlist_id']));
			if($_POST['direction'] == 'add') {
				$playlist_videos[] = ['video' => intval($_POST['video_id'])];
			} elseif($_POST['direction'] == 'remove') {
				foreach($playlist_videos as $index => $playlist_video) {
					if(!empty($playlist_video['video']) && $_POST['video_id'] == $playlist_video['video']) unset($playlist_videos[$index]);
				}
				$playlist_videos = array_values($playlist_videos);
			}

			update_post_meta($_POST['playlist_id'], 'videos', $playlists_obj->encode_json($playlist_videos));

			echo json_encode(compact('playlist_videos', 'postfields'));
		} else {
			echo json_encode(compact('postfields'));
		}

		exit;
	}
	$ajax_action = 'bbs_update_playlist_videos';
	add_action(sprintf('wp_ajax_%s', $ajax_action), $ajax_action);

	function bbs_create_playlist() {
		$postfields = $_POST;

		if(!empty($_POST['judge_id']) && !empty($_POST['title'])) {
			$playlist_id = wp_insert_post([
				'post_type' => 'bbs-playlist',
				'post_title' => $_POST['title'],
				'post_status' => 'publish',
				'meta_input' => [
					'judge' => $_POST['judge_id'],
					'public' => $_POST['public'],
				],
			]);

			update_post_meta($playlist_id, 'playlist_sort_order', get_post_time('U', true, $playlist_id));
			echo json_encode(compact('playlist_id', 'postfields'));
			exit;
		}

		echo json_encode(compact('postfields'));

		exit;
	}
	$ajax_action = 'bbs_create_playlist';
	add_action(sprintf('wp_ajax_%s', $ajax_action), $ajax_action);

	// function bbs_delete_playlist() {
	// 	$postfields = $_POST;

	// 	if(!empty($_POST['playlist_id'])) {
	// 		// $playlist_id = wp_insert_post([
	// 		// 	'post_type' => 'bbs-playlist',
	// 		// 	'post_title' => $_POST['title'],
	// 		// 	'post_status' => 'publish',
	// 		// 	'meta_input' => [
	// 		// 		'judge' => $_POST['judge_id'],
	// 		// 		'public' => $_POST['public'],
	// 		// 	],
	// 		// ]);

	// 		// update_post_meta($playlist_id, 'playlist_sort_order', get_post_time('U', true, $playlist_id));
	// 		// echo json_encode(compact('playlist_id', 'postfields'));
	// 		exit;
	// 	}

	// 	echo json_encode(compact('postfields'));

	// 	exit;
	// }
	// $ajax_action = 'bbs_delete_playlist';
	// add_action(sprintf('wp_ajax_%s', $ajax_action), $ajax_action);

	function bbs_update_playlist_privacy() {
		$postfields = $_POST;

		if(!empty($_POST['playlist_id']) && !empty($_POST['direction'])) {
			$value = $_POST['direction'] == 'public' ? 'on' : '';
			update_post_meta($_POST['playlist_id'], 'public', $value);
		}

		echo json_encode(compact('postfields'));
		exit;
	}
	$ajax_action = 'bbs_update_playlist_privacy';
	add_action(sprintf('wp_ajax_%s', $ajax_action), $ajax_action);

<?php
	function script_update_individual_playlist_typeaheads($post_id) {
		$playlist_obj = new SquareCoda\Theme\Playlists(false);

		//-----Judge ID-----
		$legacy_judge_id = get_post_meta($post_id, 'legacy_judge_id', true);

		$judge_lookup = get_posts([
			'post_type' => 'bbs-judge',
			'posts_per_page' => 1,
			'meta_query' => [
				[
					'key' => 'legacy_id',
					'value' => $legacy_judge_id,
				],
			],
			'fields' => 'ids',
		]);

		if(!empty($judge_lookup)) {
			update_post_meta($post_id, 'judge', current($judge_lookup));
		}


		//-----Video IDs-----
		$legacy_video_ids = get_post_meta($post_id, 'legacy_video_ids', true);
		$legacy_video_ids = $playlist_obj->decode_json($legacy_video_ids);

		if(!empty($legacy_video_ids)) {
			$videos = [];
			foreach($legacy_video_ids as $legacy_video_id) {
				$video_lookup = get_posts([
					'post_type' => 'bbs-video',
					'posts_per_page' => 1,
					'meta_query' => [
						[
							'key' => 'legacy_id',
							'value' => $legacy_video_id['id'],
						],
					],
					'fields' => 'ids',
				]);
				if(!empty($video_lookup)) {
					$videos[]['video'] = current($video_lookup);
				}
			}
			if(!empty($videos)) {
				update_post_meta($post_id, 'videos', $playlist_obj->encode_json($videos));
			}
		}


		//-----Migrated-----
		update_post_meta($post_id, 'migrated', 'yes');
	}

	function script_update_individual_score_typeaheads($post_id) {
		$scores_obj = new SquareCoda\Theme\Scores(false);

		//-----Legacy ID-----
		$legacy_id = get_the_title($post_id);
		update_post_meta($post_id, 'legacy_id', $legacy_id);

		//-----Judge ID-----
		$legacy_judge_id = get_post_meta($post_id, 'legacy_judge_id', true);

		if(!empty($legacy_judge_id)) {
			$judge_lookup = get_posts([
				'post_type' => 'bbs-judge',
				'posts_per_page' => 1,
				'meta_query' => [
					[
						'key' => 'legacy_id',
						'value' => $legacy_judge_id,
					],
				],
				'fields' => 'ids',
			]);

			if(!empty($judge_lookup)) {
				update_post_meta($post_id, 'judge', current($judge_lookup));
			}
		}


		//-----Video ID-----
		$legacy_video_id = get_post_meta($post_id, 'legacy_video_id', true);

		if(!empty($legacy_video_id)) {
			$video_lookup = get_posts([
				'post_type' => 'bbs-video',
				'posts_per_page' => 1,
				'meta_query' => [
					[
						'key' => 'legacy_id',
						'value' => $legacy_video_id,
					],
				],
				'fields' => 'ids',
			]);


			if(!empty($video_lookup)) {
				update_post_meta($post_id, 'video', current($video_lookup));
			}
		}


		//-----Playlist ID-----
		$playlist_name = get_post_meta($post_id, 'playlist_name', true);

		if(!empty($playlist_name)) {
			$playlist_lookup = get_posts([
				'post_type' => 'bbs-playlist',
				'posts_per_page' => 1,
				'title' => $playlist_name,
				'fields' => 'ids',
			]);

			if(!empty($playlist_lookup)) {
				update_post_meta($post_id, 'playlist', current($playlist_lookup));
			}
		}


		//-----Migrated-----
		update_post_meta($post_id, 'migrated', 'yes');


		//-----Update Title-----
		$scores_obj->update_title_when_data_changed($post_id);
	}




	function script_update_playlist_typeaheads() {
		if(current_user_can('administrator') && !empty($_GET[__FUNCTION__]) && $_GET[__FUNCTION__] == 'true') {
			$query = new WP_Query([
				'post_type' => 'bbs-playlist',
				'posts_per_page' => !empty($_GET['posts_per_page']) ? $_GET['posts_per_page'] : 250,
				'meta_query' => [
					[
						'key' => 'legacy_judge_id',
						'compare' => 'EXISTS',
					],
					[
						'key' => 'legacy_judge_id',
						'value' => '',
						'compare' => '!=',
					],
					[
						'key' => 'migrated',
						'compare' => 'NOT EXISTS',
					],
				],
				'fields' => 'ids',
			]);

			$results = $query->posts;

			display_result($query->found_posts);
			display_result(count($results));

			foreach($results as $post_id) {
				script_update_individual_playlist_typeaheads($post_id);
			}
			display_result($results);
			wp_die();
		}
	}
	add_action('admin_init', 'script_update_playlist_typeaheads');

	function script_update_score_typeaheads() {
		if(current_user_can('administrator') && !empty($_GET[__FUNCTION__]) && $_GET[__FUNCTION__] == 'true') {
			$query = new WP_Query([
				'post_type' => 'bbs-score',
				// 'posts_per_page' => 8000,
				'posts_per_page' => !empty($_GET['posts_per_page']) ? $_GET['posts_per_page'] : 5000,
				'meta_query' => [
					[
						'key' => 'legacy_video_id',
						'compare' => 'EXISTS',
					],
					[
						'key' => 'legacy_video_id',
						'value' => '',
						'compare' => '!=',
					],
					[
						'key' => 'migrated',
						'compare' => 'NOT EXISTS',
					],
				],
				'fields' => 'ids',
			]);

			$results = $query->posts;

			display_result($query->found_posts);
			display_result(count($results));

			foreach($results as $post_id) {
				script_update_individual_score_typeaheads($post_id);
			}
			display_result($results);
			wp_die();
		}
	}	
	add_action('admin_init', 'script_update_score_typeaheads');
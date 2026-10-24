<?php
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
		foreach($videos as $video) {
			$video_array = get_video_array($videos_obj->get_int_from_typeahead_field($video['video']), $judge_id);
			if(!empty($video_array['score'])) $score_number++;
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
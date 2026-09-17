<?php
	function get_reference_scores($post_id) {
		$categories = [
			'mus',
			'per',
			'sng',
		];

		$scores_array = [];
		foreach($categories as $category) {
			$scores_array[$category] = readable_score_history(get_reference_score_history($post_id, $category), $post_id, $category);
		}

		return $scores_array;
	}

	function get_contest_scores($post_id) {
		$categories = [
			'mus',
			'per',
			'sng',
		];

		$scores_array = [];
		foreach($categories as $category) {
			$scores_array[$category] = get_post_meta($post_id, sprintf('contest_score_%s', $category), true);
		}

		return $scores_array;
	}

	function readable_score_history($history, $post_id, $category) {
		if(empty($history)) return $history;

		$has_contest_score = false;
		foreach($history as $index => $entry) {
			if(!empty($entry['type']) && $entry['type'] == 'contest_score') {
				$has_contest_score = true;
			} else {
				$history[$index]['timestamp'] = $entry['time'] != 'Imported'
					? sprintf('%s', wp_date('n/j/Y g:i:s a T', round($entry['time'] / 1000)))
					: 'Imported';

				$updated_by = '';
				//First get judge record name
				if(!empty($entry['judge_id'])) $updated_by = get_the_title($entry['judge_id']);

				//Next get user record name
				if(empty($updated_by) && !empty($entry['user_id'])) {
					if($entry['user_id'] == 'Imported') {
						$updated_by = 'Imported';
					} else {
						$user = get_user_by('ID', $entry['user_id']);
						$updated_by = !empty($user->data->display_name) ? $user->data->display_name : '';
					}
				}

				$history[$index]['updated_by'] = $updated_by;
			}
		}

		if(!$has_contest_score) {
			$contest_score = get_post_meta($post_id, sprintf('contest_score_%s', $category), true);
			if(!empty($contest_score)) {
				$history[] = [
					'type' => 'contest_score',
					'score' => $contest_score,
					'time' => 0,
				];
			}
		}

		return $history;
	}

	function get_reference_score_history($post_id, $category) {
		$value = get_post_meta($post_id, sprintf('%s_scores', $category), true);
		$decoded_value = !empty($value) ? json_decode($value, true) : [];

		//Sort newest to oldest
		if(!empty($decoded_value)) {
			usort($decoded_value, function($a, $b){
				return $b['time'] <=> $a['time'];
			});
		} else {
			$contest_score = get_post_meta($post_id, sprintf('contest_score_%s', $category), true);
			if(!empty($contest_score)) {
				$decoded_value[] = [
					'type' => 'contest_score',
					'score' => $contest_score,
					'time' => 0,
				];
			}
		}

		return $decoded_value;
	}

	function update_reference_score($update_data) {
		$required_fields = [
			'score',
			'post_id',
			'category',
			'time',
			'user_id',
		];

		//Return if required fields are empty
		foreach($required_fields as $field) {
			if(empty($update_data[$field])) return;
		}

		extract($update_data);
		$history = get_reference_score_history($post_id, $category);

		$new_score = $update_data;
		unset($new_score['category']);
		unset($new_score['post_id']);

		array_unshift($history, $new_score);

		$history_to_save = [];
		foreach($history as $index => $entry) {
			if(!empty($entry['type']) && $entry['type'] == 'contest_score') {
				// Do nothing if contest_score
			} else {
				$history_to_save[] = $entry;
			}
		}

		update_post_meta($post_id, sprintf('%s_scores', $category), json_encode($history_to_save));

		return readable_score_history($history, $post_id, $category);
	}
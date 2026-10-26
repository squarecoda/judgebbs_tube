<?php
	function get_current_judge_id() {
		if(current_user_can('administrator') && !empty($_GET['judge_id'])) {
			return $_GET['judge_id'];
		}

		$user = wp_get_current_user();
		if(empty($user)) return '';

		$search = get_posts([
			'post_type' => 'bbs-judge',
			'posts_per_page' => 1,
			'meta_query' => [
				[
					'key' => 'user_account',
					'value' => $user->ID
				],
			],
			'fields' => 'ids',
		]);
		if(empty($search)) return '';

		return current($search);
	}

	function get_judge_category($post_id) {
		return get_post_meta($post_id, 'judge_category', true);
	}

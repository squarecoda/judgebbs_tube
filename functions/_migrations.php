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
				'posts_per_page' => !empty($_GET['posts_per_page']) ? $_GET['posts_per_page'] : 200,
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
	// add_action('admin_init', 'script_update_playlist_typeaheads');

	function script_update_score_typeaheads() {
		if(current_user_can('administrator') && !empty($_GET[__FUNCTION__]) && $_GET[__FUNCTION__] == 'true') {
			$query = new WP_Query([
				'post_type' => 'bbs-score',
				'posts_per_page' => !empty($_GET['posts_per_page']) ? $_GET['posts_per_page'] : 200,
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
	// add_action('admin_init', 'script_update_score_typeaheads');

	function add_sort_order_to_playlists() {
		if(current_user_can('administrator') && !empty($_GET[__FUNCTION__]) && $_GET[__FUNCTION__] == 'true') {
			$results = get_posts([
				'post_type' => 'bbs-playlist',
				'posts_per_page' => -1,
				'fields' => 'ids',
			]);

			foreach($results as $result_id) {
				$sort_order = get_post_meta($result_id, 'playlist_sort_order', true);
				if(empty($sort_order)) {
					$legacy_id = get_post_meta($result_id, 'legacy_id', true);
					$sort_order = !empty($legacy_id) ? $legacy_id : get_post_time('U', true, $result_id);
					display_result($sort_order);
					update_post_meta($result_id, 'playlist_sort_order', $sort_order);
				}
			}
			wp_die();
		}
	}
	add_action('admin_init', 'add_sort_order_to_playlists');

	function create_users_for_active_judges() {
		if(current_user_can('administrator') && !empty($_GET[__FUNCTION__]) && $_GET[__FUNCTION__] == 'true') {
			$results = get_posts([
				'post_type' => 'bbs-judge',
				'posts_per_page' => -1,
				'fields' => 'ids',
				'meta_query' => [
					[
						'relation' => 'OR',
						[
							'key' => 'user_account',
							'value' => '',
						],
						[
							'key' => 'user_account',
							'compare' => 'NOT EXISTS',
						],
					],
					[
						'key' => 'status',
						'value' => 'inactive',
						'compare' => '!=',
					],
				],
			]);

			display_result(count($results));
			display_result($results);


			foreach($results as $result_id) {
				$fields = [
					'email',
					'first_name',
					'last_name',
					'legacy_username',
				];

				foreach($fields as $field) $$field = get_post_meta($result_id, $field, true);
	
				// display_result($first_name);
				// display_result($last_name);
				// display_result($email);
				// display_result(get_the_title($result_id));
				// display_result(get_edit_post_link($result_id));

				// wp_die();

				if(email_exists($email)) {
					echo sprintf('A user with this email (%s) already exists.  Please try another email address.', $email);
				}

				//Set unique username
				if(!empty($first_name) && !empty($first_name)) {
					$username = strtolower(str_replace([' ', '-', '.'], '', sprintf('%s%s', $first_name, $last_name)));
				} else {
					$username = $legacy_username;
				}
				$original_username = $username;
				$i = 1;
				while(username_exists($username)) { 
					$username = sprintf('%s%s', $original_username, $i);
					$i++;
				}

				//Create User
				$user_id = wp_create_user($username, bin2hex(openssl_random_pseudo_bytes(4)), $email);
				if(!empty($first_name) && !empty($first_name)) {
					wp_update_user(['ID' => $user_id, 'first_name' => $first_name, 'last_name' => $last_name]);
				}

				//Update Judge Record
				update_post_meta($result_id, 'user_account', $user_id);
			}
			wp_die();
		}
	}
	add_action('admin_init', 'create_users_for_active_judges');


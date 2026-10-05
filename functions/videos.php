<?php
	function get_video_search_results() {
		$videos_obj = new SquareCoda\Theme\Videos(false);
		return $videos_obj->get_search_results();
	}

	function clear_empty_params_for_search_pages() {
		if(isset($_GET['video-search-filters'])) {
			$has_empty_vars = false;
			$new_get_vars = [];
			foreach($_GET as $key => $value) {
				$quartet_size_override = in_array($key, ['size_min', 'size_max']) && !empty($_GET['contestant_type']) && $_GET['contestant_type'] == 'quartet';

				if(!empty($value) && !$quartet_size_override) {
					$new_get_vars[$key] = $value;
				} else {
					$has_empty_vars = true;
				}
			}

			$request_scheme = !empty($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https';
			$hostname = $_SERVER['HTTP_HOST'];

			if(!empty($new_get_vars) && $has_empty_vars) {
				$redirect_url = sprintf('%s://%s%s?%s', $request_scheme, $hostname, current(explode('?', $_SERVER['REQUEST_URI'])), http_build_query($new_get_vars));
				wp_redirect($redirect_url); exit;
			} elseif($has_empty_vars) {

				$redirect_url = sprintf('%s://%s%s', $request_scheme, $hostname, current(explode('?', $_SERVER['REQUEST_URI'])));
				wp_redirect($redirect_url); exit;
			}
		}
	}
	add_action('template_redirect', 'clear_empty_params_for_search_pages');

	function update_video_hidden_fields() {
		if(current_user_can('administrator') && !empty($_GET[__FUNCTION__]) && $_GET[__FUNCTION__] == 'true') {
			$videos_obj = new SquareCoda\Theme\Videos(false);

			$args = [
				'post_type' => 'bbs-video',
				'posts_per_page' => -1,
				'fields' => 'ids',
			];

			$videos = get_posts($args);

			foreach($videos as $video_id) {
				$videos_obj->update_hidden_score_fields($video_id);
				$videos_obj->update_hidden_contestant_fields($video_id);
			}

			display_result(count($videos));
			display_result($videos);

			wp_die();
		}
	}
	// add_action('admin_init', 'update_video_hidden_fields');
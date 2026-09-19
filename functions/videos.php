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
				if(!empty($value)) {
					$new_get_vars[$key] = $value;
				} else {
					$has_empty_vars = true;
				}
			}
	
			if(!empty($new_get_vars) && $has_empty_vars) {
				// display_result(http_build_query($new_get_vars));
				$redirect_url = sprintf('%s://%s%s?%s', $_SERVER['REQUEST_SCHEME'], $_SERVER['HTTP_HOST'], current(explode('?', $_SERVER['REQUEST_URI'])), http_build_query($new_get_vars));
				wp_redirect($redirect_url); exit;
			} elseif($has_empty_vars) {
				$redirect_url = sprintf('%s://%s%s', $_SERVER['REQUEST_SCHEME'], $_SERVER['HTTP_HOST'], current(explode('?', $_SERVER['REQUEST_URI'])));
				wp_redirect($redirect_url); exit;
			}
		}
	}
	add_action('template_redirect', 'clear_empty_params_for_search_pages');
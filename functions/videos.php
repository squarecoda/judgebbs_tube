<?php
	function get_video_search_results() {
		$videos_obj = new SquareCoda\Theme\Videos(false);
		return $videos_obj->get_search_results();
	}

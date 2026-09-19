<?php
	function add_post_and_get_to_context( $context ) {
	    if(!empty($_GET)) $context['request']['get'] = $_GET;
	    if(!empty($_POST)) $context['request']['post'] = $_POST;
	    return $context;
	}
	add_filter('timber/context', 'add_post_and_get_to_context');

	function get_scoring_categories() {
		return [
			'mus' => 'Musicality',
			'per' => 'Performance',
			'sng' => 'Singing',
		];
	}
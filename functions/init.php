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

	function show_logo_and_title_in_header_title_display($display, $header_logo, $header_title, $branding) {
		if(!empty($header_logo)) {
			$img_class = (!empty($branding['vertical_or_square_logo']) && $branding['vertical_or_square_logo'] == 'yes') ? 'vertical-or-square' : '';
			return sprintf('<a class="header__branding" href="%s">%s<div class="header-text">%s</div></a>', home_url('/'), co_get_image_html($header_logo, $img_class), $header_title);
		} else {
			return sprintf('<a class="header__branding" href="%s">%s</a>', home_url('/'), $header_title);
		}

		return $display;
	}
	add_filter(sprintf('%s/header_title_display', 'sc-theme'), 'show_logo_and_title_in_header_title_display', 10, 4);

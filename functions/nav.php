<?php
	function add_logout_link_to_menu($items, $args) {
		$items .= sprintf('<li class="menu-item menu-item-type-post_type menu-item-object-page"><a href="%s">%s</a></li>', wp_logout_url('/'), 'Logout');
		return $items;
	}
	add_filter('wp_nav_menu_items', 'add_logout_link_to_menu', 10, 2);
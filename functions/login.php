<?php
	function show_login_form_unless_logged_in($content) {
		if(!empty(get_current_judge_id())) return $content;

		if(is_user_logged_in()) {
			ob_start();
				echo 'Your login is not associated with a judge account.  Please contact SCJC if this is in error.';
				echo sprintf('<a href="%s" class="logout-link">%s <span class="fas fa-right-from-bracket"></span></a>', wp_logout_url('/'), 'Logout');
			return sprintf('<div class="bhs-login-form-wrapper">%s</div>', sc_get_message(ob_get_clean(), 'info'));
		}

		ob_start();
			include sprintf('%s/templates/login-form.php', get_stylesheet_directory());
		return ob_get_clean();
	}
	add_filter('the_content', 'show_login_form_unless_logged_in', 50);

	function hide_admin_bar_if_non_admin($show) {
		return current_user_can('manage_options') ? $show : false;
	}
	add_filter('show_admin_bar', 'hide_admin_bar_if_non_admin');

	function hide_backend_if_non_editor() {
		if(!current_user_can('edit_pages')) {
			wp_safe_redirect('/');
			exit;
		}
	}
	add_action('admin_init', 'hide_backend_if_non_editor');

	function redirect_to_home_if_not_logged_in() {
		if(!is_home() && !is_front_page() && (!is_user_logged_in() || empty(get_current_judge_id()))) {
			wp_safe_redirect('/');
			exit;
		}
	}
	add_action('template_redirect', 'redirect_to_home_if_not_logged_in');

	function hide_main_menu_if_non_admin() {
		if(!empty(get_current_judge_id())) return;
		ob_start();
			echo sprintf('.header-menu{display: none !important;}');
			echo sprintf('.header-wrapper.is-row{--sc-header-border-bottom-width: 5px}');
			echo sprintf('.header-wrapper.is-row > .container{justify-content: center;}');
		echo sprintf('<style>%s</style>', ob_get_clean());
	}
	add_action(sprintf('%s/%s', 'sc-theme', 'header'), 'hide_main_menu_if_non_admin');

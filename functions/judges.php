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

	function get_judge_status_label($value) {
		$judges_obj = new SquareCoda\Theme\Judges(false);
		$status_options = $judges_obj->get_judge_status_options();

		return !empty($status_options[$value]) ? $status_options[$value] : $value;
	}
	add_filter(sprintf('bbs/meta_display/judge/%s', 'status'), 'get_judge_status_label');

	function get_judge_email_display($value) {
		if(empty($value)) return '';

		return sprintf('<a href="mailto:%s">%s</a>', $value, $value);
	}
	add_filter(sprintf('bbs/meta_display/judge/%s', 'email'), 'get_judge_email_display');

	function get_judge_tel_display($value) {
		if(empty($value)) return '';

		return sprintf('<a href="tel:%s">%s</a>', $value, formatted_phone($value));
	}
	add_filter(sprintf('bbs/meta_display/judge/%s', 'home_phone'), 'get_judge_tel_display');
	add_filter(sprintf('bbs/meta_display/judge/%s', 'cell_phone'), 'get_judge_tel_display');


	function get_judge_address_display($value, $context) {
		if(empty($value)) return '';

		$fields = [
			'address_1',
			'address_2',
			'city',
			'state_province',
			'postal_code',
		];

		foreach($fields as $field) $$field = $context[$field];

		$delimiter = '<br>';

		$html = '';
		$html .= $address_1;
		$html .= ($address_2) ? sprintf('%s%s', ', ', $address_2) : '';
		$html .= sprintf('%s%s, %s %s', $delimiter, $city, $state_province, $postal_code);

		return $html;
	}
	add_filter(sprintf('bbs/meta_display/judge/%s', 'address_1'), 'get_judge_address_display', 10, 2);


	function get_judge_district_label($value) {
		$judges_obj = new SquareCoda\Theme\Judges(false);
		$options = $judges_obj->get_district_options();

		return !empty($options[$value]) ? $options[$value] : $value;
	}
	add_filter(sprintf('bbs/meta_display/judge/%s', 'district'), 'get_judge_district_label');	
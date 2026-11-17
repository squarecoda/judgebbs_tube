<?php

function ac_show_judge_name($value, $context, $post_id, $table_screen, $list_screen) {
	$post_type = 'bbs-playlist';
	$fields = [
		'judge'
	];

	if($context->get_post_type() == $post_type) {
		$meta_key = $context->get_meta_key();
		if(in_array($meta_key, $fields)) {
			$meta_value = get_post_meta($post_id, $meta_key, true);
			if(!empty($meta_value)) {
				$value = get_the_title($meta_value);
			}
		}
	}

	return $value;
}
add_filter('ac/column/render', 'ac_show_judge_name', 10, 5);

function ac_show_check($value, $context, $post_id, $table_screen, $list_screen) {
	$post_type = 'bbs-playlist';
	$fields = [
		'public',
		'scoring',
		'hide_scores',
	];

	if($context->get_post_type() == $post_type) {
		$meta_key = $context->get_meta_key();
		if(in_array($meta_key, $fields)) {
			$meta_value = get_post_meta($post_id, $meta_key, true);
			if($meta_value === 'on') {
				$value = '<span class="far fa-check-circle" style="color: mediumseagreen; font-size: 20px"></span>';
			}
		}
	}

	return $value;
}
add_filter('ac/column/render', 'ac_show_check', 10, 5);

function ac_show_judge_status($value, $context, $post_id, $table_screen, $list_screen) {
	$post_type = 'bbs-judge';
	$fields = [
		'status'
	];

	if($context->get_post_type() == $post_type) {
		$meta_key = $context->get_meta_key();
		if(in_array($meta_key, $fields)) {
			$meta_value = get_post_meta($post_id, $meta_key, true);
			if(!empty($meta_value)) {
				$judges_obj = new SquareCoda\Theme\Judges(false);
				$status_options = $judges_obj->get_judge_status_options();

				if(!empty($status_options[$meta_value])) $value = $status_options[$meta_value];
			}
		}
	}

	return $value;
}
add_filter('ac/column/render', 'ac_show_judge_status', 10, 5);

function ac_show_judge_category($value, $context, $post_id, $table_screen, $list_screen) {
	$post_type = 'bbs-judge';
	$fields = [
		'judge_category'
	];

	if($context->get_post_type() == $post_type) {
		$meta_key = $context->get_meta_key();
		if(in_array($meta_key, $fields)) {
			$meta_value = get_post_meta($post_id, $meta_key, true);
			if(!empty($meta_value)) {
				$value = strtoupper($meta_value);
			}
		}
	}

	return $value;
}
add_filter('ac/column/render', 'ac_show_judge_category', 10, 5);

<?php

function ac_show_judge_name($value, $context, $post_id, $table_screen, $list_screen) {
	if($context->get_post_type() == 'bbs-playlist') {
		$meta_key = $context->get_meta_key();
		if($meta_key == 'judge') {
			$meta_value = get_post_meta($post_id, $meta_key, true);
			if(!empty($meta_value)) $value = get_the_title($meta_value);
		}
	}

	return $value;
}
add_filter('ac/column/render', 'ac_show_judge_name', 10, 5);

function ac_show_check($value, $context, $post_id, $table_screen, $list_screen) {
	$checkbox_fields = [
		'public',
		'scoring',
		'hide_scores',
	];

	if($context->get_post_type() == 'bbs-playlist') {
		$meta_key = $context->get_meta_key();
		if(in_array($meta_key, $checkbox_fields)) {
			$meta_value = get_post_meta($post_id, $meta_key, true);
			if($meta_value === 'on') {
				$value = '<span class="far fa-check-circle" style="color: mediumseagreen; font-size: 20px"></span>';
			}
		}
	}

	return $value;
}
add_filter('ac/column/render', 'ac_show_check', 10, 5);

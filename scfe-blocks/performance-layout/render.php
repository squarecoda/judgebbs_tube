<?php
	global $post;

	$performance_obj = new SquareCoda\Theme\Performances(false);

	$fields = $performance_obj->decode_json($performance_obj->custom_fields());

	$field_values = [];
	foreach($fields as $field_name => $field_info) {
		$field_values[$field_name] = get_post_meta($post->ID, $field_name, true);
	}

	Timber::render('performance-layout/display.twig', $field_values);

	// display_result($field_values);
	// display_result($fields);

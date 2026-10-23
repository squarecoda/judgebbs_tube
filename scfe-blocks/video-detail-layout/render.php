<?php
	global $post;

	$post_obj = new SquareCoda\Theme\Videos(false);

	$fields = $post_obj->decode_json($post_obj->custom_fields());

	$field_values = [];
	foreach($fields as $field_name => $field_info) {
		$field_values[$field_name] = get_post_meta($post->ID, $field_name, true);
	}

	Timber::render(sprintf('%s/display.twig', basename(dirname(__FILE__))), $field_values);

	// display_result($field_values);
	// display_result($fields);

<?php
	global $post;

	$post_obj = new SquareCoda\Theme\Videos(false);

	$fields = $post_obj->decode_json($post_obj->custom_fields());

	$field_values = ['id' => $post->ID];
	foreach($fields as $field_name => $field_info) {
		$field_values[$field_name] = get_post_meta($post->ID, $field_name, true);
	}

	$all_fields = array_merge(
		[
			'get' => !empty($_GET) ? $_GET : [],
			'display_fields' => $post_obj->get_post_display_fields($post->ID),
		],
		$field_values,
	);

	Timber::render(sprintf('%s/display.twig', basename(dirname(__FILE__))), $all_fields);

	// display_result($field_values);
	// display_result($fields);
	// display_result($all_fields);

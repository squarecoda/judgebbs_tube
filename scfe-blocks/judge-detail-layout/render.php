<?php
	global $post;

	$post_obj = new SquareCoda\Theme\Judges(false);

	$fields = $post_obj->decode_json($post_obj->custom_fields());

	$field_values = [
		'id' => $post->ID,
		'title' => get_the_title($post->ID),
	];
	foreach($fields as $field_name => $field_info) {
		if(!in_array($field_info['type'], ['divider', 'tab', 'section_header'])) $field_values[$field_name] = get_post_meta($post->ID, $field_name, true);
	}

	Timber::render(sprintf('%s/display.twig', basename(dirname(__FILE__))), $field_values);

	// display_result($field_values);
	// display_result($fields);

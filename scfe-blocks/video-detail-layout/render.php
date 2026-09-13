<?php
	global $post;

	$videos_obj = new SquareCoda\Theme\Videos(false);

	$fields = $videos_obj->decode_json($videos_obj->custom_fields());

	$field_values = [];
	foreach($fields as $field_name => $field_info) {
		$field_values[$field_name] = get_post_meta($post->ID, $field_name, true);
	}

	Timber::render('video-detail-layout/display.twig', $field_values);

	// display_result($field_values);
	// display_result($fields);

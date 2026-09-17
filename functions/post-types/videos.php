<?php

namespace SquareCoda\Theme;

use DateTime;

class Videos extends Child_Theme {

	public $module_slug = 'video';
	public $post_type_slug = 'video';
	public $post_type = 'bbs-video';
	public $singular = 'Video';
	public $plural = 'Videos';

	public function __construct($run_filters = true) {
		parent::set_props();

		if($run_filters) {
			// Register Post Types
			add_action('init', [$this, 'register_post_type'], 20);

			// Update Reference Scores
			$ajax_action = 'bbs_update_reference_score';
			add_action(sprintf('wp_ajax_%s', $ajax_action), [$this, $ajax_action]);

			// Get Posts

			// Post Type Edit Pages
			add_action('edit_form_after_title', [$this, 'custom_edit_form']);

			// Field Formatting
			add_action('edit_form_after_title', [$this, 'show_name_instead_of_title'], 5);
			add_action('sc_field_editor/after_process_fields', [$this, 'update_title_when_data_changed']);

			// Custom Fields
			add_shortcode(sprintf('sc_meta_fields_%s', $this->post_type), [$this, 'custom_fields']);

			// Imports
			add_filter(sprintf('%s/%s/%s/%s', 'sc_field_editor', 'imports', $this->post_type, 'ignore_headers'), [$this, 'ignore_import_headers']);
			add_action(sprintf('%s/%s/%s/%s/%s', 'sc_field_editor', 'imports', 'process', $this->post_type, 'contestant'), [$this, 'process_contestant_field'], 10, 3);
			add_action(sprintf('%s/%s/%s/%s/%s', 'sc_field_editor', 'imports', 'process', $this->post_type, 'contest_score_mus'), [$this, 'process_scores_field'], 10, 3);
			add_action(sprintf('%s/%s/%s/%s', 'sc_field_editor', 'imports', 'after_create_post', $this->post_type), [$this, 'process_fields_after_import']);
		}
	}


	//======================
	// Register Post Types
	//======================
	public function register_post_type() {
		foreach($this->get_post_types() as $slug => $post_info) {
			do_shortcode(sprintf('[sc_field_editor_register_post_type slug="%s" post_info="%s"]', $slug, $this->encode_json($post_info)));
		}
	}

	protected function get_post_types() {
		return [
			$this->post_type => [
				'singular' => $this->singular,
				'plural' => $this->plural,
				'rewrite' => $this->post_type_slug,
				'menu_icon' => 'dashicons-video-alt3',
				'supports' => ['none'],
			],
		];
	}


	//=========================
	// Update Reference Scores
	//=========================
	public function bbs_update_reference_score() {
		$update_data = $_POST;
		unset($update_data['action']);

		$score = $_POST['score'];

		//User/Judge IDs
		$update_data['user_id'] = get_current_user_id();
		$update_data['judge_id'] = $this->get_judge_from_user($update_data['user_id']);

		$score_history = update_reference_score($update_data);
		// $score_history = $update_data;

		$postfields = $_POST;
		echo json_encode(compact('score', 'update_data', 'score_history', 'postfields'));
		exit;
	}


	//======================
	// Get Posts
	//======================


	//======================
	// Post Type Edit Pages
	//======================
	public function custom_edit_form() {
		global $post;

		if($post->post_type == $this->post_type) {
			echo do_shortcode('[sc_meta_form back_end="true" post_type="' . $post->post_type . '" post_id="' . $post->ID . '"]');
		}
	}


	//======================
	// Field Formatting
	//======================
	public function update_title_when_data_changed($post_id) {
		if(get_post_type($post_id) == $this->post_type) {
			$full_name = $this->get_full_name($post_id);
			if(get_the_title($post_id) != $full_name) {
				wp_update_post([
					'ID' => $post_id,
					'post_title' => $full_name,
					'post_name' => '',
				]);
			}
		}
	}

	public function get_full_name($post_id) {
		$fields = [
			'song_title',
			'contestant',
			'contest_district',
			'contest_type',
			'video_date',
		];

		foreach($fields as $field) $$field = get_post_meta($post_id, $field, true);

		//Options
		$district_options = $this->get_district_options(true);
		$type_options = $this->get_contest_type_options();

		//Updates to fields
		if(!empty($contestant)) $contestant = get_the_title($contestant);
		if(!empty($district_options[$contest_district])) $contest_district = $district_options[$contest_district];
		if(!empty($type_options[$contest_type])) $contest_type = $type_options[$contest_type];
		if(!empty($video_date)) $video_date = date('n/j/Y', strtotime($video_date));

		$event_name = in_array($contest_type, ['District', 'Prelims', 'Divisional']) 
			? sprintf('%s %s', $contest_district, $contest_type) 
			: sprintf('%s %s', $contest_type, 'Contest');

		return sprintf('%s &ndash; %s (%s %s)', $song_title, $contestant, $event_name, $video_date);
	}

	public function show_name_instead_of_title($post) {
		if($post->post_type == $this->post_type) {
			echo sprintf('<h1>%s</h1>', get_the_title($post->ID));
			$permalink_pattern = '<div id="edit-slug-box" class="hide-if-no-js" style="padding-left: 0"><strong>Permalink:</strong> <span id="sample-permalink"><a href="%s">%s</a></span></div>';
			echo sprintf($permalink_pattern, get_the_permalink($post->ID), get_the_permalink($post->ID));
		}
	}


	//====================
	// Custom Fields
	//====================
	public function custom_fields($attributes = []) {
		extract(shortcode_atts([
			'edit' => false,
		], $attributes));

		$fields = [
			'song_title' => [
				'type' => 'text',
				'styles' => [
					'width' => '25%',
				],
				'attributes' => [
					'required' => true,
				],
			],
			'contestant' => [
				'type' => 'typeahead',
				'label' => 'Contestant',
				'search_type' => 'bbs-contestant', 
				'search_fields' => [ 
					'title',
				],
				'attributes' => [
					'required' => true,
					'placeholder' => 'Type contestant name',
				],
				'additional_fields' => [
					'type' => ['function' => 'static_get_type_display', 'class' => '\\SquareCoda\\Theme\\Contestants'],
					'voicing' => ['function' => 'static_get_voicing_display', 'class' => '\\SquareCoda\Theme\\Contestants'],
					'age' => ['function' => 'static_get_age_display', 'class' => '\\SquareCoda\Theme\\Contestants'],
					'size' => ['function' => 'static_get_size_display', 'class' => '\\SquareCoda\Theme\\Contestants'],
				],
				'result_template' => '<div class="title">{{title}}</div><div class="meta"></div><div class="meta"><span class="description">Voicing: </span><span class="value">{{voicing}}</span></div><div class="meta"><span class="description">Type: </span><span class="value">{{type}}</span></div><div class="meta"><span class="description">Contestant Size: </span><span class="value">{{size}}</span></div><div class="meta"><span class="description">Age: </span><span class="value">{{age}}</span></div>',
				'multiple' => false,
				'add_new' => true,
				'styles' => [
					'width' => '25%'
				],
			],
			'song_style' => [
				'type' => 'radio',
				'radio_options' => $this->get_song_style_options(),
				'styles' => [
					'width' => '25%',
				],
			],
			'video_url' => [
				'type' => 'text',
				'label' => 'Video URL',
				'styles' => [
					'width' => '25%',
				],
			],
			'use_custom_size' => [
				'type' => 'true-false',
				'label' => 'Custom Contestant Size?',
				'styles' => [
					'width' => '25%'
				],
			],
			'use_custom_age' => [
				'type' => 'true-false',
				'label' => 'Custom Contestant Age?',
				'styles' => [
					'width' => '25%'
				],
			],
			'custom_contestant_size' => [
				'type' => 'number',
				'label' => 'Contestant Size',
				'styles' => [
					'width' => '25%',
				],
				'conditional_rules' => [
					'show' => [
						[
							'key' => 'use_custom_size',
							'value' => 'on',
						],
					],
				],
			],
			'custom_contestant_age' => [
				'type' => 'radio',
				'label' => 'Contestant Age',
				'radio_options' => $this->get_contestant_age_options(),
				'attributes' => [
					'required' => true,
				],
				'styles' => [
					'width' => '25%',
				],
				'conditional_rules' => [
					'show' => [
						[
							'key' => 'use_custom_age',
							'value' => 'on',
						],
					],
				],
			],
		];

		if(current_user_can('administrator')) {
			$fields = array_merge($fields, [
				'scores_divider' => [
					'type' => 'divider',
				],
				'reference_scores' => [
					'type' => 'html',
					'label' => ' ',
					'content' => $this->show_timber_template('reference-scores.twig'),
				],
				'contest_scores' => [
					'type' => 'html',
					'label' => ' ',
					'content' => $this->show_timber_template('contest-scores.twig'),
				],
			]);
		}

		$fields = array_merge($fields, [
			'contest_divider' => [
				'type' => 'divider',
			],
			'video_date' => [
				'type' => 'datepicker',
				'styles' => [
					'width' => '25%',
				],
			],
			'contest_district' => [
				'type' => 'select',
				'select_options' => $this->get_district_options(true),
				'styles' => [
					'width' => '25%',
				],
				'attributes' => [
					'required' => true,
				],
			],
			'contest_type' => [
				'type' => 'select',
				'select_options' => $this->get_contest_type_options(),
				'styles' => [
					'width' => '25%',
				],
				'attributes' => [
					'required' => true,
				],
			],
			'panel_size' => [
				'type' => 'number',
				'styles' => [
					'width' => '25%',
				],
			],
		]);

		$fields = array_merge($fields, [
			'misc_divider' => [
				'type' => 'divider',
			],
			'contest_set_pairing' => [
				'type' => 'typeahead',
				'search_type' => 'bbs-video', 
				'search_fields' => [ 
					'title',
				],
				'attributes' => [
					'placeholder' => 'Type video name',
				],
				'additional_fields' => [
					'type' => ['function' => 'static_get_type_display', 'class' => '\\SquareCoda\\Theme\\Contestants'],
					'voicing' => ['function' => 'static_get_voicing_display', 'class' => '\\SquareCoda\Theme\\Contestants'],
					'age' => ['function' => 'static_get_age_display', 'class' => '\\SquareCoda\Theme\\Contestants'],
					'size' => ['function' => 'static_get_size_display', 'class' => '\\SquareCoda\Theme\\Contestants'],
				],
				'result_template' => '<div class="title">{{title}}</div>',
				'multiple' => false,
				'add_new' => false,
				'styles' => [
					'width' => '25%'
				],
			],
			'contest_set_order' => [
				'type' => 'number',
				'styles' => [
					'width' => '25%',
				],
			],
			'media_quality' => [
				'type' => 'select',
				'select_options' => $this->get_media_quality_options(),
				'styles' => [
					'width' => '25%',
				],
			],
			'comments' => [
				'type' => 'textarea',
				'attributes' => [
					'rows' => 4,
				],
				'styles' => [
					'width' => '25%',
				],
			],
		]);

		$fields = array_merge($fields, [
			'legacy_divider' => [
				'type' => 'divider',
			],
			'legacy_id' => [
				'type' => 'text',
				'label' => 'Legacy ID',
				'instructions' => 'ID for video from previous website',
				'styles' => [
					'width' => '25%',
				],
			],
			'legacy_contest_set_pairing' => [
				'type' => 'number',
				'instructions' => 'ID for video from previous website',
				'styles' => [
					'width' => '25%',
				],
			],
		]);

		return $this->encode_json(apply_filters(sprintf('%s/%s/fields', $this->theme_slug, $this->module_slug), $fields, $edit));
	}


	//====================
	// Imports
	//====================
	public function ignore_import_headers($headers) {
		$headers = array_merge($headers, [
			'contestant_age',
			'contestant_voicing',
			'contestant_group_size',
			'contestant_group_type',
		]);

		$categories = ['mus', 'per', 'sng'];
		foreach($categories as $category) {
			$headers = array_merge($headers, [
				sprintf('%s_ref', $category),
				sprintf('%s_ref_updated', $category),
				sprintf('contest_score_%s', $category),
			]);
		}

		return $headers;
	}

	public function process_contestant_field($field_value, $field_name, $row) {
		$contestant_search = get_posts([
			'post_type' => 'bbs-contestant',
			'posts_per_page' => 1,
			'name' => $field_value,
			'fields' => 'ids',
		]);

		if(!empty($contestant_search)) {
			$id = current($contestant_search);

			//Add contestant_size and age
			$row_values = [
				'age' => !empty($row['contestant_age']) ? $row['contestant_age'] : '',
				'contestant_size' => !empty($row['contestant_group_size']) ? $row['contestant_group_size'] : '',
			];

			$field_value_array = ['id' => $id];
			foreach($row_values as $key => $value) $field_value_array[$key] = $value;

			return $this->encode_json($field_value_array);
		} else {
			$id = wp_insert_post([
				'post_type' => 'bbs-contestant',
				'post_title' => $field_value,
				'post_status' => 'publish',
				'meta_input' => [
					'age' => !empty($row['contestant_age']) ? $row['contestant_age'] : '',
					'voicing' => !empty($row['contestant_voicing']) ? $row['contestant_voicing'] : '',
					'type' => !empty($row['contestant_group_type']) ? $row['contestant_group_type'] : '',
					'contestant_size' => !empty($row['contestant_group_size']) ? $row['contestant_group_size'] : '',
				],
			]);

			return $this->encode_json(compact('id'));
		}
	}

	public function process_scores_field($field_value, $field_name, $row) {
		$categories = ['mus', 'per', 'sng'];
		
		$field_value_array = [];
		foreach($categories as $category) {
			$field_names = [
				sprintf('%s_ref', $category),
				sprintf('%s_ref_updated', $category),
				sprintf('contest_score_%s', $category),
			];
			foreach($field_names as $field_name) {
				$field_value_array[$field_name] = !empty($row[$field_name]) ? $row[$field_name] : '';
			}
		}
		return $this->encode_json($field_value_array);
	}

	public function process_fields_after_import($post_id) {
		//Contestants
		$this->process_contestant_after_import($post_id);

		//Scores
		update_post_meta($post_id, 'imported_scores', $this->get_field('contest_score_mus', $post_id));
		update_post_meta($post_id, 'contest_score_mus', '');
		$this->process_scores_after_import($post_id);

		//Title
		$this->update_title_when_data_changed($post_id);
	}

	public function process_contestant_after_import($post_id) {
		$contestant_array = $this->decode_json($this->get_field('contestant', $post_id));

		//Set contestant value to id
		update_post_meta($post_id, 'contestant', $contestant_array['id']);

		//Check contestant_size and age
		if(!empty($contestant_array['age'])) {
			$import_value = $contestant_array['age'];
			$post_value = $this->get_field('age', $contestant_array['id']);
			if($import_value != $post_value) {
				update_post_meta($post_id, 'use_custom_age', 'on');
				update_post_meta($post_id, 'custom_contestant_age', $import_value);
			}
		}

		if(!empty($contestant_array['contestant_size'])) {
			$import_value = $contestant_array['contestant_size'];
			$post_value = $this->get_field('contestant_size', $contestant_array['id']);
			if($import_value != $post_value) {
				update_post_meta($post_id, 'use_custom_size', 'on');
				update_post_meta($post_id, 'custom_contestant_size', $import_value);

				update_post_meta($post_id, 'use_custom_size', 'on');
				update_post_meta($post_id, 'custom_contestant_size', $contestant_array['contestant_size']);
			}
		}
	}

	public function process_scores_after_import($post_id) {
		$scores_array = $this->decode_json($this->get_field('imported_scores', $post_id));

		$categories = ['mus', 'per', 'sng'];

		foreach($categories as $category) {
			//Update contest scores
			$field_name = sprintf('contest_score_%s', $category);
			if(!empty($scores_array[$field_name])) {
				update_post_meta($post_id, $field_name, $scores_array[$field_name]);
			}

			$field_name = sprintf('%s_ref', $category);
			if(!empty($scores_array[$field_name])) {
				$score = $scores_array[$field_name];

				$date_field_name = sprintf('%s_ref_updated', $category);

				$time = !empty($scores_array[$date_field_name]) 
					? 1000 * (strtotime($scores_array[$date_field_name]) - wp_timezone()->getOffset(new DateTime($scores_array[$date_field_name]))) 
					: 'Imported';

				$user_id = 'Imported';

				$new_score = compact('score', 'post_id', 'category', 'time', 'user_id');

				$history = [];
				array_unshift($history, $new_score);

				update_post_meta($post_id, sprintf('%s_scores', $category), json_encode($history));
			}
		}
	}



	//====================
	// Helpers
	//====================
	public function get_song_style_options() {
		$labels = [
			'Uptune',
			'Ballad',
			'Swing',
			'Comedy',
			'Other',
		];

		$options = [];
		foreach($labels as $label) {
			$options[strtolower($label)] = $label;
		}

		return $options;
	}

	public function get_contest_type_options() {
		$labels = [
			'International',
			'Prelims',
			'District',
			'Division',
			'National',
			'Other',
		];

		$options = [];
		foreach($labels as $label) {
			$options[strtolower($label)] = $label;
		}

		return $options;		
	}

	public function get_media_quality_options() {
		$labels = [
			'Excellent',
			'Good',
			'Poor',
		];

		$options = [];
		foreach($labels as $label) {
			$options[strtolower($label)] = $label;
		}

		return $options;		
	}

}

new Videos;
<?php

namespace SquareCoda\Theme;

class Performances extends Child_Theme {

	public $module_slug = 'performances';
	public $post_type_slug = 'performance';
	public $post_type = 'bbs-performance';
	public $singular = 'Performance';
	public $plural = 'Performances';

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
				'menu_icon' => 'dashicons-microphone',
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
		// return;
		if(get_post_type($post_id) == $this->post_type) {
			$full_name = $this->get_full_performance_name($post_id);
			if(get_the_title($post_id) != $full_name) {
				wp_update_post([
					'ID' => $post_id,
					'post_title' => $full_name,
					'post_name' => '',
				]);
			}
		}
	}

	public function get_full_performance_name($post_id) {
		$fields = [
			'song_title',
			'contestant',
			'contest_district',
			'contest_type',
			'performance_date',
		];

		foreach($fields as $field) $$field = get_post_meta($post_id, $field, true);

		//Options
		$district_options = $this->get_district_options(true);
		$type_options = $this->get_contest_type_options();

		//Updates to fields
		if(!empty($contestant)) $contestant = get_the_title($contestant);
		if(!empty($district_options[$contest_district])) $contest_district = $district_options[$contest_district];
		if(!empty($type_options[$contest_type])) $contest_type = $type_options[$contest_type];
		if(!empty($performance_date)) $performance_date = date('n/j/Y', strtotime($performance_date));

		$performance_name = in_array($contest_type, ['District', 'Prelims', 'Divisional']) 
			? sprintf('%s %s', $contest_district, $contest_type) 
			: sprintf('%s %s', $contest_type, 'Contest');

		return sprintf('%s &ndash; %s (%s %s)', $song_title, $contestant, $performance_name, $performance_date);
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
			'song_style' => [
				'type' => 'select',
				'select_options' => $this->get_song_style_options(),
				'styles' => [
					'width' => '20%',
				],
			],
			'video_type' => [
				'type' => 'radio',
				'radio_options' => [
					'vimeo' => 'Vimeo',
					'youtube' => 'Youtube',
					'url' => 'Full URL',
				],
				'styles' => [
					'width' => '20%',
				],
			],
			'video_id' => [
				'type' => 'text',
				'label' => 'Video ID',
				'styles' => [
					'width' => '35%',
				],
				'conditional_rules' => [
					'show' => [
						[
							'key' => 'video_type',
							'value' => 'has_value',
						],
					],
					'hide' => [
						[
							'key' => 'video_type',
							'value' => 'url',
						],
					],
				]
			],
			'video_url' => [
				'type' => 'text',
				'label' => 'Video URL',
				'styles' => [
					'width' => '35%',
				],
				'conditional_rules' => [
					'show' => [
						[
							'key' => 'video_type',
							'value' => 'url',
						],
					],
				]
			],
		];

		$fields = array_merge($fields, [
			'contestant_divider' => [
				'type' => 'divider',
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
					'contestant_type' => ['function' => 'static_get_contestant_type_display', 'class' => '\\SquareCoda\\Theme\\Contestants'],
					'contestant_voicing' => ['function' => 'static_get_contestant_voicing_display', 'class' => '\\SquareCoda\Theme\\Contestants'],
				],
				'result_template' => '<div class="title">{{title}}</div><div class="meta"></div><div class="meta"><span class="description">Voicing: </span><span class="value">{{contestant_voicing}}</span></div><div class="meta"><span class="description">Type: </span><span class="value">{{contestant_type}}</span></div>', 
				'multiple' => false,
				'styles' => [
					'width' => '25%'
				],
			],
			'contestant_size' => [
				'type' => 'number',
				'styles' => [
					'width' => '25%',
				],
			],
			'contestant_age' => [
				'type' => 'radio',
				'radio_options' => $this->get_contestant_age_options(),
				'styles' => [
					'width' => '25%',
				],
				'attributes' => [
					'required' => true,
				],
			],
		]);

		$fields = array_merge($fields, [
			'scores_divider' => [
				'type' => 'divider',
			],
			'reference_scores' => [
				'type' => 'html',
				'label' => ' ',
				'content' => $this->show_timber_template('reference-scores.twig'),
			],
		]);

		$fields = array_merge($fields, [
			'contest_divider' => [
				'type' => 'divider',
			],
			'performance_date' => [
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
			'notes_divider' => [
				'type' => 'divider',
			],
			'legacy_id' => [
				'type' => 'text',
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
					'width' => '50%',
				],
			],
		]);

		return $this->encode_json(apply_filters(sprintf('%s/%s/fields', $this->theme_slug, $this->module_slug), $fields, $edit));
	}

	public function get_song_style_options() {
		$labels = [
			'Uptune',
			'Ballad',
			'Swing',
			'Other',
			'Comedy',
		];

		$options = [];
		foreach($labels as $label) {
			$options[strtolower($label)] = $label;
		}

		return $options;
	}

	public function get_contestant_age_options() {
		$labels = [
			'Adult',
			'Senior',
			'Youth',
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

new Performances;
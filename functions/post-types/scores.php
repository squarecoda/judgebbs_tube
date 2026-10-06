<?php

namespace SquareCoda\Theme;

use Timber;

class Scores extends Child_Theme {

	public $module_slug = 'scores';
	public $post_type_slug = 'score';
	public $post_type = 'bbs-score';
	public $singular = 'Score';
	public $plural = 'Scores';

	public function __construct($run_filters = true) {
		parent::set_props();

		if($run_filters) {
			// Register Post Types
			add_action('init', [$this, 'register_post_type'], 20);

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
				'menu_icon' => 'dashicons-yes',
				'supports' => ['none'],
				// 'public' => false,
				// 'publicly_queryable' => false,
				// 'show_ui' => false,
				// 'show_in_rest' => false,
				// 'show_in_menu' => false,
			],
		];
	}


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
	public function get_full_name($post_id) {
		$fields = [
			'video',
			'judge',
			'playlist',
		];

		foreach($fields as $field) $$field = get_post_meta($post_id, $field, true);

		if(!empty($video) || !empty($judge) || !empty($playlist)) {

			$event_name_parts = [];
			foreach($fields as $field) {
				//Updates to fields
				if(!empty($$field)) {
					$$field = get_the_title($$field);
					$event_name_parts[] = $$field;
				}
			}

			if(!empty($event_name_parts)) return implode(' | ', $event_name_parts);

			return get_post_meta($post_id, 'legacy_id', true);
		}
	}

	public function show_name_instead_of_title($post) {
		if($post->post_type == $this->post_type) {
			echo sprintf('<h1>%s</h1>', get_the_title($post->ID));
			$permalink_pattern = '<div id="edit-slug-box" class="hide-if-no-js" style="padding-left: 0"><strong>Permalink:</strong> <span id="sample-permalink"><a href="%s">%s</a></span></div>';
			echo sprintf($permalink_pattern, get_the_permalink($post->ID), get_the_permalink($post->ID));
		}
	}

	public function update_title_when_data_changed($post_id) {
		if(get_post_type($post_id) == $this->post_type) {
			$full_name = trim($this->get_full_name($post_id));
			if(!empty($full_name) && sanitize_title(get_the_title($post_id)) != sanitize_title($full_name)) {
				wp_update_post([
					'ID' => $post_id,
					'post_title' => $full_name,
					'post_name' => '',
				]);
			}
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
			'video' => [
				'type' => 'typeahead',
				'search_type' => 'bbs-video', 
				'search_fields' => [ 
					'title',
				],
				'attributes' => [
					'placeholder' => 'Type video name',
				],
				'additional_fields' => [
					'song_title' => ['function' => 'static_get_song_title_display', 'class' => '\\SquareCoda\\Theme\\Videos'],
					'contestant' => ['function' => 'static_get_contestant_display', 'class' => '\\SquareCoda\\Theme\\Videos'],
					'video_date' => ['function' => 'static_get_video_date_display', 'class' => '\\SquareCoda\\Theme\\Videos'],
					'district' => ['function' => 'static_get_district_display', 'class' => '\\SquareCoda\\Theme\\Videos'],
				],
				'result_template' => Timber::compile('typeahead-results/video.twig'),
				'multiple' => false,
				'add_new' => false,
				'styles' => [
					'width' => '25%',
				],
			],
			'judge' => [
				'type' => 'typeahead',
				'search_type' => 'bbs-judge', 
				'search_fields' => [ 
					'title',
				],
				'attributes' => [
					'placeholder' => 'Type judge name',
				],
				'additional_fields' => [
					'judge_category' => ['function' => 'static_get_category_display', 'class' => '\\SquareCoda\\Theme\\Judges'],
				],
				'result_template' => Timber::compile('typeahead-results/judge.twig'),
				'multiple' => false,
				'add_new' => false,
				'styles' => [
					'width' => '25%',
				],
			],
			'playlist' => [
				'type' => 'typeahead',
				'search_type' => 'bbs-playlist', 
				'search_fields' => [ 
					'title',
				],
				'attributes' => [
					'placeholder' => 'Type playlist name',
				],
				'additional_fields' => [
					// 'judge_category' => ['function' => 'static_get_category_display', 'class' => '\\SquareCoda\\Theme\\Judges'],
				],
				'result_template' => Timber::compile('typeahead-results/playlist.twig'),
				'multiple' => false,
				'add_new' => false,
				'styles' => [
					'width' => '25%',
				],
			],
			'score' => [
				'type' => 'number',
				'styles' => [
					'width' => '25%',
				],
			],
			'judge_category' => [
				'type' => 'text',
				'styles' => [
					'width' => '20%',
				],
			],
			'penalty' => [
				'type' => 'wysiwyg',
				'classes' => [
					'even-tabs',
				],
				'styles' => [
					'width' => '40%',
				],
			],
			'comments' => [
				'type' => 'wysiwyg',
				'classes' => [
					'even-tabs',
				],
				'styles' => [
					'width' => '40%',
				],
			],


			'legacy_divider' => [
				'type' => 'divider',
			],
			'legacy_id' => [
				'type' => 'number',
				'styles' => [
					'width' => '25%',
				],
			],
			'legacy_video_id' => [
				'type' => 'number',
				'styles' => [
					'width' => '25%',
				],
			],
			'legacy_judge_id' => [
				'type' => 'number',
				'styles' => [
					'width' => '25%',
				],
			],
			'playlist_name' => [
				'type' => 'text',
				'styles' => [
					'width' => '25%',
				],
			],
		];

		return $this->encode_json(apply_filters(sprintf('%s/%s/fields', $this->theme_slug, $this->module_slug), $fields, $edit));
	}

	static public function get_type_options() {
		$labels = [
			'Quartet',
			'Chorus',
			'Other',
		];

		$options = [];
		foreach($labels as $label) {
			$options[strtolower($label)] = $label;
		}

		return $options;
	}

	static public function get_voicing_options() {
		$labels = [
			'TTBB',
			'SATB',
			'SSAA',
		];

		$options = [];
		foreach($labels as $label) {
			$options[strtolower($label)] = $label;
		}

		return $options;
	}

	static public function static_get_type_display($post_id) {
		$value = get_post_meta($post_id, 'type', true);
		$options = self::get_type_options();

		return !empty($options[$value]) ? $options[$value] : $value;
	}

	static public function static_get_voicing_display($post_id) {
		$value = get_post_meta($post_id, 'voicing', true);
		$options = self::get_voicing_options();

		return !empty($options[$value]) ? $options[$value] : $value;
	}

	static public function static_get_age_display($post_id) {
		$value = get_post_meta($post_id, 'age', true);
		$options = self::get_contestant_age_options();

		return !empty($options[$value]) ? $options[$value] : $value;
	}

	static public function static_get_size_display($post_id) {
		$type = get_post_meta($post_id, 'type', true);
		if($type == 'quartet') return 4;

		$value = get_post_meta($post_id, 'contestant_size', true);
		return $value;
	}

}

new Scores;
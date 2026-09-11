<?php

namespace SquareCoda\Theme;

class Contestants extends Child_Theme {

	public $module_slug = 'contestants';
	public $post_type_slug = 'contestant';
	public $post_type = 'bbs-contestant';
	public $singular = 'Contestant';
	public $plural = 'Contestants';

	public function __construct($run_filters = true) {
		parent::set_props();

		if($run_filters) {
			// Register Post Types
			add_action('init', [$this, 'register_post_type'], 20);

			// Get Posts

			// Post Type Edit Pages
			add_action('edit_form_after_title', [$this, 'custom_edit_form']);

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
				'menu_icon' => 'dashicons-groups',
				'menu_position' => 25,
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


	//====================
	// Custom Fields
	//====================
	public function custom_fields($attributes = []) {
		extract(shortcode_atts([
			'edit' => false,
		], $attributes));

		$fields = [
			'contestant_type' => [
				'type' => 'select',
				'select_options' => $this->get_contestant_type_options(),
				'styles' => [
					'width' => '25%',
				],
				'attributes' => [
					'required' => true,
				],
			],
			'contestant_voicing' => [
				'type' => 'select',
				'select_options' => $this->get_contestant_voicing_options(),
				'styles' => [
					'width' => '25%',
				],
				'attributes' => [
					'required' => true,
				],
			],
		];

		return $this->encode_json(apply_filters(sprintf('%s/%s/fields', $this->theme_slug, $this->module_slug), $fields, $edit));
	}

	public function get_contestant_type_options() {
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

	public function get_contestant_voicing_options() {
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

}

new Contestants;
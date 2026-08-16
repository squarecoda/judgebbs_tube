<?php

namespace SquareCoda\Theme;

class Child_Theme extends Base {

	public function __construct($hooks = true) {
		parent::set_props();

		if($hooks) {
			// Scripts
			add_action('wp_enqueue_scripts', [$this, 'theme_css']);
			add_action('wp_enqueue_scripts', [$this, 'theme_js']);
			add_action('admin_enqueue_scripts', [$this, 'theme_css']);
			add_action('admin_enqueue_scripts', [$this, 'theme_js']);
			add_action('customize_controls_enqueue_scripts', [$this, 'theme_js']);
		}
	}


	//====================
	// Scripts
	//====================	
	public function theme_css() {
		$suffix = 'main.min.css';
		wp_enqueue_style(sprintf('%s-main', 'child'), $this->get_asset('css', $suffix), $this->parent_css, filemtime($this->get_asset('css', $suffix, 'dir')));
	}

	public function theme_js() {
		$suffix = 'main.min.js';
		wp_enqueue_script(sprintf('%s-main', 'child'), $this->get_asset('js', $suffix), ['jquery'], filemtime($this->get_asset('js', $suffix, 'dir')), true);
	}

	protected function get_asset($folder, $suffix, $type = 'url') {
		return sprintf('%s/assets/%s/%s', ($type == 'url') ? get_stylesheet_directory_uri() : get_stylesheet_directory(), $folder, $suffix);
	}

	public function get_address_fields() {
		$fields = [
			'address_1' => [
				'type' => 'text',
				'label' => 'Address 1',
				'styles' => [
					'width' => '50%',
				],
			],
			'address_2' => [
				'type' => 'text',
				'label' => 'Address 2',
				'styles' => [
					'width' => '50%',
				],
			],
			'city' => [
				'type' => 'text',
				'label' => 'City',
				'styles' => [
					'width' => '50%',
				],
			],
			'state_province' => [
				'type' => 'text',
				'label' => 'State/Province',
				'styles' => [
					'width' => '20%',
				],
			],
			'postal_code' => [
				'type' => 'text',
				'label' => 'Postal Code',
				'styles' => [
					'width' => '30%',
				],
			],
		];

		return $fields;
	}

	public function get_full_name($post_id) {
		$fields = [
			'prefix',
			'first_name',
			'middle_initial',
			'last_name',
			'suffix',
			'nickname',
			'display_name',
		];

		$field_values = [];
		foreach($fields as $field) $field_values[$field] = $this->get_field($field, $post_id);

		return apply_filters(sprintf('%s/%s/%s', $this->theme_slug, 'members', 'full_name'), $this->get_full_name_from_fields($field_values), $post_id);
	}

	public function get_full_name_from_fields($field_values) {
		extract($field_values);

		$full_name = (!empty($middle_initial)) ? $first_name . ' ' . $middle_initial . ' ' . $last_name : $first_name . ' ' . $last_name;
		if(!empty($prefix)) $full_name = $prefix . ' ' . $full_name;
		if(!empty($suffix)) $full_name .= ' ' . $suffix;

		return $full_name;	
	}

}

new Child_Theme;
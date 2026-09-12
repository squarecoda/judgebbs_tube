<?php

namespace SquareCoda\Theme;

use Timber;

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


	//====================
	// Helpers
	//====================	
	public function get_judge_from_user($user_id = null) {
		if(empty($user_id)) $user_id = get_current_user_id();

		$judge_search = get_posts([
			'post_type' => 'bbs-judge',
			'posts_per_page' => 1,
			'meta_query' => [
				[
					'key' => 'user_account',
					'value' => $user_id,
				]
			],
			'fields' => 'ids',
		]);

		return !empty($judge_search) ? current($judge_search) : '';
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

	public function get_district_options($include_international = false) {
		$labels = [
			'CAR',
			'CSD',
			'EVG',
			'ILL',
			'JAD',
			'LOL',
			'MAD',
			'NED',
			'NSC',
			'ONT',
			'PIO',
			'SHD',
			'SLD',
			'SUN',
			'FWD',
			'RMD',
			'SWD',

			'BABS',
			'BHA',
			'BHNZ',
			'SNOBS',
		];

		if($include_international) {
			$labels = array_merge(['INTL'], $labels, ['Other']);
		}

		$options = [];
		foreach($labels as $label) {
			$options[strtolower($label)] = $label;
		}

		return $options;
	}

	public function show_timber_template($template, $values = []) {
		ob_start();
		Timber::render($template, $values);
		return ob_get_clean();
	}


}

new Child_Theme;
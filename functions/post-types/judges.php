<?php

namespace SquareCoda\Theme;

class Judges extends Child_Theme {

	public $module_slug = 'judges';
	public $post_type_slug = 'judge';
	public $post_type = 'bbs-judge';
	public $singular = 'Judge';
	public $plural = 'Judges';

	public $name_cookie;
	public $id_cookie;
	public $cookie_length;

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
			add_action('sc_field_editor/after_process_fields', [$this, 'update_member_title_when_full_name_changed']);
			add_filter('the_title', [$this, 'get_name_instead_of_title'], 10, 2);
			add_filter(sprintf('%s/imports/%s/posts_for_associate_files', 'sc_field_editor', $this->post_type), [$this, 'add_post_title_to_import']);
			add_filter(sprintf('%s/imports/%s/posts_with_attachment_ids', 'sc_field_editor', $this->post_type), [$this, 'add_post_title_to_import']);

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
				'menu_icon' => 'dashicons-admin-users',
				'supports' => ['none'],
			],
		];
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
	public function update_member_title_when_full_name_changed($post_id) {
		if(get_post_type($post_id) == $this->post_type) {
			if(get_the_title($post_id) != $this->get_full_name($post_id)) {
				wp_update_post([
					'ID' => $post_id,
					'post_title' => $this->get_full_name($post_id),
					'post_name' => '',
				]);
			}
		}
	}

	public function get_name_instead_of_title($title, $post_id) {
		if(get_post_type($post_id) == $this->post_type) {
			return $this->get_full_name($post_id);
		}

		return $title;
	}

	public function show_name_instead_of_title($post) {
		if($post->post_type == $this->post_type) {
			echo sprintf('<h1>%s</h1>', get_the_title($post->ID));
			$permalink_pattern = '<div id="edit-slug-box" class="hide-if-no-js" style="padding-left: 0"><strong>Permalink:</strong> <span id="sample-permalink"><a href="%s">%s</a></span></div>';
			echo sprintf($permalink_pattern, get_the_permalink($post->ID), get_the_permalink($post->ID));
		}
	}

	public function add_post_title_to_import($posts) {
		foreach($posts as $index => $post) {
			$post['post_title'] = $this->get_full_name_from_fields($post);
			$posts[$index] = $post;
		}
		return $posts;
	}


	//====================
	// Custom Fields
	//====================
	public function custom_fields($attributes = []) {
		extract(shortcode_atts([
			'edit' => false,
		], $attributes));

		$fields = [
			'prefix' => [
				'type' => 'text',
				'styles' => [
					'width' => '15%',
				],
			],
			'first_name' => [
				'type' => 'text',
				'styles' => [
					'width' => '25%',
				],
				'attributes' => [
					'required' => true,
				],
			],
			'middle_initial' => [
				'type' => 'text',
				'label' => 'Middle',
				'styles' => [
					'width' => '15%',
				],
			],
			'last_name' => [
				'type' => 'text',
				'styles' => [
					'width' => '30%',
				],
				'attributes' => [
					'required' => true,
				],
			],
			'suffix' => [
				'type' => 'text',
				'styles' => [
					'width' => '15%',
				],
			],

			'contact_info_divider' => [
				'type' => 'divider',
			],
			'contact_info_section_header' => [
				'type' => 'section_header',
				'label' => 'Contact Info',
			],
			'email' => [
				'type' => 'email',
				'label' => 'Email',
				'styles' => [
					'width' => '40%',
				],
			],
			'home_phone' => [
				'type' => 'tel',
				'classes' => [
					'can-be-hidden',
				],
				'wrapper_attributes' => [
					'hidden_field' => 'home_phone',
				],
				'styles' => [
					'width' => '30%',
				],
			],
			'cell_phone' => [
				'type' => 'tel',
				'classes' => [
					'can-be-hidden',
				],
				'wrapper_attributes' => [
					'hidden_field' => 'cell_phone',
				],
				'styles' => [
					'width' => '30%',
				],
			],
		];


		$fields = array_merge($fields, [
			'address_section_header' => [
				'type' => 'section_header',
				'label' => 'Address',
				'wrapper_attributes' => [
					'hidden_field' => 'home_address',
				],
				'classes' => [
					'can-be-hidden',
					'can-be-hidden-section-header',
				],
			],
		]);
		$fields = array_merge($fields, $this->get_address_fields());


		$fields = array_merge($fields, [
			'personal_info_divider' => [
				'type' => 'divider',
			],
			'personal_info_section_header' => [
				'type' => 'section_header',
				'label' => 'Personal Info',
			],
			'headshot' => [
				'type' => 'image',
				'styles' => [
					'width' => '25%',
				],
				'crop' => [
					'dimensions' => [
						'height' => 800,
						'width' => 600,
					],
				],
			],
			'district' => [
				'type' => 'select',
				'label' => 'District/Affiliate',
				'select_options' => $this->get_district_options(),
				'styles' => [
					'width' => '25%',
				],
			],
			'airports' => [
				'type' => 'text',
				'label' => 'Airport(s)',
				'styles' => [
					'width' => '25%',
				],
			],
		]);

		if(current_user_can('administrator')) {
			$fields = array_merge($fields, [
				[
					'type' => 'tab',
					'label' => 'Admin Info',
					'id' => 'admin-info',
				],
				[
					'type' => 'section_header',
					'label' => 'Admin Info',
				],
				'user_account' => [ 
					'type' => 'user', 
					'label' => 'User Account', 
					'styles' => [
						'width' => '25%',
					],
					'add_new' => true,
					'add_new_fields' => [
						'first_name' => 'first_name',
						'last_name' => 'last_name',
						'email' => 'email',
					],
				],
			]);
		}

		return $this->encode_json(apply_filters(sprintf('%s/%s/fields', $this->theme_slug, $this->module_slug), $fields, $edit));
	}

	public function get_district_options() {
		$districts = [

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

		$options = [];
		foreach($districts as $district) {
			$options[strtolower($district)] = $district;
		}

		return $options;
	}

}

new Judges;
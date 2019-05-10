<?php

/**
 *	Class Admin
 */
class Karma_Admin {

	/**
	 *	Constructor
	 */
	public function __construct() {

		add_action('init', array($this, 'init'), 11); // after post type registration
		add_action('admin_menu', array($this, 'admin_menu'));
		add_filter('sanitize_file_name', array($this, 'sanitize_file_name'), 11);

		//add_filter('upload_mimes', array($this, 'mime_types'));
// 		add_filter('wp_editor_settings', array($this, 'customize_editor'));
// 		add_filter('register_post_type_args', array($this, 'customize_post_type'), 10, 2);

		// handle metaboxes
		// require(get_template_directory() . '/admin/metaboxes.php');

		// handle event cache
		// require(get_template_directory() . '/admin/admin-event.php');


	}


	/**
	 *	Init
	 */
	public function init() {

		add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));

		// do_action('post_gallery_box_register', array(
		// 	'id' => 'images',
		// 	'title' => 'Images',
		// 	'post_types' => array('project', 'page'),
		// 	'metabox' => true,
		// 	'context' => 'normal',
		// 	'button_name' => 'Ajouter...'
		// ));

//
//
// 		do_action('media_box_register', array(
// 			'id' => 'medias',
// 			'title' => 'Médias',
// 			'post_types' => array('project'),
// 			'context' => 'normal'
// 			//'button_name' => 'Ajouter...'
// 		));





		// unregister_taxonomy_for_object_type('post_tag', 'post');
// 		unregister_taxonomy_for_object_type('category', 'post');


// 		add_filter('manage_project_posts_columns', array($this, 'fill_project_table_header'));
// 		add_filter('manage_project_posts_custom_column', array($this, 'fill_project_table_content'), 10, 2);


		// add_post_type_support('page', 'thumbnail');

		remove_post_type_support( 'page', 'slug' );
		remove_post_type_support( 'page', 'author' );
		remove_post_type_support( 'page', 'custom-fields' );
		remove_post_type_support( 'page', 'comments' );
		remove_post_type_support( 'page', 'thumbnail' );


		// add_action('page_attributes_misc_attributes', array($this, 'page_attributes_misc_attributes'));

	}





	/**
	 * Allow mime type
	 * @hook 'upload_mimes'
	 */
	public function mime_types($mimes) {

//     $mime_types['dwg'] = 'application/acad'; // image/vnd.dwg / image/x-dwg
//     $mime_types['vw'] = 'application/vw';

		$mimes['svg'] = 'image/svg+xml';

    return $mimes;

	}


	/**
	 * Add subpage in menu
	 * Hook for 'admin_menu'
	 */
	public function customize_admin_menu() {

		if (!current_user_can('manage_options')) {

			remove_menu_page('edit.php');
			remove_menu_page( 'tools.php' );

		}

	}

	/**
	 * @filter 'wp_editor_settings'
	 */
	public function customize_editor($settings) {
		global $typenow;

// 		if ($typenow === 'spectacle') {
//
// 			$settings['media_buttons'] = false;
//
// 		}

		return $settings;
	}





	/**
	 * Enqueue styles
	 *
	 * Hook for 'admin_enqueue_scripts'
	 */
	function enqueue_styles() {

		wp_register_style('date-popup-styles', get_template_directory_uri().'/admin/css/date-popup.css');
		wp_enqueue_style('karma-admin-styles', get_template_directory_uri().'/admin/css/admin-style.css');
		// wp_enqueue_style('date-popup-styles', get_template_directory_uri().'/admin/css/date-popup.css');
		// wp_enqueue_style('children-table-styles', get_template_directory_uri().'/admin/css/children-table.css');

		wp_register_script('build', get_template_directory_uri() . '/js/utils/build.js', array(), $this->version, true);
		wp_register_script('calendar', get_template_directory_uri() . '/js/utils/calendar.js', array(), $this->version, true);
		wp_register_script('sortable', get_template_directory_uri() . '/js/utils/sortable.js', array(), $this->version, true);
		wp_register_script('date-popup', get_template_directory_uri() . '/admin/js/date-popup.js', array('build', 'calendar'), $this->version, true);
		// wp_register_script('children-table', get_template_directory_uri() . '/admin/js/children-table.js', array('build', 'calendar', 'sortable'), $this->version, true);


		// wp_enqueue_script('date-popup');
		// wp_enqueue_script('children-table');
	}

	/**
	 * Add Custom Option Page
	 *
	 * @hook admin_menu
	 */
	public function admin_menu() {

// 		add_theme_page(
// 			'Custom settings',
// 			'Custom settings',
// 			'edit_theme_options', // permission
// 			'custom_settings', // page slug
// 			array($this, 'options_page')
// 		);
//
//
// 		add_submenu_page(
// 			'edit.php?post_type=mediation',
// 			'Options médiation',
// 			'Options médiation',
// 			'edit_posts',
// 			'mediation_options',
// 			array($this, 'print_mediation_option_page')
// 		);


//
// 		add_submenu_page (
// 			'edit.php?post_type=spectacle',
// 			'Couleurs',
// 			'Couleurs',
// 			'edit_pages',
// 			'couleur_options',
// 			array($this, 'print_couleur_options')
// 		);


		// customize admin menu
		if (!current_user_can('manage_options')) {
			remove_menu_page('edit.php');
			remove_menu_page( 'tools.php' );
		}

	}



	/**
	 * Save custom settings
	 *
	 * @hook init
	 */
	public function save_settings() {

// 		if (isset($_POST[$this->settings_nonce]) && wp_verify_nonce($_POST[$this->settings_nonce], $this->settings_action) && current_user_can('edit_theme_options')) {
//
// 		}

	}



	/**
	 * @callback add_options_page()
	 */
	public function options_page() {

		include get_template_directory() . '/admin/include/options.php';

	}




	// /**
	//  * @hook 'page_attributes_misc_attributes'
	//  */
	// public function page_attributes_misc_attributes($post) {
	//
	// 	wp_nonce_field('custom_page_attributes-action', 'custom_page_attributes_nonce', false, true);
	//
	// 	// include get_template_directory() . '/admin/include/in-menu-checkbox.php';
	// 	include get_template_directory() . '/admin/include/sticky-checkbox.php';
	//
	// 	wp_nonce_field('footer_page-action', 'footer_page_nonce', false, true);
	// 	include get_template_directory() . '/admin/include/in-footer-checkbox.php';
	//
	// }



//
// 	/**
// 	 * @filter 'manage_project_posts_columns' ("manage_{$post_type}_posts_columns")
// 	 */
// 	public function fill_project_table_header($defaults) {
//
// 		unset($defaults['date']);
// 		unset($defaults['categories']);
// 		$defaults['locations'] = 'Locations';
// 		$defaults['categories'] = 'Catégories';
// 		$defaults['annee'] = 'Année';
//
// 		return $defaults;
//
// 	}
//
// 	/**
// 	 * @hook manage_project_posts_custom_column ("manage_{$post_type}_posts_custom_column")
// 	 */
// 	public function fill_project_table_content($column_name, $post_id) {
//
// 		if ($column_name === 'locations') {
//
// 			$locations = get_post_meta($post_id, 'locations');
//
// 			$locations = str_replace(array('slideshow', 'home', 'index'), array('Slideshow', 'Grille', 'Index'), $locations);
//
// 			echo implode('<br>', $locations);
//
// 		} else if ($column_name === 'annee') {
//
// 			echo get_post_meta($post_id, 'year', true);
//
// 		}
//
// 	}
//
//



	/**
	 * customize admin bar menu
	 *
	 * Hook for 'admin_bar_menu'
	 */
	public function customize_bar_menu( $wp_admin_bar ) {

		//$wp_admin_bar->remove_node( 'wp-logo' );
		//$wp_admin_bar->add_node(...);

	}

	/**
	 * Disable Auto P
	 *
	 * @hook 'wp_editor_settings'
	 */
	public function disable_autop($settings, $editor_id) {

		$settings['wpautop'] = false;

		return $settings;
	}


	public function tinymce_disable_autop($settings) {

//   	$settings["forced_root_block"] = false;
//     $settings["force_br_newlines"] = true;
//     $settings["force_p_newlines"] = false;
//     $settings["convert_newlines_to_brs"] = true;
//   	$settings['wpautop'] = false;

  	return $settings;

	}


	/**
	 * @filter 'sanitize_file_name'
	 */
	public function sanitize_file_name( $filename ) {

    return preg_replace('/[^a-zA-Z0-9._-]/', '', remove_accents($filename));

	}





	/**
	 * save searchable meta values in excerpt field
	 * Filter for 'wp_insert_post_data'
	 *
	 * @from 1.0
	 */
	public function insert_post($data, $postarr) {

// 		if (isset($_POST['searchable_nonce'], $this->searchable_meta) && wp_verify_nonce($_POST['searchable_nonce'], 'searchable-action')) {
//
// 			$values = array();
//
// 			foreach ($this->searchable_meta as $meta_key) {
//
// 				if (isset($postarr[$meta_key])) {
//
// 					$values = $postarr[$meta_key];
//
// 				}
//
// 			}
//
// 			$data['post_excerpt'] =  implode(' ', $values);
//
// 		}

		return $data;
	}

}

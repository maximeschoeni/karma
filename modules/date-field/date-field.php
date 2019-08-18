<?php

/**
 *	Class Event
 */
class Karma_date_field {

	/**
	 *	Constructor
	 */
	public function __construct() {

		if (is_admin()) {

			add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
			add_action('init', array($this, 'init'));
			add_action('karma_date_field', array($this, 'print_date_field'), 10, 2);

		}

	}

	/**
	 * Hook for 'admin_enqueue_scripts'
	 */
	public function enqueue_styles() {
		global $karma;

		wp_enqueue_style('date-field-styles', get_template_directory_uri().'/modules/date-field/date-field.css');
		wp_enqueue_script('date-field', get_template_directory_uri() . '/modules/date-field/date-field.js', array('build', 'calendar'), $karma->version, true);

	}

	/**
	 * @hook init
	 */
	public function init() {

		add_action('save_post', array($this, 'save'), 10, 3);

	}

	/**
	 * Save meta boxes
	 *
	 * @hook 'save_post'
	 */
	public function save($post_id, $post, $update) {

		if (current_user_can('edit_post', $post_id) && (!defined( 'DOING_AUTOSAVE' ) || !DOING_AUTOSAVE )) {

			$action = "karma_date_field-action";
			$nonce = "karma_date_field-nonce";

			if (isset($_REQUEST['karma_date_fields'], $_REQUEST[$nonce]) && wp_verify_nonce($_POST[$nonce], $action)) {

				$meta_keys = $_REQUEST['karma_date_fields'];

				foreach ($meta_keys as $meta_key) {

					$name = "karma_date_field-$meta_key";

					if (isset($_REQUEST[$name])) {

						$value = apply_filters('karma_field_date_save', $_REQUEST[$name], $post_id, $meta_key);

						update_post_meta($post_id, $meta_key, $value);

					}

				}

			}

		}

	}

	/**
	 * @hook 'karma_date_field'
	 */
 	public function print_date_field($post_id, $meta_key, $args = array()) {

 		include_once get_template_directory() . '/modules/date-field/include/date-field-nonce.php';

 		include get_template_directory() . '/modules/date-field/include/date-field-hidden.php';

 		$args['value'] = get_post_meta($post_id, $meta_key, true);

 		$args = apply_filters('karma_date_field_input', $args, $post_id, $meta_key);

		$name = "karma_date_field-$meta_key";

		include get_template_directory() . '/modules/date-field/include/date.php';

	}



}

new Karma_date_field;

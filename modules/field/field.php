<?php


Class Karma_Field {

	var $version = '0.1';

	/**
	 *	constructor
	 */
	public function __construct() {

		require_once get_template_directory() . '/modules/field/multilanguage.php';

		if (is_admin()) {

			add_action('karma_field', array($this, 'print_field'), 10, 4);
			add_action('init', array($this, 'init'));

		}

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

			$action = "karma_field-action";
			$nonce = "karma_field-nonce";

			if (isset($_REQUEST['karma-fields'], $_REQUEST[$nonce]) && wp_verify_nonce($_POST[$nonce], $action)) {

				$meta_keys = $_REQUEST['karma-fields'];

				foreach ($meta_keys as $meta_key) {

					$name = "karma_field-$meta_key";

					if (isset($_REQUEST[$name])) {

						$type = $_REQUEST['karma_field_type'][$meta_key];

						$value = apply_filters('karma_field_save', $_REQUEST[$name], $post_id, $meta_key, $type);

						update_post_meta($post_id, $meta_key, $value);

					}

				}

			}

		}

	}

	/**
	 * @hook 'karma_field'
	 */
	public function print_field($post_id, $meta_key, $type, $args = array()) {

		include_once get_template_directory() . '/modules/field/includes/field-nonce.php';

		include get_template_directory() . '/modules/field/includes/field-hidden.php';

		$args['value'] = get_post_meta($post_id, $meta_key, true);

		$args = apply_filters('karma_field_input', $args, $post_id, $meta_key, $type);

		include get_template_directory() . "/modules/field/includes/field-$type.php";

	}

}

if (is_admin()) new Karma_Field();

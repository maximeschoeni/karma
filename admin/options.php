<?php


class Karma_Options {

	/**
	 * options name
	 */
	var $option_name = 'karma';

	/**
	 *	options cache
	 */
	var $options;

	/**
	 *	nonce/action
	 */
	var $settings_nonce = 'karma_options_nonce';
	var $settings_action = 'karma_options_action';

	/**
	 *	Constructor
	 */
	public function __construct() {

		if (is_admin()) {

			add_action('admin_menu', array($this, 'admin_menu'));
			$this->save_options();

		}

	}

	/**
	 * @hook 'admin_menu'
	 */
	public function admin_menu() {

		add_options_page(
			'Theme Options',
			'Theme Options',
			'manage_options',
			'custom_settings',
			array($this, 'print_options')
		);

	}

	/**
	 * @callback add_options_page()
	 */
	public function print_options() {

		include get_template_directory() . '/admin/include/utils/options.php';

	}

	/**
	 * Save custom settings
	 *
	 * @hook init
	 */
	public function save_options() {

		if (isset($_POST[$this->settings_nonce]) && wp_verify_nonce($_POST[$this->settings_nonce], $this->settings_action) && current_user_can('edit_theme_options')) {

			// -> save options
			do_action('karma_save_options', $this);

			if (isset($_POST['_wp_http_referer'])) {

				wp_redirect($_POST['_wp_http_referer']);
				exit;

			} else {

				die('_wp_http_referer not set');

			}

		}

	}

	/**
	 *	get option
	 */
	public function get_option($name, $fallback = false) {

		if (!isset($this->options)) {

			$this->options = get_option($this->option_name);

		}

		if (isset($this->options[$name])) {

			return $this->options[$name];

		}

		return $fallback;
	}

	/**
	 *	get options
	 */
	public function get_options() {

		if (!isset($this->options)) {

			$this->options = get_option($this->option_name);

		}

		return $this->options;
	}

	/**
	 * Update option
	 */
	public function update_option($name, $value) {

		$this->options = $this->get_options();

		$this->options[$name] = $value;

		update_option($this->option_name, $this->options);

	}

}

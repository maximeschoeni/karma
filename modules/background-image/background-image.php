<?php


// $reflector = new \ReflectionClass('Karma_Background_Image');
// echo $reflector->getFileName();
// die();

Class Karma_Background_Image {

	var $version = '0.1';

	/**
	 *	constructor
	 */
	public function __construct() {

		if (!is_admin()) {

			add_action('init', array($this, 'init'));

		}

	}

	/**
	 * @hook init
	 */
	public function init() {

		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 10);

	}

	/**
	 * @hook 'wp_enqueue_scripts'
	 */
	public function enqueue_scripts() {

		wp_register_script('karma-background-image', get_template_directory_uri() . '/modules/background-image/js/background-image.js', array(), $this->version, true);

	}

}

new Karma_Background_Image();

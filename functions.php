<?php


class Karma {

	var $version = '000';

	/**
	 *	Karma_Options object
	 */
	var $options;

	/**
	 *	Constructor
	 */
	public function __construct() {

		add_action('after_setup_theme', array($this, 'setup'));
		add_filter('jpeg_quality', array($this, 'jpeg_quality'));
		add_filter('sanitize_file_name', array($this, 'sanitize_file_name'), 11);

		// add_action('init', array($this, 'register'));

		require_once get_template_directory() . '/admin/date.php';

		// require_once get_template_directory() . '/public/shortcode.php';
		// require_once get_template_directory() . '/admin/admin-page.php';

		require_once get_template_directory() . '/admin/options.php';
		$this->options = new Karma_Options;

		require_once get_template_directory() . '/admin/sublanguage.php';

		// require_once get_template_directory() . '/admin/post_type-project.php';


		if (is_admin()) {

			// require(get_template_directory() . '/admin/admin.php');
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 99);

		} else {

			// require(get_template_directory() . '/public/site.php');
			add_action('wp_enqueue_scripts', array($this, 'public_enqueue_scripts'), 99);

		}

	}

	/**
	 *	Theme Setup
	 */
	public function setup() {

		load_theme_textdomain( 'karma', get_template_directory() . '/languages' );

		// add_theme_support( 'post-thumbnails' );
		//
		// add_editor_style();
		//
		// register_nav_menus(array(
		// 	'main_menu' => 'Top Menu'
		// ));
		//
		// add_image_size( 'larger', 1536, 9999 );
		// add_image_size( 'big', 2400, 9999 );

// 		add_filter('register_post_type_args', array($this, 'register_post_type_args'), 10, 2);

	}

	/**
	 *	Register format taxonomies
	 */
	public function jpeg_quality($quality) {

		return 95;

	}



	/**
	 *	Print theme styles and scripts
	 */
	public function public_enqueue_scripts() {

		wp_register_script('tinyAnimate', get_template_directory_uri() . '/js/utils/TinyAnimate.js', array(), $this->version, true);
		wp_register_script('swipe', get_template_directory_uri() . '/js/utils/swipe.js', array(), $this->version, true);
		wp_register_script('media-player', get_template_directory_uri() . '/js/utils/media-player.js', array('tinyAnimate'), $this->version, true);
		wp_register_script('build', get_template_directory_uri() . '/js/utils/build.js', array(), $this->version, true);
		wp_register_script('ajax', get_template_directory_uri() . '/js/utils/ajax.js', array(), $this->version, true);
		wp_register_script('marquee', get_template_directory_uri() . '/js/utils/marquee.js', array(), $this->version, true);
		wp_register_script('popup', get_template_directory_uri() . '/js/utils/popup.js', array('tinyAnimate'), $this->version, true);
		wp_register_script('calendar', get_template_directory_uri() . '/js/utils/calendar.js', array(), $this->version, true);


		wp_register_script('grid-system', get_template_directory_uri() . '/js/utils/grid-system.js', array('tinyAnimate'), $this->version, true);
		wp_register_script('sticky', get_template_directory_uri() . '/js/utils/sticky.js', array(), $this->version, true);
// 		wp_enqueue_script('grid', get_template_directory_uri() . '/js/grid.js', array('grid-system'), $this->version, true);
// 		wp_enqueue_script('projects-grid', get_template_directory_uri() . '/js/projects.js', array('grid'), $this->version, true);
// 		wp_enqueue_script('project', get_template_directory_uri() . '/js/project.js', array('grid'), $this->version, true);
// 		wp_enqueue_script('image', get_template_directory_uri() . '/js/image.js', array('tinyAnimate'), $this->version, true);
		// wp_register_script('grid-slideshow', get_template_directory_uri() . '/js/grid-slideshow.js', array('media-player', 'swipe', 'build'), $this->version, true);

		// wp_enqueue_script('home', get_template_directory_uri() . '/js/home.js', array('grid-slideshow'), $this->version, true);
		// wp_enqueue_script('header', get_template_directory_uri() . '/js/header.js', array('popup', 'sticky', 'marquee'), $this->version, true);
		// wp_enqueue_script('single', get_template_directory_uri() . '/js/single.js', array('grid-slideshow'), $this->version, true);
		// wp_enqueue_script('bios', get_template_directory_uri() . '/js/bios.js', array('popup'), $this->version, true);
		// wp_enqueue_script('agenda', get_template_directory_uri() . '/js/agenda.js', array('popup', 'ajax', 'build', 'calendar', 'grid-slideshow'), $this->version, true);
		// wp_enqueue_script('intro', get_template_directory_uri() . '/js/intro.js', array('media-player'), $this->version, true);

		wp_register_script('cookies', get_template_directory_uri() . '/js/utils/cookies.js', array('media-player'), $this->version, false);


	}


	/**
	 * Enqueue styles
	 *
	 * Hook for 'admin_enqueue_scripts'
	 */
	function admin_enqueue_scripts() {

		wp_register_style('date-popup-styles', get_template_directory_uri().'/admin/css/date-popup.css');
		// wp_enqueue_style('karma-admin-styles', get_template_directory_uri().'/admin/css/admin-style.css');
		// wp_enqueue_style('date-popup-styles', get_template_directory_uri().'/admin/css/date-popup.css');
		// wp_enqueue_style('children-table-styles', get_template_directory_uri().'/admin/css/children-table.css');

		wp_register_script('build', get_template_directory_uri() . '/js/utils/build.js', array(), $this->version, true);
		wp_register_script('calendar', get_template_directory_uri() . '/js/utils/calendar.js', array(), $this->version, true);
		wp_register_script('sortable', get_template_directory_uri() . '/js/utils/sortable.js', array(), $this->version, true);
		wp_register_script('date-popup', get_template_directory_uri() . '/admin/js/date-popup.js', array('build', 'calendar'), $this->version, true);
		wp_register_script('children-table', get_template_directory_uri() . '/admin/js/children-table.js', array('build', 'calendar', 'sortable'), $this->version, true);


		// wp_enqueue_script('date-popup');
		// wp_enqueue_script('children-table');
	}

	/**
	 * @filter 'sanitize_file_name'
	 */
	public function sanitize_file_name( $filename ) {

		return preg_replace('/[^a-zA-Z0-9._-]/', '', remove_accents($filename));

	}



}

global $karma;

$karma = new Karma;

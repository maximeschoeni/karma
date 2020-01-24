<?php

/**
 *	Class Karma_Dependency_Expirer
 */
class Karma_Clusters_UI {

	/**
	 *	Constructor
	 */
	function __construct() {

		add_action('admin_notices', array($this, 'print_notice'));

		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

	}

	/**
	 * @hook 'admin_enqueue_scripts'
	 */
	function admin_enqueue_scripts( $hook ) {
		global $karma;

	  wp_enqueue_script('clusters-update', get_template_directory_uri() . '/modules/clusters/js/clusters-update.js', array('ajax'), $karma->version, true);

		wp_localize_script('clusters-update', 'Clusters', array(
			'ajax_url' => admin_url('admin-ajax.php')
		));

	}


	/**
	 * @hook 'wp_loaded'
	 */
	function print_notice() {
		global $karma;

		$dependencies = $karma->options->get_option('expired_clusters', array());

		if ($dependencies) {

			include get_template_directory() . '/modules/clusters/include/notice.php';

		}

	}


}

new Karma_Clusters_UI();

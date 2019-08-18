<?php

/**
 *	Class Karma_Task_Manager
 */
class Karma_Task_Manager {

	/**
	 *	Constructor
	 */
	function __construct() {

		add_action('admin_notices', array($this, 'print_notice'), 30);

		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

		add_action('wp_ajax_karma_get_task', array($this, 'ajax_get_task'));

	}

	/**
	 * @hook 'admin_enqueue_scripts'
	 */
	function admin_enqueue_scripts( $hook ) {
		global $karma;

	  wp_enqueue_script('task-manager', get_template_directory_uri() . '/modules/task-manager/js/task-manager.js', array('ajax', 'custom-dispatcher'), $karma->version, true);

		wp_localize_script('task-manager', 'KarmaTaskManager', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'is_admin' => current_user_can('manage_options') ? 1 : 0
		));

	}

	/**
	 * @ajax 'karma_get_task'
	 */
	public function ajax_get_task() {
		global $karma;

		$tasks = apply_filters('karma_task', array());

		$output = array();

		if ($tasks) {

			$output = $tasks[0];

		}

		echo json_encode($output);

		exit;

	}


	/**
	 * @hook 'wp_loaded'
	 */
	function print_notice() {
		global $karma;

		// $tasks = apply_filters('karma_task', array());
		//
		// if ($tasks) {
		//
		// 	include get_template_directory() . '/modules/task-manager/include/notice.php';
		//
		// }

		include get_template_directory() . '/modules/task-manager/include/notice.php';

	}


}

new Karma_Task_Manager();

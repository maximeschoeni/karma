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

		add_action('wp_ajax_karma_task', array($this, 'ajax_task'));

	}

	/**
	 * @hook 'admin_enqueue_scripts'
	 */
	function admin_enqueue_scripts( $hook ) {
		global $karma;

	  wp_enqueue_script('task-manager', get_template_directory_uri() . '/modules/task-manager/js/task-manager.js', array('ajax'), $karma->version, true);

		wp_localize_script('task-manager', 'KarmaTaskManager', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'is_admin' => current_user_can('manage_options') ? 1 : 0
		));

	}

	/**
	 * @ajax 'karma_task'
	 */
	public function ajax_task() {
		global $karma;

		// $output = array();

		$output = apply_filters('karma_task', array());

		// if ($task) {
		//
		// 	$output = $task;
		//
		// }

		// if (!$output) {
		//
		// 	$output['done'] = true;
		// 	$output['notice'] = 'Done.';
		// }

		echo json_encode($output);

		exit;

	}


	/**
	 * @hook 'wp_loaded'
	 */
	function print_notice() {
		global $karma, $dependencies;

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

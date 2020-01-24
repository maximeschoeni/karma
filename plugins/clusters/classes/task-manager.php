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
		global $karma_clusters;

		wp_register_script('ajax', $karma_clusters->url . '/js/ajax.js', array(), false, true);
	  wp_enqueue_script('task-manager', $karma_clusters->url . '/js/task-manager.js', array('ajax'), false, true);

		wp_localize_script('task-manager', 'KarmaTaskManager', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'is_admin' => current_user_can('manage_options') ? 1 : 0
		));

	}

	/**
	 * @ajax 'karma_task'
	 */
	public function ajax_task() {

		$output = apply_filters('karma_task', array());

		echo json_encode($output);

		exit;

	}


	/**
	 * @hook 'wp_loaded'
	 */
	function print_notice() {
		global $dependencies;

		include dirname(dirname(__FILE__)) . '/include/notice.php';

	}


}

new Karma_Task_Manager();

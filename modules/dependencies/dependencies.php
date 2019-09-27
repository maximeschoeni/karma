<?php


Class Karma_Cache_Dependencies {

	public $table_name = 'cache_dep';

	public $dependencies = array();

	/**
	 * constructor
	 */
	public function __construct() {



		// add_action('karma_cache_dependency_add_id', array($this, 'add_id'), 10, 6);
		// add_action('karma_cache_dependency_add_ids', array($this, 'add_ids'), 10, 6);
		// add_action('karma_cache_dependency_add_type', array($this, 'add_type'), 10, 5);
		//
		// add_action('karma_cache_dependency_init', array($this, 'init'), 10, 5);
		// add_action('karma_cache_dependency_save', array($this, 'save'), 10, 5);

		add_action('save_post', array($this, 'save_post'), 10, 3);
		add_action('before_delete_post', array($this, 'delete_post'), 10);
		add_action('edit_term', array($this, 'edit_term'), 10, 3);
		add_action('create_term', array($this, 'edit_term'), 10, 3);
		add_action('pre_delete_term', array($this, 'delete_term'), 10, 2);

		add_action('wp_ajax_karma_cache_clear_dependencies', array($this, 'ajax_clear'));

		add_action('karma_dependency_delete_target', array($this, 'delete_target'));

		if (is_admin()) {

			add_action('init', array($this, 'create_dependency_tables'), 9);

			add_action( 'admin_bar_menu', array($this, 'add_toolbar_button'), 999);

		}

	}


	/**
	 * @hook 'init'
	 */
	function create_dependency_tables() {

		if (is_admin()) {

			require_once get_template_directory() . '/admin/table.php';

			Karma_Table::create($this->table_name, "
				id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				target varchar(50) NOT NULL,
				target_id BIGINT(20) NOT NULL,
				object varchar(10) NOT NULL,
				object_id BIGINT(20) NOT NULL,
				type varchar(50) NOT NULL,
				priority smallint(1) NOT NULL
			", '001');

		}

	}

	/**
	 * create_instance
	 */
	public function create_instance($target, $target_id) {

		require_once get_template_directory() . '/modules/dependencies/instance.php';

		return new Karma_Cache_Dependencies_Instance($this, $target, $target_id);

	}

	// /**
	//  * @hook karma_cache_dependency_add_id
	//  */
	// public function add_id($target, $target_id, $object, $type, $id, $priority = 1) {
	//
	// 	$this->dependencies[$target][$target_id][$object][$type][$id] = $priority;
	//
	// }
	//
	// /**
	//  * @hook karma_cache_dependency_add_ids
	//  */
	// public function add_ids($target, $target_id, $object, $type, $ids, $priority = 1) {
	//
	// 	foreach ($ids as $id) {
	//
	// 		$this->add_id($target, $target_id, $object, $type, $id, $priority);
	//
	// 	}
	//
	// }
	//
	// /**
	//  * @hook karma_cache_dependency_add_type
	//  */
	// public function add_type($target, $target_id, $object, $type, $priority = 1) {
	//
	// 	$this->dependencies[$target][$target_id][$object][$type][0] = $priority;
	//
	// }
	//
	// /**
	//  * @hook 'karma_cache_dependency_save'
	//  */
	// public function init() {
	//
	// 	$this->dependencies = [];
	//
	// }
	//
	// /**
	//  * @hook 'karma_cache_dependency_save'
	//  */
	// public function save() {
	// 	global $wpdb;
	//
	// 	$dependency_table = $wpdb->prefix.$this->dependency_table;
	//
	// 	foreach ($this->dependencies as $target => $target_dependency) {
	//
	// 		foreach ($target_dependency as $target_id => $target_id_dependency) {
	//
	// 			$dependency_ids = array();
	//
	// 			foreach ($target_id_dependency as $object => $object_dependency) {
	//
	// 				foreach ($object_dependency as $type => $type_dependency) {
	//
	// 					foreach ($type_dependency as $id => $priority) {
	//
	// 						$dependency_id = $wpdb->get_var($wpdb->prepare(
	// 							"SELECT id FROM $dependency_table WHERE target = %s AND target_id = %d AND object = %s AND object_id = %d AND type = %s AND priority = %d",
	// 							$target,
	// 							$target_id,
	// 							$object,
	// 							$id,
	// 							$type,
	// 							$priority
	// 						));
	//
	// 						if (!$dependency_id) {
	//
	// 							$wpdb->insert($dependency_table, array(
	// 								'target' => $target,
	// 								'target_id' => $target_id,
	// 								'object' => $object,
	// 								'object_id' => $id,
	// 								'type' => $type,
	// 								'priority' => $priority
	// 							), array(
	// 								'%s',
	// 								'%d',
	// 								'%s',
	// 								'%d',
	// 								'%s',
	// 								'%d'
	// 							));
	//
	// 							$dependency_id = $wpdb->insert_id;
	//
	// 						}
	//
	// 						$dependency_ids[] = $dependency_id;
	//
	// 					}
	//
	// 				}
	//
	// 			}
	//
	// 			if ($dependency_ids) {
	//
	// 				$sql = implode(',', array_map('intval', $dependency_ids));
	//
	// 				$wpdb->query($wpdb->prepare(
	// 					"DELETE FROM $cluster_table WHERE target = %s AND target_id = %d AND id NOT IN ($sql)",
	// 					$target,
	// 					$target_id
	// 				));
	//
	// 			} else {
	//
	// 				$wpdb->query($wpdb->prepare(
	// 					"DELETE FROM $cluster_table WHERE target = %s AND target_id = %d",
	// 					$target,
	// 					$target_id
	// 				));
	//
	// 			}
	//
	// 		}
	//
	// 	}
	//
	// }

	/**
	 * @hook 'save_post'
	 */
	function save_post($post_id, $post, $update) {

		$this->update_object('post', $post->post_type, $post_id);

	}

	/**
	 * @hook 'before_delete_post'
	 */
	function delete_post($post_id) {

		$post = get_post($post_id);

		$this->delete_object('post', $post->post_type, $post_id);

	}

	/**
	 * @hook 'edit_term', 'create_term'
	 */
	function edit_term($term_id, $tt_id, $taxonomy) {

		$this->update_object('term', $taxonomy, $term_id);

	}

	/**
	 * @hook 'pre_delete_term'
	 */
	function delete_term($term, $taxonomy) {

		$this->delete_object('term', $taxonomy, $term->term_id);

	}


	/**
	 * @hook 'karma_cache_create_object'
	 */
	function create_object($object, $type, $id = 0) {

		$this->update_object($object, $type);

	}

	/**
	 * @hook 'karma_cache_update_object'
	 */
	function update_object($object, $type, $id = 0) {
		global $wpdb;

		$dependency_table = $wpdb->prefix.$this->table_name;

		$dependencies = $wpdb->get_results($wpdb->prepare(
			"SELECT target, target_id, priority FROM $dependency_table
			WHERE object = %s AND type = %s AND (object_id = 0 OR object_id = %d)
			ORDER BY priority DESC",
			// ORDER BY field(target, 'cluster', 'patch', 'html') ASC, priority DESC
			$object,
			$type,
			$id
		));

		foreach ($dependencies as $dependency) {

			do_action("karma_cache_{$dependency->target}_dependency_updated", $dependency);

		}

	}

	/**
	 * @hook 'karma_cache_delete_object'
	 */
	function delete_object($object, $type, $id) {
		global $wpdb;

		$this->update_object($object, $type, $id);

		$dependency_table = $wpdb->prefix.$this->table_name;

		$wpdb->delete($dependency_table, array(
			'object' => $object,
			'type' => $type,
			'object_id' => $id
		), array(
			'%s',
			'%s',
			'%d'
		));

		// $wpdb->query($wpdb->prepare(
		// 	"DELETE FROM $dependency_table WHERE object = %s AND type = %s AND object_id = %d",
		// 	$object,
		// 	$type,
		// 	$id
		// ));

	}


	/**
	 * @ajax 'karma_cache_clear_dependencies'
	 */
	public function ajax_clear() {
		global $karma, $wpdb;

		$output = array();

		$dependency_table = $wpdb->prefix.$this->table_name;

		$wpdb->query("truncate $dependency_table");

		$output['flush'] = 'done';

		echo json_encode($output);
		exit;

	}

	/**
	 * @callbak 'admin_bar_menu'
	 */
	public function add_toolbar_button( $wp_admin_bar ) {
		global $karma;

		if (current_user_can('manage_options')) {

			$wp_admin_bar->add_node(array(
				'id'    => 'clear-dependencies',
				'title' => 'Clear Dependencies',
				'href'  => '#',
				'meta'  => array(
					'onclick' => 'ajaxGet('.admin_url('admin-ajax.php').',{action:"karma_cache_clear_dependencies"},console.log);event.preventDefault();'
				)
			));

		}

	}



	/**
	 * @hook 'karma_dependency_delete_target'
	 */
	public function delete_target($target) {
		global $wpdb;

		$dependency_table = $wpdb->prefix.$this->table_name;

		$wpdb->delete($dependency_table, array(
			'target' => $target
		), array(
			'%s'
		));

	}

}

global $karma_dependencies;
$karma_dependencies = new Karma_Cache_Dependencies();

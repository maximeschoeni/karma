<?php
/**
 *	Class Karma_Patches
 */
class Karma_Patches {

	// var $dependencies = array();
	var $post_types = array();
	// var $dependency_table = 'clusters';
	var $table_name = 'cache_patches';
	var $patches_path = WP_CONTENT_DIR.'/patches';
	var $path = 'patches';



	// var $patches_url = WP_CONTENT_URL.'/patches';

	/**
	 *	Constructor
	 */
	public function __construct() {

		// require_once get_template_directory() . '/modules/patches/multilanguage.php';
		require_once get_template_directory() . '/modules/task-manager/task-manager.php';
		require_once get_template_directory() . '/modules/files/files.php';

		$this->files = new Karma_Files();

		add_action('wp_ajax_karma_update_patches', array($this, 'ajax_update_all_patches'));
		add_action('wp_ajax_karma_delete_patches', array($this, 'ajax_delete_all_patches'));
		add_action('wp_ajax_karma_toggle_patches', array($this, 'ajax_toggle_patches'));

		add_filter('karma_task', array($this, 'add_task'));
		add_action('karma_cache_patch_dependency_updated', array($this, 'dependency_updated'));

		add_action('karma_cache_patch', array($this, 'print_patch'), 10, 3);

		add_filter('karma_cache_html_output', array($this, 'filter_dynamic_patches'), 10, 2);

		if (is_admin()) {

			add_action('init', array($this, 'create_dependency_tables'));
			add_action('karma_task_notice', array($this, 'task_notice'));
			add_action('admin_bar_menu', array($this, 'add_toolbar_button'), 999);

		}

	}

	/**
	 * @hook 'init'
	 */
	function create_dependency_tables() {

		if (is_admin()) {

			require_once get_template_directory() . '/admin/table.php';

			Karma_Table::create($this->table_name, "
				id BIGINT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				request VARCHAR(255) NOT NULL,
				path VARCHAR(255) NOT NULL,
				active SMALLINT(1) NOT NULL,
				status SMALLINT(1) NOT NULL
			", '001');

		}

	}

	/**
	 * @filter 'karma_cache_html_output'
	 */
	public function filter_dynamic_patches($content, $karma_cache) {

		preg_match_all('/<!-- patch:(\S*) -->/', $content, $matches);

		foreach ($matches[0] as $i => $tag) {

			$end_tag = '<!-- patch -->';
			$path = $matches[1][$i];

			$start = strpos($content, $tag);
			$end = strpos($content, $end_tag, $start+strlen($tag));

			$patch_content = substr($content, $start, $end-$start+strlen($end_tag));

			$depth = substr_count($karma_cache->sitepage->path, '/')+($karma_cache->sitepage->path ? 1 : 0)+1;

			$file = str_repeat("../", $depth).'wp-content'.$path;

			$content = str_replace($patch_content, "<?php include '$file'; ?>", $content);

		}

		return $content;
	}

	/**
	 * @hook 'karma_cache_patch'
	 */
	public function print_patch($request, $name, $no_cache = false) {
		global $wpdb, $karma;

		$include = get_stylesheet_directory().'/'.$request;

		$path = apply_filters('karma_patch_path', $name);

		if (file_exists(WP_CONTENT_DIR.'/'.$this->path.'/'.$path.'/patch.php')) {

			$include = WP_CONTENT_DIR.'/'.$this->path.'/'.$path.'/patch.php';

		} else if ($karma->options->get_option('patches_active') && !$no_cache) {

			$table = $wpdb->prefix.$this->table_name;

			$patch = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM $table WHERE path = %s",
				$path
			));

			if (!$patch) {

				$patch = $this->create_patch($request, $path);

			}

			if ($patch->active) {

				$this->rebuild_patch($patch);

				$include = WP_CONTENT_DIR.'/'.$this->path.'/'.$patch->path.'/patch.php';

			}

		}

		echo '<!-- patch:'.str_replace(WP_CONTENT_DIR, '', $include).' -->';

		include $include;

		echo '<!-- patch -->';

	}

	// /**
	//  * @hook 'karma_cache_patch'
	//  */
	// public function print_patch($request, $name, $no_cache = false) {
	// 	global $wpdb, $karma, $karma_cache;
	//
	// 	if ($karma->options->get_option('patches_active') && !$no_cache) {
	//
	// 		$path = apply_filters('karma_patch_path', $name);
	//
	// 		echo '<!-- patch:'.$path.' -->';
	//
	// 		if ($no_cache) {
	//
	// 			include get_template_directory().'/'.$request;
	//
	// 		} else if (file_exists($this->patches_path.'/'.$path.'/patch.php')) {
	//
	// 			include $this->patches_path.'/'.$path.'/patch.php';
	//
	// 		} else {
	//
	// 			$table = $wpdb->prefix.$this->table_name;
	//
	// 			$patch = $wpdb->get_row($wpdb->prepare(
	// 				"SELECT * FROM $table WHERE path = %s",
	// 				$path
	// 			));
	//
	// 			if (!$patch) {
	//
	// 				$patch = $patch->create_patch($request, $path);
	//
	// 				$this->rebuild_patch($patch);
	//
	// 			}
	//
	// 			if ($patch->active) {
	//
	// 				include $this->patches_path.'/'.$patch->path.'/patch.php';
	//
	// 			} else {
	//
	// 				include get_template_directory().'/'.$request;
	//
	// 			}
	//
	// 		}
	//
	// 	} else {
	//
	// 		include get_template_directory().'/'.$request;
	//
	// 	}
	//
	// 	echo '<!-- patch -->';
	//
	// 	//
	// 	// 	include $this->patches_path.'/'.$path.'/patch.php';
	// 	//
	// 	//
	// 	// 	if (!$no_cache) {
	// 	//
	// 	//
	// 	//
	// 	//
	// 	//
	// 	// 		if ($patch->active) {
	// 	//
	// 	// 			$include_file = "{$this->patches_path}/{$patch->path}/patch.php";
	// 	//
	// 	// 			// do_action('karma_cache_build_patch', $patch, );
	// 	//
	// 	// 		} else {
	// 	//
	// 	// 			$include_file = get_template_directory().'/'.$request;
	// 	//
	// 	// 		}
	// 	//
	// 	// 	} else {
	// 	//
	// 	// 		$include_file = get_template_directory().'/'.$request;
	// 	//
	// 	// 	}
	// 	//
	// 		// echo "\<?php include '$include_file'; ?\>";
	// 	//
	// 	// } else {
	// 	//
	// 	// 	if (isset($karma_cache) && isset($karma_cache->dependency_instance)) {
	// 	//
	// 	// 		add_action('karma_patch_add_dependency_id', array($karma_cache->dependency_instance, 'add_id'), 10, 4);
	// 	// 		add_action('karma_patch_add_dependency_ids', array($karma_cache->dependency_instance, 'add_ids'), 10, 4);
	// 	// 		add_action('karma_patch_add_dependency_type', array($karma_cache->dependency_instance, 'add_type'), 10, 3);
	// 	//
	// 	// 	}
	// 	//
	// 	// 	include get_template_directory().'/'.$request;
	// 	//
	// 	// }
	//
	// }

	/**
	 * rebuild_patch
	 */
	public function rebuild_patch($patch) {
		global $karma_dependencies;

		$dependency_instance = $karma_dependencies->create_instance('patch', $patch->id);

		add_action('karma_patch_add_dependency_id', array($dependency_instance, 'add_id'), 10, 4);
		add_action('karma_patch_add_dependency_ids', array($dependency_instance, 'add_ids'), 10, 4);
		add_action('karma_patch_add_dependency_type', array($dependency_instance, 'add_type'), 10, 3);

		ob_start();

		include get_stylesheet_directory().'/'.$patch->request;

		$content = ob_get_clean();

		remove_action('karma_patch_add_dependency_id', array($dependency_instance, 'add_id'), 10);
		remove_action('karma_patch_add_dependency_ids', array($dependency_instance, 'add_ids'), 10);
		remove_action('karma_patch_add_dependency_type', array($dependency_instance, 'add_type'), 10);

		$dependency_instance->save();

		$this->files->write_file($this->patches_path.'/'.$patch->path, 'patch.php', $content);

	}

	/**
	 * create_patch
	 */
	public function create_patch($request, $path) {
		global $wpdb;

		$table = $wpdb->prefix.$this->table_name;

		$wpdb->insert($table, array(
			'path' => $path,
			'request' => $request,
			'active' => 1,
			'status' => 0
		), array(
			'%s',
			'%s',
			'%d',
			'%d'
		));

		$patch = new stdClass();
		$patch->id = $wpdb->insert_id;
		$patch->path = $path; //(string) $post_id;
		$patch->active = 1;
		$patch->request = $request; //"p=$post_id";

		return $patch;
	}



	/**
	 * @filter 'karma_task'
	 */
	public function add_task($task) {
		global $wpdb, $karma;

		if (empty($task) && $karma->options->get_option('patches_active')) {

			$table = $wpdb->prefix.$this->table_name;

			$outdated_patch = $wpdb->get_row("SELECT * FROM $table WHERE status > 0 ORDER BY status DESC LIMIT 1");

			if ($outdated_patch) {

				$this->rebuild_patch($outdated_patch);

				$task['action'] = 'patch updated';
				$task['patch'] = $outdated_patch;
				$task['notice'] = 'updating...';

				$wpdb->query($wpdb->prepare(
					"UPDATE $table SET status = 0 WHERE id = %d",
					$outdated_patch->id
				));

			}

		}

		return $task;
	}

	/**
	 * @hook "karma_cache_{$dependency->target}_dependency_updated"
	 */
	public function dependency_updated($dependency) {
		global $wpdb, $karma;

		if ($karma->options->get_option('patches_active')) {

			$table = $wpdb->prefix.$this->table_name;

			$wpdb->query($wpdb->prepare(
				"UPDATE $table SET status = GREATEST(status, %d) WHERE id = %d",
				$dependency->priority,
				$dependency->target_id
			));

		}

	}



	/**
	 * @ajax 'karma_delete_patches'
	 */
	public function ajax_delete_all_patches() {
		global $wpdb;

		$table = $wpdb->prefix.$this->table_name;

		$wpdb->query("truncate $table");

		do_action('karma_dependency_delete_target', 'patch');

		$this->files->remove($this->patches_path);

	}




	/**
	 * @ajax 'karma_update_clusters'
	 */
	public function ajax_update_all_patches() {
		global $wpdb;

		$table = $wpdb->prefix.$this->table_name;

		$wpdb->query($wpdb->prepare(
			"UPDATE $table SET status = %d",
			1
		));

		$num_task = $wpdb->get_var("SELECT count(id) AS num FROM $table");

		$output['notice'] = "Updating patches ($num_task) ";

		echo json_encode($output);
		exit;

	}

	/**
	 * @ajax 'karma_toggle_patch'
	 */
	public function ajax_toggle_patch() {
		global $wpdb;

		if (isset($_GET['id'])) {

			$patch_id = intval($_GET['id']);

			$patch = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM $table WHERE id = %d",
				$patch_id
			));

			if ($patch) {

				$active = 1 - $patch->active;

				$wpdb->query($wpdb->prepare(
					"UPDATE $table SET active = %d",
					$active
				));

				if ($active) {

					$this->rebuild_patch($patch);

					$output['notice'] = "Patch ($patch->path) active";

				} else {

					$this->files->remove($this->patches_path.'/'.$patch->path.'/patch.php');

					$output['notice'] = "Patch ($patch->path) inactive";

				}

			} else {

				trigger_error("patch not exist: $patch_id");

			}

		} else {

			trigger_error("id not set");

		}

		echo json_encode($output);
		exit;

	}

	/**
	 * @ajax 'karma_toggle_patch'
	 */
	public function ajax_toggle_patches() {
		global $wpdb, $karma;

		$output = array();

		if ($karma->options->get_option('patches_active')) {

			$karma->options->update_option('patches_active', '');

			$output['title'] = 'Patches (disabled)';
			$output['label'] = 'Activate Patches';
			$output['notice'] = "Deactivate Patches. ";
			$output['action'] = 'deactivate patches';

			$this->files->remove($this->patches_path);

		} else {

			$karma->options->update_option('patches_active', '1');

			$table = $wpdb->prefix.$this->table_name;

			$wpdb->query($wpdb->prepare(
				"UPDATE $table SET status = %d",
				1
			));

			$num_task = $wpdb->get_var("SELECT count(id) AS num FROM $table");

			$output['title'] = 'Patches (enabled)';
			$output['label'] = 'Deactivate Patches';
			$output['notice'] = "Activating Patches ($num_task). ";
			$output['action'] = 'rebuild patches';

		}

		echo json_encode($output);
		exit;
	}

	/**
	 * @callbak 'admin_bar_menu'
	 */
	public function add_toolbar_button( $wp_admin_bar ) {
		global $wpdb, $karma;

		if (current_user_can('manage_options')) {

			$patches_active = $karma->options->get_option('patches_active');

			$wp_admin_bar->add_node(array(
				'id'    => 'patches-group',
				'title' => 'Patches ('.($patches_active ? 'enabled' : 'disabled').')'
			));

			$table = $wpdb->prefix.$this->table_name;

			$patches = $wpdb->get_results("SELECT * FROM $table ORDER BY path");

			if ($patches) {

				$wp_admin_bar->add_node(array(
					'id'    => 'patches-list',
					'parent'    => 'patches-group',
					'title' => 'Patches'
				));

				foreach ($patches as $patch) {

					$wp_admin_bar->add_node(array(
						'id'    => 'update-patch-'.$patch->id,
						'parent' => 'patches-list',
						'title' => $patch->path,
						'href'  => '#',
						'meta'  => array(
							// 'onclick' => 'ajaxPost("'.admin_url('admin-ajax.php').'", {action: "karma_update_clusters"}, function(results) {KarmaTaskManager.update(results.notice);});event.preventDefault();'
							'onclick' => ''
						)
					));

				}

			}

			$wp_admin_bar->add_node(array(
				'id'    => 'update-patches',
				'parent' => 'patches-group',
				'title' => 'Update All Patches',
				'href'  => '#',
				'meta'  => array(
					// 'onclick' => 'ajaxPost("'.admin_url('admin-ajax.php').'", {action: "karma_update_clusters"}, function(results) {KarmaTaskManager.update(results.notice);});event.preventDefault();'
					'onclick' => 'KarmaTaskManager&&KarmaTaskManager.addTask("karma_update_patches",this);event.preventDefault()'
				)
			));

			$wp_admin_bar->add_node(array(
				'id'    => 'delete-patches',
				'parent' => 'patches-group',
				'title' => 'Delete all Patches',
				'href'  => '#',
				'meta'  => array(
					'onclick' => 'KarmaTaskManager&&KarmaTaskManager.addTask("karma_delete_patches",this);event.preventDefault()'
				)
			));

			$wp_admin_bar->add_node(array(
				'id'    => 'toggle-patches',
				'title' => $patches_active ? 'Deactivate Patches' : 'Activate Patches',
				'parent' => 'patches-group',
				'href'  => '#',
				'meta'  => array(
					'onclick' => 'KarmaTaskManager&&KarmaTaskManager.addTask("karma_toggle_patches",this,function(results){this.innerHTML=results.label;this.parentNode.parentNode.parentNode.previousSibling.innerHTML=results.title});event.preventDefault()'
				)
			));

		}

	}



	/**
	 * @filter 'karma_task_notice'
	 */
	public function task_notice($tasks) {
		global $wpdb;

		$table = $wpdb->prefix.$this->table_name;

		$num_task = $wpdb->get_var("SELECT count(id) AS num FROM $table WHERE status > 0");

		if ($num_task) {

			$tasks[] = "Updating Patches ($num_task). ";

		}

		return $tasks;
	}



}

global $karma_patches;
$karma_patches = new Karma_Patches;

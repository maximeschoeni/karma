<?php

/**
 *	Class Karma_Clusters
 */
class Karma_Clusters {

	// var $dependencies = array();
	var $post_types = array();
	// var $dependency_table = 'clusters';
	var $table_name = 'cache_clusters';
	var $cluster_path = WP_CONTENT_DIR.'/clusters';
	var $cluster_url = WP_CONTENT_URL.'/clusters';

	/**
	 *	Constructor
	 */
	public function __construct() {

		require_once get_template_directory() . '/modules/clusters/multilanguage.php';
		require_once get_template_directory() . '/modules/task-manager/task-manager.php';
		require_once get_template_directory() . '/modules/files/files.php';

		$this->files = new Karma_Files();

		add_action('wp_ajax_karma_update_clusters', array($this, 'ajax_update_clusters'));
		add_action('wp_ajax_karma_create_clusters', array($this, 'ajax_create_clusters'));
		add_action('wp_ajax_karma_delete_clusters', array($this, 'ajax_delete_clusters'));
		add_action('wp_ajax_karma_toggle_clusters', array($this, 'ajax_toggle_clusters'));

		add_action('wp_ajax_get_cluster', array($this, 'ajax_get_cluster'));
		add_action('wp_ajax_nopriv_get_cluster', array($this, 'ajax_get_cluster'));

		add_filter('karma_task', array($this, 'add_task'));
		add_action('karma_cache_cluster_dependency_updated', array($this, 'dependency_updated'));

		add_action('save_post', array($this, 'save_post'), 10, 3);
		add_action('before_delete_post', array($this, 'delete_post'), 99);

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
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				request varchar(255) NOT NULL,
				path varchar(255) NOT NULL,
				post_type varchar(255) NOT NULL,
				status smallint(1) NOT NULL
			", '002');

		}

	}


	/**
	 * register cluster for post type
	 */
	public function register($post_type, $callback) {

		$this->post_types[$post_type] = $callback;

	}

	/**
	 * create_cluster
	 */
	public function create_cluster($request, $path, $post_type) {
		global $wpdb;

		$cluster_table = $wpdb->prefix.$this->table_name;

		$wpdb->insert($cluster_table, array(
			'path' => $path,
			'request' => $request,
			'post_type' => $post_type,
			'status' => 100
		), array(
			'%s',
			'%s',
			'%s',
			'%d'
		));

		$cluster_row = new stdClass();
		$cluster_row->id = $wpdb->insert_id;
		$cluster_row->path = $path; //(string) $post_id;
		$cluster_row->request = $request; //"p=$post_id";
		$cluster_row->post_type = $post_type;

		return $cluster_row;
	}



	/**
	 * update_cluster
	 */
	public function update_cluster($cluster_row) {
		global $karma_dependencies, $wpdb;

		$query = new WP_Query($cluster_row->request);

		if ($query->have_posts()) {

			while ($query->have_posts()) {

				$query->the_post();

				$post = $query->post;

				if (isset($this->post_types[$post->post_type]) && is_callable($this->post_types[$post->post_type])) {

					$dependency_instance = $karma_dependencies->create_instance('cluster', $cluster_row->id);

					// $dependency_instance->add_id('post', $post->post_type, $post->ID, 100);

					$cluster = new stdClass();

					call_user_func($this->post_types[$post->post_type], $cluster, $post, $dependency_instance, $this);

					$dependency_instance->save();

					$this->update_cache($cluster_row->path, $cluster);

				}

			}

		} else { // -> delete cluster

			$this->delete_cache($cluster_row->path);

			$cluster_table = $wpdb->prefix.$this->table_name;

			$wpdb->query($wpdb->prepare("DELETE FROM $cluster_table WHERE id = %d", $cluster_row->id));


		}

		wp_reset_postdata();

		return $query;
	}

	/**
	 * return Cluster or null
	 */
	public function get_cluster($post_id, $post_type) {
		global $wpdb, $karma;

		if ($karma->options->get_option('clusters_active')) {

			$path = apply_filters('karma_cluster_path', (string) $post_id, $post_type);

			$cluster = $this->get_cache($path);

			if (!$cluster) {

				$cluster_table = $wpdb->prefix.$this->table_name;

				$cluster_row = $wpdb->get_row($wpdb->prepare(
					"SELECT * FROM $cluster_table WHERE path = %s",
					$path
				));

				if (!$cluster_row) {

					$post = get_post($post_id);

					if ($post) {

						$request = apply_filters('karma_cluster_request', "p=$post_id&post_type={$post_type}");

						$cluster_row = $this->create_cluster($request, $path, $post->post_type);

					} else { // post not exist!

						return;

					}

				}

				$this->update_cluster($cluster_row);

				$cluster = $this->get_cache($path);

			}

			return $cluster;

		} else if (isset($this->post_types[$post_type]) && is_callable($this->post_types[$post_type])) {

			$query = new WP_Query(array(
				'p' => $post_id,
				'post_type' => $post_type
			));

			if ($query->have_posts()) {

				while ($query->have_posts()) {

					$query->the_post();

					$post = $query->post;

					$dependency_instance = $karma_dependencies->create_instance('cluster', 0);

					$cluster = new stdClass();

					call_user_func($this->post_types[$post_type], $cluster, $post, $dependency_instance, $this);

					return $cluster;

				}

			}

		}

	}

	/**
	 * get clusters from array of ids
	 */
	public function get_clusters($ids, $post_type) {

		$clusters = array();

		foreach ($ids as $id) {

			$clusters[] = $this->get_cluster($id, $post_type);

		}

		return $clusters;
	}

	/**
	 * update cache
	 */
	public function update_cache($path, $data) {

		$this->files->write_file($this->cluster_path.'/'.$path, 'data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

	}

	/**
	 * delete cache
	 */
	public function delete_cache($path) {

		$this->files->remove($this->cluster_path.'/'.$path);

	}

	/**
	 * update cache
	 */
	public function get_cache($path) {

		$data = $this->files->read_file($this->cluster_path.'/'.$path, 'data.json');

		if ($data) {

			return json_decode($data);

		}

	}

	/**
	 * Get page link to fetch json
	 */
	public function get_cluster_link($post_id, $post_type) {
		global $wpdb, $karma;

		if ($karma->options->get_option('clusters_active')) {

			$path = apply_filters('karma_cluster_path', (string) $post_id, $post_type);

			if (!file_exists($this->cluster_path.'/'.$path.'/data.json')) {

				$cluster_table = $wpdb->prefix.$this->table_name;

				$cluster_row = $wpdb->get_row($wpdb->prepare(
					"SELECT * FROM $cluster_table WHERE path = %s",
					$path
				));

				if (!$cluster_row) {

					$request = apply_filters('karma_cluster_request', "p=$post_id&post_type={$post_type}");

					$cluster_row = $this->create_cluster($request, $path, $post_type);

				}

				$this->update_cluster($cluster_row);

			}

			return $this->cluster_url.'/'.$path.'/data.json';

		} else {

			return apply_filters('karma_cluster_request', add_query_arg(array(
				'action' => 'get_cluster',
				'id' => $post_id,
				'post_type' => $post_type
			), admin_url('admin-ajax.php')));

		}

	}

	/**
	 * get cluster links from array of ids
	 */
	public function get_cluster_links($ids, $post_type) {

		$links = array();

		foreach ($ids as $id) {

			$links[] = $this->get_cluster_link($id, $post_type);

		}

		return $links;
	}

	/**
	 * @filter 'karma_task'
	 */
	public function add_task($task) {
		global $wpdb, $karma;

		if (empty($task) && $karma->options->get_option('clusters_active')) {

			$cluster_table = $wpdb->prefix.$this->table_name;

			$outdated_cluster = $wpdb->get_row("SELECT * FROM $cluster_table WHERE status > 0 ORDER BY status DESC LIMIT 1");

			if ($outdated_cluster) {

				$this->update_cluster($outdated_cluster);

				$task['action'] = 'cluster updated';
				$task['cluster_row'] = $outdated_cluster;
				$task['cluster'] = $this->get_cache($outdated_cluster->path);
				$task['notice'] = 'updating...';

				$wpdb->query($wpdb->prepare(
					"UPDATE $cluster_table SET status = 0 WHERE id = %d",
					$outdated_cluster->id
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

		if ($karma->options->get_option('clusters_active')) {

			$cluster_table = $wpdb->prefix.$this->table_name;

			$wpdb->query($wpdb->prepare(
				"UPDATE $cluster_table SET status = GREATEST(status, %d) WHERE id = %d",
				$dependency->priority,
				$dependency->target_id
			));

		}

	}

	/**
	 * @hook 'save_post'
	 */
	function save_post($post_id, $post, $update) {
		global $wpdb, $karma;

		if ($karma->options->get_option('clusters_active') && isset($this->post_types[$post->post_type])) {

			// $path = apply_filters('karma_cluster_path', (string) $post_id, $post->post_type);
			$path = (string) $post_id;

			$cluster_table = $wpdb->prefix.$this->table_name;

			$cluster_row = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM $cluster_table WHERE path = %s",
				$path
			));

			if ($cluster_row) {

				$wpdb->query($wpdb->prepare(
					"UPDATE $cluster_table SET status = 100 WHERE id = %d",
					$cluster_row->id
				));

			} else {

				// $request = apply_filters('karma_cluster_request', "p=$post_id");
				$request = "p={$post_id}&post_type={$post->post_type}";
				$cluster_row = $this->create_cluster($request, $path, $post->post_type);

				// do_action('karma_cluster_create', $cluster_row->id, $request, $path, $post->ID, $post->post_type, $this);

			}

			do_action('karma_save_cluster', $cluster_row, $post->ID, $post->post_type, $this);

		}

	}

	/**
	 * @hook 'before_delete_post'
	 *
	 * Must trigger after dependencies!
	 */
	public function delete_post($post_id) {
		global $wpdb;

		if ($karma->options->get_option('clusters_active')) {

			$table = $wpdb->prefix.$this->table_name;

			// $wpdb->query($wpdb->prepare(
			// 	"DELETE FROM $cluster_table WHERE path LIKE %s",
			// 	$post_id.'%'
			// ));

			$cluster_row = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM $table WHERE path = %s",
				(string) $post_id
			));

			if ($cluster_row) {

				do_action('karma_delete_cluster', $cluster_row, $post_id, $cluster_row->post_type, $this);

				$this->files->remove($cluster_row->path);

				$wpdb->query($wpdb->prepare(
					"DELETE FROM $table WHERE id = %d",
					$cluster_row->id
				));

			}

		}

	}

	/**
	 *
	 */
	public function delete_all_clusters() {
		global $wpdb;

		$cluster_table = $wpdb->prefix.$this->table_name;

		$wpdb->query("truncate $cluster_table");

		do_action('karma_dependency_delete_target', 'cluster');

		$this->files->remove($this->cluster_path);

	}

	/**
	 *
	 */
	public function create_all_clusters() {
		global $wpdb;

		if ($this->post_types) {

			$sql = implode("', '", array_map('esc_sql', array_keys($this->post_types)));

			$posts = $wpdb->get_results(
				"SELECT ID, post_type FROM $wpdb->posts WHERE post_type IN ('$sql')"
			);

			foreach ($posts as $post) {

				$request = "p={$post->ID}&post_type={$post->post_type}";

				$path = (string) $post->ID;

				$cluster_row = $this->create_cluster($request, $path, $post->post_type);

				// do_action('karma_cluster_create', $cluster_row->id, $request, $path, $post->ID, $post->post_type, $this);

				do_action('karma_save_cluster', $cluster_row, $post->ID, $post->post_type, $this);

			}

		}

	}

	/**
	 *
	 */
	// public function update_all_clusters() {
	// 	global $wpdb;
	//
	// 	$table = $wpdb->prefix.$this->table_name;
	//
	// 	$wpdb->query($wpdb->prepare(
	// 		"UPDATE $table SET status = %d",
	// 		1
	// 	));
	//
	// }

	/**
	 * @ajax 'karma_update_clusters'
	 */
	public function ajax_update_clusters() {
		global $wpdb;

		$table = $wpdb->prefix.$this->table_name;

		$wpdb->query($wpdb->prepare(
			"UPDATE $table SET status = %d",
			1
		));

		$num_task = $wpdb->get_var("SELECT count(id) AS num FROM $table");

		$output['notice'] = "Updating $num_task clusters";

		echo json_encode($output);
		exit;

	}

	/**
	 * @ajax 'karma_create_clusters'
	 */
	public function ajax_create_clusters() {
		global $wpdb;

		$this->delete_all_clusters();
		$this->create_all_clusters();

		$cluster_table = $wpdb->prefix.$this->table_name;

		$num_task = $wpdb->get_var("SELECT count(id) AS num FROM $cluster_table WHERE status > 0");

		$output['notice'] = "Creating $num_task clusters";

		echo json_encode($output);
		exit;

	}

	/**
	 * @ajax 'karma_delete_clusters'
	 */
	public function ajax_delete_clusters() {

		$this->delete_all_clusters();

		$output['action'] = 'delete all';

		echo json_encode($output);
		exit;

	}

	/**
	 * @ajax 'karma_toggle_clusters'
	 */
	public function ajax_toggle_clusters() {
		global $wpdb, $karma;

		$output = array();

		if ($karma->options->get_option('clusters_active')) {

			$karma->options->update_option('clusters_active', '');

			$output['title'] = 'Clusters (disabled)';
			$output['label'] = 'Activate Clusters';
			$output['notice'] = "Deactivate Clusters. ";
			$output['action'] = 'deactivate clusters';

		} else {

			$karma->options->update_option('clusters_active', '1');

			$table = $wpdb->prefix.$this->sitepage_table;
			$num_task = $wpdb->get_var("SELECT count(id) AS num FROM $table");

			$output['title'] = 'Clusters (enabled)';
			$output['label'] = 'Deactivate Clusters';
			$output['notice'] = "Activating Clusters ($num_task). ";
			$output['action'] = 'rebuild clusters';

		}

		echo json_encode($output);
		exit;
	}

	/**
	 * @callbak 'admin_bar_menu'
	 */
	public function add_toolbar_button( $wp_admin_bar ) {
		global $karma;

		if (current_user_can('manage_options')) {

			$clusters_active = $karma->options->get_option('clusters_active');

			$wp_admin_bar->add_node(array(
				'id'    => 'clusters-group',
				'title' => 'Clusters ('.($clusters_active ? 'enabled' : 'disabled').')'
			));

			$wp_admin_bar->add_node(array(
				'id'    => 'update-clusters',
				'parent' => 'clusters-group',
				'title' => 'Update Clusters',
				'href'  => '#',
				'meta'  => array(
					// 'onclick' => 'ajaxPost("'.admin_url('admin-ajax.php').'", {action: "karma_update_clusters"}, function(results) {KarmaTaskManager.update(results.notice);});event.preventDefault();'
					'onclick' => 'KarmaTaskManager&&KarmaTaskManager.addTask("karma_update_clusters",this);event.preventDefault()'
				)
			));

			$wp_admin_bar->add_node(array(
				'id'    => 'create-clusters',
				'parent' => 'clusters-group',
				'title' => 'Create Clusters',
				'href'  => '#',
				'meta'  => array(
					'onclick' => 'KarmaTaskManager&&KarmaTaskManager.addTask("karma_create_clusters",this);event.preventDefault()'
				)
			));

			$wp_admin_bar->add_node(array(
				'id'    => 'delete-clusters',
				'parent' => 'clusters-group',
				'title' => 'Delete Clusters',
				'href'  => '#',
				'meta'  => array(
					'onclick' => 'KarmaTaskManager&&KarmaTaskManager.addTask("karma_delete_clusters",this);event.preventDefault()'
				)
			));

			$wp_admin_bar->add_node(array(
				'id'    => 'toggle-clusters',
				'title' => $clusters_active ? 'Deactivate Clusters' : 'Activate Clusters',
				'parent' => 'clusters-group',
				'href'  => '#',
				'meta'  => array(
					'onclick' => 'KarmaTaskManager&&KarmaTaskManager.addTask("karma_toggle_clusters",this,function(results){this.innerHTML=results.label;this.parentNode.parentNode.parentNode.previousSibling.innerHTML=results.title});event.preventDefault()'
				)
			));

		}

	}



	/**
	 * @filter 'karma_task_notice'
	 */
	public function task_notice($tasks) {
		global $wpdb;

		$cluster_table = $wpdb->prefix.$this->table_name;

		$num_task = $wpdb->get_var("SELECT count(id) AS num FROM $cluster_table WHERE status > 0");

		if ($num_task) {

			$tasks[] = "Updating $num_task clusters. ";

		}

		return $tasks;
	}

	/**
	 * @ajax 'get_cluster'
	 */
	public function ajax_get_cluster() {

		$cluster = $this->get_cluster($_GET['id'], $_GET['post_type']);

		if ($cluster) {

			echo json_encode($cluster);

		}

		exit;
	}


}

global $karma_clusters;
$karma_clusters = new Karma_Clusters;


function karma_get_cluster($post_id, $post_type = null) {
	global $karma_clusters;

	return $karma_clusters->get_cluster($post_id, $post_type);
}

function karma_get_clusters($post_ids, $post_type = null) {
	global $karma_clusters;

	return $karma_clusters->get_clusters($post_ids, $post_type);
}

function karma_get_cluster_link($post_id, $post_type = null) {
	global $karma_clusters;

	return $karma_clusters->get_cluster_link($post_id, $post_type);
}

function karma_get_cluster_links($post_ids, $post_type = null) {
	global $karma_clusters;

	return $karma_clusters->get_cluster_links($post_ids, $post_type);
}

function karma_register_cluster($post_type, $callback) {
	global $karma_clusters;

	return $karma_clusters->register($post_type, $callback);
}

<?php

/**
 *	Class Karma_Clusters
 */
class Karma_Clusters {

	var $version = '1';
	var $post_types = array();
	var $table_name = 'cache_clusters';
	var $cluster_path = WP_CONTENT_DIR.'/clusters';
	var $cluster_url = WP_CONTENT_URL.'/clusters';

	/**
	 *	Constructor
	 */
	public function __construct() {

		require_once get_template_directory() . '/modules/dependencies/dependencies.php';
		require_once get_template_directory() . '/modules/clusters/multilanguage.php';
		require_once get_template_directory() . '/modules/task-manager/task-manager.php';

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
	 * get_path
	 */
	public function get_path($post) {

		$path = get_page_uri($post);

		if ($post->post_type !== 'post' && $post->post_type !== 'page') {

			$path = $post->post_type.'/'.$path;

		}

		return apply_filters('karma_cluster_uri', $path, $post);
	}

	/**
	 * create_cluster
	 */
	public function create_cluster($post, $request, $path) {
		global $wpdb;

		$cluster_table = $wpdb->prefix.$this->table_name;

		$wpdb->insert($cluster_table, array(
			'path' => $path,
			'request' => $request,
			'post_type' => $post->post_type,
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
		$cluster_row->post_type = $post->post_type;

		return $cluster_row;
	}



	/**
	 * update_cluster
	 */
	public function update_cluster($cluster_row) {
		global $karma_dependencies, $wpdb;

		$query = new WP_Query($cluster_row->request);

		$query = apply_filters('karma_update_cluster_query', $query, $cluster_row, $this);

		if ($query->have_posts()) {

			while ($query->have_posts()) {

				$query->the_post();

				$post = $query->post;

				if (isset($this->post_types[$post->post_type]) && is_callable($this->post_types[$post->post_type])) {

					$dependency_instance = $karma_dependencies->create_instance('cluster', $cluster_row->id);

					// $dependency_instance->add_id('post', $post->post_type, $post->ID, 100);

					$cluster = new stdClass();

					call_user_func($this->post_types[$post->post_type], $cluster, $post, $dependency_instance, $this, $query);

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
	 * update cache
	 */
	public function update_cache($path, $data) {

		$file = $this->cluster_path . '/' . $path.'.json';

		if (!file_exists(dirname($file))) {

			mkdir(dirname($file), 0777, true);

		}

		file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

	}

	/**
	 * delete cache
	 */
	public function delete_cache($path) {

		$this->rrmdir($this->cluster_path.'/'.$path);

	}

	/**
	 * Remove directory and all content
	 */
	private function rrmdir($dir) {

		if (is_dir($dir)) {

			$objects = scandir($dir);

			foreach ($objects as $object) {

				if ($object != "." && $object != "..") {

					$this->rrmdir($dir."/".$object);

				}

			}

			rmdir($dir);

		} else if (is_file($dir)) {

			unlink($dir);

		}

	}

	/**
	 * update cache
	 */
	public function get_cache($path) {

		$file = $this->cluster_path . '/' . $path.'.json';

		if (file_exists($file)) {

			return json_decode(file_get_contents($file));

		}

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

			$path = $this->get_path($post);

			$cluster_table = $wpdb->prefix.$this->table_name;

			$cluster_row = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM $cluster_table WHERE $path = %s",
				$path
			));

			if ($cluster_row) {

				$wpdb->query($wpdb->prepare(
					"UPDATE $cluster_table SET status = 100 WHERE id = %d",
					$cluster_row->id
				));

			} else {

				$request = "p={$post->ID}&post_type={$post->post_type}";
				$cluster_row = $this->create_cluster($post, $request, $path);

			}

			do_action('karma_save_cluster', $cluster_row, $post, $this);

		}

	}

	/**
	 * @hook 'before_delete_post'
	 *
	 * Must trigger after dependencies!
	 */
	public function delete_post($post_id) {
		global $wpdb, $karma;

		if ($karma->options->get_option('clusters_active')) {

			$table = $wpdb->prefix.$this->table_name;

			$post = get_post($post_id);
			$path = $this->get_path($post);

			$cluster_row = $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM $table WHERE path = %s",
				$path
			));

			if ($cluster_row) {

				do_action('karma_delete_cluster', $cluster_row, $post_id, $cluster_row->post_type, $this);

				$this->delete_cache($cluster_row->path);

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

		$this->rrmdir($this->cluster_path);

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

				$path = $this->get_path($post);
				$request = "p={$post->ID}&post_type={$post->post_type}";
				$cluster_row = $this->create_cluster($post, $request, $path);

				do_action('karma_save_cluster', $cluster_row, $post, $this);

			}

		}

	}

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

			// $table = $wpdb->prefix.$this->table_name;
			// $num_task = $wpdb->get_var("SELECT count(id) AS num FROM $table");

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


}

global $karma_clusters;
$karma_clusters = new Karma_Clusters;

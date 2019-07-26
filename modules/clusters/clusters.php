<?php

/**
 *	Class Karma_Clusters
 */
class Karma_Clusters {

	var $dependencies = array();
	var $post_types = array();
	var $dependency_table = 'clusters';

	/**
	 *	Constructor
	 */
	function __construct() {

		// require_once get_template_directory() . '/modules/clusters/ui.php';
		require_once get_template_directory() . '/modules/task-manager/task-manager.php';

		require_once get_template_directory() . '/admin/class-file.php';

		$this->file_manager = new Karma_Content_Directory;
		$this->file_manager->directory = 'clusters';



		// add_action('wp_insert_post', array($this, 'on_save'), 99, 3);

		add_action('wp_ajax_get_cluster', array($this, 'ajax_get_cluster'));
		add_action('wp_ajax_nopriv_get_cluster', array($this, 'ajax_get_cluster'));

		add_filter('karma_task', array($this, 'add_task'));
		add_action('wp_ajax_clusters_update', array($this, 'ajax_clusters_update'));


		// add_action('wp_ajax_clusters_update', array($this, 'ajax_clusters_update'));
		// add_action('wp_ajax_clusters_get_expired_clusters', array($this, 'ajax_clusters_get_expired_clusters'));

		add_action('init', array($this, 'create_dependency_tables'));

		add_action('save_post', array($this, 'save_post'), 10, 3);
		add_action('before_delete_post', array($this, 'before_delete_post'), 10);
		add_action('edit_term', array($this, 'edit_term'), 10, 3);
		add_action('create_term', array($this, 'create_term'), 10, 3);
		add_action('pre_delete_term', array($this, 'pre_delete_term'), 10, 2);

		// add_action('updated_post_meta', array($this, 'updated_post_meta'), 10, 4);
		// add_action('added_post_meta', array($this, 'updated_post_meta'), 10, 4);
		// add_action('deleted_post_meta', array($this, 'updated_post_meta'), 10, 4);
		// add_action('updated_term_meta', array($this, 'updated_term_meta'), 10, 4);
		// add_action('added_term_meta', array($this, 'updated_term_meta'), 10, 4);
		// add_action('deleted_term_meta', array($this, 'updated_term_meta'), 10, 4);

		// add_action('wp_loaded', array($this, 'update_dependencies'));
		// add_action('redirect_post_location', array($this, 'redirect'));



	}

	/**
	 * @hook 'init'
	 */
	function create_dependency_tables() {

		if (is_admin()) {

			require_once get_template_directory() . '/admin/table.php';

			Karma_Table::create($this->dependency_table, "
				id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				object_id int(11) NOT NULL,
				target_id int(11) NOT NULL,
				object varchar(10) NOT NULL,
				type varchar(50) NOT NULL
			", '000');

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
	public function create_cluster($post) {

		$cluster = new stdClass();

		if (isset($this->post_types[$post->post_type]) && is_callable($this->post_types[$post->post_type])) {

			require_once get_template_directory() . '/modules/clusters/dependencies.php';

			$dependencies = new Karma_Cluster_Dependencies($post->ID, $this->dependency_table);

			$this->current_dependencies = $dependencies;

			call_user_func($this->post_types[$post->post_type], $cluster, $post, $dependencies, $this);

			// -> html cache
			// do_action('karma_cluster_save', $post, $cluster, $this);
		}

		return $cluster;
	}

	/**
	 * update_cluster
	 */
	public function update_cluster($post_id) {

		$post = get_post($post_id);

		if ($post) {

			$current = $this->get_cache($post_id);

			$cluster = $this->create_cluster($post);
			$this->update_cache($post->ID, $cluster);

			if ($current) {

				do_action('karma_cache_update_object', 'cluster', $post->post_type, $post_id);

			} else {

				do_action('karma_cache_create_object', 'cluster', $post->post_type, $post_id);

			}

			// -> sublanguage
			do_action('karma_cluster_update', $post, $cluster, $this);

			return $cluster;

		} else {

			$this->delete_cache($post_id);

			do_action('karma_cache_delete_object', 'cluster', '', $post_id);

		}

	}

	/**
	 * ONLY FOR TEST !
	 *
	 * @hook 'wp_insert_post'
	 */
	public function on_save($id, $post, $update) {

		if (isset($this->post_types[$post->post_type])) {

			$this->update_cluster($id);

		}

	}

	/**
	 * @return Cluster or null
	 */
	public function get_cluster($id) {

		$cluster = $this->get_cache($id);

		if (!$cluster) {

			$cluster = $this->update_cluster($id);

			// $post = get_post($id);
			//
			// if ($post) {
			//
			// 	$cluster = $this->update_cluster($post);
			//
			// } else {
			//
			// 	$this->delete_cache($id);
			//
			// }

		}

		return $cluster;
	}

	/**
	 * @hook 'wp_loaded'
	 */
	// function update_dependencies() {
	//
	// 	if ($this->dependencies) {
	//
	// 		$query = new WP_Query(array(
	// 			'post__in' => array_map('intval', array_unique($this->dependencies)),
	// 			'post_status' => 'any',
	// 			'post_type' => array_keys($this->post_types)
	// 		));
	//
	// 		foreach ($query->posts as $post) {
	//
	// 			$this->update_cluster($post);
	//
	// 		}
	//
	// 	}
	//
	// }
	//
	// /**
	//  * @hook 'save_post'
	//  */
	// function save_post($post_id, $post, $update) {
	//
	// 	$dependencies = get_post_meta($post_id, 'dependencies');
	//
	// 	$this->dependencies[$post->post_type] = array_merge($this->dependencies, $dependencies);
	//
	// }
	//
	// /**
	//  * @hook 'before_delete_post'
	//  */
	// function before_delete_post($post_id) {
	//
	// 	$dependencies = get_post_meta($post_id, 'dependencies');
	//
	// 	$this->dependencies = array_merge($this->dependencies, $dependencies);
	//
	// }
	//
	// /**
	//  * @hook 'edit_term'
	//  */
	// function edit_term($term_id, $tt_id, $taxonomy) {
	//
	// 	$dependencies = get_term_meta($term_id, 'dependencies');
	//
	// 	$this->dependencies = array_merge($this->dependencies, $dependencies);
	//
	// }
	//
	// /**
	//  * @hook 'pre_delete_term'
	//  */
	// function pre_delete_term($term, $taxonomy) {
	//
	// 	$dependencies = get_term_meta($term->term_id, 'dependencies');
	//
	// 	$this->dependencies = array_merge($this->dependencies, $dependencies);
	//
	// }

	/**
	 * update cache
	 */
	public function update_cache($post_id, $data) {

		$this->file_manager->write_file(apply_filters('karma_append_language_to_path', $post_id), 'data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

	}

	/**
	 * delete cache
	 */
	public function delete_cache($post_id) {

		$this->file_manager->erase_dir($post_id);

	}

	/**
	 * update cache
	 */
	public function get_cache($post_id) {

		$data = $this->file_manager->read_file(apply_filters('karma_append_language_to_path', $post_id), 'data.json');

		if ($data) {

			return json_decode($data);

		}

	}


	/**
	 * Get page link to fetch json
	 */
	public function get_cluster_link($post_id) {
		global $wp_object_cache, $sublanguage;

		if ($this->file_manager->file_exists(apply_filters('karma_append_language_to_path', $post_id), 'data.json')) {

			return $this->file_manager->get_url(apply_filters('karma_append_language_to_path', $post_id), 'data.json');

		} else {

			return add_query_arg(array(
				'action' => 'get_cluster',
				'id' => $post_id
			), admin_url('admin-ajax.php'));

		}

	}

	/**
	 * @ajax 'get_cluster'
	 */
	public function ajax_get_cluster() {

		if (isset($_GET['id'])) {

			$post_id = intval($_GET['id']);
			$cluster = $this->get_cluster($post_id);

			if ($cluster) {

				echo json_encode($cluster, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

			} else {

				echo json_encode(array(
					'error' => "post not found for this id: $post_id",
					'cluser' => $cluster
				));

			}

		} else {

			echo json_encode("id not set");

		}

		exit;

	}

	/**
	 * @ajax 'clusters_update'
	 */
	public function ajax_clusters_update() {
		global $karma;

		$output = array();

		if (isset($_POST['id'])) {

			$post_id = intval($_POST['id']);
			$dependencies = $karma->options->get_option('expired_clusters');

			$output['id'] = $post_id;
			$output['dependencies'] = $dependencies;

			if (in_array($post_id, $dependencies)) {

				$cluster = $this->update_cluster($post_id);

				$output['success'] = true;
				$output['cluster'] = $cluster;

				// $output['options'] = get_option('karma');

				$output['remove_log'] = $this->remove_dependency($post_id);

				// $output['options_after'] = get_option('karma');
				//
				// $output['dependencies_after'] = $karma->options->get_option('expired_clusters');


				// $post = get_post($post_id);
				//
				// if ($post) {
				//
				// 	$cluster = $this->update_cluster($post);
				// 	$output['success'] = true;
				// 	$output['cluster'] = $cluster;
				//
				// } else {
				//
				// 	$output['error'] = 'post not exists';
				//
				// }

				// $index = array_search($post_id, $dependencies);
				//
				// $output['index'] = $index;
				//
				// unset($dependencies[$index]);
				//
				// $karma->options->update_option('expired_clusters', $dependencies);

			} else {

				$output['error'] = 'id not in expire list';


			}

		} else {

			$output['error'] = 'id not set';

		}

		echo json_encode($output);
		exit;

	}

	/**
	 * @ajax 'clusters_save_dependences'
	 */
	// public function ajax_clusters_save_dependences() {
	// 	global $karma, $wpdb;
	//
	// 	$output = array();
	//
	// 	if (isset($_POST['id'])) {
	//
	// 		$post_id = intval($_POST['id']);
	//
	// 		$dependences = $karma->options->get_option('dependences');
	//
	// 		if (isset($dependences[$post_id])) {
	//
	// 			$output['id'] = $post_id;
	// 			$output['dependences'] = $dependences[$post_id];
	//
	// 			$wpdb->delete($wpdb->postmeta, array(
	// 				'meta_key' => 'dependencies',
	// 				'meta_value' => $post_id
	// 			), array(
	// 				'%s',
	// 				'%d'
	// 			));
	//
	// 			$wpdb->delete($wpdb->termmeta, array(
	// 				'meta_key' => 'dependencies',
	// 				'meta_value' => $post_id
	// 			), array(
	// 				'%s',
	// 				'%d'
	// 			));
	//
	// 			if (isset($dependences[$post_id]['posts'])) {
	//
	// 				foreach ($dependences[$post_id]['posts'] as $dependent_post_id) {
	//
	// 					add_post_meta($dependent_post_id, 'dependencies', $post_id);
	//
	// 				}
	//
	// 				foreach ($dependences[$post_id]['terms'] as $dependent_term_id) {
	//
	// 					add_term_meta($dependent_term_id, 'dependencies', $post_id);
	//
	// 				}
	//
	// 			}
	//
	// 			unset($dependences[$post_id]);
	//
	// 			$karma->options->update_option('dependences', $dependences);
	//
	// 			$output['success'] = true;
	//
	// 		} else {
	//
	// 			$output['error'] = 'id not in dependences';
	// 			$output['id'] = $post_id;
	// 			$output['dependences'] = $dependences[$post_id];
	//
	// 		}
	//
	// 	} else {
	//
	// 		$output['error'] = 'id not set';
	//
	// 	}
	//
	// 	echo json_encode($output);
	// 	exit;
	//
	// }


	/**
	 * @ajax 'clusters_get_expired_clusters'
	 */
	// public function ajax_clusters_get_expired_clusters() {
	// 	global $karma;
	//
	// 	$output = $karma->options->get_option('expired_clusters');
	//
	// 	echo json_encode($output);
	//
	// 	exit;
	//
	// }

	/**
	 * @filter 'karma_task'
	 */
	public function add_task($tasks) {
		global $karma;

		$ids = $karma->options->get_option('expired_clusters', array());

		if ($ids) {

			$items = array();

			foreach ($ids as $id) {

				$items[] = array(
					'id' => $id
				);

			}

			$tasks[] = array(
				'name' => 'Clusters',
				'items' => $items,
				'task' => 'clusters_update'
			);

		}

		return $tasks;
	}

	/**
	 * @ajax 'clusters_get_dependencies'
	 */
	// public function ajax_clusters_get_dependences() {
	// 	global $karma;
	//
	// 	$output = array_keys($karma->options->get_option('dependences'));
	//
	// 	echo json_encode($output);
	//
	// 	exit;
	//
	// }


	/**
	 * @filter 'redirect_post_location'
	 */
	// function redirect($url) {
	//
	// 	$this->update_dependencies();
	//
	// 	return $url;
	// }
	//
	// /**
	//  * @hook 'wp_loaded'
	//  */
	// function update_dependencies() {
	// 	global $karma;
	//
	// 	if ($this->dependencies) {
	//
	// 		$dependencies = $karma->options->get_option('expired_clusters', array());
	// 		$dependencies = array_merge($this->dependencies, $dependencies);
	// 		$dependencies = array_map('intval', array_unique($dependencies));
	//
	// 		$karma->options->update_option('expired_clusters', $dependencies);
	//
	// 	}
	//
	// }

	/**
	 * add_dependencies
	 */
	function add_dependencies($ids) {
		global $karma;

		$dependencies = $karma->options->get_option('expired_clusters', array());
		$dependencies = array_merge($ids, $dependencies);
		$dependencies = array_map('intval', array_unique($dependencies));

		$karma->options->update_option('expired_clusters', $dependencies);

	}

	/**
	 * add_dependency
	 */
	function add_dependency($id) {
		global $karma;

		$dependencies = $karma->options->get_option('expired_clusters', array());
		$dependencies[] = intval($id);

		$karma->options->update_option('expired_clusters', $dependencies);

	}

	/**
	 * remove_dependency
	 */
	function remove_dependency($id) {
		global $karma;

		$dependencies = $karma->options->get_option('expired_clusters', array());

		$index = array_search($id, $dependencies);

		if ($index !== false) {

			array_splice($dependencies, $index, 1);

		}

		return $karma->options->update_option('expired_clusters', $dependencies);

	}


	/**
	 * @hook 'save_post'
	 */
	function save_post($post_id, $post, $update) {
		global $wpdb;

		$dependencies = array();

		$table = $wpdb->prefix.$this->dependency_table;

		if ($update) {

			$dependencies = $wpdb->get_col($wpdb->prepare("SELECT target_id FROM $table WHERE object = 'post' AND object_id = %d", $post_id));

		} else {

			$dependencies = $wpdb->get_col($wpdb->prepare("SELECT target_id FROM $table WHERE object = 'post' AND type = %s", $post->post_type));

		}

		if (isset($this->post_types[$post->post_type])) {

			$dependencies[] = $post_id;

		}

		// $dependencies = get_post_meta($post_id, 'dependencies');

		// $this->dependencies = array_merge($this->dependencies, $dependencies);

		$this->add_dependencies($dependencies);

	}

	/**
	 * @hook 'before_delete_post'
	 */
	function before_delete_post($post_id) {
		global $wpdb;


		$table = $wpdb->prefix.$this->dependency_table;

		$dependencies = $wpdb->get_col($wpdb->prepare("SELECT target_id FROM $table WHERE object = 'post' AND object_id = %d", $post_id));

		$wpdb->delete($table, array(
			'object' => 'post',
			'object_id' => $post_id
		), array(
			'%s',
			'%d'
		));

		// $dependencies = get_post_meta($post_id, 'dependencies');

		// $this->dependencies = array_merge($this->dependencies, $dependencies);

		$this->add_dependencies($dependencies);

	}

	/**
	 * @hook 'update_{$meta_type}_meta', "added_{$meta_type}_meta", "deleted_{$meta_type}_meta"
	 */
	function updated_post_meta($meta_id, $object_id, $meta_key, $meta_value) {

		// if ($meta_key !== 'dependencies') {
		//
		// 	$dependencies = get_post_meta($object_id, 'dependencies');
		// 	$this->dependencies = array_merge($this->dependencies, $dependencies);
		//
		// }

	}

	/**
	 * @hook 'edit_term'
	 */
	function edit_term($term_id, $tt_id, $taxonomy) {
		global $wpdb;

		$table = $wpdb->prefix.$this->dependency_table;

		$dependencies = $wpdb->get_col($wpdb->prepare("SELECT target_id FROM $table WHERE object = 'term' AND object_id = %d", $term_id));


		// $dependencies = get_term_meta($term_id, 'dependencies');

		// $this->dependencies = array_merge($this->dependencies, $dependencies);

		$this->add_dependencies($dependencies);

	}

	/**
	 * @hook 'create_term'
	 */
	function create_term($term_id, $tt_id, $taxonomy) {
		global $wpdb;

		$table = $wpdb->prefix.$this->dependency_table;

		$dependencies = $wpdb->get_col($wpdb->prepare("SELECT target_id FROM $table WHERE object = 'term' AND type = %s", $taxonomy));


		// $dependencies = get_term_meta($term_id, 'dependencies');

		// $this->dependencies = array_merge($this->dependencies, $dependencies);

		$this->add_dependencies($dependencies);

	}





	/**
	 * @hook 'pre_delete_term'
	 */
	function pre_delete_term($term, $taxonomy) {
		global $wpdb;

		$table = $wpdb->prefix.$this->dependency_table;

		$dependencies = $wpdb->get_col($wpdb->prepare("SELECT target_id FROM $table WHERE object = 'term' AND object_id = %d", $term->term_id));

		$wpdb->delete($table, array(
			'object' => 'term',
			'object_id' => $term->term_id
		), array(
			'%s',
			'%d'
		));

		// $dependencies = get_term_meta($term->term_id, 'dependencies');

		// $this->dependencies = array_merge($this->dependencies, $dependencies);

		$this->add_dependencies($dependencies);

	}

	/**
	 * @hook 'update_{$meta_type}_meta', "added_{$meta_type}_meta", "deleted_{$meta_type}_meta"
	 */
	function updated_term_meta($meta_id, $object_id, $meta_key, $meta_value) {

		// if ($meta_key !== 'dependencies') {
		//
		// 	$dependencies = get_term_meta($object_id, 'dependencies');
		// 	$this->dependencies = array_merge($this->dependencies, $dependencies);
		//
		// }

	}



}

global $karma_clusters;
$karma_clusters = new Karma_Clusters;


function karma_get_cluster($post_id) {
	global $karma_clusters;

	return $karma_clusters->get_cluster($post_id);
}

function karma_get_cluster_link($post_id) {
	global $karma_clusters;

	return $karma_clusters->get_cluster_link($post_id);
}

function karma_register_cluster($post_type, $callback) {
	global $karma_clusters;

	return $karma_clusters->register($post_type, $callback);
}

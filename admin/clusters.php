<?php

/**
 *	Class Karma_Clusters
 */
class Karma_Clusters {

	var $dependencies = array();
	var $post_types = array();

	/**
	 *	Constructor
	 */
	function __construct() {

		require_once get_template_directory() . '/admin/class-file.php';

		$this->file_manager = new Karma_Content_Directory;
		$this->file_manager->directory = 'clusters';

		add_action('save_post', array($this, 'save_post'), 10, 3);
		add_action('before_delete_post', array($this, 'before_delete_post'), 10);
		add_action('edit_term', array($this, 'edit_term'), 10, 3);
		add_action('pre_delete_term', array($this, 'pre_delete_term'), 10, 2);

		add_action('wp_loaded', array($this, 'update_dependencies'));

		// add_action('karma_cache_write', array($this, 'cache_write'), 10, 4);
		add_filter('karma_cluster_json_link', array($this, 'filter_cluster_json_link'), 10, 2);

		add_action('wp_insert_post', array($this, 'on_save'), 99, 3);

		add_action('wp_ajax_get_cluster', array($this, 'ajax_get_cluster'));
		add_action('wp_ajax_nopriv_get_cluster', array($this, 'ajax_get_cluster'));
	}

	/**
	 * @deprecated
	 *
	 * register_post_type
	 */
	function register_post_type($post_type) {

		// $this->post_types[] = $post_type;
		$this->post_types[$post_type] = true;

	}

	/**
	 * register cluster for post type
	 */
	function register($post_type, $callback) {

		$this->post_types[$post_type] = $callback;

	}

	/**
	 * create_cluster
	 */
	// public function create_cluster($post) {
	//
	// 	$this->clear_dependencies($post->ID);
	// 	$cluster = new stdClass();
	// 	$cluster = apply_filters('karma_clusters_update_'.$post->post_type, $cluster, $post, $this);
	//
	// 	return $cluster;
	//
	// }

	/**
	 * update_cluster
	 */
	public function update_cluster($post) {

		$cluster = new stdClass();

		if (isset($this->post_types[$post->post_type]) && is_callable($this->post_types[$post->post_type])) {

			require_once get_template_directory() . '/admin/cluster-dependencies.php';

			$dependencies = new Karma_Cluster_Dependencies($post->ID);

			call_user_func($this->post_types[$post->post_type], $cluster, $post, $dependencies);

		}

		// compat
		else if (has_filter('karma_clusters_update_'.$post->post_type)) {

			$this->clear_dependencies($post->ID);

			$cluster = apply_filters('karma_clusters_update_'.$post->post_type, $cluster, $post, $this);

		}

		$this->update_cache($post->ID, $cluster);

		return $cluster;

	}

	/**
	 * @hook 'wp_insert_post'
	 */
	public function on_save($id, $post, $update) {

		// if (in_array($post->post_type, $this->post_types)) {

		// if (has_filter('karma_clusters_update_'.$post->post_type)) {

		$this->update_cluster($post);

		// if (isset($this->post_types[$post->post_type])) { // -> compat
		//
		// 	$cluster = $this->create_cluster($post);
		//
		// 	call_user_func($this->post_types[$post->post_type], $cluster, $post, $this);
		//
		// 	$this->update_cache($post->ID, $cluster);
		//
		// }
		//
		// // compat
		// else if (has_filter('karma_clusters_update_'.$post->post_type)) {
		//
		// 	$cluster = $this->create_cluster($post);
		// 	$this->update_cache($post->ID, $cluster);
		//
		// }

	}


	/**
	 * @return Cluster
	 */
	public function get_cluster($id) {

		$cluster = $this->get_cache($id);

		if (!$cluster) {

			$post = get_post($id);

			if ($post) {

				$cluster = $this->update_cluster($post);

			}
			// if ($post && isset($this->post_types[$post->post_type])) {
			//
			// 	$cluster = $this->create_cluster($post);
			//
			// 	call_user_func($this->post_types[$post->post_type], $cluster, $post, $this);
			//
			// 	$this->update_cache($post->ID, $cluster);
			//
			// }
			//
			// // compat
			// else if ($post) {
			//
			// 	$cluster = $this->create_cluster($post);
			// 	$this->update_cache($post->ID, $cluster);
			//
			// }

		}

		return $cluster;
	}

	/**
	 * @hook 'wp_loaded'
	 */
	function update_dependencies() {

		if ($this->dependencies) {

			$query = new WP_Query(array(
				'post__in' => array_map('intval', array_unique($this->dependencies)),
				'post_status' => 'any',
				'post_type' => array_keys($this->post_types)
			));

			foreach ($query->posts as $post) {

				$this->update_cluster($post);

				// $cluster = $this->create_cluster($post);
				// $this->update_cache($post->ID, $cluster);

			}

		}

	}

	/**
	 * @hook 'save_post'
	 */
	function save_post($post_id, $post, $update) {

		$dependencies = get_post_meta($post_id, 'dependencies');

		$this->dependencies[$post->post_type] = array_merge($this->dependencies, $dependencies);

	}

	/**
	 * @hook 'before_delete_post'
	 */
	function before_delete_post($post_id) {

		$dependencies = get_post_meta($post_id, 'dependencies');

		$this->dependencies = array_merge($this->dependencies, $dependencies);

	}

	/**
	 * @hook 'edit_term'
	 */
	function edit_term($term_id, $tt_id, $taxonomy) {

		$dependencies = get_term_meta($term_id, 'dependencies');

		$this->dependencies = array_merge($this->dependencies, $dependencies);

	}

	/**
	 * @hook 'pre_delete_term'
	 */
	function pre_delete_term($term, $taxonomy) {

		$dependencies = get_term_meta($term->term_id, 'dependencies');

		$this->dependencies = array_merge($this->dependencies, $dependencies);

	}

	/**
	 * @hook 'karma_cache_write'
	 */
	// public function cache_write($data, $key, $group, $object_cache) {
	//
	// 	if ($group === 'clusters') {
	//
	// 		$path = $object_cache->object_dir . '/' . $group . '/' . $key . apply_filters('karma_append_language_to_path', '');
	//
	// 		$object_cache->write_file($path, 'data.json', json_encode($data, JSON_PRETTY_PRINT));
	//
	// 	}
	//
	// }

	/**
	 * update cache
	 */
	public function update_cache($post_id, $data) {

		// wp_cache_set($post_id, $data, 'clusters');

		$this->file_manager->write_file(apply_filters('karma_append_language_to_path', $post_id), 'data.json', json_encode($data, JSON_PRETTY_PRINT));

	}

	/**
	 * delete cache
	 */
	public function delete_cache($post_id) {

		// wp_cache_delete($post_id, 'clusters');

		$this->file_manager->erase_dir($post_id);

	}

	/**
	 * update cache
	 */
	public function get_cache($post_id) {

		// return wp_cache_get($post_id, 'clusters');

		$data = $this->file_manager->read_file(apply_filters('karma_append_language_to_path', $post_id), 'data.json');

		return json_decode($data);

	}


	/**
	 * Get page link to fetch json
	 */
	public function get_cluster_link($post_id) {
		global $wp_object_cache, $sublanguage;

		if ($this->file_manager->file_exists(apply_filters('karma_append_language_to_path', $post_id), 'data.json')) {

		// if (isset($wp_object_cache->cache_dir) && is_file(WP_CONTENT_DIR . '/' . $wp_object_cache->cache_dir . '/' . $wp_object_cache->object_dir . '/clusters/' . $post_id . apply_filters('karma_append_language_to_path', '') . '/data.json')) {

			// return WP_CONTENT_URL . '/' . $wp_object_cache->cache_dir . '/' . $wp_object_cache->object_dir . '/clusters/' . $post_id . apply_filters('karma_append_language_to_path', '') . '/data.json';

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

				echo json_encode($cluster);

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
	 * @Deprecated
	 *
	 * @filter 'karma_cluster_json_link'
	 */
	public function filter_cluster_json_link($link, $post_id) {

		if (isset($wp_object_cache) && is_file(WP_CONTENT_DIR . '/' . $wp_object_cache->cache_dir . '/' . $wp_object_cache->object_dir . '/clusters/' . $post_id . apply_filters('karma_append_language_to_path', '') . '/data.json')) {

			return WP_CONTENT_URL . '/' . $wp_object_cache->cache_dir . '/' . $wp_object_cache->object_dir . '/clusters/' . $post_id . apply_filters('karma_append_language_to_path', '') . '/data.json';

		}

		return $link;
	}

	/**
	 * @deprecated use add_dependent_post_id()
	 *
	 * clear dependencies
	 */
	public function clear_dependencies($post_id) {
		global $wpdb;

		$wpdb->delete($wpdb->postmeta, array(
			'meta_key' => 'dependencies',
			'meta_value' => $post_id
		), array(
			'%s',
			'%d'
		));

		$wpdb->delete($wpdb->termmeta, array(
			'meta_key' => 'dependencies',
			'meta_value' => $post_id
		), array(
			'%s',
			'%d'
		));

	}

	/**
	 * @deprecated use add_dependent_post_id()
	 *
	 * add post dependency
	 */
	public function add_post_dependency($post_id, $dependent_post_id) {

		add_post_meta($dependent_post_id, 'dependencies', $post_id);

	}

	/**
	 * @deprecated use add_dependent_post_ids()
	 *
	 * add post dependencies
	 */
	public function add_post_dependencies($post_id, $dependent_post_ids) {

		foreach ($dependent_post_ids as $dependent_post_id) {

			$this->add_post_dependency($post_id, $dependent_post_id);

		}

	}

	/**
	 * add post dependency
	 */
	// public function add_dependent_post_id($post_id) {
	//
	// 	if ($this->current_post_id) {
	//
	// 		add_post_meta($post_id, 'dependencies', $this->current_post_id);
	//
	// 	}
	//
	// }
	//
	// /**
	//  * add post dependencies
	//  */
	// public function add_dependent_post_ids($post_ids) {
	//
	// 	foreach ($post_ids as $post_id) {
	//
	// 		$this->add_dependent_post_id($post_id);
	//
	// 	}
	//
	// }

	/**
	 * @deprecated use add_dependent_term_id()
	 *
	 * add term dependency
	 */
	public function add_term_dependency($post_id, $dependent_term_id) {

		add_term_meta($dependent_term_id, 'dependencies', $post_id);

	}

	/**
	 * @deprecated use add_dependent_term_ids()
	 *
	 * add term dependencies
	 */
	public function add_term_dependencies($post_id, $dependent_term_ids) {

		foreach ($dependent_term_ids as $dependent_term_id) {

			$this->add_term_dependency($post_id, $dependent_term_id);

		}

	}

	/**
	 * add term dependency
	 */
	// public function add_dependent_term_id($term_id) {
	//
	// 	if ($this->current_post_id) {
	//
	// 		add_term_meta($term_id, 'dependencies', $this->current_post_id);
	//
	// 	}
	//
	// }
	//
	// /**
	//  * add term dependencies
	//  */
	// public function add_dependent_term_ids($term_ids) {
	//
	// 	foreach ($term_ids as $term_id) {
	//
	// 		$this->add_dependent_term_id($term_id);
	//
	// 	}
	//
	// }





	/**
	 * get image sizes data
	 */
	public function get_image_data($attachement_id) {

		$metadata = wp_get_attachment_metadata($attachement_id);

		$sources = apply_filters('background-image-manager-sources', array(array(
			'src' => wp_get_attachment_url($attachement_id),
			'width' => $metadata['width'],
			'height' => $metadata['height']
		)), $attachement_id);

		return $sources;
	}

	/**
	 * get all image sizes data
	 */
	public function get_images_data($attachement_ids) {

		$images = array();

		foreach ($attachement_ids as $attachement_id) {

			$images[] = $this->get_image_data($attachement_id);

		}

		return $images;
	}

}

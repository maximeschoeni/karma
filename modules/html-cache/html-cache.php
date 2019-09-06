<?php


Class Karma_Cache {

	public $version = '01';

	public $dependency_table = 'htmlcache';

	public $dependencies = array();


	/**
	 *	Profile requirement metabox callback
	 */
	public function __construct() {





		// register_activation_hook(__FILE__, array('Karma_Cache', 'activate'));
		// register_deactivation_hook(__FILE__, array('Karma_Cache', 'deactivate'));

		$this->path = get_template_directory() . '/modules/html-cache';

		require_once get_template_directory() . '/modules/html-cache/multilanguage.php';

		// require_once get_template_directory() . '/admin/class-file.php';
		//
		// $this->file_manager = new Karma_Content_Directory;
		// $this->file_manager->directory = 'cache';

		// add_filter('mod_rewrite_rules', array($this, 'mod_rewrite_rules'));

		// add_action('karma_cache_add_post_dependency', array($this, 'add_post_dependency'));
		// add_action('karma_cache_add_term_dependency', array($this, 'add_term_dependency'));
		// add_action('karma_cache_add_type_dependency', array($this, 'add_type_dependency'));
		// add_action('karma_cache_add_taxonomy_dependency', array($this, 'add_taxonomy_dependency'));


		add_action('karma_cache_add_dependency_id', array($this, 'add_dependency_id'), 10, 2);
		add_action('karma_cache_add_dependency_ids', array($this, 'add_dependency_ids'), 10, 2);
		add_action('karma_cache_add_dependency_type', array($this, 'add_dependency_type'), 10, 3);


		add_filter('karma_task', array($this, 'add_task'), 8);
		add_filter('karma_task', array($this, 'rebuild_all_task'));

		add_action('save_post', array($this, 'save_post'), 10, 3);
		add_action('before_delete_post', array($this, 'before_delete_post'), 10);
		add_action('edit_term', array($this, 'edit_term'), 10, 3);
		add_action('create_term', array($this, 'create_term'), 10, 3);
		add_action('pre_delete_term', array($this, 'pre_delete_term'), 10, 2);

		add_action('karma_cluster_create_object', array($this, 'create_object'), 10, 3);
		add_action('karma_cluster_update_object', array($this, 'update_object'), 10, 3);
		add_action('karma_cluster_delete_object', array($this, 'delete_object'), 10, 3);

		add_action('wp_ajax_karma_cache_regenerate_url', array($this, 'ajax_regenerate_url'));
		add_action('wp_ajax_karma_htmlcache_flush', array($this, 'ajax_flush'));

		if (is_admin()) {

			// TEST
			// add_action('admin_init', array($this, 'get_all_resources'), 99);


			// -> handle admin option page
			add_action('init', array($this, 'create_dependency_tables'), 9);

			add_action('karma_save_options', array($this, 'save_options'));
			add_action('karma_print_options', array($this, 'print_options'));

			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

			add_action( 'admin_bar_menu', array($this, 'add_toolbar_button'), 999);

		} else {

			// skip request parsing
			add_filter('do_parse_request', array($this, 'do_parse_request'), 10, 3);

			// delete cache when status is not publish
			add_filter('posts_results', array($this, 'posts_results'), 10, 2);

			// -> handle html cache
			add_action('wp', array($this, 'wp'), 12); // after sublanguage redirection!

		}

		// add_action('parse_query', function($wp_query) {
		//
		// 	var_dump($wp_query->is_404);
		// 	die();
		// });

		// do_action_ref_array( 'pre_get_posts', array( &$this ) );


		// add_action('posts_request', function($request, $wp_query) {
		// 	echo '<pre>';
		// 	print_r($request);
		// 	// die();
		//
		// 	return $request;
		// }, 10, 2);
		//
		//
		//
		// add_filter('posts_results', function($posts, $wp_query) {
		// 	var_dump('posts_results', $posts);
		//
		// 	return $posts;
		// }, 10, 2);
		//
		//
		// add_filter('posts_pre_query', function($null, $wp_query) {
		// 	var_dump($wp_query->request);
		//
		// 	return $null;
		// }, 10, 2);
		//
		// add_action('the_posts', function($posts, $wp_query) {
		// 	echo '<pre>';
		// 	var_dump('the_posts', $posts);
		//
		// }, 10, 2);

		//
		// apply_filters_ref_array( 'posts_request', array( $this->request, &$this ) );



	}



	/**
	 * @hook 'admin_enqueue_scripts'
	 */
	function admin_enqueue_scripts( $hook ) {
		global $karma;

	  wp_enqueue_script('html-cache', get_template_directory_uri() . '/modules/html-cache/js/html-cache.js', array('task-manager'), $karma->version, true);

	}


	/**
	 * @hook 'init'
	 */
	function create_dependency_tables() {

		if (is_admin()) {

			require_once get_template_directory() . '/admin/table.php';

			Karma_Table::create($this->dependency_table, "
				id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				url varchar(255) NOT NULL,
				object_id int(11) NOT NULL,
				object varchar(10) NOT NULL,
				type varchar(50) NOT NULL,
				context varchar(1) NOT NULL,
				status smallint(1) NOT NULL
			", '002');

		}

	}

	/**
	 * @filter 'do_parse_request'
	 */
	public function do_parse_request($true, $wp, $extra_query_vars) {
		global $karma;

		if ($karma->options->get_option('html_cache')) {

			$wp->query_vars = $_REQUEST;

			// -> for sublanguage filter
			do_action_ref_array( 'parse_request', array( &$this ) );

			return false;

		}

		return $true;
	}

	/**
	 * @filter 'posts_results'
	 */
	public function posts_results($posts, $wp_query) {
		global $karma;

		if ($wp_query->is_main_query() && $karma->options->get_option('html_cache') && is_singular()) {

			foreach ($posts as $post) {

				if ($this->is_post_type_single_cacheable($post->post_type) && $post->post_status !== 'publish') {

					// -> delete post cache
					$this->delete_post_cache($post);

				}

			}

		}

		return $posts;
 	}

	// /**
	//  * get url for resource
	//  */
	// public function get_raw_url() {
	// 	global $wp_query;
	//
	// 	$query_object = get_queried_object();
	//
	// 	$link = '';
	//
	// 	if (is_home()) {
	//
	// 		$link = '';
	//
	// 	} else if (is_page()) {
	//
	// 		$link = '?page_id='.$query_object->ID;
	//
	// 	} else if (is_singular()) {
	//
	// 		$link = '?p='.$query_object->ID.'&post_type='.get_query_var('post_type');
	//
	// 	} else if (is_category() || is_tag() || is_tax()) {
	//
	// 		$link = '?taxonomy='.$query_object->taxonomy.'&term='.$query_object->slug;
	//
	// 	} else if (is_post_type_archive()) {
	//
	// 		$link = '?post_type=' . get_query_var('post_type');
	//
	// 	} else if (is_date()) {
	//
	// 		if (is_day()) {
	//
	// 			$link = '?m=' . get_query_var('year') . zeroise(get_query_var('monthnum'), 2) . zeroise(get_query_var('day'), 2);
	//
	// 		} else if (is_month()) {
	//
	// 			$link = '?m=' . get_query_var('year') . zeroise(get_query_var('monthnum'), 2);
	//
	// 		} else {
	// 			// return get_year_link(get_query_var('year'));
	// 			$link = '?m=' . get_query_var('year');
	//
	// 		}
	//
	// 	}
	//
	// 	 return apply_filters('karma_html_cache_url', $link);
	//
	//  }

	/**
	 * get url for resource
	 */
	public function get_current_query() {

		$queried_object = get_queried_object();

		if (is_home()) {

			$link = '';

		} else if (is_singular()) {

			$link = $this->get_post_query($queried_object);

		} else if (is_category() || is_tag() || is_tax()) {

			$link = $this->get_term_query($queried_object);

		} else if (is_post_type_archive()) {

			$link = $this->get_archive_query(get_query_var('post_type'));

		} else {

			$link = null;

		}

		return $link;

	}

	/**
	 * get query for post
	 */
	public function get_post_query($post) {

		if ($post->post_type === 'page') {

			$link = '?page_id='.$post->ID;

		} else  {

			$link = '?p='.$post->ID.'&post_type='.$post->post_type;

		}

		return $link;

	}

	/**
	 * get query for term
	 */
	public function get_term_query($term) {

		return '?taxonomy='.$term->taxonomy.'&term='.$term->slug;

	}

	/**
	 * get query for archive
	 */
	public function get_archive_query($post_type) {

		return '?post_type='.$post_type;

	}


	/**
 	 * get url for resource
 	 */
 	// public function get_formated_url() {
 	// 	 global $wp_query;
	//
 	// 	 $query_object = get_queried_object();
	//
 	// 	 $link = '';
	//
 	// 	 if (is_home()) {
	//
 	// 		 $link = home_url();
	//
 	// 	 } else if (is_singular() && $this->is_post_type_single_cacheable($query_object->post_type)) {
	//
 	// 		 $link = get_permalink($query_object);
	//
 	// 	 } else if ((is_category() || is_tag() || is_tax()) && $this->is_taxonomy_cacheable($query_object->taxonomy)) {
	//
	// 		 $link = get_term_link($query_object, $query_object->taxonomy);
	//
 	// 	 } else if (is_post_type_archive() && $this->is_post_type_archive_cacheable(get_query_var('post_type'))) {
	//
 	// 		 $link = get_post_type_archive_link(get_query_var('post_type'));
	//
 	// 	 // } else if (is_date()) {
	// 	 //
 	// 		//  if (is_day()) {
	// 	 //
	// 		// 	 $link = get_day_link(get_query_var('year'), get_query_var('monthnum'), get_query_var('day'));
	// 	 //
 	// 		//  } else if (is_month()) {
	// 	 //
	// 		// 	 $link = get_month_link(get_query_var('year'), get_query_var('monthnum'));
	// 	 //
 	// 		//  } else {
	// 	 //
 	// 		// 	 $link = get_year_link(get_query_var('year'));
	// 	 //
 	// 		//  }
	// 	 //
 	// 	 // } else if (is_author()) {
	// 	 //
 	// 		//  $link = get_author_posts_url(get_user_by('slug', get_query_var('author_name'))->ID);
	// 	 //
 	// 	 // } else if (is_search()) {
	// 	 //
 	// 		//  $link = get_search_link(get_search_query());
	//
	//
 	// 	 }
	//
 	// 	 return apply_filters('karma_cache_formated_url', $link);
	//
 	//  }


	/**
 	 * get url for resource
 	 */
 	public function get_current_cache_path() {

		$queried_object = get_queried_object();

		if (is_home() || is_front_page()) {

			$link = ABSPATH;

		} else if (is_singular() && $this->is_post_type_single_cacheable($queried_object->post_type)) {

			$link = $this->get_post_cache_path($queried_object);

		} else if ((is_category() || is_tag() || is_tax()) && $this->is_taxonomy_cacheable($queried_object->taxonomy)) {

			$link = $this->get_term_cache_path($queried_object);

		} else if (is_post_type_archive() && $this->is_post_type_archive_cacheable(get_query_var('post_type'))) {

			$link = $this->get_archive_cache_path(get_query_var('post_type'));

		} else {

			$link = null;

		}

		return $link;

	}

	/**
	 * get url for resource
	 */
	public function get_post_cache_path($post) {

		$link = get_permalink($post);

		$link = str_replace(get_option('home'), rtrim(ABSPATH, '/'), $link);

		return $link;


		// global $wp_rewrite;
		//
		// $link = '';
		//
		// if ($post->post_type === 'page') {
		//
		// 	if (get_option( 'show_on_front' ) !== 'page' || $post->ID !== get_option('page_on_front')) {
		//
		// 		$link = $wp_rewrite->get_page_permastruct();
		//
    //     if ($link) {
		//
    //       $link = str_replace( '%pagename%', get_page_uri($post), $link );
		//
    //     } else {
		//
		// 			$link = get_page_uri($post);
		//
		// 		}
		//
		// 		if ($post->post_status === 'trash') {
		//
		// 			$link = str_replace('__trashed', '', $link);
		//
		// 		}
		//
    // 	}
		//
		// } else if ($post->post_type === 'post') {
		//
		// 	// -> todo
		//
		// } else {
		//
		// 	$link = $wp_rewrite->get_extra_permastruct($post->post_type);
		//
		// 	$post_type_obj = get_post_type_object($post->post_type);
		//
		// 	if ($post_type_obj->hierarchical) {
		//
		// 		$slug = get_page_uri($post);
		//
		// 	} else {
		//
		// 		$slug = $post->post_name;
		//
		// 	}
		//
		// 	if ($link) {
		//
		// 		$link = str_replace("%$post->post_type%", $slug, $link);
		//
		// 	} else {
		//
		// 		$link = $post->post_type.'/'.$slug;
		//
		// 	}
		//
		// 	if ($post->post_status === 'trash') {
		//
		// 		$link = str_replace('__trashed', '', $link);
		//
		// 	}
		//
		// }
		//
		// return ABSPATH.$link;
	}

	/**
	 * get url for resource
	 */
	public function get_archive_cache_path($post_type) {

		$link = get_post_type_archive_link($post_type);

		$link = str_replace(get_option('home'), rtrim(ABSPATH, '/'), $link);

		return $link;

		// global $wp_rewrite;
		//
		// $post_type_obj = get_post_type_object($post_type);
		//
		// $link = $post_type_obj->rewrite['slug'];
		//
    // if ( $post_type_obj->rewrite['with_front'] ) {
		//
    //   $link = $wp_rewrite->front . $link;
		//
    // } else {
		//
    //   $link = $wp_rewrite->root . $link;
		//
    // }
		//
		// return ABSPATH.$link;
	}

	/**
	 * get url for resource
	 */
	public function get_term_cache_path($term) {
		$link = get_term_link($term);

		$link = str_replace(get_option('home'), rtrim(ABSPATH, '/'), $link);

		return $link;

		//
		// global $wp_rewrite;
		//
		// $taxonomy = $term->taxonomy;
		//
    // $termlink = $wp_rewrite->get_extra_permastruct( $taxonomy );
		//
		//
    // $slug = $term->slug;
		//
    // $t = get_taxonomy($taxonomy);
		//
    // if ($t->rewrite['hierarchical']) {
		//
    //   $hierarchical_slugs = array();
    //   $ancestors          = get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );
    //   foreach ( (array) $ancestors as $ancestor ) {
    //       $ancestor_term        = get_term( $ancestor, $taxonomy );
    //       $hierarchical_slugs[] = $ancestor_term->slug;
    //   }
    //   $hierarchical_slugs   = array_reverse( $hierarchical_slugs );
    //   $hierarchical_slugs[] = $slug;
    //   $termlink             = str_replace( "%$taxonomy%", implode( '/', $hierarchical_slugs ), $termlink );
		//
    // } else {
		//
    //   $termlink = str_replace( "%$taxonomy%", $slug, $termlink );
		//
    // }
		//
		// return ABSPATH.$termlink;
	}


	/**
	 * get url for resource
	 */
	public function delete_post_cache($post) {
		global $wpdb;

		$children = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_parent = %d", $post->ID));

		foreach ($children as $child) {

			$this->delete_post_cache($child);

		}

		$path = $this->get_post_cache_path($post);

		if (file_exists($path.'/index.html')) {

			unlink($path.'/index.html');

		}

		if (file_exists($path.'/script.js')) {

			unlink($path.'/script.js');

		}

		if (file_exists($path)) {

			unlink($path);

		}

	}

	/**
	 * @hook wp
	 */
	public function wp($wp) {
		global $karma;

// global $sublanguage;
//
// echo '<pre>';
// var_dump(get_permalink(222));
// die();


		if ($karma->options->get_option('html_cache') && is_user_logged_in()) {

			remove_action( 'template_redirect', 'redirect_canonical' );

			$this->current_query = add_query_arg($_REQUEST, ''); // $this->get_current_query();
			$this->current_path = $this->get_current_cache_path();

			// var_dump($this->get_current_query(), $this->current_query, build_query($_REQUEST));
			// die();
			$q = get_queried_object();
			// var_dump(is_home());
			// die();

			if ($this->current_query && $this->current_path) {


				//
				//
				// $this->resource_link = $this->get_formated_url();
				//
				// if ($this->resource_link) {

			// if (is_home() || is_singular() || is_archive() || is_search() || apply_filters('karma_html_cache_save', false)) {
			//
			// 	if ($karma->options->get_option('html_cache') && !apply_filters('karma_html_cache_disable', false)) {


					add_filter('show_admin_bar','__return_false');

					add_action('wp_print_scripts', array($this, 'dequeue_script'), 100);

					$this->files = array();

					add_action('wp_head', array($this, 'wp_header'));
					add_action('wp_footer', array($this, 'wp_footer'));

					ob_start(array($this, 'save_ob'));

				// }

			} else {



			}

		}

	}

	/**
	 * @callback ob_start
	 */
	public function save_ob($content) {

		// $url = $this->get_current_query();

		$this->save_dependencies($this->current_query);

		$this->files['index.html'] = $content;

		// $this->write_files();

		// $path = $this->get_current_cache_path();

		if (!file_exists($this->current_path)) {

			mkdir($this->current_path, 0777, true);

		}

		foreach ($this->files as $filename => $data) {

			file_put_contents($this->current_path.'/'.$filename, $data);

		}

		return $content;
	}

	/**
	 * Write file
	 */
	// public function write_files() {
	// 	global $sublanguage;
	//
	// 	$cache_info = array();
	//
	// 	$url = $this->resource_link; //$this->get_formated_url();
	//
	// 	$path = str_replace(get_option('home'), rtrim(ABSPATH, '/'), $url);
	// 	$path = rtrim($path, '/') . '/';
	//
	// 	$current_file_info = $path . 'cache-info.json';
	//
	// 	$version = 0;
	//
	// 	if (file_exists($current_file_info)) {
	//
	// 		$current_info = json_decode(file_get_contents($current_file_info));
	//
	// 		if (isset($current_info->version)) {
	//
	// 			$version = intval($current_info->version);
	// 			$version++;
	//
	// 		}
	//
	//
	// 	}
	//
	// 	$cache_info['version'] = $version;
	//
	// 	if ($path !== ABSPATH) {
	//
	// 		$parent = dirname($path);
	// 		$basename = basename($path);
	// 		$parent_file_info = $parent . '/' . 'cache-info.json';
	//
	// 		if (file_exists($parent_file_info)) {
	//
	// 			$parent_info = json_decode(file_get_contents($parent_file_info));
	//
	// 			if (empty($parent_info->dir) || !in_array($basename, $parent_info->dir)) {
	//
	// 				$parent_info->dir[] = $basename;
	//
	// 			}
	//
	// 			file_put_contents($parent_file_info, json_encode($parent_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	//
	// 		}
	//
	// 	}
	//
	// 	$cache_info['dependencies'] = $this->dependencies;
	//
	// 	$cache_info['last-update'] = date('Y-m-d h:s:i');
	//
	// 	if (!file_exists($path)) {
	//
	// 		mkdir($path, 0777, true);
	//
	// 	}
	//
	// 	foreach ($this->files as $filename => $data) {
	//
	// 		file_put_contents($path . $filename, $data);
	//
	// 		$cache_info['files'][] = $filename;
	//
	// 	}
	//
	// 	file_put_contents($path . '/cache-info.json', json_encode($cache_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	//
	// }

	/**
	 * Remove all cache content
	 */
	// public function remove_cache() {
	// 	global $wpdb;
	//
	// 	$table = $wpdb->prefix.$this->dependency_table;
	//
	// 	$wpdb->query("DELETE FROM $table");
	//
	// 	$this->rrmdir(ABSPATH);
	//
	// }

	/**
	 * Remove all cache content
	 */
	// private function rrmdir($path) {
	//
	// 	$path = rtrim($path, '/');
	//
	// 	$file_info = $path . '/cache-info.json';
	//
	// 	if (file_exists($file_info)) {
	//
	// 		$info = json_decode(file_get_contents($file_info));
	//
	// 		if (isset($info->dir) && is_array($info->dir)) {
	//
	// 			foreach ($info->dir as $dir) {
	//
	// 				$child_path = $path . '/' . $dir;
	//
	// 				if (is_dir($child_path)) {
	//
	// 					$this->rrmdir($child_path);
	//
	// 					rmdir($child_path);
	//
	// 				}
	//
	// 			}
	//
	// 		}
	//
	// 		if (isset($info->files) && is_array($info->files)) {
	//
	// 			foreach ($info->files as $file) {
	//
	// 				$file_path = $path . '/' . $file;
	//
	// 				if (file_exists($file_path)) {
	//
	// 					unlink($file_path);
	//
	// 				}
	//
	// 			}
	//
	// 		}
	//
	// 		// var_dump($file_info);
	// 		unlink($file_info);
	//
	// 	}
	//
	// }


	/**
	 * @hook wp_head
	 */
	public function dequeue_script() {
		global $wp_scripts;

		$this->header_scripts = $this->get_all_scripts(0);
		$this->footer_scripts = $this->get_all_scripts(1);

		foreach ($wp_scripts->queue as $script) {

			wp_dequeue_script($script);

		}

	}

	/**
	 * @hook wp_head
	 */
	public function wp_header() {

		$this->print_scripts($this->header_scripts, true);

	}

	/**
	 * @hook wp_footer
	 */
	public function wp_footer() {

		$this->print_scripts($this->footer_scripts, false);

	}




	/**
	 * @hook karma_cache_add_dependency_id
	 */
	public function add_dependency_id($object, $id, $type = null) {

		$this->dependencies[$object][$id] = '';

		if ($type) {

			$this->add_dependency_type($object, $type);

		}

	}

	/**
	 * @hook karma_cache_add_dependency_ids
	 */
	public function add_dependency_ids($object, $ids, $type = null) {

		foreach ($ids as $id) {

			$this->add_dependency_id($object, $id);

		}

		if ($type) {

			$this->add_dependency_type($object, $type);

		}

	}

	/**
	 * @hook karma_cache_add_dependency_type
	 */
	public function add_dependency_type($object, $type, $context = '') {

		$this->dependencies[$object][0] = $type;

	}


	/**
	 * add post_type dependency
	 */
	public function save_dependencies($url) {
		global $wpdb;

		$this->files['dependencies.json'] = json_encode($this->dependencies);

		$table = $wpdb->prefix.$this->dependency_table;

		$dependency_ids = array();

		foreach ($this->dependencies as $object => $object_dependency) {

			foreach ($object_dependency as $id => $type) {

				$dependency_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE url = %s AND object = %s AND id = %d AND type = %s", $url, $object, $id, $type));

				if (!$dependency_id) {

					$wpdb->insert($table, array(
						'url' => $url,
						'object' => $object,
						'object_id' => $id,
						'type' => $type
					), array(
						'%s',
						'%s',
						'%d',
						'%s'
					));

					$dependency_id = $wpdb->insert_id;

				}

				$dependency_ids[] = $dependency_id;

			}

		}

		if ($dependency_ids) {

			$sql_ids = implode(',', array_map('intval', $dependency_ids));

			$wpdb->query($wpdb->prepare("DELETE FROM $table WHERE url = %s AND id NOT IN ($sql_ids)", $url));

		} else {

			$wpdb->query($wpdb->prepare("DELETE FROM $table WHERE url = %s", $url));

		}


	}

	/**
	 * add post_type dependency
	 */
	// public function save_dependencies($url) {
	// 	global $wpdb;
	//
	// 	$wpdb->delete($wpdb->prefix.$this->dependency_table, array(
	// 		'url' => $url,
	// 	), array(
	// 		'%s'
	// 	));
	//
	// 	foreach ($this->dependencies as $object => $object_dependency) {
	//
	// 		if (isset($object_dependency['ids'])) {
	//
	// 			foreach ($object_dependency['ids'] as $id => $nocare) {
	//
	// 				$wpdb->insert($wpdb->prefix.$this->dependency_table, array(
	// 					'url' => $url,
	// 					'object' => $object,
	// 					'object_id' => $id
	// 				), array(
	// 					'%s',
	// 					'%s',
	// 					'%d',
	// 				));
	//
	// 			}
	//
	// 		}
	//
	// 		if (isset($object_dependency['types'])) {
	//
	// 			foreach ($object_dependency['types'] as $type => $context) {
	//
	// 				$wpdb->insert($wpdb->prefix.$this->dependency_table, array(
	// 					'url' => $url,
	// 					'object' => $object,
	// 					'type' => $type,
	// 				), array(
	// 					'%s',
	// 					'%s',
	// 					'%s',
	// 					'%s'
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
	 * @filter 'karma_task'
	 */
	public function add_task($task) {
		global $wpdb, $karma;

		if (empty($task)) {

			$table = $wpdb->prefix.$this->dependency_table;

			$urls = $wpdb->get_col("SELECT DISTINCT url FROM $table WHERE status > 0");

			if ($urls) {

				$items = array();

				foreach ($urls as $url) {

					$items[] = array(
						'url' => $url ? $url : '',
						'action' => 'karma_cache_regenerate_url'
					);

				}

				$task = array(
					'name' => 'HTML Cache',
					'items' => $items
					// 'task' => 'karma_cache_regenerate_url'
				);

			}

		}

		return $task;
	}

	/**
	 * @ajax 'karma_cache_regenerate_url'
	 */
	public function ajax_regenerate_url() {
		global $wpdb;

		$output = array();

		if (isset($_POST['url'])) {

			$url = $_POST['url'];

			$table = $wpdb->prefix.$this->dependency_table;

			$wpdb->update($table, array(
				'status' => 0
			), array(
				'status' => 1,
				'url' => $url
			), array(
				'%d',
				'%d'
			), array(
				'%d'
			));

			$wpdb->delete($table, array(
	 			'status' => 2,
				'url' => $url
	 		), array(
	 			'%d',
				'%s'
	 		));

			// wp_redirect(add_query_arg(array('cache' => '1'), home_url($url)));
			wp_safe_redirect(get_option('home').('/index.php'.$url));

			exit;

		} else {

			$output['error'] = 'url not set';

		}

		echo json_encode($output);
		exit;

	}

	/**
	 * update_post_dependency on saving a cacheable post
	 */
	function update_object_dependency($url, $id, $object, $type, $delete = false) {
		global $wpdb;

		$table = $wpdb->prefix.$this->dependency_table;

		$dependency_id = $wpdb->get_var($wpdb->prepare(
			"SELECT id FROM $table
			WHERE url = %s AND object = %s AND (object_id = %d OR type = %s)",
			$url,
			$object,
			$id,
			$type
		));

		if ($dependency_id) {

			$wpdb->update($table, array(
				'status' => $delete ? 2 : 1
			), array(
				'id' => $dependency_id
			), array(
				'%d'
			), array(
				'%d'
			));

		} else if (!$dependency_id) {

			$wpdb->insert($table, array(
				'url' => $url,
				'object' => $object,
				'object_id' => $id,
				'status' => $delete ? 2 : 1
			), array(
				'%s',
				'%s',
				'%d',
				'%d'
			));

		}

	}
	//
	// /**
	//  * update_term_dependency
	//  */
	// function update_term_dependency($url, $term, $delete = false) {
	// 	global $wpdb;
	//
	// 	$table = $wpdb->prefix.$this->dependency_table;
	//
	// 	$dependency_id = $wpdb->get_var($wpdb->prepare(
	// 		"SELECT id FROM $table
	// 		WHERE url = %s AND object_id = %d AND object = %s",
	// 		$url,
	// 		$term->term_id,
	// 		'term'
	// 	));
	//
	// 	if ($dependency_id) {
	//
	// 		$wpdb->update($table, array(
	// 			'status' => $delete ? 2 : 1
	// 		), array(
	// 			'id' => $dependency_id
	// 		), array(
	// 			'%d'
	// 		), array(
	// 			'%d'
	// 		));
	//
	// 	} else if (!$dependency_id) {
	//
	// 		$wpdb->insert($table, array(
	// 			'url' => $url,
	// 			'object' => 'term',
	// 			'object_id' => $term->term_id,
	// 			'status' => $delete ? 2 : 1
	// 		), array(
	// 			'%s',
	// 			'%s',
	// 			'%d',
	// 			'%d'
	// 		));
	//
	// 	}
	//
	// }
	//



	/**
	 * @hook 'save_post'
	 */
	function save_post($post_id, $post, $update) {

		if ($this->is_post_type_single_cacheable($post->post_type)) {

			if ($post->post_type === 'page') {

				$url = '?page_id='.$post->ID;

			} else {

				$url = '?p='.$post->ID.'&post_type='.$post->post_type;

			}

			$this->update_object_dependency($url, $post->ID, 'post', $post->post_type);

		}

		if ($this->is_post_type_archive_cacheable($post->post_type)) {

			$url = '?post_type='.$post->post_type;

			$this->update_object_dependency($url, $post->ID, 'post', $post->post_type);

		}


		if ($update) {

			$this->update_object('post', $post->post_type, $post_id);

		} else {

			$this->create_object('post', $post->post_type, $post_id);
		}

	}

	/**
	 * @hook 'before_delete_post'
	 */
	function before_delete_post($post_id) {

		$post = get_post($post_id);

		// -> need to remove all dependencies to self and delete page cache now!


		$this->delete_object('post', $post->post_type, $post_id);

	}

	/**
	 * @hook 'edit_term'
	 */
	function edit_term($term_id, $tt_id, $taxonomy) {

		if ($this->is_taxonomy_cacheable($taxonomy)) {

			$term = get_term($term_id, $taxonomy);

			$url = '?taxonomy='.$taxonomy.'&term='.$term->slug;

			$this->update_object_dependency($url, $term->term_id, 'term', $taxonomy);

		}

		$this->update_object('term', $taxonomy, $term_id);

	}

	/**
	 * @hook 'create_term'
	 */
	function create_term($term_id, $tt_id, $taxonomy) {

		if ($this->is_taxonomy_cacheable($taxonomy)) {

			$term = get_term($term_id, $taxonomy);

			$url = '?taxonomy='.$taxonomy.'&term='.$term->slug;

			$this->update_object_dependency($url, $term->term_id, 'term', $taxonomy);

		}

		$this->create_object('term', $taxonomy, $term_id);

	}

	/**
	 * @hook 'pre_delete_term'
	 */
	function pre_delete_term($term, $taxonomy) {

		$this->delete_object('term', $taxonomy, $term->term_id);
	}


	/**
	 * @hook 'karma_cache_create_object'
	 */
	function create_object($object, $type, $id) {
		global $wpdb;

		$table = $wpdb->prefix.$this->dependency_table;

		$wpdb->query($wpdb->prepare(
			"UPDATE $table
			SET status = %d
			WHERE object = %s AND type = %s",
			1,
			$object,
			$type
		));

	}

	/**
	 * @hook 'karma_cache_update_object'
	 */
	function update_object($object, $type, $id) {
		global $wpdb;

		$table = $wpdb->prefix.$this->dependency_table;

		$wpdb->query($wpdb->prepare(
			"UPDATE $table
			SET status = %d
			WHERE object = %s AND (object_id = %d OR type = %s)",
			1,
			$object,
			$id,
			$type
		));

	}

	/**
	 * @hook 'karma_cache_delete_object'
	 */
	function delete_object($object, $type, $id) {
		global $wpdb;

		$table = $wpdb->prefix.$this->dependency_table;

		$wpdb->query($wpdb->prepare(
			"UPDATE $table
			SET status = %d
			WHERE object = %s AND (object_id = %d OR type = %s)",
			2,
			$object,
			$id,
			$type
		));


	}

	/**
	 * @hook 'karma_print_options'
	 */
	public function print_options() {
		global $karma;

		$html_cache = $karma->options->get_option('html_cache');

		include get_template_directory() . '/modules/html-cache/include/options.php';

	}

	/**
	 * @hook 'karma_save_options'
	 */
	public function save_options($options) {
		global $karma;

		$html_cache = isset($_POST['html_cache']) && $_POST['html_cache'] ? 1 : 0;

		// require_once get_template_directory() . '/modules/html-cache/class-mod-rewrite.php';
		//
		// $mod_rewrite = new Karma_Cache_Mod_Rewrite();
		//
		if ($karma->options->get_option('html_cache')) {

			if (!$html_cache) {

				$this->remove_cache();

				//$mod_rewrite->remove();

			}

		} else {

			if ($html_cache) {

				// $mod_rewrite->add();

			}

		}

		$karma->options->update_option('html_cache', $html_cache);

	}

	/**
	 * Erase html cache
	 */
	public function erase_html_cache() {

		$this->file_manager->erase_dir();

	}

	/**
	 * print_scripts
	 */
	public function print_scripts($scripts, $internal = false) {
		global $wp_scripts;

		$key = implode(',', $scripts);
		// $js_filename = md5($key) . '.js';
		$l10n = '';
		$js = '';

		foreach ($scripts as $handle) {

			if (isset($wp_scripts->registered[$handle]->extra['data'])) {

				$l10n .= $wp_scripts->registered[$handle]->extra['data'] . "\n";

			}

		}

		if ($l10n) {

			echo '<script type="text/javascript">'."\n".$l10n."\n".'</script>'."\n";

		}

		foreach ($scripts as $handle) {

			$script_src = $wp_scripts->registered[$handle]->src;

			if (strpos($script_src, WP_CONTENT_URL) === 0) {

				$script_file = str_replace(WP_CONTENT_URL, WP_CONTENT_DIR, $script_src);

				$js .= "\n/*** $handle ***/\n" . file_get_contents($script_file) . "\n";

			} else {

				echo '<script type="text/javascript" src="'.$script_src.'"></script>';

			}

		}

		if ($js) {

			//require_once get_template_directory() . '/modules/html-cache/jshrink/minifier.php';
			// $js = JShrink\Minifier::minify($js);

			if ($internal) {

				echo '<script type="text/javascript">'."\n".$js."\n".'</script>';

			} else {

				// $this->file_manager->write_file('js', $js_filename, $js);

				// $this->file_manager->write_file('html/'.$this->dir, 'script.js', $js);

				$this->files['script.js'] = $js;

				echo '<script type="text/javascript" src="script.js"></script>';

			}

		}

	}

	/**
	 * get scripts
	 */
	public function get_all_scripts($in_footer) {
		global $wp_scripts;

		$deps_keys = array();

		foreach ($wp_scripts->queue as $script) {

			if ($in_footer == (isset($wp_scripts->registered[$script]->extra['group']) && $wp_scripts->registered[$script]->extra['group'] === 1)) {

				$deps_keys = array_merge($deps_keys, $this->get_script_deps($script));

			}

		}

		return array_keys($deps_keys);
	}

	/**
	 * get script deps
	 */
	public function get_script_deps($script) {
		global $wp_scripts;

		$deps_keys = array();

		if (isset($wp_scripts->registered[$script]->deps) && $wp_scripts->registered[$script]->deps) {

			foreach ($wp_scripts->registered[$script]->deps as $child) {

				$deps_keys = array_merge($deps_keys, $this->get_script_deps($child));

			}

		}

		$deps_keys[$script] = true;

		return $deps_keys;
	}

	/**
	 * is_post_type_single_cacheable
	 */
	public function is_post_type_single_cacheable($post_type) {

		$post_type_obj = get_post_type_object($post_type);

		return apply_filters('karma_htmlcache_single_post_type', $post_type_obj->publicly_queryable && $post_type_obj->rewrite || $post_type === 'page', $post_type);

	}

	/**
	 * is_post_type_archive_cacheable
	 */
	public function is_post_type_archive_cacheable($post_type) {

		$post_type_obj = get_post_type_object($post_type);

		return apply_filters('karma_htmlcache_archive_post_type', $post_type_obj->has_archive, $post_type);

	}

	/**
	 * is_taxonomy_cacheable
	 */
	public function is_taxonomy_cacheable($taxonomy) {

		$taxonomy_obj = get_taxonomy($taxonomy);

		return apply_filters('karma_htmlcache_taxonomy', $taxonomy_obj->publicly_queryable && $taxonomy_obj->rewrite, $taxonomy);

	}

	/**
	 * rebuild cache
	 */
	// public function get_cacheable_post_types() {
	//
	// 	$post_types = apply_filters('karma_htmlcache_single_post_types', array_merge(array(
	// 		'page',
	// 		'post'
	// 	), get_post_types(array(
	// 		'publicly_queryable' => true,
	// 		'rewrite' => true
	// 	))));
	//
	// 	$post_types = array_filter(array_merge(array(
	// 		'page',
	// 		'post'
	// 	), get_post_types(array(
	// 		'publicly_queryable' => true,
	// 		'rewrite' => true
	// 	))), array($this, 'is_post_type_cacheable'));
	//
	// 	return apply_filters('karma_htmlcache_single_post_type', true, $post_type);
	//
	// }

	/**
	 * rebuild cache
	 */
	public function get_all_resources() {
		global $wpdb;

		$items = array();

		$items[] = array(
			'url' => '',
			'action' => 'karma_cache_regenerate_url'
		);

		$post_types = array_filter(get_post_types(), array($this, 'is_post_type_single_cacheable'));

		if ($post_types) {

			$post_types_sql = implode("','", array_map('esc_sql', $post_types));

			$results = $wpdb->get_results("SELECT ID, post_type FROM $wpdb->posts
				WHERE post_type IN ('$post_types_sql') AND post_status = 'publish'");

			foreach ($results as $result) {

				$items[] = array(
					'url' => $this->get_post_query($result),
					'action' => 'karma_cache_regenerate_url'
				);

			}

		}

		$post_types = array_filter(get_post_types(), array($this, 'is_post_type_archive_cacheable'));

		if ($post_types) {

			foreach ($post_types as $post_type) {

				$items[] = array(
					'url' => $this->get_archive_query($post_type),
					'action' => 'karma_cache_regenerate_url'
				);

			}

		}

		$taxonomies = array_filter(get_taxonomies(), array($this, 'is_taxonomy_cacheable'));

		if ($taxonomies) {

			$taxonomies_sql = implode("','", array_map('esc_sql', $taxonomies));

			$terms = $wpdb->get_results("SELECT tt.taxonomy, t.slug FROM $wpdb->term_taxonomy AS tt
				JOIN $wpdb->terms AS t ON (t.term_id = tt.term_id)
				WHERE tt.taxonomy IN ('$taxonomies_sql')");

			foreach ($terms as $term) {

				$items[] = array(
					'url' => $this->get_term_query($term),
					'action' => 'karma_cache_regenerate_url'
				);

			}

		}

		$items = apply_filters('karma_htmlcache_items_to_update', $items);

		// echo '<pre>';
		// print_r($items);
		// die();

		return $items;
	}

	/**
	 * @filter 'karma_rebuild_all_task'
	 */
	public function rebuild_all_task($task) {
		global $karma;

		if (empty($task)) {

			if ($karma->options->get_option('htmlcache_rebuild', false)) {

				$task = array(
					'name' => 'HTML Rebuild All Cache',
					'items' => $this->get_all_resources()
				);

				$karma->options->update_option('htmlcache_rebuild', false);
			}

		}

		return $task;
	}

	/**
	 * @ajax 'karma_htmlcache_flush'
	 */
	public function ajax_flush() {
		global $karma;

		$output = array();

	//	$this->remove_cache();
		$karma->options->update_option('htmlcache_rebuild', true);


		// $table = $wpdb->prefix.$this->dependency_table;
		//
		// $wpdb->query($wpdb->prepare(
		// 	"UPDATE $table SET status = %d",
		// 	1
		// ));

		echo json_encode($output);
		exit;

	}

	/**
	 * @callbak 'admin_bar_menu'
	 */
	public function add_toolbar_button( $wp_admin_bar ) {
		global $karma;

		if ($karma->options->get_option('html_cache') && current_user_can('manage_options')) {

			$wp_admin_bar->add_node(array(
				'id'    => 'update-html-cache',
				'title' => 'Update HTML Cache',
				'href'  => '#',
				'meta'  => array(
					'onclick' => 'ajaxGet(KarmaTaskManager.ajax_url, {action: "karma_htmlcache_flush"}, function(results) {KarmaTaskManager.update();});event.preventDefault();'
				)
			));

		}

	}

	/**
	 * @filter 'mod_rewrite_rules'
	 */
	// public function mod_rewrite_rules($rules) {
	// 	global $wp_rewrite;
	//
	// 	// $home_root = parse_url(home_url());
	// 	//
  //   // if (isset($home_root['path'])) {
	// 	//
  //   //   $home_root = trailingslashit($home_root['path']);
	// 	//
  //   // } else {
	// 	//
  //   //   $home_root = '/';
	// 	//
  //   // }
	//
	// 	$original_rules = "RewriteCond %{REQUEST_FILENAME} !-d";
	//
	// 	$new_rules = "RewriteCond %{REQUEST_FILENAME} !-d [OR]\n".
	// 	"RewriteCond %{QUERY_STRING} >''";
	//
	// 	$rules = str_replace($original_rules, $new_rules, $rules);
	//
	// 	return $rules;
	// }

}

global $karma_cache;
$karma_cache = new Karma_Cache();

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

		add_action('save_post', array($this, 'save_post'), 10, 3);
		add_action('before_delete_post', array($this, 'before_delete_post'), 10);
		add_action('edit_term', array($this, 'edit_term'), 10, 3);
		add_action('create_term', array($this, 'create_term'), 10, 3);
		add_action('pre_delete_term', array($this, 'pre_delete_term'), 10, 2);

		add_action('karma_cluster_create_object', array($this, 'create_object'), 10, 3);
		add_action('karma_cluster_update_object', array($this, 'update_object'), 10, 3);
		add_action('karma_cluster_delete_object', array($this, 'delete_object'), 10, 3);

		add_action('wp_ajax_karma_cache_regenerate_url', array($this, 'ajax_regenerate_url'));
		add_action('wp_ajax_karma_cache_update_all', array($this, 'ajax_update_all'));

		if (is_admin()) {

			// -> handle admin option page
			add_action('init', array($this, 'create_dependency_tables'), 9);

			add_action('karma_save_options', array($this, 'save_options'));
			add_action('karma_print_options', array($this, 'print_options'));

			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

			add_action( 'admin_bar_menu', array($this, 'add_toolbar_button'), 999);

		} else {

			// -> handle html cache
			add_action('wp', array($this, 'wp'), 12); // after sublanguage redirection!

		}

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
	 * get url for resource
	 */
	 public function get_raw_url() {
		 global $wp_query;

		 $query_object = get_queried_object();

		 $link = '';

		 if (is_home()) {

			 $link = '';

		 } else if (is_singular() || $wp_query->is_posts_page) {

			 $link = '?p='.$query_object->ID;

		 } else if (is_category() || is_tag() || is_tax()) {

			 $link = '?taxonomy='.$query_object->taxonomy.'&term='.$query_object->slug;

		 } else if (is_post_type_archive()) {

			 $link = '?post_type=' . get_query_var('post_type');

		 } else if (is_date()) {

			 if (is_day()) {

				 $link = '?m=' . get_query_var('year') . zeroise(get_query_var('monthnum'), 2) . zeroise(get_query_var('day'), 2);

			 } else if (is_month()) {

				 $link = '?m=' . get_query_var('year') . zeroise(get_query_var('monthnum'), 2);

			 } else {
				 // return get_year_link(get_query_var('year'));
				 $link = '?m=' . get_query_var('year');

			 }

		 } else if (is_author()) {

			 $link = '?author=' . get_user_by('slug', get_query_var('author_name'))->ID;

		 } else if (is_search()) {

			 $link = '?s=' . urlencode(get_search_query());

		 } else {

			 // ??

		 }

		 return apply_filters('karma_html_cache_url', $link);

	 }

	 /**
 	 * get url for resource
 	 */
 	 public function get_formated_url() {
 		 global $wp_query;

 		 $query_object = get_queried_object();

 		 $link = '';

 		 if (is_home()) {

 			 $link = home_url();

 		 } else if (is_singular() || $wp_query->is_posts_page) {

 			 $link = get_permalink($query_object);

 		 } else if (is_category() || is_tag() || is_tax()) {

			 $link = get_term_link($query_object, $query_object->taxonomy);

 		 } else if (is_post_type_archive()) {

 			 $link = get_post_type_archive_link(get_query_var('post_type'));

 		 } else if (is_date()) {

 			 if (is_day()) {

				 $link = get_day_link(get_query_var('year'), get_query_var('monthnum'), get_query_var('day'));

 			 } else if (is_month()) {

				 $link = get_month_link(get_query_var('year'), get_query_var('monthnum'));

 			 } else {

 				 $link = get_year_link(get_query_var('year'));

 			 }

 		 } else if (is_author()) {

 			 $link = get_author_posts_url(get_user_by('slug', get_query_var('author_name'))->ID);

 		 } else if (is_search()) {

 			 $link = get_search_link(get_search_query());

 		 } else {

 			 // ??

 		 }

 		 return apply_filters('karma_cache_formated_url', $link);

 	 }


	// public function get_url() {
	// 	global $wp_query;
	//
	// 	$query_object = get_queried_object();
	//
	// 	if (is_home()) {
	//
	// 		return home_url();
	//
	// 	} else if (is_singular() || $wp_query->is_posts_page) {
	//
	// 		return get_permalink($query_object->ID);
	//
	// 	} else if (is_category() || is_tag() || is_tax()) {
	//
	// 		$original_term = get_term($query_object->term_id, $query_object->taxonomy);
	//
	// 		return get_term_link($original_term, $original_term->taxonomy);
	//
	// 	} else if (is_post_type_archive()) {
	//
	// 		return get_post_type_archive_link(get_post_type());
	//
	// 	} else if (is_date()) {
	//
	// 		if (is_day())
	// 			return get_day_link(get_query_var('year'), get_query_var('monthnum'), get_query_var('day'));
	// 		else if (is_month())
	// 			return get_month_link(get_query_var('year'), get_query_var('monthnum'));
	// 		else if (is_year())
	// 			return get_year_link(get_query_var('year'));
	// 		else
	// 			return home_url('/');
	//
	// 	} else if (is_author()) {
	//
	// 		return get_author_posts_url(get_user_by('slug', get_query_var('author_name'))->ID);
	//
	// 	} else if (is_search()) {
	//
	// 		return get_search_link( get_search_query() );
	//
	// 	}
	//
	// 	return apply_filters('karma_html_cache_url', false);
	//
	// }

	/**
	 * @hook wp
	 */
	public function wp($wp) {
		global $karma;

		if (is_home() || is_singular() || is_archive() || is_search() || apply_filters('karma_html_cache_save', false)) {

			if ($karma->options->get_option('html_cache') && !apply_filters('karma_html_cache_disable', false)) {

// apply_filters( 'redirect_canonical', $redirect_url, $requested_url );

				remove_action( 'template_redirect', 'redirect_canonical' );
				add_filter('show_admin_bar','__return_false');

				// add_filter('redirect_canonical', function($redirect_url, $requested_url) {
				//
				// 	var_dump($redirect_url, $requested_url);
				// 	die();
				//
				// 	return $redirect_url;
				// }, 10, 2);


// 				if (empty($_GET['cache'])) {
//
// // echo '<pre>';
// // 					var_dump($wp);
// // 					die();
//
// 					// wp_redirect(add_query_arg(array('cache' => 1), get_option('home') . $this->get_raw_url()));
// 					// wp_redirect(add_query_arg(array('cache' => 1), home_url($this->get_raw_url())));
// 					wp_redirect(add_query_arg(array('cache' => 1), get_option('home').'/'.$wp->request));
// 					exit;
//
// 				}



				// $raw_url = get_option('home') . $this->get_raw_url();

				// if ($url && strpos($url, $_SERVER['REQUEST_URI']) === false) {
				//
				// 	// global $sublanguage;
				// 	//
				// 	// $sublanguage->set_language($sublanguage->get_main_language());
				// 	//
				// 	// var_dump($sublanguage->get_language());
				//
				//
				//
				// }

				add_action('wp_print_scripts', array($this, 'dequeue_script'), 100);

				// $parts = explode('?', $_SERVER['REQUEST_URI']);
				// $this->dir = trim($parts[0], '/');
				//$this->request_url = $wp->request;
				$this->files = array();


				add_action('wp_head', array($this, 'wp_header'));
				add_action('wp_footer', array($this, 'wp_footer'));

				ob_start(array($this, 'save_ob'));

			}

		}

	}

	/**
	 * @callback ob_start
	 */
	public function save_ob($content) {

		$url = $this->get_raw_url();

		$this->save_dependencies($url);

		$this->files['index.html'] = $content;
		$this->files['dependencies.json'] = json_encode($this->dependencies, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

		// file_put_contents(ABSPATH . '/' . $this->request_url . '/index.html', $content);
		// file_put_contents(ABSPATH . '/' . $this->request_url . '/dependencies.json', json_encode($this->dependencies, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));


		// $this->file_manager->write_file('html/'.$this->dir, 'dependencies.json', json_encode($this->dependencies, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
		// $this->file_manager->write_file('html/'.$this->dir, 'index.html', $content);

		$this->write_files();

		return $content;
	}

	/**
	 * Write file
	 */
	public function write_files() {
		global $sublanguage;

		$cache_info = array();

		$url = $this->get_formated_url();

		$path = str_replace(get_option('home'), rtrim(ABSPATH, '/'), $url);
		$path = rtrim($path, '/') . '/';

		$current_file_info = $path . 'cache-info.json';

		$version = 0;

		if (file_exists($current_file_info)) {

			$current_info = json_decode(file_get_contents($current_file_info));

			if (isset($current_info->version)) {

				$version = intval($current_info->version);
				$version++;

			}


		}

		$cache_info['version'] = $version;

		if ($path !== ABSPATH) {

			$parent = dirname($path);
			$basename = basename($path);
			$parent_file_info = $parent . '/' . 'cache-info.json';

			if (file_exists($parent_file_info)) {

				$parent_info = json_decode(file_get_contents($parent_file_info));

				if (empty($parent_info->dir) || !in_array($basename, $parent_info->dir)) {

					$parent_info->dir[] = $basename;

				}

				file_put_contents($parent_file_info, json_encode($parent_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

			}

		}

		$cache_info['dependencies'] = $this->dependencies;

		$cache_info['last-update'] = date('Y-m-d h:s:i');

		if (!file_exists($path)) {

			mkdir($path, 0777, true);

		}

		foreach ($this->files as $filename => $data) {

			file_put_contents($path . $filename, $data);

			$cache_info['files'][] = $filename;

		}

		file_put_contents($path . '/cache-info.json', json_encode($cache_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));


	}


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

		$this->dependencies[$object]['ids'][$id] = true;

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

		$this->dependencies[$object]['types'][$type] = $context;

	}


	/**
	 * @hook karma_cache_add_post_dependency
	 */
	// public function add_post_dependency($id) {
	//
	// 	// $this->post_dependency_ids[] = $id;
	// 	$this->add_dependency($id, 'post', 'id');
	//
	// }
	//
	// /**
	//  * @hook karma_cache_add_type_dependency
	//  */
	// public function add_type_dependency($post_type) {
	//
	// 	// $this->type_dependencies[] = $id;
	// 	$this->add_dependency($post_type, 'post', 'type');
	//
	// }
	//
	// /**
	//  * @hook karma_cache_add_post_dependency
	//  */
	// public function add_term_dependency($id) {
	//
	// 	// $this->term_dependency_ids[] = $post_type;
	// 	$this->add_dependency($id, 'term', 'id');
	//
	// }
	//
	// /**
	//  * @hook karma_cache_add_type_dependency
	//  */
	// public function add_taxonomy_dependency($taxonomy) {
	//
	// 	// $this->taxonomy_dependencies[] = $taxonomy;
	// 	$this->add_dependency($taxonomy, 'term', 'type');
	//
	// }


	/**
	 * add post_type dependency
	 */
	public function save_dependencies($url) {
		global $wpdb;

		$wpdb->delete($wpdb->prefix.$this->dependency_table, array(
			'url' => $url,
		), array(
			'%s'
		));

		foreach ($this->dependencies as $object => $object_dependency) {

			if (isset($object_dependency['ids'])) {

				foreach ($object_dependency['ids'] as $id => $nocare) {

					$wpdb->insert($wpdb->prefix.$this->dependency_table, array(
						'url' => $url,
						'object' => $object,
						'object_id' => $id
					), array(
						'%s',
						'%s',
						'%d',
					));

				}

			}

			if (isset($object_dependency['types'])) {

				foreach ($object_dependency['types'] as $type => $context) {

					$wpdb->insert($wpdb->prefix.$this->dependency_table, array(
						'url' => $url,
						'object' => $object,
						'type' => $type,
						'context' => $context
					), array(
						'%s',
						'%s',
						'%s',
						'%s'
					));

				}

			}

		}

		// if (isset($this->dependencies['type'])) {
		//
		// 	foreach ($this->dependencies['type'] as $object => $dependency_types) {
		//
		// 		foreach ($dependency_types as $type) {
		//
		// 			$wpdb->insert($wpdb->prefix.$this->dependency_table, array(
		// 				'url' => $url,
		// 				'object' => $object,
		// 				'type' => $type
		// 			), array(
		// 				'%s',
		// 				'%s',
		// 				'%s'
		// 			));
		//
		// 		}
		//
		// 	}
		//
		// }
		//
		// if (isset($this->dependencies['id'])) {
		//
		// 	foreach ($this->dependencies['id'] as $object => $dependency_ids) {
		//
		// 		foreach ($dependency_ids as $id) {
		//
		// 			$wpdb->insert($wpdb->prefix.$this->dependency_table, array(
		// 				'url' => $url,
		// 				'object' => $object,
		// 				'object_id' => $id
		// 			), array(
		// 				'%s',
		// 				'%s',
		// 				'%d'
		// 			));
		//
		// 		}
		//
		// 	}
		//
		// }

	}


	/**
	 * add post_type dependency
	 */
	// public function delta_dependencies($url) {
	// 	global $wpdb;
	//
	// 	$current_dependencies = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$this->dependency_table}
	// 		WHERE url = %s",
	// 		$url
	// 	));
	//
	// 	if (isset($this->dependencies['type'])) {
	//
	// 		foreach ($this->dependencies['type'] as $object => $dependency_types) {
	//
	// 			foreach ($dependency_types as $type) {
	//
	// 				$wpdb->insert($wpdb->prefix.$this->dependency_table, array(
	// 					'url' => $url,
	// 					'object' => $object,
	// 					'type' => $type
	// 				), array(
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
	// 	if (isset($this->dependencies['id'])) {
	//
	// 		foreach ($this->dependencies['id'] as $object => $dependency_ids) {
	//
	// 			foreach ($dependency_ids as $id) {
	//
	// 				$wpdb->insert($wpdb->prefix.$this->dependency_table, array(
	// 					'url' => $url,
	// 					'object' => $object,
	// 					'object_id' => $id
	// 				), array(
	// 					'%s',
	// 					'%s',
	// 					'%d'
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
	public function add_task($tasks) {
		global $wpdb, $karma;

		//$urls = $karma->options->get_option('karma_cache_expired_url');

		// if ($urls) {
		//
		// 	$items = array();
		//
		// 	foreach ($urls as $url) {
		//
		// 		$items[] = array(
		// 			'url' => $url
		// 		);
		//
		// 	}
		//
		// 	$tasks[] = array(
		// 		'name' => 'HTML Cache',
		// 		'items' => $items,
		// 		'task' => 'karma_cache_regenerate_url'
		// 	);
		//
		// }

		$table = $wpdb->prefix.$this->dependency_table;

		// $dependency_ids = $wpdb->get_col("SELECT id FROM $table WHERE status > 0");

		$urls = $wpdb->get_col("SELECT DISTINCT url FROM $table WHERE status > 0");


		if ($urls) {

			$items = array();

			foreach ($urls as $url) {

				$items[] = array(
					'url' => $url ? $url : '',
					'action' => 'karma_cache_regenerate_url'
				);

			}

			$tasks[] = array(
				'name' => 'HTML Cache',
				'items' => $items,
				'task' => 'karma_cache_regenerate_url'
			);

		}

		return $tasks;
	}

	/**
	 * @ajax 'karma_cache_regenerate_url'
	 */
	 public function ajax_regenerate_url() {
		 global $wpdb;

		 $output = array();

		 if (isset($_POST['url'])) {

			$table = $wpdb->prefix.$this->dependency_table;

			$url = $_POST['url'];

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


			// $dependency_id = $_POST['url'];
			//
			// $row = $wpdb->get_row($wpdb->prepare(
			//  "SELECT url, status FROM $table WHERE id = %d",
			//  $dependency_id
			// ));
			//
			// if ($row->status === '1') {
			//
			// 	$wpdb->update($table, array(
			// 		'status' => 0
			// 	), array(
			// 		'id' => $dependency_id,
			// 	), array(
			// 		'%d'
			// 	), array(
			// 		'%d'
			// 	));
			//
			// } else if ($row->status === '2') {
			//
			// 	$wpdb->delete($table, array(
		 	// 		'id' => $dependency_id,
		 	// 	), array(
		 	// 		'%d'
		 	// 	));
			//
			// }


			// wp_redirect(add_query_arg(array('cache' => '1'), home_url($url)));
			wp_safe_redirect(get_option('home').('/index.php'.$url));

			exit;


			 // $base = trim(substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'wp-admin/admin-ajax.php')), '/'); //: "/contrechamps/wp-admin/admin-ajax.php"
			 //
			 // $output['base'] = $base;

 // 			$this->file_manager->erase_dir('html/'.$base.'/'.$url, 'index.html');
 //
 // 			// $output['erase_dir'] = 'html/'.$base.'/'.$url. '/index.html';
 // 			// $output['server'] = $_SERVER;
 //
 // // ob_start();
 // // 			$ch = curl_init();
 // // 			curl_setopt($ch, CURLOPT_URL, trim($full_url).'/cache=1');
 // // 			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 // // 			curl_exec($ch);
 // // 			curl_close($ch);
 // //
 // // $output['ob'] = ob_get_clean();
 //
			 // $this->remove_expired_url($url);

			 // header("Location: $full_url");
			 // die();

			 // $output['dependency_id'] = $dependency_id;
			 // $output['url'] = $row->url;
			 // $output['full_url'] = $full_url;

		 } else {

			 $output['error'] = 'url not set';

		 }

		 echo json_encode($output);
		 exit;

	 }

// 	public function ajax_regenerate_url() {
// 		global $karma;
//
// 		$output = array();
//
// 		if (isset($_POST['url'])) {
//
// 			$url = $_POST['url'];
// 			$full_url = home_url($url);
//
// 			$base = trim(substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'wp-admin/admin-ajax.php')), '/'); //: "/contrechamps/wp-admin/admin-ajax.php"
//
// 			$output['base'] = $base;
//
// // 			$this->file_manager->erase_dir('html/'.$base.'/'.$url, 'index.html');
// //
// // 			// $output['erase_dir'] = 'html/'.$base.'/'.$url. '/index.html';
// // 			// $output['server'] = $_SERVER;
// //
// // // ob_start();
// // // 			$ch = curl_init();
// // // 			curl_setopt($ch, CURLOPT_URL, trim($full_url).'/cache=1');
// // // 			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// // // 			curl_exec($ch);
// // // 			curl_close($ch);
// // //
// // // $output['ob'] = ob_get_clean();
// //
// 			$this->remove_expired_url($url);
//
// 			// header("Location: $full_url");
// 			// die();
//
// 			$output['url'] = $url;
// 			$output['full_url'] = trim($full_url, '/') . '/';
//
// 		} else {
//
// 			$output['error'] = 'url not set';
//
// 		}
//
// 		echo json_encode($output);
// 		exit;
//
// 	}

	/**
	 *  add_expired_urls
	 */
	// function add_expired_urls($urls) {
	// 	global $karma;
	//
	// 	$karma->options->update_option('karma_cache_expired_url', array_merge(
	// 		$karma->options->get_option('karma_cache_expired_url', array()),
	// 		$urls
	// 	));
	//
	// }
	//
	// /**
	//  * remove_expired_url
	//  */
	// function remove_expired_url($url) {
	// 	global $karma;
	//
	// 	$urls = $karma->options->get_option('karma_cache_expired_url', array());
	//
	// 	$index = array_search($url, $urls);
	//
	// 	if ($index !== null) {
	//
	// 		array_splice($urls, $index, 1);
	//
	// 	}
	//
	// 	$karma->options->update_option('karma_cache_expired_url', $urls);
	//
	// }


	/**
	 * @hook 'save_post'
	 */
	function save_post($post_id, $post, $update) {
		// global $wpdb;
		//
		// $table = $wpdb->prefix.$this->dependency_table;
		//
		// if ($update) {
		//
		// 	$urls = $wpdb->get_col($wpdb->prepare("SELECT url FROM $table WHERE object = 'post' AND object_id = %d", $post_id));
		//
		// } else {
		//
		// 	$urls = $wpdb->get_col($wpdb->prepare("SELECT url FROM $table WHERE object = 'post' AND type = %s", $post->post_type));
		//
		// }
		//
		// $this->add_expired_urls($urls);

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
		// global $wpdb;
		//
		// $table = $wpdb->prefix.$this->dependency_table;
		//
		// $urls = $wpdb->get_col($wpdb->prepare("SELECT url FROM $table WHERE object = 'post' AND object_id = %d", $post_id));
		//
		// $wpdb->delete($table, array(
		// 	'object' => 'post',
		// 	'object_id' => $post_id
		// ), array(
		// 	'%s',
		// 	'%d'
		// ));
		//
		// $this->add_expired_urls($urls);

		$post = get_post($post_id);

		$this->delete_object('post', $post->post_type, $post_id);

	}

	/**
	 * @hook 'edit_term'
	 */
	function edit_term($term_id, $tt_id, $taxonomy) {
		// global $wpdb;
		//
		// $table = $wpdb->prefix.$this->dependency_table;
		//
		// $urls = $wpdb->get_col($wpdb->prepare("SELECT url FROM $table WHERE object = 'term' AND object_id = %d", $term_id));
		//
		// $this->add_expired_urls($urls);

		$this->update_object('term', $taxonomy, $term_id);

	}

	/**
	 * @hook 'create_term'
	 */
	function create_term($term_id, $tt_id, $taxonomy) {
		// global $wpdb;
		//
		// $table = $wpdb->prefix.$this->dependency_table;
		//
		// $urls = $wpdb->get_col($wpdb->prepare("SELECT url FROM $table WHERE object = 'term' AND type = %s", $taxonomy));
		//
		// $this->add_expired_urls($urls);

		$this->create_object('term', $taxonomy, $term_id);

	}

	/**
	 * @hook 'pre_delete_term'
	 */
	function pre_delete_term($term, $taxonomy) {
		// global $wpdb;
		//
		// $table = $wpdb->prefix.$this->dependency_table;
		//
		// $url = $wpdb->get_col($wpdb->prepare("SELECT url FROM $table WHERE object = 'term' AND object_id = %d", $term->term_id));
		//
		// $wpdb->delete($table, array(
		// 	'object' => 'term',
		// 	'object_id' => $term->term_id
		// ), array(
		// 	'%s',
		// 	'%d'
		// ));
		//
		// $this->add_expired_urls($urls);

		$this->delete_object('term', $taxonomy, $term->term_id);
	}


	/**
	 * @hook 'karma_cache_create_object'
	 */
	function create_object($object, $type, $id) {
		global $wpdb;

		$table = $wpdb->prefix.$this->dependency_table;


		// $wpdb->update($table, array(
		// 	'status' => 1
		// ), array(
		// 	'object' => $object,
		// 	'type' => $type
		// ), array(
		// 	'%d'
		// ), array(
		// 	'%s',
		// 	'%s'
		// ));

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

		// $wpdb->update($table, array(
		// 	'status' => 1
		// ), array(
		// 	'object' => $object,
		// 	'object_id' => $id
		// ), array(
		// 	'%d'
		// ), array(
		// 	'%s',
		// 	'%d'
		// ));

		$wpdb->query($wpdb->prepare(
			"UPDATE $table
			SET status = %d
			WHERE object = %s AND (object_id = %d OR type = %s AND context = '*')",
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


		// $wpdb->update($table, array(
		// 	'status' => '2'
		// ), array(
		// 	'object' => $object,
		// 	'object_id' => $id
		// ), array(
		// 	'%d'
		// ), array(
		// 	'%s',
		// 	'%d'
		// ));


		$wpdb->query($wpdb->prepare(
			"UPDATE $table
			SET status = %d
			WHERE object = %s AND (object_id = %d OR type = %s AND context = '*')",
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
		// if ($karma->options->get_option('html_cache')) {
		//
		// 	if (!$html_cache) {
		//
		// 		$mod_rewrite->remove();
		//
		// 	}
		//
		// } else {
		//
		// 	if ($html_cache) {
		//
		// 		$mod_rewrite->add();
		//
		// 	}
		//
		// }

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
	 * print_scripts
	 */
// 	public function print_scripts($in_footer = 0) {
// 		global $wp_scripts;
//
// 		$key = implode(',', $wp_scripts->queue);
//
// 		$js_filename = md5($key) . '.js';
//
// 		// require_once plugin_dir_path( __FILE__ ) . 'class-cache-file.php';
// 		//
// 		// $file = new Karma_Cache_File;
//
// 		// require_once get_template_directory() . '/admin/class-file.php';
// 		// $this->file_manager = new Karma_Content_Directory;
// 		// $this->file_manager->directory = 'js-cache';
//
// 		// handle script localization
// 		$scripts = $this->get_all_scripts($in_footer); // -> also used later!
//
// 		$l10n = '';
// 		foreach ($scripts as $handle) {
//
// 			// var_dump($handle, isset($wp_scripts->registered[$handle]->extra['group']));
//
// 			if (isset($wp_scripts->registered[$handle]->extra['data'])) {
// 				$l10n .= $wp_scripts->registered[$handle]->extra['data'] . "\n";
// 			}
// 		}
// 		if ($l10n) {
// 			echo '<script type="text/javascript">'."\n".$l10n."\n".'</script>'."\n";
// 		}
//
// 		// if (!file_exists(WP_CONTENT_DIR . '/' . $file->cache_dir . '/js/' . $js_filename)) {
//
// 		if (!$this->file_manager->file_exists('js', $js_filename)) {
//
// 			$js = '';
//
// // 			$scripts = $this->get_all_scripts($wp_scripts->queue);
//
// 			foreach ($scripts as $handle) {
//
// 				$script_src = $wp_scripts->registered[$handle]->src;
//
// 				if (strpos($script_src, WP_CONTENT_URL) === 0) {
//
// 					$script_file = str_replace(WP_CONTENT_URL, WP_CONTENT_DIR, $script_src);
//
// 					$js .= "\n\n\n/**************** $handle ***************/\n\n\n" . file_get_contents($script_file) . "\n";
//
// 				} else {
//
// 					echo '<script type="text/javascript" src="'.$script_src.'?t='.time().'"></script>';
//
// 				}
//
// 			}
//
// 			if ($js) {
//
// 				include get_template_directory() . '/modules/html-cache/jshrink/minifier.php';
//
// 				// $js = JShrink\Minifier::minify($js);
//
// 				$this->file_manager->write_file('js', $js_filename, $js);
//
// 			}
//
// 		}
//
//
// 		if (!$this->file_manager->file_exists('js', $js_filename)) {
//
// 			$js_src = $this->file_manager->get_url('js', $js_filename);
//
// 	    echo '<script type="text/javascript" src="'.$js_src.'?t='.time().'"></script>';
//
// 		}
//
// 	}

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
	 * @ajax 'karma_cache_update_all'
	 */
	public function ajax_update_all() {
		global $wpdb;

		$table = $wpdb->prefix.$this->dependency_table;

		$wpdb->query($wpdb->prepare(
			"UPDATE $table SET status = %d",
			1
		));

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
					'onclick' => 'ajaxGet(KarmaTaskManager.ajax_url, {action: "karma_cache_update_all"}, function(results) {KarmaTaskManager.update();});'
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

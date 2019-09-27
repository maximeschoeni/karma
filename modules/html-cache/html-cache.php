<?php


Class Karma_Cache {

	public $version = '01';
	public $cache_directory = 'cache';
	// public $dependency_table = 'htmlcache';
	public $sitepage_table = 'cache_html';
	// public $dependencies = array();


	/**
	 *	Profile requirement metabox callback
	 */
	public function __construct() {

		// register_activation_hook(__FILE__, array('Karma_Cache', 'activate'));
		// register_deactivation_hook(__FILE__, array('Karma_Cache', 'deactivate'));

		// $this->path = get_template_directory() . '/modules/html-cache';

		require_once get_template_directory() . '/modules/dependencies/dependencies.php';
		require_once get_template_directory() . '/modules/html-cache/multilanguage.php';
		require_once get_template_directory() . '/modules/files/files.php';

		$this->files_manager = new Karma_Files();

		// require_once get_template_directory() . '/admin/class-file.php';
		//
		// $this->file_manager = new Karma_Content_Directory;
		// $this->file_manager->directory = 'cache';

		// add_filter('mod_rewrite_rules', array($this, 'mod_rewrite_rules'));

		// add_action('karma_cache_add_post_dependency', array($this, 'add_post_dependency'));
		// add_action('karma_cache_add_term_dependency', array($this, 'add_term_dependency'));
		// add_action('karma_cache_add_type_dependency', array($this, 'add_type_dependency'));
		// add_action('karma_cache_add_taxonomy_dependency', array($this, 'add_taxonomy_dependency'));

		// compat
		// add_action('karma_cache_add_dependency_id', array($this, 'add_dependency_id'), 10, 4);
		// add_action('karma_cache_add_dependency_ids', array($this, 'add_dependency_ids'), 10, 4);
		// add_action('karma_cache_add_dependency_type', array($this, 'add_dependency_type'), 10, 3);


		add_action('karma_cache_html_dependency_updated', array($this, 'dependency_updated'));
		add_filter('karma_task', array($this, 'add_task'), 11);
		// add_filter('karma_task', array($this, 'rebuild_all_task'), 11);

		add_action('save_post', array($this, 'save_post'), 10, 3);
		add_action('before_delete_post', array($this, 'delete_post'), 10);
		add_action('edit_term', array($this, 'edit_term'), 10, 3);
		add_action('create_term', array($this, 'edit_term'), 10, 3);
		add_action('pre_delete_term', array($this, 'delete_term'), 10, 2);

		// add_action('karma_cluster_create_object', array($this, 'create_object'), 10, 3);
		// add_action('karma_cluster_update_object', array($this, 'update_object'), 10, 3);
		// add_action('karma_cluster_delete_object', array($this, 'delete_object'), 10, 3);

		// add_action('wp_ajax_karma_cache_regenerate_url', array($this, 'ajax_regenerate_url'));
		// add_action('wp_ajax_karma_htmlcache_flush', array($this, 'ajax_flush'));
		add_action('wp_ajax_karma_htmlcache_update', array($this, 'ajax_update'));
		add_action('wp_ajax_karma_htmlcache_rebuild', array($this, 'ajax_rebuild'));
		add_action('wp_ajax_karma_htmlcache_delete', array($this, 'ajax_delete'));
		add_action('wp_ajax_karma_htmlcache_toggle', array($this, 'ajax_toggle'));

		add_filter('mod_rewrite_rules', array($this, 'mod_rewrite'));



		if (is_admin()) {

			// TEST
			// add_action('admin_init', array($this, 'get_all_resources'), 99);


			// -> handle admin option page
			add_action('init', array($this, 'create_dependency_tables'), 9);

			add_action('karma_save_options', array($this, 'save_options'));
			add_action('karma_print_options', array($this, 'print_options'));
			add_action('admin_bar_menu', array($this, 'add_toolbar_button'), 999);
			add_action('karma_task_notice', array($this, 'task_notice'));

		} else {

			// skip request parsing
			add_filter('do_parse_request', array($this, 'do_parse_request'), 10, 3);

		}

	}

	/**
	 * log
	 */
	public function log($msg) {

		$logs = $this->files_manager->read_file(ABSPATH.$this->cache_directory, 'log.log');

		$logs .= $msg."\n";

		$this->files_manager->write_file(ABSPATH.$this->cache_directory, 'log.log', $logs);

		// if (file_exists(ABSPATH.'cache/log.log')) {
		// 	$logs = file_get_contents(ABSPATH.'cache/log.log');
		// } else {
		// 	$logs = '';
		// }
		//
		// $logs .= $msg."\n";
		//
		// if (!file_exists(ABSPATH.'cache')) {
		//
		// 	mkdir(ABSPATH.'cache', 0777, true);
		//
		// }
		//
		// file_put_contents(ABSPATH.'cache/log.log', $logs);

	}


	/**
	 * @hook 'init'
	 */
	function create_dependency_tables() {

		if (is_admin()) {

			require_once get_template_directory() . '/admin/table.php';

			Karma_Table::create($this->sitepage_table, "
				id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				parent BIGINT(20) NOT NULL,
				path VARCHAR(255) NOT NULL,
				request VARCHAR(255) NOT NULL,
				status SMALLINT(1) NOT NULL
			", '002');

		}

	}

	/**
	 * @filter 'do_parse_request'
	 */
	public function do_parse_request($true, $wp, $extra_query_vars) {
		global $karma, $karma_dependencies;

		if ($karma->options->get_option('html_cache') && is_user_logged_in()) {

			$request = add_query_arg($_REQUEST, ''); // $this->get_current_query();

			if (isset($_REQUEST['updatepage'])) {

				$request = remove_query_arg('updatepage', $request);

				$this->update_sitepage($request, $_REQUEST['updatepage']);

			}

			$this->sitepage = $this->get_sitepage($request);

			if ($this->sitepage) {

				$wp->query_vars = $_REQUEST;

				// -> for sublanguage filter
				do_action_ref_array( 'parse_request', array( &$this ) );

				// -> create dependency instance
				$this->dependency_instance = $karma_dependencies->create_instance('html', $this->sitepage->id);

				add_action('karma_cache_add_dependency_id', array($this->dependency_instance, 'add_id'), 10, 4);
				add_action('karma_cache_add_dependency_ids', array($this->dependency_instance, 'add_ids'), 10, 4);
				add_action('karma_cache_add_dependency_type', array($this->dependency_instance, 'add_type'), 10, 3);

				// -> handle html cache
				add_action('wp', array($this, 'wp'), 12); // after sublanguage redirection!

				return false;

			}

		}

		return $true;
	}

	// /**
	//  * @hook 'karma_cache_add_dependency_id'
	//  */
	// public function add_dependency_id($object, $type, $id, $priority = 1) {
	//
	// 	if (isset($this->dependency_instance)) {
	//
	// 		$this->dependency_instance->add_id($object, $type, $id, $priority);
	//
	// 	}
	//
	// }
	//
	// /**
	//  * @hook 'karma_cache_add_dependency_ids'
	//  */
	// public function add_dependency_ids($object, $type, $ids, $priority = 1) {
	//
	// 	if (isset($this->dependency_instance)) {
	//
	// 		$this->dependency_instance->add_ids($object, $type, $ids, $priority);
	//
	// 	}
	//
	// }
	//
	// /**
	//  * @hook 'karma_cache_add_dependency_type'
	//  */
	// public function add_dependency_type($object, $type, $priority = 1) {
	//
	// 	if (isset($this->dependency_instance)) {
	//
	// 		$this->dependency_instance->add_type($object, $type, $priority);
	//
	// 	}
	//
	// }

	/**
	 * @hook wp
	 */
	public function wp($wp) {
		global $karma, $dependencies;

		remove_action( 'template_redirect', 'redirect_canonical' );

		add_filter('show_admin_bar','__return_false');

		add_action('wp_print_scripts', array($this, 'dequeue_script'), 100);

		$this->files = array();

		add_action('wp_head', array($this, 'wp_header'));
		add_action('wp_footer', array($this, 'wp_footer'));

		ob_start(array($this, 'save_ob'));

	}

	/**
	 * @callback ob_start
	 */
	public function save_ob($content) {
		global $wpdb;
		// $url = $this->get_current_query();

		// $this->save_dependencies($this->sitepage);

		$this->dependency_instance->save();

		$file_content = apply_filters('karma_cache_html_output', $content, $this);

		$this->files_manager->write_file(ABSPATH.$this->cache_directory.'/'.$this->sitepage->path, 'dependencies.json', json_encode($this->dependency_instance->dependencies, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
		$this->files_manager->write_file(ABSPATH.$this->cache_directory.'/'.$this->sitepage->path, 'index.php', $file_content);


		//
		// $this->files['dependencies.json'] = json_encode($this->dependencies);
		// $this->files['index.html'] = $content;
		//
		// // $this->write_files();
		//
		// // $this->current_path = $this->get_current_cache_path();
		//
		//
		// // $this->files['log.txt'] = $this->current_path;
		//
		//
		//
		// foreach ($this->files as $filename => $data) {
		//
		// 	// $files_manager->write($path.'/'.$filename, $data);
		//
		// 	$this->files_manager->write_file($this->cache_directory.'/'.$this->sitepage->path, 'data.json', $data);
		//
		// }
		//
		//
		// $cache_directory
		//
		// $path = ABSPATH.'cache/'.$this->sitepage->path;
		//
		// if (!file_exists($path)) {
		//
		// 	mkdir($path, 0777, true);
		//
		// }
		//
		// foreach ($this->files as $filename => $data) {
		//
		// 	file_put_contents($path.'/'.$filename, $data);
		//
		// }

		$sitepage_table = $wpdb->prefix.$this->sitepage_table;

		$wpdb->update($sitepage_table, array(
			'status' => 0
		), array(
			'id' => $this->sitepage->id
		), array(
			'%d'
		), array(
			'%d'
		));

		return $content;
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
	 * get query for post
	 */
	public function get_post_query($post) {

		if ($post->post_type === 'page') {

			$link = '?page_id='.$post->ID;

		} else  {

			$link = '?p='.$post->ID.'&post_type='.$post->post_type;

		}

		$link = apply_filters('karma_htmlcache_post_request', $link, $post, $this);

		return $link;

	}

	/**
	 * get query for term
	 */
	public function get_term_query($term) {

		$link = '?taxonomy='.$term->taxonomy.'&term='.$term->slug;

		$link = apply_filters('karma_htmlcache_term_request', $link, $term, $this);

		return $link;

	}

	/**
	 * get query for archive
	 */
	public function get_archive_query($post_type) {

		$link = '?post_type='.$post_type;

		$link = apply_filters('karma_htmlcache_archive_request', $link, $post_type, $this);

		return $link;

	}



	/**
	 * get url for resource
	 */
	public function get_post_cache_path($post) {
		global $wp_rewrite;

		$post_type_obj = get_post_type_object($post->post_type);

		$link = '';

		if ($post_type_obj->rewrite) {

			$link .= $post_type_obj->rewrite['slug'].'/';

		}

		if ($post_type_obj->hierarchical) {

			$link .= get_page_uri($post);

		} else {

			$link .= $post->post_name;

		}

		return apply_filters('karma_htmlcache_post_path', $link, $post, $post_type_obj, $this);

	}

	/**
	 * get url for resource
	 */
	public function get_archive_cache_path($post_type) {

		$post_type_obj = get_post_type_object($post_type);

		$path = $post_type_obj->rewrite['slug'];

		return apply_filters('karma_htmlcache_archive_path', $path, $post_type, $post_type_obj, $this);

	}

	/**
	 * get url for resource
	 */
	public function get_term_cache_path($term) {

		$taxonomy_obj = get_taxonomy($term->taxonomy);

		$path = $term->slug;

		if ($taxonomy_obj->rewrite['with_front']) {

			$path = $taxonomy_obj->rewrite['slug'].'/'.$path;

		}

		return apply_filters('karma_htmlcache_term_path', $path, $term, $taxonomy_obj, $this);

		// -> todo: hierarchical terms

		return $path;
	}


	/**
	 * @filter 'karma_task'
	 */
	public function add_task($task) {
		global $wpdb, $karma;

		if (empty($task) && $karma->options->get_option('html_cache')) {

			$sitepage_table = $wpdb->prefix.$this->sitepage_table;

			$outdated_sitepage = $wpdb->get_row("SELECT * FROM $sitepage_table WHERE status > 0 ORDER BY status DESC LIMIT 1");

			if ($outdated_sitepage) {

				wp_safe_redirect(get_option('home').('/index.php'.$outdated_sitepage->request));

				exit;

			}

		}

		return $task;
	}

	/**
	 * get_sitepage from request string
	 */
	public function get_sitepage($url) {
		global $wpdb;

		$sitepage_table = $wpdb->prefix.$this->sitepage_table;

		return $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $sitepage_table WHERE request = %s",
			$url
		));

	}

	/**
	 * update_sitepage on saving a cacheable post
	 */
	function update_sitepage($url, $path, $parent_url = '') {
		global $wpdb;

		$sitepage_table = $wpdb->prefix.$this->sitepage_table;

		$sitepage = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $sitepage_table WHERE request = %s",
			$url
		));

		$parent_id = 0;

		if ($parent_url) {

			$parent_id = intval($wpdb->get_var($wpdb->prepare(
				"SELECT id FROM $sitepage_table WHERE request = %s",
				$parent_url
			)));

		}

		if ($sitepage) {

			if ($parent_id !== $sitepage->parent) {

				$wpdb->update($sitepage_table, array(
					'parent' => $parent_id,
				), array(
					'id' => $sitepage->id
				), array(
					'%d'
				), array(
					'%d'
				));

			}

			if ($sitepage->path !== $path) {

				// page url changed -> delete page in cache files
				$this->delete_cachefile($sitepage);

				$this->outdate_sitepage_deep($sitepage, 100);

				$wpdb->update($sitepage_table, array(
					'path' => $path,
				), array(
					'id' => $sitepage->id
				), array(
					'%s'
				), array(
					'%d'
				));

			} else {

				$this->outdate_sitepage($sitepage, 100);

			}

		} else { // -> create sitepage

			$wpdb->insert($sitepage_table, array(
				'parent' => $parent_id,
				'path' => $path,
				'request' => $url,
				'status' => 100
			), array(
				'%d',
				'%s',
				'%s',
				'%d'
			));

		}

	}

	/**
	 * remove_sitepage on saving a cacheable post
	 */
	function remove_sitepage($url) {
		global $wpdb;

		$sitepage_table = $wpdb->prefix.$this->sitepage_table;

		$sitepage = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $sitepage_table WHERE request = %s",
			$url
		));

		if ($sitepage) {

			// delete page in cache files
			$this->delete_cachefile($sitepage);

			// delete database entry
			$wpdb->delete($sitepage_table, array(
				'id' => $sitepage->id
			), array(
				'%d'
			));

			// delete dependencies
			// $dependency_table = $wpdb->prefix.$this->dependency_table;
			//
			// $wpdb->delete($dependency_table, array(
			// 	'page_id' => $sitepage->id
			// ), array(
			// 	'%d'
			// ));

		}

	}

	/**
	 * outdate_children make all children outdated
	 */
	public function outdate_sitepage($sitepage, $status = 1) {
		global $wpdb;

		$sitepage_table = $wpdb->prefix.$this->sitepage_table;

		$wpdb->update($sitepage_table, array(
			'status' => $status,
		), array(
			'id' => $sitepage->id
		), array(
			'%d'
		), array(
			'%d'
		));

	}

	/**
	 * outdate_children make all children outdated
	 */
	public function outdate_sitepage_deep($sitepage, $status = 1) {
		global $wpdb;

		$sitepage_table = $wpdb->prefix.$this->sitepage_table;

		$children = $wpdb->get_results($wpdb->prepare("SELECT * FROM $sitepage_table WHERE parent = %d", $sitepage->id));

		foreach ($children as $child) {

			$this->outdate_sitepage_deep($child, $status);

		}

		$this->outdate_sitepage($sitepage, $status);

	}

	/**
	 * delete cache file
	 */
	public function delete_cachefile($sitepage) {
		global $wpdb;

		$sitepage_table = $wpdb->prefix.$this->sitepage_table;

		$children = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM $sitepage_table WHERE parent = %d",
			$sitepage->id
		));

		foreach ($children as $child) {

			$this->delete_cachefile($child);

		}

		$this->files_manager->remove(ABSPATH.$this->cache_directory.'/'.$sitepage->path);

		// if (file_exists($sitepage->path.'/index.html')) {
		//
		// 	unlink($sitepage->path.'/index.html');
		//
		// }
		//
		// if (file_exists($sitepage->path.'/script.js')) {
		//
		// 	unlink($sitepage->path.'/script.js');
		//
		// }
		//
		// if (file_exists($sitepage->path.'/dependencies.json')) {
		//
		// 	unlink($sitepage->path.'/dependencies.json');
		//
		// }
		//
		// if (file_exists($sitepage->path)) {
		//
		// 	unlink($sitepage->path);
		//
		// }

	}



	/**
	 * @hook 'save_post'
	 */
	public function save_post($post_id, $post, $update) {
		global $karma;

		if ($karma->options->get_option('html_cache')) {

			if ($this->is_post_type_single_cacheable($post->post_type)) {

				$url = $this->get_post_query($post);
				$path = $this->get_post_cache_path($post);

				if ($post->post_status === 'publish') {

					if ($post->post_parent) {

						$parent_post = get_post($post->post_parent);
						$parent_url = $this->get_post_query($parent_post);

					} else {

						$parent_url = '';

					}

					$this->update_sitepage($url, $path, $parent_url);

					do_action('karma_htmlcache_update_post_sitepage', $post, $url, $path, $parent_url, $this);

				} else {

					$this->remove_sitepage($url);

					do_action('karma_htmlcache_remove_post_sitepage', $post, $url, $this);

				}

			}

			if ($this->is_post_type_archive_cacheable($post->post_type)) {

				$url = $this->get_archive_query($post->post_type);
				$path = $this->get_archive_cache_path($post->post_type);

				$this->update_sitepage($url, $path);

				do_action('karma_htmlcache_update_archive_sitepage', $post->post_type, $url, $path, $this);

			}

		}

	}

	/**
	 * @hook 'before_delete_post'
	 */
	function delete_post($post_id) {
		global $karma;

		if ($karma->options->get_option('html_cache')) {

			$post = get_post($post_id);

			if ($this->is_post_type_single_cacheable($post->post_type)) {

				$url = $this->get_post_query($post);

				$this->remove_sitepage($url);

				do_action('karma_htmlcache_remove_post_sitepage', $post, $url, $this);

			}

		}

	}

	/**
	 * @hook 'edit_term', 'create_term'
	 */
	function edit_term($term_id, $tt_id, $taxonomy) {
		global $karma;

		if ($karma->options->get_option('html_cache')) {

			if ($this->is_taxonomy_cacheable($taxonomy)) {

				$term = get_term($term_id, $taxonomy);

				$url = $this->get_term_query($term);
				$path = $this->get_term_cache_path($term);

				$this->update_sitepage($url, $path);

				do_action('karma_htmlcache_update_term_sitepage', $term, $url, $path, $this);

			}

		}

	}

	/**
	 * @hook 'pre_delete_term'
	 */
	function delete_term($term, $taxonomy) {
		global $karma;

		if ($karma->options->get_option('html_cache')) {

			if ($this->is_taxonomy_cacheable($taxonomy)) {

				$term = get_term($term_id, $taxonomy);

				$url = $this->get_term_query($term);

				$this->remove_sitepage($url);

				do_action('karma_htmlcache_remove_term_sitepage', $term, $url, $this);

			}

		}

	}

	/**
	 * @hook "karma_cache_{$dependency->target}_dependency_updated"
	 */
	public function dependency_updated($dependency) {
		global $wpdb, $karma;

		if ($karma->options->get_option('html_cache')) {

			$sitepage_table = $wpdb->prefix.$this->sitepage_table;

			$wpdb->query($wpdb->prepare(
				"UPDATE $sitepage_table
				SET status = GREATEST(status, %d)
				WHERE id = %d",
				$dependency->priority,
				$dependency->target_id
			));

		}

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

		$karma->options->update_option('html_cache', $html_cache);

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

				$this->files_manager->write_file(ABSPATH.$this->cache_directory.'/'.$this->sitepage->path, 'script.js', $js);

				$path = get_option('home');

				if ($this->sitepage->path) {

					$path .= '/'.$this->sitepage->path;

				}

				echo '<script type="text/javascript" src="'.$path.'/script.js"></script>';

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
	public function create_sitemap() {
		global $wpdb;

		$items = array();

		$this->update_sitepage('', '');

		do_action('karma_htmlcache_update_home_sitepage', '', '', $this);

		$post_types = array_filter(get_post_types(), array($this, 'is_post_type_single_cacheable'));

		if ($post_types) {

			$post_types_sql = implode("','", array_map('esc_sql', $post_types));

			$posts = $wpdb->get_results(
				"SELECT * FROM $wpdb->posts
				WHERE post_type IN ('$post_types_sql') AND post_status = 'publish'"
			);

			foreach ($posts as $post) {

				$url = $this->get_post_query($post);
				$path = $this->get_post_cache_path($post);

				if ($post->post_parent) {

					$parent_post = get_post($post->post_parent);
					$parent_url = $this->get_post_query($parent_post);

				} else {

					$parent_url = '';

				}

				$this->update_sitepage($url, $path, $parent_url);

				do_action('karma_htmlcache_update_post_sitepage', $post, $url, $path, $parent_url, $this);

			}

		}

		$post_types = array_filter(get_post_types(), array($this, 'is_post_type_archive_cacheable'));

		if ($post_types) {

			foreach ($post_types as $post_type) {

				$url = $this->get_archive_query($post_type);
				$path = $this->get_archive_cache_path($post_type);

				$this->update_sitepage($url, $path);

				do_action('karma_htmlcache_update_archive_sitepage', $post_type, $url, $path, $this);

			}

		}

		$taxonomies = array_filter(get_taxonomies(), array($this, 'is_taxonomy_cacheable'));

		if ($taxonomies) {

			$taxonomies_sql = implode("','", array_map('esc_sql', $taxonomies));

			$terms = $wpdb->get_results("SELECT tt.taxonomy, t.slug FROM $wpdb->term_taxonomy AS tt
				JOIN $wpdb->terms AS t ON (t.term_id = tt.term_id)
				WHERE tt.taxonomy IN ('$taxonomies_sql')");

			foreach ($terms as $term) {

				$url = $this->get_term_query($term);
				$path = $this->get_term_cache_path($term);

				$this->update_sitepage($url, $path);

				do_action('karma_htmlcache_update_term_sitepage', $term, $url, $path, $this);

			}

		}

		return $items;
	}


	/**
	 * Delete html cache
	 */
	public function delete_html_cache() {
		global $wpdb;

		$this->files_manager->remove(ABSPATH.$this->cache_directory);

		$sitepage_table = $wpdb->prefix.$this->sitepage_table;

		$wpdb->query("truncate $sitepage_table");

		do_action('karma_dependency_delete_target', 'html');

	}


	/**
	 * @ajax 'karma_htmlcache_update'
	 */
	public function ajax_update() {
		global $wpdb;

		$output = array();

		$table = $wpdb->prefix.$this->sitepage_table;

		$wpdb->query($wpdb->prepare(
			"UPDATE $table SET status = %d",
			1
		));

		$num_task = $wpdb->get_var("SELECT count(id) AS num FROM $table");

		$output['notice'] = "Updating $num_task HTML Cache Pages. ";
		$output['action'] = 'updating html cache';

		echo json_encode($output);
		exit;

	}

	/**
	 * @ajax 'karma_htmlcache_rebuild'
	 */
	public function ajax_rebuild() {
		global $wpdb;

		$output = array();

		$this->delete_html_cache();
		$this->create_sitemap();

		$table = $wpdb->prefix.$this->sitepage_table;
		$num_task = $wpdb->get_var("SELECT count(id) AS num FROM $table");

		$output['notice'] = "Rebuilding $num_task HTML Cache Pages. ";
		$output['action'] = 'rebuild html cache';

		echo json_encode($output);
		exit;

	}

	/**
	 * @ajax 'karma_htmlcache_delete'
	 */
	public function ajax_delete() {

		$output = array();

		$this->delete_html_cache();

		$output['notice'] = "Delete HTML Cache Pages. ";
		$output['action'] = 'delete html cache';

		echo json_encode($output);
		exit;

	}

	/**
	 * @ajax 'karma_htmlcache_deactivate'
	 */
	public function ajax_deactivate() {
		global $karma;

		$output = array();

		$this->delete_html_cache();

		$karma->options->update_option('html_cache', 0);

		flush_rewrite_rules();

		$output['notice'] = "Deactivate HTML Cache. ";
		$output['action'] = 'deactivate html cache';

		echo json_encode($output);
		exit;

	}

	/**
	 * @ajax 'karma_htmlcache_activate'
	 */
	public function ajax_activate() {
		global $wpdb, $karma;

		$output = array();

		$this->delete_html_cache();
		$this->create_sitemap();

		$karma->options->update_option('html_cache', 1);

		flush_rewrite_rules();

		$table = $wpdb->prefix.$this->sitepage_table;
		$num_task = $wpdb->get_var("SELECT count(id) AS num FROM $table");

		$output['notice'] = "Activating HTML Cache. Building $num_task Pages. ";
		$output['action'] = 'rebuild html cache';

		echo json_encode($output);
		exit;

	}

	/**
	 * @ajax 'karma_htmlcache_toggle'
	 */
	public function ajax_toggle() {
		global $wpdb, $karma;

		$output = array();

		$this->delete_html_cache();

		if ($karma->options->get_option('html_cache')) {

			$karma->options->update_option('html_cache', 0);

			$output['title'] = 'HTML Cache (disabled)';
			$output['label'] = 'Activate HTML Cache';
			$output['notice'] = "Deactivate HTML Cache. ";
			$output['action'] = 'deactivate html cache';

			unlink(ABSPATH.'cache.php');

		} else {

			$this->create_sitemap();

			$table = $wpdb->prefix.$this->sitepage_table;
			$num_task = $wpdb->get_var("SELECT count(id) AS num FROM $table");

			$karma->options->update_option('html_cache', 1);

			$output['title'] = 'HTML Cache (enabled)';
			$output['label'] = 'Deactivate HTML Cache';
			$output['notice'] = "Activating HTML Cache. Building $num_task Pages. ";
			$output['action'] = 'rebuild html cache';

			copy(get_template_directory().'/modules/html-cache/include/cache.php', ABSPATH.'cache.php');

		}

		flush_rewrite_rules();

		echo json_encode($output);
		exit;

	}


	/**
	 * @ajax 'karma_htmlcache_flush'
	 */
	// public function ajax_flush() {
	// 	global $wpdb;
	//
	// 	$output = array();
	//
	//
	// 	$sitepage_table = $wpdb->prefix.$this->sitepage_table;
	//
	// 	$wpdb->query("truncate $sitepage_table");
	//
	// 	do_action('karma_dependency_delete_target', 'html');
	//
	// 	$this->create_sitemap();
	//
	// 	$output['action'] = 'flush html done';
	//
	// 	echo json_encode($output);
	// 	exit;
	//
	// }

	/**
	 * @callbak 'admin_bar_menu'
	 */
	public function add_toolbar_button( $wp_admin_bar ) {
		global $karma;

		$html_cache = $karma->options->get_option('html_cache');

		if (current_user_can('manage_options')) {

			$wp_admin_bar->add_node(array(
				'id'    => 'htmlcache-group',
				'title' => 'HTML Cache ('.($html_cache ? 'enabled' : 'disabled').')'
			));

			$wp_admin_bar->add_node(array(
				'id'    => 'update-html-cache',
				'title' => 'Update HTML Cache',
				'parent' => 'htmlcache-group',
				'href'  => '#',
				'meta'  => array(
					// 'onclick' => 'ajaxGet(KarmaTaskManager.ajax_url, {action: "karma_htmlcache_flush"}, function(results) {KarmaTaskManager.update();console.log(results);});event.preventDefault();'
					'onclick' => 'KarmaTaskManager&&KarmaTaskManager.addTask("karma_htmlcache_update",this);event.preventDefault()'
				)
			));

			$wp_admin_bar->add_node(array(
				'id'    => 'rebuild-html-cache',
				'title' => 'Rebuild HTML Cache',
				'parent' => 'htmlcache-group',
				'href'  => '#',
				'meta'  => array(
					// 'onclick' => 'ajaxGet(KarmaTaskManager.ajax_url, {action: "karma_htmlcache_flush"}, function(results) {KarmaTaskManager.update();console.log(results);});event.preventDefault();'
					'onclick' => 'KarmaTaskManager&&KarmaTaskManager.addTask("karma_htmlcache_rebuild",this);event.preventDefault()'
				)
			));

			$wp_admin_bar->add_node(array(
				'id'    => 'delete-html-cache',
				'title' => 'Delete HTML Cache',
				'parent' => 'htmlcache-group',
				'href'  => '#',
				'meta'  => array(
					// 'onclick' => 'ajaxGet(KarmaTaskManager.ajax_url, {action: "karma_htmlcache_flush"}, function(results) {KarmaTaskManager.update();console.log(results);});event.preventDefault();'
					'onclick' => 'KarmaTaskManager&&KarmaTaskManager.addTask("karma_htmlcache_delete",this);event.preventDefault()'
				)
			));

			$wp_admin_bar->add_node(array(
				'id'    => 'toggle-html-cache',
				'title' => $html_cache ? 'Deactivate HTML Cache' : 'Activate HTML Cache',
				'parent' => 'htmlcache-group',
				'href'  => '#',
				'meta'  => array(
					// 'onclick' => 'ajaxGet(KarmaTaskManager.ajax_url, {action: "karma_htmlcache_flush"}, function(results) {KarmaTaskManager.update();console.log(results);});event.preventDefault();'
					'onclick' => 'KarmaTaskManager&&KarmaTaskManager.addTask("karma_htmlcache_toggle",this,function(results){this.innerHTML=results.label;this.parentNode.parentNode.parentNode.previousSibling.innerHTML=results.title});event.preventDefault()'
				)
			));

			// $wp_admin_bar->add_node(array(
			// 	'id'    => 'delete-html-cache',
			// 	'title' => 'Delete HTML Cache',
			// 	'parent' => 'htmlcache-group',
			// 	'href'  => '#',
			// 	'meta'  => array(
			// 		// 'onclick' => 'ajaxGet(KarmaTaskManager.ajax_url, {action: "karma_htmlcache_flush"}, function(results) {KarmaTaskManager.update();console.log(results);});event.preventDefault();'
			// 		'onclick' => "KarmaTaskManager&&KarmaTaskManager.addTask('karma_htmlcache_delete');event.preventDefault()"
			// 	)
			// ));

		}

	}

	/**
	 * @filter 'karma_task_notice'
	 */
	public function task_notice($tasks) {
		global $wpdb;

		$table = $wpdb->prefix.$this->sitepage_table;

		$num_task = $wpdb->get_var("SELECT count(id) AS num FROM $table WHERE status > 0");

		if ($num_task) {

			$tasks[] = "Updating $num_task HTML Page. ";

		}

		return $tasks;
	}

	/**
	 * @hook 'mod_rewrite_rules'
	 */
	public function mod_rewrite( $rules ) {
		global $karma, $wp_rewrite;

		if ($karma->options->get_option('html_cache')) {

			$home_root = parse_url( home_url() );
	    if ( isset( $home_root['path'] ) ) {
	        $home_root = trailingslashit( $home_root['path'] );
	    } else {
	        $home_root = '/';
	    }

			// $rules = str_replace("RewriteRule . {$home_root}{$wp_rewrite->index} [L]", "RewriteRule ^(.*)$ cache/$1 [L]", $rules);

			$rules = "<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase {$home_root}
RewriteRule ^index\.php$ - [L]
RewriteRule ^cache\.php.*$ - [L]
RewriteRule ^{$this->cache_directory}/.*$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ cache.php?d={$this->cache_directory}/$1
RewriteRule ^$ cache.php?d={$this->cache_directory}

</IfModule>";

//RewriteRule ^(.*)$ {$home_root}{$this->cache_directory}/$1

		}

		return $rules;
	}



}

global $karma_cache;
$karma_cache = new Karma_Cache();

<?php



Class Karma_Cache {

	public $version = '01';


	/**
	 * @hook register_activation_hook
	 */
	// static function activate() {
	// 	global $karma_cache;
	//
	// 	require_once plugin_dir_path( __FILE__ ) . 'class-object-cache-manager.php';
	//
	// 	$obj_cache_manager = new Karma_Object_Cache_Manager();
	// 	$obj_cache_manager->create();
	//
	// 	$karma_cache->update_option('object_cache', 1);
	//
	// }

	/**
	 * @hook register_deactivation_hook
	 */
	static function deactivate() {
		global $karma_cache;


		// if ($karma_cache->get_option('object_cache')) {
		//
		// 	require_once plugin_dir_path( __FILE__ ) . 'class-object-cache-manager.php';
		// 	$obj_cache_manager = new Karma_Object_Cache_Manager();
		// 	$obj_cache_manager->destroy();
		//
		// 	$karma_cache->update_option('object_cache', 0);
		//
		// }


		if ($karma_cache->get_option('html_cache')) {

			require_once get_tempate_directory() . '/modules/html-cache/class-mod-rewrite.php';

			$mod_rewrite = new Karma_Cache_Mod_Rewrite();
			$mod_rewrite->remove();

			$karma_cache->update_option('html_cache', 0);

			$this->erase_html_cache();

		}

	}

	/**
	 *	Profile requirement metabox callback
	 */
	public function __construct() {

		// register_activation_hook(__FILE__, array('Karma_Cache', 'activate'));
		// register_deactivation_hook(__FILE__, array('Karma_Cache', 'deactivate'));

		$this->path = get_tempate_directory() . '/modules/html-cache';

		if (is_admin()) {

			// -> handle admin option page
			// add_action('admin_menu', array($this, 'admin_menu'));

			add_action('karma_save_options', array($this, 'save_options'));
			add_action('karma_print_options', array($this, 'print_options'));

			// add_action('admin_post_karma_cache_save_options', array($this, 'save_options'));

			add_action('save_post', array($this, 'save_post'));
			add_action('edited_term', array($this, 'save_post'));
			add_action('created_term', array($this, 'save_post'));
			add_action('deleted_post', array($this, 'save_post'));
			add_action('delete_term', array($this, 'save_post'));

		} else {

			// -> handle html cache
			add_action('wp', array($this, 'wp'));

		}

	}

	/**
	 * @hook wp
	 */
	public function wp() {
		global $karma;

		if (is_home() || is_singular() || is_archive() || is_search() || apply_filters('karma_html_cache_save', false)) {

			if ($karma->options->get_option('html_cache') && empty($_SERVER['QUERY_STRING']) && !apply_filters('karma_html_cache_disable', false)) {

				remove_action('wp_footer', 'wp_print_footer_scripts', 20);

				add_action('wp_footer', array($this, 'wp_footer'), 20);

				ob_start(array($this, 'save_ob'));

			}

		}

	}

	/**
	 * @hook wp_footer
	 */
	public function wp_footer() {

		require_once plugin_dir_path( __FILE__ ) . 'class-html-cache-manager.php';

		$html_cache_manager = new Karma_Html_Cache_Manager();
		$html_cache_manager->print_scripts();

	}

	/**
	 * @callback ob_start
	 */
	public function save_ob($content) {

		require_once get_tempate_directory() . '/admin/class-file.php';

		$file = new Karma_Content_Directory;
		$file->directory = 'html';

		// require_once plugin_dir_path( __FILE__ ) . 'class-cache-file.php';
		//
		// $file = new Karma_Cache_File;

		$dir = esc_attr($_SERVER['REQUEST_URI']);

		$file->write_file($dir, 'index.html', $content);

		return $content;
	}





// 	/**
// 	 * @hook wp_footer
// 	 */
// 	public function wp_footer() {
// 		global $wp_scripts;
//
// 		$key = implode(',', $wp_scripts->queue);
//
// 		$js_filename = md5($key) . '.js';
//
// 		require_once plugin_dir_path( __FILE__ ) . 'class-cache-file.php';
//
// 		$file = new Karma_Cache_File;
//
// 		// handle script localization
// 		$scripts = $this->get_all_scripts($wp_scripts->queue); // -> also used later!
// 		$l10n = '';
// 		foreach ($scripts as $handle) {
// 			if (isset($wp_scripts->registered[$handle]->extra['data'])) {
// 				$l10n .= $wp_scripts->registered[$handle]->extra['data'] . "\n";
// 			}
// 		}
// 		if ($l10n) {
// 			echo '<script type="text/javascript">'."\n".$l10n."\n".'</script>'."\n";
// 		}
//
// 		if (!file_exists(WP_CONTENT_DIR . '/' . $file->cache_dir . '/js/' . $js_filename)) {
//
// 			$l10n = '';
// 			$js = '';
//
// // 			$scripts = $this->get_all_scripts($wp_scripts->queue);
//
// 			foreach ($scripts as $handle) {
//
// 				$js .= file_get_contents($wp_scripts->registered[$handle]->src) . "\n";
//
// 			}
//
// 			include plugin_dir_path( __FILE__ ) . 'jshrink/minifier.php';
//
// 			$minified_code = JShrink\Minifier::minify($js);
//
// 			$file->write_file('js', $js_filename, $minified_code);
//
// 		}
//
// 		$js_src = WP_CONTENT_URL . '/cache/js/' . $js_filename;
//
//     echo '<script type="text/javascript" src="'.$js_src.'?t='.time().'"></script>';
//
// 	}
//
// 	/**
// 	 * get scripts
// 	 */
// 	public function get_all_scripts($script_queue) {
//
// 		$deps_keys = array();
//
// 		foreach ($script_queue as $handle) {
//
// 			$deps_keys = array_merge($deps_keys, $this->get_script_deps($handle));
//
// 		}
//
// 		return array_keys($deps_keys);
// 	}
//
// 	/**
// 	 * get script deps
// 	 */
// 	public function get_script_deps($script) {
//
// 		$deps_keys = array();
//
// 		if (isset($wp_scripts->registered[$script]->deps) && $wp_scripts->registered[$script]->deps) {
//
// 			foreach ($wp_scripts->registered[$script]->deps as $child) {
//
// 				$deps_keys = array_merge($deps_keys, $this->get_script_deps($child));
//
// 			}
//
// 		} else {
//
// 			$deps_keys[$script] = true;
//
// 		}
//
// 		return $deps_keys;
// 	}
//
//
//
//
//
//
// 	/**
// 	 * @callback ob_start
// 	 */
// 	public function save_ob($content) {
//
// 		require_once plugin_dir_path( __FILE__ ) . 'class-cache-file.php';
//
// 		$file = new Karma_Cache_File;
//
// 		$dir = 'html' . esc_attr($_SERVER['REQUEST_URI']);
//
// 		$file->write_file($dir, 'index.html', $content);
//
// 		return $content;
// 	}
//

	/**
	 * Add Custom Option Page
	 *
	 * @hook admin_menu
	 */
	// public function admin_menu() {
	//
	// 	add_options_page(
	// 		'Karma Cache Settings',
	// 		'Karma Cache',
	// 		'manage_options', // permission
	// 		'karma_cache_settings', // page slug
	// 		array($this, 'print_options_page')
	// 	);
	//
	// }

	/**
	 * @callback add_options_page()
	 */
	// public function print_options_page() {
	//
	// 	include plugin_dir_path( __FILE__ ) . 'includes/options.php';
	//
	// }

	/**
	 * @hook 'karma_print_options'
	 */
	public function print_options() {

		include get_tempate_directory() . '/modules/html-cache/include/options.php';

	}

	/**
	 * @hook 'karma_save_options'
	 */
	public function save_options($options) {

		// if (isset($_POST['karma_html_cache']) && wp_verify_nonce($_POST['karma_html_cache'], 'karma_html_cache')) {

			// -> save object cache
			// $object_cache = isset($_POST['object_cache']) && $_POST['object_cache'] ? 1 : 0;
			//
			// require_once plugin_dir_path( __FILE__ ) . 'class-object-cache-manager.php';
			//
			// $obj_cache_manager = new Karma_Object_Cache_Manager();
			//
			// if ($this->get_option('object_cache')) {
			//
			// 	if (!$object_cache) {
			//
			// 		$obj_cache_manager->destroy();
			//
			// 	}
			//
			// } else {
			//
			// 	if ($object_cache) {
			//
			// 		$obj_cache_manager->create();
			//
			// 	}
			//
			// }
			//
			// $this->update_option('object_cache', $object_cache);


		$html_cache = isset($_POST['html_cache']) && $_POST['html_cache'] ? 1 : 0;

		require_once plugin_dir_path( __FILE__ ) . 'class-mod-rewrite.php';

		$mod_rewrite = new Karma_Cache_Mod_Rewrite();

		if ($options->get_option('html_cache')) {

			if (!$html_cache) {

				$mod_rewrite->remove();

			}

		} else {

			if ($html_cache) {

				$mod_rewrite->add();

			}

		}

		$options->update_option('html_cache', $html_cache);

		$this->erase_html_cache();

		// }

	}

	/**
	 * Erase html cache
	 */
	public function erase_html_cache() {

		require_once get_tempate_directory() . '/admin/class-file.php';

		$file = new Karma_Content_Directory;
		$file->directory = 'html';
		$file->erase_dir('html');
		$file->erase_dir('js');

	}


	/**
	 * @hook 'save_post', 'edited_term', 'created_term', 'deleted_post', 'delete_term'
	 */
	public function save_post($post_or_term_id) {

		$this->erase_html_cache();

		// @todo: rebuild cache

	}


//
//
// 	/**
// 	 * append mod_rewrite modules into .htaccess file
// 	 */
// 	public function add_mod_rewrite() {
//
//
//
// 		$htaccess_file = get_home_path() . '.htaccess';
//
// 		$home_root = parse_url(home_url());
//
// 		if (isset($home_root['path'])) {
//
// 			$home_root = trailingslashit($home_root['path']);
//
// 		} else {
//
// 			$home_root = '/';
//
// 		}
//
// 		$rules = array(
// 			'<IfModule mod_rewrite.c>',
// 			'RewriteEngine On',
// 			'RewriteBase '.$home_root,
// 			'RewriteCond %{DOCUMENT_ROOT}'.$home_root.'wp_content/cache/html%{REQUEST_URI} -d',
// 			'RewriteRule . '.$home_root.'wp_content/cache/html%{REQUEST_URI} [L]',
// 			'</IfModule>'
// 		);
//
//
//
// 		$this->remove_mod_rewrite(); // just in case
//
// 		if (file_exists($htaccess_file) && is_writable($htaccess_file)) {
//
// 			$htaccess_content = file_get_contents($htaccess_file);
//
// 			$start_marker = '# BEGIN ' . $this->mod_rewrite_marker . "\n";
// 			$end_marker   = '# END ' . $this->mod_rewrite_marker . "\n";
//
// 			$rules_content = $start_marker;
// 			$rules_content .= implode("\n", $rules) . "\n";
// 			$rules_content .= $end_marker;
//
// 			file_put_contents($htaccess_file, $rules_content . $htaccess_content);
//
// 		}
//
// 	}
//
// 	/**
// 	 * find mod_rewrite modules content in .htaccess file
// 	 */
// // 	public function get_mod_rewrite_content() {
// //
// // 		$home_path = get_home_path();
// // 		$htaccess_file = $home_path . '.htaccess';
// //
// // 		if (file_exists($htaccess_file) && is_writable($htaccess_file)) {
// //
// // 			$htaccess_content = file_get_contents($htaccess_file);
// //
// // 			$start_marker = '# BEGIN ' . $this->mod_rewrite_marker . "\n";
// // 			$end_marker   = '# END ' . $this->mod_rewrite_marker . "\n";
// //
// // 			$start_index = strpos($htaccess_content, $start_marker);
// // 			$end_index = strpos($htaccess_content, $start_marker);
// //
// // 			if ($start_index !== false && $end_index > $start_index) {
// //
// // 				return substr($start_index, $end_index - $start_index + strlen($end_marker));
// //
// // 			}
// //
// // 		}
// //
// // 		return false;
// // 	}
//
//
// 	/**
// 	 * remove mod_rewrite modules into .htaccess file
// 	 */
// 	public function remove_mod_rewrite() {
//
// 		$htaccess_file = get_home_path() . '.htaccess';
//
// 		if (file_exists($htaccess_file) && is_writable($htaccess_file)) {
//
// 			$htaccess_content = file_get_contents($htaccess_file);
//
// 			$start_marker = '# BEGIN ' . $this->mod_rewrite_marker . "\n";
// 			$end_marker   = '# END ' . $this->mod_rewrite_marker . "\n";
//
// 			$start_index = strpos($htaccess_content, $start_marker);
// 			$end_index = strpos($htaccess_content, $end_marker);
//
// 			if ($start_index !== false && $end_index > $start_index) {
//
// 				$rules_content = substr($htaccess_content, $start_index, $end_index - $start_index + strlen($end_marker));
//
// 				$htaccess_content = str_replace($rules_content, '', $htaccess_content);
//
// 				file_put_contents($htaccess_file, $htaccess_content);
//
// 			}
//
// 		}
//
// 	}



// 	function save_mod_rewrite_rules() {
// 		if ( is_multisite() ) {
// 				return;
// 		}
//
// 		global $wp_rewrite;
//
// 		// Ensure get_home_path() is declared.
// 		require_once( ABSPATH . 'wp-admin/includes/file.php' );
//
// 		$home_path     = get_home_path();
// 		$htaccess_file = $home_path . '.htaccess';
//
//
// 		if (is_writable($htaccess_file)) {
// 			$rules = explode( "\n", $this->mod_rewrite_rules() );
// 			return insert_with_markers( $htaccess_file, 'WordPress', $rules );
// 		}
//
// 		return false;
// 	}
//



	/**
	 * Enqueue styles
	 *
	 * Hook for 'admin_enqueue_scripts'
	 */
// 	function enqueue_styles() {
//
// 		wp_enqueue_style('media-box-styles', plugin_dir_url( __FILE__ ) . 'media-box-styles.css');
//
// 		wp_enqueue_script('media-box-sortable', plugin_dir_url( __FILE__ ) . 'js/sortable.js');
// 		wp_enqueue_script('media-box-script', plugin_dir_url( __FILE__ ) . 'media-box-script.js', array('media-box-sortable'), null, true);
//
// 	}

// 	public function write($group, $key, $filename, $data) {
//
// 		$path = WP_CONTENT_DIR . '/' . $this->directory;
//
// 		if (!is_dir($path)) {
//
// 			mkdir($path);
//
// 		}
//
// 		$path .= '/' . $group;
//
// 		if (!is_dir($path)) {
//
// 			mkdir($path);
//
// 		}
//
// 		$path .= '/' . $key;
//
// 		if (!is_dir($path)) {
//
// 			mkdir($path);
//
// 		}
//
// 		return file_put_contents($path . '/' . $filename, $data);
//
// 	}
//
// 	public function get_file($group, $key, $filename) {
//
// 		return WP_CONTENT_DIR . '/' . $this->directory . '/' . $group . '/' . $key . '/' . $filename;
//
// 	}
//
//
//
// 	/**
// 	 * Get cache
// 	 * @filter 'karma_cache_get'
// 	 */
// 	public function get_cache($false, $key, $group) {
//
// // 		var_dump($key, $group);
// // die('get');
//
//
// 		// $this->output[] = array($key, $group);
//
// 		switch ($group) {
//
// 			case 'posts':
//
// 				$file = WP_CONTENT_DIR . '/' . $this->directory . '/' . $group . '/' . $key . '/data.json';
//
// 				if (file_exists($file)) {
//
// 					return json_decode(file_get_contents($file));
//
// 				}
//
// 		}
//
// 		return $false;
// 	}
//
// 	/**
// 	 * Get cache
// 	 * @filter 'karma_cache_set'
// 	 */
// 	public function set_cache($data, $key, $group) {
//
// 		switch ($group) {
//
// 			case 'posts':
//
// 				$r = $this->write($group, $key, '/data.json', json_encode($data));
//
// 		}
//
// 		return $data;
// 	}
//
//

	/**
	 *	get option
	 */
	public function get_option($name, $fallback = false) {

		if (!isset($this->options)) {

			$this->options = get_option($this->option_name);

		}

		if (isset($this->options[$name])) {

			return $this->options[$name];

		}

		return $fallback;
	}

	/**
	 *	get options
	 */
	public function get_options() {

		if (!isset($this->options)) {

			$this->options = get_option($this->option_name);

		}

		return $this->options;
	}

	/**
	 * Update option
	 */
	public function update_option($name, $value) {

		$this->options = $this->get_options();

		$this->options[$name] = $value;

		update_option($this->option_name, $this->options);

	}

}

global $karma_cache;
$karma_cache = new Karma_Cache();

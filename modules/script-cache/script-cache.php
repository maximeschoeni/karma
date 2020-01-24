<?php


Class Karma_Cache_Script {

	public $version = '01';
	public $scripts = array();
	public $cache_directory = 'scripts';

	/**
	 *	Profile requirement metabox callback
	 */
	public function __construct() {

		// add_action('wp_print_scripts', array($this, 'dequeue_script'), 100);


		add_action('wp_head', array($this, 'wp_header'));
		add_action('wp_footer', array($this, 'wp_footer'));


		add_action('karma_cache_script', array($this, 'add_script'), 10, 2);

	}

	/**
	 * @hook karma_cache_script
	 */
	public function add_script($script, $context = null) {
		global $wp_scripts;

		if (isset($wp_scripts->registered[$script]->extra['group'])) {

			$group = $wp_scripts->registered[$script]->extra['group']; // (=1) -> footer

		} else {

			$group = 0; // -> header

		}

		if (isset($context)) {

			$this->scripts[$group]['context'][$context][] = $script;

		} else {

			$this->scripts[$group]['general'][] = $script;

		}

		wp_dequeue_script($script);

	}


	/**
	 *
	 */
	public function group_scripts() {

		static $script_groups;

		if (!isset($script_groups)) {

			$script_groups = array();
			$all_deps_keys = array();

			foreach ($this->scripts as $group_key => $group) {

				if (isset($group['general'])) {

					$general_deps_keys = array();

					foreach ($group['general'] as $script) {

						$general_deps_keys = array_merge($general_deps_keys, $this->get_script_deps($script));

					}

					$general_deps_keys = array_diff_key($general_deps_keys, $all_deps_keys);
					$all_deps_keys = array_merge($general_deps_keys, $all_deps_keys);

					$script_groups[$group_key]['general'] = array_keys($general_deps_keys);

				}

				if (isset($group['context'])) {

					foreach ($group['context'] as $context => $scripts) {

						$contextual_deps_keys = array();

						foreach ($scripts as $script) {

							$contextual_deps_keys = array_merge($contextual_deps_keys, $this->get_script_deps($script));

						}

						$contextual_deps_keys = array_diff_key($contextual_deps_keys, $all_deps_keys);
						$all_deps_keys = array_merge($contextual_deps_keys, $all_deps_keys);

						$script_groups[$group_key]['context'][$context] = array_keys($general_deps_keys);

					}

				}

			}

		}

		return $script_groups;

	}


	/**
	 * @hook wp_head
	 */
	public function wp_header() {

		$this->print_group(0);

	}

	/**
	 * @hook wp_footer
	 */
	public function wp_footer() {

		$this->print_group(1);

	}

	/**
	 * print_scripts
	 */
	public function print_group($group) {

		$script_groups = $this->group_scripts();

		if (isset($script_groups[$group])) {

			$script_group = $script_groups[1];

			if (isset($script_group['general'])) {

				$this->print_scripts($script_group['general'], '');

			}

			if (isset($script_group['context'])) {

				foreach ($script_group['context'] as $context => $scripts) {

					$this->print_scripts($scripts, $context);

				}

			}

		}

	}

	/**
	 * print_scripts
	 */
	public function print_scripts($scripts, $internal, $path) {
		global $wp_scripts;

		$script_dir = WP_CONTENT_DIR.$this->cache_directory;

		if ($internal) {

			$script_dir .= '/header';

		} else {

			$script_dir .= '/footer';

		}

		if ($path) {

			$script_dir .= '/'.$path;

		}

		if (!file_exists($script_dir.'/script.php')) {


		}

		foreach ($scripts as $handle) {

			if (isset($wp_scripts->registered[$handle]->extra['data'])) {

				$l10n .= $wp_scripts->registered[$handle]->extra['data'] . "\n";

			}

		}

		if ($l10n) {

			echo '<script type="text/javascript">'.$l10n.'</script>'."\n";

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

}

new Karma_Cache_Script();

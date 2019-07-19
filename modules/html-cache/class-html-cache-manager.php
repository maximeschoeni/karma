<?php

/**
 * Core HTML cache manager
 */
class Karma_Html_Cache_Manager {

	/**
	 * print_scripts
	 */
	public function print_scripts() {
		global $wp_scripts;

		$key = implode(',', $wp_scripts->queue);

		$js_filename = md5($key) . '.js';

		require_once plugin_dir_path( __FILE__ ) . 'class-cache-file.php';

		$file = new Karma_Cache_File;

		// handle script localization
		$scripts = $this->get_all_scripts($wp_scripts->queue); // -> also used later!
		$l10n = '';
		foreach ($scripts as $handle) {
			if (isset($wp_scripts->registered[$handle]->extra['data'])) {
				$l10n .= $wp_scripts->registered[$handle]->extra['data'] . "\n";
			}
		}
		if ($l10n) {
			echo '<script type="text/javascript">'."\n".$l10n."\n".'</script>'."\n";
		}

		if (!file_exists(WP_CONTENT_DIR . '/' . $file->cache_dir . '/js/' . $js_filename)) {

			$l10n = '';
			$js = '';

// 			$scripts = $this->get_all_scripts($wp_scripts->queue);

			foreach ($scripts as $handle) {

				$script_src = $wp_scripts->registered[$handle]->src;
				$script_file = str_replace(WP_CONTENT_URL, WP_CONTENT_DIR, $script_src);

				$js .= file_get_contents($script_file) . "\n";

			}

			include plugin_dir_path( __FILE__ ) . 'jshrink/minifier.php';

			$minified_code = JShrink\Minifier::minify($js);

			$file->write_file('js', $js_filename, $minified_code);

		}

		$js_src = WP_CONTENT_URL . '/cache/js/' . $js_filename;

    echo '<script type="text/javascript" src="'.$js_src.'?t='.time().'"></script>';

	}

	/**
	 * get scripts
	 */
	public function get_all_scripts($script_queue) {

		$deps_keys = array();

		foreach ($script_queue as $handle) {

			$deps_keys = array_merge($deps_keys, $this->get_script_deps($handle));

		}

		return array_keys($deps_keys);
	}

	/**
	 * get script deps
	 */
	public function get_script_deps($script) {

		$deps_keys = array();

		if (isset($wp_scripts->registered[$script]->deps) && $wp_scripts->registered[$script]->deps) {

			foreach ($wp_scripts->registered[$script]->deps as $child) {

				$deps_keys = array_merge($deps_keys, $this->get_script_deps($child));

			}

		} else {

			$deps_keys[$script] = true;

		}

		return $deps_keys;
	}


}

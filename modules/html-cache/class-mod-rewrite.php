<?php


Class Karma_Cache_Mod_Rewrite {

	private $mod_rewrite_marker = 'Karma Cache';

	/**
	 * append mod_rewrite modules into .htaccess file
	 */
	public function add() {


		// $htaccess_file = get_home_path() . '.htaccess';

		$htaccess_file = ABSPATH . '.htaccess';


		$home_root = parse_url(home_url());

		if (isset($home_root['path'])) {

			$home_root = trailingslashit($home_root['path']);

		} else {

			$home_root = '/';

		}

		// $rules = array(
		// 	'<IfModule mod_rewrite.c>',
		// 	'RewriteEngine On',
		// 	'RewriteBase '.$home_root,
		// 	'RewriteCond %{QUERY_STRING} =""',
		// 	'RewriteCond %{DOCUMENT_ROOT}'.$home_root.'wp-content/cache/html%{REQUEST_URI} -f [OR]',
		// 	'RewriteCond %{DOCUMENT_ROOT}'.$home_root.'wp-content/cache/html%{REQUEST_URI} -d',
		// 	'RewriteRule ^.*$ '.$home_root.'wp-content/cache/html%{REQUEST_URI} [L]',
		// 	'</IfModule>'
		// );

		$rules = array(
			'<IfModule mod_rewrite.c>',
			'RewriteEngine On',
			'RewriteBase '.$home_root,
			'RewriteCond %{QUERY_STRING} =""',
			'RewriteCond %{DOCUMENT_ROOT}/arcoop/wp-content/cache/html%{REQUEST_URI} -f [OR]',
			'RewriteCond %{DOCUMENT_ROOT}/arcoop/wp-content/cache/html%{REQUEST_URI}index.html -f [OR]',
			'RewriteCond %{DOCUMENT_ROOT}/arcoop/wp-content/cache/html%{REQUEST_URI}/index.html -f',
			'RewriteRule ^.*$ '.$home_root.'wp-content/cache/html%{REQUEST_URI} [L]',
			'</IfModule>'
		);


		$this->remove(); // just in case

		if (file_exists($htaccess_file) && is_writable($htaccess_file)) {

			$htaccess_content = file_get_contents($htaccess_file);

			$start_marker = '# BEGIN ' . $this->mod_rewrite_marker . "\n";
			$end_marker   = '# END ' . $this->mod_rewrite_marker . "\n";

			$rules_content = $start_marker;
			$rules_content .= implode("\n", $rules) . "\n";
			$rules_content .= $end_marker;

			file_put_contents($htaccess_file, $rules_content . $htaccess_content);

		}

	}

	/**
	 * remove mod_rewrite modules into .htaccess file
	 */
	public function remove() {

		// $htaccess_file = get_home_path() . '.htaccess';

		$htaccess_file = ABSPATH . '.htaccess';

		if (file_exists($htaccess_file) && is_writable($htaccess_file)) {

			$htaccess_content = file_get_contents($htaccess_file);

			$start_marker = '# BEGIN ' . $this->mod_rewrite_marker . "\n";
			$end_marker   = '# END ' . $this->mod_rewrite_marker . "\n";

			$start_index = strpos($htaccess_content, $start_marker);
			$end_index = strpos($htaccess_content, $end_marker);

			if ($start_index !== false && $end_index > $start_index) {

				$rules_content = substr($htaccess_content, $start_index, $end_index - $start_index + strlen($end_marker));

				$htaccess_content = str_replace($rules_content, '', $htaccess_content);

				file_put_contents($htaccess_file, $htaccess_content);

			}

		}

	}

}

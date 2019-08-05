<?php


/**
 * Read/write into wp-content sub-directory
 */
class Karma_Cache_System_File {

	public $directory = 'cache';

	/**
	 * get url
	 */
	public function get_url($dir = '', $file = '') {

		$url = WP_CONTENT_URL . '/' . $this->directory;

		if ($dir) {

			$url .= '/' . $dir;

		}

		if ($file) {

			$url .= '/' . $file;

		}

		return $url;

	}

	/**
	 * get path
	 */
	public function get_path($dir = '', $file = '') {

		$path = WP_CONTENT_DIR . '/' . $this->directory;

		if ($dir) {

			$path .= '/' . $dir;

		}

		if ($file) {

			$path .= '/' . $file;

		}

		return $path;

	}

	/**
	 * Check file
	 */
	public function file_exists($dir = '', $file = '') {

		$path = $this->get_path($dir, $file);

		return file_exists($path);

	}

	/**
	 * Write file
	 */
	public function write_file($dir, $filename, $data) {

		$path = $this->get_path($dir);

		if (!file_exists($path)) {

			mkdir($path, 0777, true);

		}

		file_put_contents($path . '/' . $filename, $data);

	}

	/**
	 * Read file
	 */
	public function read_file($dir, $filename) {

		$file = $this->get_path($dir, $filename);

		if (file_exists($file)) {

			return file_get_contents($file);

		}

		return false;
	}

	/**
	 * Erase directory
	 */
	public function erase_dir($dir = '', $file = '') {

		$path = $this->get_path($dir, $file);

		$this->rrmdir($path);

	}

	/**
	 * Erase directory
	 */
	// public function erase_file($dir = '', $file = '') {
	//
	// 	$path = $this->get_path($dir, $file);
	//
	// 	unlink($path);
	//
	// 	return $path;
	//
	// }

	/**
	 * DEPRECATED
	 *
	 * Erase cache
	 */
	public function erase_root() {

		$this->erase_dir();

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

					// if (is_dir($dir."/".$object)) {
					//
					// 	$this->rrmdir($dir."/".$object);
					//
				 	// } else {
					//
					// 	unlink($dir."/".$object);
					//
					// }

				}

			}

			rmdir($dir);

		} else if (is_file($dir)) {

			unlink($dir);

		}

	}

}

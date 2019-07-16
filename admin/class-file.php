<?php


/**
 * Read/write into wp-content sub-directory
 */
class Karma_Content_Directory {

	public $directory = 'cache';

	/**
	 * get url
	 */
	public function get_url($dir, $file) {

		return WP_CONTENT_URL . '/' . $this->directory . '/' . $dir . '/' . $file;

	}

	/**
	 * Check file
	 */
	public function file_exists($dir, $file = '') {

		$path = WP_CONTENT_DIR . '/' . $this->directory . '/' . $dir . '/' . $file;

		return file_exists($path);

	}

	/**
	 * Write file
	 */
	public function write_file($dir, $filename, $data) {

		$path = WP_CONTENT_DIR . '/' . $this->directory . '/' . $dir;

		if (!file_exists($path)) {

			mkdir($path, 0777, true);

		}

		file_put_contents($path . '/' . $filename, $data);

	}

	/**
	 * Read file
	 */
	public function read_file($dir, $filename) {

		$file = WP_CONTENT_DIR . '/' . $this->directory . '/' . $dir . '/' . $filename;

		if (file_exists($file)) {

			return file_get_contents($file);

		}

		return false;
	}

	/**
	 * Erase directory
	 */
	public function erase_dir($dir) {

		$dir = WP_CONTENT_DIR . '/' . $this->directory . '/' . $dir;

		$this->rrmdir($dir);

	}

	/**
	 * Erase cache
	 */
	public function erase_root() {

		$dir = WP_CONTENT_DIR . '/' . $this->directory;

		$this->rrmdir($dir);

	}

	/**
	 * Remove directory and all content
	 */
	private function rrmdir($dir) {

		if (is_dir($dir)) {

			$objects = scandir($dir);

			foreach ($objects as $object) {

				if ($object != "." && $object != "..") {

					if (is_dir($dir."/".$object)) {

						$this->rrmdir($dir."/".$object);

				 	} else {

						unlink($dir."/".$object);

					}

				}

			}

			rmdir($dir);
		}

	}

}

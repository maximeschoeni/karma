<?php


/**
 * Read/write into wp-content sub-directory
 */
class Karma_Files {

	/**
	 * Write file
	 */
	public function write_file($path, $filename, $data) {

		$file = $path . '/' . $filename;
		// if (!file_exists($path)) {
		//
		// 	mkdir($path, 0777, true);
		//
		// }

		if (!file_exists(dirname($file))) {

			mkdir(dirname($file), 0777, true);

		}

		file_put_contents($file, $data);

	}

	/**
	 * Read file
	 */
	public function read_file($path, $filename) {

		$file = $path . '/' . $filename;

		if (file_exists($file)) {

			return file_get_contents($file);

		}

		return '';
	}

	/**
	 * Erase directory
	 */
	public function remove($path) {

		$this->rrmdir($path);

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

				}

			}

			rmdir($dir);

		} else if (is_file($dir)) {

			unlink($dir);

		}

	}

	

}

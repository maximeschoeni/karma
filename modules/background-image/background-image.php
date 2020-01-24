<?php


// $reflector = new \ReflectionClass('Karma_Background_Image');
// echo $reflector->getFileName();
// die();

global $karma_background_image;

Class Karma_Background_Image {

	var $version = '0.1';

	/**
	 *	constructor
	 */
	public function __construct() {

		if (!is_admin()) {

			add_action('init', array($this, 'init'));

		}

	}

	/**
	 * @hook init
	 */
	public function init() {

		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 10);

	}

	/**
	 * @hook 'wp_enqueue_scripts'
	 */
	public function enqueue_scripts() {

		wp_register_script('karma-background-image', get_template_directory_uri() . '/modules/background-image/js/background-image.js', array('build'), $this->version, true);

	}

	/**
	 * print image
	 */
	public static function print_image($image_id, $size = 'cover', $postion = 'center') {

		echo apply_filters(
			'background-image',
			'<div style="width:100%;height:100%;background-image:url('.wp_get_attachment_url($image_id).');background-size:'.$size.';background-position:'.$postion.'"></div>',
			$image_id,
			$size,
			$postion,
			array('class' => 'background-image'));

	}

	/**
	 * Get image sources
	 */
	public function get_image_source($img_id, $img_sizes = null, $type = 'image/jpeg') {
		static $baseurl;

		if (!isset($baseurl)) {

			$uploads = wp_get_upload_dir();
			$baseurl = $uploads['baseurl'] . '/';

		}

		$sources = array();
		$metadata = wp_get_attachment_metadata($img_id);
		$path = '';
		$file = get_post_meta($img_id, '_wp_attached_file', true);



		if ($type === 'image/jpeg' || $type === 'image/jpg' || $type === 'image/png') {

			if (!$img_sizes) {

				$img_sizes = get_intermediate_image_sizes();

			}

			$basename = basename($file);
			$path = str_replace($basename, '', $file);

			foreach ($img_sizes as $img_size) {

				if (isset($metadata['sizes'][$img_size])) {

					$sources[] = array(
						'src' => $baseurl . $path . $metadata['sizes'][$img_size]['file'],
						'width' => $metadata['sizes'][$img_size]['width'],
						'height' => $metadata['sizes'][$img_size]['height']
					);

				}

			}

			if (!$sources) {

				$sources[] = array(
					'src' => $baseurl . $file,
					'width' => $metadata['width'],
					'height' => $metadata['height']
				);

			}


// 		full ->
//
// 			$sources[] = array(
// 				'src' => $metadata['file'],
// 				'width' => $metadata['width'],
// 				'height' => $metadata['height']
// 			);

		} else if (strpos($type, 'video') !== false) {

			$sources[] = array(
				'src' => $baseurl . $file,
				'width' => $metadata['width'],
				'height' => $metadata['height']
			);

		}

		return $sources;

	}

}

$karma_background_image = new Karma_Background_Image();

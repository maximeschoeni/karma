<?php

/**
 *	Class Karma_Sublanguage
 */
class Karma_Background_Image_Compat {

	/**
	 * get image sizes data
	 */
	public function get_image_data($attachement_id) {

		$metadata = wp_get_attachment_metadata($attachement_id);

		if ($metadata) {

			$sources = apply_filters('background-image-manager-sources', array(array(
				'src' => wp_get_attachment_url($attachement_id),
				'width' => $metadata['width'],
				'height' => $metadata['height']
			)), $attachement_id);

			return $sources;

		}

		return array();
	}

	/**
	 * get all image sizes data
	 */
	public function get_images_data($attachement_ids) {

		$images = array();

		foreach ($attachement_ids as $attachement_id) {

			$images[] = $this->get_image_data($attachement_id);

		}

		return $images;
	}

	/**
	 * print image
	 */
	public function print_image($image_id, $size = 'cover', $postion = 'center') {

		echo apply_filters(
			'background-image',
			'<div style="width:100%;height:100%;background-image:url('.wp_get_attachment_url($image_id).');background-size:'.$size.';background-position:'.$postion.'"></div>',
			$image_id,
			$size,
			$postion,
			array('class' => 'background-image'));

	}

}

global $karma_background_image_compat;
$karma_background_image_compat = new Karma_Background_Image_Compat;

function karma_get_image_data($attachement_id) {
	global $karma_background_image_compat;

	return $karma_background_image_compat->get_image_data($attachement_id);
}
function karma_get_images_data($attachement_ids) {
	global $karma_background_image_compat;

	return $karma_background_image_compat->get_images_data($attachement_ids);
}
function karma_print_image($image_id, $size = 'cover', $postion = 'center') {
	global $karma_background_image_compat;

	return $karma_background_image_compat->print_image($image_id, $size, $postion);
}

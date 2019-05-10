<?php


/**
 *	Class Admin
 */
class Karma_Attachment_Metabox {

	/**
	 *	Constructor
	 */
	public function __construct() {

		add_filter('attachment_fields_to_edit', array($this, 'print_attachment_thumbsize_metabox'), 10, 2);
		add_action('edit_attachment', array($this, 'save_attachment_thumbsize'));

	}

	/**
	 * Print attachment metafield
	 * @filter 'attachment_fields_to_edit'
	 */
	public function print_attachment_thumbsize_metabox($form_fields, $post) {

		ob_start();

		$image_format = get_post_meta($post->ID, 'col', true).'x'.get_post_meta($post->ID, 'row', true);

		$this->print_format_selector(null, 'image_format', $image_format);

		$form_fields['image_format'] = array(
			'label' => 'Format',
			'input' => 'html',
			'html' => ob_get_contents(),
		);

		ob_end_clean();

		return $form_fields;
	}


	/**
	 * Save meta box
	 * @hook 'edit_attachment'
	 */
	public function save_attachment_thumbsize($attachment_id) {

		if (isset($_REQUEST['image_format'])) {

			$image_format = $_REQUEST['image_format'];

			$values = explode('x', $image_format);

			if (count($values) === 2) {

				update_post_meta($attachment_id, 'col', intval($values[0]));
				update_post_meta($attachment_id, 'row', intval($values[1]));

			}

    }

	}


}

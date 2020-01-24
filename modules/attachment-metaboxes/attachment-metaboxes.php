<?php


/**
 *	Class Admin
 */
class Karma_Attachment_Metabox {

	public $metaboxes = array();

	/**
	 *	Constructor
	 */
	public function __construct() {

		add_filter('attachment_fields_to_edit', array($this, 'print_attachment_thumbsize_metabox'), 10, 2);
		add_action('edit_attachment', array($this, 'save_attachment_thumbsize'));

		add_action('karma_register_attachment_metabox', array($this, 'register'), 10, 4);

	}

	/**
	 * API
	 * @hook 'karma_register_attachment_metabox'
	 */
	public function register($id, $label, $get_callback, $update_callback) {

		$this->metaboxes[] = array(
			'id' => $id,
			'label' => $label,
			'get' => $get_callback,
			'update' => $update_callback
		);

	}

	/**
	 * Print attachment metafield
	 * @filter 'attachment_fields_to_edit'
	 */
	public function print_attachment_thumbsize_metabox($form_fields, $post) {

		foreach ($this->metaboxes as $metabox) {

			$id = $metabox['id'];
			$form_fields[$id] = array(
				'label' => $metabox['label'],
				'input' => 'html',
				'html' => call_user_func($metabox['get'], $id, $post)
			);

		}

		return $form_fields;
	}


	/**
	 * Save meta box
	 * @hook 'edit_attachment'
	 */
	public function save_attachment_thumbsize($attachment_id) {

		foreach ($this->metaboxes as $metabox) {

			$id = $metabox['id'];

			if (isset($_REQUEST[$id])) {

				$value = $_REQUEST[$id];

				call_user_func($metabox['update'], $id, $value, $attachment_id);

	    }

		}

	}

}

new Karma_Attachment_Metabox;

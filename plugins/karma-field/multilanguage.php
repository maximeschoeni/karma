<?php


Class Karma_Field_Multilanguage {

	/**
	 *	constructor
	 */
	public function __construct() {

		add_filter('karma_field_input', array($this, 'field_input'), 10, 5);

	}

	/**
	 * @filter 'karma_field_input'
	 */
	public function field_input($args, $post_id, $meta_key, $type) {
		global $sublanguage_admin;

		$post = get_post($post_id);

		if (isset($sublanguage_admin) && $sublanguage_admin->is_post_type_translatable($post->post_type) && $sublanguage_admin->is_meta_key_translatable($post->post_type, $meta_key) && $sublanguage_admin->is_sub()) {

			$args['placeholder'] = $sublanguage_admin->get_untranslated_post_meta($post_id, $meta_key, true);

		}

		return $args;
	}

}

new Karma_Field_Multilanguage;

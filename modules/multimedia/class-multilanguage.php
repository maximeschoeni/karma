<?php


Class Karma_Multimedia_Multilanguage {

	/**
	 *	constructor
	 */
	public function __construct() {

		// add_action('init', array($this, 'init'));

		// apply_filters('sublanguage_postmeta_override', $meta_val, $key, $object_id)

		// add_filter('sublanguage_postmeta_override', array($this, 'postmeta_override'), 10, 3);



		add_filter('karma_multimedia_prepare_medias', array($this, 'prepare_media'), 10, 5);
		add_filter('karma_multimedia_save_medias', array($this, 'save_media'), 10, 4);


	}


	/**
	 * @filter 'karma_multimedia_prepare_medias'
	 */
	public function prepare_media($medias_obj, $post_id, $meta_key, $types, $columns) {
		global $sublanguage_admin;

		$post = get_post($post_id);

		if (isset($sublanguage_admin) && $sublanguage_admin->is_post_type_translatable($post->post_type) && $sublanguage_admin->is_meta_key_translatable($post->post_type, $meta_key) && $sublanguage_admin->is_sub()) {

			$translatable_columns = array();

			foreach ($columns as $column) {

				if (isset($column['translatable']) && $column['translatable']) {

					$translatable_columns[] = $column['key'];

				}

			}

			$translated_items = $medias_obj;
			$untranslated_items = $sublanguage_admin->get_untranslated_post_meta($post_id, $meta_key, true);

			foreach ($untranslated_items as $index => $item) {

				foreach ($item as $key => $column) {

					if (in_array($key, $translatable_columns)) {

						$column->placeholder = $column->text;

						if (empty($translated_items[$index]->$key->text) || $column->text === $translated_items[$index]->$key->text) {

							$column->text = '';

						} else {

							$column->text = $translated_items[$index]->$key->text;

						}

					}

				}

			}

			$medias_obj = $untranslated_items;

		}

		return $medias_obj;
	}


	/**
	 * @filter 'karma_multimedia_save_medias'
	 */
	public function save_media($medias_obj, $post_id, $meta_key, $medias_json) {
		global $sublanguage_admin;

		$post = get_post($post_id);

		if (isset($sublanguage_admin) && $sublanguage_admin->is_post_type_translatable($post->post_type) && $sublanguage_admin->is_meta_key_translatable($post->post_type, $meta_key) && $sublanguage_admin->is_sub()) {

			$translated_items = $medias_obj;
			$untranslated_items = json_decode($medias_json); // need an exact copie without referencies

			foreach ($untranslated_items as $index => $item) {

				foreach ($item as $key => $column) {

					if (isset($column->placeholder, $column->text)) {

						$column->text = $column->placeholder;
						unset($column->placeholder);
						unset($translated_items[$index]->$key->placeholder);

					}

				}

			}

			$medias_obj = $translated_items;

			$main_language = $sublanguage_admin->get_main_language();

			$sublanguage_admin->set_language($main_language);

			update_post_meta($post_id, $meta_key, $untranslated_items);

			$sublanguage_admin->set_language();

		}

		return $medias_obj;
	}



}


new Karma_Multimedia_Multilanguage;

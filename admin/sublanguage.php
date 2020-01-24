<?php

/**
 *	Class Karma_Sublanguage
 */
class Karma_Sublanguage {

	/**
	 *	Constructor
	 */
	public function __construct() {

		if (is_admin()) {

			add_filter('karma_default_meta_value', array($this, 'get_default_meta_value'), 10, 3);
			add_filter('karma_default_field_value', array($this, 'get_default_field_value'), 10, 3);

			// add_action('karma_cluster_update', array($this, 'cluster_update'), 10, 3);

		}

		add_filter('karma_append_language_to_path', array($this, 'append_language_to_path'), 10, 2);

		// add_filter('karma_cluster_path', array($this, 'append_language_to_path'), 10, 2);
		//
		// add_filter('karma_html_cache_url', array($this, 'append_language_to_query'));
		// add_filter('karma_cluster_link_raw', array($this, 'append_language_to_query'));
		//
		// add_filter('karma_cluster_items_to_update', array($this, 'cluster_items_to_update'));



	}

	/**
	 * @filter 'karma_append_language_to_url'
	 */
	public function append_language_to_path($path, $post_type = null) {
		global $sublanguage, $sublanguage_admin;

		if (isset($sublanguage_admin) && $post_type && $sublanguage_admin->is_post_type_translatable($post_type) && (!$sublanguage_admin->is_default() || $sublanguage_admin->get_option('show_slug'))) {

			$language = $sublanguage_admin->get_language();

		} else if (isset($sublanguage) && $post_type && $sublanguage->is_post_type_translatable($post_type) && (!$sublanguage->is_default() || $sublanguage->get_option('show_slug'))) {

			$language = $sublanguage->get_language();

		}

		if (isset($language) && $language) {

			$path .= '/' . $language->post_name;

		}

		return $path;
	}

	/**
	 * @filter 'karma_default_meta_value'
	 */
	public function get_default_meta_value($default, $post, $meta_key) {
		global $sublanguage_admin;

		if (isset($sublanguage_admin) && $sublanguage_admin->is_sub() && $sublanguage_admin->is_post_type_translatable($post->post_type)) {

			$language = $sublanguage_admin->get_main_language();

			return $sublanguage_admin->get_post_meta_translation($post, $meta_key, true, $language);

		}

		return $default;
	}

	/**
	 * @filter 'karma_default_field_value'
	 */
	public function get_default_field_value($default, $post, $field) {
		global $sublanguage_admin;

		if (isset($sublanguage_admin) && $sublanguage_admin->is_sub() && $sublanguage_admin->is_post_type_translatable($post->post_type)) {

			$language = $sublanguage_admin->get_main_language();

			return $sublanguage_admin->translate_post_field($post, $field, $language, $default);

		}

		return $default;
	}


	/**
	 * @hook 'karma_cluster_update'
	 */
	public function cluster_update($post, $cluster, $clusters) {
		// global $sublanguage_admin;
		//
		// if (isset($sublanguage_admin) && $sublanguage_admin->is_post_type_translatable($post->post_type)) {
		//
		// 	$current_language = $sublanguage_admin->get_language();
		//
		// 	foreach ($sublanguage_admin->get_languages() as $language) {
		//
		// 		if ($language !== $current_language) {
		//
		// 			$sublanguage_admin->set_language($language);
		//
		// 			$cluster = $clusters->create_cluster($post);
		// 			$clusters->update_cache($cluster);
		//
		// 		}
		//
		// 	}
		//
		// 	$sublanguage_admin->set_language($current_language);
		//
		// }

	}

	/**
	 * @filter 'karma_cluster_items_to_update'
	 */
	public function cluster_items_to_update($items) {
		global $sublanguage_admin;

		$new_items = array();

		if (isset($sublanguage_admin)) {

			foreach ($items as $item) {

				if ($sublanguage_admin->is_post_type_translatable($item['post_type'])) {

					foreach ($sublanguage_admin->get_languages() as $language) {

						$new_item = $item;
						$new_item['language'] = $language->post_name;
						$new_items[] = $new_item;

					}

				} else {

					$new_item = $item;
					$new_items[] = $new_item;

				}

			}

			return $new_items;

		}

		return $items;
	}

	/**
	 * @filter 'karma_append_language_to_query'
	 */
	public function append_language_to_query($query) {
		global $sublanguage, $sublanguage_admin;

		if (isset($sublanguage_admin) && $sublanguage_admin->is_sub()) {

			$language = $sublanguage_admin->get_language();

		} else if (isset($sublanguage) && $sublanguage->is_sub()) {

			$language = $sublanguage->get_language();

		}

		if (isset($language) && $language) {

			$query = add_query_arg(array('language' => $language->post_name), $query);

		}

		return $query;
	}



}

new Karma_Sublanguage;

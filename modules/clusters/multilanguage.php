<?php

/**
 *	Class Karma_Sublanguage
 */
class Karma_Cluster_Multilanguage {

	/**
	 *	Constructor
	 */
	public function __construct() {

		add_filter('karma_cluster_path', array($this, 'append_language_to_path'), 10, 2);
		add_filter('karma_cluster_link_raw', array($this, 'append_language_to_query'));
		add_filter('karma_cluster_items_to_update', array($this, 'cluster_items_to_update'));

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

					$new_items[] = $item;

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

new Karma_Cluster_Multilanguage;

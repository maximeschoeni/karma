<?php

/**
 *	Class Karma_HTMLCache_Multilanguage
 */
class Karma_HTMLCache_Multilanguage {

	/**
	 *	Constructor
	 */
	public function __construct() {

		add_filter('karma_html_cache_url', array($this, 'append_language_to_query'));
		add_filter('karma_htmlcache_items_to_update', array($this, 'items_to_update'));

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

	/**
	 * @filter 'karma_htmlcache_items_to_update'
	 */
	public function items_to_update($items) {
		global $sublanguage_admin;

		$new_items = array();

		if (isset($sublanguage_admin)) {

			foreach ($items as $item) {

				foreach ($sublanguage_admin->get_languages() as $language) {

					$new_item = $item;
					// $new_item['language'] = $language->post_name;
					$new_item['url'] = add_query_arg(array('language' => $language->post_name), $item['url']);
					$new_items[] = $new_item;

				}

			}

			return $new_items;

		}

		return $items;
	}



}

new Karma_HTMLCache_Multilanguage;

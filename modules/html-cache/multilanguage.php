<?php

/**
 *	Class Karma_HTMLCache_Multilanguage
 */
class Karma_HTMLCache_Multilanguage {

	/**
	 *	Constructor
	 */
	public function __construct() {

		// add_filter('karma_html_cache_url', array($this, 'append_language_to_query'));
		// add_filter('karma_htmlcache_items_to_update', array($this, 'items_to_update'));

		// add_filter('karma_htmlcache_post_request', array($this, 'post_request'), 10, 3);
		// add_filter('karma_htmlcache_term_request', array($this, 'term_request'), 10, 3);
		// add_filter('karma_htmlcache_archive_request', array($this, 'archive_request'), 10, 3);
		//
		// add_filter('karma_htmlcache_post_path', array($this, 'post_path'), 10, 4);
		// add_filter('karma_htmlcache_term_path', array($this, 'term_path'), 10, 4);
		// add_filter('karma_htmlcache_archive_path', array($this, 'archive_path'), 10, 4);

		add_action('karma_htmlcache_update_home_sitepage', array($this, 'update_home_sitepage'), 10, 3);
		add_action('karma_htmlcache_update_post_sitepage', array($this, 'update_post_sitepage'), 10, 5);
		add_action('karma_htmlcache_update_archive_sitepage', array($this, 'update_archive_sitepage'), 10, 4);
		add_action('karma_htmlcache_update_term_sitepage', array($this, 'update_term_sitepage'), 10, 4);

		add_action('karma_htmlcache_remove_post_sitepage', array($this, 'remove_post_sitepage'), 10, 3);
		add_action('karma_htmlcache_remove_term_sitepage', array($this, 'remove_term_sitepage'), 10, 3);

	}


	/**
	 * @hook 'karma_htmlcache_update_post_sitepage'
	 */
	public function update_post_sitepage($post, $url, $path, $parent_url, $karma_cache) {
		global $sublanguage_admin;

		if (isset($sublanguage_admin) && $sublanguage_admin->is_post_type_translatable($post->post_type)) {

			foreach ($sublanguage_admin->get_languages() as $language) {

				if ($sublanguage_admin->is_sub($language)) {

					$path = $this->get_post_path($post, $language, $path);
					$url = add_query_arg(array('language' => $language->post_name), $url);
					$parent_url = add_query_arg(array('language' => $language->post_name), $parent_url);

					$karma_cache->update_sitepage($url, $path, $parent_url);

					do_action('karma_htmlcache_update_post_sitepage_language', $post, $url, $path, $parent_url, $karma_cache, $language, $sublanguage_admin);

				}

			}

		}

	}

	/**
	 * @hook 'karma_htmlcache_remove_post_sitepage'
	 */
	public function remove_post_sitepage($post, $url, $karma_cache) {
		global $sublanguage_admin;

		if (isset($sublanguage_admin) && $sublanguage_admin->is_post_type_translatable($post->post_type)) {

			foreach ($sublanguage_admin->get_languages() as $language) {

				if ($sublanguage_admin->is_sub($language)) {

					$url = add_query_arg(array('language' => $language->post_name), $url);

					$karma_cache->remove_sitepage($url);

					do_action('karma_htmlcache_remove_post_sitepage_language', $post, $url, $karma_cache, $language, $sublanguage_admin);

				}

			}

		}

	}

	/**
	 * get_post_path
	 */
	public function get_post_path($post, $language, $fallback = null) {
		global $sublanguage_admin;

		if (isset($sublanguage_admin) && $sublanguage_admin->is_post_type_translatable($post->post_type)) {

			$post_type_obj = get_post_type_object($post->post_type);

			$parent_id = $post->post_parent;

			$path = $sublanguage_admin->translate_post_field($post, 'post_name', $language, $post->post_name);

			while ($parent_id && $post_type_obj->hierarchical) {

				$parent = get_post($parent_id);

				$path = $sublanguage_admin->translate_post_field($parent, 'post_name', $language, $parent->post_name).'/'.$path;

				$parent_id = $parent->post_parent;

			}

			if ($post_type_obj->rewrite) {

				$path = $sublanguage_admin->translate_cpt_archive($post->post_type, $language, $post_type_obj->rewrite['slug']).'/'.$path;

			}

			$path = $language->post_name.'/'.$path;

			return $path;

		}

		return $fallback;

	}
	/**
	 * @filter 'karma_htmlcache_post_path'
	 */
	// public function post_path($path, $post, $post_type_obj, $karma_cache) {
	//
	// 	return $this->get_post_path($post, null, $path);
	//
	// }

	/**
	 * @hook 'karma_htmlcache_update_archive_sitepage'
	 */
	public function update_archive_sitepage($post_type, $url, $path, $karma_cache) {
		global $sublanguage_admin;

		if (isset($sublanguage_admin) && $sublanguage_admin->is_post_type_translatable($post_type)) {

			foreach ($sublanguage_admin->get_languages() as $language) {

				if ($sublanguage_admin->is_sub($language)) {

					$path = $this->get_archive_path($post_type, $language, $path);
					$url = add_query_arg(array('language' => $language->post_name), $url);

					$karma_cache->update_sitepage($url, $path);

					do_action('karma_htmlcache_update_archive_sitepage_language', $post_type, $url, $path, $karma_cache, $language, $sublanguage_admin);

				}

			}

		}

	}

	/**
	 * get_archive_path
	 */
	public function get_archive_path($post_type, $language, $fallback = null) {
		global $sublanguage_admin;

		if (isset($sublanguage_admin) && $sublanguage_admin->is_post_type_translatable($post_type)) {

			$path = $sublanguage_admin->translate_cpt_archive($post_type, $language, $fallback);

			$path = $language->post_name.'/'.$path;

			return $path;

		}

		return $fallback;
	}

	/**
	 * @filter 'karma_htmlcache_archive_path'
	 */
	// public function archive_path($path, $post_type, $post_type_obj, $karma_cache) {
	//
	// 	 return $this->get_archive_path($post_type, null, $path);
	//
	// }

	/**
	 * @hook 'karma_htmlcache_update_term_sitepage'
	 */
	public function update_term_sitepage($term, $url, $path, $karma_cache) {
		global $sublanguage_admin;

		if (isset($sublanguage_admin) && $sublanguage_admin->is_taxonomy_translatable($term->taxonomy)) {

			foreach ($sublanguage_admin->get_languages() as $language) {

				if ($sublanguage_admin->is_sub($language)) {

					$path = $this->get_term_path($term, $language, $path);
					$url = add_query_arg(array('language' => $language->post_name), $url);

					$karma_cache->update_sitepage($url, $path);

					do_action('karma_htmlcache_update_term_sitepage_language', $term, $url, $path, $karma_cache, $language, $sublanguage_admin);

				}

			}

		}

	}

	/**
	 * @hook 'karma_htmlcache_remove_term_sitepage'
	 */
	public function remove_term_sitepage($term, $url, $karma_cache) {
		global $sublanguage_admin;

		if (isset($sublanguage_admin) && $sublanguage_admin->is_taxonomy_translatable($term->taxonomy)) {

			foreach ($sublanguage_admin->get_languages() as $language) {

				if ($sublanguage_admin->is_sub($language)) {

					$url = add_query_arg(array('language' => $language->post_name), $url);

					$karma_cache->remove_sitepage($url);

					do_action('karma_htmlcache_remove_term_sitepage_language', $term, $url, $karma_cache, $language, $sublanguage_admin);

				}

			}

		}

	}

	/**
	 * get_term_path
	 */
	public function get_term_path($term, $language, $fallback = null) {
 		global $sublanguage_admin;

 		if (isset($sublanguage_admin) && $sublanguage_admin->is_taxonomy_translatable($term->taxonomy)) {

 			$path = $sublanguage_admin->translate_term_field($term, $term->taxonomy, 'slug', $language, $term->slug);

 			// todo -> hierarchical taxonomy

			$taxonomy_obj = get_taxonomy($term->taxonomy);

 			if ($taxonomy_obj->rewrite['with_front']) {

 				$path = $sublanguage_admin->translate_taxonomy($term->taxonomy, $language, $taxonomy_obj->rewrite['slug']).'/'.$path;

 			}

			$path = $language->post_name.'/'.$path;

			return $path;

 		}

 		return $fallback;
 	}

	/**
	 * @filter 'karma_htmlcache_term_path'
	 */
	// public function term_path($path, $term, $taxonomy_obj, $karma_cache) {
	//
 	// 	return $this->get_term_path($term, null, $path);
	//
 	// }

	/**
	 * @hook 'karma_htmlcache_update_home_sitepage'
	 */
	public function update_home_sitepage($url, $path, $karma_cache) {
		global $sublanguage_admin;

		if (isset($sublanguage_admin)) {

			foreach ($sublanguage_admin->get_languages() as $language) {

				if ($sublanguage_admin->is_sub($language)) {

					$path = $language->post_name;
					$url = add_query_arg(array('language' => $language->post_name), $url);

					$karma_cache->update_sitepage($url, $path);

					do_action('karma_htmlcache_update_home_sitepage_language', $url, $path, $karma_cache, $language, $sublanguage_admin);

				}

			}

		}

	}

	/**
	 * @filter 'karma_htmlcache_post_request'
	 */
	// public function post_request($request, $post, $karma_cache) {
	// 	global $sublanguage_admin;
	//
	// 	if (isset($sublanguage_admin) && $sublanguage_admin->is_sub() && $sublanguage_admin->is_post_type_translatable($post->post_type)) {
	//
	// 		$language = $sublanguage_admin->get_language();
	//
	// 		$request = add_query_arg(array('language' => $language->post_name), $request);
	//
	// 	}
	//
	// 	return $request;
	// }

	/**
	 * @filter 'karma_htmlcache_archive_request'
	 */
	// public function archive_request($request, $post_type) {
	// 	global $sublanguage, $sublanguage_admin;
	//
	// 	if (isset($sublanguage_admin) && $sublanguage_admin->is_sub() && $sublanguage_admin->is_post_type_translatable($post_type)) {
	//
	// 		$language = $sublanguage_admin->get_language();
	//
	// 		$request = add_query_arg(array('language' => $language->post_name), $request);
	//
	// 	}
	//
	// 	return $request;
	// }

	/**
	 * @filter 'karma_append_language_to_request'
	 */
	// public function term_request($request, $term) {
	// 	global $sublanguage, $sublanguage_admin;
	//
	// 	if (isset($sublanguage_admin) && $sublanguage_admin->is_sub() && $sublanguage_admin->is_taxonomy_translatable($term->taxonomy)) {
	//
	// 		$language = $sublanguage_admin->get_language();
	//
	// 		$request = add_query_arg(array('language' => $language->post_name), $request);
	//
	// 	}
	//
	// 	return $request;
	// }






	/**
	 * @filter 'karma_htmlcache_term_query'
	 */
	// public function append_language_to_query($query) {
	// 	global $sublanguage, $sublanguage_admin;
	//
	// 	if (isset($sublanguage_admin) && $sublanguage_admin->is_sub()) {
	//
	// 		$language = $sublanguage_admin->get_language();
	//
	// 	} else if (isset($sublanguage) && $sublanguage->is_sub()) {
	//
	// 		$language = $sublanguage->get_language();
	//
	// 	}
	//
	// 	if (isset($language) && $language) {
	//
	// 		$query = add_query_arg(array('language' => $language->post_name), $query);
	//
	// 	}
	//
	// 	return $query;
	// }

	/**
	 * @filter 'karma_htmlcache_items_to_update'
	 */
	// public function items_to_update($items) {
	// 	global $sublanguage_admin;
	//
	// 	$new_items = array();
	//
	// 	if (isset($sublanguage_admin)) {
	//
	// 		foreach ($items as $item) {
	//
	// 			foreach ($sublanguage_admin->get_languages() as $language) {
	//
	// 				$new_item = $item;
	// 				// $new_item['language'] = $language->post_name;
	// 				$new_item['url'] = add_query_arg(array('language' => $language->post_name), $item['url']);
	// 				$new_items[] = $new_item;
	//
	// 			}
	//
	// 		}
	//
	// 		return $new_items;
	//
	// 	}
	//
	// 	return $items;
	// }



}

new Karma_HTMLCache_Multilanguage;

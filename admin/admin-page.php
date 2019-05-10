<?php

/**
 *	Class Karma_Admin_Event
 */
class Karma_Admin_Page {

	/**
	 *	Constructor
	 */
	public function __construct() {

		// public
		add_filter('nav_menu_link_attributes', array($this, 'nav_menu_link_attributes'), 10, 4);

		// admin
		add_action('save_post', array($this, 'save_post'), 99, 2);
		add_action('after_delete_post', array($this, 'delete_post'), 10, 1);
		add_action('karma_cache_write', array($this, 'cache_write'), 10, 4);
    add_action('wp_ajax_get_page', array($this, 'ajax_get_page'));
    add_action('wp_ajax_nopriv_get_page', array($this, 'ajax_get_page'));

	}

	/**
	 * @ajax 'get_event'
	 */
	public function ajax_get_page() {

		$output = array();

		if (isset($_GET['page_id'])) {

			$page = $this->update_page(intval($_GET['page_id']));

			if ($page) {

				$output['page'] = $page;

			}

		}

	 	echo json_encode($output);
		exit;
	}


	/**
	 * Get page link to fetch json
	 */
	public function get_page_json_link($page_id) {
		global $wp_object_cache, $sublanguage;

		if (isset($wp_object_cache->cache_dir) && is_file(WP_CONTENT_DIR . '/' . $wp_object_cache->cache_dir . '/' . $wp_object_cache->object_dir . '/event/' . $page_id . '/data.json')) {

			if (isset($sublanguage) && $sublanguage->is_sub()) {

				$language .= '/' . $sublanguage->get_language()->post_name;

			} else {

				$language = '';

			}

			return WP_CONTENT_URL . '/' . $wp_object_cache->cache_dir . '/' . $wp_object_cache->object_dir . '/event/' . $page_id . $language . '/data.json';

		} else {

			return add_query_arg(array(
				'action' => 'get_page',
				'page_id' => $page_id
			), admin_url('admin-ajax.php'));

		}

	}

	/**
	 * @filter 'nav_menu_item_args'
	 */
	public function nav_menu_link_attributes($atts, $item, $args, $depth) {

		if ($item->type === 'post_type' && $item->object === 'page') {

			//$this->get_page_json_link($item->object_id)
			$atts['data-json'] = $this->get_page_json_link($item->object_id);

		}

		return $atts;
	}

	/**
	 * @hook 'karma_cache_write'
	 */
	public function cache_write($data, $key, $group, $object_cache) {
		global $sublanguage_admin;

		// if ($group === 'event' || substr($group, 0, 6) === 'event/') {
		if ($group === 'page-content') {

			$path = $object_cache->object_dir . '/' . $group . '/' . $key;

			$filename = 'data.json';

			if (isset($sublanguage_admin) && $sublanguage_admin->is_sub()) {

				$filename = $sublanguage_admin->get_language()->post_name . '-' . $filename;

			}

			$object_cache->write_file($path, $filename, json_encode($data, JSON_PRETTY_PRINT));

		}

	}



	/**
	 * @hook 'save_post'
	 */
	public function save_post($post_id, $post) {

		if ($post->post_type === 'page') {

			$this->update_page($post_id);

		}

	}

	/**
	 * @hook 'after_delete_post'
	 */
	public function delete_post($post_id) {

		// if post is event
		wp_cache_delete($post_id, 'page-content');

	}

	/**
	 * get page
	 */
	public function get_page($page_id) {

		$post = get_post($page_id);

		if ($post && $post->post_type === 'page') {

			return array(
				'content' => apply_filters('the_content', apply_filters('sublanguage_translate_post_field', $post->post_content, $post, 'post_content')),
				'title' => get_the_title($post)
			);
		}

	}

	/**
	 * update event
	 */
	public function update_page($page_id) {

		$page = $this->get_page($page_id);

		if ($page) {

			wp_cache_set($page_id, $page, 'page-content');

			return $page;

		}

		// $post = get_post($page_id);
		//
		// if ($post && $post->post_type === 'page') {
		//
		// 	$page = array(
		// 		'content' => apply_filters('the_content', apply_filters('sublanguage_translate_post_field', $post->post_content, $post, 'post_content')),
		// 		'title' => get_the_title($post)
		// 	);
		//
		// 	wp_cache_set($page_id, $post_content, 'page-content');
		//
		// 	return $post_content;
		//
		// }

	}



}

new Karma_Admin_Page;

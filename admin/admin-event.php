<?php

/**
 *	Class Karma_Admin_Event
 */
class Karma_Admin_Event {

	/**
	 *	Constructor
	 */
	public function __construct() {

		add_action('save_post', array($this, 'save_post'), 99, 2);
		add_action('after_delete_post', array($this, 'delete_post'), 10, 1);

		// add_action('add_post_meta', array($this, 'add_post_meta'), 10, 3);
		// add_action('update_post_meta', array($this, 'update_post_meta'), 10, 4);
		// add_action('delete_post_meta', array($this, 'update_post_meta'), 10, 4);

		add_action('karma_cache_write', array($this, 'cache_write'), 10, 4);

    add_action('wp_ajax_get_event', array($this, 'ajax_get_event'));
    add_action('wp_ajax_nopriv_get_event', array($this, 'ajax_get_event'));

	}

	/**
	 * @ajax 'get_event'
	 */
	public function ajax_get_event() {

		$output = array();

		if (isset($_GET['event_id'])) {

			$output = $this->update_event(intval($_GET['event_id']));

		}

	 	echo json_encode($output);
		exit;
	}

	/**
	 * @hook 'add_post_meta' ("add_{$meta_type}_meta")
	 */
	public function cache_write($data, $key, $group, $object_cache) {
		global $sublanguage_admin;

		// if ($group === 'event' || substr($group, 0, 6) === 'event/') {
		if ($group === 'event') {

			$path = $object_cache->object_dir . '/' . $group . '/' . $key;

			if (isset($sublanguage_admin) && $sublanguage_admin->is_sub()) {

				$path .= '/' . $sublanguage_admin->get_language()->post_name;

			}

			$object_cache->write_file($path, 'data.json', json_encode($data, JSON_PRETTY_PRINT));

		}

	}

	/**
	 * @hook 'wp_insert_post'
	 */
	public function save_post($post_id, $post) {

		if ($post->post_type === 'event') {

			if ($post->post_parent) {

				$this->update_project($post->post_parent);

			} else {

				$this->update_event($post_id);

			}

		} else if ($post->post_type === 'project') {

			$this->update_project($post_id);

		}

	}

	/**
	 * @hook 'after_delete_post'
	 */
	public function delete_post($post_id) {

		// if post is event
		 wp_cache_delete($post_id, 'event');

		// if post is project
		$this->update_project($post_id);

	}


	/**
	 * @hook 'add_post_meta' ("add_{$meta_type}_meta")
	 */
	public function add_post_meta($object_id, $meta_key, $_meta_value) {

		$post = get_post($object_id);

		$this->save_post($object_id, $post);

	}

	/**
	 * @hook 'update_post_meta' ("update_{$meta_type}_meta")
	 * @hook 'delete_post_meta' ("delete_{$meta_type}_meta")
	 */
	public function update_post_meta($meta_id, $object_id, $meta_key, $_meta_value) {

		$post = get_post($object_id);

		$this->save_post($object_id, $post);

	}

	/**
	 * get event
	 */
	public function get_event($post_id) {
		global $wpdb;

		$post_event = get_post($post_id);

		$event = array(
			'title' => apply_filters('sublanguage_translate_post_field', $post_event->post_title, $post_event, 'post_title'),
			'content' => apply_filters('the_content', apply_filters('sublanguage_translate_post_field', $post_event->post_content, $post_event, 'post_content')),
			'start_date' => get_post_meta($post_event->ID, 'start_date', true),
			'end_date' => get_post_meta($post_event->ID, 'end_date', true),
			'hour' => get_post_meta($post_event->ID, 'hour', true),
			'name' => get_post_meta($post_event->ID, 'name', true),
			'place' => get_post_meta($post_event->ID, 'place', true),
			'city' => get_post_meta($post_event->ID, 'city', true),
			'country' => get_post_meta($post_event->ID, 'country', true),
			'description' => get_post_meta($post_event->ID, 'description', true),
			'auteur' => get_post_meta($post_event->ID, 'auteur', true),
			'project_id' => $post_event->post_parent
		);

		if ($post_event->post_parent) {

			$post_project = get_post($post_event->post_parent);

		}

		if (isset($post_project) && $post_project) {

			$event['project'] = array(
				'id' => $post_project->ID,
				'name' => $post_project->post_name,
				'title' => apply_filters('sublanguage_translate_post_field', $post_project->post_title, $post_project, 'post_title'),
				'content' => apply_filters('the_content', apply_filters('sublanguage_translate_post_field', $post_project->post_content, $post_project, 'post_content')),
				'description' => get_post_meta($post_project->ID, 'description', true),
				'auteur' => get_post_meta($post_project->ID, 'auteur', true),
				'program' => apply_filters('the_content', get_post_meta($post_project->ID, 'program_content', true)),
				'images' => array()
			);

			$related_event_ids = $this->get_project_children($post_event->post_parent);

			if ($related_event_ids) {

				foreach ($related_event_ids as $related_event_id) {

					if ($related_event_id !== $post_id) {

						$event['project']['events'][] = array(
							'start_date' => get_post_meta($related_event_id, 'start_date', true),
							'end_date' => get_post_meta($related_event_id, 'end_date', true),
							'hour' => get_post_meta($related_event_id, 'hour', true),
							'name' => get_post_meta($related_event_id, 'name', true),
							'place' => get_post_meta($related_event_id, 'place', true),
							'city' => get_post_meta($related_event_id, 'city', true),
							'country' => get_post_meta($related_event_id, 'country', true),
						);

					}

				}

			}

			$image_ids = get_post_meta($post_project->ID, 'images');

			if ($image_ids) {

				foreach ($image_ids as $image_id) {

					$metadata = wp_get_attachment_metadata($image_id);

					$event['project']['images'][] = apply_filters('background-image-manager-sources', array(
						'url' => wp_get_attachment_url($image_id),
						'width' => $metadata['width'],
						'height' => $metadata['height']
					), $image_id);

				}

			}

		}

		return $event;
	}

	/**
	 * update event
	 */
	public function update_event($event_id) {
		// global $sublanguage_admin;

		$data = $this->get_event($event_id);

		wp_cache_set($event_id, $data, 'event');

		return $data;
	}

	/**
	 * update project
	 */
	public function update_project($project_id) {
		global $wpdb;

		$event_ids = $this->get_project_children($project_id);

		foreach ($event_ids as $event_id) {

			$this->update_event($event_id);

		}

	}

	/**
	 * update project
	 */
	public function get_project_children($project_id) {
		global $wpdb;
		static $cache = array();

		if (!isset($cache[$project_id])) {

			$cache[$project_id] = array_map('intval', $wpdb->get_col($wpdb->prepare(
				"SELECT ID FROM $wpdb->posts
				WHERE post_parent = %d AND post_type = 'event' AND post_status = 'publish'",
				$project_id
			)));

		}

		return $cache[$project_id];
	}

}

new Karma_Admin_Event;

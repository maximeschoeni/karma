<?php

/**
 *	Class Karma_Post_type_Cache
 */
class Karma_Post_type_Cache {

	/**
	 *	Constructor
	 */
	public function init_cache() {

		if (isset($this->post_type)) {

			add_action('save_post_'.$this->post_type, array($this, 'save_post'), 99, 2);
			add_action('after_delete_post', array($this, 'delete_post'), 10, 1);

			add_action('karma_cache_write', array($this, 'cache_write'), 10, 4);

	    add_action('wp_ajax_get_'.$this->post_type, array($this, 'ajax_export'));
	    add_action('wp_ajax_nopriv_get_'.$this->post_type, array($this, 'ajax_export'));

		}


	}

	/**
	 * update event
	 */
	public function update($id) {

		$data = $this->export($id);

		wp_cache_set($id, $data, $this->post_type);

		return $data;
	}

	/**
	 * @ajax 'get_{$post_type}'
	 */
	public function ajax_export() {

		$output = array();

		if (isset($_GET['id'])) {

			$output = $this->update(intval($_GET['id']));

		}

	 	echo json_encode($output);
		exit;
	}

	/**
	 * @hook 'karma_cache_write'
	 */
	public function cache_write($data, $key, $group, $object_cache) {

		if ($group === $this->post_type) {

			$path = $object_cache->object_dir . '/' . $group . '/' . $key . apply_filters('append_language_to_path', '');

			$object_cache->write_file($path, 'data.json', json_encode($data, JSON_PRETTY_PRINT));

		}

	}

	/**
	 * @hook 'save_post_{$post_type}'
	 */
	public function save_post($post_id, $post) {

		$this->update($post_id);

	}

	/**
	 * @hook 'after_delete_post'
	 */
	public function delete_post($post_id) {

		// if post is event
		 wp_cache_delete($post_id, $this->post_type);

		// if post is event
		$this->update($post_id);

	}


	/**
	 * export. To be overrided
	 */
	public function export($post_id) {

		$post = get_post($post_id);

		$event = array(
			'id' => $post->ID,
			'name' => $post->post_name,
			'title' => apply_filters('sublanguage_translate_post_field', $post->post_title, $post, 'post_title'),
			'content' => apply_filters('the_content', apply_filters('sublanguage_translate_post_field', $post->post_content, $post, 'post_content')),
		);

		return $event;
	}

	/**
	 * get all image sizes data
	 */
	public function get_images_data($attachement_ids) {

		$images = array();

		foreach ($attachement_ids as $attachement_id) {

			$metadata = wp_get_attachment_metadata($attachement_id);

			$images[] = apply_filters('background-image-manager-sources', array(array(
				'url' => wp_get_attachment_url($attachement_id),
				'width' => $metadata['width'],
				'height' => $metadata['height']
			)), $attachement_id);

		}

		return $images;
	}

}

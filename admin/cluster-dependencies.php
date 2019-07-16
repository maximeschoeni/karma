<?php

/**
 *	Class Karma_Cluster_Dependencies
 */
class Karma_Cluster_Dependencies {

	public function __construct($post_id) {

		$this->post_id = $post_id;
		$this->clear($post_id);

	}

	/**
	 *
	 * clear dependencies
	 */
	public function clear($post_id) {
		global $wpdb;

		$wpdb->delete($wpdb->postmeta, array(
			'meta_key' => 'dependencies',
			'meta_value' => $post_id
		), array(
			'%s',
			'%d'
		));

		$wpdb->delete($wpdb->termmeta, array(
			'meta_key' => 'dependencies',
			'meta_value' => $post_id
		), array(
			'%s',
			'%d'
		));

	}

	/**
	 * add post dependency
	 */
	public function add_post_id($post_id) {

		add_post_meta($post_id, 'dependencies', $this->post_id);

	}

	/**
	 * add post dependencies
	 */
	public function add_post_ids($post_ids) {

		foreach ($post_ids as $post_id) {

			$this->add_post_id($post_id);

		}

	}

	/**
	 * add term dependency
	 */
	public function add_term_id($term_id) {

		add_term_meta($term_id, 'dependencies', $this->post_id);

	}

	/**
	 * add term dependencies
	 */
	public function add_term_ids($term_ids) {

		foreach ($term_ids as $term_id) {

			$this->add_term_id($term_id);

		}

	}


}

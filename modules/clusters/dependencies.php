<?php

/**
 *	Class Karma_Cluster_Dependencies
 */
class Karma_Cluster_Dependencies {

	// var $post_dependencies = array();
	// var $term_dependencies = array();

	public function __construct($post_id) {

		$this->post_id = $post_id;
		$this->clear($post_id);

	}

	/**
	 *
	 * clear dependencies
	 */
	public function update() {
		// global $karma;
		//
		// $dependencies = $karma->options->get_option('dependencies', array());
		// $dependencies[$this->post_id]['posts'] = array_unique($this->post_dependencies);
		// $dependencies[$this->post_id]['terms'] = array_unique($this->term_dependencies);
		//
		// $karma->options->update_option('dependencies', $dependencies);

	}


	/**
	 *
	 * clear dependencies
	 */
	public function clear($post_id) {
		global $wpdb;

		// $this->dependencies[$post_id] = array();


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

		// $this->post_dependencies[] = $post_id;



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

		// $this->dependencies[$this->post_id]['terms'][] = $term_id;
		// $this->term_dependencies[] = $term_id;

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

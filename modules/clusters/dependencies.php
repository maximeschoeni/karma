<?php

/**
 *	Class Karma_Cluster_Dependencies
 */
class Karma_Cluster_Dependencies {

	// var $post_dependencies = array();
	// var $term_dependencies = array();

	var $dependency_table;

	public function __construct($post_id, $dependency_table) {

		$this->dependency_table = $dependency_table;
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

		$wpdb->delete($wpdb->prefix.$this->dependency_table, array(
			'target_id' => $post_id,
		), array(
			'%d'
		));

	}

	/**
	 * add post_type dependency
	 */
	public function add_post_type($post_type) {
		global $wpdb;

		$wpdb->insert($wpdb->prefix.$this->dependency_table, array(
			'target_id' => $this->post_id,
			'object' => 'post',
			'type' => $post_type
		), array(
			'%d',
			'%s',
			'%s'
		));

	}

	/**
	 * add taxonomy dependency
	 */
	public function add_taxonomy($taxonomy) {
		global $wpdb;

		$wpdb->insert($wpdb->prefix.$this->dependency_table, array(
			'target_id' => $this->post_id,
			'object' => 'term',
			'type' => $taxonomy
		), array(
			'%d',
			'%s',
			'%s'
		));

		// $wpdb->insert($wpdb->prefix . 'cluster_tax_dep', array(
		// 	'object_id' => $this->post_id,
		// 	'taxonomy' => $taxonomy
		// ), array(
		// 	'%d',
		// 	'%s'
		// ));

	}

	/**
	 * add post dependency
	 */
	public function add_post_id($post_id) {
		global $wpdb;

		$wpdb->insert($wpdb->prefix.$this->dependency_table, array(
			'target_id' => $this->post_id,
			'object' => 'post',
			'object_id' => $post_id
		), array(
			'%d',
			'%s',
			'%d'
		));

		// $wpdb->insert($wpdb->prefix . 'cluster_post_dep', array(
		// 	'object_id' => $this->post_id,
		// 	'post_id' => $post_id
		// ), array(
		// 	'%d',
		// 	'%d'
		// ));

		// $this->post_dependencies[] = $post_id;



		// add_post_meta($post_id, 'dependencies', $this->post_id);

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
		global $wpdb;

		$wpdb->insert($wpdb->prefix.$this->dependency_table, array(
			'target_id' => $this->post_id,
			'object' => 'term',
			'object_id' => $term_id
		), array(
			'%d',
			'%s',
			'%d'
		));

		// $this->dependencies[$this->post_id]['terms'][] = $term_id;
		// $this->term_dependencies[] = $term_id;

		// add_term_meta($term_id, 'dependencies', $this->post_id);

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

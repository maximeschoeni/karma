<?php

/**
 *	Class Karma_Dependency_Expirer
 */
class Karma_Clusters_Dependency_Expirer {

	var $dependencies = array();

	/**
	 *	Constructor
	 */
	function __construct() {

		add_action('save_post', array($this, 'save_post'), 10, 3);
		add_action('before_delete_post', array($this, 'before_delete_post'), 10);
		add_action('edit_term', array($this, 'edit_term'), 10, 3);
		add_action('pre_delete_term', array($this, 'pre_delete_term'), 10, 2);

		add_action('wp_loaded', array($this, 'update_dependencies'));

	}

	/**
	 * @hook 'wp_loaded'
	 */
	function update_dependencies() {
		global $karma;

		if ($this->dependencies) {

			$dependencies = $karma->options->get_option('expired_clusters', array());
			$dependencies = array_merge($this->dependencies, $dependencies);
			$dependencies = array_map('intval', array_unique($dependencies));

			$karma->options->update_option('expired_clusters', $dependencies);

		}

	}


	/**
	 * @hook 'save_post'
	 */
	function save_post($post_id, $post, $update) {
		global $karma;

		$this->dependencies[] = $post_id;

		$dependencies = get_post_meta($post_id, 'dependencies');

		$this->dependencies = array_merge($this->dependencies, $dependencies);

	}

	/**
	 * @hook 'before_delete_post'
	 */
	function before_delete_post($post_id) {

		$dependencies = get_post_meta($post_id, 'dependencies');

		$this->dependencies = array_merge($this->dependencies, $dependencies);

	}

	/**
	 * @hook 'edit_term'
	 */
	function edit_term($term_id, $tt_id, $taxonomy) {

		$dependencies = get_term_meta($term_id, 'dependencies');

		$this->dependencies = array_merge($this->dependencies, $dependencies);

	}

	/**
	 * @hook 'pre_delete_term'
	 */
	function pre_delete_term($term, $taxonomy) {

		$dependencies = get_term_meta($term->term_id, 'dependencies');

		$this->dependencies = array_merge($this->dependencies, $dependencies);

	}

}

new Karma_Clusters_Dependency_Expirer();

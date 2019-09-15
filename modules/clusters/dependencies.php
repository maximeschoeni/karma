<?php

/**
 *	Class Karma_Cluster_Dependencies
 */
class Karma_Cluster_Dependencies {

	// var $post_dependencies = array();
	// var $term_dependencies = array();

	var $dependency_table;
	var $dependency_ids;

	public function __construct($post_id, $dependency_table) {

		$this->dependency_ids = array();
		$this->dependency_table = $dependency_table;
		$this->post_id = $post_id;
		// $this->clear($post_id);



	}

	/**
	 *
	 * clear dependencies
	 */
	public function update() {
		global $wpdb;

		$table = $wpdb->prefix.$this->dependency_table;

		if ($this->dependency_ids) {

			$sql_ids = implode(',', array_map('intval', $this->dependency_ids));

			$wpdb->query($wpdb->prepare("DELETE FROM $table
				WHERE target_id = %d AND id NOT IN ($sql_ids)",
				$this->post_id
			));

		} else {

			$wpdb->query($wpdb->prepare("DELETE FROM $table
				WHERE target_id = %d",
				$this->post_id
			));

		}

	}


	/**
	 *
	 * clear dependencies
	 */
	// public function clear($post_id) {
	// 	global $wpdb;
	//
	// 	$wpdb->delete($wpdb->prefix.$this->dependency_table, array(
	// 		'target_id' => $post_id,
	// 	), array(
	// 		'%d'
	// 	));
	//
	// }

	/**
	 * add post_type dependency
	 */
	public function add_type($object, $type, $context = '') {
		global $wpdb;

		$table = $wpdb->prefix.$this->dependency_table;

		$dependency_id = $wpdb->get_var($wpdb->prepare(
			"SELECT id FROM $table WHERE target_id = %d AND object = %s AND type = %s",
			$this->post_id,
			$object,
			$type
		));

		if (!$dependency_id) {

			$wpdb->insert($table, array(
				'target_id' => $this->post_id,
				'object' => $object,
				'type' => $type
			), array(
				'%d',
				'%s',
				'%s'
			));

			$dependency_id = $wpdb->insert_id;

		}

		$this->dependency_ids[] = $dependency_id;

	}

	/**
	 * add post dependency
	 */
	public function add_id($object, $id) {
		global $wpdb;

		$table = $wpdb->prefix.$this->dependency_table;

		$dependency_id = $wpdb->get_var($wpdb->prepare(
			"SELECT id FROM $table WHERE target_id = %d AND object = %s AND object_id = %d",
			$this->post_id,
			$object,
			$id
		));

		if (!$dependency_id) {

			$wpdb->insert($wpdb->prefix.$this->dependency_table, array(
				'target_id' => $this->post_id,
				'object' => $object,
				'object_id' => $id
			), array(
				'%d',
				'%s',
				'%d'
			));

			$dependency_id = $wpdb->insert_id;

		}

		$this->dependency_ids[] = $dependency_id;

	}

	/**
	 * add post dependency
	 */
	public function add_ids($object, $ids, $type = null) {

		foreach ($ids as $id) {

			$this->add_id($object, $id);

		}

		if ($type) {

			$this->add_type($object, $type);

		}

	}



	/**
	 * add post_type dependency
	 */
	public function add_post_type($post_type) {
		// global $wpdb;
		//
		// $wpdb->insert($wpdb->prefix.$this->dependency_table, array(
		// 	'target_id' => $this->post_id,
		// 	'object' => 'post',
		// 	'type' => $post_type
		// ), array(
		// 	'%d',
		// 	'%s',
		// 	'%s'
		// ));

		$this->add_type('post', $post_type);

	}

	/**
	 * add taxonomy dependency
	 */
	public function add_taxonomy($taxonomy) {
		// global $wpdb;
		//
		// $wpdb->insert($wpdb->prefix.$this->dependency_table, array(
		// 	'target_id' => $this->post_id,
		// 	'object' => 'term',
		// 	'type' => $taxonomy
		// ), array(
		// 	'%d',
		// 	'%s',
		// 	'%s'
		// ));

		// $wpdb->insert($wpdb->prefix . 'cluster_tax_dep', array(
		// 	'object_id' => $this->post_id,
		// 	'taxonomy' => $taxonomy
		// ), array(
		// 	'%d',
		// 	'%s'
		// ));

		$this->add_type('term', $taxonomy);

	}

	/**
	 * add post dependency
	 */
	public function add_post_id($post_id) {
		// global $wpdb;
		//
		// $wpdb->insert($wpdb->prefix.$this->dependency_table, array(
		// 	'target_id' => $this->post_id,
		// 	'object' => 'post',
		// 	'object_id' => $post_id
		// ), array(
		// 	'%d',
		// 	'%s',
		// 	'%d'
		// ));

		// $wpdb->insert($wpdb->prefix . 'cluster_post_dep', array(
		// 	'object_id' => $this->post_id,
		// 	'post_id' => $post_id
		// ), array(
		// 	'%d',
		// 	'%d'
		// ));

		// $this->post_dependencies[] = $post_id;



		// add_post_meta($post_id, 'dependencies', $this->post_id);

		$this->add_id('post', $post_id);
	}

	/**
	 * add post dependencies
	 */
	public function add_post_ids($post_ids) {

		// foreach ($post_ids as $post_id) {
		//
		// 	$this->add_post_id($post_id);
		//
		// }

		$this->add_ids('post', $post_ids);

	}

	/**
	 * add term dependency
	 */
	public function add_term_id($term_id) {
		// global $wpdb;
		//
		// $wpdb->insert($wpdb->prefix.$this->dependency_table, array(
		// 	'target_id' => $this->post_id,
		// 	'object' => 'term',
		// 	'object_id' => $term_id
		// ), array(
		// 	'%d',
		// 	'%s',
		// 	'%d'
		// ));

		// $this->dependencies[$this->post_id]['terms'][] = $term_id;
		// $this->term_dependencies[] = $term_id;

		// add_term_meta($term_id, 'dependencies', $this->post_id);

		$this->add_id('term', $term_id);

	}

	/**
	 * add term dependencies
	 */
	public function add_term_ids($term_ids) {

		// foreach ($term_ids as $term_id) {
		//
		// 	$this->add_term_id($term_id);
		//
		// }

		$this->add_ids('term', $term_ids);

	}


}

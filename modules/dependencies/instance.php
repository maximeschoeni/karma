<?php


Class Karma_Cache_Dependencies_Instance {

	public $parent;

	public $dependencies = array();

	/**
	 * constructor
	 */
	public function __construct($parent, $target, $target_id) {

		$this->parent = $parent;
		$this->target = $target;
		$this->target_id = $target_id;

	}

	/**
	 * add_id
	 */
	public function add_id($object, $type, $id, $priority = 1) {

		$this->dependencies[$object][$type][$id] = $priority;

	}

	/**
	 * add_ids
	 */
	public function add_ids($object, $type, $ids, $priority = 1) {

		foreach ($ids as $id) {

			$this->add_id($object, $type, $id, $priority);

		}

	}

	/**
	 * add_type
	 */
	public function add_type($object, $type, $priority = 1) {

		$this->dependencies[$object][$type][0] = $priority;

	}

	/**
	 * @hook 'karma_cache_dependency_save'
	 */
	public function save() {
		global $wpdb;

		$dependency_table = $wpdb->prefix.$this->parent->table_name;

		$dependency_ids = array();

		foreach ($this->dependencies as $object => $object_dependency) {

			foreach ($object_dependency as $type => $type_dependency) {

				foreach ($type_dependency as $id => $priority) {

					$dependency_id = $wpdb->get_var($wpdb->prepare(
						"SELECT id FROM $dependency_table WHERE target = %s AND target_id = %d AND object = %s AND object_id = %d AND type = %s AND priority = %d",
						$this->target,
						$this->target_id,
						$object,
						$id,
						$type,
						$priority
					));

					if (!$dependency_id) {

						$wpdb->insert($dependency_table, array(
							'target' => $this->target,
							'target_id' => $this->target_id,
							'object' => $object,
							'object_id' => $id,
							'type' => $type,
							'priority' => $priority
						), array(
							'%s',
							'%d',
							'%s',
							'%d',
							'%s',
							'%d'
						));

						$dependency_id = $wpdb->insert_id;

					}

					$dependency_ids[] = $dependency_id;

				}

			}

		}

		if ($dependency_ids) {

			$sql = implode(',', array_map('intval', $dependency_ids));

			$wpdb->query($wpdb->prepare(
				"DELETE FROM $dependency_table WHERE target = %s AND target_id = %d AND id NOT IN ($sql)",
				$this->target,
				$this->target_id
			));

		} else {

			$wpdb->query($wpdb->prepare(
				"DELETE FROM $dependency_table WHERE target = %s AND target_id = %d",
				$this->target,
				$this->target_id
			));

		}

	}

}

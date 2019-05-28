<?php


/**
 *	Class Admin
 */
class Karma_Collection {

	public $items = array();
	public $keys = array();
	public $groups = array();

	/**
	 *	Constructor
	 */
	public function __construct($items = array()) {

		$this->items = $items;

	}

	public function get_item($key, $value) {

		if (!isset($this->keys[$key])) {

			$this->map_keys($key);

		}

		if (isset($this->keys[$key][$value])) {

			return $this->keys[$key][$value];

		}

	}

	public function filter_by($key, $value) {

		if (!isset($this->groups[$key])) {

			$this->map_groups($key);

		}

		if (isset($this->groups[$key][$value])) {

			return new Karma_Collection($this->groups[$key][$value]);

		}

		return new Karma_Collection();
	}

	public function group_by($key) {

		$groups = array();

		foreach ($this->items as $item) {

			if (isset($item->$key)) {

				$groups[$item->$key][] = $item;

			}

		}

		return $groups;

		// if (!isset($this->groups[$key])) {
		//
		// 	$this->map_groups($key);
		//
		// }
		//
		// $groups = array();
		//
		// if (isset($this->groups[$key])) {
		//
		// 	foreach ($this->groups[$key] as $group_key => $group) {
		//
		// 		$groups[$group_key][] = new Karma_Collection($group);
		//
		// 	}
		//
		// 	return $groups;
		//
		// }

	}

	public function map_keys($key) {

		foreach ($this->items as $item) {

			if (isset($item->$key)) {

				$this->keys[$key][$item->$key] = $item;

			}

		}

	}

	public function map_groups($key) {

		foreach ($this->items as $item) {

			if (isset($item->$key)) {

				$this->groups[$key][$item->$key][] = $item;

			}

		}

	}

}

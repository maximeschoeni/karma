<?php


/**
 *	Class Admin
 */
class Karma_Collection {

	public $items = array();

	/**
	 *	Constructor
	 */
	public function __construct($items = array()) {

		$this->items = $items;

	}

	public function get_item($key, $value) {

		foreach ($this->items as $item) {

			if (isset($item->$key) && $item->$key === $value) {

				return $item;

			}

		}

	}

	public function filter_by($key, $value) {

		$collection = new Karma_Collection();

		foreach ($this->items as $item) {

			if (isset($item->$key) && $item->$key === $value) {

				$collection->items[] = $item;

			}

		}

		return $collection;
	}

	public function group_by($key) {

		$groups = array();

		foreach ($this->items as $item) {

			if (isset($item->$key)) {

				$groups[$item->$key][] = $item;

			}

		}

		return $groups;

	}

}

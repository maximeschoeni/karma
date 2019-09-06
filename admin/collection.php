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

			$item = (object) $item;

			if (isset($item->$key) && $item->$key === $value) {

				return $item;

			}

		}

	}

	public function get_item_index($key, $value) {

		foreach ($this->items as $index => $item) {

			$item = (object) $item;

			if (isset($item->$key) && $item->$key === $value) {

				return $index;

			}

		}

		return -1;

	}

	public function remove_item($key, $value) {

		$index = $this->get_item_index($key, $value);

		if ($index > -1) {

			unset($this->items[$index]);

		}

	}

	public function remove_items($key, $value) {

		$new_items = array();

		foreach ($this->items as $item) {

			if ($item->$key !== $value)) {

				$new_items[] = $item;

			}

		}

		$this->items = $new_items;
	}

	public function filter_by($key, $value) {

		$collection = new Karma_Collection();

		foreach ($this->items as $item) {

			$item = (object) $item;

			if (isset($item->$key) && (is_array($item->$key) && in_array($value, $item->$key) || $item->$key === $value)) {

				$collection->items[] = $item;

			}

		}

		return $collection;
	}

	public function group_by($key) {

		$groups = array();

		foreach ($this->items as $item) {

			$item = (object) $item;

			if (isset($item->$key)) {

				if (is_array($item->$key)) {

					foreach ($item->$key as $value) {

						$groups[$value][] = $item;

					}

				} else {

					$groups[$item->$key][] = $item;

				}

			}

		}

		return $groups;

	}

}

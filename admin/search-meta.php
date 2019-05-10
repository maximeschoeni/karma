<?php


class Karma_Search_Meta {

	public $searchable_meta = array();

	/**
	 * @constructor
	 */
	public function __construct() {

		add_filter('wp_insert_post_data', array($this, 'insert_post'));

	}

	/**
	 * save searchable meta values in excerpt field
	 * Filter for 'wp_insert_post_data'
	 *
	 * @from 1.0
	 */
	public function insert_post($data, $postarr) {

		$searchable_meta = apply_filters('karma_register_search_meta', $searchable_meta, $data, $postarr);

		if ($searchable_meta) {

			$values = array();

			foreach ($searchable_meta as $meta_key) {

				if (isset($postarr[$meta_key])) {

					$values = $postarr[$meta_key];

				}

			}

			$data['post_excerpt'] =  implode(' ', $values);

		}

		return $data;
	}

}

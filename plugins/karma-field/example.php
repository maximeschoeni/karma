<?php


class Karma_Field_Test {

	/**
	 *	Constructor
	 */
	public function __construct() {

		add_action('init', array($this, 'init'));

	}

	/**
	 *	@hook 'init'
	 */
	public function init() {

		add_action('add_meta_boxes', array($this, 'meta_boxes'), 10, 2);

	}

	/**
	 * @hook add_meta_boxes
	 */
	public function meta_boxes($post_type, $post) {

		if ($post_type === 'simple-event') {

			add_meta_box(
				'settings',
				'Event Settings',
				array($this, 'settings_meta_box'),
				array('page'),
				'normal',
				'default'
			);

		}

	}

	/**
	 * @hook add_meta_boxes
	 */
	public function settings_meta_box($post) {
		global $wpdb;

		$trainers_query = $wpdb->get_results(
			"SELECT ID, post_title FROM $wpdb->posts
			WHERE post_status = 'publish' AND post_type = 'post'
			ORDER BY post_title ASC");

		$trainers = array_map(function($post) {
			return array(
				'key' => $post->ID,
				'name' => $post->post_title
			);
		}, $trainers_query);

		do_action('karma_field_group', $post, array(
			'fields' => array(
				array(
					'meta_key' => 'subtitle',
					'label' => 'Subtitle',
					'field' => 'text'
				),
				array(
					'meta_key' => 'date',
					'label' => 'Date',
					'field' => 'date'
				)
			),
			'display' => 'group'
		));

		do_action('karma_field_group', $post, array(
			'fields' => array(
				array(
					'meta_key' => 'subtitle2',
					'label' => 'Subtitle',
					'field' => 'text'
				),
				array(
					'meta_key' => 'date2',
					'label' => 'Date',
					'field' => 'date'
				),
				array(
					'meta_key' => 'imagex',
					'label' => 'image X',
					'field' => 'image'
				),
				array(
					'meta_key' => 'gallery_images234',
					'label' => 'Gallery',
					'field' => 'gallery',
					'mimeTypes' => array()
					// 'args' => array(
					// 	// 'types' => array('application/pdf')
					// )
				),
				array(
					'meta_key' => 'trainer',
					'label' => 'Trainers',
					'field' => 'select',
					'values' => $trainers,
					'novalue' => '-'
				),
				array(
					'meta_key' => 'test-multi',
					'label' => 'multi',
					'field' => 'multimedia',
					'columns' => array(
						array(
							'key' => 'test-text',
							'name' => 'Text',
							'field' => 'text'
						),
						array(
							'key' => 'test-date',
							'name' => 'Date',
							'field' => 'date'
						),
						array(
							'key' => 'test-m2',
							'name' => 'M2',
							'field' => 'multimedia',
							'noheader' => true,
							'columns' => array(
								array(
									'key' => 'test-text2',
									'field' => 'text'
								)
							)
						)
					)
				)
			),
			'display' => 'table'
		));

	}

}

new Karma_Field_Test;

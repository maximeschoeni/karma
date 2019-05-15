<?php

require_once get_template_directory() . '/admin/post_type-cache.php';

/**
 *	Class Karma_Subevent
 */
class Karma_Subevent extends Karma_Post_type_Cache {

	public $version = '000';

	public $subevent_post_type = 'event';
	public $subevent_post_type_name = 'Date';
	public $post_type = 'project';
	public $fields = array();

	public $nonce = 'subevents_nonce';
	public $action = 'subevents-action';

	/**
	 *	Constructor
	 */
	public function __construct() {

		if (is_admin()) {

			add_action('add_meta_boxes', array($this, 'meta_boxes'), 10, 2);
			add_action('save_post', array($this, 'save_meta_boxes'), 11, 3);
			add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));

		}

		add_action('init', array($this, 'register_taxonomy'));
	}

	/**
	 * Hook for 'admin_enqueue_scripts'
	 */
	public function enqueue_styles() {

		wp_enqueue_style('subevent-styles', get_template_directory_uri().'/admin/css/subevent.css', array('date-popup-styles'), $this->version);
		wp_enqueue_script('subevent', get_template_directory_uri() . '/admin/js/subevent.js', array('build', 'sortable', 'date-popup'), $this->version, true);

	}

	/**
	 * @hook 'init'
	 */
	public function register_taxonomy() {

		register_post_type($this->subevent_post_type, array(
			'labels'             => array(
				'name' => $this->subevent_post_type_name,
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest' => true,
			'rewrite'            => true,
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array('title', 'editor')
		));

		foreach ($this->fields as $field) {

			if ($field['type'] === 'taxonomy') {

				register_taxonomy($field['taxonomy'], array($this->subevent_post_type), array(
					'hierarchical'          => true,
					'labels'                => array(
						'name'                       => 'Types',
						'singular_name'              => 'Type'
					),
					'show_ui'               => true,
					'show_admin_column'     => true,
					'query_var'             => true,
					'rewrite'               => true
				));

			}

		}

	}

	/**
	 * @hook add_meta_boxes
	 */
	public function meta_boxes($post_type, $post) {

		add_meta_box(
			'events',
			'Evenements associés',
			array($this, 'project_events_meta_box'),
			array($this->post_type),
			'normal',
			'default'
		);

	}

	/**
	 * @callback 'add_meta_box'
	 */
	public function project_events_meta_box($post) {
		global $wpdb;

		wp_nonce_field($this->action, $this->nonce, false, true);

		// $subevent_ids = $wpdb->get_col($wpdb->prepare(
		// 	"SELECT p.ID FROM $wpdb->posts AS p
		// 	JOIN $wpdb->postmeta AS pm ON (pm.post_id = p.ID AND pm.meta_key = 'end_date')
		// 	WHERE p.post_type = %s AND p.post_status = %s AND p.post_parent = %d
		// 	GROUP BY p.ID
		// 	ORDER BY pm.meta_value ASC",
		// 	$this->subevent_post_type, 'publish', $post->ID));

		$types = array();

		foreach ($this->fields as $field) {

			if ($field['type'] === 'taxonomy') {

				$types[$field['name']] = $wpdb->get_results($wpdb->prepare(
					"SELECT tt.term_id AS 'key', t.name FROM $wpdb->term_taxonomy AS tt
					JOIN $wpdb->terms AS t ON (tt.term_id = t.term_id)
					WHERE tt.taxonomy = %s
					GROUP BY tt.term_id
					ORDER BY t.slug ASC",
					$field['taxonomy']));

			}

		}

		$subevents = $this->export_subevents($post->ID);

		// $events = array();
		//
		// foreach ($event_ids as $event_id) {
		// 	$event = array(
		// 		'id' => $event_id,
		// 		'start_date' => get_post_meta($event_id, 'start_date', true),
		// 		// 'hour' => get_post_meta($event_id, 'hour', true),
		// 		// 'name' => get_post_meta($event_id, 'name', true),
		// 		// 'place' => get_post_meta($event_id, 'place', true),
		// 		// 'city' => get_post_meta($event_id, 'city', true),
		// 		// 'country' => get_post_meta($event_id, 'country', true),
		// 	);
		//
		// 	$post_event = get_post($event_id);
		//
		// 	foreach ($this->fields as $field) {
		//
		// 		if ($field['type'] === 'post_field') {
		//
		// 			$event[$field['name']] = $post_event->{$field['post_field']};
		//
		// 		} else if ($field['type'] === 'meta') {
		//
		// 			$event[$field['name']] = get_post_meta($event_id, $field['name'], true);
		//
		// 		} else if ($field['type'] === 'taxonomy') {
		//
		// 			$terms = get_the_terms($post_event, $field['taxonomy']);
		//
		// 			if ($terms && !is_wp_error($terms)) {
		//
		// 				foreach ($terms as $term) {
		//
		// 					$event[$field['name']] = $term->term_id;
		// 					break;
		//
		// 				}
		//
		// 			}
		//
		// 		}
		//
		// 	}
		//
		// 	$events[] = $event;
		//
		//
		// 	// $events[] = array(
		// 	// 	'id' => $event_id,
		// 	// 	'start_date' => get_post_meta($event_id, 'start_date', true),
		// 	// 	'hour' => get_post_meta($event_id, 'hour', true),
		// 	// 	'name' => get_post_meta($event_id, 'name', true),
		// 	// 	'place' => get_post_meta($event_id, 'place', true),
		// 	// 	'city' => get_post_meta($event_id, 'city', true),
		// 	// 	'country' => get_post_meta($event_id, 'country', true),
		// 	// );
		// }

		include get_template_directory() . '/admin/include/utils/metabox-subevent.php';

	}

	/**
	 * Save meta boxes
	 *
	 * @hook 'save_post'
	 */
	public function save_meta_boxes($post_id, $post, $update) {
		global $wpdb;

		if (current_user_can('edit_post', $post_id) && (!defined( 'DOING_AUTOSAVE' ) || !DOING_AUTOSAVE )) {

			if ($post->post_type === $this->post_type && isset($_POST[$this->nonce]) && wp_verify_nonce($_POST[$this->nonce], $this->action)) {

				if (isset($_POST['subevent']['event_id'])) {

					// delete events
					$event_ids = array_filter(array_map('intval', $_POST['subevent']['event_id']));
					$sql_not_in = $event_ids ? "AND ID NOT IN (".implode(',', $event_ids).")" : "";
					$event_to_delete_ids = $wpdb->get_col($wpdb->prepare(
						"SELECT ID FROM $wpdb->posts
						WHERE post_parent = %d AND post_status != %s $sql_not_in",
						$post_id, 'trash'
					));

					foreach ($event_to_delete_ids as $event_to_delete_id) {

						wp_trash_post($event_to_delete_id);

					}

					// echo '<pre>';
					// var_dump($_POST);
					// die();
					// add/update events
					foreach ($_POST['subevent']['event_id'] as $i => $event_id) {



						if (!isset($_POST['subevent']['start_date'][$i])) {

							die('start_date not set!');

						}

						$start_date = Karma_Date::parse($_POST['subevent']['start_date'][$i], 'dd.mm.yyyy', 'yyyy-mm-dd hh:ii:ss');

						$post_fields = array(
							'post_type' => $this->subevent_post_type,
							'post_status' => 'publish',
							'post_parent' => $post_id,
							// 'post_title' => $post->post_title . ' ' . $_POST['start_date'][$i],
							'meta_input' => array(
								'start_date' => $start_date,
								'end_date' => $start_date,
								// 'hour' => $_POST['hour'][$i],
								// 'name' => $_POST['name'][$i],
								// 'place' => $_POST['place'][$i],
								// 'city' => $_POST['city'][$i],
								// 'country' => $_POST['country'][$i]
							)
						);



						foreach ($this->fields as $field) {

							if (isset($_POST['subevent'][$field['name']][$i])) {

								$value = $_POST['subevent'][$field['name']][$i];

								if ($field['type'] === 'post_field') {

									$post_fields[$field['post_field']] = $value;

								} else if ($field['type'] === 'meta') {

									$post_fields['meta_input'][$field['name']] = $value;

								} else if ($field['type'] === 'taxonomy') {

									$post_fields['tax_input'][$field['taxonomy']] = array(intval($value));

								}

							}

						}

						if ($event_id) { // -> update

							$post_fields['ID'] = $event_id;

						}

						wp_insert_post($post_fields);

					}

				}

			}

		}

	}

	/**
	 * export children
	 */
	public function export_subevents($post_id) {
		global $wpdb;

		$children_ids = $wpdb->get_col($wpdb->prepare(
			"SELECT p.ID FROM $wpdb->posts AS p
			JOIN $wpdb->postmeta AS pm ON (pm.post_id = p.ID AND pm.meta_key = 'end_date')
			WHERE p.post_type = %s AND p.post_status = %s AND p.post_parent = %d
			GROUP BY p.ID
			ORDER BY pm.meta_value ASC",
			$this->subevent_post_type, 'publish', $post_id));

		$subevents = array();

		foreach ($children_ids as $child_id) {

			$child = get_post($child_id);

			$subevent = array(
				'id' => $child_id,
				'start_date' => get_post_meta($child_id, 'start_date', true),
				'end_date' => get_post_meta($child_id, 'end_date', true)
			);

			foreach ($this->fields as $field) {

				if ($field['type'] === 'post_field') {

					$subevent[$field['name']] = $child->{$field['post_field']};

				} else if ($field['type'] === 'meta') {

					$subevent[$field['name']] = get_post_meta($child_id, $field['name'], true);

				} else if ($field['type'] === 'taxonomy') {

					$terms = get_the_terms($child, $field['taxonomy']);

					if ($terms && !is_wp_error($terms)) {

						foreach ($terms as $term) {

							$subevent[$field['name']][] = $term->term_id;

						}

					}

				}

			}

			$subevents[] = $subevent;

		}

		return $subevents;
	}

	/**
	 * get event
	 */
	public function export($post_id) {
		global $wpdb;

		$post = get_post($post_id);

		$event = array(
			'id' => $post->ID,
			'name' => $post->post_name,
			'title' => apply_filters('sublanguage_translate_post_field', $post->post_title, $post, 'post_title'),
			'content' => apply_filters('the_content', apply_filters('sublanguage_translate_post_field', $post->post_content, $post, 'post_content')),
			'subtitle' => get_post_meta($post->ID, 'subtitle', true),
			'images' => $this->get_images_data(get_post_meta($post->ID, 'images')),
			'subevents' => $this->export_subevents($post_id)
		);

		// $children_ids = $wpdb->get_col($wpdb->prepare(
		// 	"SELECT ID FROM $wpdb->posts
		// 	WHERE post_parent = %d AND post_type = %s AND post_status = 'publish'",
		// 	$post_id, $this->subevent_post_type
		// ));
		//
		// foreach ($children_ids as $children_id) {
		//
		// 	$subevent = array();
		// 	$child_post = get_post($children_id);
		//
		// 	foreach ($this->fields as $field) {
		//
		// 		if ($field['type'] === 'post_field') {
		//
		// 			$subevent[$field['name']] = $child_post->{$field['post_field']};
		//
		// 		} else if ($field['type'] === 'meta') {
		//
		// 			$subevent[$field['name']] = get_post_meta($children_id, $field['name'], true);
		//
		// 		} else if ($field['type'] === 'taxonomy') {
		//
		// 			$terms = get_the_terms($child_post, $field['taxonomy']);
		//
		// 			if ($terms && !is_wp_error($terms)) {
		//
		// 				foreach ($terms as $term) {
		//
		// 					$subevent[$field['name']][] = $term->term_id;
		//
		// 				}
		//
		// 			}
		//
		// 		}
		//
		// 	}
		//
		// 	$event['subevents'][] = $subevent;
		// }

		return $event;
	}

	// public function get_images_data($attachement_ids) {
	//
	// 	$images = array();
	//
	// 	foreach ($attachement_ids as $attachement_id) {
	//
	// 		$metadata = wp_get_attachment_metadata($attachement_id);
	//
	// 		$images[] = apply_filters('background-image-manager-sources', array(array(
	// 			'url' => wp_get_attachment_url($image_id),
	// 			'width' => $metadata['width'],
	// 			'height' => $metadata['height']
	// 		)), $image_id);
	//
	// 	}
	//
	// 	return $images;
	// }

	//
	// /**
	//  * @ajax 'get_event'
	//  */
	// public function ajax_get_event() {
	//
	// 	$output = array();
	//
	// 	if (isset($_GET['id'])) {
	//
	// 		$output = $this->update_event(intval($_GET['id']));
	//
	// 	}
	//
	//  	echo json_encode($output);
	// 	exit;
	// }
	//
	// /**
	//  * @hook 'karma_cache_write'
	//  */
	// public function cache_write($data, $key, $group, $object_cache) {
	//
	// 	if ($group === $this->post_type) {
	//
	// 		$path = $object_cache->object_dir . '/' . $group . '/' . $key . apply_filters('append_language_to_path', '');
	//
	// 		$object_cache->write_file($path, 'data.json', json_encode($data, JSON_PRETTY_PRINT));
	//
	// 	}
	//
	// }
	//
	// /**
	//  * @hook 'save_post_{$post_type}'
	//  */
	// public function save_post($post_id, $post) {
	//
	// 	$this->update_project($post_id);
	//
	// }
	//
	// /**
	//  * @hook 'after_delete_post'
	//  */
	// public function delete_post($post_id) {
	//
	// 	// if post is event
	// 	 wp_cache_delete($post_id, 'event');
	//
	// 	// if post is project
	// 	$this->update_project($post_id);
	//
	// }
	//
	//
	//
	// /**
	//  * update event
	//  */
	// public function update_event($event_id) {
	//
	// 	$data = $this->export_event($event_id);
	//
	// 	wp_cache_set($event_id, $data, $this->post_type);
	//
	// 	return $data;
	// }
	//
	//



}

<?php


/**
 *	Class Karma_Metabox_Subevent
 */
class Karma_Metabox_Subevent {

	public $version = '000';

	public $event_post_type = 'event';
	public $project_post_type = 'project';
	public $nonce = 'subevents_nonce';
	public $action = 'subevents-action';

	/**
	 *	Constructor
	 */
	public function __construct() {

		if (is_admin()) {

			add_action('add_meta_boxes', array($this, 'meta_boxes'), 10, 2);
			add_action('save_post', array($this, 'save_meta_boxes'), 10, 3);
			add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));

		}

	}

	/**
	 * Hook for 'admin_enqueue_scripts'
	 */
	function enqueue_styles() {

		wp_enqueue_style('children-table-styles', get_template_directory_uri().'/admin/css/children-table.css', array('date-popup-styles'), $this->version);
		wp_enqueue_script('children-table', get_template_directory_uri() . '/admin/js/children-table.js', array('build', 'calendar', 'sortable'), $this->version, true);

	}


	/**
	 * @hook add_meta_boxes
	 */
	public function meta_boxes($post_type, $post) {

		add_meta_box(
			'events',
			'Evenements associés',
			array($this, 'project_events_meta_box'),
			array($this->project_post_type),
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

		$event_ids = $wpdb->get_col($wpdb->prepare(
			"SELECT p.ID FROM $wpdb->posts AS p
			JOIN $wpdb->postmeta AS pm ON (pm.post_id = p.ID AND pm.meta_key = 'end_date')
			WHERE p.post_type = %s AND p.post_status = %s AND p.post_parent = %d
			GROUP BY p.ID
			ORDER BY pm.meta_value ASC",
			$this->event_post_type, 'publish', $post->ID));

		$events = array();

		foreach ($event_ids as $event_id) {
			$events[] = array(
				'id' => $event_id,
				'start_date' => get_post_meta($event_id, 'start_date', true),
				'hour' => get_post_meta($event_id, 'hour', true),
				'name' => get_post_meta($event_id, 'name', true),
				'place' => get_post_meta($event_id, 'place', true),
				'city' => get_post_meta($event_id, 'city', true),
				'country' => get_post_meta($event_id, 'country', true),
			);
		}

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

			if ($post->post_type === $this->project_post_type && isset($_POST[$this->nonce]) && wp_verify_nonce($_POST[$this->nonce], $this->action)) {

				if (isset($_POST['event_id'])) {

					// delete events
					$event_ids = array_filter(array_map('intval', $_POST['event_id']));
					$sql_not_in = $event_ids ? "AND ID NOT IN (".implode(',', $event_ids).")" : "";
					$event_to_delete_ids = $wpdb->get_col($wpdb->prepare(
						"SELECT ID FROM $wpdb->posts
						WHERE post_parent = %d AND post_status != %s $sql_not_in",
						$post_id, 'trash'
					));

					foreach ($event_to_delete_ids as $event_to_delete_id) {

						wp_trash_post($event_to_delete_id);

					}

					// add/update events
					foreach ($_POST['event_id'] as $i => $event_id) {

						if (!isset($_POST['start_date'][$i], $_POST['hour'][$i], $_POST['place'][$i], $_POST['city'][$i], $_POST['country'][$i])) {

							die('problem with project events data!');

						}

						$start_date = Karma_Date::parse($_POST['start_date'][$i], 'dd.mm.yyyy', 'yyyy-mm-dd hh:ii:ss');

						$fields = array(
							'post_type' => $this->event_post_type,
							'post_status' => 'publish',
							'post_parent' => $post_id,
							'post_title' => $post->post_title . ' ' . $_POST['start_date'][$i],
							'meta_input' => array(
								'start_date' => $start_date,
								'end_date' => $start_date,
								'hour' => $_POST['hour'][$i],
								'name' => $_POST['name'][$i],
								'place' => $_POST['place'][$i],
								'city' => $_POST['city'][$i],
								'country' => $_POST['country'][$i]
							)
						);

						if ($event_id) { // -> update

							$fields['ID'] = $event_id;

						}

						wp_insert_post($fields);

					}

				}

			}

		}

	}

}

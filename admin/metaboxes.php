<?php


/**
 *	Class Karma_Metaboxes
 */
class Karma_Metaboxes {

	/**
	 *	Constructor
	 */
	public function __construct() {

		add_action('init', array($this, 'init'), 10); // after post type registration

	}

	/**
	 *	Init
	 */
	public function init() {

		add_action('add_meta_boxes', array($this, 'meta_boxes'), 10, 2);
		add_action('save_post', array($this, 'save_meta_boxes'), 10, 3);

		// 		add_action('edit_form_after_title', array($this, 'subtitle_field'));


	}

	/**
	 * @hook add_meta_boxes
	 */
	public function meta_boxes($post_type, $post) {

		// add_meta_box(
		// 	'page-header',
		// 	'Vignette',
		// 	array($this, 'page_header_meta_box'),
		// 	array('page'),
		// 	'normal',
		// 	'default'
		// );

		add_meta_box(
			'project-header',
			'Project Header',
			array($this, 'project_header_meta_box'),
			array('project'),
			'normal',
			'default'
		);

		add_meta_box(
			'sticky-metabox',
			'Details',
			array($this, 'sticky_meta_box'),
			array('project'),
			'normal',
			'default'
		);

		add_meta_box(
			'events',
			'Evenements associés',
			array($this, 'project_events_meta_box'),
			array('project'),
			'normal',
			'default'
		);

		add_meta_box(
			'event-details',
			'Event Details',
			array($this, 'event_details_meta_box'),
			array('event'),
			'normal',
			'default'
		);

		add_meta_box(
			'event-parent',
			'Parent Project',
			array($this, 'event_parent_meta_box'),
			array('event'),
			'side',
			'default'
		);

		add_meta_box(
			'bio-name',
			'Nom/prénom',
			array($this, 'bio_meta_box'),
			array('bio'),
			'side',
			'default'
		);

	}


	/**
	 * @callback 'add_meta_box'
	 */
	public function page_header_meta_box($post) {

		wp_nonce_field('page_header-action', 'page_header_nonce', false, true);

		include get_template_directory() . '/admin/include/page-header.php';

	}

	/**
	 * @callback 'add_meta_box'
	 */
	public function sticky_meta_box($post) {

		wp_nonce_field('custom_page_attributes-action', 'custom_page_attributes_nonce', false, true);

		include get_template_directory() . '/admin/include/sticky-checkbox.php';
		include get_template_directory() . '/admin/include/agenda-checkbox.php';

	}

	/**
	 * @callback 'add_meta_box'
	 */
	public function project_header_meta_box($post) {

		wp_nonce_field('project_header-action', 'project_header_nonce', false, true);

		include get_template_directory() . '/admin/include/project-header.php';

	}

	/**
	 * @callback 'add_meta_box'
	 */
	public function project_events_meta_box($post) {
		global $wpdb;

		wp_nonce_field('project_events-action', 'project_events_nonce', false, true);


		// $events = $wpdb->get_results($wpdb->prepare(
		// 	"SELECT p.ID AS id, p.post_title, p.post_content, pm1.meta_value AS start_date, pm2.meta_value AS end_date, pm3.meta_value AS hour, pm4.meta_value AS place, pm5.meta_value AS city, pm6.meta_value AS country FROM $wpdb->posts AS p
		// 	JOIN $wpdb->postmeta AS pm1 ON (pm1.post_id = p.ID AND pm1.meta_key = 'start_date')
		// 	JOIN $wpdb->postmeta AS pm2 ON (pm2.post_id = p.ID AND pm2.meta_key = 'end_date')
		// 	JOIN $wpdb->postmeta AS pm3 ON (pm3.post_id = p.ID AND pm3.meta_key = 'hour')
		// 	JOIN $wpdb->postmeta AS pm4 ON (pm4.post_id = p.ID AND pm4.meta_key = 'place')
		// 	JOIN $wpdb->postmeta AS pm5 ON (pm5.post_id = p.ID AND pm5.meta_key = 'city')
		// 	JOIN $wpdb->postmeta AS pm6 ON (pm6.post_id = p.ID AND pm6.meta_key = 'country')
		// 	WHERE p.post_type = %s AND p.post_status = %s AND p.post_parent = %d
		// 	GROUP BY p.ID
		// 	ORDER BY pm2.meta_value DESC",
		// 	'event', 'publish', $post->ID));

		$event_ids = $wpdb->get_col($wpdb->prepare(
			"SELECT p.ID FROM $wpdb->posts AS p
			JOIN $wpdb->postmeta AS pm ON (pm.post_id = p.ID AND pm.meta_key = 'end_date')
			WHERE p.post_type = %s AND p.post_status = %s AND p.post_parent = %d
			GROUP BY p.ID
			ORDER BY pm.meta_value ASC",
			'event', 'publish', $post->ID));

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

		include get_template_directory() . '/admin/include/project-events.php';

	}

	/**
	 * @callback 'add_meta_box'
	 */
	public function event_details_meta_box($post) {

		wp_nonce_field('event_details-action', 'event_details_nonce', false, true);

		include get_template_directory() . '/admin/include/event-details.php';

	}

	/**
	 * @callback 'add_meta_box'
	 */
	public function event_parent_meta_box($post) {
		global $wpdb;

		wp_nonce_field('event_parent-action', 'event_parent_nonce', false, true);

		$project_ids = $wpdb->get_col($wpdb->prepare(
			"SELECT p.ID FROM $wpdb->posts AS p
			WHERE p.post_type = %s AND p.post_status != %s",
			'project', 'trash'));

		include get_template_directory() . '/admin/include/event-parent.php';

	}

	/**
	 * @callback 'add_meta_box'
	 */
	public function bio_meta_box($post) {
		global $wpdb;

		wp_nonce_field('bio-action', 'bio_nonce', false, true);

		include get_template_directory() . '/admin/include/bio.php';

	}



	/**
	 * Save meta boxes
	 *
	 * @hook 'save_post'
	 */
	public function save_meta_boxes($post_id, $post, $update) {
		global $wpdb;

		if (current_user_can('edit_post', $post_id) && (!defined( 'DOING_AUTOSAVE' ) || !DOING_AUTOSAVE )) {

			if (($post->post_type === 'project' || $post->post_type === 'page') && isset($_POST['custom_page_attributes_nonce']) && wp_verify_nonce($_POST['custom_page_attributes_nonce'], 'custom_page_attributes-action')) {

				$in_menu = isset($_POST['in_menu']) && $_POST['in_menu'] ? '1' : '';
				$in_footer = isset($_POST['in_footer']) && $_POST['in_footer'] ? '1' : '';
				$sticky = isset($_POST['custom-sticky']) && $_POST['custom-sticky'] ? '1' : '';
				$color = isset($_POST['color']) && $_POST['color'] ? $_POST['color'] : '';
				$in_agenda = isset($_POST['in_agenda']) && $_POST['in_agenda'] ? '1' : '';

				update_post_meta($post_id, 'in_menu', $in_menu);
				update_post_meta($post_id, 'in_footer', $in_footer);
				update_post_meta($post_id, 'sticky', $sticky);
				update_post_meta($post_id, 'color', $color);
				update_post_meta($post_id, 'in_agenda', $in_agenda);

			}

			if (isset($_POST['footer_page_nonce']) && wp_verify_nonce($_POST['footer_page_nonce'], 'footer_page-action')) {

				$footer1 = isset($_POST['footer1']) && $_POST['footer1'] ? '1' : '';
				$footer2 = isset($_POST['footer2']) && $_POST['footer2'] ? '1' : '';
				$footer3= isset($_POST['footer3']) && $_POST['footer3'] ? '1' : '';

				update_post_meta($post_id, 'footer1', $footer1);
				update_post_meta($post_id, 'footer2', $footer2);
				update_post_meta($post_id, 'footer3', $footer3);

			}

			if ($post->post_type === 'page' && isset($_POST['page_header_nonce']) && wp_verify_nonce($_POST['page_header_nonce'], 'page_header-action')) {

				if (isset($_POST['thumb_title'])) {
					update_post_meta($post_id, 'description1', $_POST['thumb_title']);
				}
				if (isset($_POST['description1'])) {
					update_post_meta($post_id, 'description1', $_POST['description1']);
				}
				if (isset($_POST['description2'])) {
					update_post_meta($post_id, 'description2', $_POST['description2']);
				}

			}

			if ($post->post_type === 'project' && isset($_POST['project_header_nonce']) && wp_verify_nonce($_POST['project_header_nonce'], 'project_header-action')) {

				if (isset($_POST['auteur'])) {
					update_post_meta($post_id, 'auteur', $_POST['auteur']);
				}
				if (isset($_POST['description'])) {
					update_post_meta($post_id, 'description', $_POST['description']);
				}
				if (isset($_POST['program_content'])) {
					update_post_meta($post_id, 'program_content', $_POST['program_content']);
				}


			}

			if ($post->post_type === 'project' && isset($_POST['project_events_nonce']) && wp_verify_nonce($_POST['project_events_nonce'], 'project_events-action')) {

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
							'post_type' => 'event',
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

			if ($post->post_type === 'event' && isset($_POST['event_details_nonce']) && wp_verify_nonce($_POST['event_details_nonce'], 'event_details-action')) {

				if (isset($_POST['start_date'])) {

					$start_date = Karma_Date::parse($_POST['start_date'], 'dd.mm.yyyy', 'yyyy-mm-dd hh:ii:ss');

					if ($start_date) {

						update_post_meta($post_id, 'start_date', $start_date);

						if (isset($_POST['end_date']) && $_POST['end_date']) {

							$end_date = Karma_Date::parse($_POST['end_date'], 'dd.mm.yyyy', 'yyyy-mm-dd hh:ii:ss');

						} else {

							$end_date = $start_date;
						}

						update_post_meta($post_id, 'end_date', $end_date);

					}

				}

				if (isset($_POST['hour'])) {

					update_post_meta($post_id, 'hour', $_POST['hour']);

				}

				if (isset($_POST['name'])) {

					update_post_meta($post_id, 'name', $_POST['name']);

				}

				if (isset($_POST['place'])) {

					update_post_meta($post_id, 'place', $_POST['place']);

				}

				if (isset($_POST['city'])) {

					update_post_meta($post_id, 'city', $_POST['city']);

				}

				if (isset($_POST['country'])) {

					update_post_meta($post_id, 'country', $_POST['country']);

				}

				if (isset($_POST['description'])) {

					update_post_meta($post_id, 'description', $_POST['description']);

				}

				if (isset($_POST['auteur'])) {

					update_post_meta($post_id, 'auteur', $_POST['auteur']);

				}

			}

			if ($post->post_type === 'bio' && isset($_POST['bio_nonce']) && wp_verify_nonce($_POST['bio_nonce'], 'bio-action')) {

				if (isset($_POST['lastname'])) {

					update_post_meta($post_id, 'lastname', $_POST['lastname']);

				}

				if (isset($_POST['firstname'])) {

					update_post_meta($post_id, 'firstname', $_POST['firstname']);

				}

			}

		}

	}


	/**
	 * @hook edit_form_after_title
	 */
	public function subtitle_field($post) {

// 		if ($post->post_type === 'page' || $post->post_type === 'team') {
//
// 			wp_nonce_field('subtitle-action', 'subtitle_nonce', false, true);
//
// 			include get_template_directory() . '/admin/include/post_edit-subtitle.php';
//
// 		}

	}

}

new Karma_Metaboxes();

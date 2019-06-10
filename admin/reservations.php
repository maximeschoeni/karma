<?php

class Karma_Reservations {

	var $reservations_table = 'reservations';
	var $members_table = 'reservation_members';
	var $tickets_table = 'reservation_tickets';

	var $page_name = 'reservations';
	var $tarif_post_type = 'tarif';

	var $num_rows = 50;

	/**
	 *	constructor
	 */
	public function __construct() {

		add_action('init', array($this, 'register_post_type'));

		if (is_admin()) {

			add_action('admin_menu', array($this, 'admin_menu'));
			add_action('init', array($this, 'create_reservations_table'));
			add_action('init', array($this, 'create_members_table'));
			add_action('init', array($this, 'create_tickets_table'));

			add_action('wp_ajax_get_all_reservations', array($this, 'ajax_get_all_reservations'));
	    add_action('wp_ajax_nopriv_get_all_reservations', array($this, 'ajax_get_all_reservations'));

			add_action('wp_ajax_get_user_reservations', array($this, 'ajax_get_user_reservations'));
	    add_action('wp_ajax_nopriv_get_user_reservations', array($this, 'ajax_get_user_reservations'));

			add_action('wp_ajax_get_user_reservation', array($this, 'ajax_get_user_reservation'));
	    add_action('wp_ajax_nopriv_get_user_reservation', array($this, 'ajax_get_user_reservation'));

			add_action('wp_ajax_add_user_reservation', array($this, 'ajax_add_user_reservation'));
	    add_action('wp_ajax_nopriv_add_user_reservation', array($this, 'ajax_add_user_reservation'));

			// add_action('init', array($this, 'request_orders'), 20);

			// add_action('admin_post_nopriv_get_invoice', array($this, 'admin_post_get_invoice'));
			// add_action('admin_post_get_invoice', array($this, 'admin_post_get_invoice'));

		} else {

			// add_action('wp_enqueue_scripts', array($this, 'scripts_styles'));

		}

	}

	/**
	 * @ajax 'get_all_reservations'
	 */
	public function ajax_get_all_reservations() {
		global $karma, $wpdb;

		$reservations_table = $wpdb->prefix.$this->reservations_table;
		$tickets_table = $wpdb->prefix.$this->tickets_table;

		$reservations = $wpdb->get_results($wpdb->prepare(
			"SELECT r.id, r.date_id, COUNT(rt.id) AS num FROM $reservations_table AS r
			JOIN $tickets_table AS rt ON (rt.reservation_id = r.id)
			JOIN $wpdb->posts AS p ON (p.ID = r.date_id)
			JOIN $wpdb->postmeta AS pm ON (pm.post_id = p.ID AND pm.meta_key = 'end_date')
			WHERE pm.meta_value >= %s
			GROUP BY r.id",
			date('Y-m-d')
		));

		echo json_encode($reservations);
		exit;

	}

	/**
	 * @ajax 'get_user_reservations'
	 */
	public function ajax_get_user_reservations() {
		global $karma, $wpdb;

		$output = array();

		if (isset($_GET['ids'])) {

			$reservation_ids = array_filter(array_map('intval', explode(',', $_GET['ids'])));//json_decode(stripslashes($_GET['ids']));

			if ($reservation_ids) {

				$sql_ids = implode(',', $reservation_ids);

				$reservations_table = $wpdb->prefix.$this->reservations_table;
				$tickets_table = $wpdb->prefix.$this->tickets_table;
				$members_table = $wpdb->prefix.$this->members_table;

				$output['reservations'] = $wpdb->get_results(
					"SELECT r.id, r.date_id FROM $reservations_table AS r
					WHERE r.id IN ($sql_ids)"
				);

				// $output['tickets'] = $wpdb->get_results(
				// 	"SELECT rt.tarif_id, r.date_id FROM $tickets_table AS rt
				// 	JOIN $reservations_table AS r ON (rt.reservation_id = r.id)
				// 	WHERE rt.reservation_id IN ($sql_ids)"
				// );

			} else {

				$output['error'] = 'ids is empty';
				$output['error_log'] = $reservation_ids;

			}

		} else {

			$output['error'] = 'ids not set';

		}

		echo json_encode($reservations);
		exit;

	}

	/**
	 * @ajax 'get_user_reservation'
	 */
	public function ajax_get_user_reservation() {
		global $karma, $wpdb;

		$output = array();

		if (isset($_GET['id'])) {

			$reservation_id = intval($_GET['id']);

			$reservations_table = $wpdb->prefix.$this->reservations_table;
			$tickets_table = $wpdb->prefix.$this->tickets_table;
			$members_table = $wpdb->prefix.$this->members_table;

			$output['date_id'] = $wpdb->get_val($wpdb->prepare(
				"SELECT r.date_id FROM $reservations_table AS r
				WHERE r.id = %d",
				$reservation_id
			));

			$output['member'] = $wpdb->get_row($wpdb->prepare(
				"SELECT rm.firstname, rm.lastname, rm.email, rm.phone FROM $members_table AS rm
				JOIN $reservations_table AS r ON (rm.id = r.member_id)
				WHERE r.id = %d",
				$reservation_id
			));

			$output['tickets'] = $wpdb->get_results($wpdb->prepare(
				"SELECT rt.tarif_id, COUNT(rt.tarif_id) AS num FROM $tickets_table AS rt
				WHERE rt.reservation_id = %d
				GROUP BY rt.tarif_id",
				$reservation_id
			));

		}

		echo json_encode($reservations);
		exit;

	}

	/**
	 * @ajax 'get_user_reservation'
	 */
	public function ajax_add_user_reservation() {
		global $karma, $wpdb;

		$output = array();

		if (isset($_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['date_id'], $_POST['tickets'])) {

			$tickets = json_decode(stripslashes($_POST['tickets']));

			$reservations_table = $wpdb->prefix.$this->reservations_table;
			$tickets_table = $wpdb->prefix.$this->tickets_table;
			$members_table = $wpdb->prefix.$this->members_table;

			$member_id = $wpdb->get_var($wpdb->prepare(
				"SELECT id FROM $members_table
				WHERE email = %s AND lastname = %s AND firstname = %s",
				$_POST['email'],
				$_POST['lastname'],
				$_POST['firstname']
			));

			if (!$member_id) {

				$member_data = array(
					'email' => '%s',
					'lastname' => '%s',
					'firstname' => '%s',
					'phone' => '%s'
				);

				$member_values = array();
				$member_types = array();

				foreach ($member_data as $key => $type) {

					if (isset($_POST[$key])) {

						$member_values[$key] = $_POST[$key];
						$member_types[] = $type;

					}

				}

				// $member_values = array(
				// 	'email' => $_POST['email'],
				// 	'lastname' => $_POST['lastname'],
				// 	'firstname' => $_POST['firstname'],
				// 	'phone' => isset($_POST['phone']) ? $_POST['phone'] : ''
				// );
				// $member_types = array ('%s', '%s', '%s', '%s');

				$query = $wpdb->insert($members_table, $member_values, $member_types);
				$member_id = $wpdb->insert_id;

			}

			$date_id = intval($_POST['date_id']);

			$query = $wpdb->insert(
				$reservations_table,
				array(
					'date_id' => $date_id,
					'member_id' => $member_id,
					'status' => 2,
					'notes' => isset($_POST['note']) ? $_POST['note'] : ''
				),
				array ('%d', '%d', '%d', '%s', '%s')
			);

			$reservation_id = $wpdb->insert_id;

			foreach ($tickets as $ticket) {

				for ($i = 0; $i < $ticket->num; $i++) {

					$query = $wpdb->insert(
						$tickets_table,
						array(
							'reservation_id' => $reservation_id,
							'tarif_id' => $ticket->tarif_id
						),
						array ('%d', '%d')
					);

				}

			}

			$output['id'] = $reservation_id;

			do_action('karma_reservation_add', $reservation_id, $date_id, $member_id, $tickets, $_POST);

		} else {

			$output['error'] = 'missing fields';

		}

		echo json_encode($output);
		exit;

	}

	/**
	 *	create reservations table
	 */
	public function create_reservations_table(){
		global $wpdb, $karma;

		$table_version = '002';

		if ($table_version !== $karma->options->get_option('reservations_table_version')) {

			$table = $wpdb->prefix.$this->reservations_table;

			$charset_collate = '';

			if (!empty($wpdb->charset)){
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}

			if (!empty($wpdb->collate)){
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			$mysql = "CREATE TABLE $table (
				id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				date_id int(11) NOT NULL,
				member_id int(11) NOT NULL,
				status tinyint(1) NOT NULL,
				notes text NOT NULL
			) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($mysql);

			$karma->options->update_option('reservations_table_version', $table_version);
		}

	}

	/**
	 *	create member table
	 */
	public function create_members_table(){
		global $wpdb, $karma;

		$table_version = '002';

		if ($table_version !== $karma->options->get_option('members_table_version')) {

			$table = $wpdb->prefix.$this->members_table;

			$charset_collate = '';

			if (!empty($wpdb->charset)){
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}

			if (!empty($wpdb->collate)){
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			$mysql = "CREATE TABLE $table (
				id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				email varchar(50) NOT NULL,
				firstname varchar(50) NOT NULL,
				lastname varchar(50) NOT NULL,
				phone varchar(50) NOT NULL,
				address varchar(100) NOT NULL,
				zip varchar(10) NOT NULL,
				city varchar(50) NOT NULL,
				country varchar(50) NOT NULL,
				meta text NOT NULL
			) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($mysql);

			$karma->options->update_option('members_table_version', $table_version);
		}

	}

	/**
	 *	create tickets table
	 */
	public function create_tickets_table(){
		global $wpdb, $karma;

		$table_version = '002';

		if ($table_version !== $karma->options->get_option('tickets_table_version')) {

			$table = $wpdb->prefix.$this->tickets_table;

			$charset_collate = '';

			if (!empty($wpdb->charset)){
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}

			if (!empty($wpdb->collate)){
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			$mysql = "CREATE TABLE $table (
				id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				reservation_id int(11) NOT NULL,
				tarif_id int(11) NOT NULL
			) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($mysql);

			$karma->options->update_option('tickets_table_version', $table_version);
		}

	}


	/**
	 *	@hook init
	 */
	public function register_post_type() {

		if (is_admin()) {

			add_action('add_meta_boxes', array($this, 'meta_boxes'), 10, 2);
			add_action('save_post_'.$this->tarif_post_type, array($this, 'save_meta_boxes'), 10, 3);

		}

		register_post_type($this->tarif_post_type, array(
			'labels'             => array(
				'name' => 'Billet'
			),
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest' => true,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array('title')
		));

	}

	/**
	 * @hook add_meta_boxes
	 */
	public function meta_boxes($post_type, $post) {

		if ($post_type === $this->tarif_post_type) {

			add_meta_box(
				'price',
				'Prix',
				array($this, 'ticket_price_meta_box'),
				array($this->tarif_post_type),
				'normal',
				'default'
			);

		}

	}

	/**
	 * @hook add_meta_boxes
	 */
	public function ticket_price_meta_box($post) {

		wp_nonce_field('ticket_price-action', 'ticket_price_nonce', false, true);

		include get_template_directory() . '/admin/include/reservations-tarif-metabox.php';

	}

	/**
	 * Save meta boxes
	 *
	 * @hook 'save_post_{post_type}'
	 */
	public function save_meta_boxes($post_id, $post, $update) {

		if (current_user_can('edit_post', $post_id) && (!defined( 'DOING_AUTOSAVE' ) || !DOING_AUTOSAVE )) {

			if (isset($_POST['ticket_price_nonce']) && wp_verify_nonce($_POST['ticket_price_nonce'], 'ticket_price-action')) {

				if (isset($_POST['price'])) {

					update_post_meta($post_id, 'price', $_POST['price']);

				}

			}

		}

	}

	/**
	 *	Create admin menu
	 */
	function admin_menu(){

		add_menu_page(
			'Reservations',
			'Reservations',
			'read',
			$this->page_name,
			array($this, 'print_reservations'),
			'dashicons-clipboard'
		);

	}




	/**
	 *	Print Reservations
	 */
	function print_reservations(){
		global $wpdb;

		$reservations_table = $wpdb->prefix.$this->reservations_table;
		$members_table = $wpdb->prefix.$this->members_table;
		$tickets_table = $wpdb->prefix.$this->tickets_table;

		$reservations = $wpdb->get_results(
			"SELECT rm.email, rm.phone, rm.firstname, rm.lastname, rt.tarif_id, r.date_id, r.date, r.notes FROM $tickets_table AS rt
			JOIN $reservations_table AS r ON (rt.reservation_id = r.id)
			JOIN $members_table AS rm ON (r.member_id = rm.id)
			JOIN $wpdb->postmeta as pm ON (rt.tarif_id = pm.post_id AND pm.meta_key = 'price')
			"
		);

		echo '<pre>';
		print_r($reservations);
		echo '</pre>';

		// include get_template_directory() . '/admin/include/orders-logs.php';

	}


}

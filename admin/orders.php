<?php

class Karma_Orders {

	var $table_name = 'orders';
	var $page_name = 'orders';
	var $invoice_table_name = 'invoice';
	// var $base_num_invoice = 4356000;

	var $num_rows = 50;

	/**
	 *	constructor
	 */
	public function __construct() {

		add_action('karma-orders-add-row', array($this, 'add_row'));



		if (is_admin()) {

			add_action('admin_menu', array($this, 'admin_menu'));
			add_action('init', array($this, 'create_invoice_table'));
			add_action('init', array($this, 'create_log_table'));
			add_action('init', array($this, 'request_orders'), 20);

			add_action('admin_post_nopriv_get_invoice', array($this, 'admin_post_get_invoice'));
			add_action('admin_post_get_invoice', array($this, 'admin_post_get_invoice'));

		}

	}



	/**
	 *	create log table
	 */
	public function create_log_table(){
		global $wpdb, $karma;

		$table_version = '003';

		if ($table_version !== $karma->options->get_option('orders_table_version')) {

			// create the table for logging ipn data

			$table = $wpdb->prefix.$this->table_name;

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
				email varchar(50) NOT NULL,
				firstname varchar(50) NOT NULL,
				lastname varchar(50) NOT NULL,
				phone varchar(50) NOT NULL,
				address varchar(100) NOT NULL,
				zip varchar(50) NOT NULL,
				city varchar(50) NOT NULL,
				country varchar(50) NOT NULL,
				price float NOT NULL DEFAULT '0',
				shipping float NOT NULL DEFAULT '0',
				currency varchar(4) NOT NULL,
				items text NOT NULL,
				notes text NOT NULL,
				meta text NOT NULL
			) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($mysql);

			$karma->options->update_option('orders_table_version', $table_version);
		}

	}


	/**
	 *	create log table
	 */
	public function create_invoice_table(){
		global $wpdb, $karma;

		$table_version = '003';

		if ($table_version !== $karma->options->get_option('orders_invoice_table_version')) {

			$table = $wpdb->prefix.$this->invoice_table_name;

			$charset_collate = '';

			if (!empty($wpdb->charset)){
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}

			if (!empty($wpdb->collate)){
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			$mysql = "CREATE TABLE $table (
				id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				order_id int(11) NOT NULL,
				hash varchar(16) NOT NULL
			) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($mysql);

			$karma->options->update_option('orders_invoice_table_version', $table_version);
		}

	}

	/**
	 *	Create admin menu
	 */
	function admin_menu(){

		add_menu_page(
			'Orders',
			'Orders',
			'read',
			$this->page_name,
			array($this, 'print_logs'),
			'dashicons-cart'
		);

	}


	/**
	 * parse order request params
	 *
	 * @hook 'init
	 * @from nov2018
	 */
	public function request_orders() {

		if (isset($_GET['page']) && $_GET['page'] === $this->page_name) {

			$this->request_orders = array();

			if (isset($_GET['year']) && $_GET['year']) {

				$year = intval($_GET['year']);
				$min_time = mktime(0, 0, 0, 1, 1, $year);
				$max_time = mktime(0, 0, 0, 1, 1, $year + 1);
				$this->request_orders['year'] = $year;

			}

			if (isset($_GET['month']) && $_GET['month']) {

				if (!isset($year)) {

					$year = intval(date('Y'));
					$this->request_orders['year'] = $year;

				}

				$month = intval($_GET['month']);
				$min_time = mktime(0, 0, 0, $month, 1, $year);
				$max_time = mktime(0, 0, 0, $month+1, 1, $year);
				$this->request_orders['month'] = $month;

			}

			if (isset($max_time, $min_time)) {

				$this->request_orders['min_date'] = date('Y-m-d h:i:s', $min_time);
				$this->request_orders['max_date'] = date('Y-m-d h:i:s', $max_time);

			}

			if (isset($_GET['pagenum'])){

				$this->request_orders['pagenum'] = intval($_GET['pagenum']);

			}	else {

				$this->request_orders['pagenum'] = 1;

			}

			$this->request_orders['num_rows'] = $this->num_rows;


			// if (isset($_GET['export']) && $_GET['export']) {
			//
			// 	$this->export_orders(urldecode($_GET['export']));
			//
			// }

			if (isset($_GET['download']) && $_GET['download']) {

				$this->download_invoices(urldecode($_GET['download']));

			}

		}

	}

	/**
	 * Query orders
	 *
	 * @from nov2018
	 */
	public function get_orders($request_orders) {
		global $wpdb;

		$table = $wpdb->prefix.$this->table_name;
		$invoice_table = $wpdb->prefix.$this->invoice_table_name;

		$where_args = array();

		if (isset($request_orders['min_date'])) {

			$where_args[] = $wpdb->prepare('o.date > %s', $request_orders['min_date']);

		}

		if (isset($request_orders['max_date'])) {

			$where_args[] = $wpdb->prepare('o.date < %s', $request_orders['max_date']);

		}

		$where = $where_args ? 'WHERE ' . implode(' AND ', $where_args) : '';

		$join = "JOIN $invoice_table AS i ON (o.id = i.order_id)";

		if (isset($request_orders['pagenum'], $request_orders['num_rows'])) {

			$num_rows = $request_orders['num_rows'];
			$pagenum = $request_orders['pagenum'];
			$offset = ($pagenum - 1) * $num_rows;
			$limit = "LIMIT $offset, $num_rows";

		} else {

			$limit = '';

		}

		$order = "ORDER BY o.date DESC";

		return array(
			'items' => $wpdb->get_results("SELECT o.*, i.hash AS invoice FROM $table AS o $where $join GROUP BY o.id $order $limit"),
			'total' => $wpdb->get_var("SELECT COUNT('id') FROM $table $where")
		);

	}




	/**
	 *	Print Orders logs
	 */
	function print_logs(){

		$orders_results = $this->get_orders($this->request_orders);

		$page_links = paginate_links(array(
			'base' => add_query_arg('pagenum', '%#%'),
			'format' => '',
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'total' => ceil($orders_results['total']/$this->num_rows),
			'current' => $this->request_orders['pagenum']
		));

		$fields = apply_filters('karma_orders_fields', array(
			'id',
			'date',
			'email',
			'price'
		));

		add_filter('karma_orders_header_cell', array($this, 'print_header_cell'));

		include get_template_directory() . '/admin/include/orders-logs.php';

	}

	/**
	 * Print Header Cell
	 *
	 * @filter 'karma_orders_header_cell'
	 */
	public function print_header_cell($col) {

		switch ($col) {
			case 'id': return 'ID';
			case 'date': return 'Date';
			case 'email': return 'Email';
			case 'price': return 'Price';
		}

		return $col;
	}

	/**
	 * Print Table filters
	 *
	 * @from nov2018
	 */
	public function print_filters() {

		include get_template_directory() . '/admin/include/orders-filters.php';

	}


	/**
	 *	add row
	 *
	 *	@hook 'karma-orders-add-row'
	 */
	// public function add_row(
	// 	$email,
	// 	$licence,
	// 	$billed,
	// 	$items,
	// 	$currency) {
	// 	global $wpdb;
	//
	// 	$query = $wpdb->insert(
	// 		$wpdb->prefix.$this->table_name,
	// 		array(
	// 			'email' => $email,
	// 			'licence' => $licence,
	// 			'billed' => $billed,
	// 			'items' => $items,
	// 			'currency' => $currency,
	// 		),
	// 		array ('%s', '%s', '%s', '%s', '%s')
	// 	);
	//
	// 	return $wpdb->insert_id;
	// }

	/**
	 *	get row
	 */
	public function get_row($id) {
		global $wpdb;

		$id = intval($id);
		$table = $wpdb->prefix.$this->table_name;

		return $wpdb->get_row( "SELECT * FROM $table WHERE id = $id" );

	}

	/**
	 *	add row to invoice table
	 */
	public function add_invoice($order_id) {
		global $wpdb;

		// if (function_exists('random_bytes')) {
		//
		// 	$hash = random_bytes(16);
		//
		// } else {
		//
		// 	$hash = uniqid();
		//
		// }

		$hash = uniqid();

		$query = $wpdb->insert(
			$wpdb->prefix.$this->invoice_table_name,
			array(
				'order_id' => $order_id,
				'hash' => $hash
			),
			array ('%d', '%s')
		);

		return $hash;
	}

	/**
	 * @admin-post 'get_invoice'
	 */
	public function admin_post_get_invoice() {
		global $wpdb;

		if (isset($_GET['invoice'])) {

			$table = $wpdb->prefix.$this->invoice_table_name;

			$order_id = $wpdb->get_var($wpdb->prepare(
				"SELECT order_id FROM $table WHERE hash = %s",
				esc_attr($_GET['invoice'])
			));

			if ($order_id) {

				$this->print_invoice($order_id);
				exit;

			} else {

				die("order does not exist ($order_id)");

			}

		}

		die("no invoice");

	}

	/**
	 *	print invoice
	 */
	public function print_invoice($order_id, $filename = null) {
		global $wpdb;

		$table = $wpdb->prefix.$this->table_name;

		$order = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $table WHERE id = %d",
			$order_id
		));

		if ($order) {

			do_action('karma_fpdf');

			if (class_exists('FPDF')) {

				do_action('karma_print_invoice', $order);

			} else {

				die("FPDF not installed");

			}

		} else {

			die("order not exist ($order_id)");

		}



		// class PDF extends FPDF
		// {
		// // Page header
		// function Header()
		// {
		//     // Logo
		//     $this->Image('logo.png',10,6,30);
		//     // Arial bold 15
		//     $this->SetFont('Arial','B',15);
		//     // Move to the right
		//     $this->Cell(80);
		//     // Title
		//     $this->Cell(30,10,'Title',1,0,'C');
		//     // Line break
		//     $this->Ln(20);
		// }
		//
		// // Page footer
		// function Footer()
		// {
		//     // Position at 1.5 cm from bottom
		//     $this->SetY(-15);
		//     // Arial italic 8
		//     $this->SetFont('Arial','I',8);
		//     // Page number
		//     $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
		// }
		// }

		// Instanciation of inherited class
		// $pdf = new FPDF();
		// $pdf->AliasNbPages();
		// $pdf->AddPage();
		// $pdf->SetFont('Times','',12);
		// for($i=1;$i<=40;$i++)
		//     $pdf->Cell(0,10,'Printing line number '.$i,0,1);
		// $pdf->Output();
		//
		//
		//
		// var_dump($order);

		die();



	}

	/**
	 * Receive Stripe payment and redirect
	 *
	 * @from 4.0
	 */
	// public function add_stripe_payment($token, $amount, $currency = 'CHF', $description = '') {
	// 	global $color_profile, $karma_stripe;
	//
	// 	if (isset($karma_stripe)) {
	//
	// 		$charge = $karma_stripe->charge(array(
	// 			'source' => $token,
	// 			'amount' => round($amount) * 100,
	// 			'currency' => 'CHF',
	// 			'description' => $description
	// 		));
	//
	// 	if ($charge && $charge->paid) {
	//
	// 		$id = $color_profile->orders->add_row(
	// 			$_SESSION['licence']['email'],
	// 			serialize($_SESSION['licence']),
	// 			isset($_SESSION['billing']) ? serialize($_SESSION['billing']) : '',
	// 			serialize($items),
	// 			'CHF'
	// 		);
	//
	//
	// 		$this->send_email($id, $data, $link);
	//
	// 		$success_page_id = $color_profile->get_option('success_page_id');
	//
	// 		wp_redirect(get_permalink($success_page_id));
	//
	// 	}
	//
	// 	exit;
	// }


	/**
	 * export route
	 *
	 * @hook 'init
	 * @from nov2018
	 */
	// public function export_orders($filename) {
	//
	// 	$export_request_orders = $this->request_orders;
	//
	// 	unset($export_request_orders['pagenum']);
	// 	unset($export_request_orders['num_rows']);
	//
	// 	$orders_results = $this->get_orders($export_request_orders);
	//
	// 	$licence = unserialize($orders_results['items'][0]->licence);
	// 	$items = unserialize($orders_results['items'][0]->items);
	//
	// 	$profile_ids = array();
	//
	// 	foreach ($orders_results['items'] as $item) {
	//
	// 		if ($item->items) {
	//
	// 			$profile_items = unserialize($item->items);
	//
	// 			foreach ($profile_items as $profile_item) {
	//
	// 				$profile_ids[] = $profile_item['id'];
	//
	// 			}
	//
	// 		}
	//
	// 		if ($item->profiles) {
	//
	// 			$profile_ids = unserialize($item->profiles);
	//
	// 			foreach ($profile_ids as $profile_id) {
	//
	// 				$profile_ids[] = $profile_id;
	//
	// 			}
	//
	// 		}
	//
	// 	}
	//
	// 	// $this->cache_profiles($profile_ids);
	//
	// 	$data = array();
	//
	// 	foreach ($orders_results['items'] as $item) {
	//
	// 		$object = new stdClass();
	//
	// 		$object->id = isset($item->id) ? $item->id : 0;
	// 		$object->date = isset($item->timestamp) ? $item->timestamp : '';
	// 		$object->email = isset($item->email) ? $item->email : '';
	// 		$object->num = 0;
	// 		$object->cost = isset($item->cost) ? $item->cost : 0;
	// 		$object->currency = isset($item->currency) ? $item->currency : '';
	//
	//
	// 		if ($item->items) {
	//
	// 			$profile_items = unserialize($item->items);
	//
	// 			$profile_lines = array();
	//
	// 			foreach ($profile_items as $profile_item) {
	//
	// 				$profile = get_post($profile_item['id']);
	// 				$profile_lines[] = $profile->post_title . " ({$profile_item['num_user']}, {$profile_item['price']}, {$profile_item['base_price']}, {$profile_item['user_price']}, {$profile_item['discount']})";
	//
	// 				$object->num++;
	// 				$object->cost += intval($profile_item['price']);
	//
	// 			}
	//
	// 			$object->profiles = implode("\n", $profile_lines);
	//
	// 		} else if ($item->profiles) { // -> compat
	//
	// 			$profile_ids = unserialize($item->profiles);
	// 			$profile_lines = array();
	//
	// 			foreach ($profile_ids as $profile_id) {
	//
	// 				$profile = get_post($profile_id);
	// 				$profile_lines[] = $profile->post_title;
	// 				$object->num++;
	//
	// 			}
	//
	// 			$object->profiles = implode("\n", $profile_lines);
	//
	// 		} else {
	//
	// 			$object->profiles = '?';
	//
	// 		}
	//
	// 		$licence = array();
	//
	// 		if ($item->licence) {
	//
	// 			$licence = unserialize($item->licence);
	//
	// 		}
	//
	// 		$billed = array();
	//
	// 		if ($item->billed) {
	//
	// 			$billed = unserialize($item->billed);
	//
	// 		}
	//
	// 		$object->licence_email = isset($licence['email']) ? $licence['email'] : '';
	// 		$object->licence_firstname = isset($licence['firstname']) ? $licence['firstname'] : '';
	// 		$object->licence_lastname = isset($licence['lastname']) ? $licence['lastname'] : '';
	// 		$object->licence_company = isset($licence['company']) ? $licence['company'] : '';
	// 		$object->licence_address = isset($licence['address']) ? $licence['address'] : '';
	// 		$object->licence_city = isset($licence['city']) ? $licence['city'] : '';
	// 		$object->licence_zip = isset($licence['zip']) ? $licence['zip'] : '';
	// 		$object->licence_state = isset($licence['state']) ? $licence['state'] : '';
	// 		$object->licence_country = isset($licence['country']) ? $licence['country'] : '';
	// 		$object->licence_phone = isset($licence['phone']) ? $licence['phone'] : '';
	//
	// 		$object->billed_email = isset($billed['email']) ? $billed['email'] : '';
	// 		$object->billed_firstname = isset($billed['firstname']) ? $billed['firstname'] : '';
	// 		$object->billed_lastname = isset($billed['lastname']) ? $billed['lastname'] : '';
	// 		$object->billed_company = isset($billed['company']) ? $billed['company'] : '';
	// 		$object->billed_address = isset($billed['address']) ? $billed['address'] : '';
	// 		$object->billed_city = isset($billed['city']) ? $billed['city'] : '';
	// 		$object->billed_zip = isset($billed['zip']) ? $billed['zip'] : '';
	// 		$object->billed_state = isset($billed['state']) ? $billed['state'] : '';
	// 		$object->billed_country = isset($billed['country']) ? $billed['country'] : '';
	// 		$object->billed_phone = isset($billed['phone']) ? $billed['phone'] : '';
	//
	// 		$data[] = $object;
	//
	// 	}
	//
	// 	$headers = array(
	// 		'id' => 'id',
	// 		'date' => 'date',
	// 		'email' => 'email',
	// 		'num' => 'num',
	// 		'cost' => 'cost',
	// 		'currency' => 'currency',
	// 		'profiles' => 'details (num_user, price, base_price, user_price, discount)',
	// 		'licence_email' => 'licence_email',
	// 		'licence_firstname' => 'licence_firstname',
	// 		'licence_lastname' => 'licence_lastname',
	// 		'licence_company' => 'licence_company',
	// 		'licence_address' => 'licence_address',
	// 		'licence_city' => 'licence_city',
	// 		'licence_zip' => 'licence_zip',
	// 		'licence_state' => 'licence_state',
	// 		'licence_country' => 'licence_country',
	// 		'licence_phone' => 'licence_phone',
	// 		'billed_email' => 'billed_email',
	// 		'billed_firstname' => 'billed_firstname',
	// 		'billed_lastname' => 'billed_lastname',
	// 		'billed_company' => 'billed_company',
	// 		'billed_address' => 'billed_address',
	// 		'billed_city' => 'billed_city',
	// 		'billed_zip' => 'billed_zip',
	// 		'billed_state' => 'billed_state',
	// 		'billed_country' => 'billed_country',
	// 		'billed_phone' => 'billed_phone',
	// 	);
	//
	// 	if (!$filename) {
	//
	// 		$filename = 'export.xlsx';
	//
	// 	}
	//
	// 	do_action('karma_excel_export', $data, $filename, $headers);
	//
	// }






	/**
	 *	print invoice
	 */
// 	public function print_invoice($id, $filename = null) {
//
// 		static $mdpf_included = false;
//
// 		if (!$mdpf_included) {
//
// 			do_action('include_mpdf');
// 			$mdpf_included = true;
//
// 		}
//
// 		$row = $this->get_row($id);
//
// 		$num = $this->base_num_invoice + intval($id);
// 		$licence = unserialize($row->licence);
// 		$billed = ($row->billed && $row->billed != 'N;') ? unserialize($row->billed) : $licence;
// 		$currency = $row->currency;
// 		$items = unserialize($row->items);
//
// 		// -> retro compatibilité
// 		if (empty($items)) {
//
// 			$items = array_map(function($id) use($row) {
// 				return array(
// 					'id' => $id,
// 					'num_user' => 1,
// 					'price' => $row->cost
// 				);
// 			}, unserialize($row->profiles));
//
// 		}
//
// 		$profile_ids = array_map(function($item) {
// 			return intval($item['id']);
// 		}, $items);
//
//
// 		$this->cache_profiles($profile_ids);
//
// 		$mpdf = new mPDF();
//
// 		$output = '
// <table>
// 	<tr>
// 		<td><h1>Color Library</h1></td>
// 		<td><h1>Invoice No '.$num.'</h1></td>
// 	</tr>
// </table>
// <table>
// 	<tr>
// 		<td class="underline">Date</td>
// 	</tr>
// 	<tr>
// 		<td>'.preg_replace('/(\d{4})-(\d{2})-(\d{2}).*/', '$3.$2.$1', $row->timestamp).'</td>
// 	</tr>
// </table>
// <table>
// 	<tr>
// 		<td class="underline">Licence to</td>
// 		<td class="underline">Billed to</td>
// 	</tr>
// 	<tr>
// 		<td>'.$licence['firstname'].' '.$licence['lastname'].'<br/>'.$licence['address'].'<br/>'.$licence['zip'].' '.$licence['city'].'<br/>'.$licence['country'].'</td>
// 		<td>'.$billed['firstname'].' '.$billed['lastname'].'<br/>'.$billed['address'].'<br/>'.$billed['zip'].' '.$billed['city'].'<br/>'.$billed['country'].'</td>
//
// 	</tr>
// </table>
// <table>
// 	<tr>
// 		<td class="underline">Profile(s)</td>
// 		<td class="underline right">'.$currency.'</td>
// 	</tr>';
//
// 		$total = 0;
//
// 		foreach ($items as $item) {
//
// 			$total += $item['price'];
//
// 			$output .= '<tr><td>'.get_the_title($item['id']).' ('.colorprofile_print_usercount($item['num_user']).')</td><td class="right">'.number_format($item['price'], 2, '.', "'").'</td></tr>';
//
// 		}
//
// 		$output .= str_repeat('<tr><td><br/></td><td><br/></td></tr>', max(0, 17-count($items)));
//
// 		$output .= '
// 	<tr>
// 		<td class="underline"></td>
// 		<td class="underline"></td>
// 	</tr>
// 	<tr>
// 		<td class="underline">TOTAL (VAT free)</td>
// 		<td class="underline right">'.number_format($total, 2, '.', "'").'</td>
// 	</tr>
// </table>';
//
// 		$mpdf->WriteHTML('
// h1 {
// 	font-weight: normal;
// }
// table {
// 	width:100%;
// 	margin-bottom:30px;
// 	border-collapse: collapse;
// 	font-family: basel;
// }
// td {
// 	width: 50%;
// 	padding-top: 10px;
// 	padding-bottom: 0px;
// }
// td.underline {
// 	border-bottom: 1px solid #000000;
// 	padding-bottom: 10px;
// }
// .right {
// 	text-align: right;
// }',1);
//
// 		$mpdf->WriteHTML($output);
//
// 		$mpdf->SetHTMLFooter('
// <table>
// 	<tr>
// 		<td>
// 			Color Library<br/>
// 			Automated Color Separation<br/>
// 			www.colorlibrary.ch<br/>
// 			color@colorlibrary.ch</td>
// 		<td>
// 			Color Library<br/>
// 			Le Tirage 10<br/>
// 			1143 Apples<br/>
// 			Switzerland
// 		</td>
// 		<td>
// 			Paid in full by credit card or PayPal in<br/>
// 			Swiss Francs (CHF). Color Library is a non-profit <br/>
// 			organisation, and is not subject to VAT, according <br/>
// 			to Swiss law. IDE/VATIN: CHE-451.769.975
// 		</td>
// 	</tr>
// </table>');
//
// 		//$mpdf->Output('filename.pdf','D'); // -> download file
//
// 		if (isset($filename)) {
//
// 			$mpdf->Output($filename,'F'); // -> save on server
//
// 		} else {
//
// 			$mpdf->Output(); // -> open in browser
//
// 			exit;
//
// 		}
//
// 	}

	/**
	 * download zip of all invoices
	 *
	 * @from nov2018
	 */
	// public function download_invoices($filename) {
	//
	// 	$export_request_orders = $this->request_orders;
	//
	// 	unset($export_request_orders['pagenum']);
	// 	unset($export_request_orders['num_rows']);
	//
	// 	$orders_results = $this->get_orders($export_request_orders);
	//
	// 	$dir = wp_upload_dir();
	//
	// 	$invoice_dir = $dir['path'].'/invoices'.uniqid().'/';
	//
	// 	if (!file_exists($invoice_dir)) {
	//
	// 		mkdir($invoice_dir, 0777);
	//
	// 	}
	//
	// 	$files = array();
	//
	// 	foreach ($orders_results['items'] as $item) {
	//
	// 		$id = $item->id;
	// 		$file = $invoice_dir.'invoice-'.$id.'.pdf';
	// 		$this->print_invoice($id, $file);
	// 		$files[] = $file;
	//
	// 	}
	//
	// 	$zip = new ZipArchive();
	// 	$r = $zip->open($dir['path']."/".$filename, ZipArchive::CREATE);
	//
	// 	foreach ($files as $file) {
	//
	// 		$zip->addFile($file, basename($file));
	//
	// 	}
	//
	// 	$zip->close();
	//
	// 	header("Content-type: application/zip");
	// 	header("Content-Disposition: attachment; filename=$filename");
	// 	header('Cache-Control: max-age=0');
	// 	readfile($dir['url']."/".$filename);
	//
	// 	unlink($dir['path']."/".$filename);
	//
	// 	foreach ($files as $file) {
	//
	// 		unlink($file);
	//
	// 	}
	//
	// 	rmdir($invoice_dir);
	//
	// 	exit;
	//
	// }



}

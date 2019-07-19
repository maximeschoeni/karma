<?php

class Karma_Table {


	/**
	 *	constructor
	 */
	// function __construct() {
	//
	// 	if (is_admin()) {
	//
	// 		add_action('init', array($this, 'init'));
	//
	// 	}
	//
	// }
	//
	// /**
	//  * @hook 'init'
	//  */
	// public function init() {
	//
	// 	// do_action('karma_create_table', $name, $mysql, $version);
	//
	// 	add_action('karma_create_table', array($this, 'create_table'), 10, 3);
	//
	// }


	/**
	 *	create table
	 */
	static function create($name, $mysql, $version = '000') {
		global $wpdb, $karma;



		if ($version !== $karma->options->get_option('karma_table_'.$name.'_version')) {

			// create the table for logging ipn data

			$table = $wpdb->prefix.$name;

			$charset_collate = '';

			if (!empty($wpdb->charset)){
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}

			if (!empty($wpdb->collate)){
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			// CREATE TABLE $table (
			// 	id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			// 	date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			// 	email varchar(50) NOT NULL,
			// 	firstname varchar(50) NOT NULL,
			// 	lastname varchar(50) NOT NULL,
			// 	phone varchar(50) NOT NULL,
			// 	address varchar(100) NOT NULL,
			// 	zip varchar(50) NOT NULL,
			// 	city varchar(50) NOT NULL,
			// 	country varchar(50) NOT NULL,
			// 	price float NOT NULL DEFAULT '0',
			// 	currency varchar(4) NOT NULL,
			// 	items text NOT NULL,
			// 	notes text NOT NULL,
			// 	meta text NOT NULL
			// )

			// id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			// date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			// email varchar(50) NOT NULL,
			// firstname varchar(50) NOT NULL,
			// lastname varchar(50) NOT NULL,
			// phone varchar(50) NOT NULL,
			// address varchar(100) NOT NULL,
			// zip varchar(50) NOT NULL,
			// city varchar(50) NOT NULL,
			// country varchar(50) NOT NULL,
			// price float NOT NULL DEFAULT '0',
			// currency varchar(4) NOT NULL,
			// items text NOT NULL,
			// notes text NOT NULL,
			// meta text NOT NULL

			$mysql = "CREATE TABLE $table (
				$mysql
			) $charset_collate;";


			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($mysql);

			$karma->options->update_option('karma_table_'.$name.'_version', $version);
		}

	}



}

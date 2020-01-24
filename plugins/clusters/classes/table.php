<?php

class Karma_Table {

	/**
	 *	create table
	 */
	static function create($name, $mysql, $version = '000') {
		global $wpdb, $karma_clusters_options;

		if ($version !== $karma_clusters_options->get_option('karma_table_'.$name.'_version')) {

			$table = $wpdb->prefix.$name;

			$charset_collate = '';

			if (!empty($wpdb->charset)){
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}

			if (!empty($wpdb->collate)){
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			$mysql = "CREATE TABLE $table (
				$mysql
			) $charset_collate;";


			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($mysql);

			$karma_clusters_options->update_option('karma_table_'.$name.'_version', $version);
		}

	}

}

<?php

/**
 *	Class Karma_Sublanguage
 */
class Karma_Cluster_Multilanguage {

	/**
	 *	Constructor
	 */
	public function __construct() {

		add_filter('karma_cluster_path', array($this, 'append_language_to_path'), 10, 2);
		add_filter('karma_cluster_request', array($this, 'append_language_to_query'));

		add_filter('karma_save_cluster', array($this, 'save_cluster'), 10, 4);
		add_filter('karma_delete_cluster', array($this, 'delete_cluster'), 10, 4);

		add_filter('karma_update_cluster_query', array($this, 'update_cluster_query'), 10, 3);
		// add_action('karma_update_cluster', array($this, 'update_cluster'));

	}

	/**
	 * @hook 'karma_save_cluster'
	 */
	public function save_cluster($cluster_row, $post_id, $post_type, $clusters) {
		global $wpdb, $sublanguage_admin;

		if (isset($sublanguage_admin) && $sublanguage_admin->is_post_type_translatable($post_type)) {

			foreach ($sublanguage_admin->get_languages() as $language) {

				if ($sublanguage_admin->is_sub($language)) {

					$t_path = $cluster_row->path.'/'.$language->post_name;

					$cluster_table = $wpdb->prefix.$clusters->table_name;

					$t_cluster_row = $wpdb->get_row($wpdb->prepare(
						"SELECT * FROM $cluster_table WHERE path = %s",
						$t_path
					));

					if ($t_cluster_row) {

						$wpdb->query($wpdb->prepare(
							"UPDATE $cluster_table SET status = 100 WHERE id = %d",
							$t_cluster_row->id
						));

					} else {

						$t_request = add_query_arg(array('language' => $language->post_name), $cluster_row->request);
						// parse_str($request, $query_vars);
						// $t_request = http_build_query(array_merge($query_vars, array('language' => $language->post_name)));

						$t_cluster_row = $clusters->create_cluster($t_request, $t_path, $post_type);

					}

					do_action('karma_save_cluster_multilanguage', $t_cluster_row, $post_id, $post_type, $cluster_row, $this, $language, $sublanguage_admin);

				}

			}

		}

	}

	/**
	 * @hook 'karma_delete_cluster'
	 */
	public function delete_cluster($cluster_row, $post_id, $post_type, $clusters) {
		global $wpdb, $sublanguage_admin;

		foreach ($sublanguage_admin->get_languages() as $language) {

			if ($sublanguage_admin->is_sub($language)) {

				$t_path = $cluster_row->path.'/'.$language->post_name;

				$table = $wpdb->prefix.$clusters->table_name;

				$t_cluster_row = $wpdb->get_row($wpdb->prepare(
					"SELECT * FROM $table WHERE path = %s",
					$t_path
				));

				do_action('karma_delete_cluster_multilanguage', $t_cluster_row, $post_id, $post_type, $cluster_row, $this, $language, $sublanguage_admin);

				if ($t_cluster_row) {

					$this->files->remove($t_cluster_row->path);

					$wpdb->query($wpdb->prepare(
						"DELETE FROM $table WHERE id = %d",
						$t_cluster_row->id
					));

				}

			}

		}

	}

	/**
	 * @filter 'karma_append_language_to_url'
	 */
	public function append_language_to_path($path, $post_type = null) {
		global $sublanguage, $sublanguage_admin;

		if (isset($sublanguage_admin) && $sublanguage_admin->is_sub()) {

			$language = $sublanguage_admin->get_language();

		} else if (isset($sublanguage) && $sublanguage->is_sub()) {

			$language = $sublanguage->get_language();

		}

		if (isset($language) && $language) {

			$path .= '/' . $language->post_name;

		}

		return $path;
	}


	// /**
	//  * @filter 'karma_cluster_items_to_update'
	//  */
	// public function cluster_items_to_update($items) {
	// 	global $sublanguage_admin;
	//
	// 	$new_items = array();
	//
	// 	if (isset($sublanguage_admin)) {
	//
	// 		foreach ($items as $item) {
	//
	// 			if ($sublanguage_admin->is_post_type_translatable($item['post_type'])) {
	//
	// 				foreach ($sublanguage_admin->get_languages() as $language) {
	//
	// 					$new_item = $item;
	// 					$new_item['language'] = $language->post_name;
	// 					$new_items[] = $new_item;
	//
	// 				}
	//
	// 			} else {
	//
	// 				$new_items[] = $item;
	//
	// 			}
	//
	// 		}
	//
	// 		return $new_items;
	//
	// 	}
	//
	// 	return $items;
	// }

	/**
	 * @filter 'karma_append_language_to_query'
	 */
	public function append_language_to_query($query) {
		global $sublanguage, $sublanguage_admin;

		if (isset($sublanguage_admin) && $sublanguage_admin->is_sub()) {

			$language = $sublanguage_admin->get_language();

		} else if (isset($sublanguage) && $sublanguage->is_sub()) {

			$language = $sublanguage->get_language();

		}

		if (isset($language) && $language) {

			$query = add_query_arg(array('language' => $language->post_name), $query);

		}

		return $query;
	}

	/**
	 * @filter 'karma_update_cluster_query'
	 */
	public function update_cluster_query($query, $cluster_row, $clusters) {
		global $sublanguage, $sublanguage_admin;

		if (isset($sublanguage_admin)) {

			if (isset($query->query[$sublanguage_admin->language_query_var])) {

				$language = $sublanguage_admin->get_language_by($query->query[$sublanguage_admin->language_query_var], 'post_name');

				if (isset($language) && $language) {

					$sublanguage_admin->set_language($language);

				}

			}

		} else if (isset($sublanguage)) {

			if (isset($query->query[$sublanguage->language_query_var])) {

				$language = $sublanguage->get_language_by($query->query[$sublanguage->language_query_var], 'post_name');

				if (isset($language) && $language) {

					$sublanguage->set_language($language);

				}

			}

		}

		remove_filter('sublanguage_postmeta_override', '__return_true');


		return $query;
	}



}

new Karma_Cluster_Multilanguage;

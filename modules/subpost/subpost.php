<?php


Class Karma_Subposts extends Karma_Multimedia {

	/**
	 * Get medias
	 *
	 * @override Karma_Multimedia::get_medias
	 */
	public function get_medias($post_id, $meta_key, $columns) {
		global $wpdb;


		foreach ($columns as $column) {



		}


	}

	/**
	 * Save medias
	 *
	 * @override Karma_Multimedia::save_medias
	 */
	public function save_medias($post_id, $meta_key, $medias_obj) {

		$medias_obj = apply_filters('karma_multimedia_save_medias', $medias_obj, $post_id, $meta_key, $medias);

		update_post_meta($post_id, $meta_key, $medias_obj);

	}




}

new Karma_Subposts();

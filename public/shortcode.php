<?php

/**
 *	Class Shortcode
 */
class Karma_Shortcode {

	/**
	 *	Constructor
	 */
	public function __construct() {

		add_action('init', array($this, 'init'));

	}

	/**
	 *
	 */
	public function init() {

		add_shortcode('newsletter', array($this, 'print_newsletter_form'));
		add_shortcode('bio', array($this, 'print_bios'));

	}

	/**
	 * @shortcode 'bio'
	 */
	public function print_bios($attr) {
		global $wpdb;

		ob_start();

		$bio_ids = array();

		if (isset($attr['department'])) {

			$term = get_term_by('slug', $attr['department'], 'department');

			if ($term) {

				$bio_ids = $wpdb->get_col($wpdb->prepare(
					"SELECT p.ID FROM $wpdb->posts AS p
					JOIN $wpdb->postmeta AS pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = 'lastname')
					JOIN $wpdb->term_relationships AS tr ON (p.ID = tr.object_id)
					JOIN $wpdb->term_taxonomy AS tt ON (tt.term_taxonomy_id = tr.term_taxonomy_id)
					WHERE p.post_type = 'bio' AND p.post_status = 'publish' AND tt.taxonomy = 'department' AND tt.term_id = %d
					GROUP BY p.ID
					ORDER BY pm2.meta_value ASC",
					$term->term_id
				));

				if (empty($attr['title'])) {

					$attr['title'] = $term->name;

				}

			}

		} else {

			$bio_ids = $wpdb->get_col(
				"SELECT p.ID FROM $wpdb->posts AS p
				JOIN $wpdb->postmeta AS pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = 'lastname')
				WHERE p.post_type = 'bio' AND p.post_status = 'publish'
				ORDER BY pm2.meta_value ASC"
			);

		}

		include get_template_directory() . '/public/include/bios.php';

		return ob_get_clean();

	}

	/**
	 * @shortcode 'newsletter'
	 */
	public function print_newsletter_form($attr) {

		ob_start();

		include get_template_directory() . '/public/include/newsletter-form.php';

		return ob_get_clean();

	}

}

new Karma_Shortcode;

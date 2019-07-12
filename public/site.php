<?php

/**
 *	Class Front-end
 */
class Karma_Site extends Karma {

	// public $saison_start_month = '08';
	// public $saison_end_month = '08';

	/**
	 *	Constructor
	 */
	public function __construct() {

		parent::__construct();

		add_action('wp_enqueue_scripts', array($this, 'scripts_styles'), 99);

		add_action('init', array($this, 'init'));

	}

	/**
	 *
	 */
	public function init() {

// 		add_action('karma_home_slideshow', array($this, 'print_home_slideshow'));
// 		add_action('karma_home_grid', array($this, 'print_home_grid'));
// 		add_action('karma_projects_grid', array($this, 'print_projects_grid'));
// 		add_action('karma_project', array($this, 'print_project'));
// 		add_action('karma_footer', array($this, 'print_footer'));
// 		add_action('karma_menu', array($this, 'print_menu'));
// 		add_action('karma_index', array($this, 'print_index'));
// 		add_action('karma_page', array($this, 'print_page'));
// 		add_shortcode('team', array($this, 'print_team'));
// 		add_action('sublanguage_custom_switch', array($this, 'print_language_switch'), 10, 2);

		// add_action('karma_header', array($this, 'print_header'));
		// add_action('karma_grid', array($this, 'print_grid'));
		// add_action('karma_single', array($this, 'print_single'));
		// add_action('karma_agenda', array($this, 'print_agenda'));
		// add_action('karma_event', array($this, 'print_event'));
		// add_action('karma_page', array($this, 'print_page'));
		// add_action('wp_footer', array($this, 'print_footer'));
		// add_action('karma_intro', array($this, 'print_intro'));

		// add_shortcode('newsletter', array($this, 'print_newsletter_form'));
		// add_shortcode('bio', array($this, 'print_bios'));


		// agenda archives route
		// add_action('parse_request', array($this, 'parse_request'));
		// add_filter('template_include', array($this, 'template_include'));

		// fix menu custom link
		// add_filter('nav_menu_item_args', array($this, 'nav_menu_item_args'), 10, 3);



	}

	/**
	 *	Print theme styles and scripts
	 */
	public function scripts_styles() {

		wp_enqueue_style('stylesheet', get_stylesheet_uri(), array(), $this->version);
		wp_register_script('tinyAnimate', get_template_directory_uri() . '/js/utils/TinyAnimate.js', array(), $this->version, true);
		wp_register_script('swipe', get_template_directory_uri() . '/js/utils/swipe.js', array(), $this->version, true);
		wp_register_script('media-player', get_template_directory_uri() . '/js/utils/media-player.js', array('tinyAnimate'), $this->version, true);
		wp_register_script('build', get_template_directory_uri() . '/js/utils/build.js', array(), $this->version, true);
		wp_register_script('ajax', get_template_directory_uri() . '/js/utils/ajax.js', array(), $this->version, true);
		wp_register_script('marquee', get_template_directory_uri() . '/js/utils/marquee.js', array(), $this->version, true);
		wp_register_script('popup', get_template_directory_uri() . '/js/utils/popup.js', array('tinyAnimate'), $this->version, true);
		wp_register_script('calendar', get_template_directory_uri() . '/js/utils/calendar.js', array(), $this->version, true);


// 		wp_enqueue_script('grid-system', get_template_directory_uri() . '/js/utils/grid-system.js', array('tinyAnimate'), $this->version, true);
		wp_register_script('sticky', get_template_directory_uri() . '/js/utils/sticky.js', array(), $this->version, true);
// 		wp_enqueue_script('slideshow', get_template_directory_uri() . '/js/slideshow.js', array('media-player', 'swipe'), $this->version, true);
// 		wp_enqueue_script('grid', get_template_directory_uri() . '/js/grid.js', array('grid-system'), $this->version, true);
// 		wp_enqueue_script('projects-grid', get_template_directory_uri() . '/js/projects.js', array('grid'), $this->version, true);
// 		wp_enqueue_script('project', get_template_directory_uri() . '/js/project.js', array('grid'), $this->version, true);
// 		wp_enqueue_script('image', get_template_directory_uri() . '/js/image.js', array('tinyAnimate'), $this->version, true);
		wp_register_script('grid-slideshow', get_template_directory_uri() . '/js/grid-slideshow.js', array('media-player', 'swipe', 'build'), $this->version, true);

		// wp_enqueue_script('home', get_template_directory_uri() . '/js/home.js', array('grid-slideshow'), $this->version, true);
		// wp_enqueue_script('header', get_template_directory_uri() . '/js/header.js', array('popup', 'sticky', 'marquee'), $this->version, true);
		// wp_enqueue_script('single', get_template_directory_uri() . '/js/single.js', array('grid-slideshow'), $this->version, true);
		// wp_enqueue_script('bios', get_template_directory_uri() . '/js/bios.js', array('popup'), $this->version, true);
		// wp_enqueue_script('agenda', get_template_directory_uri() . '/js/agenda.js', array('popup', 'ajax', 'build', 'calendar', 'grid-slideshow'), $this->version, true);
		// wp_enqueue_script('intro', get_template_directory_uri() . '/js/intro.js', array('media-player'), $this->version, true);

		// wp_register_script('gmap-api', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCD-L65XebK9SUZUtCGJbpf7IBSMYSBbn8', array(), false, true);
		// wp_register_script('gmap', get_template_directory_uri() . '/js/gmap.js', array('gmap-api'), $this->version, true);


		wp_enqueue_script('cookies', get_template_directory_uri() . '/js/utils/cookies.js', array('media-player'), $this->version, false);


	}

	/**
	 * @hook 'parse_query'
	 */
	public function parse_query($wp_query) {

	}

	/**
	 * @hook 'parse_request'
	 */
	public function parse_request($wp) {

		// if (isset($wp->query_vars['pagename'], $wp->query_vars['archives'])) {
		//
		// 	if ($wp->query_vars['pagename'] === 'agenda') {
		//
		// 		$this->archive_query = $wp->query_vars['archives'];
		//
		// 	} else {
		//
		// 		unset($wp->query_vars['pagename']);
		// 		unset($wp->query_vars['archives']);
		// 		$wp->query_vars['error'] = '404';
		//
		// 	}
		//
		// }

	}

	/**
	 * Get link to fetch json
	 */
	// public function get_event_json_link($event_id) {
	// 	global $wp_object_cache, $sublanguage;
	//
	// 	if (isset($wp_object_cache->cache_dir) && is_file(WP_CONTENT_DIR . '/' . $wp_object_cache->cache_dir . '/' . $wp_object_cache->object_dir . '/event/' . $event_id . '/data.json')) {
	//
	// 		if (isset($sublanguage) && $sublanguage->is_sub()) {
	//
	// 			$language .= '/' . $sublanguage->get_language()->post_name;
	//
	// 		} else {
	//
	// 			$language = '';
	//
	// 		}
	//
	// 		return WP_CONTENT_URL . '/' . $wp_object_cache->cache_dir . '/' . $wp_object_cache->object_dir . '/event/' . $event_id . $language . '/data.json';
	//
	// 	} else {
	//
	// 		return add_query_arg(array(
	// 			'action' => 'get_event',
	// 			'event_id' => $event_id
	// 		), admin_url('admin-ajax.php'));
	//
	// 	}
	//
	// }


	/**
	 * @filter 'template_include'
	 */
	public function template_include( $template ) {
		global $wp_query;

		// if (isset($wp_query->query_vars['archives']) && file_exists(STYLESHEETPATH . '/page-agenda.php')) {
		//
		// 	return STYLESHEETPATH . '/page-agenda.php';
		//
		// }

		return $template;
	}


	/**
	 * @hook 'karma_header'
	 */
	public function print_header() {

		$page_collection = $this->get_pages();

		include get_template_directory() . '/public/include/header.php';

	}

	/**
	 * @hook 'karma_grid'
	 */
	public function print_grid() {
		global $wpdb;

		$sticky_ids = $wpdb->get_col(
			"SELECT p.ID FROM $wpdb->posts AS p
			JOIN $wpdb->postmeta AS pm ON (p.ID = pm.post_id)
			WHERE pm.meta_key = 'sticky' AND pm.meta_value = 1 AND p.post_status = 'publish'
			ORDER BY p.menu_order ASC"
		);

		$event_ids = $wpdb->get_col(
			"SELECT p.ID FROM $wpdb->posts AS p
			JOIN $wpdb->postmeta AS pm ON (p.ID = pm.post_id AND pm.meta_key = 'end_date')
			WHERE p.post_type = 'event' AND p.post_status = 'publish'
			ORDER BY pm.meta_value DESC
			LIMIT 10"
		);

		$footer_ids = $wpdb->get_col($wpdb->prepare(
			"SELECT p.ID FROM $wpdb->posts AS p
			JOIN $wpdb->postmeta AS pm ON (p.ID = pm.post_id)
			WHERE (pm.meta_key = %s OR pm.meta_key = %s OR pm.meta_key = %s) AND pm.meta_value = 1 AND p.post_status = %s
			ORDER BY p.menu_order ASC
			LIMIT 3",
			'footer1',
			'footer2',
			'footer3',
			'publish'
		));



		include get_template_directory() . '/public/include/grid.php';

	}


	/**
	 * @hook 'karma_single'
	 */
	public function print_single() {
		global $wpdb;

		$post = get_queried_object();

		$image_ids = get_post_meta($post->ID, 'images');

		$prev_post_id = $wpdb->get_var($wpdb->prepare(
			"SELECT p.ID FROM $wpdb->posts AS p
			WHERE p.post_type = %s AND p.post_status = %s AND p.post_date < %s
			ORDER BY p.post_date DESC
			LIMIT 1",
			'post',
			'publish',
			$post->post_date
		));

		$next_post_id = $wpdb->get_var($wpdb->prepare(
			"SELECT p.ID FROM $wpdb->posts AS p
			WHERE p.post_type = %s AND p.post_status = %s AND p.post_date > %s
			ORDER BY p.post_date ASC
			LIMIT 1",
			'post',
			'publish',
			$post->post_date
		));

		include get_template_directory() . '/public/include/single.php';

	}

	/**
	 * @hook 'karma_single'
	 */
	public function print_agenda() {
		global $wp_query;

		if (isset($wp_query->query_vars['archives']) && $wp_query->query_vars['archives']) {

			$current_year = intval($wp_query->query_vars['archives']);

		} else {

			$current_year = intval(date('Y'));

			if (date('m') < $this->saison_start_month) {

				$current_year = $current_year - 1;

			}

		}

		$this->print_agenda_header($current_year);
		$this->print_agenda_body($current_year);


		// global $wpdb, $wp_query;
		//
		// $saison_start_month = '08';
		// $saison_end_month = '08';
		//
		// if (isset($wp_query->query_vars['archives']) && $wp_query->query_vars['archives']) {
		//
		// 	$current_year = intval($wp_query->query_vars['archives']);
		//
		// } else {
		//
		// 	$current_year = intval(date('Y'));
		//
		// 	if (date('m') < $saison_start_month) {
		//
		// 		$current_year = $current_year - 1;
		//
		// 	}
		//
		// }
		//
		// $saison_start = $current_year . '-' . $saison_start_month;
		// $saison_end = ($current_year + 1) . '-' . $saison_end_month;
		//
		// $event_ids = $wpdb->get_col($wpdb->prepare(
		// 	"SELECT p.ID FROM $wpdb->posts AS p
		// 	JOIN $wpdb->postmeta AS pm1 ON (p.ID = pm1.post_id AND pm1.meta_key = 'start_date')
		// 	JOIN $wpdb->postmeta AS pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = 'end_date')
		// 	WHERE p.post_type = 'event' AND p.post_status = 'publish' AND pm2.meta_value > %s AND pm1.meta_value < %s
		// 	ORDER BY pm2.meta_value DESC",
		// 	$saison_start,
		// 	$saison_end
		// ));
		//
		// include get_template_directory() . '/public/include/agenda-header.php';
		// include get_template_directory() . '/public/include/agenda-body.php';
	}

	/**
	 * print agenda header
	 */
	public function print_agenda_header($current_year) {
		global $wpdb;

		$min_year = 2011;
		$max_year = intval(Karma_Date::parse($wpdb->get_var(
			"SELECT pm2.meta_value FROM $wpdb->posts AS p
			JOIN $wpdb->postmeta AS pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = 'end_date')
			WHERE p.post_type = 'event' AND p.post_status = 'publish'
			ORDER BY pm2.meta_value DESC
			LIMIT 1"
		), 'yyyy-mm-dd hh:ii:ss', 'yyyy'));

		include get_template_directory() . '/public/include/agenda-header.php';

	}

	/**
	 * print agenda body
	 */
	public function print_agenda_body($current_year) {
		global $wpdb;

		$saison_start = $current_year . '-' . $this->saison_start_month;
		$saison_end = ($current_year + 1) . '-' . $this->saison_end_month;

		$event_ids = $wpdb->get_col($wpdb->prepare(
			"SELECT p.ID FROM $wpdb->posts AS p
			JOIN $wpdb->postmeta AS pm1 ON (p.ID = pm1.post_id AND pm1.meta_key = 'start_date')
			JOIN $wpdb->postmeta AS pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = 'end_date')
			WHERE p.post_type = 'event' AND p.post_status = 'publish' AND pm2.meta_value > %s AND pm1.meta_value < %s
			ORDER BY pm2.meta_value DESC",
			$saison_start,
			$saison_end
		));

		include get_template_directory() . '/public/include/agenda-body.php';
	}


	/**
	 * @hook 'karma_event'
	 */
	public function print_event() {
		global $wpdb, $wp_query;

		$event = get_queried_object();
		$event_id = $event->ID;
		$project_id = $event->post_parent;
		$project = get_post($project_id);
		$start_date = get_post_meta($event->ID, 'start_date', true);
		$end_date = get_post_meta($event->ID, 'end_date', true);
		$current_year = intval(substr($start_date, 0, 4));

		if ($start_date < $current_year . '-' . $this->saison_start_month) {

			$current_year--;

		}

		$post_content = $event->post_content;

		if (!$post_content && $project) {

			$post_content = $project->post_content;

		}

		$auteur = get_post_meta($event->ID, 'auteur', true);

		if (!$auteur && $project_id) {

			$auteur = get_post_meta($project_id, 'auteur', true);

		}

		$description = get_post_meta($event->ID, 'description', true);

		if (!$description && $project_id) {

			$description = get_post_meta($project_id, 'description', true);

		}

		$image_ids = get_post_meta($event->ID, 'images');

		if (!$image_ids && $project_id) {

			$image_ids = get_post_meta($project_id, 'images');

		}

		$place = get_post_meta($event->ID, 'place', true);
		$city = get_post_meta($event->ID, 'city', true);
		$country = get_post_meta($event->ID, 'country', true);

		if ($city && $country) {

			$city .= ' ('.$country.')';

		}

		$prev_id = $wpdb->get_var($wpdb->prepare(
			"SELECT p.ID FROM $wpdb->posts AS p
			JOIN $wpdb->postmeta AS pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = 'end_date')
			WHERE p.post_type = 'event' AND p.post_status = 'publish' AND pm2.meta_value < %s
			ORDER BY pm2.meta_value DESC
			LIMIT 1",
			$end_date
		));

		$next_id = $wpdb->get_var($wpdb->prepare(
			"SELECT p.ID FROM $wpdb->posts AS p
			JOIN $wpdb->postmeta AS pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = 'end_date')
			WHERE p.post_type = 'event' AND p.post_status = 'publish' AND pm2.meta_value > %s
			ORDER BY pm2.meta_value ASC
			LIMIT 1",
			$end_date
		));

		$this->print_agenda_header($current_year);

		include get_template_directory() . '/public/include/event.php';

	}


	/**
	 * @hook 'karma_page'
	 */
	public function print_page() {
		global $wpdb;

		$page = get_queried_object();

		include get_template_directory() . '/public/include/page.php';

	}

	/**
	 * @hook 'wp_footer'
	 */
	public function print_footer() {
		global $wpdb;

		$footer_id = $wpdb->get_var(
			"SELECT ID FROM $wpdb->posts WHERE post_name = 'page-footer'"
		);

		include get_template_directory() . '/public/include/footer.php';

	}


	/**
	 * @hook 'karma_intro'
	 */
	public function print_intro() {
		global $wpdb;

		$intro_id = $wpdb->get_var(
			"SELECT ID FROM $wpdb->posts WHERE post_name = 'page-intro'"
		);

		if ($intro_id) {

			$image_ids = get_post_meta($intro_id, 'images');

			if ($image_ids) {

				include get_template_directory() . '/public/include/intro.php';

			}

		}

	}

	/**
	 * @shortcode 'bio'
	 */
	// public function print_bios($args) {
	// 	global $wpdb;
	//
	// 	ob_start();
	//
	// 	$bio_ids = $wpdb->get_col(
	// 		"SELECT p.ID FROM $wpdb->posts AS p
	// 		JOIN $wpdb->postmeta AS pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = 'lastname')
	// 		WHERE p.post_type = 'bio' AND p.post_status = 'publish'
	// 		ORDER BY pm2.meta_value ASC"
	// 	);
	//
	// 	include get_template_directory() . '/public/include/bios.php';
	//
	// 	return ob_get_clean();
	//
	// }
	//
	// /**
	//  * @shortcode 'newsletter'
	//  */
	// public function print_newsletter_form($args) {
	//
	// 	ob_start();
	//
	// 	include get_template_directory() . '/public/include/newsletter-form.php';
	//
	// 	return ob_get_clean();
	//
	// }


	/**
	 * @hook 'karma_projects_grid'
	 */
// 	public function print_projects_grid() {
//
// 		$query = New WP_Query(array(
// 			'post_type' => 'project',
// 			'status' => 'publish',
// 			'posts_per_page' => -1,
// 			'orderby' => 'menu_order',
// 			'order' => 'asc',
// 			'meta_query' => array(
// 				array(
// 					'key'     => 'locations',
// 					'value'   => 'home'
// 				)
// 			)
// 		));
//
// 		$categories = get_terms(array(
// 			'taxonomy' => 'category',
// 			'hide_empty' => false,
// 			'exclude' => array(1)
// 		));
//
// 		if ($query->posts) {
//
// 			update_post_thumbnail_cache($query);
//
// 			include get_template_directory() . '/public/include/projects-grid.php';
//
// 		}
//
// 	}
//
//
//
//
// 	/**
// 	 * get more projects
// 	 */
// 	public function get_mores($post, $more_num = 3) {
//
// 		$term_ids = array(); // $this->map_terms($post->ID, 'term_id');
//
// 		$terms = get_the_terms($post->ID, 'category');
//
// 		if ($terms && !is_wp_error($terms)) {
//
// 			foreach ($terms as $term) {
//
// 				$term_ids[] = $term->term_id;
//
// 			}
//
// 		}
//
// 		$more_posts = array();
// 		$exclude_post_ids = array($post->ID);
//
// 		if ($term_ids) {
//
// 			$more_query = New WP_Query(array(
// 				'post_type' => 'project',
// 				'status' => 'publish',
// 				'post__not_in' => $exclude_post_ids,
// 				'posts_per_page' => $more_num,
// 				'tax_query' => array(
// 					array(
// 						'taxonomy' => 'category',
// 						'terms'    => $term_ids,
// 					),
// 				),
// 				'orderby' => 'menu_order',
// 				'meta_query' => array(
// 					array(
// 						'key'     => 'locations',
// 						'value'   => 'home'
// 					)
// 				)
// 			));
//
// 			$more_posts = $more_query->posts;
//
// 		}
//
// 		if (count($more_posts) < $more_num) {
//
// 			$more_post_ids = array();
//
// 			foreach ($more_posts as $more_post) {
//
// 				$exclude_post_ids[] = $more_post->ID;
//
// 			}
//
// 			$more_query = New WP_Query(array(
// 				'post_type' => 'project',
// 				'status' => 'publish',
// 				'post__not_in' => $exclude_post_ids,
// 				'posts_per_page' => $more_num - count($more_posts),
// 				'orderby' => 'menu_order',
// 				'meta_query' => array(
// 					array(
// 						'key'     => 'locations',
// 						'value'   => 'home'
// 					)
// 				)
// 			));
//
// 			$more_posts = array_merge($more_posts, $more_query->posts);
//
// 		}
//
// 		return $more_posts;
// 	}
//
//
// 	/**
// 	 * @hook 'karma_project'
// 	 */
// 	public function print_project($post) {
//
// 		$image_ids = array(); //get_post_meta($post->ID, 'images');
// 		$medias = get_post_meta($post->ID, 'medias');
//
// 		foreach ($medias as $media) {
//
// 			if (isset($media->type) && $media->type === 'image' && isset($media->id) && $media->id) {
//
// 				$image_ids[] = $media->id;
//
// 			}
//
// 			if (isset($media->type) && $media->type === 'slideshow' && isset($media->ids) && $media->ids) {
//
// 				$image_ids = array_merge($image_ids, $media->ids);
//
// 			}
//
// 		}
//
// 		$thumb_id = get_post_thumbnail_id($post->ID);
//
// 		$image_ids[] = $thumb_id;
//
// 		$image_ids = array_unique($image_ids);
//
// 		$this->cache_images($image_ids);
//
// 		// text
// 		$post_contents = explode('<!--nextpage-->', $post->post_content);
// 		$post_contents = array_map('trim', $post_contents);
// 		$post_contents = array_map('nl2br', $post_contents);
// 		$page_index = 0;
//
// 		$terms = get_the_terms($post->ID, 'category');
//
// 		$more_posts = $this->get_mores($post, 3);
//
// 		include get_template_directory() . '/public/include/project-header.php';
// 		include get_template_directory() . '/public/include/project-grid.php';
// 		include get_template_directory() . '/public/include/project-more.php';
//
// 	}
//
//
// 	/**
// 	 * @hook 'karma_index'
// 	 */
// 	public function print_index() {
//
// 		$query = New WP_Query(array(
// 			'post_type' => 'project',
// 			'status' => 'publish',
// 			'posts_per_page' => -1,
// 			'orderby'  => array(
// 				'meta_value_num' => 'DESC',
// 				'title' => 'ASC'
// 			),
// 			'meta_key' => 'year',
// 			'meta_query' => array(
// 					array(
// 						'key'     => 'locations',
// 						'value'   => 'index'
// 					)
// 				)
// 		));
//
// 		if ($query->posts) {
//
// 			update_post_thumbnail_cache($query);
//
// 			include get_template_directory() . '/public/include/index-projects.php';
//
// 		}
//
// 	}
//
// 	/**
// 	 * @hook 'karma_page'
// 	 */
// 	public function print_page($page) {
//
// 		include get_template_directory() . '/public/include/page-default.php';
//
// 	}
//
//
//
//
//
//
// 	/**
// 	 * @hook 'karma_footer'
// 	 */
// 	public function print_footer() {
//
// 		include get_template_directory() . '/public/include/footer.php';
//
// 	}
//
//
// 	/**
// 	 * @callback map
// 	 */
// 	public function get_term_slug($term) {
//
// 		return $term->slug;
//
// 	}
//
// 	/**
// 	 * @callback map
// 	 */
// 	public function get_term_name($term) {
//
// 		return $term->name;
//
// 	}
//
// 	/**
// 	 * @helper
// 	 */
// 	public function map_terms($post_id, $field = 'name') {
//
// 		$terms = get_the_terms($post_id, 'category');
//
// 		$values = array();
//
// 		if ($terms && !is_wp_error($terms)) {
//
// 			foreach ($terms as $term) {
//
// 				$values[] = $term->$field;
//
// 			}
//
// 		}
//
// 		return $values;
//
// 	}



	/**
	 * get pages
	 */
	// public function get_pages() {
	// 	global $wpdb;
	// 	static $page_collection;
	//
	// 	if (!isset($page_collection)) {
	//
	// 		$pages = $wpdb->get_results($wpdb->prepare(
	// 			"SELECT p.ID, pm.meta_value AS in_menu, p.menu_order, p.post_parent FROM $wpdb->posts AS p
	// 			JOIN  $wpdb->postmeta AS pm ON (p.ID = pm.post_id AND pm.meta_key = %s)
	// 			WHERE p.post_type = %s AND p.post_status = %s
	// 			ORDER BY p.menu_order ASC",
	// 			'in_menu',
	// 			'page',
	// 			'publish'
	// 		));
	//
	// 		require_once(get_template_directory() . '/admin/collection.php');
	//
	// 		$page_collection = new Karma_Collection($pages);
	//
	// 	}
	//
	// 	return $page_collection;
	// }

// 	/**
// 	 * @shortcode 'karma_team'
// 	 */
// 	public function print_team($args) {
//
// 		$team_query = New WP_Query(array(
// 			'post_type' => 'team',
// 			'status' => 'publish',
// 			'posts_per_page' => -1,
// 			'orderby'  => 'title',
// 			'order' => 'asc'
// 		));
//
//
// 		ob_start();
//
// 		include get_template_directory() . '/public/include/team.php';
//
// 		return ob_get_clean();
//
// 	}




	/**
	 * @filter 'nav_menu_item_args'
	 */
	public function nav_menu_item_args($args, $item, $depth) {

		if ($item->type === 'custom' && substr($item->url, 0, 1) === '/') {
			//var_dump($_SERVER);
			$item->url = home_url() . $item->url;

		}

		return $args;
	}



	/**
	 * @shortcode 'sublanguage_custom_switch'
	 */
	public function print_language_switch($languages, $sublanguage) {

		include get_template_directory() . '/public/include/language-switch.php';

	}

}

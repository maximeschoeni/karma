<?php


Class Karma_Field {

	var $version = '1';

	/**
	 *	constructor
	 */
	public function __construct() {

		require_once dirname(__FILE__) . '/multilanguage.php';

		if (is_admin()) {

			add_action('karma_field', array($this, 'print_field'), 10, 4);
			add_action('karma_field_group', array($this, 'print_field_group'), 10, 3);
			add_action('init', array($this, 'init'));
			add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
			add_action('wp_ajax_karma_multimedia_get_image_src', array($this, 'ajax_get_image_src'));
			// add_action('wp_ajax_karma_field_query_posts', array($this, 'ajax_query_posts'));

		}

	}

	/**
	 * @hook init
	 */
	public function init() {

		add_action('save_post', array($this, 'save'), 10, 3);

	}

	/**
	 * Hook for 'admin_enqueue_scripts'
	 */
	public function enqueue_styles() {

		wp_enqueue_style('date-field-styles', $this->url . '/css/date-field.css');
		wp_enqueue_style('media-field-styles', $this->url . '/css/media-field.css');
		wp_enqueue_style('multimedia-styles', $this->url . '/css/multimedia.css');

		wp_register_script('build', $this->url . '/js/build-v2.js', array(), $this->version, true);
		wp_register_script('calendar', $this->url . '/js/calendar.js', array(), $this->version, true);
		wp_register_script('ajax', $this->url . '/js/ajax-v2.js', array(), $this->version, true);
		wp_register_script('sortable', $this->url . '/js/sortable.js', array(), $this->version, true);

		wp_enqueue_script('media-field', $this->url . '/js/media.js', array('ajax', 'build'), $this->version, true);
		wp_localize_script('media-field', 'KarmaFieldMedia', array(
			'ajax_url' => admin_url('admin-ajax.php')
		));

		// wp_enqueue_script('gallery-uploader', $this->url . '/js/media.js', array('ajax'), $this->version, true);
		// wp_enqueue_script('field-media', $this->url . '/js/media.js', array('ajax'), $this->version, true);


		wp_enqueue_script('date-field', $this->url . '/js/date-field-v2.js', array('media-field', 'build', 'calendar'), $this->version, true);

		wp_enqueue_script('multimedia-field', $this->url . '/js/multimedia.js', array('build', 'sortable', 'media-field'), $this->version, true);

	}

	/**
	 * @ajax karma_multimedia_get_image_src
	 */
	public function ajax_get_image_src() {

		if (isset($_GET['id'])) {

			$id = intval($_GET['id']);
			// $post = get_post($id);
			$src_data = wp_get_attachment_image_src($id, 'thumbnail', true);
			$filename = basename(get_attached_file($id));

			echo json_encode(array(
				'filename' => $filename,
				'url' => $src_data[0],
				'width' => $src_data[1],
				'height' => $src_data[2]
			));

		} else {

			trigger_error('id not set');

		}

		exit;

	}

	/**
	 * @ajax karma_field_query_posts
	 */
	// public function ajax_query_posts() {
	// 	global $wpdb;
	//
	// 	$post_type = (isset($_GET['post_type'])) ? $_GET['post_type'] : 'post';
	// 	$post_status = (isset($_GET['post_status'])) ? $_GET['post_status'] : 'publish';
	// 	$orderby = (isset($_GET['orderby'])) ? $_GET['orderby'] : 'post_title';
	// 	$order = (isset($_GET['order'])) ? $_GET['order'] : 'ASC';
	//
	// 	$args = apply_filters('karma_field_query_posts', array(
	// 		'post_type' => $post_type,
	// 		'post_status' => $post_status,
	// 		'orderby' => $orderby,
	// 		'order' => $order,
	// 		'posts_per_page' => 1
	// 	), $_GET);
	//
	// 	$query = new WP_Query($args);
	//
	// 	$posts = array();
	//
	// 	if ($query->have_posts()) {
	//
	// 		foreach ($query->posts as $post) {
	//
	// 			$posts[] = array(
	// 				'key' => $post->ID,
	// 				'name' => $post->post_title
	// 			);
	//
	// 		}
	//
	// 	}
	//
	// 	echo json_encode(array(
	// 		'posts' => $posts
	// 	));
	//
	// 	exit;
	//
	// }


	/**
	 * Save meta boxes
	 *
	 * @hook 'save_post'
	 */
	public function save($post_id, $post, $update) {

		if (current_user_can('edit_post', $post_id) && (!defined( 'DOING_AUTOSAVE' ) || !DOING_AUTOSAVE )) {

			$action = "karma_field-action";
			$nonce = "karma_field-nonce";

			if (isset($_REQUEST['karma-fields'], $_REQUEST[$nonce]) && wp_verify_nonce($_POST[$nonce], $action)) {

				$meta_keys = $_REQUEST['karma-fields'];

				foreach ($meta_keys as $meta_key) {

					$field = $_REQUEST['karma_field_type'][$meta_key];
					$datatype = isset($_REQUEST['karma_field_datatype'][$meta_key]) ? $_REQUEST['karma_field_datatype'][$meta_key] : 'string';
					$name = "karma_field-$meta_key";

					$value = isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';

					if ($datatype === 'json' && $value) {

						$value = stripslashes($value);
						$value = json_decode($value);

					}

					$value = apply_filters('karma_field_save', $value, $post, $meta_key, $field);

					if (!is_null($value)) {

						update_post_meta($post_id, $meta_key, $value);

					}


					// if ($multiple && is_array($value)) {
					//
					// 	$values = get_post_meta($post_id, $meta_key, false);
					//
					// 	$new_values = isset($_REQUEST[$name]) && is_array($_REQUEST[$name]) ? $_REQUEST[$name] : array();
					//
					// 	foreach (array_diff($new_values, $values) as $value) {
					//
					// 		add_post_meta($post_id, $meta_key, $value);
					//
					// 	}
					//
					// 	foreach (array_diff($values, $new_values) as $value) {
					//
					// 		delete_post_meta($post_id, $meta_key, $value);
					//
					// 	}
					//
					// }


					// if ($type === 'checkbox') {
					//
					// 	update_post_meta($post_id, $meta_key, isset($_REQUEST[$name]) ? '1' : '');
					//
					// } else if ($type === 'checkboxes') {
					//
					// 	$values = get_post_meta($post_id, $meta_key, false);
					//
					// 	$new_values = isset($_REQUEST[$name]) && is_array($_REQUEST[$name]) ? $_REQUEST[$name] : array();
					//
					// 	foreach (array_diff($new_values, $values) as $value) {
					//
					// 		add_post_meta($post_id, $meta_key, $value);
					//
					// 	}
					//
					// 	foreach (array_diff($values, $new_values) as $value) {
					//
					// 		delete_post_meta($post_id, $meta_key, $value);
					//
					// 	}
					//
					// } else if (isset($_REQUEST[$name])) {
					//
					// 	$value = apply_filters('karma_field_save', $_REQUEST[$name], $post_id, $meta_key, $type);
					//
					// 	update_post_meta($post_id, $meta_key, $value);
					//
					// }

				}

			}

		}

	}

	/**
	 * @hook 'karma_field'
	 */
	public function print_field($post, $args = array()) {
		static $index = 1;

		
		// if (isset($args['meta_key'])) {
		//
		// 	$meta_key = $args['meta_key'];
		//
		// } else {
		//
		// 	$meta_key = 'custom_meta_field_'.$index;
		// 	$index++;
		//
		// }

		$args = apply_filters('karma_field_args', $args, $post);

		$meta_key = isset($args['meta_key']) ? $args['meta_key'] : 'custom_meta_field_'.($index++);
		$field = isset($args['field']) ? $args['field'] : 'text';

		include_once dirname(__FILE__) . '/includes/field-nonce.php';
		include dirname(__FILE__) . '/includes/field-hidden.php';

		$value = get_post_meta($post->ID, $meta_key, true);
		$value = apply_filters('karma_field_input', $value, $post, $meta_key, $field, $args);



		include dirname(__FILE__) . "/includes/field-$field.php";

	}

	/**
	 * @hook 'karma_field_group'
	 */
	public function print_field_group($post, $args) {

		$fields = $args['fields'];
		$display = $args['display'];

		include dirname(__FILE__) . "/includes/fields-$display.php";

	}

}

// global $karma_field;
// $karma_field = new Karma_Field();

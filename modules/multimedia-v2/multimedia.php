<?php


Class Karma_Multimedia {

	var $registered_fields = array();
	var $version = '2';

	/**
	 *	constructor
	 */
	public function __construct() {

		if (is_admin()) {

			add_action('karma_multimedia_field', array($this, 'print_field'), 10, 3);
			add_action('karma_multimedia_register', array($this, 'register'), 10, 5);

			// add_action('wp_ajax_karma_multimedia_get_image', array($this, 'ajax_get_image'));
			add_action('wp_ajax_karma_multimedia_get_image_src', array($this, 'ajax_get_image_src'));

			add_action('init', array($this, 'init'));

		}

	}

	/**
	 * @hook init
	 */
	public function init() {

		add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));

		add_action('save_post', array($this, 'save'), 10, 3);


	}


	/**
	 * Enqueue styles
	 *
	 * Hook for 'admin_enqueue_scripts'
	 */
	function enqueue_styles($hook) {
		wp_enqueue_style('date-field-styles', get_template_directory_uri().'/modules/date-field/date-field.css');
		wp_enqueue_script('date-field', get_template_directory_uri() . '/modules/date-field/date-field.js', array('build', 'calendar'), $this->version, true);

		wp_enqueue_style('media-box-styles', get_template_directory_uri() . '/modules/multimedia-v2/media-box-styles.css', array('date-field-styles'), $this->version);
		wp_enqueue_script('karma-image-uploader', get_template_directory_uri() . '/modules/multimedia-v2/js/image-uploader.js', array('build'), $this->version, true);
		wp_enqueue_script('karma-gallery-uploader', get_template_directory_uri() . '/modules/multimedia-v2/js/gallery-uploader.js', array('build'), $this->version, true);
		wp_enqueue_script('karma-multimedia', get_template_directory_uri() . '/modules/multimedia-v2/js/multimedia.js', array('sortable', 'build', 'karma-image-uploader', 'karma-gallery-uploader', 'collection', 'date-field'), $this->version, true);

		wp_localize_script('karma-multimedia', 'KarmaMultimedia', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'settings' => $this->get_settings()
			// 'attachments' => $this->get_attachments()
		));

	}

	/**
	 * Get medias
	 *
	 * @overridable
	 */
	public function get_medias($post_id, $meta_key, $columns) {

		$medias_obj = get_post_meta($post_id, $meta_key, true);

		if (!$medias_obj) {

			$medias_obj = array();

		}

		return apply_filters('karma_multimedia_prepare_medias', $medias_obj, $post_id, $meta_key, $columns);

	}

	/**
	 * Save medias
	 *
	 * @overridable
	 */
	public function save_medias($post_id, $meta_key, $medias_obj, $medias) {

		$medias_obj = apply_filters('karma_multimedia_save_medias', $medias_obj, $post_id, $meta_key, $medias);

		update_post_meta($post_id, $meta_key, $medias_obj);

	}


	/**
	 * Save meta boxes
	 *
	 * @hook 'save_post'
	 */
	public function save($post_id, $post, $update) {


		if (current_user_can('edit_post', $post_id) && (!defined( 'DOING_AUTOSAVE' ) || !DOING_AUTOSAVE )) {

			if (isset($_REQUEST['karma-multimedia'])) {

				foreach ($_REQUEST['karma-multimedia'] as $meta_key) {

					// $meta_key = $_REQUEST['karma-multimedia'];
					$name = "karma_mm-$meta_key";
					$action = "karma_mm-$meta_key-action";
					$nonce = "karma_mm-$meta_key-nonce";

					if (isset($_REQUEST[$name], $_REQUEST[$nonce]) && wp_verify_nonce($_POST[$nonce], $action)) {

						$medias = $_REQUEST[$name];
						$medias = stripslashes($medias);
						$medias_obj = json_decode($medias);

						if (isset($this->registered_fields[$meta_key])) {

							$columns = $this->registered_fields[$meta_key]['columns'];
							$save_data = $this->registered_fields[$meta_key]['save_data'];
							$medias_obj = call_user_func($save_data, $post_id, $meta_key, $medias_obj, $columns);

						} else {

							$this->save_medias($post_id, $meta_key, $medias_obj, $medias);

						}

					}

				}

			}

			// if (isset($_REQUEST['karma-mm-attachments'])) {
			//
			// 	$attachment_ids = array_unique(array_map('intval', $_REQUEST['karma-mm-attachments']));
			//
			// 	update_post_meta($post_id, '_mm-attachments', $attachment_ids);
			//
			// }

		}

	}

	/**
	 * @hook 'karma_multimedia_field'
	 */
	public function print_field($post_id, $meta_key, $columns = null) {


		$name = "karma_mm-$meta_key";
		$action = "karma_mm-$meta_key-action";
		$nonce = "karma_mm-$meta_key-nonce";

		wp_nonce_field($action, $nonce, false, true);

		if (isset($this->registered_fields[$meta_key])) {

			$columns = $this->registered_fields[$meta_key]['columns'];
			$get_data = $this->registered_fields[$meta_key]['get_data'];
			$medias_obj = call_user_func($get_data, $post_id, $meta_key, $columns);

		} else {

			$medias_obj = $this->get_medias($post_id, $meta_key, $columns);

		}

		$medias = json_encode($medias_obj);

		if (!$medias) {

			$medias = '[]';

		}

		include get_template_directory() . '/modules/multimedia-v2/includes/multimedia-input.php';

	}

	/**
	 * @hook 'karma_multimedia_field'
	 */
	public function register($key, $columns, $get_data, $save_data, $settings = array()) {

		$this->registered_fields[$key] = array(
			'key' => $key,
			'columns' => $columns,
			'get_data' => $get_data,
			'save_data' => $save_data,
			'settings' => $settings
		);

	}

	/**
	 * @hook 'karma_multimedia_field'
	 */
	public function get_settings() {

		$settings = array();

		foreach ($this->registered_fields as $key => $field) {

			$settings[$key] = $field['settings'];

		}

		return $settings;
	}

	// /**
	//  * @ajax karma_multimedia_get_image
	//  */
	// public function ajax_get_image() {
	//
	// 	$output = array();
	//
	// 	if (isset($_GET['id'])) {
	//
	// 		$id = intval($_GET['id']);
	// 		$src_data = wp_get_attachment_image_src($id, 'thumbnail', true);
	//
	// 		wp_redirect($src_data[0]);
	//
	// 	}
	//
	// 	exit;
	//
	// }

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

	// /**
	//  * get all attachment sources
	//  */
	// public function get_attachments() {
	// 	global $post, $wpdb;
	// 	// var_dump('asdfasfd', $hook);
	//
	// 	$output = array();
	//
	// 	if (isset($post) && $post->ID) {
	//
	// 		$attachment_ids = get_post_meta($post->ID, '_mm-attachments', true);
	//
	// 		if ($attachment_ids && is_array($attachment_ids)) {
	//
	// 			$attachment_ids_sql = implode(",", array_map('intval', $attachment_ids));
	//
	// 			$attachments = $wpdb->get_results(
	// 				"SELECT * FROM $wpdb->posts WHERE ID IN ($attachment_ids_sql)"
	// 			);
	//
	// 			update_post_caches($attachments, 'any', false, true);
	//
	// 			foreach ($attachments as $attachment) {
	//
	// 				$src_data = wp_get_attachment_image_src($attachment->ID, 'thumbnail', true);
	//
	// 				$output[$attachment->ID] = array(
	// 					'src' => $src_data[0],
	// 					'filename' => basename(get_attached_file($attachment->ID))
	// 				);
	//
	// 			}
	//
	// 		}
	//
	// 	}
	//
	// 	return $output;
	// }



}

if (is_admin()) new Karma_Multimedia();

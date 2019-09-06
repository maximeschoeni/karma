<?php


Class Karma_Multimedia {

	var $version = '0.1';

	/**
	 *	constructor
	 */
	public function __construct() {

		require_once get_template_directory() . '/modules/multimedia/class-multilanguage.php';

		if (is_admin()) {

			add_action('karma_multimedia_field', array($this, 'print_field'), 10, 4);

			add_action('wp_ajax_karma_multimedia_get_image', array($this, 'ajax_get_image'));
			// add_action('wp_ajax_karma_multimedia_get_image_src', array($this, 'ajax_get_image_src'));
			// add_action('wp_ajax_karma_multimedia_get_images_src', array($this, 'ajax_get_images_src'));

			add_action('init', array($this, 'init'));

		}

	}

	/**
	 * @hook init
	 */
	public function init() {

		// print metaboxes
		// add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 10, 2);
		//
		// // save metaboxes
		// add_action('save_post', array($this, 'save_post'), 10, 2);

		add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));

		add_action('save_post', array($this, 'save'), 10, 3);


	}


	/**
	 * Enqueue styles
	 *
	 * Hook for 'admin_enqueue_scripts'
	 */
	function enqueue_styles() {

		wp_enqueue_style('media-box-styles', get_template_directory_uri() . '/modules/multimedia/media-box-styles.css');

		wp_enqueue_script('karma-image-uploader', get_template_directory_uri() . '/modules/multimedia/js/image-uploader.js', array('build'), $this->version, true);
		wp_enqueue_script('karma-gallery-uploader', get_template_directory_uri() . '/modules/multimedia/js/gallery-uploader.js', array('build'), $this->version, true);
		wp_enqueue_script('karma-multimedia', get_template_directory_uri() . '/modules/multimedia/js/multimedia.js', array('sortable', 'build', 'karma-image-uploader', 'karma-gallery-uploader', 'collection'), $this->version, true);



		wp_localize_script('karma-multimedia', 'KarmaMultimedia', array(
			'ajax_url' => admin_url('admin-ajax.php')
		));

	}


	/**
	 * Save meta boxes
	 *
	 * @hook 'save_post'
	 */
	public function save($post_id, $post, $update) {


		if (current_user_can('edit_post', $post_id) && (!defined( 'DOING_AUTOSAVE' ) || !DOING_AUTOSAVE )) {

			if (isset($_REQUEST['karma-multimedia'])) {

				$meta_key = $_REQUEST['karma-multimedia'];
				$name = "karma_mm-$meta_key";
				$action = "karma_mm-$meta_key-action";
				$nonce = "karma_mm-$meta_key-nonce";

				if (isset($_REQUEST[$name], $_REQUEST[$nonce]) && wp_verify_nonce($_POST[$nonce], $action)) {

					$medias = $_REQUEST[$name];
					$medias = stripslashes($medias);
					$medias_obj = json_decode($medias);
					// var_dump($medias_obj);
					// die();

									// var_dump(stripslashes($medias));
									//
									// // var_dump($meta_key, $name, $action, $nonce, isset($_REQUEST[$name], $_REQUEST[$nonce]), wp_verify_nonce($_POST[$nonce], $action));
									// die();

					$medias_obj = apply_filters('karma_multimedia_save_medias', $medias_obj, $post_id, $meta_key, $medias);


// 					global $sublanguage_admin;
//
// 					if ($sublanguage_admin->is_sub()) {
//
// 						// $medias = stripslashes($medias);
//
// 						$translated_items = $medias_obj;
// 						$untranslated_items = $translated_items;
//
// // echo '<pre>';
// // var_dump($medias);
// // var_dump($translated_items);
// // var_dump($untranslated_items);
// // die();
//
//
// 						// $sublanguage_admin->get_untranslated_post_meta($post_id, $meta_key, true);
// 						// $untranslated_items = json_decode($untranslated_medias, true);
//
// 						foreach ($untranslated_items as $index => $item) {
//
// 							foreach ($item as $key => $column) {
//
// 								if (isset($column['placeholder'], $column['text'])) {
//
// 									$untranslated_items[$index][$key]['text'] = $column['placeholder'];
// 									unset($untranslated_items[$index][$key]['placeholder']);
// 									unset($translated_items[$index][$key]['placeholder']);
//
// 								}
//
// 							}
//
// 						}
//
// 						// $medias = json_encode($translated_items, JSON_UNESCAPED_SLASHES);
// 						// $untranslated_medias = json_encode($untranslated_items, JSON_UNESCAPED_SLASHES);
//
// 						$medias_obj = $translated_items;
//
// 						$main_language = $sublanguage_admin->get_main_language();
//
// 						$sublanguage_admin->set_language($main_language);
//
// // var_dump($untranslated_medias);
// 						update_post_meta($post_id, $meta_key, $untranslated_items);
//
// 						$sublanguage_admin->set_language();
//
// 					}

// var_dump($untranslated_medias);
					update_post_meta($post_id, $meta_key, $medias_obj);

				}

			}

		}

	}

	/**
	 * @hook 'karma_multimedia_field'
	 */
	public function print_field($post_id, $meta_key, $types, $columns = array()) {



		// require_once get_template_directory() . '/admin/collection.php';


		// $columns = json_decode(json_encode($columns));
		// var_dump(json_decode(json_encode($columns)));
		// $columns_collection = new Karma_Collection(json_decode(json_encode($columns)));

		// $columns_collection = new Karma_Collection($columns);

		// $translatable_columns = array();
		//
		// foreach ($columns as $column) {
		//
		// 	if (isset($column['translatable']) && $column['translatable']) {
		//
		// 		$translatable_columns[] = $column['key'];
		//
		// 	}
		//
		// 	// $columns_repertory[$column['key']] = $column;
		//
		// }


		// [{"type":"gallery","ids":[416,414]},{"type":"embed","text":"dfsbdfh"}]


		// add_filter('sublanguage_postmeta_override', function($meta_val, $key, $object_id) use($id) {
		// 	if ($key === $id) {
		// 		return true;
		// 	}
		// 	return $meta_val;
		// }, 10, 3);

		$name = "karma_mm-$meta_key";
		$action = "karma_mm-$meta_key-action";
		$nonce = "karma_mm-$meta_key-nonce";

		wp_nonce_field($action, $nonce, false, true);

		$medias_obj = get_post_meta($post_id, $meta_key, true);

		if (!$medias_obj) {

			$medias_obj = array();

		}

		$medias_obj = apply_filters('karma_multimedia_prepare_medias', $medias_obj, $post_id, $meta_key, $types, $columns);


// 		global $sublanguage_admin;
//
// 		if ($sublanguage_admin->is_sub()) {
//
// 			// $translated_items = json_decode($medias, true);
// 			// $untranslated_medias = $sublanguage_admin->get_untranslated_post_meta($post_id, $meta_key, true);
// 			// $untranslated_items = json_decode($untranslated_medias, true);
// 			$translated_items = $medias_obj;
// 			$untranslated_items = $sublanguage_admin->get_untranslated_post_meta($post_id, $meta_key, true);
//
// // var_dump($medias);
//
//
// 			foreach ($untranslated_items as $index => $item) {
//
// 				foreach ($item as $key => $column) {
//
// 					// $column_definition = $columns_collection->get_item('key', $key);
//
// 					if (in_array($key, $translatable_columns)) {
// 					// if (isset($column_definition->translatable, $column['text']) && $column_definition->translatable) {
//
// 						$untranslated_items[$index][$key]['placeholder'] = $untranslated_items[$index][$key]['text'];
//
// 						if (empty($translated_items[$index][$key]['text']) || $untranslated_items[$index][$key]['text'] === $translated_items[$index][$key]['text']) {
//
// 							$untranslated_items[$index][$key]['text'] = '';
//
// 						} else {
//
// 							$untranslated_items[$index][$key]['text'] = $translated_items[$index][$key]['text'];
//
// 						}
//
// 					}
//
// 				}
//
// 			}
//
// 			$medias_obj = $untranslated_items;
//
// 		}

		$medias = json_encode($medias_obj);

		if (!$medias) {

			$medias = '[]';

		}

		include get_template_directory() . '/modules/multimedia/includes/multimedia-input.php';

	}

	// public function modify($untranslated_data, $translated_data) {
	//
	// 	if (is_array($untranslated_data)) {
	//
	// 		foreach ($untranslated_data as $key => $value) {
	//
	// 			if (isset($translated_data[$key]) && $translated_data[$key] !== $value) {
	//
	//
	//
	// 			}
	//
	// 		}
	//
	// 	}
	//
	//
	// }
	//
	//
	// public function recursive_dif($data1, $data2) {
	//
	// 	if (is_array($data1)) {
	//
	// 		$dif = array();
	//
	// 		foreach ($data1 as $key => $val) {
	//
	// 			if (isset($data2[$key])) {
	//
	// 				$subdif = $this->recursive_dif($val, $data2[$key]);
	//
	// 				if (!is_array($subdif) || $subdif) {
	//
	// 					$dif[$key] = $subdif;
	//
	// 				}
	//
	// 			}
	//
	// 		}
	//
	// 	} else if ($data1 === $data2) {
	//
	// 		return $data2;
	//
	// 	}
	//
	// }
	//


	/**
	 * @ajax karma_multimedia_get_image
	 */
	public function ajax_get_image() {

		$output = array();

		if (isset($_GET['id'])) {

			$id = intval($_GET['id']);
			$src_data = wp_get_attachment_image_src($id, 'thumbnail', true);

			wp_redirect($src_data[0]);

		}

		exit;

	}


	/**
	 *	save META BOX
	 */
	// public function save_post($post_id, $post) {
	//
	// 	if ((!defined( 'DOING_AUTOSAVE') || !DOING_AUTOSAVE) && current_user_can( 'edit_post', $post_id)) {
	//
	// 		foreach ($this->items as $item) {
	//
	// 			$nonce = 'media_box' . $item['id'];
	// 			$name = 'media-box-' . $item['id'];
	//
	// 			if (isset($_POST[$nonce]) && wp_verify_nonce( $_POST[$nonce], 'media-box') && in_array($post->post_type, $item['post_types'])) { // -> check post-type
	//
	// 				$medias = isset($_POST[$name]) ? json_decode(stripslashes($_POST[$name])) : array();
	//
	// 				delete_post_meta($post_id, $item['id']);
	//
	// 				foreach ($medias as $media) {
	//
	// 					add_post_meta($post_id, $item['id'], $media);
	//
	// 				}
	//
	// 			}
	//
	// 		}
	//
	// 	}
	//
	// }

}

if (is_admin()) new Karma_Multimedia();

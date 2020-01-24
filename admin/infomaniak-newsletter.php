<?php

/**
 *	Class Karma_Mailchimp
 */
class Karma_Infomaniak_Newsletter {

	/**
	 *	Constructor
	 */
	public function __construct() {

		add_action('wp_ajax_subscribe_inl', array($this, 'ajax_subscribe_inl'));
		add_action('wp_ajax_nopriv_subscribe_inl', array($this, 'ajax_subscribe_inl'));


		add_action('karma_save_options', array($this, 'save_options'));
		add_action('karma_print_options', array($this, 'print_options'));

	}

	/**
	 * @ajax 'karma_print_options'
	 */
	public function print_options($options) {

		include get_template_directory() . '/admin/include/utils/infomaniak-newsletter-options.php';

	}

	/**
	 * @ajax 'karma_save_options'
	 */
	public function save_options($options) {

		if (isset($_POST['inl_key'])) {

			$options->update_option('inl_key', $_POST['inl_key']);

		}

		if (isset($_POST['inl_secret_key'])) {

			$options->update_option('inl_secret_key', $_POST['inl_secret_key']);

		}

		if (isset($_POST['inl_id'])) {

			$options->update_option('inl_id', $_POST['inl_id']);

		}

	}

	// /**
	//  * Subscribe Mailchimps
	//  */
	// public function subscribe_mailchimp($email, $args) {
	//
	// 	$api_key = $this->get_option('mailchimp_key');
	// 	$list_id = $this->get_option('mailchimp_id');
	//
	// 	$member_id = md5(strtolower($email));
	// 	$data_center = substr($api_key,strpos($api_key,'-')+1);
	// 	$url = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . $member_id;
	//
	// 	$data = array(
	// 		'email_address' => $email,
	// 		'status'        => 'subscribed'
	// 	);
	//
	// 	if (isset($args['firstname'])) {
	//
	// 		$data['merge_fields']['FNAME'] = $args['firstname'];
	//
	// 	}
	//
	// 	if (isset($args['lastname'])) {
	//
	// 		$data['merge_fields']['LNAME'] = $args['lastname'];
	//
	// 	}
	//
	// 	$json = json_encode($data);
	// 	$ch = curl_init($url);
	//
	// 	curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $api_key);
	// 	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// 	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	// 	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	// 	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// 	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	//
	// 	$result = curl_exec($ch);
	// 	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	// 	curl_close($ch);
	//
	// }


	/**
	 * @ajax 'subscribe_newsletter'
	 */
	public function ajax_subscribe_inl($options) {
		global $karma;

		if (isset($_POST['email'])) {

			// $data = array(
			// 	'email_address' => sanitize_email($_POST['email']),
			// 	'status'        => 'subscribed'
			// );
			//
			// if (isset($_POST['firstname'])) {
			//
			// 	$data['merge_fields']['FNAME'] = esc_attr($_POST['firstname']);
			//
			// }
			//
			// if (isset($_POST['lastname'])) {
			//
			// 	$data['merge_fields']['LNAME'] = esc_attr($_POST['lastname']);
			//
			// }

			$email_address = sanitize_email($_POST['email']);

			if ($email_address) {

				$results = $this->subscribe_inl($email_address);

				$output['infomaniak'] = $results;
				$output['success'] = $results['http_code'] === 200;

			} else {

				$output['error'] = 'invalid email';

			}

		} else {

			$output['error'] = 'email not set';
			$output['log'] = $_POST;

		}

		echo json_encode($output);
		exit;

	}



	/**
	 * Subscribe Mailchimps
	 */
	public function subscribe_inl($email) {
		global $karma;

		$api_key = $karma->options->get_option('inl_key');
		$api_secret_key = $karma->options->get_option('inl_secret_key');
		$list_id = $karma->options->get_option('inl_id');

		if (!$api_key || !$api_secret_key || !$list_id) {

			trigger_error('API Key/List ID not set');

		}

		// $member_id = md5(strtolower($email));
		// $data_center = substr($api_key,strpos($api_key,'-')+1);
		// $url = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . $member_id;

		$url = "https://newsletter.infomaniak.com/api/v1/public/mailinglist/{$list_id}/importcontact";

		$data = array(
			'contacts' => array(
				'email' => $email
			)
		);

		$json = json_encode($data);
		$ch = curl_init($url);

		// curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		// 	'Accept-Language: fr_FR',
		// 	'key: '.$api_key,
		// 	'currency: 1',
		// 	'Content-Type: application/json'
		// ));

		curl_setopt($ch, CURLOPT_USERPWD, $api_key.':'.$api_secret_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json'

		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

		$result = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return array(
			'results' => json_decode($result),
			'http_code' => $http_code,
			'json' => $json
		);

	}

}

new Karma_Infomaniak_Newsletter;

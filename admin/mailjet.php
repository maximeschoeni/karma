<?php

/**
 *	Class Karma_Mailchimp
 */
class Karma_Mailjet {

	/**
	 *	Constructor
	 */
	public function __construct() {

		add_action('wp_ajax_subscribe_mailjet', array($this, 'ajax_subscribe_mailjet'));
		add_action('wp_ajax_nopriv_subscribe_mailjet', array($this, 'ajax_subscribe_mailjet'));


		add_action('karma_save_options', array($this, 'save_options'));
		add_action('karma_print_options', array($this, 'print_options'));

	}

	/**
	 * @ajax 'karma_print_options'
	 */
	public function print_options($options) {

		include get_template_directory() . '/admin/include/utils/mailjet-options.php';

	}

	/**
	 * @ajax 'karma_save_options'
	 */
	public function save_options($options) {

		if (isset($_POST['mailjet_key_private'])) {

			$options->update_option('mailjet_key_private', $_POST['mailjet_key_private']);

		}

		if (isset($_POST['mailjet_key_public'])) {

			$options->update_option('mailjet_key_public', $_POST['mailjet_key_public']);

		}

	}

	/**
	 * @ajax 'subscribe_mailjet'
	 */
	public function ajax_subscribe_mailjet($options) {
		global $karma;

		$output = array();

		if (isset($_POST['email'])) {

			$email = sanitize_email($_POST['email']);

			$data = array(
				'IsExcludedFromCampaigns' => false,
				'Email' => $email
			);

			if (isset($_POST['name'])) {

				$data['Name'] = esc_attr($_POST['name']);

			}

			$contact = $this->add_contact($data);

			$output['contact'] = $contact;

			// $subscription = $this->subscribe(urlencode($email), array(
			// 	array(
			// 		'ListID' => 14984,
			// 		'Action' => 'addnoforce'
			// 	)
			// ));

			$subscription = $this->subscribe($email, 14984);

			$output['subscription'] = $subscription;

			$output['success'] = 1;


		} else {

			$output['error'] = 'email not set';
			$output['log'] = $_POST;

		}

		echo json_encode($output);
		exit;

	}



	/**
	 * Subscribe Mailjet
	 */
	public function add_contact($data) {
		global $karma;

		$api_key_public = $karma->options->get_option('mailjet_key_public');
		$api_key_private = $karma->options->get_option('mailjet_key_private');

		if ($api_key_public && $api_key_private) {

			$url = 'https://api.mailjet.com/v3/REST/contact';

			$json = json_encode($data);
			$ch = curl_init($url);

			curl_setopt($ch, CURLOPT_USERPWD, $api_key_public.':'.$api_key_private);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			curl_setopt($ch, CURLOPT_POST, 1);

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

	// /**
	//  * Subscribe Mailjet
	//  */
	// public function subscribe($contact_id, $lists) {
	// 	global $karma;
	//
	// 	$api_key_public = $karma->options->get_option('mailjet_key_public');
	// 	$api_key_private = $karma->options->get_option('mailjet_key_private');
	//
	// 	if ($api_key_public && $api_key_private) {
	//
	// 		$url = 'https://api.mailjet.com/v3/REST/contact/'.$contact_id.'/managecontactslists';
	//
	// 		$json = json_encode(array(
	// 			'ContactsLists' => $list_ids
	// 			// array(
	// 			// 	array(
	// 			// 		'ListID' => $list_id,
	// 			// 		'Action' => 'addnoforce'
	// 			// 	)
	// 			// )
	// 		));
	//
	// 		$ch = curl_init($url);
	//
	// 		curl_setopt($ch, CURLOPT_USERPWD, $api_key_public.':'.$api_key_private);
	// 		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	// 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// 		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	// 		curl_setopt($ch, CURLOPT_POST, 1);
	//
	// 		$result = curl_exec($ch);
	// 		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	// 		curl_close($ch);
	//
	// 		return array(
	// 			'results' => json_decode($result),
	// 			'http_code' => $http_code,
	// 			'json' => $json
	// 		);
	//
	// 	}
	//
	// }



	/**
	 * Subscribe Mailjet
	 */
	public function subscribe($email, $list_id) {
		global $karma;

		$api_key_public = $karma->options->get_option('mailjet_key_public');
		$api_key_private = $karma->options->get_option('mailjet_key_private');

		if ($api_key_public && $api_key_private) {

			$url = 'https://api.mailjet.com/v3/REST/contactslist/'.$list_id.'/managecontact';

			$json = json_encode(array(
				// 'Name' => 'test',
				'Action' => 'addnoforce',
				'Email' => $email
			));

			$ch = curl_init($url);

			curl_setopt($ch, CURLOPT_USERPWD, $api_key_public.':'.$api_key_private);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			curl_setopt($ch, CURLOPT_POST, 1);

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


}

global $karma_mailjet;
$karma_mailjet = new Karma_Mailjet;

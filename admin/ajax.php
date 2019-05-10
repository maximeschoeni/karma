<?php

/**
 *	Class Karma_AJAX
 */
class Karma_AJAX {
	
	/**
	 *	Constructor
	 */
	public function __construct() {

//     add_action('wp_ajax_get_spectacle', array($this, 'ajax_get_spectacle'));
//     add_action('wp_ajax_nopriv_get_spectacle', array($this, 'ajax_get_spectacle'));
//
//     add_action('wp_ajax_subscribe_newsletter', array($this, 'ajax_subscribe_newsletter'));
//     add_action('wp_ajax_nopriv_subscribe_newsletter', array($this, 'ajax_subscribe_newsletter'));
//
// 		// only logged user
// 		add_action('wp_ajax_delete_reservation', array($this, 'ajax_delete_reservation'));
//
// 		add_action('wp_ajax_get_user_tokens', array($this, 'ajax_get_user_tokens'));
//     add_action('wp_ajax_nopriv_get_user_tokens', array($this, 'ajax_get_user_tokens'));
//
// 		// format date range (admin only)
// 		add_action('wp_ajax_format_date_range', array($this, 'ajax_format_date_range'));


	}

	/**
	 * @ajax 'format_date_range'
	 */
	public function ajax_format_date_range() {

// 		$output = array();
//
// 		if (isset($_GET['date1'], $_GET['date2'])) {
//
// 			$output['date_range'] = $this->format_date_range($_GET['date1'], $_GET['date2']);
//
// 		} else if (isset($_GET['spectacle_ids'])) {
//
// 			$spectacle_ids = array_map('intval', explode(',', $_GET['spectacle_ids']));
//
// 			$output['date_range'] = $this->shows->get_spectacles_date_ranges($spectacle_ids);
//
// 		}
//
// 	 	echo json_encode($output);
// 		exit;

	}



	/**
	 * Subscribe Mailchimps
	 */
	public function subscribe_mailchimp($data) {

// 		$api_key = $this->get_option('mailchimp_key'); //'9b439382b8a68b0ff00e9105e0a0f43c-us15';
// 		$list_id = $this->get_option('mailchimp_id'); //'4ccc2b3849';
//
// 		$member_id = md5(strtolower($email));
// 		$data_center = substr($api_key,strpos($api_key,'-')+1);
// 		$url = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . $member_id;
//
// 		$json = json_encode(array(
// 				'email_address' => $data['email'],
// 				'status'        => 'subscribed' // "subscribed","unsubscribed","cleaned","pending"
// // 				'merge_fields'  => array(
// // 						'FNAME'     => $data['first_name'],
// // 						'LNAME'     => $data['last_name']
// // 				)
// 		));
//
// 		$ch = curl_init($url);
//
// 		curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $api_key);
// 		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
// 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// 		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
// 		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
// 		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// 		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
//
// 		$result = curl_exec($ch);
// 		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// 		curl_close($ch);

	}


}

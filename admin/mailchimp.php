<?php

/**
 *	Class Karma_Mailchimp
 */
class Karma_Mailchimp {

	/**
	 *	Constructor
	 */
	public function __construct() {


//     add_action('wp_ajax_get_spectacle', array($this, 'ajax_get_spectacle'));
//     add_action('wp_ajax_nopriv_get_spectacle', array($this, 'ajax_get_spectacle'));
//
    add_action('wp_ajax_subscribe_newsletter', array($this, 'ajax_subscribe_newsletter'));
    add_action('wp_ajax_nopriv_subscribe_newsletter', array($this, 'ajax_subscribe_newsletter'));


		add_action('karma_save_options', array($this, 'save_options'));
		add_action('karma_print_options', array($this, 'print_options'));

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
	 * @ajax 'karma_print_options'
	 */
	public function print_options($options) {

		include get_template_directory() . '/admin/include/utils/mailchimp-options.php';

	}

	/**
	 * @ajax 'karma_save_options'
	 */
	public function save_options($options) {

		if (isset($_POST['mailchimp_key'])) {

			$options->update_option('mailchimp_key', $_POST['mailchimp_key']);

		}

		if (isset($_POST['mailchimp_id'])) {

			$options->update_option('mailchimp_id', $_POST['mailchimp_id']);

		}

	}

	/**
	 * Subscribe Mailchimps
	 */
	public function subscribe_mailchimp($email, $args) {

		$api_key = $this->get_option('mailchimp_key');
		$list_id = $this->get_option('mailchimp_id');

		$member_id = md5(strtolower($email));
		$data_center = substr($api_key,strpos($api_key,'-')+1);
		$url = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . $member_id;

		$data = array(
			'email_address' => $email,
			'status'        => 'subscribed'
		);

		if (isset($args['firstname'])) {

			$data['merge_fields']['FNAME'] = $args['firstname'];

		}

		if (isset($args['lastname'])) {

			$data['merge_fields']['LNAME'] = $args['lastname'];

		}

		$json = json_encode($data);
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $api_key);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

		$result = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

	}

}

new Karma_Mailchimp;

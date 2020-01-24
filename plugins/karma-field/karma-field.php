<?php
/**
 * @package karma-fields
 */
/*
Plugin Name: Karma Fields
Version: 1.0
*/





if (is_admin()) {

	require_once dirname(__FILE__) . '/class-field.php';

	$karma_field = new Karma_Field;
	
	$karma_field->url = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__));

}

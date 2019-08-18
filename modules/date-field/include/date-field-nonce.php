<?php
	$action = "karma_date_field-action";
	$nonce = "karma_date_field-nonce";

	wp_nonce_field($action, $nonce, false, true);

?>

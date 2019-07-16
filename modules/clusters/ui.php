<?php

/**
 *	Class Karma_Dependency_Expirer
 */
class Karma_Clusters_UI {

	/**
	 *	Constructor
	 */
	function __construct() {

		add_action('admin_notices', array($this, 'print_notice'));

	}

	/**
	 * @hook 'wp_loaded'
	 */
	function print_notice() {
    ?>
    <div class="notice notice-info">
        <p>Updating Cache...</p>
    </div>
    <?php


	}


}

new Karma_Clusters_UI();

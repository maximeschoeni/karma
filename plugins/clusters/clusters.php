<?php
/**
 * @package Clusters
 */
/*
Plugin Name: Clusters
Version: 1.0
*/



global $karma_clusters;

require_once dirname(__FILE__) . '/classes/class-clusters.php';

$karma_clusters->url = plugin_dir_url( __FILE__ );

<?php

if (!isset($args)) {

  $args = array();

}

wp_editor($value, 'karma_field-'.$meta_key, $args);

?>

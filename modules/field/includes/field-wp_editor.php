<?php

if (!isset($args)) {

  $args = array();
  
}

wp_editor(get_post_meta($post_id, $meta_key, true), 'karma_field-'.$meta_key, $args);

?>

<?php if (isset($args['values']) && $args['values']) { ?>
<?php $values = get_post_meta($post_id, $meta_key); ?>
<ul>
  <?php foreach($args['values'] as $key => $name) { ?>
    <li><label><input type="checkbox" name="karma_field-<?php echo $meta_key; ?>[]" value="<?php echo $key; ?>" <?php if (in_array($key, $values)) echo 'checked' ?>/><?php echo $name; ?></label></li>
  <?php } ?>
</ul>
<?php } ?>

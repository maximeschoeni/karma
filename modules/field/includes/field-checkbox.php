<input
  type="checkbox"
  name="karma_field-<?php echo $meta_key; ?>"
  <?php if (isset($args['id'])) { ?>
    id="<?php echo $args['id']; ?>"
  <?php } ?>
  <?php if (isset($args['value'])) { ?>
    checked
  <?php } ?>
/>

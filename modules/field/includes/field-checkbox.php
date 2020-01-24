<input
  type="checkbox"
  name="karma_field-<?php echo $meta_key; ?>"
  id="karma_field-<?php echo $meta_key; ?>"
  <?php if (isset($args['value']) && $args['value']) { ?>
    checked
  <?php } ?>
/>
<?php if (isset($args['label']) && $args['label']) { ?>
  <label for="karma_field-<?php echo $meta_key; ?>"><?php echo $args['label']; ?></label>
<?php } ?>

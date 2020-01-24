<?php $id = isset($args['id']) ? $args['id'] : 'karma_field-'.$meta_key; ?>
<input
  type="checkbox"
  name="karma_field-<?php echo $meta_key; ?>"
  id="<?php echo $id; ?>"
  <?php if ($value) { ?>
    checked
  <?php } ?>
/>
<?php if (isset($args['label']) && $args['label']) { ?>
  <label for="<?php echo $id; ?>"><?php echo $args['label']; ?></label>
<?php } ?>

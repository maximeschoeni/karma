<?php if (isset($args['values']) && $args['values']) { ?>
  <?php $value = get_post_meta($post_id, $meta_key, true); ?>
  <select name="karma_field-<?php echo $meta_key; ?>">
    <?php if (isset($args['novalue'])) { ?>
      <option value=""><?php echo $args['novalue']; ?></option>
    <?php } ?>
    <?php foreach($args['values'] as $key => $name) { ?>
      <option value="<?php echo $key; ?>" <?php if ((string) $key === $value) echo 'selected' ?>><?php echo $name; ?></option>
    <?php } ?>
  </select>
<?php } ?>

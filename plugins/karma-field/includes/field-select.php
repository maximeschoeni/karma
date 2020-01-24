<?php if (isset($args['values']) && $args['values']) { ?>
  <select name="karma_field-<?php echo $meta_key; ?>" <?php if (isset($args['id'])) echo 'id="'.$args['id'].'"'; ?>>
    <?php if (isset($args['novalue'])) { ?>
      <option value=""><?php echo $args['novalue']; ?></option>
    <?php } ?>
    <?php foreach($args['values'] as $option) { ?>
      <option value="<?php echo $option['key']; ?>" <?php if ((string) $option['key'] === (string) $value) echo 'selected' ?>><?php echo $option['name']; ?></option>
    <?php } ?>
  </select>
<?php } ?>

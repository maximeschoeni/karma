<?php foreach ($fields as $field) {
  if (!isset($field['id']) || !$field['id']) {
    $field['id'] = 'karma_field-input-'.$field['meta_key'];
  }
  ?>
  <div class="fields-group" style="margin-top:1em">
    <label for="<?php echo $field['id']; ?>" style="display:block"><?php echo $field['label']; ?></label>
    <?php $this->print_field($post, $field); ?>
  </div>
<?php } ?>

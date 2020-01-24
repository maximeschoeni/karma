<?php if (isset($args['values']) && $args['values']) { ?>
  <ul>
    <?php foreach($args['values'] as $option) { ?>
      <li>
        <label>
          <input type="checkbox" name="karma_field-<?php echo $meta_key; ?>[]" value="<?php echo $option['key']; ?>" <?php if (is_array($value) && in_array($option['key'], $value)) echo 'checked' ?>/><?php echo $option['name']; ?>
        </label>
      </li>
    <?php } ?>
  </ul>
<?php } ?>

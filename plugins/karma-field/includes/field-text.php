<input
  type="text"
  name="karma_field-<?php echo $meta_key; ?>"
  value="<?php echo $value; ?>"
  <?php if (isset($args['placeholder'])) { ?>
    placeholder="<?php echo $args['placeholder']; ?>"
  <?php } ?>
  <?php if (isset($args['autocomplete'])) { ?>
    autocomplete="<?php echo $args['autocomplete']; ?>"
  <?php } ?>
  <?php if (isset($args['class'])) { ?>
    class="<?php echo $args['class']; ?>"
  <?php } ?>
  <?php if (isset($args['style'])) { ?>
    style="<?php echo $args['style']; ?>"
  <?php } ?>
  <?php if (isset($args['id'])) { ?>
    id="<?php echo $args['id']; ?>"
  <?php } ?>
/>

<textarea
  name="karma_field-<?php echo $meta_key; ?>"
  <?php if (isset($args['placeholder'])) { ?>
    placeholder="<?php echo $args['placeholder']; ?>"
  <?php } ?>
  <?php if (isset($args['class'])) { ?>
    class="<?php echo $args['class']; ?>"
  <?php } ?>
  <?php if (isset($args['id'])) { ?>
    id="<?php echo $args['id']; ?>"
  <?php } ?>
><?php echo $value; ?></textarea>

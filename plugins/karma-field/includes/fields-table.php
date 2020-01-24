<table>
  <?php foreach ($fields as $field) {
    if (!isset($args['id'])) {
      $field['id'] = 'karma_field-'.$field['meta_key'];
    }
    ?>
    <tr>
      <th style="padding-right:20px">
        <label for="<?php echo $field['id']; ?>"><?php echo $field['label']; ?></label>
      </th>
      <td>
        <?php $this->print_field($post, $field); ?>
      </td>
    </tr>
  <?php } ?>
</table>

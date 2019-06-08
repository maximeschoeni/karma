<div class="color-profile-orders" style="margin-right:10px">
  <h1><?php echo __('Orders logs', 'color-profile'); ?></h1>
  <br />
  <table style="width:100%;">
    <tr>
      <td>List of orders</td>
      <td style="text-align:right;">Filtres: <?php $this->print_filters(); ?></td>
    </tr>
  </table>
  <table class="wp-list-table widefat">
    <thead>
      <tr>
        <?php foreach ($fields as $field) { ?>
          <th><?php echo apply_filters('karma_orders_header_cell', $field, $this); ?></th>
        <?php } ?>
        <th>Invoice</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders_results['items'] as $i => $row) { ?>
        <tr <?php if ($i % 2 === 0) { echo 'class="alternate"'; } ?>>
          <?php foreach ($fields as $field) { ?>
            <td><?php echo apply_filters('karma_orders_cell', $row->$field, $row, $field, $this); ?></td>
          <?php } ?>
          <td><a href="<?php echo add_query_arg(array('action' => 'get_invoice', 'invoice' => $row->invoice), admin_url('admin-post.php')); ?>">Invoice</a></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
  <div class="tablenav" style="height:auto">
    <table style="width:100%;">
      <tr>
        <td><?php //include get_template_directory() . '/admin/include/orders-export.php'; ?></td>
        <td class="tablenav-pages"><?php if ($page_links) echo $page_links; ?></td>
      </tr>
    </table>
  </div>
</div>

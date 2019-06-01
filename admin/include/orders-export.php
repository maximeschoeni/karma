<?php 
	
	$filename = '';
	
	if (isset($this->request_orders['year'])) {
		
		$year = $this->request_orders['year'];
		$filename .= '-'.$year;
		
		if (isset($this->request_orders['month'])) {
		
			$month = $this->request_orders['month'];
			$filename .= '-' . zeroise($month, 2);
		
		}
		
	}
	
	$zip_filename = 'invoices'.$filename.'.zip';
	
	$export_filename = 'colorlibrary-export'.$filename.'.xlsx';
	
?>
<form action="<?php echo admin_url() ?>admin.php" method="get">
	<input type="hidden" name="page" value="<?php echo $this->page_name ?>">
	<input type="text" name="export" value="<?php echo $export_filename ?>" style="width:250px">
	<?php if (isset($year)) { ?>
		<input type="hidden" name="year" value="<?php echo $year ?>">
	<?php } ?>
	<?php if (isset($month)) { ?>
		<input type="hidden" name="month" value="<?php echo $month ?>">
	<?php } ?>
	<input class="button" type="submit" value="Download"/>
</form>
<br>
<form action="<?php echo admin_url() ?>admin.php" method="get">
	<input type="hidden" name="page" value="<?php echo $this->page_name ?>">
	<input type="text" name="download" value="<?php echo $zip_filename ?>" style="width:250px">
	<?php if (isset($year)) { ?>
		<input type="hidden" name="year" value="<?php echo $year ?>">
	<?php } ?>
	<?php if (isset($month)) { ?>
		<input type="hidden" name="month" value="<?php echo $month ?>">
	<?php } ?>
	<input class="button" type="submit" value="Download"/>
</form>
<br><br>

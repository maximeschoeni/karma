<?php 
$min_year = 2015;
$max_year = intval(date('Y'));
$monthes = array();
?>
<form style="display: inline" action="<?php echo admin_url() ?>admin.php" method="get">
	<input type="hidden" name="page" value="<?php echo $this->page_name ?>">
	<select name="month">
		<option value="">month</option>
		<option value="">-</option>
		<?php for ($i = 1; $i <= 12; $i++) { ?>
			<option value="<?php echo $i ?>"<?php if (isset($_GET['month']) && intval($_GET['month']) === $i) echo ' selected'; ?>><?php echo date("F", mktime(0, 0, 0, $i, 1, 2036)) ?></option>
		<?php } ?>
	</select>
	<select name="year">
		<option value="">year</option>
		<option value="">-</option>
		<?php for ($i = $max_year; $i >= $min_year; $i--) { ?>
			<option value="<?php echo $i ?>"<?php if (isset($_GET['year']) && intval($_GET['year']) === $i) echo ' selected'; ?>><?php echo $i ?></option>
		<?php } ?>
	</select>
	<input class="button" type="submit" value="Filtre"/>
</form>

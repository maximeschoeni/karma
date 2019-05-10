<?php if ($label) { ?>
	<label for="<?php echo $name; ?>"><?php echo $label; ?></label><br>
<?php } ?>
<select id="<?php echo $name; ?>" name="<?php echo $name; ?>">
	<option value="1x1"<?php echo ($current_format === '1x1' ? ' selected' : ''); ?>>1x1</option>
	<option value="1x2"<?php echo ($current_format === '1x2' ? ' selected' : ''); ?>>1x2</option>
	<option value="2x2"<?php echo ($current_format === '2x2' ? ' selected' : ''); ?>>2x2</option>
</select>
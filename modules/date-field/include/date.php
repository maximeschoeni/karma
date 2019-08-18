<input type="hidden" id="<?php echo $name; ?>" name="<?php echo $name; ?>" value="<?php echo $args['value']; ?>">
<input type="text" id="karma-date-input-<?php echo $meta_key; ?>" value="">
<script>
	document.addEventListener("DOMContentLoaded", function() {
		var input = document.getElementById("karma-date-input-<?php echo $meta_key; ?>");
		var hiddenInput = document.getElementById("<?php echo $name; ?>");
		createDatePopupManager(input, hiddenInput);
	});
</script>

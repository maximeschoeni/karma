<input type="hidden" id="karma_field-<?php echo $meta_key; ?>" name="karma_field-<?php echo $meta_key; ?>" value="<?php echo $value; ?>">
<div id="karma-date-input-<?php echo $meta_key; ?>-container"></div>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		var container = document.getElementById("karma-date-input-<?php echo $meta_key; ?>-container");
		var hiddenInput = document.getElementById("karma_field-<?php echo $meta_key; ?>");
		var options = <?php echo json_encode($args); ?>;
		container.appendChild(
			KarmaFieldMedia.fields.date(hiddenInput.value, options, function(value) {
				hiddenInput.value = value;
			})
		);
	});
</script>

<?php
	if (!is_array($value)) {
		$value = array();
	}
 ?>
<input type="hidden" id="karma_field-<?php echo $meta_key; ?>" name="karma_field-<?php echo $meta_key; ?>">
<input type="hidden" name="karma_field_datatype[<?php echo $meta_key; ?>]" value="json">
<div id="karma-field-multi-<?php echo $meta_key; ?>-container"></div>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		var container = document.getElementById("karma-field-multi-<?php echo $meta_key; ?>-container");
		var hiddenInput = document.getElementById("karma_field-<?php echo $meta_key; ?>");
		var options = <?php echo json_encode($args); ?>;
		var values = <?php echo json_encode($value); ?>;
		container.appendChild(
			KarmaFieldMedia.fields.multimedia(values, options, function(values) {
				hiddenInput.value = JSON.stringify(values);
			})
		);
		hiddenInput.value = JSON.stringify(values);
	});
</script>

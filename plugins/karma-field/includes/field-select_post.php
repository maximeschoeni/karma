<div id="karma-field-multi-<?php echo $meta_key; ?>-container"></div>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		var container = document.getElementById("karma-field-multi-<?php echo $meta_key; ?>-container");
		var options = <?php echo json_encode($args); ?>;
		container.appendChild(
			KarmaFieldMedia.fields.select_post('<?php echo $value; ?>', options, function(values) {
				hiddenInput.value = JSON.stringify(values);
			})
		);
		hiddenInput.value = JSON.stringify(values);
	});
</script>

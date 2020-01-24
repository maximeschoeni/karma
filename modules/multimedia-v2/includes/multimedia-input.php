<input type="hidden" name="karma-multimedia[]" value="<?php echo $meta_key; ?>">
<div class="multimedia-box" id="karma-multimedia-<?php echo $name; ?>"></div>
<script>
document.addEventListener("DOMContentLoaded", function() {
	var container = document.getElementById("karma-multimedia-<?php echo $name; ?>");
	container.appendChild(KarmaMultimedia.build("<?php echo $name; ?>", <?php echo json_encode($columns); ?>, <?php echo $medias; ?>, "<?php echo $meta_key; ?>"));
});
</script>

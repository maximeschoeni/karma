<input type="hidden" name="karma-multimedia" value="<?php echo $meta_key; ?>">
<div class="multimedia-box" id="karma-multimedia-<?php echo $name; ?>"></div>
<script>
document.addEventListener("DOMContentLoaded", function() {
	var container = document.getElementById("karma-multimedia-<?php echo $name; ?>");
	// var library = <?php // echo json_encode($library); ?>;
	// var post_contents = <?php // echo json_encode($post_contents); ?>;
	var manager = KarmaMultimedia.createManager();

	manager.inputName = "<?php echo $name; ?>";
	// manager.library = library;
	// manager.post_contents = post_contents;
	manager.items = <?php echo $medias; ?>;
	manager.types = <?php echo json_encode($types); ?>;
	manager.columns = <?php echo json_encode($columns); ?>;

	container.appendChild(manager.build());

	// for (var i = 0; i < types.length; i++) {
	// 	var type = types[i];
	// 	if (KarmaMultimedia[type.callback]) {
	// 		manager.registerType({
	// 			key: type.key,
	// 			name: type.name,
	// 			builder: type.builder
	// 		});
	// 	}
	//
	// }
	//
	// if (types.indexOf("image") > 0) {
	// 	manager.registerType({
	// 		key: "image",
	// 		name: "Image",
	// 		callback: KarmaMultimedia.buildGalleryInput
	// 	});
	// }
	// if (types.indexOf("gallery") > 0) {
	// 	manager.registerType({
	// 		key: "gallery",
	// 		name: "Gallery",
	// 		callback: KarmaMultimedia.buildGalleryInput
	// 	});
	// }
	// if (types.indexOf("embed") > 0) {
	// 	manager.registerType({
	// 		key: "embed",
	// 		name: "Video Embed",
	// 		callback: KarmaMultimedia.buildTextareaInput
	// 	});
	// }


	manager.update();
});
</script>

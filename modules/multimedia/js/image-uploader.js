function buildImageUploader(current, onSelect, btnName) {
	var content;
	var addFrame;
	function open() {
		if (!addFrame) {
			addFrame = wp.media({
				title: "Select file",
				button: {
					text: "Use this file"
				},
				multiple: true
			});
			addFrame.on("select", function() {
				onSelect(addFrame.state().get("selection").toJSON().map(function(attachment) {
					// if (attachment.type === "image") {
					// 	library[attachment.id] = attachment.sizes.thumbnail.url;
					// } else if (attachment.thumb) {
					// 	library[attachment.id] = attachment.thumb.src;
					// }
					return attachment.id;
				}));
			});
			addFrame.on("open", function(){
				var selection = addFrame.state().get("selection");
				if (current) {
					selection.add(wp.media.attachment(current));
				}
			});
		}
		addFrame.open();
	}
	if (current) {
		var img = build("img");
		img.src = KarmaMultimedia.ajax_url+"?action=karma_multimedia_get_image&id="+current;
		content = build("div.media-box-image", img);
		// ajaxGet(KarmaMultimedia.ajax_url, {
		// 	id: current,
		// 	action: "karma_multimedia_get_image_src"
		// }, function(results) {
		// 	if (results.src) {
		// 		var img = build("img");
		// 		img.src = results.src[0];
		// 		content.appendChild(img);
		// 	}
		// })


		// content = build("img.media-box-image", function() {
		// 	this.src = library[current];
		// });
	} else {
		content = build("button.button", btnName || "Ajouter");
	}
	content.addEventListener("click", function(event) {
		open();
		event.preventDefault();
	});
	return content;
}

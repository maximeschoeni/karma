function createImageUploader(type, multiple) {
	var manager = {
		addFrame: null,
		imageId: null,
		open: function () {
			if (!this.addFrame) {
				var args = {
					title: "Select file",
					button: {
						text: "Use this file"
					},
					// library: {
          //   type: 'application/font-woff'
        	// },
					multiple: multiple ? true : false
				};
				if (type) {
					args["library"] = {
            type: type
        	}
				}
				this.addFrame = wp.media(args);
				this.addFrame.on("select", function() {
					if (manager.onSelect) {
						manager.onSelect(manager.addFrame.state().get("selection").toJSON().map(function(attachment) {
							return attachment;
						}));
					}
				});
				this.addFrame.on("open", function(){
					var selection = manager.addFrame.state().get("selection");
					if (manager.imageId) {
						selection.add(wp.media.attachment(manager.imageId));
					}
				});
			}
			this.addFrame.open();
		}
	}
	return manager;
}
//
// 		// update: function() {
// 		// 	if (this.content) {
// 		// 		element.removeChild(this.content);
// 		// 	}
// 		//
// 		// 	if (this.imageId) {
// 		// 		var img = build("img");
// 		// 		img.src = KarmaMultimedia.ajax_url+"?action=karma_multimedia_get_image&id="+current;
// 		// 		content = build("div.media-box-image", img);
// 		// 		element
// 		// 	} else {
// 		//
// 		// 	}
// 		// 	element
// 		// }
// 	};
// 	return buildManager("div", manager, function(element, manager) {
//
// 	});
//
//
//
// 	var content;
// 	var addFrame;
// 	function open() {
// 		if (!addFrame) {
// 			addFrame = wp.media({
// 				title: "Select file",
// 				button: {
// 					text: "Use this file"
// 				},
// 				multiple: true
// 			});
// 			addFrame.on("select", function() {
// 				onSelect(addFrame.state().get("selection").toJSON().map(function(attachment) {
// 					// if (attachment.type === "image") {
// 					// 	library[attachment.id] = attachment.sizes.thumbnail.url;
// 					// } else if (attachment.thumb) {
// 					// 	library[attachment.id] = attachment.thumb.src;
// 					// }
// 					return attachment.id;
// 				}));
// 			});
// 			addFrame.on("open", function(){
// 				var selection = addFrame.state().get("selection");
// 				if (current) {
// 					selection.add(wp.media.attachment(current));
// 				}
// 			});
// 		}
// 		addFrame.open();
// 	}
// 	if (current) {
// 		var img = build("img");
// 		img.src = KarmaMultimedia.ajax_url+"?action=karma_multimedia_get_image&id="+current;
// 		content = build("div.media-box-image", img);
// 		// ajaxGet(KarmaMultimedia.ajax_url, {
// 		// 	id: current,
// 		// 	action: "karma_multimedia_get_image_src"
// 		// }, function(results) {
// 		// 	if (results.src) {
// 		// 		var img = build("img");
// 		// 		img.src = results.src[0];
// 		// 		content.appendChild(img);
// 		// 	}
// 		// })
//
//
// 		// content = build("img.media-box-image", function() {
// 		// 	this.src = library[current];
// 		// });
// 	} else {
// 		content = build("button.button", btnName || "Ajouter");
// 	}
// 	content.addEventListener("click", function(event) {
// 		open();
// 		event.preventDefault();
// 	});
// 	return content;
// }

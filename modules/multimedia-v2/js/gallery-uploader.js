function createGalleryUploader() {
	var manager = {
		frame: null,
		imageIds: null,
		mediaTypes: ["image"],
		open: function () {
			if (!this.frame) {
				if (this.imageIds && this.imageIds.length) {
					this.frame = wp.media.gallery.edit('[gallery ids="'+this.imageIds.join(",")+'"]');
				} else {
					// enable video
					wp.media.controller.GalleryAdd = wp.media.controller.GalleryAdd.extend({
						initialize: function() {
							if ( ! this.get('library') ) {
								this.set( 'library', wp.media.query(manager.mediaTypes ? { type: manager.mediaTypes } : null) );
							}
							wp.media.controller.Library.prototype.initialize.apply( this, arguments );
						}
					});
					wp.media.controller.GalleryEdit = wp.media.controller.GalleryEdit.extend({
						activate: function() {
							var library = this.get('library');
							library.props.set( 'type', manager.mediaTypes );
							this.get('library').observe( wp.Uploader.queue );
							this.frame.on( 'content:render:browse', this.gallerySettings, this );
							wp.media.controller.Library.prototype.activate.apply( this, arguments );
						}
					});
					wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend({
						render: function() {
							return this;
						}
					});
					this.frame = wp.media({
						frame: "post",
						state: "gallery-edit",
						type: this.mediaTypes,
						editing: true,
						multiple: true  // Set to true to allow multiple files to be selected
					});
				}
				this.frame.on("update",function(attachments) {
					manager.imageIds = attachments.map(function(attachment){
						return attachment.id;
					});
					if (manager.onChange) {
						manager.onChange(attachments);
					}
				});
			}
			this.frame.open();
		}
	}
	return manager;
}

//
// var customMediaBoxGallery = false;
//
// function buildGalleryUploader(currentIds, onChange) {
// 	var content;
// 	var frame;
// 	var mediaTypes = ["image"];
// 	function open() {
// 		if (!frame) {
// 			if (currentIds && currentIds.length) {
// 				frame = wp.media.gallery.edit('[gallery ids="'+currentIds+'"]');
// 			} else {
// 				frame = wp.media({
// 					frame:"post",
// 					state:"gallery-edit",
// 					type:mediaTypes,
// 					editing: true,
// 					multiple: true  // Set to true to allow multiple files to be selected
// 				});
// 			}
// 			frame.on("update",function(attachments) {
// 				currentIds = attachments.map(function(attachment){
// 					// if (attachment.attributes.type === "image") {
// 					// 	library[attachment.id] = (attachment.attributes.sizes.thumbnail) ? attachment.attributes.sizes.thumbnail.url : attachment.attributes.url;
// 					// } else {
// 					// 	library[attachment.id] = attachment.attributes.icon;
// 					// }
// 					return attachment.id;
// 				});
// 				onChange(currentIds);
// 			});
// 		}
// 		frame.open();
// 	}
// 	if (currentIds) {
// 		// content = build("div.media-box-gallery", currentIds.map(function(id) {
// 		// 	if (library[id]) {
// 		// 		return build("img", function() {
// 		// 			this.src = library[id];
// 		// 		});
// 		// 	}
// 		// }));
//
// 		content = build("div.media-box-gallery", currentIds.map(function(id) {
// 			var img = build("img");
// 			img.src = KarmaMultimedia.ajax_url+"?action=karma_multimedia_get_image&id="+id;
// 			return img;
// 		}));
//
// 		// ajaxGet(KarmaMultimedia.ajax_url, {
// 		// 	ids: JSON.stringify(currentIds),
// 		// 	action: "karma_multimedia_get_images_src"
// 		// }, function(results) {
// 		// 	for (var i = 0; i < currentIds.length; i++) {
// 		// 		var id = currentIds[i];
// 		// 		if (results[id]) {
// 		// 			var img = build("img");
// 		// 			img.src = results[id][0];
// 		// 			content.appendChild(img);
// 		// 		}
// 		// 	}
// 		// })
// 	} else {
// 		content = build("button.button", "Ajouter");
// 	}
// 	content.addEventListener("click", function(event) {
// 		event.preventDefault();
// 		open();
// 	});
//
// 	if (!customMediaBoxGallery) {
// 		// enable video
// 		wp.media.controller.GalleryAdd = wp.media.controller.GalleryAdd.extend({
// 			initialize: function() {
// 				if ( ! this.get('library') ) {
// 					this.set( 'library', wp.media.query(mediaTypes ? { type: mediaTypes } : null) );
// 				}
// 				wp.media.controller.Library.prototype.initialize.apply( this, arguments );
// 			}
// 		});
// 		wp.media.controller.GalleryEdit = wp.media.controller.GalleryEdit.extend({
// 			activate: function() {
// 				var library = this.get('library');
// 				library.props.set( 'type', mediaTypes );
// 				this.get('library').observe( wp.Uploader.queue );
// 				this.frame.on( 'content:render:browse', this.gallerySettings, this );
// 				wp.media.controller.Library.prototype.activate.apply( this, arguments );
// 			}
// 		});
// 		wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend({
// 			render: function() {
// 				return this;
// 			}
// 		});
// 		customMediaBoxGallery = true;
// 	}
//
// 	return content;
// }

if (!window.KarmaFieldMedia) {
	KarmaFieldMedia = {};
}
KarmaFieldMedia.fields = {};
KarmaFieldMedia.attachmentPromises = {};
KarmaFieldMedia.getImageSrc = function(id, callback) {
	if (!KarmaFieldMedia.attachmentPromises[id]) {
		KarmaFieldMedia.attachmentPromises[id] = new Promise(function(resolve, reject) {
			Ajax.get(KarmaFieldMedia.ajax_url, {
				action: "karma_multimedia_get_image_src",
				id: id
			}, function(results) {
				resolve(results);
			});
		});
	}
	if (callback) {
		KarmaFieldMedia.attachmentPromises[id].then(callback);
	}
	return KarmaFieldMedia.attachmentPromises[id];
}
KarmaFieldMedia.createImageUploader = function() {
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
					library: {
            type: manager.mimeType || null //'application/font-woff'
        	},
					multiple: false
				};
				// if (manager.mimeType) {
				// 	args["library"] = {
        //     type: manager.mimeType
        // 	}
				// }
				this.addFrame = wp.media(args);
				this.addFrame.on("select", function() {
					if (manager.onSelect) {
						manager.onSelect(manager.addFrame.state().get("selection").toJSON().map(function(attachment) {
							return attachment;
						}));
					}
				});
				this.addFrame.on("open", function() {
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
KarmaFieldMedia.createGalleryUploader = function() {
	var manager = {
		frame: null,
		imageIds: null,
		mimeTypes: ["image"],
		open: function () {
			if (!this.frame) {
				// enable video
				wp.media.controller.GalleryAdd = wp.media.controller.GalleryAdd.extend({
					initialize: function() {
						if ( ! this.get('library') ) {
							this.set( 'library', wp.media.query(manager.mimeTypes ? { type: manager.mimeTypes } : null) );
						}
						wp.media.controller.Library.prototype.initialize.apply( this, arguments );
					}
				});
				wp.media.controller.GalleryEdit = wp.media.controller.GalleryEdit.extend({
					activate: function() {
						var library = this.get('library');
						if (manager.mimeTypes) {
							library.props.set( 'type', manager.mimeTypes );
						}
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
				if (this.imageIds && this.imageIds.length) {
					this.frame = wp.media.gallery.edit('[gallery ids="'+this.imageIds.join(",")+'"]');
				} else {
					this.frame = wp.media({
						frame: "post",
						state: "gallery-edit",
						type: this.mimeTypes,
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

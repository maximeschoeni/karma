function createImageUploader() {
	var manager = {
		addFrame: null,
		imageId: null,
		mediaType: null,
		open: function () {
			if (!this.addFrame) {
				var args = {
					title: "Select file",
					button: {
						text: "Use this file"
					},
					library: {
            type: this.mediaType || null //'application/font-woff'
        	},
					multiple: false
				};
				// if (this.mediaType) {
				// 	args["library"] = {
        //     type: this.mediaType
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

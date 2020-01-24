if (!window.KarmaBackgroundImage) {
	KarmaBackgroundImage = {};
}

KarmaBackgroundImage.create = function(sources, size, position) {
	if (window.createBackgroundImage) {
		return window.createBackgroundImage(sources, size, position)
			|| build("div.background-image", function() {
				this.style.backgroundImage = "url("+sources[0].src+")";
				this.style.backgroundSize = size;
				this.style.backgroundPosition = position;
			});
	} else {
		KarmaBackgroundImage.createImage(sources, size, position)
	}
}

KarmaBackgroundImage.createImage = function(sources, size, position) {
	if (sources.length) {
		if ('objectFit' in document.documentElement.style) {
			var image = new Image();
			image.src = sources[0].src;
			image.srcset = sources.map(function(source) {
				return source.src+" "+source.width+"w";
			}).join(",");
			image.style.objectFit = size;
			image.style.objectPosition = position;
			image.style.width = "100%";
			image.style.height = "100%";
			return image;
		} else {
			var div = document.createElement("div");
			div.style.backgroundImage = "url("+sources[0].src+")";
			div.style.backgroundSize = size;
			div.style.backgroundPosition = position;
			div.style.width = "100%";
			div.style.height = "100%";
			return div;
		}
	}
}

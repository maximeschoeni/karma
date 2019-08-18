if (!window.KarmaBackgroundImage) {
	KarmaBackgroundImage = {};
}

KarmaBackgroundImage.create = function(sources, size, position) {
	return window.createBackgroundImage && window.createBackgroundImage(sources, size, position)
		|| build("div.background-image", function() {
			this.style.backgroundImage = "url("+sources[0].url+")";
			this.style.backgroundSize = size;
			this.style.backgroundPosition = position;
		});
}

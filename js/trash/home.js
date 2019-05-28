
document.addEventListener("DOMContentLoaded", function() {
	var slideshows = document.querySelectorAll(".grid .slideshow");
	for (var i = 0; i < slideshows.length; i++) {
		registerGridSlideshow(slideshows[i]);
	}
});

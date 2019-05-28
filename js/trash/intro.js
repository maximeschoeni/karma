document.addEventListener("DOMContentLoaded", function() {
	var container = document.getElementById("intro");
	var slideshow = document.getElementById("intro-slideshow");
	var isOpen = document.body.classList.contains("intro-open");
	function close() {
		if (isOpen) {
			slideshow.dispatchEvent(new CustomEvent("remove"));
			TinyAnimate.animate(container.clientHeight, 0, 300, function(value) {
				container.style.height = value.toFixed() + "px";
			}, "easeInOutSine", function() {
				container.parentNode.removeChild(container);
				document.body.classList.remove("intro-open");
			});
			isOpen = false;
		}
	}
	if (isOpen && container && slideshow) {
		registerIntroSlideshow(slideshow);
		slideshow.addEventListener("wheel", function(event) {
			event.preventDefault();
			close();
		});
		slideshow.addEventListener("click", function(event) {
			close();
		});
		slideshow.addEventListener("touchstart", function(event) {
			close();
		});
		document.addEventListener("keydown", function(event) {
			if (event.keyCode === 37) {
				player.prev();
			} else if (event.keyCode === 39) {
				player.next();
			} else {
				close();
			}
		});
	}
});


function registerIntroSlideshow(container) {
	var library = container.querySelector(".library");
	var controller = container.querySelector(".controller");
	var player = createMediaPlayer();
	function preloadNext() {
		player.getAdjacentSlides(2).forEach(function(slide) {
			var image = slide.querySelector(".image");
			if (image) {
				image.dispatchEvent(new CustomEvent("preload", {detail: {
					width: container.clientWidth,
					height: container.clientHeight,
				}}));
			}
		});
	}

	player.duration = 0;
	player.easing = "easeInOutSine";
	player.timerDuration = 1500;

	while (library.children.length) {
		player.addSlide(library.children[0]);
		library.removeChild(library.children[0]);
	}

	if (controller) {


	}



	player.onAppend = function(slide) {
		container.appendChild(slide);
		var image = slide.querySelector(".image");
		if (image) {
			image.dispatchEvent(new CustomEvent("update"));
		}
	}
	player.onRemove = function(slide) {
		container.removeChild(slide);
	}
	player.onRenderSlide = function(slide, value, dir) {
		//slide.style.left = (value*container.clientWidth).toFixed() + "px";
		slide.style.opacity = (1 - Math.abs(value)).toFixed(4);
	}
	player.onPlay = function() {
		player.next();
	}
	player.onComplete = function() {
		preloadNext();
	}
	player.onInit = function() {
		preloadNext();
	}
	container.addEventListener("remove", function(event) {
		player.pause();
	});

	player.init();
	player.play();

}

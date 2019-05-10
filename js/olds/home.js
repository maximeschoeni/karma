function registerHomeSlideshow(container, library, controller) {
	var headlineIndex = 0;
	var headlines = ['Brand and art Direction', 'Strategy and Photography'];
	
	
	if (library.children.length === 1) {
		var slide = library.children[0];
		container.appendChild(slide);
		slide.querySelector(".image").dispatchEvent(new CustomEvent("update"));
		container.removeChild(controller);
		return;
	}
	
	
	var player = createMediaPlayer();
	player.duration = 600;
	player.easing = "easeInOutSine";
	player.timerDuration = 4500;
	player.sleepTimerDuration = 10000;
	
	function preloadNext() {
		player.getAdjacentSlides(1).forEach(function(slide) {
			slide.dispatchEvent(new CustomEvent("preload", {detail: {
				width: container.clientWidth,
				height: container.clientHeight,
			}}));
		});
	}
	function setNextHeadline(nextSlide) {
		var h1 = nextSlide.querySelector("h1");
		if (h1) {
			h1.innerHTML = headlines[headlineIndex];
			headlineIndex++;
			if (headlineIndex >= headlines.length) {
				headlineIndex = 0;
			}
		}
	}
	
	if (controller) {
		registerSwipe(controller, {mouseEmulation: false});
		var lastT = 0;
// 		controller.addEventListener("click", function(event) {
// 			var t = new Date().getTime();
// 			player.duration = Math.min(t - lastT, 300);
// 			player.change(1);
// 			lastT = t;
// 		});
		
		var leftZone = controller.querySelector(".left-zone");
		var rightZone = controller.querySelector(".right-zone");
		if (leftZone) {
			leftZone.addEventListener("click", function(event) {
				event.preventDefault();
				player.prev();
				setNextHeadline(player.getCurrentSlide());
				player.sleep();
			});
		}
		if (rightZone) {
			rightZone.addEventListener("click", function(event) {
				event.preventDefault();
				player.next();
				setNextHeadline(player.getCurrentSlide());
				player.sleep();
			});
		}
		
		controller.addEventListener("swipemove", function(event) {
			if (Math.abs(event.detail.deltaX) > Math.abs(event.detail.deltaY)) {
				player.offset = event.detail.deltaX/container.clientWidth/2;
				player.shift();
			}
			player.sleep();
		}); 
		controller.addEventListener("swipe", function(event) {
			if (Math.abs(event.detail.deltaX) > Math.abs(event.detail.deltaY) && Math.abs(event.detail.deltaX) >= Math.abs(event.detail.maxDX)) {
				player.change();
				setNextHeadline(player.getCurrentSlide());
			} else {
				player.back();
			}
			player.sleep();
		});		
	}
	
	document.addEventListener("keydown", function(event) {
		if (event.keyCode === 37) {
			player.prev();
			setNextHeadline(player.getCurrentSlide());
		} else if (event.keyCode === 39) {
			player.next();
			setNextHeadline(player.getCurrentSlide());
		}
		player.sleep();
	});
	
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
	player.onRenderSlide = function(slide, value, dir, isCurrent) {
		//slide.style.left = (value*container.clientWidth).toFixed() + "px";
		slide.style.opacity = (1 - Math.abs(value)).toFixed(4);
	}
	player.onSet = function() {
		player.next();
		setNextHeadline(player.getCurrentSlide());
	}
	player.onPlay = function() {
		player.next();
		setNextHeadline(player.getCurrentSlide());
	}
	player.onAnimComplete = function() {
		preloadNext();
	}
	player.onSet = function() {
		preloadNext();
	}
	
	window.addEventListener("focus", function() {
		player.play();
	});
	window.addEventListener("blur", function() {
		player.pause();
	});
		
	while (library.children.length) {
		player.addSlide(library.children[0]);
		library.removeChild(library.children[0]);
	}
	
	container.addEventListener("update", function(event) {
		player.setIndex();
		setNextHeadline(player.getCurrentSlide());
	});
	
	player.setIndex();
	setNextHeadline(player.getCurrentSlide());
	player.play();
	
}



document.addEventListener("DOMContentLoaded", function() {
	var slideshow = document.getElementById("main-slideshow");
	var library = document.getElementById("main-slideshow-library");
	var controller = document.getElementById("main-slideshow-controller");
	
	if (slideshow && library && controller) {
		registerHomeSlideshow(slideshow, library, controller);
	}
});

document.addEventListener("DOMContentLoaded", function() {
	var grid = document.getElementById("main-grid");
	var library = document.getElementById("main-grid-library");
	if (grid && library) {
		var manager = registerGrid(grid, library, function() {
			if (window.innerWidth < 800) {
				this.grid.numCol = 1;
				this.grid.marginH = 5;
				this.grid.marginV = 5;
			} else {
				this.grid.numCol = 2;
				this.grid.marginH = 10;
				this.grid.marginV = 10;
			}
		});
		manager.update();
	}
});

document.addEventListener("DOMContentLoaded", function() {
	var homeMenu = document.querySelector(".home-header .menu");
	if (homeMenu) {
		registerSticky(homeMenu);
	}
});
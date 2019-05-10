function registerGridSlideshow(container) {
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

	if (library.children.length === 1) {
		container.addEventListener("update", function(event) {
			var image = library.querySelector(".image");
			if (image) {
				image.dispatchEvent(new CustomEvent("update"));
			}
		});
		return;
	}
	player.duration = 400;
	player.easing = "easeInOutSine";
	player.timerDuration = 4500;
	player.sleepTimerDuration = 16000;

	while (library.children.length) {
		player.addSlide(library.children[0]);
		library.removeChild(library.children[0]);
	}

	if (controller) {
		// controller.appendChild(build("ul.numerotation", player.ids.map(function(id) {
		// 	return build("li", function(element) {
		// 		this.addEventListener("click", function(event) {
		// 			event.preventDefault();
		// 			event.stopPropagation();
		// 			player.changeSlide(id);
		// 			player.sleep();
		// 		});
		// 		controller.addEventListener("update", function() {
		// 			//if (id === player.currentId) {
		// 			var currentIndex = Math.round(player.ids.indexOf(player.currentId) - player.offset);
		// 			var currentIndex = player.cycle(currentIndex, player.ids.length);
		// 			if (id === player.ids[currentIndex]) {
		// 				element.classList.add("active");
		// 			} else {
		// 				element.classList.remove("active");
		// 			}
		// 		});
		// 	});
		// })));

		// controller.addEventListener("click", function(event) {
		// 	player.sleep();
		// 	player.next();
		// });



		var swipeManager = createSwipeManager();
		swipeManager.trackMouse = true;
		swipeManager.element = controller;
		swipeManager.init();
		//registerSwipe(controller, {mouseEmulation: true});

		swipeManager.onmove = function(event) {
			if (swipeManager.absDX > swipeManager.absDY) {
				player.offset = swipeManager.deltaX/container.clientWidth/2;
				player.render();
			} else {
				event.preventDefault();
			}
			player.sleep();
		};
		swipeManager.onswipe = function(event) {
			if (swipeManager.absDX > swipeManager.absDY && swipeManager.absDX >= swipeManager.maxDX) {
				player.change();
			} else {
				player.back();
			}

			// click
			if (!swipeManager.absDX && !swipeManager.absDY) {
				var currentSlide = player.getCurrentSlide();
				if (currentSlide) {
					var a = currentSlide.querySelector("a");
					if (a) {
						window.location.href = a.href;
					}
				}
			}
			player.sleep();
		};



		// controller.addEventListener("swipemove", function(event) {
		// 	if (Math.abs(event.detail.deltaX) > Math.abs(event.detail.deltaY)) {
		// 		player.offset = event.detail.deltaX/container.clientWidth/2;
		// 		player.render();
		// 	}
		// 	player.sleep();
		// });
		// controller.addEventListener("swipe", function(event) {
		// 	if (Math.abs(event.detail.deltaX) > Math.abs(event.detail.deltaY) && Math.abs(event.detail.deltaX) >= Math.abs(event.detail.maxDX)) {
		// 		player.change();
		// 	} else {
		// 		player.back();
		// 	}
		// 	player.sleep();
		// });

	}





	// container.addEventListener("mouseenter", function(event) {
	// 	player.pause();
	// 	function mousemove(event) {
	// 		var box = container.getBoundingClientRect();
	// 		player.offset = -(player.ids.length-1)*(event.clientX - box.left)/box.width;
	// 		player.render();
	// 		controller.dispatchEvent(new CustomEvent("update"));
	// 	}
	// 	function mouseleave(event) {
	// 		player.back();
	// 		player.sleep();
	// 		container.removeEventListener("mousemove", mousemove);
	// 		container.removeEventListener("mouseleave", mouseleave);
	//
	// 	}
	// 	container.addEventListener("mousemove", mousemove);
	// 	container.addEventListener("mouseleave", mouseleave);
	// });


	document.addEventListener("keydown", function(event) {
		if (event.keyCode === 37) {
			player.prev();
		} else if (event.keyCode === 39) {
			player.next();
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
	player.onRenderSlide = function(slide, value, dir) {
		slide.style.left = (value*container.clientWidth).toFixed() + "px";
// 		slide.style.opacity = (1 - Math.abs(value)).toFixed(4);
	}
	player.onPlay = function() {
		player.next();
	}
	player.onComplete = function() {
		controller.dispatchEvent(new CustomEvent("update"));
		preloadNext();
	}
	player.onInit = function() {
		controller.dispatchEvent(new CustomEvent("update"));
		preloadNext();
	}
	window.addEventListener("focus", function() {
		player.play();
	});
	window.addEventListener("blur", function() {
		player.pause();
	});



	container.addEventListener("update", function(event) {
		player.init();
	});
	container.addEventListener("remove", function(event) {
		player.pause();
	});

	player.currentId = player.ids[0];
	player.init();
	player.play();

}

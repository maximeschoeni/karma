// function registerGrid(container, library, setGridNumCol) {	
// 	var manager = {
// 		grid: createCellsGridSystem(),
// 		update: function() {
// 			grid.dimension = 2/3;
// 			grid.marginH = 10;
// 			grid.marginV = 10;
// 			grid.width = container.clientWidth;
// 			grid.numCol = setGridNumCol.apply(this);
// 			grid.update();
// 			container.style.height = grid.height.toFixed()+"px";
// 	
// 			if (grid.width !== container.clientWidth) {
// 				grid.width = container.clientWidth;
// 				grid.update();
// 				container.style.height = grid.height.toFixed()+"px";
// 			}
// 		},
// 	};
// 	manager.grid.onAppend = function(element, rect) {
// 		container.appendChild(element);
// 		element.style.top = rect.top+"px";
// 		element.style.left = rect.left+"px";
// 		element.style.width = rect.width+"px";
// 		element.style.height = rect.height+"px";
// 		element.dispatchEvent(new CustomEvent("update"));
// 	};
// 	manager.grid.onRemove = function(element) {
// 		container.removeChild(element);
// 	};
// 	window.addEventListener("resize", function() {
// 		manager.update();
// 	});	
// 	while (library.children.length) {
// 		var element = library.children[0];
// 		var col = parseInt(element.getAttribute("data-col") || 1);
// 		var row = parseInt(element.getAttribute("data-row") || 1);
// 		library.removeChild(element);
// 		manager.grid.add(element, col, row);
// 	}
// 	manager.update();
// 	return manager;
// }


document.addEventListener("DOMContentLoaded", function() {
	var grid = document.getElementById("project-grid");
	var library = document.getElementById("project-grid-library");
	
// 	function registerGrid(container, library, setGridNumCol) {
// 		var grid = createCellsGridSystem();	
// 		var currentCategory;
// 		function update() {
// 			grid.dimension = 2/3;
// 			grid.marginH = 10;
// 			grid.marginV = 10;
// 			grid.width = container.clientWidth;
// 			grid.numCol = setGridNumCol.apply(this);
// // 			if (window.innerWidth < 800) {
// // 				grid.numCol = 1;
// // 			} else {
// // 				grid.numCol = 2;
// // 			}
// 			
// 			grid.update();
// 			container.style.height = grid.height.toFixed()+"px";
// 		
// 			if (grid.width !== container.clientWidth) {
// 				grid.width = container.clientWidth;
// 				grid.update();
// 				container.style.height = grid.height.toFixed()+"px";
// 			}
// 		}
// 		grid.onAppend = function(element, rect) {
// 			container.appendChild(element);
// 			element.style.top = rect.top+"px";
// 			element.style.left = rect.left+"px";
// 			element.style.width = rect.width+"px";
// 			element.style.height = rect.height+"px";
// 			element.dispatchEvent(new CustomEvent("update"));
// 		};
// 		grid.onRemove = function(element) {
// 			container.removeChild(element);
// 		};
// 		grid.filter = function(element) {
// 			var categories = (element.getAttribute("data-category") || "").split(" ");
// 			return categories.some(function(category) {
// 				return !currentCategory || category === currentCategory;
// 			});
// 		};
// 		window.addEventListener("resize", function() {
// 			update();
// 		});	
// 		while (library.children.length) {
// 			var element = library.children[0];
// 			var col = parseInt(element.getAttribute("data-col") || 1);
// 			var row = parseInt(element.getAttribute("data-row") || 1);
// 			library.removeChild(element);
// 			grid.add(element, col, row);
// 		}
// 		update();
// 	}
	
	function registerSlideshow(container, parentCell) {
		var library = container.querySelector(".library");
		var controller = container.querySelector(".controller");
		var player = createMediaPlayer();	
		function preloadNext() {
			player.getAdjacentSlides(1).forEach(function(slide) {
				slide.dispatchEvent(new CustomEvent("preload", {detail: {
					width: container.clientWidth,
					height: container.clientHeight,
				}}));
			});
		}
		
		player.duration = 600;
		player.easing = "easeInOutSine";
		player.timerDuration = 4500;
		player.sleepTimerDuration = 10000;
	
		if (controller) {
			registerSwipe(controller, {mouseEmulation: false});
			var leftZone = controller.querySelector(".left-zone");
			var rightZone = controller.querySelector(".right-zone");
			if (leftZone) {
				leftZone.addEventListener("click", function(event) {
					event.preventDefault();
					player.prev();
					player.sleep();
				});
			}
			if (rightZone) {
				rightZone.addEventListener("click", function(event) {
					event.preventDefault();
					player.next();
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
				} else {
					player.back();
				}
				player.sleep();
			});		
		}
	
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
		player.onRenderSlide = function(slide, value, dir, index) {
			if (index === player.currentIndex) {
				slide.style.opacity = (1 - Math.abs(value)).toFixed(4);
			}
		}
		player.onSet = function() {
			player.next();
		}
		player.onPlay = function() {
			player.next();
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
	
		
		parentCell.addEventListener("append", function(event) {
			player.setIndex();
			player.play();
		});
		parentCell.addEventListener("remove", function(event) {
			player.pause();
		});
	
// 		player.setIndex();
// 		player.play();
		
	}
	
	
	if (grid && library) {
		
// 		var slideshows = library.querySelectorAll(".slideshow");
// 		for (var i = 0; i < slideshows.length; i++) {
// 			registerSlideshow(slideshows[i]);
// 		}
		for (var i = 0; i < library.children.length; i++) {
			var slideshow = library.children[i].querySelector(".slideshow");
			if (slideshow) {
				registerSlideshow(slideshow, library.children[i]);
			}
		}
		
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
		
		
// 		manager.onAdd = function(element) {
// 			
// 			var slideshow = element.querySelector(".slideshow");
// 			if (slideshow) {
// 				var library = slideshow.querySelector(".library");
// 				var controller = slideshow.querySelector(".controller");
// 				if (library && controller) {
// 					registerSlideshow(slideshow, library, controller);
// 				}
// 			}
// 		};
		manager.update();
	}
	
	
	var moreProjectGrid = document.getElementById("more-project-grid");
	var moreProjectLibrary = document.getElementById("more-project-grid-library");
	
	if (moreProjectGrid && moreProjectLibrary) {
		var manager = registerGrid(moreProjectGrid, moreProjectLibrary, function() {
			if (window.innerWidth < 800) {
				this.grid.numCol = 1;
			} else {
				this.grid.numCol = 3;
			}
		});
		manager.update();
	}
	
	
});	


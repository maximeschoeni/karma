var mobileWidth = 800;

function registerProjectGrid(container, library, filterLinks, onUpdate) {
	var manager = registerGrid(container, library, onUpdate);	
	var currentCategory;
	function registerLink(link) {
		link.addEventListener("click", function(event) {
			event.preventDefault();
			currentCategory = this.getAttribute("data-category");
			manager.update();
			container.dispatchEvent(new CustomEvent("update"));
			window.dispatchEvent(new CustomEvent("image-anim"));
		});
		container.addEventListener("update", function(event) {
			if (currentCategory === link.getAttribute("data-category")) {
				link.classList.add("active");
			} else {
				link.classList.remove("active");
			}
		});
	}
	
	manager.grid.filter = function(element) {
// 		if (window.innerWidth < mobileWidth && element.classList.contains("placeholder")) {
// 			return false;
// 		}
		var categories = (element.getAttribute("data-category") || "").split(" ");
		return categories.some(function(category) {
			return !currentCategory || category === currentCategory;
		});
	};
	for (var i = 0; i < filterLinks.length; i++) {
		registerLink(filterLinks[i]);
	}
	if (window.location.hash) {
		currentCategory = window.location.hash.slice(1);
	}
	container.dispatchEvent(new CustomEvent("update"));
	return manager;
}


document.addEventListener("DOMContentLoaded", function() {
	var slideshow = document.getElementById("projects-grid");
	var library = document.getElementById("projects-grid-library");
	var categoryNav = document.getElementById("projects-categories");
	
	if (slideshow, library, categoryNav) {
		var categoryLinks = categoryNav.children;
		var manager = registerProjectGrid(slideshow, library, categoryLinks, function() {
			if (window.innerWidth < mobileWidth) {
				this.grid.numCol = 1;
				this.grid.marginH = 5;
				this.grid.marginV = 5;
			} else {
				this.grid.numCol = 3;
				this.grid.marginH = 10;
				this.grid.marginV = 10;
			}
		});
		manager.update();
	}
});	


// function registerProjectGrid(container, library, filterLinks) {
// 	var grid = createCellsGridSystem();	
// 	var currentCategory;
// 	function update() {
// 		grid.dimension = 2/3;
// 		grid.marginH = 10;
// 		grid.marginV = 10;
// 		grid.width = container.clientWidth;
// 		if (window.innerWidth < 800) {
// 			grid.numCol = 1;
// 		} else {
// 			grid.numCol = 3;
// 		}
// 		grid.update();
// 		container.style.height = grid.height.toFixed()+"px";
// 		
// 		if (grid.width !== container.clientWidth) {
// 			grid.width = container.clientWidth;
// 			grid.update();
// 			container.style.height = grid.height.toFixed()+"px";
// 		}
// 		
// 		window.dispatchEvent(new CustomEvent("image-anim"));
// 	}
// 	grid.onAppend = function(element, rect) {
// 		container.appendChild(element);
// 		element.style.top = rect.top+"px";
// 		element.style.left = rect.left+"px";
// 		element.style.width = rect.width+"px";
// 		element.style.height = rect.height+"px";
// 		element.dispatchEvent(new CustomEvent("update"));
// 	};
// 	grid.onRemove = function(element) {
// 		container.removeChild(element);
// 	};
// 	grid.filter = function(element) {
// 		var categories = (element.getAttribute("data-category") || "").split(" ");
// 		return categories.some(function(category) {
// 			return !currentCategory || category === currentCategory;
// 		});
// 	};
// 	window.addEventListener("resize", function() {
// 		update();
// 	});	
// 	while (library.children.length) {
// 		var element = library.children[0];
// 		var col = parseInt(element.getAttribute("data-col") || 1);
// 		var row = parseInt(element.getAttribute("data-row") || 1);
// 		library.removeChild(element);
// 		grid.add(element, col, row);
// 	}
// 	if (filterLinks) {
// 		for (var i = 0; i < filterLinks.length; i++) {
// 			filterLinks[i].addEventListener("click", function(event) {
// 				event.preventDefault();
// 				currentCategory = this.getAttribute("data-category");
// 				update();
// 			});
// 		}
// 	}
// 	
// 	if (window.location.hash) {
// 		currentCategory = window.location.hash.slice(1);
// 	}
// 	
// 	update();
// 	return grid;
// }

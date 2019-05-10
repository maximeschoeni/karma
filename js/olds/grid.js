function registerGrid(container, library, onUpdate) {	
	var manager = {
		grid: createCellsGridSystem(),
		update: function() {
			this.grid.dimension = 2/3;
			this.grid.marginH = 10;
			this.grid.marginV = 10;
			this.grid.width = container.clientWidth;
			this.grid.numCol = 2;
			
			if (onUpdate) {
				onUpdate.apply(this);
			}
			
			this.grid.update();
			container.style.height = this.grid.height.toFixed()+"px";
			
			if (this.grid.width !== container.clientWidth) {
				this.grid.width = container.clientWidth;
				this.grid.update();
				container.style.height = this.grid.height.toFixed()+"px";
			}
		}
	};
	manager.grid.onAppend = function(element, rect) {
		container.appendChild(element);
		element.style.top = rect.top+"px";
		element.style.left = rect.left+"px";
		element.style.width = rect.width+"px";
		element.style.height = rect.height+"px";
		var image = element.querySelector(".image");		
		if (image) {
			image.dispatchEvent(new CustomEvent("update"));
		}
		element.dispatchEvent(new CustomEvent("append"));
	};
	manager.grid.onRemove = function(element) {
		container.removeChild(element);
		element.dispatchEvent(new CustomEvent("remove"));
	};
	window.addEventListener("resize", function() {
		manager.update();
	});	
	while (library.children.length) {
		var element = library.children[0];
		var col = parseInt(element.getAttribute("data-col") || 1);
		var row = parseInt(element.getAttribute("data-row") || 1);
		library.removeChild(element);
		manager.grid.add(element, col, row);
		
	}
	return manager;
}


// 
// function registerGrid(container, library, filterLinks) {
// 	
// 	var grid = createCellsGridSystem();	
// 	var currentCategory;
// 	
// 	function update() {
// 		grid.dimension = 2/3;
// 		grid.marginH = 10;
// 		grid.marginV = 10;
// 		grid.width = container.clientWidth;
// 		
// 		if (window.innerWidth < 800) {
// 			grid.numCol = 1;
// 		} else {
// 			grid.numCol = 2;
// 		}
// 		
// 		grid.update();
// 		
// 		container.style.height = grid.height.toFixed()+"px";
// 	}
// 	
// 	grid.onAppend = function(element, rect) {
// 		container.appendChild(element);
// 		
// 		element.style.top = rect.top+"px";
// 		element.style.left = rect.left+"px";
// 		element.style.width = rect.width+"px";
// 		element.style.height = rect.height+"px";
// 		
// 		console.log(rect);
// 		
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
// 	
// 	window.addEventListener("resize", function() {
// 		
// 		update();
// 	});
// 	
// 	
// 	while (library.children.length) {
// 		var element = library.children[0];
// 		var col = parseInt(element.getAttribute("data-col") || 1);
// 		var row = parseInt(element.getAttribute("data-row") || 1);
// 		library.removeChild(element);
// 		grid.add(element, col, row);
// 	}
// 	
// 	if (filterLinks) {
// 		for (var i = 0; i < filterLinks.length; i++) {
// 			filterLinks[i].addEventListener("click", function(event) {
// 				event.preventDefault();
// 				currentCategory = this.getAttribute("data-category");
// 				grid.update();
// 			});
// 		}
// 	}
// 	
// 	update();
// 	
// 	return grid;
// }

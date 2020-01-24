
function createSortableManager() {
	var draggedElement, placeholder, offsetX, offsetY;
	var manager = {
		items: [],
		container: null,
		checkTarget: function(target) {
			return this.items.indexOf(target) > -1;
		},
		createPlaceholder: function(draggedElement) {
			return draggedElement.cloneNode(true);
		},
		addItem: function(element) {
			this.items.push(element);
		},
		getBox: function(element) {
			return {
				width: element.clientWidth,
				height: element.clientHeight
			};
		},
		createMap: function(itemElements, containerElement) {
			var map = [];
			var containerBox = manager.getBox(containerElement);
				x = 0,
				y = 0,
				height = 0;
			for (var i = 0; i < itemElements.length; i++) {
				var itemBox = this.getBox(itemElements[i]);
				itemBox.index = i;
				if (x + itemBox.width > containerBox.width) {
					x = 0;
					y = height;
				}
				itemBox.x = x;
				itemBox.y = y;
				x += itemBox.width;
				height = y + itemBox.height;
				map.push(itemBox);
			}
			return map;
		},
		findIndex: function(map, x, y) {
			for (var i = 0; i < map.length; i++) {
				var itemBox = map[i];
				if (y < itemBox.y + itemBox.height && (y < itemBox.y || x < itemBox.x + itemBox.width)) {
					return itemBox.index;
				}
			}
			return map.length;
		},
		test: function(draggedElement, clientX, clientY, onChange) {
			var rect = this.container.getBoundingClientRect();
			if (clientX > rect.x && clientX < rect.right && clientY > rect.y && clientY < rect.bottom) {
				var draggedIndex = this.items.indexOf(draggedElement);
				var items = this.items.filter(function(item) {
					return (item !== draggedElement);
				});
				var map = this.createMap(items, this.container);
				var index = this.findIndex(map, clientX - rect.x, clientY - rect.y);
				if (index !== draggedIndex) {
					this.items.splice(draggedIndex, 1);
					this.items.splice(index, 0, draggedElement);
					if (onChange) {
						onChange(draggedIndex, index);
					}
				}
			}
		},
		remove: function() {
			document.removeEventListener("mousedown", onMouseDown);
		},
		reset: function() {
			this.items = [];
		}
	};

	function onMouseDown(event) {
		if (manager.checkTarget(event.target)) { //event.target.classList.contains("media-box-cell")
			event.preventDefault();
			draggedElement = event.target.parentNode;
			//placeholder = draggedElement.cloneNode(true); //tag("tr.media-box-row.placeholder", tag("td"), tag("td"), tag("td"), tag("td"));
			placeholder = manager.createPlaceholder(draggedElement);
			var box = draggedElement.getBoundingClientRect();
			offsetX = event.clientX - box.left;
			offsetY = event.clientY - box.top;
			draggedElement.parentNode.insertBefore(placeholder, draggedElement);
			draggedElement.parentNode.appendChild(draggedElement);
			draggedElement.style.width = box.width.toFixed()+"px";
			draggedElement.style.height = box.height.toFixed()+"px";
			draggedElement.style.left = (event.clientX - offsetX) + "px";
			draggedElement.style.top = (event.clientY - offsetY) + "px";
			draggedElement.style.position = "fixed";
			if (manager.onStartDrag) {
				manager.onStartDrag(draggedElement, placeholder, box);
			}
			this.addEventListener("mousemove", onMouseMove);
			this.addEventListener("mouseup", onMouseUp);
		}
	}
	function onMouseMove(event) {
		event.preventDefault();
		draggedElement.style.left = (event.clientX - offsetX) + "px";
		draggedElement.style.top = (event.clientY - offsetY) + "px";
		var x = event.clientX - offsetX + draggedElement.clientWidth/2;
		var y = event.clientY - offsetY + draggedElement.clientHeight/2;
		manager.test(draggedElement, x, y, function(dragFrom, dragTo) {
			manager.container.removeChild(placeholder);
			manager.container.insertBefore(placeholder, manager.container.children[dragTo]);
			if (manager.onStartDrag) {
				manager.onChange(dragFrom, dragTo);
			}
		});
	}
	function onMouseUp(event) {
		if (draggedElement && placeholder) {
			draggedElement.style.removeProperty("width");
			draggedElement.style.removeProperty("height");
			draggedElement.style.removeProperty("left");
			draggedElement.style.removeProperty("top");
			draggedElement.style.removeProperty("position");
			draggedElement.parentNode.insertBefore(draggedElement, placeholder);
			placeholder.parentNode.removeChild(placeholder);
			if (manager.onEndDrag) {
				manager.onEndDrag(draggedElement, placeholder);
			}
			draggedElement = null;
			placeholder = null;
		}
		this.removeEventListener("mouseup", onMouseUp);
		this.removeEventListener("mousemove", onMouseMove);
	}
	document.addEventListener("mousedown", onMouseDown);
	return manager;
}

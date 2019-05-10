/**
 * Grid system ("columns" mode). "Cells" mode below.
 */
function createGridSystem() {
	var items = [];
	return {
		marginH: 0,
		marginV: 0,
		width: 0, // must be set
		numCol: 1, // must be set
		duration: 300,
		easing: "easeInOutSine",
		add: function(element, col, row) {
			items.push({
				element: element,
				col: col,
				row: row,
				index: items.length
			});
		},
		getItems: function() {
			var gridSystem = this;
			var filteredItems = items.filter(function(item) {
				return gridSystem.filter ? gridSystem.filter(item.element) : true;
			});
			if (this.sort) {
				filteredItems.sort(this.sort);
			}
			if (this.forEach) {
				filteredItems.forEach(this.forEach);
			}
			return filteredItems;
		},
		update: function() {		
			if (this.currentGrid) {
				for (var index in this.currentGrid.rectsMap) {
					if (this.onRemove) {
						this.onRemove(items[index].element);
					}
				}
			}
			this.currentGrid = this.createGrid();
			
			this.height = this.currentGrid.height;
			for (var index in this.currentGrid.rectsMap) {
				var rect = this.currentGrid.rectsMap[index];
				if (this.onAppend) {
					this.onAppend(items[index].element, rect);
				}
			}
		},
		transform: function(callback) {
			var gridSystem = this;
			var newGrid = this.createGrid();
			this.height = Math.max(this.height, newGrid.height);
			for (var index in newGrid.rectsMap) {
				if (!this.currentGrid || this.compareGridsItem(newGrid, this.currentGrid, index) === "arrive") {
					if (this.onAppend) {
						this.onAppend(items[index].element, newGrid.rectsMap[index]);
					}
					if (this.onArrive) {
						this.onArrive(items[index].element, newGrid.rectsMap[index], 0);
					}
				}
			}
			TinyAnimate.animate(0, 1, this.duration, function(value) {
				for (var i = 0; i < items.length; i++) {
					var action = gridSystem.compareGridsItem(newGrid, gridSystem.currentGrid, i);
					if (action === "arrive" && gridSystem.onArrive) {
						gridSystem.onArrive(items[i].element, newGrid.rectsMap[i], value);
					} else if (action === "leave" && gridSystem.onLeave) {
						gridSystem.onLeave(items[i].element, gridSystem.currentGrid.rectsMap[i], value);
					} else if (action === "move" && gridSystem.onMove) {
						gridSystem.onMove(items[i].element, gridSystem.currentGrid.rectsMap[i], newGrid.rectsMap[i], value);
					}
				}
			}, this.easing, function() {
				gridSystem.height = newGrid.height;
				for (var index in gridSystem.currentGrid.rectsMap) {
					var action = gridSystem.compareGridsItem(newGrid, gridSystem.currentGrid, index);
					if (action === "leave" && gridSystem.onRemove) {
						gridSystem.onRemove(items[index].element);
					}
				}
				gridSystem.currentGrid = newGrid;
				if (callback) {
					callback();
				}
			});
		},
		compareGridsItem: function(newGrid, oldGrid, itemIndex) {
			if (newGrid.rectsMap[itemIndex] && oldGrid.rectsMap[itemIndex]) {
				return "move";
			} else if (newGrid.rectsMap[itemIndex]) {
				return "arrive";
			} else if (oldGrid.rectsMap[itemIndex]) {
				return "leave";
			} 
			return "sleep";
		},
		createGrid: function() {
			var filteredItems = this.getItems();
			var marginH = this.marginH;
			var marginV = this.marginV;
			var colWidth = this.width/this.numCol;
			var grid = {
				numCol: this.numCol,
				height: 0,
				columns: [],
				lengths: [],
				rectsMap: {},
				indexes: []
			};
			function getShortest() {
				var min = Infinity;
				var shortest = 0;
				for (var i = 0; i < grid.lengths.length; i++) {
					if (grid.lengths[i] < min) {
						min = grid.lengths[i];
						shortest = i;
					}
				}
				return shortest;
			}
			function getLongest() {
				var max = 0;
				var longest = 0;
				for (var i = 0; i < grid.lengths.length; i++) {
					if (grid.lengths[i] > max) {
						max = grid.lengths[i];
						longest = i;
					}
				}
				return longest;
			}
			
			for (var i = 0; i < this.numCol; i++) {
				grid.columns.push([]);
				grid.lengths.push(0);
			}
			if (colWidth > 0) {
				for (var i = 0; i < filteredItems.length; i++) {
					var item = filteredItems[i];
					var shortestColumn = getShortest();
					var top = grid.lengths[shortestColumn];
					var width = colWidth - this.marginH*2;
					var height = width*item.row/item.col;					
					var rect = {
						left: shortestColumn*colWidth + this.marginH,
						top: grid.lengths[shortestColumn] + this.marginV,
						width: width,
						height: height
					};
					grid.lengths[shortestColumn] += height + 2*this.marginV;
					grid.columns[shortestColumn].push(item.index);
					grid.height = grid.lengths[getLongest()];
					grid.rectsMap[item.index] = rect;
					grid.indexes.push(item.index);
				}
			}
			return grid;
		}
	};
}




/**
 * "Cells" Grid system
 */
function createCellsGridSystem() {
	var gridSystem = createGridSystem();
	gridSystem.dimension = 2/3;
	gridSystem.createGrid = function() {
		var filteredItems = this.getItems();
		var marginH = this.marginH;
		var marginV = this.marginV;
		var colWidth = Math.round(this.width/this.numCol);
		var rowHeight = Math.round(colWidth*this.dimension);
		var offset = this.width - colWidth*this.numCol; // not used!
		var grid = {
			numCol: this.numCol,
			numRow: 0,
			height: 0,
			placesMap: {},
			rectsMap: {},
			boxesMap: {},
			indexes: []
		};		
			
		function hasRoom(place, col, row) {
			if (place%grid.numCol + col > grid.numCol) {
				return false;
			}
			for (var i = 0; i < col; i++) {
				for (var j = 0; j < row; j++) {
					if (grid.placesMap[place + i + j*grid.numCol]) {
						return false;
					}
				}
			}
			return true;
		};
		function add(place, col, row) {
			for (var i = 0; i < col; i++) {
				for (var j = 0; j < row; j++) {
					grid.placesMap[place + i + j*grid.numCol] = true;
				}
			}
			return {
				x: place%grid.numCol,
				y: Math.floor(place/grid.numCol),
				width: col,
				height: row
			};
		}
		for (var i = 0; i < filteredItems.length; i++) {
			var item = filteredItems[i];
			var width = (item.col > grid.numCol) ? grid.numCol : item.col;
			var height = (item.col > grid.numCol) ? Math.round(item.row*grid.numCol/item.col) : item.row;
			var place = 0;
			while (!hasRoom(place, width, height)) {
				place++;
			}
			var box = add(place, width, height);
			var rect = {
				left: box.x*colWidth + marginH,
				top: box.y*(rowHeight + marginV*2) + marginV,
				width: Math.max(0, box.width*colWidth - marginH*2),
				height: Math.max(0, box.height*(rowHeight + marginV*2) - marginV*2)
			};
			grid.numRow = Math.max(grid.numRow, box.y + box.height);
			grid.height = Math.max(grid.numRow*(rowHeight + marginV*2), marginV*2);
			grid.boxesMap[item.index] = box;
			grid.rectsMap[item.index] = rect;
			grid.indexes.push(item.index);
		}
		return grid;
	}
	return gridSystem;
}
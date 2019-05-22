/**
 * Collection
 *
 * @version mai2019
 */
function createCollection(items) {
	var collection = {
		items: items || [],
		cyclic: false,
		add: function(item) {
			this.items.push(item);
		},
		remove: function(key, value) {
			var index = this.getIndex(key, value);
			if (index > -1) {
				this.items.splice(index, 1);
			}
		},
		contains: function(item) {
			return this.items.indexOf(item) > -1;
		},
		getItem: function(key, value) {
			for (var i = 0; i < this.items.length; i++) {
				if (this.items[i][key] === value) {
					return this.items[i];
				}
			}
		},
		getItems: function(key, value) {
			var collection = createCollection();
			for (var i = 0; i < this.items.length; i++) {
				if (this.items[i][key] === value) {
					collection.items.push(this.items[i]);
				}
			}
			return collection;
		},
		group: function(key, id) {
			var groups = createCollection();
			for (var i = 0; i < this.items.length; i++) {
				var value = this.items[i][key];
				if (value || value === 0) {
					var group = groups.getItem(id, value);
					if (!group) {
						group = createCollection();
						group[id] = value;
						groups.add(group);
					}
					group.add(this.items[i]);
				}
			}
			return groups;
		},
		// join: function(collection, key) {
		// 	for (var i = 0; i < this.items.length; i++) {
		// 		var value = this.items[i][key];
		// 		if (value && value.length) { // value must be an array
		// 			var newValue = createCollection();
		// 			for (var j = 0; j < value.length; j++) {
		// 				var joinItem = collection.getItem(key, value[j]);
		// 				if (joinItem) {
		// 					newValue.add(joinItem);
		// 				}
		// 			}
		// 			this.items[i][key] = newValue;
		// 		}
		// 	}
		// },
		loop: function (value, offset) {
			while (this.items.length && value >= this.items.length + (offset || 0)) value -= this.items.length;
			while (this.items.length && value < (offset || 0)) value += this.items.length;
			return value;
		},
		getIndex: function(key, value) {
			for (var i = 0; i < this.items.length; i++) {
				if (this.items[key] === value) {
					return i;
				}
			}
			return -1;
		},
		getAdjacent: function(item, dir) {
			var index = this.items.indexOf(item);
			if (index > -1) {
				index += dir;
				if (this.cyclic) {
					index = this.loop(index);
				}
				return this.items[index];
			}
		},
		getAdjacents: function(max, item) {
			var collection = createCollection();
			for (var i = 1; i <= max; i++) {
				var prev = this.getAdjacent(item, -i);
				var next = this.getAdjacent(item, i);
				if (this.cyclic && collection.items.indexOf(prev) > -1) {
					break;
				}
				if (prev) {
					collection.items.push(prev);
				}
				if (this.cyclic && collection.items.indexOf(next) > -1) {
					break;
				}
				if (next) {
					collection.items.push(next);
				}
			}
			return collection;
		}
	};
	return collection;
}

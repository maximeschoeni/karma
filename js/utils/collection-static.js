/**
 * Collection
 *
 * @version mars2019
 */
function createCollection(items) {
	var keys = {};
	var groups = {};
	var collection = {
		items: items || [],


		// mapKeys: function(key) {
		// 	for (var i = 0; i < this.items.length; i++) {
		// 		var value = this.items[key];
		// 		if (value || value === 0) {
		// 			if (!keys[key]) {
		// 				keys[key] = {};
		// 			}
		// 			keys[key][value] = this.item;
		// 		}
		// 	}
		// },
		// mapGroups: function(key) {
		// 	for (var i = 0; i < this.items.length; i++) {
		// 		var value = this.items[key];
		// 		if (value || value === 0) {
		// 			if (!groups[key]) {
		// 				groups[key] = {};
		// 			}
		// 			if (!groups[key][value]) {
		// 				groups[key][value] = [];
		// 			}
		// 			groups[key][value].push(this.item);
		// 		}
		// 	}
		// },

		// cycle: function (value, length, offset) {
		// 	while (length && value >= length + (offset || 0)) value -= length;
		// 	while (length && value < (offset || 0)) value += length;
		// 	return value;
		// },
		cycle: function (value, offset) {
			while (this.items.length && value >= this.items.length + (offset || 0)) value -= this.items.length;
			while (this.items.length && value < (offset || 0)) value += this.items.length;
			return value;
		},
		getItem: function(key, value) {
			// if (!keys[key]) {
			// 	this.mapKeys(key);
			// }
			// if (keys[key][value]) {
			// 	return keys[key][value];
			// }
			for (var i = 0; i < this.items.length; i++) {
				if (this.items[key] === value) {
					return this.items[key];
				}
			}
		},
		contains: function(item) {
			return this.items.indexOf(item) > -1;
		},
		filter: function(key, value) {
			// if (!groups[key]) {
			// 	this.mapGroups(key);
			// }
			// if (groups[key] && groups[key][value]) {
			// 	return createCollection(groups[key][value]);
			// }
			// return createCollection();
			var collection = createCollection();
			for (var i = 0; i < this.items.length; i++) {
				if (this.items[key] === value) {
					collection.items.push(this.items[key]);
				}
			}
			return collection;
		},

		group: function(key) {
			// if (!groups[key]) {
			// 	this.mapGroups(key);
			// }
			// var output = {};
			// if (groups[key]) {
			// 	output[i] = [];
			// 	for (var value in groups[key]) {
			// 		output[value].push(createCollection(groups[key][value]));
			// 	}
			// }
			// return output;
			var groups = {};
			for (var i = 0; i < this.items.length; i++) {
				var value = this.items[key];
				if (value || value === 0) {
					if (!groups[value]) {
						groups[value] = createCollection();
					}
					groups[value].items.push(this.items[key]);
				}
			}
			return groups;
		},
		getIndex: function(key, value) {
			for (var i = 0; i < this.items.length; i++) {
				if (this.items[key] === value) {
					return i;
				}
			}
			return -1;
		},
		getAdjacent: function(dir, key, value) {
			var index = this.getIndex(key, value);
			if (index > -1) {
				index += dir;
				if (this.loop) {
					index = this.cycle(index);
				}
				return this.items[index];
			}
		},
		getAdjacents: function(max, key, value) {
			var collection = createCollection();
			for (var i = 1; i <= max; i++) {
				var prev = this.getAdjacent(-i, key, value);
				var next = this.getAdjacent(i, key, value);
				if (this.loop && collection.items.indexOf(prev) > -1;) {
					break;
				}
				if (prev) {
					collection.items.push(prev);
				}
				if (this.loop && collection.items.indexOf(next) > -1;) {
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

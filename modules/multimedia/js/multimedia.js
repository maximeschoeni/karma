if (!window.KarmaMultimedia) {
	KarmaMultimedia = {};
}

KarmaMultimedia.createManager = function() {
	var manager = {
		input: null,
		inputName: "medias",
		container: null,
		sortableManager: createSortableManager(),
		// library: {},
		items: [],
		types: [],
		columns: [],
		add: function(item) {

			// for (var i = 0; i < this.types.length; i++) {
			// 	var type = this.types[i];
			// 	item[type.key] = item[type.key] || type.default || null;
			// }
			// item.type = item.type || "none";
			// item.format = item.format || "1x1";
			this.items.push(item || {type: manager.types[0].key});
		},
		update: function() {
			if (this.content) {
				this.container.removeChild(this.content);
				this.content = null;
			}
			if (this.items.length) {
				this.content = this.buildTable();
				this.container.appendChild(this.content);
			}
			this.input.value = JSON.stringify(this.items);
		},
		build: function(name) {
			this.input = build("input.full-width");
			this.input.type = "hidden";
			this.input.name = this.inputName;
			this.container = build("div.karma-multimedia-container");

			return build("div.karma-multimedia",
				this.input,
				this.container,
				build("button.button.add-media", "Ajouter", function() {
					this.addEventListener("click", function(event) {
						event.preventDefault();
						manager.add();
						manager.update();
					});
				})
			);
		},
		buildSelector: function(values, current, onChange) {
			return build("select",
				values.map(function(value) {
					return build("option", value.name, function() {
						this.value = value.key;
						this.selected = value.key === current;
					});
				}), function() {
					this.addEventListener("change", onChange);
				}
			);
		},
		// buildRow: function(item) {
		// 	var row = build("tr.media-box-row",
		// 		build("td.media-box-cell.type",
		// 			manager.buildSelector(manager.types, item.type, function() { // [{key: "", name:"-"}].concat(
		// 				item.type = this.value;
		// 				manager.update();
		// 			})
		// 		),
		// 		manager.columns.map(function(column) {
		// 			return build("td.media-box-cell."+column.key, KarmaMultimedia[column.builder] && KarmaMultimedia[column.builder](manager, column, item));
		// 		}),
		// 		build("td.media-box-cell.value",
		// 			type && type.builder && KarmaMultimedia[type.builder](manager, item, typeIndexes)
		// 		),
		// 		build("td.media-box-cell.remove",
		// 			build("button.button", "✕", function() {
		// 				this.addEventListener("click", function(event) {
		// 					event.preventDefault();
		// 					manager.items.splice(itemIndex, 1);
		// 					manager.update();
		// 				});
		// 			})
		// 		)
		// 	);
		// 	manager.sortableManager.addItem(row);
		// 	return row;
		// },
		buildTable: function() {
			manager.sortableManager.reset();
			var typesCollection = createCollection(manager.types);
			var columnsCollection = createCollection(manager.columns);
			var typeIndexes = {};
			var tbody = build("tbody",
				manager.items.map(function(item, itemIndex) {
					if (!typeIndexes[item.type]) {
						typeIndexes[item.type] = 0;
					}
					var typeIndex = typeIndexes[item.type];
					typeIndexes[item.type]++;
					var type = item.type && typesCollection.getItem("key", item.type) || manager.types[0];
					var row = build("tr.media-box-row",
						build("td.media-box-cell.type",
							manager.buildSelector(manager.types, item.type, function() { // [{key: "", name:"-"}].concat(
								item.type = this.value;
								manager.update();
							})
						),
						manager.columns.map(function(column) {
							return build("td.media-box-cell."+column.key, KarmaMultimedia[column.builder] && KarmaMultimedia[column.builder](manager, column, item));
						}),
						build("td.media-box-cell.value",
							type && type.builder && KarmaMultimedia[type.builder](manager, item, typeIndexes)
						),
						build("td.media-box-cell.remove",
							build("button.button", "✕", function() {
								this.addEventListener("click", function(event) {
									event.preventDefault();
									manager.items.splice(itemIndex, 1);
									manager.update();
								});
							})
						)
					);
					manager.sortableManager.addItem(row);
					return row;
				})
			);

			manager.sortableManager.container = tbody;

			return build("table.media-box-table.widefat.striped",
				build("thead",
					build("tr",
						build("th.type", "Type"),
						manager.columns.map(function(column) {
							return build("th", column.name);
						}),
						build("th.value", "Value"),
						build("th.remove", "")
					)
				),
				tbody
			);
		}
	};

	// manager.items = JSON.parse(input.value);

	manager.sortableManager.checkTarget = function(target) {
		return target.classList.contains("media-box-cell");
	};
	manager.sortableManager.createPlaceholder = function(draggedElement) {
		placeholder = build("tr.media-box-row.placeholder", build("td"), build("td"), build("td"), build("td"));
		placeholder.style.height = draggedElement.clientHeight.toFixed()+"px";
		return placeholder;
	};
	manager.sortableManager.onStartDrag = function(draggedElement, placeholder, box) {
		draggedElement.style.backgroundColor = "rgb(240, 240, 240)";
	};
	manager.sortableManager.onChange = function(dragFrom, dragTo) {
		var itemToMove = manager.items.splice(dragFrom, 1)[0];
		manager.items.splice(dragTo, 0, itemToMove);
	};
	manager.sortableManager.onEndDrag = function(draggedElement, placeholder) {
		draggedElement.style.removeProperty("background-color");
		manager.update();
	};


	return manager;
}

KarmaMultimedia.buildImageInput = function(manager, item) {
	return buildImageUploader(item.id, function(ids) {
		item.id = ids.shift();
		if (ids.length) {
			ids.forEach(function(id) {
				manager.add({
					type: "image",
					id: id
				});
			});
		}
		manager.update();
	});
};
KarmaMultimedia.buildGalleryInput = function(manager, item) {
	return buildGalleryUploader(item.ids, function(ids) {
		item.ids = ids;
		manager.update();
	});
};
KarmaMultimedia.buildTextareaInput = function(manager, item) {
	return build("textarea", item.text || "", function() {
		this.addEventListener("blur", function() {
			item.text = this.value;
			manager.update();
		});
	});
};
KarmaMultimedia.buildPostContentInput = function(manager, item, pageIndex) {
	var content = manager.post_contents && pageIndex < manager.post_contents.length && manager.post_contents[pageIndex] || "";
	return build("textarea", content, function() {
		this.disabled = true;
	});
};
KarmaMultimedia.buildImageAudioInput = function(manager, item) {
	return build("div",
		buildImageUploader(item.mp3_id, function(ids) {
			item.mp3_id = ids.shift();
			manager.update();
		}, "mp3"),
		buildImageUploader(item.id, function(ids) {
			item.id = ids.shift();
			manager.update();
		}, "image")
	);
};

KarmaMultimedia.buildInputColumn = function(manager, column, item) {
	if (!item[column.key] || typeof item[column.key] === "string") {
		item[column.key] = {};
	}
	return build("input.text", function() {
		this.type = "text";
		console.log(column, item[column.key]);
		if (column.translatable) {
			this.placeholder = item[column.key].placeholder || "";
		} else if (column.placeholder) {
			this.placeholder = column.placeholder;
		}
		this.value = item[column.key].text || column.default || "";
		this.addEventListener("blur", function() {
			item[column.key].text = this.value;
			manager.update();
		});
	});
}

// KarmaMultimedia.buildFormatColumn = function(manager, item) {
// 	return build("td.media-box-cell.format",
// 		build("select",
// 			build("option", "1x1", function() {this.value = "1x1"}),
// 			build("option", "1x2", function() {this.value = "1x2"}),
// 			build("option", "2x1c", function() {this.value = "2x1 centré"}),
// 			build("option", "2x2", function() {this.value = "2x2"}),
// 			build("option", "2x2c", function() {this.value = "2x2 centré"}),
// 			function() {
// 				this.addEventListener("change", function() {
// 					item.format = this.value;
// 					manager.update();
// 				});
// 			}
// 		)
// 	);
// }


// KarmaMultimedia.buildFormatColumn = function(manager, item) {
// 	var formats = {
// 		"1x1": {
// 			name: "1x1",
// 		},
// 		"1x2": {
// 			name: "1x2",
// 		},
// 		"2x1c": {
// 			name: "2x1 centré",
// 		},
// 		"2x2": {
// 			name: "2x2",
// 		},
// 		"2x2c": {
// 			name: "2x2 centré",
// 		}
// 	};
// 	return build("td.media-box-cell.format",
// 		KarmaMultimedia.buildSelector(formats, item.format, function() {
// 			item.format = this.value;
// 			manager.update();
// 		})
// 	);
// }



// 	value = build("div",
// 		buildImageUploader(item.mp3_id, manager.library, function(ids) {
// 			item.mp3_id = ids.shift();
// 			manager.update();
// 		}, "mp3"),
// 		buildImageUploader(item.id, manager.library, function(ids) {
// 			item.id = ids.shift();
// 			manager.update();
// 		}, "image")
// 	);



//
// KarmaMultimedia.buildSelector = function(values, current, onChange) {
// 	var selector = build("select", function() {
// 		this.addEventListener("change", onChange);
// 	});
// 	for (var key in values) {
// 		var option = build("option", values[key].name);
// 		option.value = key;
// 		option.selected = key === current;
// 		selector.appendChild(option);
// 	}
// 	return selector;
// }







// function buildMediaBoxTable(manager) {
//
// 	manager.sortableManager.reset();
//
// 	var typeIndexes = {};
//
// 	var tbody = build("tbody",
// 		manager.items.map(function(item, itemIndex) {
// 			if (!typeIndexes[item.type]) {
// 				typeIndexes[item.type] = 0;
// 			}
// 			var typeIndex = typeIndexes[item.type];
// 			typeIndexes[item.type]++;
//
// 			var row = build("tr.media-box-row",
// 				build("td.media-box-cell.type",
// 					buildSelector(manager.types, item.type, function() {
// 						item.type = this.value;
// 						manager.update();
// 					})
// 				),
// 				manager.columns.map(function(column) {
// 					return build("td.media-box-cell."+column.key, column.callback(manager, item));
// 				}),
// 				build("td.media-box-cell.value",
// 					value,
// 					manager.types[item.type].inputCallback(manager, item, typeIndexes);
// 				),
// 				build("td.media-box-cell.remove",
// 					build("button.button", "✕", function() {
// 						this.addEventListener("click", function(event) {
// 							event.preventDefault();
// 							manager.items.splice(itemIndex, 1);
// 							manager.update();
// 						});
// 					})
// 				)
// 			);
// 			manager.sortableManager.addItem(row);
// 			return row;
// 		})
// 	);
//
// 	manager.sortableManager.container = tbody;
//
// 	return build("table.media-box-table.widefat.striped",
// 		build("thead",
// 			build("tr",
// 				build("th", "Type"),
// 				manager.columns.map(function(column) {
// 					return build("th", column.name);
// 				}),
// 				build("th", "Value"),
// 				build("th", "")
// 			)
// 		),
// 		tbody
// 	);
// }

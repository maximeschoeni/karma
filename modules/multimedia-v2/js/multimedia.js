if (!window.KarmaMultimedia) {
	KarmaMultimedia = {};
}

KarmaMultimedia.attachmentPromises = {};

KarmaMultimedia.getImageSrc = function(id, callback) {
	if (!KarmaMultimedia.attachmentPromises[id]) {
		KarmaMultimedia.attachmentPromises[id] = new Promise(function(resolve, reject) {
			ajaxGet(KarmaMultimedia.ajax_url, {
				action: "karma_multimedia_get_image_src",
				id: id
			}, function(results) {
				resolve(results);
			});
		});
	}
	if (callback) {
		KarmaMultimedia.attachmentPromises[id].then(callback);
	}
	return KarmaMultimedia.attachmentPromises[id];
}


KarmaMultimedia.build = function(name, columns, items, key) {
	var sortableManager = createSortableManager();
	var visibleInput;
	var dispatcher = {};

	var settings = KarmaMultimedia.settings && KarmaMultimedia.settings[key] || {};

	return buildNode("div.karma-multimedia", function(update) {

		sortableManager.checkTarget = function(target) {
			return sortableManager.items.indexOf(target.parentNode) > -1;
		};
		sortableManager.createPlaceholder = function(draggedElement) {
			placeholder = build("tr.media-box-row.placeholder", columns.map(function() {
				return build("td");
			}), build("td"));
			placeholder.style.height = draggedElement.clientHeight.toFixed()+"px";
			return placeholder;
		};
		sortableManager.onChange = function(dragFrom, dragTo) {
			var itemToMove = items.splice(dragFrom, 1)[0];
			items.splice(dragTo, 0, itemToMove);
		};
		sortableManager.onEndDrag = function(draggedElement, placeholder) {
			if (dispatcher.save) {
				dispatcher.save();
			}
		};

		return build("div",

			items.length && build("table.media-box-table.widefat.striped",
				build("thead",
					build("tr",
						columns.map(function(column) {
							return build("th", column.name, function() {
								this.style.width = column.width || (100/columns.length).toFixed(4)+"%";
							});
						}),
						build("th.remove", "")
					)
				),
				buildNode("tbody", function(updateTBody) {
					sortableManager.reset();
					sortableManager.container = this;
					return items.map(function(item, itemIndex) {
						var row = buildNode("tr.media-box-row", function(updateRow) {
							return columns.map(function(column) {
								// var input = column.input || column.inputs && (item.type && createCollection(column.inputs).getItem("type", item.type) || column.inputs[0]);
								var currentInput = column.input;
								if (column.inputs) {
									// column.inputs.filter(function(input) {
									// 	return input.type !== item.type;
									// }).forEach(function(input) {
									// 	item[input.key] = null;
									// });

									column.inputs.forEach(function(input) {
										if (input.type === item.type) {
											currentInput = input;
										}
										// else {
										// 	item[input.key] = null;
										// }
									});
								}
								var child = currentInput && KarmaMultimedia[currentInput.builder] && KarmaMultimedia[currentInput.builder](item, currentInput, column, items, columns, updateRow, dispatcher, update);
								return build("td.media-box-cell.column-"+(currentInput.key || "default"), child, function() {
									this.style.width = column.width || (100/columns.length).toFixed(4)+"%";
								});
							}).concat(
								build("td.media-box-cell.remove",
									build("button.button", "✕", function() {
										this.addEventListener("click", function(event) {
											event.preventDefault();
											items.splice(itemIndex, 1);
											update();
										});
									})
								)
							);
						});
						sortableManager.addItem(row);
						return row;
					});
				})
			),
			build("div.media-box-control",
				build("button.button.add-media", settings.add_button || "Ajouter", function() {
					this.addEventListener("click", function(event) {
						event.preventDefault();
						// items.push(Object.assign && items.length > 0 && Object.assign({}, items[items.length-1]) || {});

						items.push({});
						update();
					});
				}),
				settings.copypast && build("button.button.add-media", "Copy/Past", function() {
					this.addEventListener("click", function(event) {
						event.preventDefault();
						visibleInput = !visibleInput;
						update();
					});
				}),
				build("input.full-width", function(element) {
					this.type = visibleInput ? "text" : "hidden";
					this.name = name;
					this.addEventListener("input", function() {
						items = this.value && JSON.parse(this.value) || [];
						update();
					});
					dispatcher.save = function() {
						element.value = items.length && JSON.stringify(items) || "";
					}
					dispatcher.save();
				})
			)
		);
	});
};

KarmaMultimedia.buildSelector = function(values, current, onChange) {
	return build("select",
		values.map(function(value) {
			return build("option", value.name, function() {
				this.value = value.key;
				this.selected = value.key.toString() === current.toString();
			});
		}), function() {
			this.addEventListener("change", onChange);
		}
	);
};

KarmaMultimedia.buildTypeSelector = function(item, input, column, items, columns, updateRow, dispatcher) {
	item.type = item.type || input.values[0].key;
	return KarmaMultimedia.buildSelector(input.values, item.type, function() { // [{key: "", name:"-"}].concat(
		item.type = this.value;
		if (dispatcher.save) {
			dispatcher.save();
		}
		updateRow();
	})
};
KarmaMultimedia.buildImageInput = function(item, input, column, items, columns, updateRow, dispatcher, updateAll) {
	var imageManager = createImageUploader(input.mimetype, input.multiple);
	return buildNode("div.image-input", function(update) {
		imageManager.imageId = item[input.key];
		imageManager.onSelect = function(attachments) {
			if (attachments.length) {
				var attachment = attachments.shift();
				item[input.key] = attachment.id;
				// KarmaMultimedia.attachments[attachment.id] = {
				// 	filename: attachment.filename,
				// 	src: attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url || attachment.icon
				// };
				if (input.multiple && attachments.length) {
					attachments.forEach(function(attachment) {
						var newitem = {
							type: item.type
						}
						newitem[input.key] = attachment.id;
						items.push(newitem);
						// KarmaMultimedia.attachments[attachment.id] = {
						// 	filename: attachment.filename,
						// 	src: attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url || attachment.icon
						// };
					});
					updateAll();
				} else {
					update();
				}
				if (dispatcher.save) {
					dispatcher.save();
				}
			}

		};
		if (item[input.key]) {
			return build("div.media-box-image",
				buildPromise("div.image-box", null, function(updateImage, results) {
					this.addEventListener("click", function() {
						imageManager.open();
					});
					return build("div.image-box-content",
						build("img", function() {
							this.src = results.url;
						}),
						build("span.image-name", results.filename || "?")
					);
				}, KarmaMultimedia.getImageSrc(item[input.key])),

				// buildPromise("img", null, function(updateImage, results) {
				// 	this.src = results.url;
				// 	this.addEventListener("click", function() {
				// 		imageManager.open();
				// 	});
				// }, KarmaMultimedia.getImageSrc(item[input.key])),
				// build("span.image-name", attachment && attachment.filename || "?")

			);
		} else {
			return build("button.button", "Ajouter", function() {
				this.addEventListener("click", function(event) {
					event.preventDefault();
					imageManager.open();
				});
			});
		}
	});
};

KarmaMultimedia.buildGalleryInput = function(item, input, column, items, columns, updateRow, dispatcher) {
	var manager = createGalleryUploader();
	return buildNode("div.gallery-input", function(update) {
		manager.imageIds = item[input.key];
		manager.onChange = function(attachments) {
			item[input.key] = attachments.map(function(attachment) {
				// KarmaMultimedia.attachments[attachment.id] = {
				// 	filename: attachment.filename,
				// 	src: attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url || attachment.icon
				// };
				return attachment.id;
			});
			if (dispatcher.save) {
				dispatcher.save();
			}
			update();
		};
		if (item[input.key]) {
			return build("div.media-box-gallery", item[input.key].map(function(id) {
				// var attachment = KarmaMultimedia.attachments && KarmaMultimedia.attachments[id];
				return build("div.media-box-gallery-thumb",
					// build("img", function() {
					// 	this.src = attachment && attachment.src || KarmaMultimedia.ajax_url+"?action=karma_multimedia_get_image&id="+id;
					// }),
					buildPromise("img", null, function(updateImage, results) {
						this.src = results.url;
					}, KarmaMultimedia.getImageSrc(id))
					// build("input", function() {
					// 	this.type = "hidden";
					// 	this.name = "karma-mm-attachments[]";
					// 	this.value = id;
					// })
				);
				// var img = build("img");
				// img.src = KarmaMultimedia.ajax_url+"?action=karma_multimedia_get_image&id="+id;
				// return img;
			}), function() {
				this.addEventListener("click", function() {
					manager.open();
				});
			});
		} else {
			return build("button.button", "Ajouter", function() {
				this.addEventListener("click", function(event) {
					event.preventDefault();
					manager.open();
				});
			});
		}
	});
};

KarmaMultimedia.buildCustomSelector = function(item, input, column, items, columns, updateRow, dispatcher) {
	item[input.key] = item[input.key] || input.values[0].key.toString();
	return KarmaMultimedia.buildSelector(input.values, item[input.key.toString()], function() { // [{key: "", name:"-"}].concat(
		item[input.key] = this.value;
		if (dispatcher.save) {
			dispatcher.save();
		}
	})
};

KarmaMultimedia.buildTextInput = function(item, input, column, items, columns, updateRow, dispatcher) {
	if (!item[input.key] || typeof item[input.key] === "string") {
		item[input.key] = {};
	}
	return build("input.text", function() {
		this.type = "text";
		if (input.translatable) {
			this.placeholder = item[input.key].placeholder || "";
		} else if (input.placeholder) {
			this.placeholder = input.placeholder;
		}
		this.value = item[input.key].text || input.default || "";
		this.addEventListener("blur", function() {
			item[input.key].text = this.value;
			if (dispatcher.save) {
				dispatcher.save();
			}
		});
	});
}
KarmaMultimedia.buildBasicTextInput = function(item, input, column, items, columns, updateRow, dispatcher) {
	return build("input.text", function() {
		this.type = "text";
		this.value = item[input.key] || input.default_key && item[input.default_key] || "";
		if (input.placeholder) {
			this.placeholder = input.placeholder;
		}
		this.addEventListener("input", function() {
			item[input.key] = this.value;
			if (dispatcher.save) {
				dispatcher.save();
			}
		});
	});
}
KarmaMultimedia.buildBasicTextArea = function(item, input, column, items, columns, updateRow, dispatcher) {
	return build("textarea.text", function() {
		this.value = item[input.key] || input.default_key && item[input.default_key] || "";
		if (input.placeholder) {
			this.placeholder = input.placeholder;
		}
		this.addEventListener("input", function() {
			item[input.key] = this.value;
			if (dispatcher.save) {
				dispatcher.save();
			}
		});
	});
}

KarmaMultimedia.buildDateInput = function(item, input, column, items, columns, updateRow, dispatcher) {
	return build("input.text", function() {
		var dateManager = createDatePopupManager(this);
		dateManager.sqlDate = item[input.key] || input.default_key && item[input.default_key] || "";
		dateManager.init();
		this.type = "text";

		// this.value = item[input.key] || input.default_key && item[input.default_key] || "";
		if (input.placeholder) {
			this.placeholder = input.placeholder;
		}

		dateManager.onUpdate = function() {

			item[input.key] = dateManager.sqlDate;
			if (dispatcher.save) {
				dispatcher.save();
			}
		};
	});
}

KarmaMultimedia.buildMultiDateInput = function(item, input, column, items, columns, updateRow, dispatcher) {
	if (!item[input.key]) {
		item[input.key] = [""];
	}
	return buildNode("div.multidate", function(updateDates) {
		return build("div.multidate-content",
			item[input.key] && item[input.key].length && build("ul", item[input.key].map(function(sqlDate, index) {
				return build("li",
					build("input.text", function() {
						var dateManager = createDatePopupManager(this);
						dateManager.sqlDate = sqlDate;
						dateManager.init();
						dateManager.onUpdate = function() {

							item[input.key][index] = dateManager.sqlDate;
							// updateDates();
							if (dispatcher.save) {
								dispatcher.save();
							}
						};
					}),
					index > 0 && build("button.button", "✕", function() {
						this.addEventListener("click", function(event) {
							event.preventDefault();
							item[input.key].splice(index, 1);
							updateDates();
						});
					})
				);
			})),
			build("button.button", input.more_button || "+", function() {
				this.addEventListener("click", function(event) {
					event.preventDefault();
					item[input.key].push(item[input.key].length && item[input.key][item[input.key].length-1] || "");
					updateDates();
				});
			})
		);
	});
}

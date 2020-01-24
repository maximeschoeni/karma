// if (!window.KarmaMultimedia) {
// 	KarmaMultimedia = {};
// }

// KarmaMultimedia.attachmentPromises = {};

// KarmaMultimedia.getImageSrc = function(id, callback) {
// 	if (!KarmaMultimedia.attachmentPromises[id]) {
// 		KarmaMultimedia.attachmentPromises[id] = new Promise(function(resolve, reject) {
// 			ajaxGet(KarmaMultimedia.ajax_url, {
// 				action: "karma_multimedia_get_image_src",
// 				id: id
// 			}, function(results) {
// 				resolve(results);
// 			});
// 		});
// 	}
// 	if (callback) {
// 		KarmaMultimedia.attachmentPromises[id].then(callback);
// 	}
// 	return KarmaMultimedia.attachmentPromises[id];
// }


KarmaFieldMedia.fields.multimedia = function(value, options, onSave) {
	var columns = options.columns;
	var items = value || [];
	var sortableManager = createSortableManager();
	// var dispatcher = {};
	// var onUpdateTable;

	sortableManager.checkTarget = function(target) {
		return sortableManager.items.indexOf(target.parentNode) > -1;
	};
	sortableManager.createPlaceholder = function(draggedElement) {
		placeholder = build({
			tag: "tr",
			class: "media-box-row placeholder",
			children: columns.map(function() {
				return build({
					tag: "td"
				});
			}).concat([
				build({
					tag: "td"
				})
			])
		});
		placeholder.style.height = draggedElement.clientHeight.toFixed()+"px";
		return placeholder;
	};
	sortableManager.onChange = function(dragFrom, dragTo) {
		var itemToMove = items.splice(dragFrom, 1)[0];
		items.splice(dragTo, 0, itemToMove);
	};
	sortableManager.onEndDrag = function(draggedElement, placeholder) {
		onSave && onSave(items);
	};



	var tableManager = {
		items: items,
		columns: columns
	};

	return build({
		class: "karma-multimedia",
		children: [
			build({
				tag: "table",
				class: "media-box-table widefat striped",
				onUpdate: function() {
					return {
						children: items.length && [
							options.columns.some(function(column) {
								return column.name;
							}) && build({
								tag: "thead",
								child: build({
									tag: "tr",
									children: columns.map(function(column) {
										return build({
											tag: "th",
											text: column.name,
											init: function(th) {
												th.style.width = column.width || (100/columns.length).toFixed(4)+"%";
											}
										});
									}).concat([
										build({
											tag: "th",
											init: function(th) {
												th.style.width = (100/columns.length).toFixed(4)+"%";
											}
										})
									])
								})
							}),
							build({
								tag: "tbody",
								children: items.map(function(item, itemIndex) {
									return build({
										tag: "tr",
										class: "media-box-row",
										onUpdate: function(groupManager) {
											return {
												children: columns.map(function(column) {
													return build({
														tag: "td",
														class: "media-box-cell.column-"+(column.key || "default"),
														child: column.field && KarmaFieldMedia.fields[column.field] && column.field && KarmaFieldMedia.fields[column.field](item[column.key], column, function(value) {
															item[column.key] = value;
															onSave && onSave(items);
														}, groupManager),
														init: function(cell) {
															cell.style.width = column.width || (100/columns.length).toFixed(4)+"%";
														}
													});
												}).concat(
													[build({
														tag: "td",
														class: "media-box-cell remove",
														child: build({
															tag: "button",
															class: "button",
															text: "✕",
															init: function(button) {
																button.onclick = function(event) {
																	event.preventDefault();
																	items.splice(itemIndex, 1);
																	tableManager.update();
																}
															}
														})
													})]
												)
											};
										},
										init: function(row, update) {
											var groupManager = {};
											groupManager.item = item;
											groupManager.parent = tableManager;
											groupManager.onUpdate = update;
											update(groupManager);
											sortableManager.addItem(row);
										}
									});
								}),
								init: function(tbody) {
									sortableManager.reset();
									sortableManager.container = tbody;
								}
							})
						]
					};
				},
				init: function(table, update) {
					tableManager.update = update;
					update();
				}
			}),
			build({
				class: "media-box-control",
				children: [
					build({
						tag: "button",
						class: "button",
						text: options.button || "Add",
						init: function(button) {
							button.onclick = function(event) {
								event.preventDefault();
								items.push({});
								onSave && onSave(items);
								tableManager.update();
							}
						}
					})
					// build({
					// 	tag: "input",
					// 	init: function(input) {
					// 		input.type = "hidden";
					// 		// onSave = function() {
					// 		// 	input.value = items.length && JSON.stringify(items) || "";
					// 		// }
					// 	}
					// })
				]
			})
		]
	});
	//
	// // var settings = KarmaMultimedia.settings && KarmaMultimedia.settings[key] || {};
	//
	// return buildNode("div.karma-multimedia", function(update) {
	//
	//
	//
	// 	return build("div",
	//
	// 		items.length && build("table.media-box-table.widefat.striped",
	// 			build("thead",
	// 				build("tr",
	// 					columns.map(function(column) {
	// 						return build("th", column.name, function() {
	// 							this.style.width = column.width || (100/columns.length).toFixed(4)+"%";
	// 						});
	// 					}),
	// 					build("th.remove", "")
	// 				)
	// 			),
	// 			buildNode("tbody", function(updateTBody) {
	// 				sortableManager.reset();
	// 				sortableManager.container = this;
	// 				return items.map(function(item, itemIndex) {
	// 					var row = buildNode("tr.media-box-row", function(updateRow) {
	// 						return columns.map(function(column) {
	// 							// var input = column.input || column.inputs && (item.type && createCollection(column.inputs).getItem("type", item.type) || column.inputs[0]);
	// 							var currentInput = column.input;
	// 							if (column.inputs) {
	// 								// column.inputs.filter(function(input) {
	// 								// 	return input.type !== item.type;
	// 								// }).forEach(function(input) {
	// 								// 	item[input.key] = null;
	// 								// });
	//
	// 								column.inputs.forEach(function(input) {
	// 									if (input.type === item.type) {
	// 										currentInput = input;
	// 									}
	// 									// else {
	// 									// 	item[input.key] = null;
	// 									// }
	// 								});
	// 							}
	// 							var child = currentInput && KarmaMultimedia[currentInput.builder] && KarmaMultimedia[currentInput.builder](item, currentInput, column, items, columns, updateRow, dispatcher, update);
	// 							return build("td.media-box-cell.column-"+(currentInput.key || "default"), child, function() {
	// 								this.style.width = column.width || (100/columns.length).toFixed(4)+"%";
	// 							});
	// 						}).concat(
	// 							build("td.media-box-cell.remove",
	// 								build("button.button", "✕", function() {
	// 									this.addEventListener("click", function(event) {
	// 										event.preventDefault();
	// 										items.splice(itemIndex, 1);
	// 										update();
	// 									});
	// 								})
	// 							)
	// 						);
	// 					});
	// 					sortableManager.addItem(row);
	// 					return row;
	// 				});
	// 			})
	// 		),
	// 		build("div.media-box-control",
	// 			build("button.button.add-media", settings.add_button || "Ajouter", function() {
	// 				this.addEventListener("click", function(event) {
	// 					event.preventDefault();
	// 					// items.push(Object.assign && items.length > 0 && Object.assign({}, items[items.length-1]) || {});
	//
	// 					items.push({});
	// 					update();
	// 				});
	// 			}),
	// 			settings.copypast && build("button.button.add-media", "Copy/Past", function() {
	// 				this.addEventListener("click", function(event) {
	// 					event.preventDefault();
	// 					visibleInput = !visibleInput;
	// 					update();
	// 				});
	// 			}),
	// 			build("input.full-width", function(element) {
	// 				this.type = visibleInput ? "text" : "hidden";
	// 				this.name = name;
	// 				this.addEventListener("input", function() {
	// 					items = this.value && JSON.parse(this.value) || [];
	// 					update();
	// 				});
	// 				dispatcher.save = function() {
	// 					element.value = items.length && JSON.stringify(items) || "";
	// 				}
	// 				dispatcher.save();
	// 			})
	// 		)
	// 	);
	// });
};


KarmaFieldMedia.buildSelector = function(values, current, onChange) {
	return build({
		tag: "select",
		children: values.map(function(value) {
			return build({
				tag: "option",
				text: value.name,
				init: function(option) {
					option.value = value.key;
					option.selected = value.key.toString() === current.toString();
				}
			});
		}),
		init: function(select) {
			select.addEventListener("change", onChange);
		}
	});
};
KarmaFieldMedia.fields.select = function(value, options, save) {
	return KarmaFieldMedia.buildSelector(options.values, value, function() { // [{key: "", name:"-"}].concat(
		save(this.value);
	})
};
KarmaFieldMedia.fields.modes = function(value, options, save, group) {
	return KarmaFieldMedia.buildSelector(options.values, value, function() {
		save(this.value);
		group.update();
	})
};

KarmaFieldMedia.fields.text = function(value, options, save) {
	return build({
		tag: "input",
		class: "text",
		init: function(input) {
			input.type = "text";
			input.value = value || "";
			if (options.placeholder) {
				input.placeholder = options.placeholder;
			}
			input.addEventListener("input", function() {
				save(input.value);
			});
		}
	});
};
KarmaFieldMedia.fields.textarea = function(value, options, save) {
	return build({
		tag: "textarea",
		class: "text",
		init: function(element) {
			element.value = value || "";
			if (options.placeholder) {
				element.placeholder = options.placeholder;
			}
			element.addEventListener("input", function() {
				save(element.value);
			});
		}
	});
}

// KarmaMultimedia.buildSelector = function(values, current, onChange) {
// 	return build("select",
// 		values.map(function(value) {
// 			return build("option", value.name, function() {
// 				this.value = value.key;
// 				this.selected = value.key.toString() === current.toString();
// 			});
// 		}), function() {
// 			this.addEventListener("change", onChange);
// 		}
// 	);
// };

// KarmaMultimedia.buildTypeSelector = function(item, input, column, items, columns, updateRow, dispatcher) {
// 	item.type = item.type || input.values[0].key;
// 	return KarmaMultimedia.buildSelector(input.values, item.type, function() { // [{key: "", name:"-"}].concat(
// 		item.type = this.value;
// 		if (dispatcher.save) {
// 			dispatcher.save();
// 		}
// 		updateRow();
// 	})
// };


KarmaFieldMedia.fields.image = function(value, options, save) {
	var imageManager = KarmaFieldMedia.createImageUploader();
	imageManager.imageId = value || null;
	imageManager.mimeType = options.mimeType || null;
	return build({
		class: "image-input",
		onUpdate: function(attachment) {
			if (attachment) {
				return {
					child: build({
						class: "image-box",
						children: [
							build({
								tag: "img",
								init: function(img) {
									img.src = attachment.url;
								}
							}),
							build({
								class: "image-name",
								text: attachment.filename || "?"
							})
						],
						init: function(imageBox) {
							imageBox.addEventListener("click", function() {
								imageManager.open();
							});
						}
					})
				};
			} else {
				return {
					child: build({
						tag: "button",
						class: "button",
						text: "Ajouter",
						init: function(button) {
							button.onclick = function(event) {
								event.preventDefault();
								imageManager.open();
							};
						}
					})
				};
			}
		},
		init: function(element, update) {
			imageManager.onSelect = function(attachments) {
				if (attachments.length) {
					var attachment = attachments[0];
					value = attachment.id;
					update({
						url: attachment.sizes && attachment.sizes.thumbnail.url || attachment.icon,
						filename: attachment.filename
					});
				} else {
					value = null;
					update();
				}
				save(value);
			};
			if (imageManager.imageId) {
				KarmaFieldMedia.getImageSrc(imageManager.imageId).then(function(results) {
					update({
						url: results.url,
						filename: results.filename || "?"
					});
				});
			} else {
				update();
			}
		}
	});
}







// KarmaMultimedia.buildImageInput = function(item, input, column, items, columns, updateRow, dispatcher, updateAll) {
// 	var imageManager = createImageUploader(input.mimetype, input.multiple);
// 	return buildNode("div.image-input", function(update) {
// 		imageManager.imageId = item[input.key];
// 		imageManager.onSelect = function(attachments) {
// 			if (attachments.length) {
// 				var attachment = attachments.shift();
// 				item[input.key] = attachment.id;
// 				// KarmaMultimedia.attachments[attachment.id] = {
// 				// 	filename: attachment.filename,
// 				// 	src: attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url || attachment.icon
// 				// };
// 				if (input.multiple && attachments.length) {
// 					attachments.forEach(function(attachment) {
// 						var newitem = {
// 							type: item.type
// 						}
// 						newitem[input.key] = attachment.id;
// 						items.push(newitem);
// 						// KarmaMultimedia.attachments[attachment.id] = {
// 						// 	filename: attachment.filename,
// 						// 	src: attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url || attachment.icon
// 						// };
// 					});
// 					updateAll();
// 				} else {
// 					update();
// 				}
// 				if (dispatcher.save) {
// 					dispatcher.save();
// 				}
// 			}
//
// 		};
// 		if (item[input.key]) {
// 			return build("div.media-box-image",
// 				buildPromise("div.image-box", null, function(updateImage, results) {
// 					this.addEventListener("click", function() {
// 						imageManager.open();
// 					});
// 					return build("div.image-box-content",
// 						build("img", function() {
// 							this.src = results.url;
// 						}),
// 						build("span.image-name", results.filename || "?")
// 					);
// 				}, KarmaMultimedia.getImageSrc(item[input.key])),
//
// 				// buildPromise("img", null, function(updateImage, results) {
// 				// 	this.src = results.url;
// 				// 	this.addEventListener("click", function() {
// 				// 		imageManager.open();
// 				// 	});
// 				// }, KarmaMultimedia.getImageSrc(item[input.key])),
// 				// build("span.image-name", attachment && attachment.filename || "?")
//
// 			);
// 		} else {
// 			return build("button.button", "Ajouter", function() {
// 				this.addEventListener("click", function(event) {
// 					event.preventDefault();
// 					imageManager.open();
// 				});
// 			});
// 		}
// 	});
// };




KarmaFieldMedia.fields.gallery = function(value, options, save) {
	var galleryManager = KarmaFieldMedia.createGalleryUploader();
	galleryManager.mimeTypes = options.mimeTypes || ["image"];
	galleryManager.imageIds = value;

	return build({
		class: "gallery-input",
		onUpdate: function(attachments) {
			if (attachments.length) {
				return {
					child: build({
						class: "image-box",
						children: attachments.map(function(attachment) {
							return build({
								class: "image-box-thumb",
								onUpdate: function(src) {
									return {
										child: build({
											tag: "img",
											init: function(img) {
												img.src = src;
											}
										})
									};
								},
								init: function(thumb, update) {
									if (typeof attachment === "object") {
										update(attachment.attributes.sizes && attachment.attributes.sizes.thumbnail.url || attachment.attributes.icon);
									} else {
										KarmaFieldMedia.getImageSrc(attachment).then(function(results) {
											update(results.url);
										});
									}
								}
							});
						}),
						init: function(galleryBox) {
							galleryBox.addEventListener("click", function() {
								galleryManager.open();
							});
						}
					}),
				};
			} else {
				return {
					child: build({
						tag: "button",
						class: "button",
						text: "Ajouter",
						init: function(button) {
							button.onclick = function(event) {
								event.preventDefault();
								galleryManager.open();
							};
						}
					})
				};
			}
		},
		init: function(element, update) {
			galleryManager.onChange = function(attachments) {
				value = attachments.map(function(attachment) {
					return attachment.id;
				});
				update(attachments);
				save(value);
			};
			update(galleryManager.imageIds || []);
		}
	});
}


// KarmaMultimedia.buildGalleryInput = function(item, input, column, items, columns, updateRow, dispatcher) {
// 	var manager = createGalleryUploader();
// 	return buildNode("div.gallery-input", function(update) {
// 		manager.imageIds = item[input.key];
// 		manager.onChange = function(attachments) {
// 			item[input.key] = attachments.map(function(attachment) {
// 				// KarmaMultimedia.attachments[attachment.id] = {
// 				// 	filename: attachment.filename,
// 				// 	src: attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url || attachment.icon
// 				// };
// 				return attachment.id;
// 			});
// 			if (dispatcher.save) {
// 				dispatcher.save();
// 			}
// 			update();
// 		};
// 		if (item[input.key]) {
// 			return build("div.media-box-gallery", item[input.key].map(function(id) {
// 				// var attachment = KarmaMultimedia.attachments && KarmaMultimedia.attachments[id];
// 				return build("div.media-box-gallery-thumb",
// 					// build("img", function() {
// 					// 	this.src = attachment && attachment.src || KarmaMultimedia.ajax_url+"?action=karma_multimedia_get_image&id="+id;
// 					// }),
// 					buildPromise("img", null, function(updateImage, results) {
// 						this.src = results.url;
// 					}, KarmaMultimedia.getImageSrc(id))
// 					// build("input", function() {
// 					// 	this.type = "hidden";
// 					// 	this.name = "karma-mm-attachments[]";
// 					// 	this.value = id;
// 					// })
// 				);
// 				// var img = build("img");
// 				// img.src = KarmaMultimedia.ajax_url+"?action=karma_multimedia_get_image&id="+id;
// 				// return img;
// 			}), function() {
// 				this.addEventListener("click", function() {
// 					manager.open();
// 				});
// 			});
// 		} else {
// 			return build("button.button", "Ajouter", function() {
// 				this.addEventListener("click", function(event) {
// 					event.preventDefault();
// 					manager.open();
// 				});
// 			});
// 		}
// 	});
// };

// KarmaMultimedia.buildCustomSelector = function(item, input, column, items, columns, updateRow, dispatcher) {
// 	item[input.key] = item[input.key] || input.values[0].key.toString();
// 	return KarmaMultimedia.buildSelector(input.values, item[input.key.toString()], function() { // [{key: "", name:"-"}].concat(
// 		item[input.key] = this.value;
// 		if (dispatcher.save) {
// 			dispatcher.save();
// 		}
// 	})
// };



// KarmaMultimedia.buildTextInput = function(item, input, column, items, columns, updateRow, dispatcher) {
// 	if (!item[input.key] || typeof item[input.key] === "string") {
// 		item[input.key] = {};
// 	}
// 	return build("input.text", function() {
// 		this.type = "text";
// 		if (input.translatable) {
// 			this.placeholder = item[input.key].placeholder || "";
// 		} else if (input.placeholder) {
// 			this.placeholder = input.placeholder;
// 		}
// 		this.value = item[input.key].text || input.default || "";
// 		this.addEventListener("blur", function() {
// 			item[input.key].text = this.value;
// 			if (dispatcher.save) {
// 				dispatcher.save();
// 			}
// 		});
// 	});
// }
// KarmaMultimedia.buildBasicTextInput = function(item, input, column, items, columns, updateRow, dispatcher) {
// 	return build("input.text", function() {
// 		this.type = "text";
// 		this.value = item[input.key] || input.default_key && item[input.default_key] || "";
// 		if (input.placeholder) {
// 			this.placeholder = input.placeholder;
// 		}
// 		this.addEventListener("input", function() {
// 			item[input.key] = this.value;
// 			if (dispatcher.save) {
// 				dispatcher.save();
// 			}
// 		});
// 	});
// }
// KarmaMultimedia.buildBasicTextArea = function(item, input, column, items, columns, updateRow, dispatcher) {
// 	return build("textarea.text", function() {
// 		this.value = item[input.key] || input.default_key && item[input.default_key] || "";
// 		if (input.placeholder) {
// 			this.placeholder = input.placeholder;
// 		}
// 		this.addEventListener("input", function() {
// 			item[input.key] = this.value;
// 			if (dispatcher.save) {
// 				dispatcher.save();
// 			}
// 		});
// 	});
// }

// KarmaMultimedia.fields.date = function(item, input, column, items, columns, save) {
// 	return DateField.buildDateField(item[input.key], input, function(sqlDate) {
// 		item[input.key] = sqlDate;
// 		save();
// 	});
// }

// KarmaMultimedia.buildDateInput = function(item, input, column, items, columns, updateRow, dispatcher) {
// 	return build("input.text", function() {
// 		var dateManager = createDatePopupManager(this);
// 		dateManager.sqlDate = item[input.key] || input.default_key && item[input.default_key] || "";
// 		dateManager.init();
// 		this.type = "text";
//
// 		// this.value = item[input.key] || input.default_key && item[input.default_key] || "";
// 		if (input.placeholder) {
// 			this.placeholder = input.placeholder;
// 		}
//
// 		dateManager.onUpdate = function() {
//
// 			item[input.key] = dateManager.sqlDate;
// 			if (dispatcher.save) {
// 				dispatcher.save();
// 			}
// 		};
// 	});
// }

// KarmaMultimedia.fields.multidate = function(item, input, column, items, columns, updateRow, dispatcher) {
// 	if (!item[input.key]) {
// 		item[input.key] = [""];
// 	}
// 	return buildNode("div.multidate", function(updateDates) {
// 		return build("div.multidate-content",
// 			item[input.key] && item[input.key].length && build("ul", item[input.key].map(function(sqlDate, index) {
// 				return build("li",
// 					build("input.text", function() {
// 						var dateManager = createDatePopupManager(this);
// 						dateManager.sqlDate = sqlDate;
// 						dateManager.init();
// 						dateManager.onUpdate = function() {
//
// 							item[input.key][index] = dateManager.sqlDate;
// 							// updateDates();
// 							if (dispatcher.save) {
// 								dispatcher.save();
// 							}
// 						};
// 					}),
// 					index > 0 && build("button.button", "✕", function() {
// 						this.addEventListener("click", function(event) {
// 							event.preventDefault();
// 							item[input.key].splice(index, 1);
// 							updateDates();
// 						});
// 					})
// 				);
// 			})),
// 			build("button.button", input.more_button || "+", function() {
// 				this.addEventListener("click", function(event) {
// 					event.preventDefault();
// 					item[input.key].push(item[input.key].length && item[input.key][item[input.key].length-1] || "");
// 					updateDates();
// 				});
// 			})
// 		);
// 	});
// }


// KarmaFieldMedia.fields.select_post = function(value, options, save) {
// 	return build({
// 		class: "post-selector",
// 		onUpdate: function(posts) {
// 			return {
// 				child: KarmaFieldMedia.fields.select(posts, options, save)
// 			}
// 		},
// 		init: function(container) {
// 			KarmaFieldMedia.karma_field_query_posts({
// 				post_type: 'trainer'
// 			}).then(function(results) {
// 				console.log(results);
// 				update(results.posts || []);
// 			});
// 		}
// 	});
// };


function parseArgs(element, args) {
	var update;
	if (args.class) {
		element.className = args.class;
	}
	if (args.text) {
		element.innerHTML = args.text;
	}
	if (args.child) {
		element.appendChild(args.child);
	}
	if (args.children) {
		for (var i = 0; i < args.children.length; i++) {
			element.appendChild(args.children[i]);
		}
	}
	if (args.promise) { // deprecated
		var onPromise = args.onPromise;
		args.promise.then(function(results) {
			if (onPromise) {
				if (args) {
					unparseArgs(element, args);
				}
				args = onPromise(results, element);
				if (args) {
					parseArgs(element, args);
				}
			}
		});
	}
	if (args.start) { // deprecated
		var update = function(arg) {
			if (args) {
				unparseArgs(element, args);
			}
			args = onUpdate && onUpdate(arg, update, element);
			if (args) {
				parseArgs(element, args);
			}
		}
		var onUpdate = args.start(update);
	}
	// if (args.onUpdate) {
	// 	var onUpdate = args.onUpdate;
	// 	update = function(arg) {
	// 		if (args) {
	// 			unparseArgs(element, args);
	// 		}
	// 		args = onUpdate && onUpdate(arg, update, element);
	// 		if (args) {
	// 			parseArgs(element, args);
	// 		}
	// 	};
	// }


	// if (args.start) {
	// 	var update = function(arg) {
	// 		if (args) {
	// 			unparseArgs(element, args);
	// 		}
	// 		args = args.onUpdate && args.onUpdate(arg, update, element);
	// 		if (args) {
	// 			parseArgs(element, args);
	// 		}
	// 	}
	// 	var args = args.start(update);
	// 	parseArgs(element, args);
	// }
	//
	// if (args.onUpdate) {
	// 	var onUpdate = args.onUpdate;
	// 	var update = function() {
	// 		if (args) {
	// 			unparseArgs(element, args);
	// 		}
	// 		args = onUpdate(update, element);
	// 		if (args) {
	// 			parseArgs(element, args);
	// 		}
	// 	}
	// 	update();
	// }

	// if (args.start) {
	// 	var onUpdate = args.onUpdate;
	// 	var update = function(arg) {
	// 		if (args) {
	// 			unparseArgs(element, args);
	// 		}
	// 		args = onUpdate(arg, update, element);
	// 		if (args) {
	// 			parseArgs(element, args);
	// 		}
	// 	}
	// 	args.start(update);
	// }


	if (args.init) {
		var update;
		if (args.onUpdate) {
			var onUpdate = args.onUpdate;
			update = function(arg) {
				var newargs = onUpdate(arg, update, element);
				if (newargs) {
					unparseArgs(element, args);
					parseArgs(element, newargs);
					args = newargs;
				}
			};
		}
		// if (args.onSelfUpdate) {
		// 	var onUpdate = args.onSelfUpdate;
		// 	update = function() {
		// 		var newargs = onUpdate(update, element);
		// 		if (newargs) {
		// 			unparseArgs(element, args);
		// 			parseArgs(element, newargs);
		// 			args = newargs;
		// 		}
		// 	};
		// }
		args = args.init(element, update);
		if (args) {
			parseArgs(element, args);
		}
	}
}
function unparseArgs(element, args) {
	if (args.children) {
		for (var i = 0; i < args.children.length; i++) {
			element.removeChild(args.children[i]);
		}
	}
	if (args.child) {
		element.removeChild(args.child);
	}
	if (args.text) {
		element.innerHTML = "";
	}
}


/**
 * build (V3)
 */
function build(args) {
	var element = document.createElement(args.tag || "div");
	parseArgs(element, args);
	return element;
}

// var callback;
//
// var a = build({
// 	tag: "a",
// 	init: function(a) {
// 		a.onclick = function() {
// 			callback && callback(a.getAttribute("data-src"));
// 		}
// 	}
// });
//
// var b = build({
// 	start: function(update) {
// 		callback = update;
// 		return function(src) {
// 			return {
// 				child: build({
// 					tag: "img",
// 					init: function(img) {
// 						img.src = src
// 					}
// 				})
// 			};
// 		};
// 	},
// 	start: function(update) {
// 		callback = update;
// 		return {
// 			onUpdate: function(src) {
// 				return {
// 					child: build({
// 						tag: "img",
// 						init: function(img) {
// 							img.src = src
// 						}
// 					})
// 				};
// 			}
// 		};
// 	}
// 	// onUpdate: function(src) {
// 	//
// 	// 	return {
// 	// 		child: build({
// 	// 			tag: "img",
// 	// 			init: function(img) {
// 	// 				img.src = src
// 	// 			}
// 	// 		})
// 	// 	}
// 	// }
// });
//
//
// var b = build({
// 	start: function(update) {
// 		callback = update;
// 	},
// 	onUpdate: function(arg) {
// 		return {
// 			child: build({
// 				tag: "img",
// 				init: function(img) {
// 					img.src = src
// 				}
// 			})
// 		};
// 	}
//
// 	// start: function(update) {
// 	// 	callback = update;
// 	// 	return {
// 	// 		child: build({
// 	// 			tag: "img",
// 	// 			init: function(img) {
// 	// 				img.src = src
// 	// 			}
// 	// 		})
// 	// 	};
// 	// },
// 	// init: function(element, update) {
// 	// 	callback = update;
// 	// 	return function(src) {
// 	// 		return {
// 	// 			child: build({
// 	// 				tag: "img",
// 	// 				init: function(img) {
// 	// 					img.src = src
// 	// 				}
// 	// 			})
// 	// 		};
// 	// 	};
// 	// }
// 	// onUpdate: function(src) {
// 	//
// 	// 	return {
// 	// 		child: build({
// 	// 			tag: "img",
// 	// 			init: function(img) {
// 	// 				img.src = src
// 	// 			}
// 	// 		})
// 	// 	}
// 	// }
// })

/**
 * Builder
 */
function build(tag) {
	var classes = tag.split(".");
	var element = document.createElement(classes[0]);
	if (classes.length > 1) {
		element.className = classes.slice(1).join(" ");
	}
	for (var i = 1; i < arguments.length; i++) {
		if (typeof arguments[i] === "function") {
			arguments[i].call(element, element);
		} else if (Array.isArray(arguments[i])) {
			arguments[i].forEach(function(child) {
				element.appendChild(child);
			});
		} else if (arguments[i] && typeof arguments[i] === "object") {
			element.appendChild(arguments[i]);
		} else if (arguments[i]) {
			element.innerHTML = arguments[i].toString();
		}
	}
	return element;
}

// function buildNode(callback) {
// 	return function(container) {
// 		var content;
// 		function update() {
// 			if (content) {
// 				container.removeChild(content);
// 			}
// 			content = callback(update);
// 			container.appendChild(content);
// 		}
// 		update();
// 	};
// }

/**
 * buildNode
 */
function buildNode(tag, callback, init) {
	var element = build(tag);
	var child;


	// var onRemoves = [];
	function update() {
		// onRemoves.forEach(function(callback) {
		// 	callback.call();
		// });
		// onRemoves = [];
		if (child) {
			if (Array.isArray(child)) {
				child.forEach(function(item) {
					element.removeChild(item);
				});
			} else {
				element.removeChild(child);
			}
		}
		child = callback.call(element, update);
		if (child) {
			if (Array.isArray(child)) {
				child.forEach(function(item) {
					element.appendChild(item);
				});
			} else {
				element.appendChild(child);
			}
		}
	}
	if (init) {
		init.call(element, update);
	}
	update();
	return element;
}

/**
 * buildPromise
 */
function buildPromise(tag, child, callback, promise, init) {
	var element = build(tag, child);
	var results;
	function update() {
		if (child) {
			if (Array.isArray(child)) {
				child.forEach(function(item) {
					element.removeChild(item);
				});
			} else {
				element.removeChild(child);
			}
		}
		child = callback.call(element, update, results);
		if (child) {
			if (Array.isArray(child)) {
				child.forEach(function(item) {
					element.appendChild(item);
				});
			} else {
				element.appendChild(child);
			}
		}
	}
	if (init) {
		init.call(element, update);
	}
	promise.then(function(r) {
		results = r;
		update();
	});
	//update();
	return element;
}


/**
 * buildNode
 */
function buildWhen(tag, child, callback, condition) {
	var element = build(tag, child);
	function update() {
		if (child) {
			if (Array.isArray(child)) {
				child.forEach(function(item) {
					element.removeChild(item);
				});
			} else {
				element.removeChild(child);
			}
		}
		child = callback.call(element, update);
		if (child) {
			if (Array.isArray(child)) {
				child.forEach(function(item) {
					element.appendChild(item);
				});
			} else {
				element.appendChild(child);
			}
		}
	}
	function onFrame() {
		if (condition.call(element)) {
			update();
		} else {
			window.requestAnimationFrame(onFrame);
		}
	}
	onFrame();
	return element;
}

/**
 * buildNode
 */
// function buildWhen(tag, child, callback, condition) {
// 	var element = buildPromise(tag, child, callback, promise);
// 	var promise = new Promise(function(resolve, reject) {
// 		function update() {
// 			var result = condition.call(element);
// 			if (result) {
// 				resolve(result);
// 			} else {
// 				window.requestAnimationFrame(update);
// 			}
// 		}
// 		update();
// 	});
// 	return element;
// }

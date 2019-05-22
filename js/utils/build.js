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

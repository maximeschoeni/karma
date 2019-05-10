/**
 * Swipe
 */
function createSwipeManager() {
	var manager = {
		element: null,
		active: true,
		trackTouch: true,
		trackMouse: false,
		trackH: true,
		trackV: false,
		mouseX: null,
		mouseY: null,
		deltaX: 0,
		deltaY: 0,
		absDX: 0,
		absDY: 0,
		maxDX: 0,
		maxDY: 0,
		deltaThreshold: 0,
		init: function() {
			if (this.element) {
				if (manager.trackMouse) {
					this.element.addEventListener("mousedown", onStart);
				}
				if (manager.trackTouch) {
					this.element.addEventListener("touchstart", onStart);
				}
			}
		},
		remove: function() {
			if (this.element) {
				if (manager.trackMouse) {
					this.element.removeEventListener("mousedown", onStart);
				}
				if (manager.trackTouch) {
					this.element.removeEventListener("touchstart", onStart);
				}
			}
		}
	};
	function onStart(event) {
		manager.mouseX = event.clientX || event.touches[0].clientX;
		manager.mouseY = event.clientY || event.touches[0].clientY;
		if (manager.onstart) {
			manager.onstart(event);
		}
		if (manager.trackMouse) {
			document.addEventListener("mousemove", onMove);
			document.addEventListener("mouseup", onEnd);
		}
		if (manager.trackTouch) {
			document.addEventListener("touchmove", onMove);
			document.addEventListener("touchend", onEnd);
		}
	}
	function onMove(event) {
		var x = event.clientX || event.touches[0].clientX;
		var y = event.clientY || event.touches[0].clientY;
		manager.deltaX += x - manager.mouseX;
		manager.deltaY += y - manager.mouseY;
		manager.absDX = Math.abs(manager.deltaX);
		manager.absDY = Math.abs(manager.deltaY);
		manager.maxDX = Math.max(manager.absDX, manager.maxDX);
		manager.maxDY = Math.max(manager.absDY, manager.maxDY);
		manager.mouseX = x;
		manager.mouseY = y;
		if (manager.onmove) {
			manager.onmove(event);
		}
		// if (!manager.swipe) {
		// 	// if (manager.trackH && manager.absDX > manager.absDY && manager.absDX > manager.deltaThreshold || manager.trackV && manager.absDY > manager.absDX && manager.absDY > manager.deltaThreshold) {
		// 		if (manager.trackMouse) {
		// 			document.addEventListener("mouseup", onEnd);
		// 		}
		// 		if (manager.trackTouch) {
		// 			document.addEventListener("touchend", onEnd);
		// 		}
		// 		manager.swipe = true;
		// 	// } else {
		// 	// 	event.preventDefault();
		// 	// }
		// }
	}
	function onEnd(event) {
		if (manager.onswipe) {
			manager.onswipe(event);
		}
		manager.mouseX = null;
		manager.mouseY = null;
		manager.deltaX = 0;
		manager.deltaY = 0;
		manager.absDX = 0;
		manager.absDY = 0;
		manager.maxDX = 0;
		manager.maxDY = 0;
		manager.swipe = false;
		if (manager.trackMouse) {
			document.removeEventListener("mousemove", onMove);
			document.removeEventListener("mouseup", onEnd);
		}
		if (manager.trackTouch) {
			document.removeEventListener("touchmove", onMove);
			document.removeEventListener("touchend", onEnd);
		}
	};
	return manager;
}


// function registerSwipe(element, settings) {
// 	settings = settings || {};
// 	var touch = false;
// 	var mouseX;
// 	var mouseY;
// 	var deltaX = 0;
// 	var deltaY = 0;
// 	var maxDX = 0;
// 	var maxDY = 0;
// 	var touchstart = settings.touchstart || "touchstart";
// 	var touchmove = settings.touchmove || "touchmove";
// 	var touchend = settings.touchend || "touchend";
// 	var active = true;
// 	var onStart = function(event) {
// 		if (active) {
// 			mouseX = event.clientX || event.touches[0].clientX;
// 			mouseY = event.clientY || event.touches[0].clientY;
// 			touch = true;
// 			element.dispatchEvent(new CustomEvent("swipestart", {detail: {
// 				event: event
// 			}}));
// 		}
// 	};
// 	var onMove = function(event) {
// 		if (active && touch) {
// 			var x = event.clientX || event.touches[0].clientX;
// 			var y = event.clientY || event.touches[0].clientY;
// 			deltaX += x - mouseX;
// 			deltaY += y - mouseY;
// 			maxDX = Math.max(Math.abs(deltaX), maxDX);
// 			maxDY = Math.max(Math.abs(deltaY), maxDY);
// 			mouseX = x;
// 			mouseY = y;
// 			element.dispatchEvent(new CustomEvent("swipemove", {detail: {
// 				deltaX: deltaX,
// 				deltaY: deltaY,
// 				event: event
// 			}}));
// 		}
// 	};
// 	var onEnd = function(event) {
// 		if (active) {
// 			element.dispatchEvent(new CustomEvent("swipe", {detail: {
// 				deltaX: deltaX,
// 				deltaY: deltaY,
// 				maxDX: maxDX,
// 				maxDY: maxDY,
// 				event: event
// 			}}));
// 			mouseX = 0;
// 			mouseY = 0;
// 			deltaX = 0;
// 			deltaY = 0;
// 			maxDX = 0;
// 			maxDY = 0;
// 			touch = false;
// 		}
// 	};
// 	var unregister = function() {
// 		element.removeEventListener(touchstart, onStart);
// 		element.removeEventListener(touchmove, onMove);
// 		element.removeEventListener(touchend, onEnd);
// 		element.removeEventListener("unregister", unregister);
// 	}
// 	if (settings.mouseEmulation) {
// 		touchstart = "mousedown";
// 		touchmove = "mousemove";
// 		touchend = "mouseup";
// 	}
// 	element.addEventListener(touchstart, onStart);
// 	element.addEventListener(touchmove, onMove);
// 	element.addEventListener(touchend, onEnd);
// 	element.addEventListener("unregister", unregister);
//
//
// 	window.addEventListener("deactivate-swipe", function() {
// 		active = false;
// 	});
// 	window.addEventListener("reactivate-swipe", function() {
// 		active = true;
// 	});
// }

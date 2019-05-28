function createStickyManager() {
	var manager = {
		element: null,
		windowY: 0,
		stickOffset: 0,
		offsetY: 0,
		duration: 300,
		easing: "easeInOutSine",
		getDocumentHeight: function() {
			return Math.max(
				document.body.scrollHeight,
				document.body.offsetHeight,
				document.body.clientHeight,
				document.documentElement.scrollHeight,
				document.documentElement.offsetHeight,
				document.documentElement.clientHeight
	    );
		},
		open: function() {
			TinyAnimate.animate(this.offsetY, window.pageYOffset + this.windowY, this.duration, function(value) {
				manager.offsetY = Math.floor(value);
				manager.element.style.position = "absolute";
				manager.element.style.top = value.toFixed() + "px";
			}, this.easing);
		},
		close: function() {
			TinyAnimate.animate(this.offsetY, window.pageYOffset + this.windowY - this.element.clientHeight, this.duration, function(value) {
				manager.offsetY = Math.floor(value);
				manager.element.style.position = "absolute";
				manager.element.style.top = value.toFixed() + "px";
			}, this.easing);
		},
		update: function() {
			if (window.pageYOffset > this.offsetY + this.element.clientHeight - this.windowY) {
				this.offsetY = window.pageYOffset + this.windowY - this.element.clientHeight;
			} else if (window.pageYOffset < this.offsetY - this.windowY) {
				this.offsetY = window.pageYOffset + this.windowY;
			}
			//this.offsetY = Math.min(this.offsetY, this.getDocumentHeight() - window.innerHeight - this.element.clientHeight - this.stickOffset);
			this.offsetY = Math.max(this.offsetY, this.stickOffset);
			if (this.offsetY < window.pageYOffset + this.windowY) {
				this.element.style.position = "absolute";
				this.element.style.top = this.offsetY + "px";
			} else {
				this.element.style.position = "fixed";
				this.element.style.top = this.windowY + "px";
			}
			console.log("update", manager.offsetY);
		}
	};
	window.addEventListener("scroll", function(event) {
		manager.update();
	});
	return manager;
}


function registerSticky(element, windowY) {
	var manager = createStickyManager();
	manager.element = element;
	manager.windowY = windowY || 0;
	if (document.readyState === "complete") {
		manager.offsetY = element.offsetTop;
		manager.update();
	} else {
		window.addEventListener("load", function() {
			manager.offsetY = element.offsetTop;
			manager.update();
		});
	}
	return manager;
}


// function registerSticky(element, windowY) {
// 	windowY = windowY || 0;
// 	var stickOffset = 0;
// 	var offsetY;
// 	function init() {
// 		offsetY = element.offsetTop;
// 		window.addEventListener("scroll", function(event) {
//
// 			update();
// 		});
// 		update();
// 	}
// 	function update() {
//
// 		if (offsetY + element.clientHeight < window.pageYOffset + windowY) {
// 			offsetY = window.pageYOffset + windowY - element.clientHeight;
// 		} else if (offsetY > window.pageYOffset + windowY) {
// 			offsetY = window.pageYOffset + windowY;
// 		}
//
// 		var documentHeight = Math.max(
// 			document.body.scrollHeight,
// 			document.body.offsetHeight,
// 			document.body.clientHeight,
// 			document.documentElement.scrollHeight,
// 			document.documentElement.offsetHeight,
// 			document.documentElement.clientHeight
//     );
//
// 		offsetY = Math.min(offsetY, documentHeight - window.innerHeight - element.clientHeight - stickOffset);
// 		offsetY = Math.max(offsetY, stickOffset);
//
// 		if (offsetY < window.pageYOffset + windowY) {
// 			element.style.position = "absolute";
// 			element.style.top = offsetY + "px";
// 			console.log("absolute");
// 		} else {
// 			element.style.position = "fixed";
// 			element.style.top = windowY + "px";
// 			console.log("fixed");
// 		}
// 	}
// 	if (document.readyState === "complete") {
// 		init();
// 	} else {
// 		window.addEventListener("load", function() {
// 			init();
// 		});
// 	}
// }

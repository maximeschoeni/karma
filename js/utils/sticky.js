
function registerSticky(element, windowY) {
	windowY = windowY || 0;
	var stickOffset = 0;
	var offsetY;
	function init() {
		offsetY = element.offsetTop;
		window.addEventListener("scroll", function(event) {
			update();
		});
		update();
	}
	function update() {
		
		if (offsetY + element.clientHeight < window.pageYOffset + windowY) {
			offsetY = window.pageYOffset + windowY - element.clientHeight;
		} else if (offsetY > window.pageYOffset + windowY) {			
			offsetY = window.pageYOffset + windowY;
		}
		
		var documentHeight = Math.max(
			document.body.scrollHeight, 
			document.body.offsetHeight, 
			document.body.clientHeight, 
			document.documentElement.scrollHeight,
			document.documentElement.offsetHeight,
			document.documentElement.clientHeight
    );
		
		offsetY = Math.min(offsetY, documentHeight - window.innerHeight - element.clientHeight - stickOffset);
		offsetY = Math.max(offsetY, stickOffset);
		
		if (offsetY < window.pageYOffset + windowY) {
			element.style.position = "absolute";
			element.style.top = offsetY + "px";
			
		} else {
			element.style.position = "fixed";
			element.style.top = windowY + "px";
		}
	}
	if (document.readyState === "complete") {
		init();
	} else {
		window.addEventListener("load", function() {
			init();
		});
	}
}
document.addEventListener("DOMContentLoaded", function() {
	var baseDelay = 0;
	var incrementDelay = 100;
	function getWindowHeight() {
		return window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
	}
	function intersect(rect) {
  	return rect.top <= getWindowHeight(); // && 0 <= rect.bottom;
	}
	function anim(image, index) {
		image.classList.remove("image-anim");
		setTimeout(function() {
			image.classList.remove("image-offset");
		}, baseDelay + incrementDelay*index);
	}
	function update() {
		var images = document.querySelectorAll(".image-anim");
		for (var i = 0; i < images.length; i++) {
			if (intersect(images[i].parentNode.getBoundingClientRect())) {
				anim(images[i], i);
			}
		}
	}
	
	window.addEventListener("scroll", function() {
		update();
	});
	window.addEventListener("image-anim", function() {
		update();
	});
	
	update();
});	



// document.addEventListener("DOMContentLoaded", function() {
// 	
// 	
// 	
// 	var images = document.querySelectorAll(".image-offset");
// 	var globalIndex = 0;
// 	var delay = 100;
// 	
// 	function getWindowHeight() {
// 		return window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
// 	}
// 	function intersect(rect) {
//   	return rect.top <= getWindowHeight() && 0 <= rect.bottom;
// 	}
// 	function register(image) {
// 		console.log(image);
// 		function onScroll() {
// 			if (intersect(image.parentNode.getBoundingClientRect())) {
// 				setTimeout(function() {
// 					image.classList.remove("image-offset");
// 				}, delay*globalIndex);
// 				window.removeEventListener("scroll-image", onScroll);
// 				globalIndex++;
// 			}
// 		}
// 		window.addEventListener("scroll-image", onScroll);
// 	}
// 	for (var i = 0; i < images.length; i++) {
// 		register(images[i]);
// 	}
// 	
// 	window.addEventListener("scroll", function() {
// 		globalIndex = 0;
// 		window.dispatchEvent(new CustomEvent("scroll-image"));
// 	});
// 	window.dispatchEvent(new CustomEvent("scroll-image"));
// });	


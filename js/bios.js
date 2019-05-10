function registerBios(container) {
	var manager = createPopupManager();
	function register(container) {
		var containerHeight = container.clientHeight;
		var a = container.querySelector(".bio-header");
		var body = container.querySelector(".bio-body");
		var content = body.querySelector(".bio-content");
		var bio = {
			open: function() {
				container.classList.add("active");
			},
			close: function() {
				container.classList.remove("active");
			},
			render: function(value) {
				height = (content.clientHeight-containerHeight)*value + containerHeight;
				body.style.height = height.toFixed() + "px";
			}
		};
		if (a) {
			a.addEventListener("click", function() {
				manager.toggle(bio);
			});
		}
	}
	manager.onBeforeOpen = function(bio) {
		bio.open();
	};
	manager.onAfterClose = function(bio) {
		bio.close();
	};
	manager.onRender = function(bio, value) {
		bio.render(value);
	};
	manager.duration = 200;

	for (var i = 0; i < container.children.length; i++) {
		register(container.children[i]);
	}
}

document.addEventListener("DOMContentLoaded", function() {
	var container = document.getElementById("bios-container");
	if (container) {
		registerBios(container);
		// var manager = createPopupManager();
		// function register(container) {
		// 	var containerHeight = container.clientHeight;
		// 	var a = container.querySelector(".bio-header");
		// 	var body = container.querySelector(".bio-body");
		// 	var content = body.querySelector(".bio-content");
		// 	var bio = {
		// 		open: function() {
		// 			container.classList.add("active");
		// 		},
		// 		close: function() {
		// 			container.classList.remove("active");
		// 		},
		// 		render: function(value) {
		// 			height = (content.clientHeight-containerHeight)*value + containerHeight;
		// 			body.style.height = height.toFixed() + "px";
		// 		}
		// 	};
		// 	if (a) {
		// 		a.addEventListener("click", function() {
		// 			manager.toggle(bio);
		// 		});
		// 	}
		// }
		// manager.onBeforeOpen = function(bio) {
		// 	bio.open();
		// };
		// manager.onAfterClose = function(bio) {
		// 	bio.close();
		// };
		// manager.onRender = function(bio, value) {
		// 	bio.render(value);
		// };
		// manager.duration = 200;
		//
		// for (var i = 0; i < container.children.length; i++) {
		// 	register(container.children[i]);
		// }
	}
});

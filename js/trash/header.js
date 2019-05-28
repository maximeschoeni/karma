
// register menu
document.addEventListener("DOMContentLoaded", function() {
	var menu = document.getElementById("menu");
	var menuContent = document.getElementById("menu-content");
	var menuBtn = document.getElementById("menu-btn");
	var menuCloseBtn = document.getElementById("close-menu-btn");
	var manager = createPopupManager();
	var pagePopupManager = createPopupManager();

	function registerMenuItem(item) {
		var ul = item.querySelector("ul");
		var links = item.querySelectorAll("a");
		function registerPageLink(a) {
			if (ul && ul.children.length && a.parentNode === item) {
				a.addEventListener("click", function(event) {
					event.preventDefault();
					item.classList.toggle("current-menu-parent");
				});
			} else if (a.hasAttribute("data-json")) {
				var pageManager = createPageManager(a, pagePopupManager);
				// a.addEventListener("click", function(event) {
				// 	event.preventDefault();
				// 	pagePopupManager.toggle(pageManager, true);
				// });
			}
		}
		for (var i = 0; i < links.length; i++) {
			registerPageLink(links[i]);
		}
	}
	pagePopupManager.onBeforeOpen = function(pageManager) {
		document.body.classList.add("page-popup-open");
		scrollTo(0, 0);
		pageManager.open();
	};
	pagePopupManager.onAfterClose = function(pageManager) {
		pageManager.close();
		document.body.classList.remove("page-popup-open");
	};

	if (menu && menuContent) {
		manager.onBeforeOpen = function() {
			document.body.classList.add("menu-open");
		};
		manager.onAfterClose = function() {
			document.body.classList.remove("menu-open");
		};
		manager.onRender = function(menu, value) {
			menu.style.right = (menu.clientWidth*(-1+value)).toFixed() + "px";
		};
		for (var i = 0; i < menuContent.children.length; i++) {
			registerMenuItem(menuContent.children[i]);
		}
	}
	if (menuBtn) {
		menuBtn.addEventListener("click", function(event) {
			event.preventDefault();
			manager.update(menu);
		});
	}
	if (menuCloseBtn) {
		menuCloseBtn.addEventListener("click", function(event) {
			event.preventDefault();
			manager.update();
		});
	}
});


// register marquee
window.addEventListener("load", function() {
	var marquee = createMarquee();
	function update() {
		if (marquee.element.clientWidth < 530 || window.innerWidth < 800) {
			marquee.start();
		} else {
			marquee.stop();
		}
	}
	marquee.element = document.getElementById("marquee");
	marquee.speed = -0.5;
	window.addEventListener("resize", function() {
		update();
	});
	update();
});

// sticky header
(function() {
	var headerContent = document.getElementById("header-content");
	registerSticky(headerContent, 0);
})();

// ajax page
function createPageManager(menuLink, popupManager) {
	var promise = new Promise(function(resolve, reject) {
		ajaxGet(menuLink.getAttribute("data-json"), null, function(results) {
			resolve(results);
		});
	});
	var content;
	var manager = {
		open: function() {
			if (!content) {
				content = this.build();
			}
			document.body.appendChild(content);
			menuLink.classList.add("active");

			var biosContainers = document.querySelectorAll("#bios-container");
			for (var i = 0; i < biosContainers.length; i++) {
				registerBios(biosContainers[i]);
			}
		},
		close: function() {
			if (content) {
				document.body.removeChild(content);
				content = null;
			}
			menuLink.classList.remove("active");
		},
		build: function() {
			return build("div.page-popup-container",
				build("div.page-popup",
					build("h2", manager.pageContent.title),
					build("div.page-body", manager.pageContent.content)
				)
			);
		}
	};
	menuLink.addEventListener("click", function(event) {
		event.preventDefault();
		promise.then(function(results) {
			if (results.page) {
				manager.pageContent = results.page;
				popupManager.toggle(manager, true);
			}
		});
	});
	return manager;
}

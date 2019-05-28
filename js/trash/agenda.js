// agenda header
document.addEventListener("DOMContentLoaded", function() {
	var container = document.getElementById("agenda-header");
	var ul = document.getElementById("agenda-header-ul");
	var width = 0;
	var offset = 150;
	function getWidth() {
		width = 0;
		for (var i = 0; i < ul.children.length; i++) {
			width += ul.children[i].clientWidth;
		}
		return width;
	}
	if (container && ul) {
		container.addEventListener("mousemove", function(event) {
			var box = container.getBoundingClientRect();
			var diff = box.width - getWidth();

			var x = event.clientX - box.left;
			var left = offset + (diff - 2*offset) * (x/(box.width));
			left = Math.min(left, 0);
			left = Math.max(left, diff);

			ul.style.left = left.toFixed() + "px";


		});
	}
});

// agenda body
document.addEventListener("DOMContentLoaded", function() {
	var container = document.getElementById("agenda-body");
	var ul = document.getElementById("agenda-body-ul");
	var popupManager = createPopupManager();

	function register(item) {
		var a = item.querySelector("a");
		var promise = new Promise(function(resolve, reject) {
			ajaxGet(a.getAttribute("data-json"), null, function(results) {
				resolve(results);
			});
		});
		var agendaEvent = createAgendaEvent(item, a, popupManager);
		a.addEventListener("click", function(event) {
			event.preventDefault();
			promise.then(function(results) {
				agendaEvent.eventData = results;
				popupManager.toggle(agendaEvent);
			});
			// popupManager.toggle(agendaEvent);
		});
		if (location.hash.slice(1) === item.getAttribute("data-hash")) {
			// popupManager.update(agendaEvent);
			promise.then(function(results) {
				agendaEvent.eventData = results;
				// popupManager.onAfterOpen = function(agendaEvent) {
				//
				//
				// 	// TinyAnimate.animate(window.pageYOffset, 1, popupManager.duration, function(value) {
				// 	// 	if (manager.onRender) {
				// 	// 		manager.onRender(element, value, true);
				// 	// 	}
				// 	// }, manager.easing, function() {
				// 	// 	if (manager.onAfterOpen) {
				// 	// 		manager.onAfterOpen(element);
				// 	// 	}
				// 	// 	if (callback) {
				// 	// 		callback.call(manager);
				// 	// 	}
				// 	// });
				//
				// 	popupManager.onAfterOpen = null;
				// }
				popupManager.update(agendaEvent, true);
				agendaEvent.adjustScroll();
			});
		}
	}
	popupManager.onBeforeOpen = function(agendaEvent) {
		agendaEvent.open();
	}
	// popupManager.onAfterOpen = function(agendaEvent) {
	// 	agendaEvent.adjustScroll();
	// }
	popupManager.onAfterClose = function(agendaEvent) {
		agendaEvent.close();
	}
	popupManager.onRender = function(agendaEvent, value) {
		agendaEvent.render(value);
	}
	popupManager.duration = 300;

	if (ul) {
		for (var i = 0; i < ul.children.length; i++) {
			register(ul.children[i]);
		}
	}
});


function createAgendaEvent(item, a, popupManager) {


	var agendaEvent = {
		body: null,
		content: null,
		slideshow: null,
		open: function() {
			buildAgendaEvent(this);
			item.appendChild(this.body);
			registerGridSlideshow(this.slideshow);
			this.slideshow.dispatchEvent(new CustomEvent("update"));
			this.handleHeight = a.clientHeight;
			a.style.display = "none";
		},
		close: function() {
			item.removeChild(this.body);
			this.body = null;
			a.style.display = "block";
		},
		render: function(value) {
			height = (this.content.clientHeight-this.handleHeight)*value + this.handleHeight;
			this.body.style.height = height.toFixed() + "px";
		},
		adjustScroll: function() {
			//scrollTo(0, item.offsetTop + item.offsetParent.offsetTop);
			scrollTo(0, item.offsetTop);
		}
	};
	function buildAgendaEvent() {
		var event = agendaEvent.eventData;
		var fullPlace = event.place + (event.city ? ", " + event.city : "") + (event.country ? " (" + event.country + ")" : "");
		var fullDate = Calendar.formatRange(event.start_date, event.end_date) + (event.hour ? ", " + event.hour : "");
		var postContent = event.project && event.project.content || event.content;
		var description = event.project && event.project.description || event.description;
		var auteur = event.project && event.project.auteur || event.auteur;
		var images = event.project && event.project.images || event.images || [];
		var program = event.project && event.project.program || "";
		var title = event.project && event.project.title || "";
		agendaEvent.slideshow = build("div.slideshow",
			build("div.controller"),
			build("div.library", images.map(function(sources) {
				var image = (window.createBackgroundImage) ? createBackgroundImage(sources, "cover", "center") : build("div.background-image", function() {
					this.style.backgroundImage = "url("+sources[0].src+")";
					this.style.backgroundSize = "cover";
					this.style.backgroundPosition = "center";
				});
				image.classList.add("image");
				return build("div.slide", image);
			}))
		);
		agendaEvent.content = build("div.event-content",
			build("div.columns",
				build("div.column.left",
					build("div.place",
						build("a", function() {
							this.href = a.href;
							this.addEventListener("click", function(event) {
								event.preventDefault();
								popupManager.update();
							});
						},
							build("div", fullDate),
							event.name && build("div", event.name),
							build("div", fullPlace)
						)
					),
					event.project && event.project.events && event.project.events.map(function(relEvent) {
						var relFullPlace = relEvent.place + (relEvent.city ? ", " + relEvent.city : "") + (relEvent.country ? " (" + relEvent.country + ")" : "");
						var relFullDate = Calendar.formatRange(relEvent.start_date, relEvent.end_date) + (relEvent.hour ? ", " + relEvent.hour : "");
						return build("div.place",
							build("div", relFullDate),
							relEvent.name && build("div", relEvent.name),
							build("div", relFullPlace)
						)
					}),
					build("div.event-text",
						build("h2",
							build("strong", title),
						),
						build("h3", description),
						build("div", postContent)
					)
				),
				build("div.column.right",
					build("h2", "Programme"),
					build("div.event-text.program", program)
				)
			),
			images && agendaEvent.slideshow
		);
		agendaEvent.body = build("div.event.single.event-body",
			build("div.event-slider",
				agendaEvent.content
			)
		);
	}
	return agendaEvent;
}

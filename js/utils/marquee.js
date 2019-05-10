/**
 * Marquee
 */
function createMarquee() {
	var animationId;
	var container;
	var items = [];
	var index = 0;
	var x = 0;
	var marquee = {
		element: null,
		// segments: [],
		// items: [],
		// width: 0,
		speed: -1,
		x: 0,
		// space: 1,
		// index: 0,
		// container: null,
		start: function() {
			if (!container) {
				while (this.element && this.element.children.length) {
					items.push(this.element.children[0]);
					this.element.removeChild(this.element.children[0]);
				}
				container = document.createElement("div");
				container.className = "marquee-content";
				container.style.position = "relative";
				container.style.display = "inline-block";
				container.style.whiteSpace = "nowrap";
				this.element.style.overflow = "hidden";
				this.element.appendChild(container);
				x = 0;
				this.play();
			}
		},
		stop: function() {
			if (container) {
				this.pause();
				this.element.removeChild(container);
				container = null;
				for (var i = 0; i < items.length; i++) {
					this.element.appendChild(items[i]);
				}
				items = [];
			}
		},
		update: function() {

			// remove first
			if (this.speed < 0 && container.children.length && x < -container.children[0].clientWidth) {
				x += container.children[0].clientWidth;
				container.removeChild(container.children[0]);
			}
			// remove last
			if (this.speed > 0 && container.children.length && container.clientWidth + x - container.children[container.children.length-1].clientWidth > this.element.clientWidth) {
				container.removeChild(container.children[container.children.length-1]);
			}
			// append
			if (this.speed < 0 && container.clientWidth + x < this.element.clientWidth) {
				var clone = items[index].cloneNode(true);
				clone.style.display = "inline-block";
				container.appendChild(clone);
				index++;
				if (index >= items.length) {
					index = 0;
				}
			}
			// prepend
			if (this.speed > 0 && x > 0 && items.length > index) {
				var clone = items[index].cloneNode(true);
				clone.style.display = "inline-block";
				container.insertBefore(clone, container.firstChild);
				x -= clone.clientWidth;
				index--;
				if (index < 0) {
					index = items.length - 1;
				}
			}
			container.style.left = x.toFixed()+"px";
		},




		// init: function() {
		// 	while (this.segments.length > 0) {
		// 		var segment = this.segments.shift();
		// 		this.element.removeChild(segment.element);
		// 	}
		// 	while (items.length) {
		// 		var item = items.shift();
		// 		this.element.appendChild(item.element);
		// 	}
		// 	while (this.element && this.element.children.length) {
		// 		this.element.children[0].style.position = "relative";
		// 		this.element.children[0].style.display = "inline-block";
		// 		this.element.children[0].style.whiteSpace = "nowrap";
		// 		items.push({
		// 			text: this.element.children[0].innerHTML,
		// 			width: Math.floor(this.element.children[0].clientWidth) + this.space,
		// 			element: this.element.children[0]
		// 		});
		// 		this.element.removeChild(this.element.children[0]);
		// 	}
		// },
		// update: function() {
		// 	while (this.segments.length > 0 && Math.round(x + this.segments[0].x) + this.segments[0].width < 0) {
		// 		var segment = this.segments.shift();
		// 		this.element.removeChild(segment.element);
		// 	}
		// 	var len = 0;
		// 	for (var i = 0; i < this.segments.length; i++) {
		// 		var x = Math.round(x + this.segments[i].x);
		// 		this.segments[i].element.style.left = x.toFixed() + "px";
		// 		len = x + this.segments[i].width;
		// 	}
		// 	while (len < (this.width || this.element.clientWidth) && this.segments.length < 20 && items.length > 0) {
		// 		var index = this.segments.length ? (this.segments[this.segments.length-1].index + 1)%items.length : 0;
		// 		var item = items[index];
		// 		var segment = {
		// 			element: document.createElement("div"),
		// 			width: item.width,
		// 			x: len - x,
		// 			index: index
		// 		};
		// 		segment.element.innerHTML = item.text;
		// 		segment.element.className = "segment";
		// 		segment.element.style.position = "absolute";
		// 		segment.element.style.left = len.toFixed() + "px";
		// 		segment.element.style.width = segment.width.toFixed() + "px";
		// 		this.element.appendChild(segment.element);
		// 		this.segments.push(segment);
		// 		len += segment.width;
		// 	}
		// },
		play: function() {
			if (!animationId) {
				x += this.speed;
				this.update();
				animationId = window.requestAnimationFrame(function() {
					animationId = null;
					marquee.play();
				});
			}
		},
		pause: function() {
			if (animationId) {
				window.cancelAnimationFrame(animationId);
				animationId = null;
			}
		}
	};
	return marquee;
}

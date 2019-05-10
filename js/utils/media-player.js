
/**
 * Media Player
 *
 * @version mars2019
 */
function createMediaPlayer() {

	var directory = {};
	var renderMap = {};
	var timerID;
	var sleepTimerId;
	var player = {
		ids: [],
		offset: 0,
		currentId: null,
		loop: true,
		duration: 400,
		easing: "easeInOutSine",
		timerDuration: 5000,
		sleepTimerDuration: 0,
		addSlide: function(element, id, index) {
			index = index || this.ids.length;
			id = id || this.ids.length;
			directory[id] = element;
			this.ids.splice(index, 0, id);
		},
		removeSlide: function(id) {
			var index = this.ids.indexOf(id);
			if (index > -1) {
				delete directory[id];
				this.ids.splice(index, 1);
			}
		},
		cycle: function (value, length, offset) {
			while (length && value >= length + (offset || 0)) value -= length;
			while (length && value < (offset || 0)) value += length;
			return value;
		},
		renderSlide: function(id, position) {
			var slide = directory[id];
			if (slide) {
				var offset = this.cycle(position + this.offset, this.ids.length, -this.ids.length/2);
				if (offset > -1 && offset < 1) {
					if (!renderMap[id]) {
						if (this.onAppend) {
							this.onAppend(slide);
						}
						renderMap[id] = true;
					}
					if (this.onRenderSlide) {
						this.onRenderSlide(slide, offset, position);
					}
				} else {
					if (renderMap[id]) {
						if (this.onRemove) {
							this.onRemove(slide);
						}
						delete renderMap[id];
					}
				}
			}
		},
		render: function() {
			var index = this.ids.indexOf(this.currentId);
			if (index > -1) {
				for (var i = 0; i < this.ids.length; i++) {
					this.renderSlide(this.ids[i], i - index);
				}
			}
		},
		init: function() {
			if (this.onInit) {
				this.onInit();
			}
			if (!this.currentId) {
				this.currentId = this.ids[0];
			}
			this.offset = 0;
			this.render();
		},
		back: function() {
			this.animate(function(value) {
				player.offset = value;
				player.render();
			});
		},
		changeSlide: function(id, dir) {
			if (id !== this.currentId) {
				if (!dir) {
					var index = this.ids.indexOf(id);
					var currentIndex = this.ids.indexOf(this.currentId);
					dir = (Math.abs(index-currentIndex) < this.ids.length/2) === index-currentIndex < 0 ? -1 : 1;
				}
				var prevId = this.currentId;
				this.currentId = id;
				this.offset += dir;
				this.animate(function(value) {
					player.offset = value;
					player.renderSlide(prevId, -dir);
					player.renderSlide(player.currentId, 0);
				});
			}
		},
		change: function(dir) {
			if (!dir) {
				dir = this.offset > 0 ? -1 : 1;
			}
			this.currentId = this.getAdjacentId(dir);
			this.offset += dir;
			this.animate(function(value) {
				player.offset = value;
				player.render();
			});
		},
		animate: function(onFrame, onComplete) {
			var duration = Math.min(this.duration, this.duration*Math.abs(this.offset));
			this.animation = TinyAnimate.animate(this.offset, 0, duration, function(value) {
				if (onFrame) {
					onFrame(value);
				}
				if (player.onFrame) {
					player.onFrame(value);
				}
			}, this.easing, function() {
				if (onComplete) {
					onComplete();
				}
				if (player.onComplete) {
					player.onComplete();
				}
			});
		},
		next: function() {
			this.change(1);
		},
		prev: function() {
			this.change(-1);
		},
		getAdjacentId: function(dir, id) {
			if (!id && id !== 0) {
				id = this.currentId;
			}
			var index = this.ids.indexOf(id);
			if (index > -1) {
				index += dir;
				if (this.loop) {
					index = this.cycle(index, this.ids.length);
				}
				return this.ids[index];
			}
		},
		getAdjacentSlide: function(dir, id) {
			var id = this.getAdjacentId(dir, id);
			return directory[id];
		},
		getAdjacentIds: function(max, id) {
			if (!id && id !== 0) {
				id = this.currentId;
			}
			var len = max ? Math.min(max*2-1, this.ids.length) : this.ids.length;
			var flip = -1;
			var ids = [];
			var index = this.ids.indexOf(id);
			if (index > -1) {
				var i = 0;
				while (i < len) {
					index += flip*i;
					flip *= -1;
					if (this.loop) {
						ids.push(this.ids[this.cycle(index, this.ids.length)]);
						i++;
					} else if (index >= 0 && index < this.ids.length) {
						ids.push(this.ids[index]);
						i++;
					}
				}
			}
			return ids;
		},
		getAdjacentSlides: function(max, id) {
			return this.getAdjacentIds(max, id).map(function(id) {
				return directory[id];
			});
		},
		getSlide: function(id) {
			return directory[id];
		},
		getCurrentSlide: function() {
			return directory[this.currentId];
		},
		getDirectory: function() {
			return directory;
		},
		getSlides: function(index) {
			return this.ids.map(function(id) {
				return directory[id];
			});
		},
		sleep: function() {
			this.pause();
			if (this.sleepTimerDuration) {
				sleepTimerId = setTimeout(function() {
					player.play();
				}, this.sleepTimerDuration);
			}
		},
		play: function() {
			this.pause();
			timerID = setTimeout(function() {
				if (player.onPlay) {
					player.onPlay();
				}
				player.play();
			}, this.timerDuration);
		},
		pause: function() {
			if (timerID) {
				clearTimeout(timerID);
				timerID = null;
			}
			if (sleepTimerId) {
				clearTimeout(sleepTimerId);
				sleepTimerId = null;
			}
		}
	};
	return player;
}

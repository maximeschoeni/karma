
/**
 * Media Player
 *
 * @version mars2019
 */
function createMediaPlayer() {

	var timerID;
	var sleepTimerId;

	var player = createCollection();
	player.current = null;
	player.offset = 0;
	player.cyclic = true;
	player.duration = 400;
	player.easing = "easeInOutSine";
	player.timerDuration = 5000;
	player.sleepTimerDuration = 0;

	player.addSlide = function(item, id) {
		this.items.push({
			item: item,
			id: id || this.items.length
		});
	};
	player.addSlideAt = function(item, id, index) {
		this.items.splice(index, 0, {
			item: item,
			id: id
		});
	};
	player.renderSlide = function(slide, position) {
		if (slide) {
			var offset = this.loop(position + this.offset, -this.items.length/2);
			if (offset > -1 && offset < 1) {
				if (!slide.isRender) {
					if (this.onAppend) {
						this.onAppend(slide);
					}
					slide.isRender = true;
				}
				if (this.onRender) {
					this.onRender(slide, offset, position);
				}
			} else {
				if (slide.isRender) {
					if (this.onRemove) {
						this.onRemove(slide);
					}
					slide.isRender = false;
				}
			}
		}
	};
	player.render = function() {
		var index = this.items.indexOf(this.current);
		if (index > -1) {
			for (var i = 0; i < this.items.length; i++) {
				this.renderSlide(this.items[i], i - index);
			}
		}
	};
	player.clear = function() {
		if (player.onChange) {
			player.onChange();
		}
	};
	player.init = function() {
		if (!this.current && this.items.length) {
			this.current = this.items[0];
		}
		this.offset = 0;
		if (this.onInit) {
			this.onInit();
		}
		this.render();
		if (player.onComplete) {
			player.onComplete();
		}
	};
	player.back = function() {
		var duration = Math.min(this.duration, this.duration*Math.abs(this.offset));
		if (this.offset) {
			this.animation = TinyAnimate.animate(this.offset, 0, duration, function(value) {
				player.offset = value;
				player.render();
				if (player.onFrame) {
					player.onFrame(value);
				}
			}, this.easing);
		}

	};
	player.changeId = function(id, dir) {
		var slide = this.getItem("id", id);
		this.changeSlide(slide);
	};
	player.changeSlide = function(slide, dir) {
		if (slide !== this.current) {
			if (player.onChange) {
				player.onChange();
			}
			if (!dir) {
				var index = this.items.indexOf(slide);
				var currentIndex = this.items.indexOf(this.current);
				dir = (Math.abs(index-currentIndex) < this.ids.length/2) === index-currentIndex < 0 ? -1 : 1;
			}
			var prev = this.current;
			this.current = slide;
			this.offset += dir;
			var duration = Math.min(this.duration, this.duration*Math.abs(this.offset));
			this.animation = TinyAnimate.animate(this.offset, 0, duration, function(value) {
				player.offset = value;
				player.renderSlide(prev, -dir);
				player.renderSlide(player.current, 0);
				if (player.onFrame) {
					player.onFrame(value);
				}
			}, this.easing, function() {
				if (player.onComplete) {
					player.onComplete();
				}
			});
		}
	};
	player.change = function(dir) {
		if (!dir) {
			dir = this.offset > 0 ? -1 : 1;
		}
		var adjacent = this.getAdjacent(this.current, dir);
		if (adjacent) {
			if (player.onChange) {
				player.onChange();
			}
			this.current = adjacent;
			this.offset += dir;
			var duration = Math.min(this.duration, this.duration*Math.abs(this.offset));
			this.animation = TinyAnimate.animate(this.offset, 0, duration, function(value) {
				player.offset = value;
				player.render();
				if (player.onFrame) {
					player.onFrame(value);
				}
			}, this.easing, function() {
				if (player.onComplete) {
					player.onComplete();
				}
			});
		}
	};
	player.next = function() {
		this.change(1);
	};
	player.prev = function() {
		this.change(-1);
	};
	player.sleep = function() {
		this.pause();
		if (this.sleepTimerDuration) {
			sleepTimerId = setTimeout(function() {
				player.play();
			}, this.sleepTimerDuration);
		}
	};
	player.play = function() {
		this.pause();
		timerID = setTimeout(function() {
			if (player.onPlay) {
				player.onPlay();
			}
			player.play();
		}, this.timerDuration);
	};
	player.pause = function() {
		if (timerID) {
			clearTimeout(timerID);
			timerID = null;
		}
		if (sleepTimerId) {
			clearTimeout(sleepTimerId);
			sleepTimerId = null;
		}
	}
	return player;
}

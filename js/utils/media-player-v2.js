
/**
 * Media Player
 *
 * @version mars2019
 */
function createMediaPlayer() {

	var timerID;
	var sleepTimerId;

	var player = createCollection();
	player.offset = 0;
	player.cyclic = true;
	player.scalar = true;
	player.duration = 400;
	player.easing = "easeInOutSine";
	player.timerDuration = 5000;
	player.sleepTimerDuration = 0;
	player.x = 0;
	player.width = 0;

	player.addSlide = function(item) {
		// item.x = this.items.length;
		this.width += item.width || 1;
		this.items.push(item);
	};
	player.getWidth = function() {
		return this.width;
	};
	player.getCurrent = function() {
		return this.getSlideAt(this.x);
	};
	player.setCurrent = function(item) {
		var index = this.items.indexOf(item);
		if (index > -1) {
			this.x = this.items[index].x || index;
		}
	};
	player.getSlideAt = function(x) {
		// return this.items[Math.round(x)];
		for (var i = 0; i < this.items.length; i++) {
			if ((this.items[i].x || i) + (this.items[i].width || 1) > x) {
				return this.items[i];
			}
		}
	};
	player.renderSlide = function(slide, index) {
		var x = index-this.x;
		if (this.cyclic) {
			x = this.loop(x, -this.getWidth()/2)
		}
		if (x > -1 && x < 1) {
			if (!slide.isRender) {
				if (this.onAppend) {
					this.onAppend.call(this, slide);
				}
				slide.isRender = true;
			}
			if (this.onRenderSlide) {
				this.onRenderSlide.call(this, slide, x);
			}
		} else {
			if (slide.isRender) {
				if (this.onRemove) {
					this.onRemove.call(this, slide);
				}
				slide.isRender = false;
			}
		}
	};
	player.render = function() {
		for (var i = 0; i < this.items.length; i++) {
			this.renderSlide(this.items[i], i);
		}
		if (this.onRender) {
			var x = this.loop(this.x, -this.getWidth()/2); // wtf
			this.onRender.call(this, x, this.x);
		}
	};
	player.clear = function() {
		if (player.onChange) {
			player.onChange();
		}
	};
	player.init = function() {
		this.getItems("isRender", true).items.forEach(function(slide) {
			if (player.onRemove) {
				player.onRemove.call(this, slide);
			}
			slide.isRender = false;
		})
		if (this.onInit) {
			this.onInit();
		}
		this.render();
		if (player.onComplete) {
			player.onComplete.call(this);
		}
	};
	player.shift = function(offset) {
		if (this.cyclic || x+offset >= 0 && x+offset < this.getWidth()) {
			TinyAnimate.cancel(this.animation);
			this.x += offset-this.offset;
			this.offset = offset;
			player.render();
			if (player.onShift) {
				player.onShift.call(this);
			}
		}
	};
	player.back = function(callback) {
		if (this.offset) {
			var duration = Math.min(this.duration, this.duration*Math.abs(this.offset));
			var x = this.x-this.offset;
			this.animation = TinyAnimate.animate(this.x, x, duration, function(value) {
				player.offset = x - this.x;
				player.x = value;
				player.render();
			}, this.easing, function() {
				player.offset = 0;
				if (player.onBack) {
					player.onBack.call(this);
				}
				if (callback) {
					callback.call(this);
				}
			});
		}
	};
	// player.changeId = function(id, dir) {
	// 	var slide = this.getItem("id", id);
	// 	this.changeSlide(slide);
	// };
	// player.changeSlide = function(slide, dir) {
	// 	if (slide !== this.getCurrent()) {
	// 		if (player.onChange) {
	// 			player.onChange();
	// 		}
	// 		if (!dir) {
	// 			var index = this.items.indexOf(slide);
	// 			var currentIndex = this.items.indexOf(this.current);
	// 			dir = (Math.abs(index-currentIndex) < this.ids.length/2) === index-currentIndex < 0 ? -1 : 1;
	// 		}
	// 		var prev = this.current;
	// 		this.current = slide;
	// 		this.offset += dir;
	// 		var duration = Math.min(this.duration, this.duration*Math.abs(this.offset));
	// 		this.animation = TinyAnimate.animate(this.offset, 0, duration, function(value) {
	// 			player.offset = value;
	// 			player.renderSlide(prev, -dir);
	// 			player.renderSlide(player.current, 0);
	// 			if (player.onFrame) {
	// 				player.onFrame(value);
	// 			}
	// 		}, this.easing, function() {
	// 			if (player.onComplete) {
	// 				player.onComplete();
	// 			}
	// 		});
	// 	}
	// };
	player.change = function(x, callback) {
		if (this.cyclic || x >= 0 && x < this.getWidth()) {
			if (player.onChange) {
				player.onChange.call(this);
			}
			this.offset = 0;
			var duration = this.duration; //Math.min(this.duration, this.duration*Math.abs(this.offset));
			TinyAnimate.cancel(this.animation);
			this.animation = TinyAnimate.animate(this.x, x, duration, function(value) {
				player.x = value;
				player.render();
			}, this.easing, function() {
				if (player.onComplete) {
					player.onComplete.call(this);
				}
				if (callback) {
					callback.call(this);
				}
			});
		} else if (callback) {
			callback.call(this);
		}
	};
	player.next = function(callback) {
		var x = this.x + 1;
		if (this.scalar) {
			x = Math.ceil(x);
		}
		if (!this.cyclic) {
			x = Math.min(x, this.getWidth() -1);
		}
		this.change(x, callback);
	};
	player.prev = function(callback) {
		var x = this.x - 1;
		if (this.scalar) {
			x = Math.floor(x);
		}
		if (!this.cyclic) {
			x = Math.max(x, 0);
		}
		this.change(x, callback);
		// this.change(Math.floor(this.x-1), callback);
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

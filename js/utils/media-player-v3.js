
/**
 * Media Player
 *
 * @since mars2019 v2
 * @since dec2019 v3
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
		if (player.cyclic) {
			x = this.loop(x, -this.getWidth()/2)
		}
		return this.items[Math.round(x)];
		// for (var i = 0; i < this.items.length; i++) {
		// 	if ((this.items[i].x || i) + (this.items[i].width || 1) > x) {
		// 		return this.items[i];
		// 	}
		// }
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
		if (this.cyclic || this.x+offset >= 0 && this.x+offset < this.getWidth()) {
			if (player.onCancel) {
				player.onCancel();
			}
			var x = player.x;
			player.onCancel = function() {
				player.x = player.x - offset;
				// render();
			};
			player.onBack = function() {
				player.onCancel = null;
				player.onBack = null;
				player.onRelease = null;
				player.change(x);
			};
			player.onRelease = function() {
				player.onCancel = null;
				player.onBack = null;
				player.onRelease = null;
				var dir = offset > 0 ? 1 : -1;
				player.change(x+dir);
			};
			this.x = x + offset;
			player.render();
		}
	};
	player.change = function(x, callback) {
		if (this.cyclic || x >= 0 && x < this.getWidth()) {
			if (player.onChange) {
				player.onChange.call(this);
			}
			var duration = this.duration; //Math.min(this.duration, this.duration*Math.abs(this.offset));
			var animation;
			if (player.onCancel) {
				player.onCancel();
			}
			player.onCancel = function() {
				animation && TinyAnimate.cancel(animation);
				player.x = x;
				player.render();
			};
			animation = TinyAnimate.animate(this.x, x, duration, function(value) {
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

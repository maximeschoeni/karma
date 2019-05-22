/**
 * @version dec2018
 *
 */
function createPopupManager() {
	var current;
	var manager = {
		current: null,
		duration: 300,
		easing: "easeInOutSine",
		sequential: false,
		update: function(element, noAnim, toggle) {
			if (this.sequential) {
				if (this.current) {
					close(manager.current, noAnim, function() {
						if (element) {
							open(element, noAnim);
						}
						manager.current = element;
					});
				} else if (element) {
					open(element, noAnim);
					manager.current = element;
				}
			} else {
				if (toggle && this.current && element === this.current) {
					//close(current, noAnim);
					element = null;
					//current = null;
				}
				if (this.current && element !== this.current) {
					close(this.current, noAnim);
					this.current = null;
				}
				if (element && !this.current) {
					open(element, noAnim);
					this.current = element;
				}
			}
		},
		toggle: function(element, noAnim) {
			this.update(element, noAnim, true);
		}
	};
	function open(element, noAnim, callback) {
		if (manager.onBeforeOpen) {
			manager.onBeforeOpen(element);
		}
		if (noAnim || !manager.duration) {
			if (manager.onRender) {
				manager.onRender(element, 1, true);
			}
			if (manager.onAfterOpen) {
				manager.onAfterOpen(element);
			}
			if (callback) {
				callback.call(manager);
			}
		} else {
			if (manager.onRender) {
				manager.onRender(element, 0, true);
			}
			TinyAnimate.animate(0, 1, manager.duration, function(value) {
				if (manager.onRender) {
					manager.onRender(element, value, true);
				}
			}, manager.easing, function() {
				if (manager.onAfterOpen) {
					manager.onAfterOpen(element);
				}
				if (callback) {
					callback.call(manager);
				}
			});
		}
	}
	function close(element, noAnim, callback) {
		if (manager.onBeforeClose) {
			manager.onBeforeClose(element);
		}
		if (noAnim || !manager.duration) {
			if (manager.onRender) {
				manager.onRender(element, 0, false);
			}
			if (manager.onAfterClose) {
				manager.onAfterClose(element);
			}
			if (callback) {
				callback.call(manager);
			}
		} else {
			if (manager.onRender) {
				manager.onRender(element, 1, false);
			}
			TinyAnimate.animate(1, 0, manager.duration, function(value) {
				if (manager.onRender) {
					manager.onRender(element, value, false);
				}
			}, manager.easing, function() {
				if (manager.onAfterClose) {
					manager.onAfterClose(element);
				}
				if (callback) {
					callback.call(manager);
				}
			});
		}
	}
	return manager;
}

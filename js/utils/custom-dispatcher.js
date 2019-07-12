function CustomDispatcher() {
	this.events = {};
}
CustomDispatcher.prototype.on = function(eventName, callback) {
	if (!this.events[eventName]) {
		this.events[eventName] = [];
	}
  this.events[eventName].push(callback);
};
CustomDispatcher.prototype.off = function(eventName, callback) {
	if (eventName && callback) {
		if (this.events[eventName]) {
			var index = this.events[eventName].indexOf(callback);
			if (index > -1) {
				this.events[eventName].splice(index, 1);
			}
		}
	} else if (eventName) {
		this.events[eventName] = [];
	} else {
		this.events = {};
	}
};
CustomDispatcher.prototype.trigger = function(eventName, args) {
	if (this.events[eventName]) {
		for (var i = 0; i < this.events[eventName].length; i++) {
			this.events[eventName][i].apply(this, typeof args === "object" ? args : [args]);
		}
	}
};

if (!window.KarmaTaskManager) {
	var KarmaTaskManager = {};
}
KarmaTaskManager.interval = 10000;
KarmaTaskManager.getTask = function(callback) {
	ajaxGet(KarmaTaskManager.ajax_url, {
		action: "karma_get_task"
	}, function(results) {
		console.log(results);
		callback(results);
	});
};
KarmaTaskManager.resolveTask = function(subTask, callback) {
	var num = subTask.items.length;
	KarmaTaskManager.onStart && KarmaTaskManager.onStart(subTask.name, num);
	function loop() {
		if (subTask.items.length) {
			KarmaTaskManager.onUpdate && KarmaTaskManager.onUpdate(subTask.name, num, subTask.items.length);
			var data = subTask.items.shift();
			data.action = subTask.task;
			ajaxPost(KarmaTaskManager.ajax_url, data, function(results) {
				console.log(results);
				loop();
			});
		} else {
			KarmaTaskManager.onComplete && KarmaTaskManager.onComplete(subTask.name, num);
			callback();
		}
	}
	loop();
};
KarmaTaskManager.update = function() {
	KarmaTaskManager.getTask(function(results) {
		if (results.name && results.task && results.items && results.items.length) {
			KarmaTaskManager.resolveTask(results, function() {
				KarmaTaskManager.update();
			});
		} else {
			setTimeout(function() {
				KarmaTaskManager.update();
			}, KarmaTaskManager.interval);
		}
	});
}

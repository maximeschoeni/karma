if (!window.KarmaTaskManager) {
	var KarmaTaskManager = {};
}
KarmaTaskManager.dispatcher = new CustomDispatcher();
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
	var index = 0;
	KarmaTaskManager.onStart && KarmaTaskManager.onStart(subTask.name, subTask.items.length);
	function loop() {
		if (index < subTask.items.length) {
			KarmaTaskManager.onUpdate && KarmaTaskManager.onUpdate(subTask.name, subTask.items.length, index);
			var data = subTask.items[index];
			console.log(data);
			data.action = subTask.task;
			// ajaxPost(KarmaTaskManager.ajax_url, data, function(results) {
			// 	console.log(results);
			// 	KarmaTaskManager.dispatcher.trigger(subTask.task+"-done", [results]);
			// 	loop();
			// });

			Ajax.send(KarmaTaskManager.ajax_url, Ajax.createQuery(data), 'POST', function(results) {

				try {
   				var json = JSON.parse(results);
					console.log(json);
				} catch(e) {
					console.log(results);
				}

				loop();
			});
			index++;
		} else {
			KarmaTaskManager.onComplete && KarmaTaskManager.onComplete(subTask.name, subTask.items.length);
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

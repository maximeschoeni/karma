if (!window.KarmaTaskManager) {
	var KarmaTaskManager = {};
}

KarmaTaskManager.interval = 10000;
KarmaTaskManager.taskId = 1;
KarmaTaskManager.num = 0;

KarmaTaskManager.printNoticeTask = function(msg) {
	var element = document.getElementById("task-manager-notice-task");
	if (element) {
		element.innerHTML = msg;
	}
}
KarmaTaskManager.printNoticeStatus = function(msg) {
	var element = document.getElementById("task-manager-notice-status");
	if (element) {
		element.innerHTML = msg;
	}
}


KarmaTaskManager.update = function(notice) {
	if (KarmaTaskManager.is_admin && notice) {
		KarmaTaskManager.printNoticeTask(notice);
	}

	KarmaTaskManager.taskId++;
	var taskId = KarmaTaskManager.taskId;

	Ajax.send(KarmaTaskManager.ajax_url, "action=karma_task", "POST", function(results) {

		if (results === "[]") {
			KarmaTaskManager.num = 0;
			KarmaTaskManager.onComplete && KarmaTaskManager.onComplete();
			setTimeout(function() {
				if (taskId === KarmaTaskManager.taskId) {
					KarmaTaskManager.update();
				}
			}, KarmaTaskManager.interval);
			if (KarmaTaskManager.is_admin) {
				KarmaTaskManager.printNoticeStatus("Done. ");
			}
		} else {
			KarmaTaskManager.num++;
			KarmaTaskManager.onUpdate && KarmaTaskManager.onUpdate();
			if (taskId === KarmaTaskManager.taskId) {
				KarmaTaskManager.update();
			}
			if (KarmaTaskManager.is_admin) {
				try {
					var json = JSON.parse(results);
					KarmaTaskManager.printNoticeStatus("("+KarmaTaskManager.num+") ");
					console.log(json);
				} catch(e) {
					console.log(results);
					KarmaTaskManager.printNoticeStatus("("+KarmaTaskManager.num+") ");
				}
			}
		}
	});
};

KarmaTaskManager.addTask = function(task, link, callback) {
	var title = link.innerHTML;
	if (link) {
		link.innerHTML = "...";
	}
	ajaxPost(KarmaTaskManager.ajax_url, {
		action: task
	}, function(results) {
		KarmaTaskManager.update(results.notice);
		if (link) {
			link.innerHTML = title;
		}
		if (callback) {
			callback.call(link, results);
		}
	});
};

KarmaTaskManager.update();

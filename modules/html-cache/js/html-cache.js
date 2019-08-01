if (KarmaTaskManager && KarmaTaskManager.dispatcher) {
	KarmaTaskManager.dispatcher.on("karma_cache_regenerate_url-done", function(results) {
		console.log(results);
		Ajax.send(results.full_url);
	});
}

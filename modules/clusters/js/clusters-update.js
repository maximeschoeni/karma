if (!window.Clusters) {
	var Clusters = {};
}


console.log(window.ajaxGet);

Clusters.getExpiredClusters = function(callback) {
	ajaxGet(Clusters.ajax_url, {
		action: 'clusters_get_expired_clusters'
	}, function(ids) {
		console.log(ids);
		callback(ids);
	});
};
Clusters.updateExpiredClusters = function(callback) {
	Clusters.getExpiredClusters(function(ids) {
		var total = ids.length;
		Clusters.onStart && Clusters.onStart(total);
		function loop() {
			if (ids.length) {
					Clusters.onUpdate && Clusters.onUpdate(total, ids.length);
				ajaxPost(Clusters.ajax_url, {
					action: 'clusters_update_dependency',
					id: ids.shift()
				}, function(results) {
					console.log(results);
					loop();
				});
			} else {
				Clusters.onComplete && Clusters.onComplete(total);
				callback();
			}
		}
		loop();
	});
};
Clusters.update = function(callback) {
	Clusters.updateExpiredClusters(function() {
		Clusters.getExpiredClusters(function(ids) {
			if (ids.length) {
				Clusters.update(callback)
			} else {
				callback && callback();
			}
		});
	});
}

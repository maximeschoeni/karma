if (!window.Ajax) {
	Ajax = {};
}
Ajax.send = function(url, data, method, callback) {
	var xhr = new XMLHttpRequest();
	xhr.open(method, url);
	xhr.onreadystatechange = function () {
		if (xhr.readyState === XMLHttpRequest.DONE) {
			if (xhr.status === 200) {
				if (callback) {
					callback(xhr.responseText);
				}
			} else {
				console.log("Error: " + xhr.status); // An error occurred during the request.
			}
		}
	};
	if (method === "POST") {
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	}
	xhr.send(data);
}
Ajax.createQuery = function(data) {
	var query = [];
	for (var key in data) {
			query.push(encodeURIComponent(key) + "=" + encodeURIComponent(data[key]));
	}
	return query.join("&");
}
Ajax.addQueryArgs = function(url, data) {
	url += url.indexOf("?") > -1 ? "&" : "?";
	url += this.createQuery(data);
	return url;
}
Ajax.get = function(url, data, callback) {
	if (data) {
		url = this.addQueryArgs(url, data);
	}
	this.send(url, null, "GET", function(result) {
		callback && callback(JSON.parse(result));
	});
}
Ajax.post = function(url, data, callback) {
	this.send(url, data && Ajax.createQuery(data), "POST", function(result) {
		callback && callback(JSON.parse(result));
	});
}
function ajaxGet(url, data, callback) {
	Ajax.get(url, data, callback);
}
function ajaxPost(url, data, callback, parser) {
	Ajax.post(url, data, callback);
}

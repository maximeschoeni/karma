console.log("ajax1");
if (!window.Ajax) {
	Ajax = {};
}
Ajax.send = function(url, data, method, callback, parser) {
	var xhr = new XMLHttpRequest();
	xhr.open(method || "GET", url);
	xhr.onreadystatechange = function () {
		if (xhr.readyState === XMLHttpRequest.DONE) {
			if (xhr.status === 200) {
				if (callback) {
					if (parser) {
						callback(parser(xhr.responseText));
					} else {
						callback(xhr.responseText);
					}
				}
			} else {
				console.log("Error: " + xhr.status); // An error occurred during the request.
			}
		}
	};
	if (method === "POST") {
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		//xhr.setRequestHeader("Content-type", "text/html");
	}
	xhr.send(data);
}
Ajax.createQuery = function(data) {
	var query = [];
	for (var key in data) {
			query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
	}
	return query.join('&');
}
function ajaxCall(url, data, method, callback, parser) {
	Ajax.send(url, data, method, callback, parser);
// 	console.log(url);
	// var xhr = new XMLHttpRequest();
	// xhr.open(method, url);
	// xhr.onreadystatechange = function () {
	// 	if (xhr.readyState === XMLHttpRequest.DONE) {
	// 		if (xhr.status === 200) {
	// 			if (parser) {
	// 				callback(parser(xhr.responseText));
	// 			} else {
	// 				callback(xhr.responseText);
	// 			}
	// 		} else {
	// 			console.log("Error: " + xhr.status); // An error occurred during the request.
	// 		}
	// 	}
	// };
	// if (method === "POST") {
	// 	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	// 	//xhr.setRequestHeader("Content-type", "text/html");
	// }
	// xhr.send(data);
}
function ajaxGet(url, data, callback) {
	// if (data) {
	// 	var query = [];
	// 	for (var key in data) {
	// 		query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
	// 	}
	// 	if (query.length) {
	// 		url += '?' + query.join('&');
	// 	}
	// }
	if (data) {
		url += '?' + Ajax.createQuery(data);
	}
	ajaxCall(url, null, 'GET', callback, JSON.parse);
}
function ajaxPost(url, data, callback, parser) {
	// var query = [];
	// for (var key in data) {
	// 		query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
	// }
	// ajaxCall(url, query.join('&'), 'POST', callback, JSON.parse);

	ajaxCall(url, data && Ajax.createQuery(data), 'POST', callback, parser || JSON.parse);
}

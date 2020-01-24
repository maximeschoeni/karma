if (!window.KarmaClusters) {
	var KarmaClusters = {};
}
KarmaClusters.getClusterLink = function(id) {
	return this.url+"/"+id+this.sufix+"/data.json?v="+(new Date).getTime();
}

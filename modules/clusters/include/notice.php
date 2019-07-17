<div class="notice notice-info" id="cluster-update-notice">
		<p>Updating Cache<span id="cluster-update-percent"></span>...</p>
</div>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		var notice = document.getElementById("cluster-update-notice");
		var percent = document.getElementById("cluster-update-percent");
		if (window.Clusters) {
			percent.innerHTML = "(0%)";
			Clusters.onUpdate = function(total, index) {
				percent.innerHTML = "("+(100*index/total).toFixed()+"%)";
			}
			Clusters.onComplete = function(total) {
				percent.innerHTML = "(100%)";
			}
		}
	});
</script>

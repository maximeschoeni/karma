<div class="notice notice-info is-dismissible" id="task-manager-notice" style="display:none">
		<p>Updating <span id="task-manager-amount"></span></p>
</div>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		var notice = document.getElementById("task-manager-notice");
		var amount = document.getElementById("task-manager-amount");
		if (window.KarmaTaskManager) {
			amount.innerHTML = "...";
			KarmaTaskManager.onStart = function(taskName, total) {
				notice.style.display = "block";
			}
			KarmaTaskManager.onUpdate = function(taskName, total, index) {
				amount.innerHTML = taskName + " ("+(100*index/total).toFixed()+"%)";
			}
			KarmaTaskManager.onComplete = function(taskName, total) {
				amount.innerHTML = taskName + ". Done";
			}
			KarmaTaskManager.update();
		}
	});
</script>

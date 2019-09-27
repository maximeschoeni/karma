<?php $tasks = apply_filters('karma_task_notice', array()); ?>

<?php if (current_user_can('manage_options')) { ?>
	<div class="notice notice-info is-dismissible" id="task-manager-notice">
		<p>
			<span id="task-manager-notice-task"><?php echo $tasks ? implode(' / ', $tasks) : 'No task. '; ?></span>
			<span id="task-manager-notice-status"></span>
		</p>
	</div>
<?php } ?>
<script>
	// document.addEventListener("DOMContentLoaded", function() {
	// 	var notice = document.getElementById("task-manager-notice");
	// 	// var amount = document.getElementById("task-manager-amount");
	// 	if (window.KarmaTaskManager) {
	// 		if (notice && KarmaTaskManager.is_admin) {
	// 			KarmaTaskManager.onUpdate = function() {
	// 				notice.style.display = "block";
	// 			}
	// 			KarmaTaskManager.onComplete = function() {
	// 				notice.innerHTML = "Updating...";
	// 				notice.style.display = "none";
	// 			}
	// 		}
	// 		KarmaTaskManager.update();
	// 	}
	// });
</script>

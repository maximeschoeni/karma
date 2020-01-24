<?php $tasks = apply_filters('karma_task_notice', array()); ?>

<?php if (current_user_can('manage_options')) { ?>
	<div class="notice notice-info is-dismissible" id="task-manager-notice">
		<p>
			<span id="task-manager-notice-task"><?php echo $tasks ? implode(' / ', $tasks) : 'No task. '; ?></span>
			<span id="task-manager-notice-status"></span>
		</p>
	</div>
<?php } ?>

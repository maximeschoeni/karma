<div id="project-events-container"></div>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		var container = document.getElementById("project-events-container");
		var manager = createChildrenManager(container);
		manager.items = <?php echo json_encode($events); ?>;
		manager.admin_url = "<?php echo admin_url(); ?>";
		manager.update();
	});
</script>

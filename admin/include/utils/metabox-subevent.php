<div id="project-events-container"></div>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		var container = document.getElementById("project-events-container");
		var manager = createChildrenManager(container);
		manager.items = <?php echo json_encode($subevents); ?>;
		manager.fields = <?php echo json_encode($this->fields); ?>;
		manager.types = <?php echo json_encode($types); ?>;
		manager.admin_url = "<?php echo admin_url(); ?>";
		manager.update();
	});
</script>

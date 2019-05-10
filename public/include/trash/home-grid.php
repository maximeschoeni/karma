<div class="grid-container">
	<div id="main-grid" class="grid"></div>
</div>
<div id="main-grid-library" class="library">
	<?php foreach ($query->posts as $post) {
		include get_template_directory() . '/public/include/home-grid-cell.php';
	} ?>
</div>
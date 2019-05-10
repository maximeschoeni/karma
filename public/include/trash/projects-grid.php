<nav class="categories-nav">
	<?php if ($categories && !is_wp_error($categories)) { ?>
		<ul id="projects-categories">
			<?php foreach ($categories as $category) { ?>
				<li data-category="<?php echo $category->slug; ?>"><?php echo $category->name; ?></li>
			<?php } ?>
			<li class="all">all</li
		</ul>
	<?php } ?>
</nav>
<div class="grid-container">
	<div id="projects-grid" class="grid"></div>
</div>
<div id="projects-grid-library" class="grid-library library">
	<?php foreach ($query->posts as $post) { ?>		
		<?php include get_template_directory() . '/public/include/home-grid-cell.php'; ?>		
	<?php } ?>
</div>
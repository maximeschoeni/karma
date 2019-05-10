<div class="project-text">
	<h1><?php echo get_the_title($post); ?></h1>
	<?php echo $post_contents[$page_index]; ?>
	<?php if ($page_index === 0) include get_template_directory() . '/public/include/project-categories.php'; ?>
</div>
<?php 
	$page_index++;
	
	if ($page_index >= count($post_contents)) {
		$page_index = 0;
	}
?>
<div id="main-slideshow" class="slideshow">
	<div id="main-slideshow-controller" class="controller">
		<div class="left-zone">
			<img src="<?php echo get_template_directory_uri() ?>/images/left-arrow.svg"/>
		</div>
		<div class="right-zone">
			<img src="<?php echo get_template_directory_uri() ?>/images/right-arrow.svg"/>
		</div>
	</div>
</div>
<div id="main-slideshow-library" class="library">
	<?php foreach ($query->posts as $project) { ?>
		<?php $thumbnail_id = get_post_thumbnail_id($project->ID); ?>
		<div class="slide">
			<?php echo apply_filters(
				'background-image', 
				'<div style="width:100%;height:100%;background-image:url('.wp_get_attachment_url($thumbnail_id).');background-size:cover;background-position:center"></div>', 
				$thumbnail_id,
				'cover',
				'center',
				array('class' => 'image')); ?>
				<h1><?php echo get_post_meta($project->ID, 'slideshow', true); ?></h1>
		</div>
	<?php } ?>
</div>
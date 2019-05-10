<div class="project-header">
	<?php echo apply_filters(
		'background-image', 
		'<div class="slide" style="background-image:url('.wp_get_attachment_url($thumb_id).');background-size:cover;background-position:center"></div>', 
		$thumb_id,
		'cover',
		'center',
		array('class' => 'image')
	); ?>
</div>
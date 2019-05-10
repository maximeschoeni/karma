<?php 
	$background = '<div class="cell-background" style="background-image:url('.wp_get_attachment_url($media->id).');background-size:cover;background-position:center"></div>';
?>
<div class="cell-content image-anim image-offset">
	<?php echo apply_filters('background-image', $background, $media->id, 'cover', 'center', array('class' => 'image')); ?>
</div>

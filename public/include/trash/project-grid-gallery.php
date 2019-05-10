<div class="slideshow">
	<div class="controller">
		<div class="left-zone"><img src="<?php echo get_template_directory_uri() ?>/images/left-arrow.svg"/></div>
		<div class="right-zone"><img src="<?php echo get_template_directory_uri() ?>/images/right-arrow.svg"/></div>
	</div>
	<div class="library">
		<?php foreach ($media->ids as $id) { ?>
			<div class="slide">
				<?php echo apply_filters(
					'background-image', 
					'<div style="width:100%;height:100%;background-image:url('.wp_get_attachment_url($id).');background-size:cover;background-position:center"></div>', 
					$id,
					'cover',
					'center',
					array('class' => 'image')); ?>
			</div>
		<?php } ?>
	</div>
</div>

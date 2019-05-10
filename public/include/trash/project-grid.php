<div class="grid-container">
	<div id="project-grid" class="grid">
	</div>
	<div id="project-grid-library" class="library">
		<?php foreach ($medias as $media) { ?>		
			<?php 
				if (empty($media->format) || $media->format === '1x1' || $media->format === '1x2') {
					$col = 1;
				} else {
					$col = 2;
				}
				if (empty($media->format) || $media->format === '1x1' || $media->format === '2x1' || $media->format === '2x1c') {
					$row = 1;
				} else {
					$row = 2;
				}
				if (isset($media->format) && ($media->format === '2x1c' || $media->format === '2x2c')) {
					$centered = true;
				} else {
					$centered = false;
				}
			?>
			<div class="cell type-<?php echo $media->type; ?>" data-col="<?php echo $col; ?>" data-row="<?php echo $row; ?>">
				<div class="cell-frame<?php if ($centered) echo ' centered'; ?>">
					<?php
						if (isset($media->type) && $media->type === 'image') {
							include get_template_directory() . '/public/include/project-grid-cell.php'; 
						} else if (isset($media->type) && $media->type === 'gallery') {
							include get_template_directory() . '/public/include/project-grid-gallery.php'; 
						} else if (isset($media->type) && $media->type === 'text') {
							include get_template_directory() . '/public/include/project-grid-text.php';
						} 
					?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
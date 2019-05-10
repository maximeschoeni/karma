<div class="more-project">
	<h4>More</h4>
	<div class="more-project-grid-container">
		<div id="more-project-grid" class="grid">
		</div>
		<div id="more-project-grid-library" class="library">
			<?php foreach ($more_posts as $more_post) {
					$thumb_id = get_post_thumbnail_id($more_post->ID);
					if (!$thumb_id) continue;
					$negative_thumb = get_post_meta($more_post->ID, 'negative_thumb', true);
				?>
				<div class="cell more-cell" data-col="1" data-row="1">
					<div class="cell-content image-anim image-offset">
						<?php $background = '<div class="cell-background" style="background-image:url('.wp_get_attachment_url($thumb_id).');background-size:cover;background-position:center"></div>'; ?>
						<?php echo apply_filters('background-image', $background, $thumb_id, 'cover', 'center', array('class' => 'image')); ?>
						<a href="<?php echo get_permalink($more_post); ?>">
							<div class="cell-overlay<?php if ($negative_thumb) echo ' negative'; ?>">
								<h2><?php echo get_the_title($more_post); ?></h2>
								<h3><?php echo nl2br(get_the_excerpt($more_post)); ?></h3>
								<?php //include get_template_directory() . '/public/include/project-category-names.php'; ?>
								<div class="cell-category"><?php echo implode(', ', $this->map_terms($more_post->ID, 'name')); ?></div>
								<svg class="cross" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
									 width="60px" height="60px" viewBox="0 0 60 60" enable-background="new 0 0 60 60" xml:space="preserve">
									<g>
										<line stroke="#000000" stroke-width="1" stroke-miterlimit="10" x1="30" y1="0" x2="30" y2="60"/>
										<line stroke="#000000" stroke-width="1" stroke-miterlimit="10" x1="60" y1="30" x2="0" y2="30"/>
									</g>
								</svg>
							</div>
						</a>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
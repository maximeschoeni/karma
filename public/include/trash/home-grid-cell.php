<?php 
// 	$terms = get_the_terms($post->ID, 'category');
// 	
// 	$category_slugs = $terms && !is_wp_error($terms) ? array_map(array($this, 'get_term_slug'), $terms) : array();
	
// 	$category_names = array();
// 	$category_slugs = array();
// 	if ($terms && !is_wp_error($terms)) {
// 		foreach ($terms as $term) {
// 			$category_names[] = $term->name;
// 			$category_slugs[] = $term->slug;
// 		}
// 	}
	
	$thumb_id = get_post_thumbnail_id($post->ID);
	
	$background = '<div class="cell-background" style="background-image:url('.wp_get_attachment_url($thumb_id).');background-size:cover;background-position:center"></div>';
	
	$col = get_post_meta($post->ID, 'col', true);
	$row = get_post_meta($post->ID, 'row', true);
	
	$negative_thumb = get_post_meta($post->ID, 'negative_thumb', true);
?>
<div class="cell project-cell<?php if (!$thumb_id) echo ' placeholder'; ?>" data-col="<?php echo $col; ?>" data-row="<?php echo $row; ?>" data-category="<?php echo implode(' ', $this->map_terms($post->ID, 'slug')); ?>">
	<?php if ($thumb_id) { ?>
		<div class="cell-content image-anim image-offset">
			<?php echo apply_filters('background-image', $background, $thumb_id, 'cover', 'center', array('class' => 'image')); ?>
			<a href="<?php echo get_permalink($post); ?>">
				<div class="cell-overlay<?php if ($negative_thumb) echo ' negative'; ?>">
					<h2><?php echo get_the_title($post); ?></h2>
					<h3><?php echo nl2br(get_the_excerpt($post)); ?></h3>
					<?php //include get_template_directory() . '/public/include/project-categories.php'; ?>
					<div class="cell-category"><?php echo implode(', ', $this->map_terms($post->ID, 'name')); ?></div>
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
	<?php } ?>
</div>
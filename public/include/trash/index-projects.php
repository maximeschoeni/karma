<div class="index-projects">
	<div class="index-projects-content">
		<table id="index-projects-table">
			<thead>
				<tr>
					<th data-order="desc"><?php echo __('Year', 'karma'); ?></th>
					<th data-order="asc"><?php echo __('Work', 'karma'); ?></th>
					<th data-order="asc"><?php echo __('Client', 'karma'); ?></th>
					<th data-order="asc"><?php echo __('Category', 'karma'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($query->posts as $post) { 
					
					$terms = get_the_terms($post->ID, 'category');
					$thumb_id = get_post_thumbnail_id($post->ID);
					$thumb_src = wp_get_attachment_image_src($thumb_id, 'medium');
					?>
					<tr data-thumb="<?php echo $thumb_src[0]; ?>" data-link="<?php echo get_permalink($post); ?>">
						<td><?php echo get_post_meta($post->ID, 'year', true); ?></td>
						<td><?php echo get_the_title($post); ?></td>
						<td><?php echo get_post_meta($post->ID, 'client', true); ?></td>
						<td>
							<ul>
								<?php if ($terms && !is_wp_error($terms)) { foreach ($terms as $term) { ?>
									<li><!-- <a href="<?php echo get_post_type_archive_link('project'); ?>#<?php echo $term->slug; ?>"> --><?php echo $term->name; ?><!-- </a> --></li>
								<?php }} ?>
							</ul>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
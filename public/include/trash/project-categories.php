<ul class="project-categories">
	<?php if ($terms && !is_wp_error($terms)) { foreach ($terms as $term) { ?>
		<li><a href="<?php echo get_post_type_archive_link('project'); ?>#<?php echo $term->slug; ?>" data-slug="<?php echo $term->slug; ?>"><?php echo $term->name; ?></a></li>
	<?php }} ?>
</ul>
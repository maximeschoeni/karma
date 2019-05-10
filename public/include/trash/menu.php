<?php

	$index_page = $this->get_pages()->get_item('post_name', 'index');
	$about_page = $this->get_pages()->get_item('post_name', 'about');
	
	if ($index_page && $about_page) {
	
?>
<nav class="menu" id="nav-menu">
	<div class="menu-content">
		<div class="menu-table">
			<div class="menu-row">
				<a class="menu-cell<?php if (is_home()) echo ' active'; ?>" href="<?php echo home_url(); ?>">Marks</a>
				<a class="menu-cell<?php if (is_post_type_archive('project') || is_singular('project')) echo ' active'; ?>" href="<?php echo get_post_type_archive_link('project'); ?>"><?php echo __('Work', 'karma'); ?></a>
				<?php foreach (array($index_page, $about_page) as $page) { ?>
					<a class="menu-cell<?php if (is_page($page->ID)) echo ' active'; ?>" href="<?php echo get_permalink($page); ?>"><?php echo get_the_title($page); ?></a>
				<?php } ?>
				<?php do_action('sublanguage_print_language_switch'); ?>
			</div>
		</div>
	</div>
</nav>
<div class="menu placeholder"><br></div>
<?php } ?>
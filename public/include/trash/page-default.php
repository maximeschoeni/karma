<?php
	$section_collection = $this->get_pages()->filter_by('post_parent', $page->ID);
?>

<nav class="page-nav">
	<ul>
		<?php foreach ($section_collection->items as $section) { ?>
			<?php if (get_page_template_slug($section->ID) !== 'template-black.php') { ?>
				<li><a href="#<?php echo $section->post_name; ?>"><?php echo get_the_title($section); ?></a></li>
			<?php } ?>
		<?php } ?>
	</ul>
</nav>
<div class="page-content">
	
	
	
	<div class="subpage-container">
		
		
		<?php if (trim($page->post_content)) { ?>
			<section class="subpage">
				<div class="subpage-content">
					<div class="subpage-columns">
						<div class="subpage-column">
							<h3><?php echo nl2br(get_the_excerpt($page)); ?></h3>
						</div>
						<div class="subpage-column">
							<?php echo apply_filters('the_content', $page->post_content); ?>
						</div>
					</div>
				</div>
			</section>
		<?php } ?>
		
		<?php foreach ($section_collection->items as $section) { ?>
			<?php $subsection_collection = $this->get_pages()->filter_by('post_parent', $section->ID) ?>
			<?php /*if (get_page_template_slug($section->ID) === 'template-black.php') { ?>
				<section class="subpage black">
					<h3><?php echo nl2br(get_the_excerpt($section)); ?></h3>
					<?php echo apply_filters('the_content', $section->post_content); ?>
				</section>
			<?php } else { */ ?>
			<section class="subpage section-<?php echo $section->post_name; ?>">
				<a class="anchor" id="<?php echo $section->post_name; ?>"></a>
				<?php if ($section->post_content || $section->post_excerpt) { ?>
					<div class="subpage-content">
						<div class="subpage-columns">
							<div class="subpage-column">
								<h3><?php echo nl2br(get_the_excerpt($section)); ?></h3>
							</div>
							<div class="subpage-column">
								<?php echo apply_filters('the_content', $section->post_content); ?>
							</div>
						</div>
					</div>
				<?php } ?>
				<?php if ($subsection_collection->items) { ?>
					<div class="subsection">	
						<?php foreach ($subsection_collection->items as $subsection) { ?>
							<?php if ($section->post_name === 'contact') { ?>
								<div class="subsection-content subsection-<?php echo $subsection->post_name; ?>">
									<h2><?php echo get_the_title($subsection); ?></h2>
									<div class="text"><?php echo apply_filters('the_content', $subsection->post_content); ?></div>
									<h3><?php echo nl2br(get_the_excerpt($subsection)); ?></h3>
								</div>
							<?php } else { ?>
								<div class="subsection-content subsection-<?php echo $subsection->post_name; ?>">
									<h3><?php echo nl2br(get_the_excerpt($subsection)); ?></h3>
									<div class="text"><?php echo apply_filters('the_content', $subsection->post_content); ?></div>
								</div>
							<?php } ?>
						<?php } ?>
					</div>
				<?php } ?>
			</section>
			<?php // } ?>
		<?php } ?>
	</div>
</nav>

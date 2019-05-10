<div class="grid main-grid" id="home-grid">
	<div class="cell agenda red">
		<div class="cell-content">
			<div class="cell-frame">
				<div class="cell-frame-content">

					<!-- <div class="cell-header">
						<a class="more" href="<?php echo home_url(); ?>/agenda/"><?php echo __('tout voir', 'karma'); ?></a>
						<h2>Agenda</h2>
					</div> -->
					<?php if ($event_ids) { ?>
						<div class="slideshow" id="agenda-slideshow">
							<div class="controller"></div>
							<div class="library">
								<?php foreach ($event_ids as $index => $event_id) { ?>
									<?php $event = get_post($event_id); ?>
									<div class="slide">
										<div class="cell-background"></div>
										<a href="<?php echo home_url().'/agenda/archives/#'.$event->post_name; //echo get_permalink($event_id); ?>">
											<div class="text">
												<p class="date">
													<?php echo Karma_Date::format(get_post_meta($event_id, 'start_date', true), 'dd.mm.yyyy', 'yyyy-mm-dd hh:ii:ss'); ?>
												</p>
												<?php if ($event->post_parent) { ?>
													<?php $project = get_post($event->post_parent); ?>
													<p class="project-author">
														<?php echo get_post_meta($event_id, 'place', true); ?>,
														<?php echo get_post_meta($event_id, 'city', true); ?>
														<?php if (get_post_meta($event_id, 'country', true)) { ?>
															(<?php echo get_post_meta($event_id, 'country', true); ?>)
														<?php } ?>
													</p>
												<?php } ?>
											</div>
										</a>
									</div>
								<?php } ?>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<?php foreach ($sticky_ids as $sticky_id) { ?>
		<?php
			$post = get_post($sticky_id);
			$image_ids = get_post_meta($sticky_id, 'images');
			$color = get_post_meta($sticky_id, 'color', true);
			$text_col_1 = '';
			$text_col_2 = '';
			if ($post->post_type === 'project') {
				//$thumb_title = __('Projet', 'karma');
				$thumb_title = get_the_title($post);
				$text_col_1 = get_post_meta($sticky_id, 'auteur', true);
				$text_col_2 = get_post_meta($sticky_id, 'description', true);
			} else {
				$thumb_title = get_post_meta($sticky_id, 'thumb_title', true);
				$text_col_1 = get_post_meta($sticky_id, 'description1', true);
				$text_col_2 = get_post_meta($sticky_id, 'description2', true);
			}
			if (!$thumb_title) {
				$thumb_title = get_the_title($post);
			}
		?>
		<div class="cell type-<?php echo $post->post_type; ?><?php if ($color) echo ' '.$color; ?><?php if ($image_ids) echo ' has-thumb'; ?>">
			<div class="cell-content">
				<div class="cell-frame">
					<div class="cell-frame-content">
						<div class="cell-background"></div>
						<a href="<?php echo get_permalink($sticky_id); ?>">
							<?php if ($image_ids) { ?>
								<div class="slideshow" id="agenda-slideshow">
									<div class="controller"></div>
									<div class="library">
										<?php foreach ($image_ids as $image_id) { ?>
											<div class="slide">
												<?php
													echo apply_filters(
														'background-image',
														'<div class="background-image" style="background-image:url('.wp_get_attachment_url($image_id).');background-size:cover;background-position:center"></div>',
														$image_id,
														'cover',
														'center',
														array(
															'class' => 'image'
														)
													);
												?>
											</div>
											<?php break; ?>
										<?php } ?>
									</div>
								</div>
							<?php } ?>
							<div class="text">
								<div class="title-content"><h2><?php echo $thumb_title; ?></h2></div>
								<!-- <?php if ($text_col_1) { ?>
									<div class="description-1">
										<?php echo nl2br($text_col_1); ?>
									</div>
								<?php }  ?>
								<?php if ($text_col_2) { ?>
									<div class="description-2">
										<?php echo nl2br($text_col_2); ?>
									</div>
								<?php }  ?> -->
							</div>
						</a>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
</div>



<div class="grid footer-grid" id="home-footer-grid">
	<?php foreach ($footer_ids as $footer_id) { ?>
		<div class="cell">
			<div class="cell-content">
				<div class="cell-frame">
					<div class="cell-frame-content">
						<div class="cell-background"></div>
						<a href="<?php echo get_permalink($footer_id); ?>">
							<div class="text">
								<h2><?php echo get_the_title($footer_id); ?></h2>
							</div>
						</a>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
</div>

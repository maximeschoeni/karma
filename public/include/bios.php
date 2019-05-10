<div class="bios">
	<?php if (isset($attr['title'])) { ?>
		<h3><?php echo $attr['title']; ?></h3>
	<?php } ?>

	<ul id="bios-container">
		<?php foreach ($bio_ids as $bio_id) { ?>
			<?php
				$bio = get_post($bio_id);
				$image_id = get_post_thumbnail_id($bio_id);

			?>
			<li class="bio">
				<a class="bio-header">
					<div class="firstname"><?php echo get_post_meta($bio_id, 'firstname', true); ?></div>
					<div class="lastname"><?php echo get_post_meta($bio_id, 'lastname', true); ?></div>
				</a>
				<div class="bio-body">
					<div class="bio-slider">
						<div class="bio-content">
							<div class="columns">
								<div class="column left">
									<?php if ($image_id) { ?>
										<div class="image-container">
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
									<?php } ?>
								</div>
								<div class="column right">
									<div class="post-content">
										<?php echo apply_filters('the_content', apply_filters('sublanguage_translate_post_field', $bio->post_content, $bio, 'post_content')); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</li>
		<?php } ?>
	</ul>
</div>

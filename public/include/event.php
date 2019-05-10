<div class="single-content">
	<div class="slideshow" id="single-slideshow">
		<div class="controller"></div>
		<div class="library">
			<?php foreach ($image_ids as $index => $image_id) { ?>
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
			<?php } ?>
		</div>
	</div>
	<nav class="post-nav">
		<ul>
			<li class="left-arrow">
				<?php if ($prev_id) { ?>
					<a href="<?php echo get_permalink($prev_id); ?>">←</a>
				<?php } ?>
			</li>
			<li class="right-arrow">
				<?php if ($next_id) { ?>
					<a href="<?php echo get_permalink($next_id); ?>">→</a>
				<?php } ?>
			</li>
		</ul>
	</nav>
	<div class="columns">
		<div class="column left">
			<div class="date">
				<p><?php echo Karma_Date::format_range($start_date, $end_date); ?></p>
				<p><?php echo get_post_meta($event_id, 'hour', true); ?></p>
			</div>
			<div class="place">
				<?php if ($place) { ?>
					<p><?php echo $place; ?></p>
				<?php } ?>
				<?php if ($city) { ?>
					<p><?php echo $city; ?></p>
				<?php } ?>
			</div>
			<div class="description">
				<?php if ($description) { ?>
					<p><em><?php echo $description; ?></em></p>
				<?php } ?>
				<?php if ($auteur) { ?>
					<p><?php echo $auteur; ?></p>
				<?php } ?>
			</div>
		</div>
		<div class="column right">
			<div class="post-content">
				<?php echo apply_filters('the_content', $post_content); ?>
			</div>
		</div>
	</div>
</div>

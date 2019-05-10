<div class="intro" id="intro">
	<div class="slideshow" id="intro-slideshow">
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
			<?php } ?>
		</div>
	</div>
</div>

<p>
	<label for="thumb_title">Titre<br></label>
	<input type="text" name="thumb_title" id="thumb_title" value="<?php echo get_post_meta($post->ID, 'thumb_title', true); ?>" placeholder="<?php echo get_the_title($post); ?>" style="width:100%;box-sizing:border-box"/>
</p>
<p>
	<label for="description1">Description 1<br></label>
	<textarea name="description1" id="description1" style="width:100%;box-sizing:border-box"><?php echo get_post_meta($post->ID, 'description1', true); ?></textarea>
</p>
<p>
	<label for="description2">Description 2<br></label>
	<textarea name="description2" id="description2" style="width:100%;box-sizing:border-box"><?php echo get_post_meta($post->ID, 'description2', true); ?></textarea>
</p>

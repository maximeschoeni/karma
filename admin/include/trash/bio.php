<p>
	<label for="lastname">Nom<br></label>
	<input type="text" name="lastname" id="lastname" value="<?php echo get_post_meta($post->ID, 'lastname', true); ?>" style="width:100%;box-sizing:border-box"/>
</p>
<p>
	<label for="firstname">Prénom<br></label>
	<input type="text" name="firstname" id="firstname" value="<?php echo get_post_meta($post->ID, 'firstname', true); ?>" style="width:100%;box-sizing:border-box"/>
</p>

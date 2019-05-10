<p>
	<label for="description">Sous-titre<br></label>
	<textarea name="description" id="description" style="width:100%;box-sizing:border-box"><?php echo get_post_meta($post->ID, 'description', true); ?></textarea>
</p>
<!-- <p>
	<label for="auteur">Auteur<br></label>
	<textarea name="auteur" id="auteur" style="width:100%;box-sizing:border-box"><?php echo get_post_meta($post->ID, 'auteur', true); ?></textarea>
</p> -->
<p>
	<label for="program_content">Programme<br></label>
	<?php wp_editor(get_post_meta($post->ID, 'program_content', true), 'program_content'); ?>
</p>

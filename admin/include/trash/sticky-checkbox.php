<p>
	<input type="checkbox" id="custom-sticky" name="custom-sticky" value="1" <?php if (get_post_meta($post->ID, 'sticky', true)) echo 'checked' ?>/>
	<label for="custom-sticky">Display on homepage</label>

	<?php $color = get_post_meta($post->ID, 'color', true); ?>
	<select id="color" name="color">
		<option value="">Couleur</option>
		<option value="blue"<?php echo ($color === 'blue' ? ' selected' : ''); ?>>Bleu</option>
		<option value="red"<?php echo ($color === 'red' ? ' selected' : ''); ?>>Rouge</option>
		<option value="green"<?php echo ($color === 'green' ? ' selected' : ''); ?>>Vert</option>
		<option value="yellow"<?php echo ($color === 'yellow' ? ' selected' : ''); ?>>Jaune</option>
		<option value="metal"<?php echo ($color === 'metal' ? ' selected' : ''); ?>>Metal</option>
	</select>

</p>

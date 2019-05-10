<?php $color = get_post_meta($post->ID, 'color', true); ?>
<select id="color" name="color">
	<option value="">Couleur</option>
	<option value="blue"<?php echo ($color === 'blue' ? ' selected' : ''); ?>>Bleu</option>
	<option value="red"<?php echo ($color === 'red' ? ' selected' : ''); ?>>Rouge</option>
	<option value="green"<?php echo ($color === 'green' ? ' selected' : ''); ?>>Vert</option>
</select>
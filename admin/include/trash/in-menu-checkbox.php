<p>
	<input type="checkbox" id="in_menu" name="in_menu" value="1" <?php if (get_post_meta($post->ID, 'in_menu', true)) echo 'checked' ?>/>
	<label for="in_menu">Afficher dans le menu</label>
</p>

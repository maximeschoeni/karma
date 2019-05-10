<p>
	<input type="checkbox" id="in_agenda" name="in_agenda" value="1" <?php if (get_post_meta($post->ID, 'in_agenda', true)) echo 'checked' ?>/>
	<label for="in_agenda">Display in Agenda</label>
</p>
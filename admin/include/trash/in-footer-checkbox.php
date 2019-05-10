<p>
	<input type="checkbox" id="footer1" name="footer1" value="1" <?php if (get_post_meta($post->ID, 'footer1', true)) echo 'checked' ?>/>
	<label for="footer1">Footer #1</label>
</p>
<p>
	<input type="checkbox" id="footer2" name="footer2" value="1" <?php if (get_post_meta($post->ID, 'footer2', true)) echo 'checked' ?>/>
	<label for="footer2">Footer #2</label>
</p>
<p>
	<input type="checkbox" id="footer3" name="footer3" value="1" <?php if (get_post_meta($post->ID, 'footer3', true)) echo 'checked' ?>/>
	<label for="footer3">Footer #3</label>
</p>

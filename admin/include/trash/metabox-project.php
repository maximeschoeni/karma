<table class="karma">
	<tr>
		<th>
			<label for="year">Année </label>
		</th>
		<td>
			<input type="text" name="year" id="year" value="<?php echo $year; ?>"/>
		</td>
	</tr>
	<tr>
		<th>
			<label for="clientx">Client</label>
		</th>
		<td>
			<input type="text" class="full-width" name="client" id="clientx" value="<?php echo $client; ?>" placeholder="<?php echo $this->get_default_meta_value('', $post, 'client'); ?>"/>
		</td>
	</tr>
	<tr>
		<th>
			<label for="excerpt">Description</label>
		</th>
		<td>
			<textarea style="width:100%;box-sizing:border-box;" name="excerpt" id="excerpt" placeholder="<?php echo $this->get_default_field_value('', $post, 'post_excerpt'); ?>"><?php echo $post->post_excerpt; ?></textarea>
		</td>
	</tr>
	<tr>
		<th>
			<label>Display</label>
		</th>
		<td>
			<input type="checkbox" name="locations[]" id="display-home-slideshow" value="slideshow" <?php if (in_array('slideshow', $locations)) echo 'checked'; ?>/><label for="display-home-slideshow">Slideshow</label><br>
			<input type="checkbox" name="locations[]" id="display-home-grid" value="home" <?php if (in_array('home', $locations)) echo 'checked'; ?>/><label for="display-home-grid">Grille</label><br>
			<!-- <input type="checkbox" name="locations[]" id="display-projects-grid" value="projects" <?php if (in_array('projects', $locations)) echo 'checked'; ?>/><label for="display-projects-grid">Projects: Grid</label><br> -->
			<input type="checkbox" name="locations[]" id="display-index" value="index" <?php if (in_array('index', $locations)) echo 'checked'; ?>/><label for="display-index">Index</label><br>
		</td>
	</tr>
	<?php /*
	<!-- 
<tr>
		<th>
			<label>Headline</label>
		</th>
		<td>
			<input type="radio" name="slideshow" id="slideshow-brand-and-art-direction" value="Brand and Art Direction" <?php if ($slideshow === 'Brand and Art Direction') echo 'checked'; ?>/><label for="slideshow-brand-and-art-direction">Brand and Art Direction</label><br>
			<input type="radio" name="slideshow" id="slideshow-strategy-and-photography" value="Strategy and Photography" <?php if ($slideshow === 'Strategy and Photography') echo 'checked'; ?>/><label for="slideshow-strategy-and-photography">Strategy and Photography</label><br>
 			<script>
 				(function() {
 					var displaySlideshow = document.getElementById("display-home-slideshow");
 					displaySlideshow.onchange = function() {
 						for (var i = 0; i < this.form.slideshow.length; i++) {
 							this.form.slideshow[i].disabled = !this.checked;
 						}
 					};
 					displaySlideshow.onchange();
 				})();
 			</script>
		</td>
	</tr>
 --> */ ?>
	<tr>
		<th>
			<label>Vignette</label>
		</th>
		<td>
			<?php echo $this->print_format_selector(null, 'thumb_format', $current_format); ?><label for="thumb_format">Format</label>
			<br>
			<input type="checkbox" name="negative_thumb" id="negative_thumb" value="1" <?php if ($negative_thumb) echo 'checked'; ?>><label for="negative_thumb">vignette négative</label>
		</td>
	</tr>
</table>
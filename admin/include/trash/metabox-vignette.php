<label for="excerpt"><strong>Description</strong></label><br>
<textarea style="width:100%;box-sizing:border-box;" name="excerpt" id="excerpt"><?php echo $post->post_excerpt; ?></textarea>
<br>
<table class="karma">
	<!-- 
<tr>
		<th>
			
		</th>
		<td>
			
		</td>
	</tr>
 -->
	<tr>
		<th>
			<label for="thumb_format">Format</label>
		</th>
		<td>
			<?php echo $this->print_format_selector(null, 'thumb_format', $current_format); ?>
		</td>
	</tr>
</table>
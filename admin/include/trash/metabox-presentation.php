<table class="karma">
	<tr>
		<th>
			<label for="text_position">Position texte</label>
		</th>
		<td>
			<input type="text" name="text_position" id="text_position" value="<?php echo $text_position; ?>" placeholder="2"/>
		</td>
	</tr>
	<tr>
		<th>
			<label for="text_format">Format texte</label>
		</th>
		<td>
			<?php $this->print_format_selector(null, 'text_format', $text_format); ?>
		</td>
	</tr>
	<tr>
		<th>
			<label for="placeholders">Placeholder</label>
		</th>
		<td>
			<input type="text" name="placeholders" id="placeholders" value="<?php echo $placeholders; ?>"/>
			<span class="description">Ex: 3, 5, 9</span>
		</td>
	</tr>
</table>
<?php
	$start_date = get_post_meta($post->ID, 'start_date', true);
	$end_date = get_post_meta($post->ID, 'end_date', true);

	$start_date = Karma_Date::format($start_date, 'dd/mm/yyyy', 'yyyy-mm-dd hh:ii:ss');

	if ($end_date) {

		$end_date = Karma_Date::format($end_date, 'dd/mm/yyyy', 'yyyy-mm-dd hh:ii:ss');

	} else {

		$end_date = $start_date;

	}

?>
<table class="karma">
	<tr>
		<th>
			<label for="description">Sous-titre</label>
		</th>
		<td>
			<input type="text" name="description" id="description" value="<?php echo get_post_meta($post->ID, 'description', true); ?>" placeholder="<?php echo $post->post_parent ? get_post_meta($post->post_parent, 'description', true) : ''; ?>"/>
		</td>
	</tr>
	<!-- <tr>
		<th>
			<label for="auteur">Auteur</label>
		</th>
		<td>
			<input type="text" name="auteur" id="auteur" value="<?php echo get_post_meta($post->ID, 'auteur', true); ?>" placeholder="<?php echo $post->post_parent ? get_post_meta($post->post_parent, 'auteur', true) : ''; ?>"/>
		</td>
	</tr> -->
	<tr>
		<th>
			<label for="start_date">Date de début</label>
		</th>
		<td>
			<input type="text" name="start_date" id="start_date" value="<?php echo $start_date ?>" placeholder="dd/mm/yyyy"/>
			<script>
				document.addEventListener('DOMContentLoaded', function() {
					createDatePopupManager(document.getElementById("start_date"));
				});
			</script>
		</td>
	</tr>
	<tr>
		<th>
			<label for="end_date">Date de fin (si différente)</label>
		</th>
		<td>
			<input type="text" name="end_date" id="end_date" value="<?php if ($end_date !== $start_date) echo $end_date; ?>" placeholder="<?php echo $start_date; ?>"/>
			<script>
				document.addEventListener('DOMContentLoaded', function() {
					createDatePopupManager(document.getElementById("end_date"));
				});
			</script>
		</td>
	</tr>
	<tr>
		<th>
			<label for="hour">Heure</label>
		</th>
		<td>
			<input type="text" name="hour" id="hour" value="<?php echo get_post_meta($post->ID, 'hour', true); ?>"/>
		</td>
	</tr>
	<tr>
		<th>
			<label for="name">Nom de l'évenement</label>
		</th>
		<td>
			<input type="text" name="name" id="name" value="<?php echo get_post_meta($post->ID, 'name', true); ?>"/>
		</td>
	</tr>
	<tr>
		<th>
			<label for="place">Lieu (salle/institution)</label>
		</th>
		<td>
			<input type="text" name="place" id="place" value="<?php echo get_post_meta($post->ID, 'place', true); ?>"/>
		</td>
	</tr>
	<tr>
		<th>
			<label for="city">Lieu (ville)</label>
		</th>
		<td>
			<input type="text" name="city" id="city" value="<?php echo get_post_meta($post->ID, 'city', true); ?>"/>
		</td>
	</tr>
	<tr>
		<th>
			<label for="country">Lieu (pays)</label>
		</th>
		<td>
			<input type="text" name="country" id="country" value="<?php echo get_post_meta($post->ID, 'country', true); ?>"/>
		</td>
	</tr>

</table>

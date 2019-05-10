<table id="team-table">
	<tbody>
		<?php foreach ($team_query->posts as $post) { ?>
			<tr data-thumb="<?php echo get_the_post_thumbnail_url($post, 'medium'); ?>">
				<td><?php echo $post->post_title; ?></td>
				<td><?php echo $post->post_excerpt; ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<script>
	document.addEventListener("DOMContentLoaded", function() {
		var table = document.getElementById("team-table");
		if (table) {
			registerThumbHover(table);
		}
	});
</script>
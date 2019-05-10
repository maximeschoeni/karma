<p class="post-attributes-label-wrapper">
	<label class="post-attributes-label" for="parent_id">Parent</label>
</p>
<!-- <pre><?php //var_dump($post); ?></pre> -->

<select name="parent_id" id="parent_id">
	<option value="">(pas de parent)</option>
	<?php foreach ($project_ids as $project_id) { ?>
		<option value="<?php echo $project_id ?>"<?php if ($post->post_parent === $project_id) echo ' selected'; ?>><?php echo get_the_title($project_id); ?></option>
	<?php } ?>
</select>
<?php if ($post->post_parent) { ?>
	<p>
		Lien vers le projet: <a href="<?php echo admin_url('post.php?post='.$post->post_parent.'&action=edit'); ?>"><?php echo get_the_title($post->post_parent); ?></a>
	</p>
<?php } ?>

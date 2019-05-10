<?php foreach ($languages as $language) { ?>
	<?php if (!$sublanguage->is_current($language)) { ?>
		<a href="<?php echo $sublanguage->get_translation_link($language); ?>"><?php echo $language->post_name; ?></a>
	<?php } ?>
<?php } ?>

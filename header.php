<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php echo get_bloginfo('description'); ?>">
	<title><?php echo get_bloginfo('name'); ?><?php echo wp_title('|'); ?></title>
	<?php wp_head(); ?>
</head>

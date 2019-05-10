<?php
	get_header();
?>
<body>
	<header>
		<?php do_action('karma_header'); ?>
	</header>
	<main>
		<?php do_action('karma_body'); ?>
	</main>
	<?php wp_footer(); ?>
</body>
<?php get_footer(); ?>

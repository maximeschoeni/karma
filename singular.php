<?php
	get_header();
?>
<body class="single">
	<header>
		<?php do_action('karma_header'); ?>
	</header>
	<main>
		<?php do_action('karma_single'); ?>
	</main>
	<?php wp_footer(); ?>
</body>
<?php get_footer(); ?>

<?php
	get_header();
?>
<body class="home">
	<header>
		<?php do_action('karma_header'); ?>
	</header>
	<div class="body-frame">
		<?php do_action('karma_intro'); ?>
		<main>
			<?php do_action('karma_grid'); ?>
		</main>
		<?php wp_footer(); ?>
	</div>
</body>
<?php get_footer(); ?>

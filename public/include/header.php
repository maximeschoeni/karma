<div class="header-content" id="header-content">
	<div class="header-table">
		<div class="header-cell banner">
			<a href="<?php echo home_url(); ?>">
				<h1 id="marquee"><span>Eklekto Geneva Percussion Center&nbsp;</span></h1>
			</a>
		</div>
		<div class="header-cell menu-btn">
			<a id="menu-btn">Menu</a>
			<a id="language-btn">EN</a>
		</div>
	</div>
</div>

<div class="menu" id="menu">
	<a id="close-menu-btn" href="#">Fermer</a>
	<?php
		wp_nav_menu(array(
			'theme_location' => 'main_menu',
			'container' => false,
			'menu_class' => 'menu-content',
			'menu_id' => 'menu-content'
		));
	?>
</div>

<!-- <div id="menu-background"></div> -->

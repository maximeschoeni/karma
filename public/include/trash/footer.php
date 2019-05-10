<?php
	
// 	$footer_page = $this->find_page('footer');
// 	$subpages = $this->find_pages($footer_page->ID, 'post_parent');
// 	$contact_page = $this->find_page('contact');
	
	$footer_page = $this->get_pages()->get_item('post_name', 'footer');
	
	if ($footer_page) {
	
		$footer_contact = $this->get_pages()->get_item('post_parent', $footer_page->ID);
	
?>
<div id="top-arrow" style="opacity:none" href="#"><img src="<?php echo get_template_directory_uri() ?>/images/up-arrow.svg"/></div>
<script>
	(function() {
		var arrow = document.getElementById("top-arrow");
		function update() {
			arrow.style.display = (pageYOffset > (window.innerHeight || 500)/2) ? "block" : "none";
		}
		addEventListener("scroll", update);
		update();
	})();
</script>
<footer id="footer">
	<div class="footer-content" id="footer-content">
		<?php if (isset($footer_contact) && !is_page('about')) { ?>
			<div class="contact-page-container">
				<?php echo apply_filters('the_content', $footer_contact->post_content); ?>
			</div>
		<?php } ?>
		<div class="footer-menu">
			<?php if ($footer_page) echo apply_filters('the_content', $footer_page->post_content); ?>
		</div>
	</div>
</footer>
<div class="footer-placeholder" id="footer-placeholder"></div>
<script>
	(function() {
		var placeholder = document.getElementById("footer-placeholder");
		var content = document.getElementById("footer-content");
		function update() {
			placeholder.style.height = content.clientHeight+"px";
		}
		window.addEventListener("resize", function() {
			update();
		})
		document.addEventListener("DOMContentLoaded", function(event) {
			update();
		});
	})();
</script>
<?php } ?>
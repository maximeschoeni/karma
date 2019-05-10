<div class="agenda-header" id="agenda-header">
	<ul id="agenda-header-ul">
		<?php for ($year = $max_year; $year >= $min_year; $year--) { ?>
			<li <?php if ($year === $current_year) echo 'class="active"'; ?>>
				<a href="<?php echo home_url(); ?>/agenda/archives/<?php echo $year; ?>"><?php echo $year . '–' . ($year+1); ?></a>
			</li>
		<?php } ?>
	</ul>
</div>

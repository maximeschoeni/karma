<div class="agenda-body" id="agenda-body">
	<ul id="agenda-body-ul">
		<?php foreach ($event_ids as $index => $event_id) { ?>
			<?php
				$event = get_post($event_id);
				$start_date = get_post_meta($event_id, 'start_date', true);
				$end_date = get_post_meta($event_id, 'end_date', true);
				$date_range = Karma_Date::format_range($start_date, $end_date);
				$hour = get_post_meta($event_id, 'hour', true);
				$name = get_post_meta($event_id, 'name', true);
				$place = get_post_meta($event_id, 'place', true);
				$city = get_post_meta($event_id, 'city', true);
				$country = get_post_meta($event_id, 'country', true);
				$full_date = $date_range . ($hour ? ', ' . $hour : '');
				$full_place = ($place ? $place : '') . ($place && $city ? ', ' : '') . ($city ? $city : '') . ($country ? ' ('.$country.')' : '');
				$permalink = get_permalink($event_id);
			?>
			<li data-date="<?php echo $end_date; ?>" data-hash="<?php echo $event->post_name; ?>">
				<!-- <div class="anchor" id="<?php echo $event->post_name; ?>"></div> -->
				<div class="event-header">
					<a href="<?php echo $permalink; ?>" data-json="<?php echo $this->get_event_json_link($event_id); ?>">
						<div class="date"><?php echo $full_date; ?></div>
						<?php if ($name) { ?>
							<div class="name"><?php echo $name; ?></div>
						<?php } ?>
						<div class="place"><?php echo $full_place; ?></div>
					</a>
				</div>
			</li>

		<?php } ?>
	</ul>
</div>
<script>
	(function() {
		var ul = document.getElementById("agenda-body-ul");
		for (var i = 0; i < ul.children.length; i++) {
			if (ul.children[i].getAttribute("data-date") > (new Date()).toISOString()) {
				ul.children[i].classList.add("upcoming");
			}
		}
	})();
</script>

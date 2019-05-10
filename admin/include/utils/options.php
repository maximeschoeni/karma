<form action="<?php echo admin_url(); ?>" method="POST">
	<?php wp_nonce_field($this->settings_action, $this->settings_nonce, true, true); ?>
	<h2>Settings</h2>
	<table class="form-table">
		<tbody>

			<?php do_action('karma_print_options', $this); ?>
			
			<!-- <tr>
				<th>Reservation automatique email</th>
				<td>
					<label><input type="text" name="reservation_email" value="<?php echo $this->get_option('reservation_email', ''); ?>" autocomplete="off"/> Réservation</label><br>
					<label><input type="text" name="achat_email" value="<?php echo $this->get_option('achat_email', ''); ?>" autocomplete="off"/> Achat</label><br>
				</td>
			</tr> -->
		</tbody>
	</table>
	<?php echo submit_button(); ?>
</form>

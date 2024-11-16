<table class="form-table">
	<tr valign="top" class="settings-row settings-row-export">
		<th scope="row"><?php esc_html_e( 'Export', 'mailster' ); ?>
		<p class="description">
		<?php printf( esc_html_x( 'Use this data to copy your settings between %s installations. This data contains sensitive information like passwords so don\'t share them. Capabilities are not included.', 'Mailster', 'mailster' ), 'Mailster' ); ?>
		</p>
		</th>
		<td><textarea rows="10" cols="40" class="large-text code" id="settings-export-data"><?php echo $this->export_settings(); ?></textarea>
		<p><a class="clipboard" data-clipboard-target="#settings-export-data"><?php esc_html_e( 'Copy to Clipboard', 'mailster' ); ?></a></p>
		</td>
	</tr>
	<tr valign="top" class="settings-row settings-row-import">
		<th scope="row"><?php esc_html_e( 'Import', 'mailster' ); ?>
		<p class="description">
		<?php esc_html_e( 'Import your settings by pasting the exported data. Make sure you check the data after import.', 'mailster' ); ?>
		</p>
		</th>
		<td><textarea rows="10" cols="40" class="large-text code" name="mailster_settings_data"></textarea>
		<p class="alignright"><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Import Data', 'mailster' ); ?>" name="mailster_import_data" id="mailster_import_data" /></p>
		</td>
	</tr>
	<tr valign="top" class="settings-row settings-row-reset-settings">
		<th scope="row"><?php esc_html_e( 'Reset Settings', 'mailster' ); ?>
		</th>
		<td><a href="edit.php?post_type=newsletter&page=mailster_settings&reset-settings=1&_wpnonce=<?php echo wp_create_nonce( 'mailster-reset-settings' ); ?>" class="button" id="mailster_reset_data"><?php esc_html_e( 'Reset all settings', 'mailster' ); ?></a>
		<p class="description">
		<?php printf( esc_html_x( 'Use this options to reset the data to %s default values.', 'Mailster', 'mailster' ), 'Mailster' ); ?>
		</p>
		</td>
	</tr>
</table>

<div class="mailster-setup-step-body">

<form class="mailster-setup-step-form">

<?php $tags = mailster_option( 'tags' ); ?>

<p><?php esc_html_e( 'Please update the default settings for your email campaigns. You can customize these settings for each individual campaign.', 'mailster' ); ?></p>
<table class="form-table">

	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'From Name', 'mailster' ); ?></th>
		<td><input type="text" name="mailster_options[from_name]" value="<?php echo esc_attr( mailster_option( 'from_name' ) ); ?>" class="regular-text"> <p class="description"><?php esc_html_e( 'The sender name which is displayed in the from field', 'mailster' ); ?></p></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'From Address', 'mailster' ); ?></th>
		<td><input type="text" name="mailster_options[from]" value="<?php echo esc_attr( mailster_option( 'from' ) ); ?>" class="regular-text"> <p class="description"><?php esc_html_e( 'The sender email address. Ask your subscribers to white label this email address.', 'mailster' ); ?></p></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Reply To Address', 'mailster' ); ?></th>
		<td><input type="text" name="mailster_options[reply_to]" value="<?php echo esc_attr( mailster_option( 'reply_to' ) ); ?>" class="regular-text"> <p class="description"><?php esc_html_e( 'The address users can reply to', 'mailster' ); ?></p></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php esc_html_e( 'CAN-SPAM', 'mailster' ); ?></th>
		<td><textarea name="mailster_options[tags][can-spam]" class="regular-text" rows="5"><?php echo esc_textarea( $tags['can-spam'] ); ?></textarea><p class="description"><?php esc_html_e( 'This line is required in most countries. Your subscribers need to know why and where they have subscribed.', 'mailster' ); ?></p></td>
	</tr>
</table>
</form>

</div>



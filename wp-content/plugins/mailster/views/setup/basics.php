
<div class="mailster-setup-step-body">

<form class="mailster-setup-step-form">

	<p><?php esc_html_e( 'Please provide some basic information which is used for your newsletter campaigns. Mailster already pre-filled the fields with the default values but you should check them for correctness.', 'mailster' ); ?></p>

	<?php $tags = mailster_option( 'tags' ); ?>

	<table class="form-table">

		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Company', 'mailster' ); ?></th>
			<td><input type="text" name="mailster_options[tags][company]" value="<?php echo esc_attr( $tags['company'] ); ?>" class="regular-text"></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Copyright', 'mailster' ); ?></th>
			<td><input type="text" name="mailster_options[tags][copyright]" value="<?php echo esc_attr( $tags['copyright'] ); ?>" class="regular-text"></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Homepage', 'mailster' ); ?></th>
			<td><input type="text" name="mailster_options[tags][homepage]" value="<?php echo esc_attr( $tags['homepage'] ); ?>" class="regular-text"></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Address', 'mailster' ); ?></th>
			<td><textarea name="mailster_options[tags][address]" class="regular-text" rows="5"><?php echo esc_textarea( $tags['address'] ); ?></textarea></td>
		</tr>
		<tr valign="top">
		<th scope="row"><?php esc_html_e( 'Logo', 'mailster' ); ?>
		</th>
		<td>
			<?php mailster( 'helper' )->media_editor_link( mailster_option( 'logo', get_theme_mod( 'custom_logo' ) ), 'mailster_options[logo]', 'full' ); ?>
			<p class="description"><label><input type="hidden" name="mailster_options[logo_high_dpi]" value=""><input type="checkbox" name="mailster_options[logo_high_dpi]" value="1" <?php checked( mailster_option( 'logo_high_dpi' ) ); ?>> <?php esc_html_e( 'Use High DPI version if available.', 'mailster' ); ?></label></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Logo Link', 'mailster' ); ?></th>
			<td><input type="text" name="mailster_options[logo_link]" value="<?php echo esc_attr( mailster_option( 'logo_link' ) ); ?>" class="regular-text"> <p class="description"><?php esc_html_e( 'A link for your logo.', 'mailster' ); ?></p></td>
		</tr>		
		
		<tr valign="top">
			<th scope="row"></th>
			<td><p class="description"><?php printf( esc_html__( 'Wonder what these {placeholders} are for? Read more about tags %s.', 'mailster' ), '<a href="' . mailster_url( 'https://kb.mailster.co/611bb5296ffe270af2a99926' ) . '" data-article="611bb5296ffe270af2a99926">' . esc_html__( 'here', 'mailster' ) . '</a>' ); ?></p></td>
		</tr>

	</table>



</form>
</div>



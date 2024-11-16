<?php

/**
 * @param array $shortcodes [Shortcode name => [label, attributes]], where attributes
 *                          is [description, default (or default_label), required] (all
 *                          optional).
 *
 * @see MotoPress\Appointment\AdminPages\Custom\HelpPage
 *
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="shortcodes-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Shortcodes', 'motopress-appointment' ); ?></h1>
	<table class="widefat striped">
		<thead>
			<tr>
				<td class="column-shortcode"><?php esc_html_e( 'Shortcode', 'motopress-appointment' ); ?></td>
				<td class="column-parameter"><?php esc_html_e( 'Parameter', 'motopress-appointment' ); ?></td>
				<td class="column-description"><?php esc_html_e( 'Description', 'motopress-appointment' ); ?></td>
				<td class="column-default"><?php esc_html_e( 'Default', 'motopress-appointment' ); ?></td>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $shortcodes as $shortcodeName => $details ) { ?>
				<tr>
					<th scope="row" colspan="4">
						<p>
							<strong><?php echo $details['label']; ?></strong>
							<code>[<?php echo $shortcodeName; ?>]</code>
						</p>
					</th>
				</tr>

				<?php if ( ! empty( $details['attributes'] ) ) { ?>
					<?php foreach ( $details['attributes'] as $attribute => $args ) { ?>
						<tr>
							<td class="column-shortcode"></td>

							<td class="column-parameter">
								<code><?php echo $attribute; ?></code>
							</td>

							<td class="column-description">
								<?php if ( ! empty( $args['description'] ) ) { ?>
									<?php echo $args['description']; ?>
								<?php } ?>
								<?php if ( isset( $args['required'] ) ) { ?>
									<em>
										<?php esc_html_e( 'Required.', 'motopress-appointment' ); ?>
									</em>
								<?php } ?>
							</td>

							<td class="column-default">
								<?php
								// Can be NULL, so isset() not suitable here
								if ( array_key_exists( 'default_label', $args ) ) {
									// Can be NULL to prevent output
									$default = $args['default_label'];
								} elseif ( isset( $args['default'] ) ) {
									$default = $args['default'];
								} else {
									$default = null;
								}

								if ( isset( $default ) ) { // And not NULL
									?>
									<em>
										<?php
										// Convert to string
										if ( is_bool( $default ) ) {
											// This should not be translated
											$default = $default ? 'yes' : 'no';
										} elseif ( is_array( $default ) ) {
											$default = implode( ',', $default );
										}

										switch ( $default ) {
											case '':
												esc_html_e( 'empty string', 'motopress-appointment' );
												break;
											default:
												echo $default;
												break;
										}
										?>
									</em>
								<?php } else { ?>
									<?php echo mpa_tmpl_aria_placeholder(); ?>
								<?php } ?>
							</td>
						</tr>
					<?php } // For each parameter ?>
				<?php } // If has parameters ?>
			<?php } // For each shortcode ?>
		</tbody>
	</table>
</div>

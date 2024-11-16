<?php

/**
 * @since 1.9.0
 *
 * @param array $template_args The list of all args.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add empty value to use without reservations
echo '<input type="hidden" name="reservations" value="">';

mpa_display_template( 'shortcodes/booking/step-service-form.php', $template_args );
mpa_display_template( 'shortcodes/booking/step-period.php', $template_args );
mpa_display_template( 'shortcodes/booking/step-admin-cart.php', $template_args );

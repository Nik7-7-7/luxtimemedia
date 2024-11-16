<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string $html_id Unique ID of the shortcode instance.
 * @param string $shortcode_name Full shortcode name, like 'mpa_booking'.
 * @param array $template_args The list of all args.
 *
 * @since 1.0
 */

?>

<?php

/**
 * @hooked \MotoPress\Appointment\Shortcodes\AppointmentFormShortcode::displaySteps()
 *
 * @since 1.0
 */
do_action( "{$shortcode_name}_shortcode_steps", $template_args );

?>

<p class="mpa-message mpa-error mpa-hide"></p>

<div class="mpa-loading"></div>

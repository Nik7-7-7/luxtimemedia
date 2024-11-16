<?php

/**
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<li><?php esc_html_e( '{service_name} with {employee_name} on {reservation_date} from {start_buffer_time} till {end_buffer_time} at {location_name}.', 'motopress-appointment' ); ?></li>

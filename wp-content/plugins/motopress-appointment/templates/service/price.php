<?php

/**
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$service = mpa_get_service();

if ( is_null( $service ) ) {
	return;
}

?>
<p class="mpa-service-price mpa-regular-price">
	<span class="mpa-price-title"><?php esc_html_e( 'Price', 'motopress-appointment' ); ?>:</span>
	<?php echo mpa_tmpl_price( $service->getPrice() ); ?>
</p>

<?php
/**
 * @var string $customerName
 * @var string $customerEmail
 * @var string $customerPhone
 * @var string $totalBookings
 * @var string $bookingsURL
 *
 * @since 1.18.0
 */

?>
<div class="mpa-account-details">
    <div class="mpa-customer-details">
        <div class="mpa-customer-details-row mpa-customer-name">
            <span class="cell label"><?php esc_html_e( 'Name', 'motopress-appointment' ); ?>:</span>
            <span class="cell value"><?php echo esc_html( $customerName ); ?></span>
        </div>
        <div class="mpa-customer-details-row mpa-customer-email">
            <span class="cell label"><?php esc_html_e( 'Email', 'motopress-appointment' ); ?>:</span>
            <span class="cell value"><a href="mailto:<?php echo sanitize_email( $customerEmail ); ?>"><?php echo sanitize_email( $customerEmail ); ?></a></span>
        </div>
        <div class="mpa-customer-details-row mpa-customer-phone">
            <span class="cell label"><?php esc_html_e( 'Phone', 'motopress-appointment' ); ?>:</span>
            <span class="cell value"><a href="tel:<?php echo esc_attr( $customerPhone ); ?>"><?php echo esc_html( $customerPhone ); ?></a></span>
        </div>
        <div class="mpa-customer-details-row mpa-customer-total-bookings">
            <span class="cell label"><?php esc_html_e( 'Total Bookings', 'motopress-appointment' ); ?>:</span>
            <span class="cell value"><a href="<?php echo esc_url( $bookingsURL ); ?>"><?php echo esc_html( $totalBookings ); ?></a></span>
        </div>
    </div>
</div>

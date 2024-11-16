<?php
/**
 * @var $customerAccountURL
 * @var $bookingsURL
 * @var $logoutURL
 *
 * @since 1.18.0
 *
 */
?>
<nav class="mpa-account-menu">
    <ul>
        <li><a href="<?php echo esc_url( $customerAccountURL ); ?>"><?php echo esc_html__( 'Account', 'motopress-appointment' ); ?></a></li>
        <li><a href="<?php echo esc_url( $bookingsURL ); ?>"><?php echo esc_html__( 'Bookings', 'motopress-appointment' ); ?></a></li>
        <li><a href="<?php echo esc_url( $logoutURL ); ?>"><?php echo esc_html__( 'Logout', 'motopress-appointment' ); ?></a></li>
    </ul>
</nav>

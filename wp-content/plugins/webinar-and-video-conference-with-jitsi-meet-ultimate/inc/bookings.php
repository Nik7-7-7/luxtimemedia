<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$count = 0;

if(!isset($tables['today'])){
    $user_id      = get_current_user_id();
    $current_page = empty( $current_page ) ? 1 : absint( $current_page );
    $bookings_per_page = apply_filters( 'woocommerce_bookings_my_bookings_per_page', 10 );

    $today_bookings = WC_Booking_Data_Store::get_bookings_for_user( $user_id, apply_filters( 'woocommerce_bookings_my_bookings_today_query_args', array(
        'order_by'       => apply_filters( 'woocommerce_bookings_my_bookings_today_order_by', 'start_date' ),
        'order'          => 'ASC',
        'date_after'     => strtotime( 'yesterday', current_time( 'timestamp' ) ),
        'date_before'    => strtotime( 'tomorrow', current_time( 'timestamp' ) ),
        'offset'         => ( $current_page - 1 ) * $bookings_per_page,
        'limit'          => $bookings_per_page + 1, // Increment to detect pagination.
    ) ) );

    if ( ! empty( $today_bookings ) ) {
        $tables['today'] = array(
            'header'   => __( 'Today\'s Bookings', 'woocommerce-bookings' ),
            'bookings' => $today_bookings,
        );
    }
}


if ( ! empty( $tables ) ) : ?>
    <div class="bookings-my-account-notice"></div>

	<?php foreach ( $tables as $table ) : ?>

        <h2><?php echo esc_html( $table['header'] ); ?></h2>

        <table class="shop_table my_account_bookings">
            <thead>
            <tr>
                <th scope="col" class="booking-id"><?php esc_html_e( 'ID', 'woocommerce-bookings' ); ?></th>
                <th scope="col" class="booked-product"><?php esc_html_e( 'Booked', 'woocommerce-bookings' ); ?></th>
                <th scope="col" class="order-number"><?php esc_html_e( 'Order', 'woocommerce-bookings' ); ?></th>
                <th scope="col" class="booking-start-date"><?php esc_html_e( 'Start Date', 'woocommerce-bookings' ); ?></th>
                <th scope="col" class="booking-end-date"><?php esc_html_e( 'End Date', 'woocommerce-bookings' ); ?></th>
                <th scope="col" class="booking-join-link"><?php esc_html_e( 'Join Link', 'woocommerce-bookings' ); ?></th>
                <th scope="col" class="booking-status"><?php esc_html_e( 'Status', 'woocommerce-bookings' ); ?></th>
                <th scope="col" class="booking-cancel"></th>
            </tr>
            </thead>
            <tbody>
			<?php foreach ( $table['bookings'] as $booking ) : ?><?php $count ++; ?>
                <tr>
                    <td class="booking-id"><?php echo esc_html( $booking->get_id() ); ?></td>
                    <td class="booked-product">
						<?php if ( $booking->get_product() && $booking->get_product()->is_type( 'booking' ) ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $booking->get_product()->get_id() ) ); ?>">
								<?php echo esc_html( $booking->get_product()->get_title() ); ?>
                            </a>
						<?php endif; ?>
                    </td>
                    <td class="order-number">
						<?php if ( $booking->get_order() ) : ?>
                            <a href="<?php echo esc_url( $booking->get_order()->get_view_order_url() ); ?>">
								<?php echo esc_html( $booking->get_order()->get_order_number() ); ?>
                            </a>
						<?php endif; ?>
                    </td>
                    <td class="booking-start-date"><?php echo esc_html( $booking->get_start_date( null, null, wc_should_convert_timezone( $booking ) ) ); ?></td>
                    <td class="booking-end-date"><?php echo esc_html( $booking->get_end_date( null, null, wc_should_convert_timezone( $booking ) ) ); ?></td>
                    <td class="booking-join-link">
						<?php
						if ( $booking->get_status() === 'confirmed' || $booking->get_status() === 'paid' || $booking->get_status() === 'complete' ) {
							echo Jitsi_Meet_WP_WooCommerce_Booking::get_join_link( $booking );
						} else {
							_e( 'N/A', 'vczapi-woo-addon' );
						}
						?></td>
                    <td class="booking-status"><?php echo esc_html( wc_bookings_get_status_label( $booking->get_status() ) ); ?></td>
                    <td class="booking-cancel">
						<?php if ( 'cancelled' !== $booking->get_status() && 'completed' !== $booking->get_status() && ! $booking->passed_cancel_day() ) : ?>
                            <a href="<?php echo esc_url( $booking->get_cancel_url() ); ?>" class="button cancel"><?php esc_html_e( 'Cancel', 'woocommerce-bookings' ); ?></a>
						<?php endif; ?>
                    </td>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>

		<?php do_action( 'woocommerce_before_account_bookings_pagination' ); ?>

        <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php if ( 1 !== $page ) : ?>
                <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'bookings', $page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce-bookings' ); ?></a>
			<?php endif; ?>

			<?php if ( $count >= $bookings_per_page ) : ?>
                <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'bookings', $page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce-bookings' ); ?></a>
			<?php endif; ?>
        </div>

		<?php do_action( 'woocommerce_after_account_bookings_pagination' ); ?>

	<?php endforeach; ?>

<?php else : ?>
    <div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
        <a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php esc_html_e( 'Go Shop', 'woocommerce-bookings' ); ?>
        </a>
		<?php esc_html_e( 'No bookings available yet.', 'woocommerce-bookings' ); ?>
    </div>
<?php endif; ?>

<?php
/**
 * @var MotoPress\Appointment\Entities\Booking $booking
 * @var bool $isShowAddToCalendarLinks
 *
 * @since 1.18.0
 */
?>

<div class="mpa-booking-details">
	<div class="mpa-booking-details-section booking-reservations">
		<?php if ( count( $booking->getReservations() ) ) : ?>
			<?php foreach ( $booking->getReservations() as $reservation ) : ?>
				<div class="reservation"
					data-reservation-id="<?php echo esc_attr( $reservation->getId() ); ?>"
					data-start-time="<?php echo esc_attr( $reservation->getServiceTime()->getStartTime()->format( DateTime::ATOM ) ); ?>"
					data-end-time="<?php echo esc_attr( $reservation->getServiceTime()->getEndTime()->format( DateTime::ATOM ) ); ?>"
					data-service-name="<?php echo esc_html( get_the_title( $reservation->getServiceId() ) ); ?>"
					data-employee-name="<?php echo esc_attr( $reservation->getEmployee()->getName() ); ?>"
					data-location-name="<?php echo esc_attr( $reservation->getLocation()->getName() ); ?>"
					data-capacity="<?php echo esc_attr( $reservation->getCapacity() ); ?>"
					data-quantity-label="<?php echo esc_attr( $reservation->getService()->getQuantityLabel() ); ?>"
				>
					<div class="mpa-booking-details-section-row">
						<span class="cell reservation-title value">
							<?php echo esc_html( get_the_title( $reservation->getServiceId() ) ); ?>
							<?php if ( $reservation->getCapacity() > 1 ) { ?>
								<span class="mpa-reservation-capacity">
									<?php
									$service = mpa_get_service( $reservation->getServiceId() );

									if ( ! is_null( $service ) ) {
										echo esc_html( $service->getQuantityLabel() );
									} else {
										esc_html_e( 'Clients', 'motopress-appointment' );
									}

									echo ': ', $reservation->getCapacity();
									?>
								</span>
							<?php } ?>
						</span>
						<div class="cell reservation-full-date value">
							<?php echo sprintf(
								'%s &middot; %s',
								'<span class="reservation-date">' . esc_html( mpa_format_date( $reservation->getDate() ) ) . '</span>',
								'<span class="reservation-time">' . esc_html( $reservation->getServiceTime()->toString() ) . '</span>'
							); ?>
						</div>
					</div>

					<div class="reservation-calendar-links mpa-booking-details-section-row">
						<?php if ( isset( $isShowAddToCalendarLinks ) && $isShowAddToCalendarLinks ) : ?>
							<span class="cell label"><?php esc_html_e( 'Add to your calendar: ', 'motopress-appointment' ); ?></span>
							<div class="cell value">
								<a href="#" target="_blank" class="mpa-add-to-calendar-link mpa-add-to-calendar-link--google"
									title="<?php esc_html_e( 'Google', 'motopress-appointment' ); ?>"><span class="mpa-add-to-calendar-link-text"><?php esc_html_e( 'Google', 'motopress-appointment' ); ?></span></a>,
								<a href="#" target="_self" class="mpa-add-to-calendar-link mpa-add-to-calendar-link--apple"
									download="cal.ics" title="<?php esc_html_e( 'Apple', 'motopress-appointment' ); ?>"><span class="mpa-add-to-calendar-link-text"><?php esc_html_e( 'Apple', 'motopress-appointment' ); ?></span></a>,
								<a href="#" target="_self" class="mpa-add-to-calendar-link mpa-add-to-calendar-link--outlook"
									download="cal.ics" title="<?php esc_html_e( 'Outlook', 'motopress-appointment' ); ?>"><span class="mpa-add-to-calendar-link-text"><?php esc_html_e( 'Outlook', 'motopress-appointment' ); ?></span></a>,
								<a href="#" target="_blank" class="mpa-add-to-calendar-link mpa-add-to-calendar-link--yahoo"
									title="<?php esc_html_e( 'Yahoo!', 'motopress-appointment' ); ?>"><span class="mpa-add-to-calendar-link-text"><?php esc_html_e( 'Yahoo!', 'motopress-appointment' ); ?></span></a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<div class="mpa-booking-details-section booking-info">
			<div class="booking-id mpa-booking-details-section-row booking-status--<?php echo esc_attr( $booking->getStatus() ); ?>">
				<span class="cell label"><?php esc_html_e( 'Booking', 'motopress-appointment' ); ?> #<?php echo esc_html( $booking->getId() ); ?></span>
				<span class="cell value"><?php echo esc_html( mpapp()->postTypes()->booking()->statuses()->getLabel( $booking->getStatus() ) ); ?></span>
			</div>
			<div class="booking-date mpa-booking-details-section-row">
				<span class="cell label"><?php esc_html_e( 'Date', 'motopress-appointment' ); ?>:</span>
				<span class="cell value"><?php echo esc_html( get_post_time( mpa_date_format(), false, $booking->getId(), true ) ); ?></span>
			</div>
			<div class="booking-total-price mpa-booking-details-section-row">
				<span class="cell label"><?php esc_html_e( 'Total Price', 'motopress-appointment' ); ?>:</span>
				<span class="cell value"><?php echo mpa_tmpl_price( $booking->getTotalPrice() ); ?></span>
			</div>
			<div class="booking-total-paid mpa-booking-details-section-row">
				<span class="cell label"><?php esc_html_e( 'Total Paid', 'motopress-appointment' ); ?>:</span>
				<span class="cell value"><?php echo mpa_tmpl_price( $booking->getPaidPrice(), array( 'literal_free' => false ) ); ?></span>
			</div>
			<div class="booking-customer-name mpa-booking-details-section-row">
				<span class="cell label"><?php esc_html_e( 'Name', 'motopress-appointment' ); ?>:</span>
				<span class="cell value"><?php echo esc_html( $booking->getCustomerName() ); ?></span>
			</div>
			<div class="booking-customer-email mpa-booking-details-section-row">
				<span class="cell label"><?php esc_html_e( 'Email', 'motopress-appointment' ); ?>:</span>
				<span class="cell value"><?php echo esc_html( $booking->getCustomerEmail() ); ?></span>
			</div>
			<div class="booking-customer-phone mpa-booking-details-section-row">
				<span class="cell label"><?php esc_html_e( 'Phone', 'motopress-appointment' ); ?>:</span>
				<span class="cell value"><?php echo esc_html( $booking->getCustomerPhone() ); ?></span>
			</div>
	</div>

	<?php if ( count( $booking->getPayments() ) ) : ?>
		<div class="mpa-booking-details-section booking-payments">
			<?php foreach ( $booking->getPayments() as $payment ) : ?>
				<div class="payment">
					<div class="payment-id mpa-booking-details-section-row payment-status--<?php echo esc_attr( $payment->getStatus() ); ?>">
						<span class="cell label"><?php esc_html_e( 'Payment', 'motopress-appointment' ); ?> #<?php echo esc_html( $payment->getId() ); ?></span>
						<span class="cell value"><?php echo esc_html( mpapp()->postTypes()->payment()->statuses()->getLabel( $payment->getStatus() ) ); ?></span>
					</div>
					<div class="payment-date mpa-booking-details-section-row">
						<span class="cell label"><?php esc_html_e( 'Date', 'motopress-appointment' ); ?>:</span>
						<span class="cell value"><?php echo esc_html( get_post_time( mpa_date_format(), false, $payment->getId(), true ) ); ?></span>
					</div>
					<div class="payment-method mpa-booking-details-section-row">
						<span class="cell label"><?php esc_html_e( 'Method', 'motopress-appointment' ); ?>:</span>
						<span class="cell value"><?php echo esc_html( ! empty( $payment->getPaymentMethod() ) ? $payment->getPaymentMethod() : $payment->getGateway()->getPublicName() ); ?></span>
					</div>
					<div class="payment-amount mpa-booking-details-section-row">
						<span class="cell label"><?php esc_html_e( 'Amount', 'motopress-appointment' ); ?>:</span>
						<span class="cell value"><?php echo mpa_tmpl_price( $payment->getAmount(), array( 'literal_free' => false ) ); ?></span>
					</div>
					<?php
						do_action( 'mpa_shortcode_booking_details_payments_bottom' );
					?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php
		do_action( 'mpa_booking_details_payments_after_end', $booking );
	?>
</div>

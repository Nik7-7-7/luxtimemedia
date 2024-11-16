<div class="mpa-booking-details mpa-hide">
    <div class="mpa-booking-details-section booking-reservations">
        <div class="reservation">
            <div class="mpa-booking-details-section-row">
                <span class="cell reservation-title value"></span>
                <div class="cell reservation-full-date value">
                    <?php echo sprintf(
                        '%s &middot; %s',
                        '<span class="reservation-date"></span>',
                        '<span class="reservation-time"></span>'
                    ); ?>
                </div>
            </div>

            <div class="reservation-calendar-links mpa-booking-details-section-row">
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
            </div>
        </div>
    </div>
	<?php
		do_action( 'mpa_booking_details_section_after_end' );
	?>
</div>
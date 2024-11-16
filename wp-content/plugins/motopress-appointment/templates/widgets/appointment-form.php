<?php

/**
 * @since 1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array $widgetArgs Saved widget parameters from the database.
 *
 * @hooked WidgetsView::appointmentFormStepServiceForm() - 10
 * @hooked WidgetsView::appointmentFormStepPeriod()      - 20
 * @hooked WidgetsView::appointmentFormStepCart()        - 30
 * @hooked WidgetsView::appointmentFormStepCheckout()    - 40
 * @hooked WidgetsView::appointmentFormStepPayment()     - 50
 * @hooked WidgetsView::appointmentFormStepBooking()     - 60
 *
 * @since 1.3
 */
do_action( 'appointment_form_widget_steps', $template_args );

?>
<p class="mpa-message mpa-error mpa-hide"></p>
<div class="mpa-loading"></div>

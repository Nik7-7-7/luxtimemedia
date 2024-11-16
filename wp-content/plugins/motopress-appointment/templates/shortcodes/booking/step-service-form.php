<?php

/**
 * @param string $form_title       Optional. '' by default.
 * @param bool   $show_category    Optional. True by default.
 * @param bool   $show_service     Optional. True by default.
 * @param bool   $show_location    Optional. True by default.
 * @param bool   $show_employee    Optional. True by default.
 * @param string $default_category Optional. '' by default.
 * @param int    $default_service  Optional. 0 by default.
 * @param int    $default_location Optional. 0 by default.
 * @param int    $default_employee Optional. 0 by default.
 * @param string $label_category   Optional. '' ('Service Category') by default.
 * @param string $label_service    Optional. '' ('Service') by default.
 * @param string $label_location   Optional. '' ('Location') by default.
 * @param string $label_employee   Optional. '' ('Employee') by default.
 * @param string $label_unselected Optional. '' ('— Select —') by default.
 * @param string $label_option     Optional. '' ('— Any —') by default.
 * @param string $html_id          Optional. Unique ID of the shortcode instance.
 *
 * @since 1.0
 * @since 1.5.0 added the <code>$form_title</code> argument.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize args
extract(
	array(
		'form_title'       => '',

		'show_category'    => true,
		'show_service'     => true,
		'show_location'    => true,
		'show_employee'    => true,

		'default_category' => '',
		'default_service'  => 0,
		'default_location' => 0,
		'default_employee' => 0,

		'label_category'   => '',
		'label_service'    => '',
		'label_location'   => '',

		'label_unselected' => '',
		'label_option'     => '',

		'html_id'          => '',
	),
	EXTR_SKIP
);

if ( empty( $html_id ) ) {
	// Shortcode may pass empty string as html_id
	$html_id = uniqid();
}

// Display template
?>
<div class="mpa-booking-step mpa-booking-step-service-form mpa-hide">
	<form class="mpa-service-form" method="POST" action="">
		<?php if ( ! empty( $form_title ) ) { ?>
			<p class="mpa-shortcode-title">
				<?php echo esc_html( $form_title ); ?>
			</p>
		<?php } ?>

		<?php if ( $show_category || $default_category ) { ?>
			<p class="mpa-input-wrapper mpa-service-category-wrapper">
				<label for="mpa-service-category-<?php echo esc_attr( $html_id ); ?>">
					<?php
					if ( empty( $label_category ) ) {
						esc_html_e( 'Service Category', 'motopress-appointment' );
					} else {
						echo esc_html( $label_category );
					}
					?>
				</label>

				<?php
				echo mpa_tmpl_select(
					mpa_any_value( '', $label_option ),
					'',
					array(
						'id'             => 'mpa-service-category-' . $html_id,
						'class'          => 'mpa-service-category mpa-optional-select',
						'data-default'   => $default_category,
						'data-is-hidden' => mpa_bool_to_str( ! $show_category ),
					)
				);
				?>
			</p>
		<?php } ?>

		<p class="mpa-input-wrapper mpa-service-wrapper">
			<label for="mpa-service-<?php echo esc_attr( $html_id ); ?>">
				<?php
				if ( empty( $label_service ) ) {
					esc_html_e( 'Service', 'motopress-appointment' );
				} else {
					echo esc_html( $label_service );
				}
				?>
				<?php echo mpa_tmpl_required(); ?>
			</label>

			<?php
			// 0 is valid form value, so use '' instead
			echo mpa_tmpl_select(
				mpa_no_value( '', $label_unselected ),
				'',
				array(
					'id'             => 'mpa-service-' . $html_id,
					'class'          => 'mpa-service',
					'data-default'   => $default_service,
					'data-is-hidden' => mpa_bool_to_str( ! $show_service ),

					// Require field in StepServiceForm.js, otherwise will be
					// problems saving reservations on Edit Booking page
					// 'required'  => 'required',
				)
			);
			?>
		</p>

		<?php if ( $show_location || $default_location ) { ?>
			<p class="mpa-input-wrapper mpa-location-wrapper">
				<label for="mpa-location-<?php echo esc_attr( $html_id ); ?>">
					<?php
					if ( empty( $label_location ) ) {
						esc_html_e( 'Location', 'motopress-appointment' );
					} else {
						echo esc_html( $label_location );
					}
					?>
				</label>

				<?php
				$select_location_atts = array(
					'id'             => 'mpa-location-' . $html_id,
					'class'          => 'mpa-location mpa-optional-select',
					'data-default'   => $default_location,
					'data-is-hidden' => mpa_bool_to_str( ! $show_location ),
				);

				if ( ! $show_location ) {
					$select_location_atts['disabled'] = true;
				}

				echo mpa_tmpl_select(
					mpa_any_value( 0, $label_option ),
					0,
					$select_location_atts
				);
				?>
            </p>
		<?php } ?>

		<?php if ( $show_employee || $default_employee ) { ?>
			<p class="mpa-input-wrapper mpa-employee-wrapper">
				<label for="mpa-employee-<?php echo esc_attr( $html_id ); ?>">
					<?php
					if ( empty( $label_employee ) ) {
						esc_html_e( 'Employee', 'motopress-appointment' );
					} else {
						echo esc_html( $label_employee );
					}
					?>
				</label>

				<?php
				echo mpa_tmpl_select(
					mpa_any_value( 0, $label_option ),
					0,
					array(
						'id'             => 'mpa-employee-' . $html_id,
						'class'          => 'mpa-employee mpa-optional-select',
						'data-default'   => $default_employee,
						'data-is-hidden' => mpa_bool_to_str( ! $show_employee ),
					)
				);
				?>
			</p>
		<?php } ?>

		<p class="mpa-actions">
			<?php
			echo mpa_tmpl_button(
				esc_html__( 'Next', 'motopress-appointment' ),
				array(
					'class'    => 'button button-primary mpa-button-next',
					'type'     => 'button',
					'disabled' => 'disabled',
				)
			);
			?>
		</p>
	</form>

	<div class="mpa-loading"></div>
</div>

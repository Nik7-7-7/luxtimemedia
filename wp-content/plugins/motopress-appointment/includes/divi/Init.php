<?php

namespace MotoPress\Appointment\Divi;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.3
 */
class Init {

	public function __construct() {

		add_action( 'et_builder_modules_loaded', array( $this, 'registerDiviModules' ), 15 );
		add_action( 'et_builder_ready', array( $this, 'enqueueDiviAssets' ), 15 );

		if ( function_exists( 'et_get_option' ) ) {
			add_action( 'wp_head', array( $this, 'diviCustomizerCss' ), 15 );
		}
	}

	public function registerDiviModules() {

		new Modules\AppointmentFormModule();
		new Modules\EmployeesListModule();
		new Modules\LocationsListModule();
		new Modules\ServiceCategoriesModule();
		new Modules\ServicesListModule();
		new Modules\EmployeeImageModule();
		new Modules\EmployeeTitleModule();
		new Modules\EmployeeServicesListModule();
		new Modules\EmployeeScheduleModule();
		new Modules\EmployeeContentModule();
		new Modules\EmployeeContactsModule();
		new Modules\EmployeeSocialNetworksModule();
		new Modules\EmployeeAdditionalInfoModule();
	}

	public function enqueueDiviAssets() {
		if ( is_admin() || ( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) ) {
			wp_enqueue_script( 'mpa-public' );
			wp_enqueue_style( 'mpa-public' );
			wp_enqueue_script( 'mpa-divi-modules' );
		}
	}

	public function diviCustomizerCss() {

		$button_text_size           = absint( et_get_option( 'all_buttons_font_size', '20' ) );
		$button_text_color          = et_get_option( 'all_buttons_text_color', '#ffffff' );
		$button_bg_color            = et_get_option( 'all_buttons_bg_color', 'rgba(0,0,0,0)' );
		$button_border_width        = absint( et_get_option( 'all_buttons_border_width', '2' ) );
		$button_border_color        = et_get_option( 'all_buttons_border_color', '#ffffff' );
		$button_border_radius       = absint( et_get_option( 'all_buttons_border_radius', '3' ) );
		$button_text_style          = et_get_option( 'all_buttons_font_style', '', '', true );
		$button_spacing             = intval( et_get_option( 'all_buttons_spacing', '0' ) );
		$button_text_color_hover    = et_get_option( 'all_buttons_text_color_hover', '#ffffff' );
		$button_bg_color_hover      = et_get_option( 'all_buttons_bg_color_hover', 'rgba(255,255,255,0.2)' );
		$button_border_color_hover  = et_get_option( 'all_buttons_border_color_hover', 'rgba(0,0,0,0)' );
		$button_border_radius_hover = absint( et_get_option( 'all_buttons_border_radius_hover', '3' ) );
		$button_spacing_hover       = intval( et_get_option( 'all_buttons_spacing_hover', '0' ) );

		?>
			<style id="mpa-divi-customizer-css">
				.mpa-shortcode .button {
					<?php if ( 20 !== $button_text_size ) : ?>
						font-size: <?php echo $button_text_size; ?>px;
					<?php endif; ?>
					<?php if ( '#ffffff' !== $button_text_color ) : ?>
						color: <?php echo $button_text_color; ?>;
					<?php endif; ?>
					<?php if ( 'rgba(0,0,0,0)' !== $button_bg_color ) : ?>
						background: <?php echo $button_bg_color; ?>;
					<?php endif; ?>
					<?php if ( 2 !== $button_border_width ) : ?>
						border-width: <?php echo $button_border_width; ?>px !important;
					<?php endif; ?>
					<?php if ( '#ffffff' !== $button_border_color ) : ?>
						border-color: <?php echo $button_border_color; ?>;
					<?php endif; ?>
					<?php if ( 3 !== $button_border_radius ) : ?>
						border-radius: <?php echo $button_border_radius; ?>px;
					<?php endif; ?>
					<?php if ( '' !== $button_text_style ) : ?>
						<?php echo esc_html( et_pb_print_font_style( $button_text_style ) ); ?>;
					<?php endif; ?>
					<?php if ( 0 !== $button_spacing ) : ?>
						letter-spacing: <?php echo $button_spacing; ?>px;
					<?php endif; ?>
                    <?php if ( function_exists( 'et_pb_get_specific_default_font' ) && et_pb_get_specific_default_font( et_get_option( 'all_buttons_font', 'none' ) ) != 'none' ) : ?>
						font-family: <?php echo sanitize_text_field( et_pb_get_specific_default_font( et_get_option( 'all_buttons_font', 'none' ) ) ); ?>, sans-serif;
					<?php endif; ?>
				}

				.mpa-shortcode .button:hover {
					<?php if ( '#ffffff' !== $button_text_color_hover ) : ?>
						color: <?php echo $button_text_color_hover; ?>;
					<?php endif; ?>
					<?php if ( 'rgba(255,255,255,0.2)' !== $button_bg_color_hover ) : ?>
						background: <?php echo $button_bg_color_hover; ?>;
					<?php endif; ?>
					<?php if ( '#ffffff' !== $button_border_color_hover ) : ?>
						border-color: <?php echo $button_border_color_hover; ?>;
					<?php endif; ?>
					<?php if ( 3 !== $button_border_radius_hover ) : ?>
						border-radius: <?php echo $button_border_radius_hover; ?>px;
					<?php endif; ?>
					<?php if ( 0 !== $button_spacing_hover ) : ?>
						letter-spacing: <?php echo $button_spacing_hover; ?>px;
					<?php endif; ?>
				}
			</style>
		<?php
	}
}

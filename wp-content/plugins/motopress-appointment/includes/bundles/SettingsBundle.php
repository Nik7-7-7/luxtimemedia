<?php

namespace MotoPress\Appointment\Bundles;

use MotoPress\Appointment\ListTables\Emails\AdminEmailsListTable;
use MotoPress\Appointment\ListTables\Emails\CustomerEmailsListTable;
use MotoPress\Appointment\ListTables\Emails\TemplatePartsListTable;
use MotoPress\Appointment\ListTables\Payments\PaymentsListTable;
use MotoPress\Appointment\Entities\Notification;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class SettingsBundle {

	/**
	 * @var array [ tab id => tab label ]
	 *
	 * @since 1.0
	 */
	protected $settingsTabs = array();

	/**
	 * @var array [Category => Fields]
	 *
	 * @since 1.0
	 */
	protected $settings = array();

	/**
	 * @return array [ tab id => tab label ]
	 *
	 * @since 1.0
	 */
	public function getSettingsTabs(): array {

		if ( empty( $this->settingsTabs ) ) {

			$settingsTabs = array(
				'general'      => __( 'General', 'motopress-appointment' ),
				'email'        => __( 'Emails', 'motopress-appointment' ),
				'notification' => __( 'Notifications', 'motopress-appointment' ),
				'payment'      => __( 'Payments', 'motopress-appointment' ),
				'integrations' => __( 'Integrations', 'motopress-appointment' ),
				'misc'         => __( 'Misc', 'motopress-appointment' ),
			);

			if ( mpapp()->settings()->isLicenseEnabled() ) {

				$settingsTabs['license'] = __( 'License', 'motopress-appointment' );
			}

			/** @since 1.17.0 */
			$settingsTabs = apply_filters( 'mpa_settings_tabs', $settingsTabs );

			$this->settingsTabs = $settingsTabs;
		}

		return $this->settingsTabs;
	}

	/**
	 * 	Return array of all available subtabs(sections) for all tabs.
	 *
	 * @return array
	 */

	public function getTabsSections(): array {
		return array(
			'misc' => array(
				'styles' => __( 'Styles', 'motopress-appointment' )
			),
			'integrations' => array(
				'google_cal_sync' => __( 'Google Calendar Sync', 'motopress-appointment' )
			)
		);
	}

	/**
	 * @param string $settingsTabId
	 * @param string $currentSection
	 * @return array Settings tab fields.
	 *
	 * @since 1.0
	 */
	public function getSettings( $settingsTabId, $currentSection = '' ): array {

		if ( ! array_key_exists( $settingsTabId, $this->settings ) ) {

			switch ( $settingsTabId ) {

				case 'general':
					$this->settings[ $settingsTabId ] = $this->getGeneralSettingsTabFields();
					break;

				case 'email':
					$this->settings[ $settingsTabId ] = $this->getEmailSettingsTabFields();
					break;

				case 'notification':
					$this->settings[ $settingsTabId ] = $this->getNotificationsSettingsTabFields();
					break;

				case 'payment':
					$this->settings[ $settingsTabId ] = $this->getPaymentSettingsTabFields();
					break;

				case 'integrations':
					$this->settings[ $settingsTabId ] = $this->getIntegrationsSettingsTabFields();
					break;

				case 'license':
					$this->settings[ $settingsTabId ] = $this->getLicenseSettingsTabFields();
					break;

				case 'misc':
					$this->settings[ $settingsTabId ] = $this->getMiscSettingsTabFields( $currentSection );
					break;

				default:
					$this->settings[ $settingsTabId ] = array();
					break;
			}

			/** @since 1.0 */
			$this->settings[ $settingsTabId ] = apply_filters( "mpa_{$settingsTabId}_settings", $this->settings[ $settingsTabId ] );
		}

		return $this->settings[ $settingsTabId ];
	}

	private function getGeneralSettingsTabFields(): array {

		return array(
			// General setting
			'default_time_step'               => array(
				'type'        => 'number',
				'label'       => esc_html__( 'Default Time Step', 'motopress-appointment' ),
				'description' => esc_html__( 'Default length of the time slot step, which is used for the appointment durations.', 'motopress-appointment' ),
				'min'         => 1,
				'default'     => 30,
				'size'        => 'small',
			),
			'confirmation_mode'               => array(
				'type'    => 'radio',
				'label'   => esc_html__( 'Confirmation Mode', 'motopress-appointment' ),
				'options' => array(
					'auto'    => esc_html__( 'Confirm automatically', 'motopress-appointment' ),
					'manual'  => esc_html__( 'By admin manually', 'motopress-appointment' ),
					'payment' => esc_html__( 'Confirmation upon payment', 'motopress-appointment' ),
				),
				'default' => 'auto',
			),
			'terms_page_id_for_acceptance'    => array(
				'type'        => 'page-select',
				'label'       => esc_html__( 'Terms & Conditions', 'motopress-appointment' ),
				'description' => esc_html__( 'If you set a "Terms" page, the customer\'s consent is required at checkout.', 'motopress-appointment' ),
				'size'        => 'regular',
			),
			'allow_multibooking'              => array(
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Allow Multibooking', 'motopress-appointment' ),
				'label2'  => esc_html__( 'Allow clients to book more than one service at a time.', 'motopress-appointment' ),
				'default' => false,
			),
			'allow_coupons'                   => array(
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Allow Coupons', 'motopress-appointment' ),
				'label2'  => esc_html__( 'Enable the use of coupons.', 'motopress-appointment' ),
				'default' => false,
			),
			'user_can_cancel_booking'         => array(
				'type'        => 'checkbox',
				'label'       => esc_html__( 'Booking Cancelation', 'motopress-appointment' ),
				'label2'      => esc_html__( 'Customer can cancel a booking via the link provided inside their email.', 'motopress-appointment' ),
				'description' => sprintf(
				// translators: %s is the {cancelation_details} tag
					esc_html__( 'A customer cancelation email template must contain the %s tag.', 'motopress-appointment' ),
					'{cancelation_details}'
				),
				'default'     => false,
			),
			'booking_cancellation_page'       => array(
				'type'        => 'page-select',
				'label'       => esc_html__( 'Booking Cancelation Page', 'motopress-appointment' ),
				'description' => esc_html__( 'Page where the customer can confirm their booking cancelation. Requires [mpa_booking_details] and [mpa_direct_link_booking_cancellation_link] shortcodes.', 'motopress-appointment' ),
				'size'        => 'regular',
			),
			'booking_cancelled_page'          => array(
				'type'        => 'page-select',
				'label'       => esc_html__( 'Booking Canceled Page', 'motopress-appointment' ),
				'description' => esc_html__( 'Page to redirect the customer to after their booking is canceled.', 'motopress-appointment' ),
				'size'        => 'regular',
			),

			// Customer account settings
			'customer_account_creation_mode' => array(
				'type'        => 'select',
				'label'       => esc_html__( 'Customer Account Creation', 'motopress-appointment' ),
				'description' => esc_html__( 'Allow customers to create an account during checkout.', 'motopress-appointment' ),
				'options'     => array(
					'account_creation_disabled'  => esc_html__( 'Do not create automatically', 'motopress-appointment' ),
					'create_by_customer_request' => esc_html__( 'Enable customers to opt in', 'motopress-appointment' ),
					'create_automatically'       => esc_html__( 'Create automatically', 'motopress-appointment' ),
				),
				'default'     => 'account_creation_disabled',
				'size'        => 'regular',
			),
			'customer_account_page'          => array(
				'type'        => 'page-select',
				'label'       => esc_html__( 'Customer Account Page', 'motopress-appointment' ),
				'description' => esc_html__( 'A page where customers can log in to view their own bookings.', 'motopress-appointment' ) .
				                 // translators: %s shortcode name
				                 ' ' . sprintf( esc_html__( 'Use the %s shortcode on this page.', 'motopress-appointment' ), '[mpa_customer_account]' ),
				'size'        => 'regular',
			),

			// Misc group
			'misc_settings'                   => array(
				'type'  => 'group',
				'label' => esc_html__( 'Misc', 'motopress-appointment' ),
			),
			'country'                         => array(
				'type'    => 'select',
				'label'   => esc_html__( 'Default Country of Residence', 'motopress-appointment' ),
				'options' => array( '' => esc_html__( '— Select —', 'motopress-appointment' ) )
				             + mpapp()->bundles()->countries()->getCountries(),
				'size'    => 'regular',
			),
			'currency'                        => array(
				'type'    => 'select',
				'label'   => esc_html__( 'Currency', 'motopress-appointment' ),
				'options' => mpapp()->bundles()->currencies()->getCurrencies(),
				'default' => 'EUR',
				'size'    => 'regular',
			),
			'currency_position'               => array(
				'type'    => 'select',
				'label'   => esc_html__( 'Currency Position', 'motopress-appointment' ),
				'options' => mpapp()->bundles()->currencies()->getPositions(),
				'default' => 'before',
				'size'    => 'regular',
			),
			'decimal_separator'               => array(
				'type'    => 'text',
				'label'   => esc_html__( 'Decimal Separator', 'motopress-appointment' ),
				'default' => '.',
				'size'    => 'small',
			),
			'thousand_separator'              => array(
				'type'    => 'text',
				'label'   => esc_html__( 'Thousand Separator', 'motopress-appointment' ),
				'default' => ',',
				'size'    => 'small',
			),
			'number_of_decimals'              => array(
				'type'    => 'number',
				'label'   => esc_html__( 'Number of Decimals', 'motopress-appointment' ),
				'default' => 2,
				'size'    => 'small',
			),
		);
	}

	private function getEmailSettingsTabFields(): array {

		return array(
			// Admin emails group
			'admin_emails_group'    => array(
				'type'  => 'group',
				'label' => esc_html__( 'Admin Emails', 'motopress-appointment' ),
			),
			'admin_emails'          => array(
				'type'       => 'list-table',
				'list_table' => new AdminEmailsListTable( 'admin_emails' ),
			),

			// Customer emails group
			'customer_emails_group' => array(
				'type'  => 'group',
				'label' => esc_html__( 'Customer Emails', 'motopress-appointment' ),
			),
			'customer_emails'       => array(
				'type'       => 'list-table',
				'list_table' => new CustomerEmailsListTable( 'customer_emails' ),
			),

			// Email sender group
			'email_sender_group'    => array(
				'type'  => 'group',
				'label' => esc_html__( 'Email Sender', 'motopress-appointment' ),
			),
			'admin_email'           => array(
				'type'        => 'email',
				'label'       => esc_html__( 'Administrator Email', 'motopress-appointment' ),
				'placeholder' => mpapp()->settings()->getDefaultAdminEmail(),
				'size'        => 'regular',
			),
			'from_email'            => array(
				'type'         => 'email',
				'label'        => esc_html__( 'From Email', 'motopress-appointment' ),
				'placeholder'  => mpapp()->settings()->getAdminEmail(),
				'size'         => 'regular',
				'translatable' => true,
			),
			'from_name'             => array(
				'type'         => 'text',
				'label'        => esc_html__( 'From Name', 'motopress-appointment' ),
				'placeholder'  => mpapp()->settings()->getDefaultFromName(),
				'size'         => 'regular',
				'translatable' => true,
			),

			// Email template group
			'email_template_group'  => array(
				'type'  => 'group',
				'label' => esc_html__( 'Email Template', 'motopress-appointment' ),
			),
			'email_logo_url'        => array(
				'type'         => 'text',
				'label'        => esc_html__( 'Logo URL', 'motopress-appointment' ),
				'description'  => esc_html__( 'URL to an image you want to show in the email header.', 'motopress-appointment' ),
				'placeholder'  => mpapp()->settings()->getDefaultEmailLogoUrl(),
				'size'         => 'large',
				'translatable' => true,
			),
			'email_footer_text'     => array(
				'type'         => 'textarea',
				'label'        => esc_html__( 'Footer Text', 'motopress-appointment' ),
				'description'  => esc_html__( 'Text applied to the footer of all emails.', 'motopress-appointment' ),
				'rows'         => 3,
				'size'         => 'regular',
				'default'      => mpapp()->settings()->getDefaultEmailFooterText(),
				'translatable' => true,
			),
			'email_template_parts'  => array(
				'type'       => 'list-table',
				'label'      => esc_html__( 'Template Parts', 'motopress-appointment' ),
				'list_table' => new TemplatePartsListTable( 'email_template_parts' ),
			),

			// Email styles group
			'email_styles_group'    => array(
				'type'  => 'group',
				'label' => esc_html__( 'Styles', 'motopress-appointment' ),
			),
			'email_base_color'      => array(
				'type'    => 'color-picker',
				'label'   => esc_html__( 'Base Color', 'motopress-appointment' ),
				'default' => '#557da1',
			),
			'email_bg_color'        => array(
				'type'    => 'color-picker',
				'label'   => esc_html__( 'Background Color', 'motopress-appointment' ),
				'default' => '#f5f5f5',
			),
			'email_body_bg_color'   => array(
				'type'    => 'color-picker',
				'label'   => esc_html__( 'Body Background Color', 'motopress-appointment' ),
				'default' => '#fdfdfd',
			),
			'email_text_color'      => array(
				'type'    => 'color-picker',
				'label'   => esc_html__( 'Body Text Color', 'motopress-appointment' ),
				'default' => '#505050',
			),
		);
	}


	private function getNotificationsSettingsTabFields(): array {

		$notificationSettings = array(
			'email_notifications_group' => array(
				'type'  => 'group',
				'label' => esc_html__( 'Email notifications', 'motopress-appointment' ),
			),
		);

		$notificationSettings = apply_filters( 'mpa_' . Notification::TYPE_ID_EMAIL . '_notification_settings', $notificationSettings );

		$smsNotificationSenders = array( '' => esc_html__( '— Select —', 'motopress-appointment' ) );
		$smsSenders             = mpapp()->getNotificationHandler()->getSMSNotificationSenders();

		foreach ( $smsSenders as $smsSender ) {

			$smsNotificationSenders[ $smsSender::getSenderId() ] = $smsSender::getSenderName();
		}

		$notificationSettings += array(
			'sms_notifications_group'    => array(
				'type'  => 'group',
				'label' => esc_html__( 'SMS notifications', 'motopress-appointment' ),
			),
			'sms_notification_sender_id' => array(
				'type'        => 'select',
				'label'       => esc_html__( 'SMS Sender', 'motopress-appointment' ),
				'description' => sprintf(
					wp_kses_post(
					// translators: %s - is a URL to the plugin's Extensions menu page
						__( 'To be able to send SMS notifications, you need to <a href="%s">install the extension</a> that integrates an SMS service.', 'motopress-appointment' )
					),
					esc_url( admin_url( 'admin.php?page=mpa_extensions' ) )
				),
				'options'     => $smsNotificationSenders,
				'default'     => '',
				'size'        => 'regular',
			),
			'admin_phone'                => array(
				'type'                   => 'phone',
				'label'                  => esc_html__( 'Admin Phone Number', 'motopress-appointment' ),
				'description'            => esc_html__( "It is used to send test SMS notifications as well as regular notifications with the Admin's number included in the recipients list.", 'motopress-appointment' ),
				'size'                   => 'regular',
			),
		);

		$notificationSettings = apply_filters( 'mpa_' . Notification::TYPE_ID_SMS . '_notification_settings', $notificationSettings );

		return $notificationSettings;
	}


	private function getPaymentSettingsTabFields(): array {

		$selectableGateways = array( '' => esc_html__( '— Select —', 'motopress-appointment' ) );

		foreach ( mpapp()->payments()->getEnabled() as $paymentGateway ) {
			$selectableGateways[ $paymentGateway->getId() ] = $paymentGateway->getName();
		}

		return array(
			// Payment gateways group
			'payment_gateways_group'  => array(
				'type'  => 'group',
				'label' => esc_html__( 'Payment Methods', 'motopress-appointment' ),
			),
			'payment_gateways'        => array(
				'type'       => 'list-table',
				'list_table' => new PaymentsListTable( 'payment_gateways' ),
			),

			// General group
			'general_settings_group'  => array(
				'type'  => 'group',
				'label' => esc_html__( 'General Settings', 'motopress-appointment' ),
			),
			'default_payment_gateway' => array(
				'type'        => 'select',
				'label'       => esc_html__( 'Default Method', 'motopress-appointment' ),
				'description' => esc_html__( 'Payment method that is pre-selected on checkout by default.', 'motopress-appointment' ),
				'options'     => $selectableGateways,
				'default'     => '',
				'size'        => 'regular',
			),
			'pending_payment_time'    => array(
				'type'        => 'number',
				'label'       => esc_html__( 'Time to Complete Payment', 'motopress-appointment' ),
				'description' => esc_html__( 'Period of time in minutes the user is given to complete payment. Unpaid bookings become Abandoned and the timeslot status changes to Available.', 'motopress-appointment' ),
				'min'         => 5,
				'default'     => 60,
				'size'        => 'small',
			),

			'payment_pages_group'     => array(
				'type'        => 'group',
				'label'       => esc_html__( 'Pages', 'motopress-appointment' ),
				'description' => esc_html__( 'Required only for payment methods that handle payments on their own pages.', 'motopress-appointment' ),
			),
			'payment_success_page'    => array(
				'type'        => 'page-select',
				'label'       => esc_html__( 'Reservation Received Page', 'motopress-appointment' ),
				'description' => esc_html__( 'Page to redirect the customer to after their payment is placed on the external payment platform. Requires the [mpa_reservation_received] shortcode.', 'motopress-appointment' ),
				'size'        => 'regular',

			),
			'payment_failed_page'     => array(
				'type'        => 'page-select',
				'label'       => esc_html__( 'Failed Transaction Page', 'motopress-appointment' ),
				'description' => esc_html__( 'Page to redirect the customer to on failed transaction.', 'motopress-appointment' ),
				'size'        => 'regular',
			),
		);
	}

	private function getIntegrationsSettingsTabFields(): array {

		return array(
			// Google Calendar Sync
			'google_calendar_sync_group'    => array(
				'type'        => 'group',
				'label'       => __( 'Google Calendar Sync', 'motopress-appointment' ),
				// @NOLITE-CODE-START
				'description' => wp_kses_post(
					sprintf(
					// translators: %s is the current website URL
						__( 'Set this redirect URL in <a href="https://console.developers.google.com/">Google Developers Console</a>: %s', 'motopress-appointment' ),
						get_site_url( null, '/mpa-google-calendar-sync/' )
					)
				),
				// @NOLITE-CODE-END
				
			),
			// @NOLITE-CODE-START
			'google_calendar_client_id'     => array(
				'type'         => 'text',
				'label'        => __( 'Client ID', 'motopress-appointment' ),
				'placeholder'  => mpapp()->settings()->getGoogleCalendarClientId(),
				'size'         => 'regular',
				'translatable' => true,
			),
			'google_calendar_client_secret' => array(
				'type'         => 'text',
				'label'        => __( 'Client Secret', 'motopress-appointment' ),
				'placeholder'  => mpapp()->settings()->getGoogleCalendarClientSecret(),
				'size'         => 'regular',
				'translatable' => true,
			),
			// @NOLITE-CODE-END
		);
	}

	private function getLicenseSettingsTabFields(): array {

		return array(
			'edd_license_key' => array(
				'type' => 'license-settings',
			),
		);
	}

	private function getMiscSettingsTabFields( $currentSection = '' ): array {

		// For now, the "Misc Settings" tab has only one section with settings.
		// If additional sections are added, a section slug check should be implemented.
		// Check getTabsSections method.

		return array(
			'from_styles_group'    => array(
				'type'        => 'group',
				'label'       => __( 'Default Form Styles', 'motopress-appointment' ),
				'description' => __( 'These default styles apply to all booking forms, but you can customize each one separately.', 'motopress-appointment' )
			),
			'form_calendar_theme' => array(
				'type'        => 'select',
				'label'       => esc_html__( 'Calendar Theme', 'motopress-appointment' ),
				'options'     => array(
					''                  => esc_html__( 'Default', 'motopress-appointment' ),
					'dark'              => esc_html__( 'Dark', 'motopress-appointment' ),
					'material_blue'     => esc_html__( 'Material Blue', 'motopress-appointment' ),
					'material_green'    => esc_html__( 'Material Green', 'motopress-appointment' ),
					'material_red'      => esc_html__( 'Material Red', 'motopress-appointment' ),
					'material_orange'   => esc_html__( 'Material Orange', 'motopress-appointment' ),
					'airbnb'            => esc_html__( 'Airbnb', 'motopress-appointment' ),
					'confetti'          => esc_html__( 'Confetti', 'motopress-appointment' ),
				),
				'default'     => '',
				'size'        => 'regular',
			),
			'form_styles' => array(
				'type'      => 'container',
				'fields'    => array(
					'form_width'            => array(
						'type'          => 'text',
						'label'         => esc_html__( 'Form Width', 'motopress-appointment' ),
						'placeholder'   => '50%',
						'description'   => __( 'Example: 100%', 'motopress-appointment' )
					),
					'primary_color'         => array(
						'type'              => 'color-picker',
						'label'             => esc_html__( 'Primary Text Color', 'motopress-appointment' ),
						'colorpicker_type'  => 'component'
					),
					'primary_bg_color'      => array(
						'type'              => 'color-picker',
						'label'             => esc_html__( 'Primary Background Color', 'motopress-appointment' ),
						'colorpicker_type'  => 'component'
					),
					'secondary_color'       => array(
						'type'              => 'color-picker',
						'label'             => esc_html__( 'Secondary Text Color', 'motopress-appointment' ),
						'colorpicker_type'  => 'component'
					),
					'secondary_bg_color'    => array(
						'type'              => 'color-picker',
						'label'             => esc_html__( 'Secondary Background Color', 'motopress-appointment' ),
						'colorpicker_type'  => 'component'
					),
					'buttons_padding'      => array(
						'type'          => 'text',
						'label'         => esc_html__( 'Buttons Padding', 'motopress-appointment' ),
						'description'   => __( 'Example: 5px 10px', 'motopress-appointment' )
					),
				)
			)
		);
	}
}

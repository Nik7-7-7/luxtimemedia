<?php

namespace MotoPress\Appointment\Plugin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
trait EmailSettings {

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getAdminEmail() {

		$wpAdminEmail  = $this->getDefaultAdminEmail();
		$appAdminEmail = get_option( 'mpa_admin_email', '' );

		return $appAdminEmail ? $appAdminEmail : $wpAdminEmail;
	}

	/**
	 * @return string WordPress admin email.
	 *
	 * @since 1.1.0
	 */
	public function getDefaultAdminEmail() {
		return get_bloginfo( 'admin_email' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getFromEmail() {

		$adminEmail = $this->getAdminEmail();
		$fromEmail  = get_option( 'mpa_from_email', '' );

		return $fromEmail ? $fromEmail : $adminEmail;
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getFromName() {
		$fromName = get_option( 'mpa_from_name', '' );

		if ( ! empty( $fromName ) ) {
			$fromName = mpa_translate_string( $fromName, 'mpa_from_name' );
		} else {
			$fromName = $this->getDefaultFromName();
		}

		return $fromName;
	}

	/**
	 * @return string Blog name.
	 *
	 * @since 1.1.0
	 */
	public function getDefaultFromName() {
		return get_bloginfo( 'name' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getEmailLogoUrl() {
		return get_option( 'mpa_email_logo_url', '' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getDefaultEmailLogoUrl() {
		/** @since 1.1.0 */
		return apply_filters( 'mpa_email_default_logo_url', '' );
	}

	/**
	 * @return bool
	 *
	 * @since 1.1.0
	 */
	public function hasEmailLogo() {
		return $this->getEmailLogoUrl() !== '';
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getEmailFooterText() {

		$text = get_option( 'mpa_email_footer_text', false );

		if ( false === $text ) {
			$text = $this->getDefaultEmailFooterText();
		} else {
			$text = mpa_translate_string( $text, 'mpa_email_footer_text' );
		}

		return $text;
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getDefaultEmailFooterText() {
		return esc_html__( '{site_link} &mdash; Built with {Appointment}', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getEmailBaseColor() {
		return get_option( 'mpa_email_base_color', '#557da1' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getEmailBackgroundColor() {
		return get_option( 'mpa_email_bg_color', '#f5f5f5' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getEmailBodyColor() {
		return get_option( 'mpa_email_body_bg_color', '#fdfdfd' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getEmailTextColor() {
		return get_option( 'mpa_email_text_color', '#505050' );
	}

	/**
	 * @return array
	 *
	 * @since 1.1.0
	 */
	public function getEmailSettings() {
		return array(
			'admin_email'         => $this->getAdminEmail(),
			'from_email'          => $this->getFromEmail(),
			'from_name'           => $this->getFromName(),
			'email_logo_url'      => $this->getEmailLogoUrl(),
			'email_footer_text'   => $this->getEmailFooterText(),
			'email_base_color'    => $this->getEmailBaseColor(),
			'email_bg_color'      => $this->getEmailBackgroundColor(),
			'email_body_bg_color' => $this->getEmailBodyColor(),
			'email_text_color'    => $this->getEmailTextColor(),
		);
	}
}

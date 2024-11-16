<?php

namespace MotoPress\Appointment\Emails;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class Mailer implements MailerInterface {

	/**
	 * @param string|array $to Array or comma-separated list of email addresses
	 *     to send message.
	 * @param string $subject
	 * @param string $message
	 * @param array|string $headers Optional. Additional headers.
	 * @param array|string $attachments Optional. Files to attach.
	 * @return bool
	 *
	 * @since 1.1.0
	 */
	public function send( $to, $subject, $message, $headers = '', $attachments = array() ) {
		add_filter( 'wp_mail_from', array( $this, 'filterFromEmail' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'filterFromName' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'filterContentType' ) );

		$result = wp_mail( $to, $subject, $message, $headers, $attachments );

		remove_filter( 'wp_mail_from', array( $this, 'filterFromEmail' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'filterFromName' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'filterContentType' ) );

		return $result;
	}

	/**
	 * Filters the from address for outgoing emails.
	 *
	 * @param string $fromEmail
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function filterFromEmail( $fromEmail ) {
		$fromEmail = mpapp()->settings()->getFromEmail();

		return sanitize_email( $fromEmail );
	}

	/**
	 * Filters the from name for outgoing emails.
	 *
	 * @param string $fromName
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function filterFromName( $fromName ) {
		$fromName = mpapp()->settings()->getFromName();

		return wp_specialchars_decode( esc_html( $fromName ), ENT_QUOTES );
	}

	/**
	 * Filters the email content type.
	 *
	 * @param string $contentType
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function filterContentType( $contentType ) {
		return 'text/html';
	}
}

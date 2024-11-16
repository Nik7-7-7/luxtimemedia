<?php

namespace MotoPress\Appointment\Emails;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
interface MailerInterface {

	/**
	 * @param string|array $to Array or comma-separated list of email addresses
	 *     to send message.
	 * @param string $subject
	 * @param string $message
	 * @param array|string|null $headers Optional. Additional headers. Null by
	 *     default.
	 * @param array|string $attachments Optional. Files to attach.
	 * @return bool
	 *
	 * @since 1.1.0
	 */
	public function send( $to, $subject, $message, $headers = null, $attachments = null);
}

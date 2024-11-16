<?php

namespace MotoPress\Appointment\Emails;

use MotoPress\Appointment\Emails\Tags\InterfaceTags;
use MotoPress\Appointment\Utils\Emogrifier;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
abstract class AbstractEmail {

	/**
	 * @var string Email ID, like 'mpa_admin_pending_booking_email' (or
	 *     "mpa_{$group}_{$shortName}_email").
	 *
	 * @since 1.1.0
	 */
	protected $id;

	/**
	 * @var InterfaceTags
	 *
	 * @since 1.1.0
	 */
	protected $tags;

	/**
	 * @var \MotoPress\Appointment\Entities\Booking|null
	 *
	 * @since 1.1.0
	 */
	protected $booking = null;

	/**
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->id   = mpa_prefix( $this->getName() );
		$this->tags = apply_filters( $this->id . '_tags', $this->initTags() );
	}

	abstract protected function initTags(): InterfaceTags;

	/**
	 * @return string Email name, like 'admin_pending_booking_email' (or
	 *     "{$group}_{$shortName}_email").
	 *
	 * @since 1.1.0
	 */
	abstract public function getName();

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	abstract public function getLabel();

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getDescription() {
		return '';
	}

	/**
	 * @return string Emails, separated by comma.
	 *
	 * @since 1.1.0
	 */
	public function getRecipients() {

		$defaultRecipients = $this->getDefaultRecipients();
		$customRecipients  = $this->getCustomRecipients();

		if ( ! empty( $defaultRecipients ) && ! empty( $customRecipients ) ) {
			return $defaultRecipients . ', ' . $customRecipients;
		} else {
			return $defaultRecipients . $customRecipients;
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getSubject() {

		// Get custom or default subject
		$subject = $this->getOption( 'subject', false );

		if ( false === $subject ) {
			$subject = $this->getDefaultSubject();
		}

		// Replace tags
		$subject = $this->replaceTags( $subject );

		return $subject;
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getHeader() {

		// Get custom or default header text
		$headerText = $this->getOption( 'header', false );

		if ( false === $headerText ) {
			$headerText = $this->getDefaultHeader();
		}

		// Replace tags
		$headerText = $this->replaceTags( $headerText );

		// Render header template
		$header = mpa_render_template( 'emails/header.php', array( 'headerText' => $headerText ) );

		return $header;
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getMessage() {

		// Get custom or default message
		$message = $this->getOption( 'message', false );

		if ( false === $message ) {
			$message = $this->getDefaultMessage();
		}

		// Replace tags
		$message = $this->replaceTags( $message );

		return $message;
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getFooter() {
		// Get custom or default footer text
		$footerText = mpapp()->settings()->getEmailFooterText();
		$footerText = $this->replaceTags( $footerText );

		// Render footer template
		$footer = mpa_render_template( 'emails/footer.php', array( 'footerText' => $footerText ) );

		return $footer;
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function render() {
		// Build the message first, so tags may add more styles to email head
		$message = $this->getMessage();

		// Render email
		$email  = $this->getHeader();
		$email .= $message;
		$email .= $this->getFooter();

		$email = stripslashes( $email );

		$email = $this->applyStyles( $email );

		return $email;
	}

	/**
	 * @param string $html
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function applyStyles( $html ) {

		// Make sure we only use inline CSS for HTML emails
		$styles = mpa_render_template( 'emails/styles.php', array( 'emailId' => $this->id ) );

		// Load polyfill for function mb_convert_encoding() for Emogrifier if
		// it's not exist. Emogrifier is bad in converting non-ASCII characters.
		// See MB-1023 for more details
		if ( ! function_exists( 'mb_convert_encoding' ) ) {
			mpa_load_polyfill( 'mbstring' );
		}

		// Apply inline styles
		$emogrifier     = new Emogrifier( $html, $styles );
		$htmlWithStyles = $emogrifier->emogrify();

		return $htmlWithStyles;
	}

	/**
	 * @param string $content
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function replaceTags( $content ) {
		/**
		 * mpa_email_pre_replace_tag_content
		 *
		 * @since 2.1.1
		 */
		$content = apply_filters('mpa_email_pre_replace_tag_content', $content);
		return $this->tags->replaceTags( $content );
	}

	/**
	 * @return bool
	 *
	 * @since 1.1.0
	 */
	public function isDisabled() {
		$isEnabled   = (bool) $this->getOption( 'enable', true );
		$isPrevented = (bool) apply_filters( 'mpa_prevent_' . $this->getName(), false );

		return ! $isEnabled || $isPrevented;
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public function getCustomRecipients() {
		return $this->getOption( 'recipients' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	abstract public function getDefaultRecipients();

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	abstract protected function getDefaultSubject();

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	abstract protected function getDefaultHeader();

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getDefaultMessage() {
		$template = $this->getMessageTemplate();
		$message  = mpa_render_template( $template );

		return $message;
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	abstract protected function getMessageTemplate();

	/**
	 * @return array
	 *
	 * @since 1.1.0
	 */
	public function getSettingsFields() {

		$name = $this->getName();

		$parentUrl = mpapp()->pages()->settings()->getUrl(
			array(
				'tab' => 'email',
			)
		);

		return array(
			"{$name}_group"      => array(
				'type'          => 'group',
				'label'         => $this->getLabel(),
				'description'   => $this->getDescription(),
				'title_actions' => array(
					$parentUrl => esc_html__( 'Back', 'motopress-appointment' ),
				),
			),
			"{$name}_enable"     => array(
				'type'    => 'checkbox',
				'label'   => esc_html__( 'Enable/Disable', 'motopress-appointment' ),
				'label2'  => esc_html__( 'Enable this email notification', 'motopress-appointment' ),
				'default' => true,
			),
			"{$name}_recipients" => array(
				'type'         => 'text',
				'label'        => esc_html__( 'Other Recipients', 'motopress-appointment' ),
				'description'  => esc_html__( 'You can use multiple comma-separated email addresses.', 'motopress-appointment' ),
				'size'         => 'large',
				'translatable' => true,
			),
			"{$name}_subject"    => array(
				'type'         => 'text',
				'label'        => esc_html__( 'Subject', 'motopress-appointment' ),
				'default'      => $this->getDefaultSubject(),
				'size'         => 'large',
				'translatable' => true,
			),
			"{$name}_header"     => array(
				'type'         => 'text',
				'label'        => esc_html__( 'Header', 'motopress-appointment' ),
				'default'      => $this->getDefaultHeader(),
				'size'         => 'large',
				'translatable' => true,
			),
			"{$name}_message"    => array(
				'type'         => 'rich-editor',
				'label'        => esc_html__( 'Message Template', 'motopress-appointment' ),
				'description'  => $this->tags->getDescription(),
				'rows'         => 25,
				'default'      => $this->getDefaultMessage(),
				'translatable' => true,
			),
		);
	}

	/**
	 * @param string $field
	 * @param mixed $default Optional. '' by default.
	 * @return mixed
	 *
	 * @since 1.1.0
	 */
	protected function getOption( $field, $default = '' ) {
		$optionName = mpa_prefix( $this->id . '_' . $field );

		$optionValue = get_option( $optionName, $default );
		$optionValue = mpa_translate_string( $optionValue, $optionName );

		return $optionValue;
	}

	/**
	 * @since 1.1.0
	 */
	public function getTags(): InterfaceTags {
		return $this->tags;
	}

	/**
	 * @return string
	 *
	 * @since 1.2.1
	 */
	public function getFilename() {
		// "admin-pending-booking-email"
		$filename = mpa_tmpl_id( $this->getName() );

		// "admin-pending-booking-email.php"
		$filename .= '.php';

		return $filename;
	}
}

<?php

namespace MotoPress\Appointment\AdminPages\Custom;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class HelpPage extends AbstractCustomPage {

	/**
	 * @since 1.2.1
	 */
	protected function enqueueScripts() {
		mpa_assets()->enqueueStyle( 'mpa-admin' );
	}

	/**
	 * @access protected
	 *
	 * @since 1.1.0
	 */
	public function display() {
		$shortcodesList = mpa_shortcodes()->getShortcodeDetails();

		echo '<div class="wrap">';
			mpa_display_template( 'private/pages/shortcodes-list.php', array( 'shortcodes' => $shortcodesList ) );
		echo '</div>';
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getPageTitle() {
		return esc_html__( 'Help', 'motopress-appointment' );
	}

	/**
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getMenuTitle() {
		return esc_html__( 'Help', 'motopress-appointment' );
	}
}

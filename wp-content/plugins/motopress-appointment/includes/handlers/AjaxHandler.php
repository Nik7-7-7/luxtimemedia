<?php

namespace MotoPress\Appointment\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AjaxHandler {

	const AJAX_ACTION_CLASS_NAMES = array(
		\MotoPress\Appointment\Handlers\AjaxActions\ExportBookingsAction::class,
	);

	private static function getAjaxActionClassNames() {

		/**
		 * Use this filter to add custom ajax actions in other plugins.
		 * if action in the Motopress Appointment plugin then it must be added
		 * to the AJAX_ACTION_CLASS_NAMES array above explicitly!
		 */
		return apply_filters( 'mpa_get_ajax_action_class_names', static::AJAX_ACTION_CLASS_NAMES );
	}

	public function __construct() {

		if ( ! wp_doing_ajax() ) {
			return;
		}

		foreach ( static::getAjaxActionClassNames() as $ajaxActionClassName ) {

			$ajaxActionName = $ajaxActionClassName::getAjaxActionName();

			if ( $ajaxActionClassName::isActionForLoggedInUser() ) {

				add_action( 'wp_ajax_' . $ajaxActionName, array( $ajaxActionClassName, 'processAjaxRequest' ) );
			}

			if ( $ajaxActionClassName::isActionForGuestUser() ) {

				add_action( 'wp_ajax_nopriv_' . $ajaxActionName, array( $ajaxActionClassName, 'processAjaxRequest' ) );
			}
		}
	}

	/**
	 * @param string[] $ajaxActionNames for which WP nonces will be generated.
	 * @return array of [ action name => wp nonce ]
	 */
	public static function getAjaxActionWPNonces( array $ajaxActionNames = array() ) {

		$wpNonces = array();

		if ( empty( $ajaxActionNames ) ) {
			return $wpNonces;
		}

		foreach ( static::getAjaxActionClassNames() as $ajaxActionClassName ) {

			$ajaxActionName = $ajaxActionClassName::getAjaxActionName();

			if ( in_array( $ajaxActionName, $ajaxActionNames, true ) &&
				(
					( is_user_logged_in() && $ajaxActionClassName::isActionForLoggedInUser() ) ||
					( ! is_user_logged_in() && $ajaxActionClassName::isActionForGuestUser() )
				)
			) {
				$wpNonces[ $ajaxActionName ] = wp_create_nonce( $ajaxActionName );
			}
		}

		return $wpNonces;
	}
}

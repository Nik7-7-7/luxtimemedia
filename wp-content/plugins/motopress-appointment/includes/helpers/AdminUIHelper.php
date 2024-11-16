<?php

declare(strict_types=1);

namespace MotoPress\Appointment\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class AdminUIHelper {

	const ADMIN_NOTICE_TYPE_SUCCESS = 'success';
	const ADMIN_NOTICE_TYPE_ERROR   = 'error';
	const ADMIN_NOTICE_TYPE_WARNING = 'warning';
	const ADMIN_NOTICE_TYPE_INFO    = 'info';

	const TRANSIENT_NAME_ADMIN_NOTICES = 'mpa_admin_notices';


	// this is helper with static functions only
	private function __construct() {}


	/**
	 * We need to collect admin notices during post save processing to be able to show them after
	 * because WordPress redirects user after save to the admin edit page.
	 * @param string $noticeType - one of constants in AdminUIHelper
	 */
	public static function addAdminNotice( string $noticeType, string $noticeText ) {

		$collectedAdminNotices = get_transient( static::TRANSIENT_NAME_ADMIN_NOTICES );

		if ( empty( $collectedAdminNotices ) ) {

			$collectedAdminNotices = array();
		}

		// prevent add notice several times because of ajax calls
		if ( empty( $collectedAdminNotices[ $noticeType ] ) ||
			! in_array( $noticeText, $collectedAdminNotices[ $noticeType ], true )
		) {

			$collectedAdminNotices[ $noticeType ][] = $noticeText;
		}

		set_transient( static::TRANSIENT_NAME_ADMIN_NOTICES, $collectedAdminNotices, 30 );
	}


	public static function echoCollectedAdminNotices() {

		$collectedAdminNotices = get_transient( static::TRANSIENT_NAME_ADMIN_NOTICES );

		if ( ! empty( $collectedAdminNotices ) ) {

			foreach ( $collectedAdminNotices as $noticeType => $notices ) {

				foreach ( $notices as $noticeText ) {

					static::echoAdminNotice( $noticeType, $noticeText );
				}
			}

			delete_transient( static::TRANSIENT_NAME_ADMIN_NOTICES );
		}
	}

	/**
	 * @param string $noticeType - one of constants in AdminUIHelper
	 */
	public static function echoAdminNotice( string $noticeType, string $noticeText ) {

		if ( static::ADMIN_NOTICE_TYPE_SUCCESS === $noticeType ) {

			echo '<div class="notice notice-success">
					<p>' . esc_html( $noticeText ) . '</p>
				</div>';

		} elseif ( static::ADMIN_NOTICE_TYPE_ERROR === $noticeType ) {

			echo '<div class="notice notice-error">
					<p>' . esc_html( $noticeText ) . '</p>
				</div>';

		} elseif ( static::ADMIN_NOTICE_TYPE_WARNING === $noticeType ) {

			echo '<div class="notice notice-warning">
					<p>' . esc_html( $noticeText ) . '</p>
				</div>';

		} else {

			echo '<div class="notice notice-info">
					<p>' . esc_html( $noticeText ) . '</p>
				</div>';
		}
	}
}

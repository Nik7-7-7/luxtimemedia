<?php

declare(strict_types=1);

namespace MotoPress\Appointment\PostTypes\Logs;

use stdClass;
use wpdb;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.14.0
 */
class CustomCommentsFix {

	public function __construct() {
		$this->addHooks();
	}

	protected function addHooks() {
		add_filter( 'wp_count_comments', array( $this, 'fixCommentsCount' ), 15, 2 );

		// Disable Hotel Booking handler. It will not take into account
		// appointment logs.
		if ( mpa_is_hotel_booking_active() ) {
			$bookingLogs = MPHB()->postTypes()->booking()->logs();

			remove_filter( 'wp_count_comments', array( $bookingLogs, 'fixCommentsCount' ), 11 );
		}
	}

	/**
	 * @access protected
	 *
	 * @param array|stdClass $stats
	 * @return array|stdClass
	 *
	 * @global wpdb $wpdb
	 */
	public function fixCommentsCount( $stats, int $postId ) {
		global $wpdb;

		if ( 0 == $postId ) {
			$stats = wp_cache_get( "comments-{$postId}", 'counts' );

			if ( false === $stats ) {
				$excludeCommentTypes   = $this->getCustomCommentTypes();
				$excludeCommentsString = '"' . implode( '", "', $excludeCommentTypes ) . '"';

				$newCounts = $wpdb->get_results( "SELECT comment_approved, COUNT(*) AS total FROM {$wpdb->comments} WHERE comment_type NOT IN ({$excludeCommentsString}) GROUP BY comment_approved", ARRAY_A );

				$stats = array(
					'approved'       => 0,
					'moderated'      => 0,
					'spam'           => 0,
					'trash'          => 0,
					'post-trashed'   => 0,
					'total_comments' => 0,
					'all'            => 0,
				);

				foreach ( $newCounts as $row ) {
					switch ( $row['comment_approved'] ) {
						case '1':
							$stats['approved']        = $row['total'];
							$stats['total_comments'] += $row['total'];
							$stats['all']            += $row['total'];
							break;

						case '0':
							$stats['moderated']       = $row['total'];
							$stats['total_comments'] += $row['total'];
							$stats['all']            += $row['total'];
							break;

						case 'spam':
							$stats['spam']            = $row['total'];
							$stats['total_comments'] += $row['total'];
							break;

						case 'trash':
							$stats['trash'] = $row['total'];
							break;

						case 'post-trashed':
							$stats['post-trashed'] = $row['total'];
							break;
					}
				} // For each stat

				$stats = (object) $stats;

				wp_cache_set( "comments-{$postId}", $stats, 'counts' );

			} // If $stats === false
		} // If $postId > 0

		return $stats;
	}

	/**
	 * @return string[]
	 */
	protected function getCustomCommentTypes(): array {

		$customCommentTypes = array(
			// Appointment Booking
			'mpa_booking_log',
			'mpa_payment_log',

			// Hotel Booking
			'mphb_booking_log',
			'mphb_payment_log', // Does not exist, but maybe in the future

			// WooCommerce
			'order_note',
			'webhook_delivery',

			// Easy Digital Downloads
			'edd_payment_note',
		);

		/**
		 * @since 1.14.0
		 *
		 * @param string[] $customCommentTypes
		 */
		$customCommentTypes = apply_filters( 'mpa_get_custom_comment_types', $customCommentTypes );

		return $customCommentTypes;
	}
}

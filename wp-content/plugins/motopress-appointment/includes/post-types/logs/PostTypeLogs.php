<?php

declare(strict_types=1);

namespace MotoPress\Appointment\PostTypes\Logs;

use WP_Comment;
use WP_Comment_Query;
use wpdb;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.14.0
 */
class PostTypeLogs {

	/**
	 * @var string
	 *
	 * @example 'mpa_booking'
	 */
	protected $postType = '';

	/**
	 * @var string Post type + '_log'.
	 *
	 * @example 'mpa_booking_log'
	 */
	protected $commentType = '';

	/**
	 * @var string Comment type without prefix.
	 *
	 * @example 'booking_log'
	 */
	protected $commentSlug = '';

	public function __construct( string $postType ) {
		$this->postType    = $postType;
		$this->commentType = $postType . '_log';
		$this->commentSlug = mpa_unprefix( $this->commentType );

		$this->addHooks();
	}

	protected function addHooks() {

		$this->startHidingFromComments();

		// RSS feed
		add_filter( 'comment_feed_where', array( $this, 'hideFromFeed' ) );

		// self::getLogs()
		add_action( "mpa_before_get_{$this->commentSlug}s", array( $this, 'stopHidingFromComments' ) );
		add_action( "mpa_after_get_{$this->commentSlug}s", array( $this, 'startHidingFromComments' ) );
	}

	/**
	 * @access protected
	 */
	public function startHidingFromComments() {
		add_action( 'pre_get_comments', array( $this, 'hideFromComments' ) );
	}

	/**
	 * @access protected
	 */
	public function stopHidingFromComments() {
		remove_action( 'pre_get_comments', array( $this, 'hideFromComments' ) );
	}

	/**
	 * @access protected
	 */
	public function hideFromComments( WP_Comment_Query $query ) {
		$typesNotIn = array();

		if ( ! empty( $query->query_vars['type__not_in'] ) ) {
			$typesNotIn = (array) $query->query_vars['type__not_in'];
		}

		$typesNotIn[] = $this->commentType;

		$query->query_vars['type__not_in'] = $typesNotIn;
	}

	/**
	 * @access protected
	 *
	 * @global wpdb $wpdb
	 */
	public function hideFromFeed( string $where ): string {
		global $wpdb;

		if ( ! empty( $where ) ) {
			$where .= ' AND ';
		}

		$where .= $wpdb->prepare( 'comment_type != %s', $this->commentType );

		return $where;
	}

	/**
	 * @param int|null $authorId Optional. Current logged in user ID by default.
	 * @return int|false The new comment's ID on success, false on failure.
	 */
	public function addLog( int $entityId, string $message, $authorId = null ) {
		if ( ! $authorId ) {
			$authorId = get_current_user_id();
		}

		return wp_insert_comment(
			array(
				'comment_content'  => $message,
				'comment_post_ID'  => $entityId,
				'comment_type'     => $this->commentType,
				'comment_date'     => current_time( 'mysql' ),
				'comment_date_gmt' => current_time( 'mysql', get_option( 'gmt_offset' ) ),
				'comment_approved' => 1,
				'user_id'          => $authorId,
			)
		);
	}

	/**
	 * @return WP_Comment[] Logs in descending order.
	 */
	public function getLogs( int $entityId ): array {
		/**
		 * @since 1.14.0
		 */
		do_action( "mpa_before_get_{$this->commentSlug}s" );

		$comments = get_comments(
			array(
				'type'    => $this->commentType,
				'post_id' => $entityId,
				'orderby' => array( 'comment_date_gmt', 'comment_ID' ),
				'order'   => 'DESC',
			)
		);

		/**
		 * @since 1.14.0
		 */
		do_action( "mpa_after_get_{$this->commentSlug}s" );

		return $comments;
	}
}

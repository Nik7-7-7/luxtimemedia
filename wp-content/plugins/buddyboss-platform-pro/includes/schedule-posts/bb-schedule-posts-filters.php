<?php
/**
 * Schedule posts filters.
 *
 * @since   2.5.20
 *
 * @package BuddyBossPro
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Filters for the REST settings.
add_filter( 'bp_rest_platform_settings', 'bb_rest_schedule_posts_platform_settings' );
add_filter( 'bb_is_enabled_activity_schedule_posts', 'bb_is_enabled_activity_schedule_posts_filter', 999 );
add_filter( 'bp_core_get_js_strings', 'bb_schedule_posts_localize_scripts', 11 );
add_filter( 'bb_is_enabled_activity_schedule_posts', 'bb_is_enabled_activity_schedule_posts_admin_only', PHP_INT_MAX );

/**
 * Add schedule posts settings into API.
 *
 * @since 2.5.20
 *
 * @param array $settings Array settings.
 *
 * @return array Array of settings.
 */
function bb_rest_schedule_posts_platform_settings( $settings ) {

	if (
		! function_exists( 'bp_is_active' ) ||
		! bp_is_active( 'activity' ) ||
		! function_exists( 'bb_is_enabled_activity_schedule_posts' )
	) {
		return $settings;
	}

	$settings['bb_enable_activity_schedule_post'] = bb_is_enabled_activity_schedule_posts();

	return $settings;
}

/**
 * Filter to check platform pro active with valid license for scheduled posts.
 *
 * @since 2.5.20
 *
 * @return bool $value Filtered schedule posts setting value.
 */
function bb_is_enabled_activity_schedule_posts_filter() {

	// Return false if platform pro has not valid license.
	if (
		! bbp_pro_is_license_valid() ||
		version_compare( bb_platform_pro()->version, '2.5.20', '<' )
	) {
		return false;
	}

	return (bool) bp_get_option( '_bb_enable_activity_schedule_posts', false );
}

/**
 * Localize the strings needed for the schedule posts.
 *
 * @since 2.5.20
 *
 * @param array $params Associative array containing the js strings needed by scripts.
 *
 * @return array The same array with specific strings for the schedule posts if needed.
 */
function bb_schedule_posts_localize_scripts( $params ) {

	if ( ! bb_is_enabled_activity_schedule_posts() ) {
		return $params;
	}

	$activity_params = array(
		'scheduled_post_nonce'   => wp_create_nonce( 'scheduled_post_nonce' ),
		'scheduled_post_enabled' => function_exists( 'bb_is_enabled_activity_schedule_posts' ) && bb_is_enabled_activity_schedule_posts(),
		'can_schedule_in_feed'   => bb_can_user_schedule_activity(),
	);

	$activity_strings = array(
		'schedulePostButton'        => esc_html__( 'Schedule', 'buddyboss-pro' ),
		'confirmDeletePost'         => esc_html__( 'Are you sure you want to delete that permanently?', 'buddyboss-pro' ),
		'scheduleWarning'           => esc_html__( 'Schedule Outdated', 'buddyboss-pro' ),
		'successDeletionTitle'      => esc_html__( 'Scheduled Post Deleted', 'buddyboss-pro' ),
		'successDeletionDesc'       => esc_html__( 'Your scheduled post has been deleted.', 'buddyboss-pro' ),
		'successScheduleTitle'      => esc_html__( 'Successfully Scheduled Post', 'buddyboss-pro' ),
		'successScheduleDesc'       => esc_html__( 'Your post has been scheduled.', 'buddyboss-pro' ),
		'EditSuccessScheduleTitle'  => esc_html__( 'Successfully Updated Post', 'buddyboss-pro' ),
		'EditSuccessScheduleDesc'   => esc_html__( 'Your post schedule has been updated.', 'buddyboss-pro' ),
		'EditViewSchedulePost'      => esc_html__( 'View now', 'buddyboss-pro' ),
		'viewSchedulePosts'         => esc_html__( 'View all posts', 'buddyboss-pro' ),
		'activity_schedule_enabled' => function_exists( 'bb_is_enabled_activity_schedule_posts' ) && bb_is_enabled_activity_schedule_posts(),
		'notAllowScheduleWarning'   => esc_html__( 'Unable to schedule post as you are not the owner or moderator of this group', 'buddyboss-pro' ),
	);

	if ( ! empty( $params['activity_schedule']['params'] ) ) {
		$params['activity_schedule']['params'] = array_merge( $params['activity_schedule']['params'], $activity_params );
	} else {
		$params['activity_schedule']['params'] = $activity_params;
	}

	if ( ! empty( $params['activity_schedule']['strings'] ) ) {
		$params['activity_schedule']['strings'] = array_merge( $params['activity_schedule']['strings'], $activity_strings );
	} else {
		$params['activity_schedule']['strings'] = $activity_strings;
	}

	return $params;
}

/**
 * Function to allow always schedule post for admin.
 *
 * @since 2.5.21
 *
 * @param bool $retval Value of schedule post.
 *
 * @return bool
 */
function bb_is_enabled_activity_schedule_posts_admin_only( $retval ) {

	if ( bp_current_user_can( 'administrator' ) ) {
		return true;
	}

	return $retval;
}

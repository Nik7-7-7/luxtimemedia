<?php
/**
 * Schedule Posts Actions.
 *
 * @since   2.5.20
 *
 * @package BuddyBossPro
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


add_action( 'bp_nouveau_enqueue_scripts', 'bb_schedule_post_enqueue_scripts' );

/**
 * Enqueue the scripts for schedule posts.
 *
 * @since 2.5.20
 *
 * @return void
 */
function bb_schedule_post_enqueue_scripts() {
	if (
		(
			! bp_is_activity_component() &&
			! bp_is_group_activity()
		) ||
		! bb_is_enabled_activity_schedule_posts()
	) {
		return;
	}

	if ( bp_nouveau_current_user_can( 'publish_activity' ) ) {
		wp_enqueue_style( 'jquery-datetimepicker' );
		wp_enqueue_script( 'jquery-datetimepicker' );
	}

}

<?php
/**
 * Review Notice
 *
 * @package JITSI_MEET_WP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<p>
<?php
esc_html_e( 'Hi there, it seems like Jitsi Meet WP is bringing you some value, and that is pretty awesome!
    Can you please show us some love and rate Jitsi Meet WP on WordPress? It will take two minutes of your time, and will really help us spread the world.', 'jitsi-meet-wp' );
?>
</p>

<div class="notice-actions">
	<a class="hide_notice" data-value="hide_notice"
	href="https://wordpress.org/support/plugin/webinar-and-video-conference-with-jitsi-meet/reviews/#new-post" target="_blank">
	<?php
	esc_html_e( "I'd love to help :)", 'jitsi-meet-wp' );
	?>
	</a>
	<a href="#" class="remind_later"><?php esc_html_e( 'Not this time', 'jitsi-meet-wp' ); ?></a>
	<a href="#" class="hide_notice"
	data-value="hide_notice"><?php esc_html_e( 'I\'ve already rated you', 'jitsi-meet-wp' ); ?></a>
</div>

<div class="notice-overlay-wrap">
	<div class="notice-overlay">
		<h4><?php esc_html_e( 'Would you like us to remind you about this later?', 'jitsi-meet-wp' ); ?></h4>

		<div class="notice-overlay-actions">
			<a href="#" data-value="3"><?php esc_html_e( 'Remind me in 3 days', 'jitsi-meet-wp' ); ?></a>
			<a href="#" data-value="10"><?php esc_html_e( 'Remind me in 10 days', 'jitsi-meet-wp' ); ?></a>
			<a href="#"
			data-value="hide_notice"><?php esc_html_e( 'Don\'t remind me about this', 'jitsi-meet-wp' ); ?></a>
		</div>

		<button type="button" class="close-notice">&times;</button>
	</div>
</div>

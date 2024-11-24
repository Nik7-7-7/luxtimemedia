<?php
/**
 * Affiliate Notice
 *
 * @package JITSI_MEET_WP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p>
<?php echo wp_kses_post( wp_sprintf( 'Hey! Enjoying <b>Jitsi Meet WP </b> plugin? Join our <a href="https://go.wppool.dev/8dIc" target="_blank" class="affiliate-notice-link">influencer program  </a> to make money from your social media content. You can also check our <a href="https://go.wppool.dev/edUn" target="_blank" class="affiliate-notice-link">affiliate program  </a> to get a 25%% commission on every sale!', '' ) ); ?>
</p>

<div class="notice-actions">
	<a class="hide_notice button button-primary" data-value="hide_notice" href="https://go.wppool.dev/8dIc" target="_blank">
		<?php esc_html_e( 'Tell me more ', 'jitsi-meet-wp' ); ?> 
		<i class="dashicons dashicons-arrow-right-alt"></i></a>

	<span class="dashicons dashicons-dismiss"></span>

</div>

<div class="notice-overlay-wrap">
	<div class="notice-overlay">
		<h4><?php esc_html_e( 'Would you like us to remind you about this later?', 'jitsi-meet-wp' ); ?></h4>

		<div class="notice-overlay-actions">
			<a href="#" data-value="3"><?php esc_html_e( 'Remind me in 3 days', 'jitsi-meet-wp' ); ?></a>
			<a href="#" data-value="10"><?php esc_html_e( 'Remind me in 10 days', 'jitsi-meet-wp' ); ?></a>
			<a href="#" data-value="hide_notice"><?php esc_html_e( 'Don\'t remind me about this', 'jitsi-meet-wp' ); ?></a>
		</div>

		<button type="button" class="close-notice">&times;</button>
	</div>
</div>

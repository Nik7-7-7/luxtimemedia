<?php wp_nonce_field( 'mailster_nonce', 'mailster_nonce', false ); ?>
<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
<?php

$screen = get_current_screen();

$classes = array( 'wrap', 'mailster-dashboard' );
if ( $this->update ) {
	$classes[] = 'has-update';
}

?>
<div class="<?php echo implode( ' ', $classes ); ?>">
<h1><?php esc_html_e( 'Dashboard', 'mailster' ); ?></h1>
	
<div id="dashboard-widgets-wrap">
	<div id="dashboard-widgets" class="metabox-holder">
		<div id="postbox-container-1" class="postbox-container" data-id="normal">
			<?php do_meta_boxes( $screen->id, 'normal', '' ); ?>
		</div>
		<div id="postbox-container-2" class="postbox-container" data-id="side">
			<?php do_meta_boxes( $screen->id, 'side', '' ); ?>
		</div>
		<div id="postbox-container-3" class="postbox-container" data-id="column3">
			<?php do_meta_boxes( $screen->id, 'column3', '' ); ?>
		</div>
		<div id="postbox-container-4" class="postbox-container" data-id="column4">
			<?php do_meta_boxes( $screen->id, 'column4', '' ); ?>
		</div>
	</div>
</div>

</div>

<div class="wrap" id="mailster-setup">

<?php wp_nonce_field( 'mailster_nonce', 'mailster_nonce', false ); ?>

<?php

$timeformat = mailster( 'helper' )->timeformat();
$timeoffset = mailster( 'helper' )->gmt_offset( true );

$is_verified        = mailster()->is_verified();
$active_plugins     = get_option( 'active_plugins', array() );
$active_pluginslugs = preg_replace( '/^(.*)\/.*$/', '$1', $active_plugins );
$plugins            = array_keys( get_plugins() );
$pluginslugs        = preg_replace( '/^(.*)\/.*$/', '$1', $plugins );

$sections     = array(
	'basics'    => __( 'Business Details', 'mailster' ),
	'sending'   => __( 'Sending Information', 'mailster' ),
	'privacy'   => __( 'Privacy & Compliance', 'mailster' ),
	'homepage'  => __( 'Newsletter Homepage', 'mailster' ),
	'templates' => __( 'What should your campaigns look like?', 'mailster' ),
	'delivery'  => __( 'What is your preferred method for email delivery?', 'mailster' ),
	// 'extensions' => sprintf( __( 'Recommendations for %s', 'mailster' ), wp_parse_url( home_url(), PHP_URL_HOST ) ),
	'community' => __( 'Join the Community', 'mailster' ),
	'finish'    => __( 'Great, you\'re done!', 'mailster' ),
);
$section_keys = array_keys( $sections );

$beacons = array(
	'homepage' => '6453abdab9f4b70821b98a1b',
	'delivery' => '611bb9daf886c9486f8d992f',
);

?>

	<input style="display:none"><input type="password" style="display:none">

	<ol class="mailster-setup-steps-nav">
		<?php foreach ( $sections as $id => $name ) : ?>
		<li title="<?php echo esc_attr( $name ); ?>"><a href="#<?php echo esc_attr( $id ); ?>"></a></li>
		<?php endforeach; ?>
	</ol>

	<div class="mailster-setup-steps">

	<div class="mailster-setup-step active" id="step_start">

		<?php require MAILSTER_DIR . 'views/setup/start.php'; ?>	

	</div>

	<?php foreach ( $sections as $id => $name ) : ?>

		<?php $pos = array_search( $id, $section_keys ); ?>
		<?php $prev = isset( $section_keys[ $pos - 1 ] ) ? $section_keys[ $pos - 1 ] : null; ?>
		<?php $next = isset( $section_keys[ $pos + 1 ] ) ? $section_keys[ $pos + 1 ] : null; ?>

		<div class="mailster-setup-step" id="step_<?php echo esc_attr( $id ); ?>">
			
			<h2 class="section-title"><?php echo esc_html( $name ); ?>
			<?php
			if ( isset( $beacons[ $id ] ) ) {
				echo mailster()->beacon( $beacons[ $id ] );
			}
			?>
			</h2>
			<?php require MAILSTER_DIR . 'views/setup/' . esc_attr( $id ) . '.php'; ?>		

			<div class="mailster-setup-step-buttons">

			<?php if ( $prev ) : ?>
				<a class="button button-link last-step alignleft" href="#<?php echo esc_attr( $prev ); ?>" title="<?php printf( esc_attr__( 'Back to "%s"', 'mailster' ), $sections[ $id ] ); ?>">← <?php esc_html_e( 'Go Back', 'mailster' ); ?></a>
			<?php endif; ?>
			<span class="alignleft status"></span>
			<i class="spinner"></i>

			<?php if ( $next ) : ?>
				<a class="button button-secondary skip-step" href="#<?php echo esc_attr( $next ); ?>"><?php esc_html_e( 'Skip this Step', 'mailster' ); ?></a>
				<a class="button  button-primary next-step" href="#<?php echo esc_attr( $next ); ?>"><?php esc_html_e( 'Next Step', 'mailster' ); ?></a>
			<?php else : ?>
				<a class="button button-primary" href="admin.php?page=mailster_dashboard&mailster_setup_complete=<?php echo wp_create_nonce( 'mailster_setup_complete' ); ?>"><?php esc_html_e( 'Ok, got it!', 'mailster' ); ?></a>
	
			<?php endif; ?>
			</div>

		</div>
	<?php endforeach; ?>	

	</div>
	
</div>


<div class="mailster-setup-step-body">

<form class="mailster-setup-step-form">

<div class="mailster-setup-col">

	<?php if ( mailster()->is_trial() ) : ?>
	<h2><?php printf( esc_html_x( 'Thanks for Testing %s!', 'Mailster', 'mailster' ), 'Mailster' ); ?></h2>
	<?php else : ?>
	<h2><?php printf( esc_html_x( 'Welcome to %s', 'Mailster', 'mailster' ), 'Mailster' ); ?></h2>
	<?php endif; ?>

	<p><?php esc_html_e( 'Before you can start sending your campaigns Mailster needs some info to get started.', 'mailster' ); ?></p>

	<p><?php esc_html_e( 'This wizard helps you to setup Mailster. All options available can be found later in the settings. You can always skip each step and adjust your settings later if you\'re not sure.', 'mailster' ); ?></p>
	<?php if ( mailster()->is_trial() ) : ?>
		<p><?php esc_html_e( 'You are currently using the trial version of Mailster. You can use all features of Mailster for 14 days. After that you need to purchase a license to continue using Mailster.', 'mailster' ); ?></p>

	<?php endif; ?>
	<p><a class="button button-hero button-primary next-step" href="#basics"><?php esc_html_e( 'Start Wizard', 'mailster' ); ?></a></p>

	<p class="skip-link"><a href="admin.php?page=mailster_dashboard&mailster_setup_complete=<?php echo wp_create_nonce( 'mailster_setup_complete' ); ?>"><?php esc_html_e( 'Skip the Wizard', 'mailster' ); ?></a></p>
</div>
	<div class="mailster-setup-col welcome-splash">
		<img src="https://static.mailster.co/images/wizard/welcome-splash.png" width="512" height="512" draggable="false">
	</div>


</form>

</div>

<div class="mailster-setup-step-buttons" hidden>

	<span class="alignleft status"></span>
	<i class="spinner"></i>

	<a class="button button-primary next-step" href="#basics"><?php esc_html_e( 'Start Wizard', 'mailster' ); ?></a>

</div>

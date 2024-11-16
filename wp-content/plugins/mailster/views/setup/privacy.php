<div class="mailster-setup-step-body">

	<form class="mailster-setup-step-form">

	<p><?php esc_html_e( 'Mailster can track specific behaviors and the location of your subscribers to target your audience better. In most countries you must get the consent of the subscriber if you sent them marketing emails. Please get in touch with your lawyer for legal advice in your country.', 'mailster' ); ?></p>
	<p><?php esc_html_e( 'If you have users in the European Union you have to comply with the General Data Protection Regulation (GDPR). Please check our knowledge base on how Mailster can help you.', 'mailster' ); ?></p>
	<p><a href="<?php echo mailster_url( 'https://kb.mailster.co/tag/gdpr/' ); ?>" class="external button"><?php esc_html_e( 'Knowledge Base', 'mailster' ); ?></a></p>

	<?php require MAILSTER_DIR . '/views/settings/privacy.php'; ?>

	</form>

</div>



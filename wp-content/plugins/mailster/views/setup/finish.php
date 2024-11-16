<div class="mailster-setup-step-body">

<?php if ( mailster()->is_trial() ) : ?>
	<?php
			$license = mailster_freemius()->_get_license();
			$expires = $license ? strtotime( $license->expiration ) : 0;
			$offset  = $expires - time();
			$display = $offset > DAY_IN_SECONDS ? human_time_diff( $expires ) : date( 'H:i:s', strtotime( 'midnight' ) + $offset - 1 );
	?>
	<?php if ( $offset > 0 ) : ?>
		<a role="tab" aria-controls="activity-panel-help" id="mailster-trial-upgrade" class="panel-tab action" href="<?php echo mailster()->get_upgrade_url(); ?>" data-offset=<?php echo absint( $offset ); ?> title="<?php esc_attr_e( 'Upgrade now!', 'mailster' ); ?>"><?php printf( esc_html__( 'Your trial expires in %s', 'mailster' ), '<span>' . esc_html( $display ) . '</span>' ); ?></a>
	<?php else : ?>
		<a role="tab" aria-controls="activity-panel-help" id="mailster-trial-upgrade" class="panel-tab action expired" href="<?php echo mailster()->get_upgrade_url(); ?>" title="<?php esc_attr_e( 'Upgrade now!', 'mailster' ); ?>"><?php esc_html_e( 'Your trial has expired!', 'mailster' ); ?><span><?php esc_html_e( 'Upgrade now!', 'mailster' ); ?></span></a>
	<?php endif; ?>
<p><?php esc_html_e( 'Now you can continue to customize Mailster to your needs.', 'mailster' ); ?></p>
<?php else : ?>
<p><?php esc_html_e( 'Now you can continue to customize Mailster to your needs.', 'mailster' ); ?></p>
<?php endif; ?>

<div class="feature-section two-col">
	<div class="col">

	<ol>
		<li><a class="external" href="edit.php?post_type=newsletter&page=mailster_settings"><?php esc_html_e( 'Complete your settings', 'mailster' ); ?></a></li>
		<li><a class="external" href="post-new.php?post_type=newsletter"><?php esc_html_e( 'Create your first campaign', 'mailster' ); ?></a></li>
		<li><a class="external" href="post-new.php?post_type=mailster-workflow"><?php esc_html_e( 'Create your first automation', 'mailster' ); ?></a></li>
		<li><a class="external" href="edit.php?post_type=mailster-form"><?php esc_html_e( 'Update your forms', 'mailster' ); ?></a></li>
		<li><a class="external" href="edit.php?post_type=newsletter&page=mailster_manage_subscribers"><?php esc_html_e( 'Import your subscribers', 'mailster' ); ?></a></li>
		<li><a class="external" href="edit.php?post_type=newsletter&page=mailster_templates"><?php esc_html_e( 'Check out the templates', 'mailster' ); ?></a></li>
		<li><a class="external" href="edit.php?post_type=newsletter&page=mailster_addons"><?php esc_html_e( 'Extend Mailster', 'mailster' ); ?></a></li>
	</ol>
	<h3><?php esc_html_e( 'External Resources', 'mailster' ); ?> <small>(<?php esc_html_e( 'in English', 'mailster' ); ?>)</small></h3>
	<ol>
		<li><a data-mode="modal" data-article="6460f6909a2fac195e609002" href="<?php echo mailster_url( 'https://kb.mailster.co/6460f6909a2fac195e609002' ); ?>"><?php esc_html_e( 'Email Marketing Automation in Mailster', 'mailster' ); ?></a></li>
		<li><a data-mode="modal" data-article="63f5f51ee6d6615225472ab9" href="<?php echo mailster_url( 'https://kb.mailster.co/63f5f51ee6d6615225472ab9' ); ?>"><?php esc_html_e( 'How do I import my subscribers?', 'mailster' ); ?></a></li>
		<li><a data-mode="modal" data-article="611bbdd6b55c2b04bf6df15f" href="<?php echo mailster_url( 'https://kb.mailster.co/611bbdd6b55c2b04bf6df15f' ); ?>"><?php esc_html_e( 'Customize the notification template', 'mailster' ); ?></a></li>
		<li><a data-mode="modal" data-article="611bb8eab37d837a3d0e47b8" href="<?php echo mailster_url( 'https://kb.mailster.co/611bb8eab37d837a3d0e47b8' ); ?>"><?php esc_html_e( 'Send your latest posts automatically', 'mailster' ); ?></a></li>
		<li><a data-mode="modal" data-article="611bb8346ffe270af2a9994e" href="<?php echo mailster_url( 'https://kb.mailster.co/611bb8346ffe270af2a9994e/' ); ?>"><?php esc_html_e( 'Learn more about segmentation', 'mailster' ); ?></a></li>
	</ol>
	</div>
	<div class="col subscribe"></div>
	
</div>	
</div>

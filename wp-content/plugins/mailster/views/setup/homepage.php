<div class="mailster-setup-step-body">

<form class="mailster-setup-step-form">

<p><?php esc_html_e( 'Mailster needs a Newsletter Homepage were users can subscribe, update and unsubscribe their subscription. It\'s a regular page with special Newsletter signup form.', 'mailster' ); ?></p>

<?php

$homepage_id = mailster_option( 'homepage' );
if ( ! $homepage_id ) {
	$homepage_id = mailster( 'settings' )->create_homepage();
}

$homepage = get_post( $homepage_id );

?>
<p>
<label><strong><?php esc_html_e( 'Page Title', 'mailster' ); ?>:</strong>
<input id="homepage_title" type="text" name="post_title" size="30" value="<?php echo esc_attr( $homepage->post_title ); ?>" id="title" spellcheck="true" autocomplete="off"></label>

<?php if ( mailster( 'helper' )->using_permalinks() ) : ?>

	<?php $url = trailingslashit( get_bloginfo( 'url' ) ); ?>
	<label><?php echo esc_html_x( 'Location', 'the URL not the place', 'mailster' ); ?>:</label>
	<span>
		<a href="<?php echo get_permalink( $homepage ); ?>" class="external"><?php echo esc_url( $url ); ?><strong><?php echo sanitize_title( $homepage->post_name ); ?></strong>/</a>
		<a class="button button-small hide-if-no-js edit-slug"><?php echo esc_html__( 'Edit', 'mailster' ); ?></a>
	</span>
	<span class="edit-slug-area">
	<?php echo esc_url( $url ); ?><input type="text" name="post_name" value="<?php echo sanitize_title( $homepage->post_name ); ?>" class="regular-text">
	</span>

<?php endif; ?>

</p>

<?php
$slugs = mailster_option(
	'slugs',
	array(
		'confirm'     => 'confirm',
		'subscribe'   => 'subscribe',
		'unsubscribe' => 'unsubscribe',
		'profile'     => 'profile',
	)
);

if ( mailster( 'helper' )->using_permalinks() ) :
	$homepage = trailingslashit( get_permalink( mailster_option( 'homepage' ) ) );
	?>
<ul class="mailster-homepage-slugs">
<li title="<?php esc_attr_e( 'Confirm Slug', 'mailster' ); ?>">
	<span>
		<?php echo esc_html( $slugs['confirm'] ); ?>
		<a class="button button-small hide-if-no-js edit-slug"><?php echo esc_html__( 'Edit', 'mailster' ); ?></a>
	</span>
	<span class="edit-slug-area">
	<input type="text" name="mailster_options[slugs][confirm]" value="<?php echo esc_attr( $slugs['confirm'] ); ?>" class="small-text">
	</span>
</li>
<li title="<?php esc_attr_e( 'Subscribe Slug', 'mailster' ); ?>">
	<span>
		<?php echo esc_html( $slugs['subscribe'] ); ?>
		<a class="button button-small hide-if-no-js edit-slug"><?php echo esc_html__( 'Edit', 'mailster' ); ?></a>
	</span>
	<span class="edit-slug-area">
	<input type="text" name="mailster_options[slugs][subscribe]" value="<?php echo esc_attr( $slugs['subscribe'] ); ?>" class="small-text">
	</span>
</li>
<li title="<?php esc_attr_e( 'Unsubscribe Slug', 'mailster' ); ?>">
	<span>
		<?php echo esc_html( $slugs['unsubscribe'] ); ?>
		<a class="button button-small hide-if-no-js edit-slug"><?php echo esc_html__( 'Edit', 'mailster' ); ?></a>
	</span>
	<span class="edit-slug-area">
	<input type="text" name="mailster_options[slugs][unsubscribe]" value="<?php echo esc_attr( $slugs['unsubscribe'] ); ?>" class="small-text">
	</span>
</li>
<li title="<?php esc_attr_e( 'Profile Slug', 'mailster' ); ?>">
	<span>
		<?php echo esc_html( $slugs['profile'] ); ?>
		<a class="button button-small hide-if-no-js edit-slug"><?php echo esc_html__( 'Edit', 'mailster' ); ?></a>
	</span>
	<span class="edit-slug-area">
	<input type="text" name="mailster_options[slugs][profile]" value="<?php echo esc_attr( $slugs['profile'] ); ?>" class="small-text">
	</span>
</li>
</ul>
<?php else : ?>

<input type="hidden" name="mailster_options[slugs][confirm]" value="<?php echo esc_attr( $slugs['confirm'] ); ?>">
<input type="hidden" name="mailster_options[slugs][subscribe]" value="<?php echo esc_attr( $slugs['subscribe'] ); ?>">
<input type="hidden" name="mailster_options[slugs][unsubscribe]" value="<?php echo esc_attr( $slugs['unsubscribe'] ); ?>">
<input type="hidden" name="mailster_options[slugs][profile]" value="<?php echo esc_attr( $slugs['profile'] ); ?>">

<?php endif; ?>	

<?php

$url = add_query_arg(
	array(
		'preview'         => true,
		'_mailster_page'  => 'submission',
		'_mailster_setup' => wp_create_nonce( 'mailster_setup' ),
	),
	get_permalink( $homepage_id )
);

$edit_link = add_query_arg(
	array(
		'post'   => $homepage_id,
		'action' => 'edit',
	),
	admin_url( 'post.php' )
);

?>
<div class="mailster-homepage-previews">
			
	<div class="mailster-homepage-preview" data-type="submission">
		<div class="action-buttons">
			<a href="<?php echo esc_url( $edit_link ); ?>#mailster-submission" class="edit-homepage" target="mailster_edit_homepage"><?php esc_html_e( 'Edit', 'mailster' ); ?></a>
			<a href="<?php echo esc_url( $url ); ?>" class="preview-homepage" target="mailster_preview_homepage"><?php esc_html_e( 'Preview', 'mailster' ); ?></a>
		</div>
		<div class="mailster-homepage-preview-browser" >
			<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 3661.5 80" style="enable-background:new 0 0 3661.5 80" xml:space="preserve"><path style="fill:#fff" d="M0 0h3661.5v80H0z"/><circle class="st1" fill="#58595b" cx="40.1" cy="40" r="9.1"/><circle class="st1" fill="#58595b" cx="68.5" cy="40" r="9.1"/><circle class="st1" fill="#58595b" cx="96.9" cy="40" r="9.1"/><path class="st1" fill="#58595b" d="M3587.2 27h23.6c1.2 0 2.2 1 2.2 2.2v1.9c0 1.2-1 2.2-2.2 2.2h-23.6c-1.2 0-2.2-1-2.2-2.2v-1.9c0-1.2 1-2.2 2.2-2.2zM3587.2 37.8h23.6c1.2 0 2.2 1 2.2 2.2v2c0 1.2-1 2.2-2.2 2.2h-23.6c-1.2 0-2.2-1-2.2-2.2v-1.9c0-1.3 1-2.3 2.2-2.3zM3587.2 48.6h23.6c1.2 0 2.2 1 2.2 2.2v1.9c0 1.2-1 2.2-2.2 2.2h-23.6c-1.2 0-2.2-1-2.2-2.2v-1.9c0-1.2 1-2.2 2.2-2.2z"/></svg>
			<iframe src="<?php echo esc_url( $url ); ?>" sandbox loading="lazy"></iframe>
		</div>
	</div>

	<div class="mailster-homepage-preview-other">
	
	<?php foreach ( array( 'unsubscribe', 'profile', 'subscribe' ) as $type ) : ?>

		<?php $url = add_query_arg( array( '_mailster_page' => $type ), $url ); ?>
		<div class="mailster-homepage-preview mailster-homepage-preview-small" data-type="<?php echo esc_attr( $type ); ?>">
				<div class="action-buttons">
					<a href="<?php echo esc_url( $edit_link ) . '#mailster-' . $type; ?>" class="edit-homepage" target="mailster_edit_homepage"><?php esc_html_e( 'Edit', 'mailster' ); ?></a>
					<a href="<?php echo esc_url( $url ); ?>" class="preview-homepage" target="mailster_preview_homepage"><?php esc_html_e( 'Preview', 'mailster' ); ?></a>
				</div>
				<div class="mailster-homepage-preview-browser" >
				<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 3661.5 80" style="enable-background:new 0 0 3661.5 80" xml:space="preserve"><path style="fill:#fff" d="M0 0h3661.5v80H0z"/><circle class="st1" fill="#58595b" cx="40.1" cy="40" r="9.1"/><circle class="st1" fill="#58595b" cx="68.5" cy="40" r="9.1"/><circle class="st1" fill="#58595b" cx="96.9" cy="40" r="9.1"/><path class="st1" fill="#58595b" d="M3587.2 27h23.6c1.2 0 2.2 1 2.2 2.2v1.9c0 1.2-1 2.2-2.2 2.2h-23.6c-1.2 0-2.2-1-2.2-2.2v-1.9c0-1.2 1-2.2 2.2-2.2zM3587.2 37.8h23.6c1.2 0 2.2 1 2.2 2.2v2c0 1.2-1 2.2-2.2 2.2h-23.6c-1.2 0-2.2-1-2.2-2.2v-1.9c0-1.3 1-2.3 2.2-2.3zM3587.2 48.6h23.6c1.2 0 2.2 1 2.2 2.2v1.9c0 1.2-1 2.2-2.2 2.2h-23.6c-1.2 0-2.2-1-2.2-2.2v-1.9c0-1.2 1-2.2 2.2-2.2z"/></svg>
				<iframe src="<?php echo esc_url( $url ); ?>" sandbox loading="lazy"></iframe>
			</div>
		</div>
	
	<?php endforeach; ?>

	</div>

	</div>
	<p><?php esc_html_e( 'You can update the form, content and texts later.', 'mailster' ); ?></p>

	</form>
</div>

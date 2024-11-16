<?php
$classes = array( 'template' );


$classes[] = 'template-' . $slug;
if ( $item['is_default'] ) {
	$classes[] = 'active';
}
if ( $item['installed'] ) {
	$classes[] = 'is-installed';
}
if ( ! $item['is_supported'] && ! $item['installed'] ) {
	$classes[] = 'not-supported';
}
if ( $item['is_premium'] && ! mailster_freemius()->is_plan( 'professional' ) ) {
	$classes[] = 'is-locked';
}
if ( $item['is_premium'] ) {
	$classes[] = 'is-premium';
}
if ( $item['update_available'] ) {
	$classes[] = 'update-available';
}
if ( $item['envato_item_id'] && ! $item['is_premium'] ) {
	$classes[] = 'envato-item';
}

if ( $slug !== 'mailster' ) {
		// return;
}


?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" tabindex="0" data-slug="<?php echo esc_attr( $slug ); ?>">

	<?php
	$html    = base64_decode( $item['sample'] );
	$content = mailster( 'template' )->load_template_html( $html );

	$content = mailster()->sanitize_content( $content );

	$placeholder = mailster( 'placeholder', $content );

	$content = $placeholder->get_content();
	$content = mailster( 'helper' )->strip_structure_html( $content );

	$content = mailster( 'helper' )->add_mailster_styles( $content );
	$content = mailster( 'helper' )->handle_shortcodes( $content );

	$plans = mailster()->get_plans();
	?>

	<div class="mailster-template-preview">
		<header>
			<?php echo esc_html( $item['name'] ); ?>
			<span class="theme-badge theme-default-badge"><?php esc_html_e( 'Current', 'mailster' ); ?></span>
		</header>
		<div class="locked">
			<p><?php esc_html_e( 'You have to upgrade your plan to access this template!', 'mailster' ); ?></p>
			<p>
				<?php esc_html_e( 'Upgrade to', 'mailster' ); ?><br>
				<?php foreach ( $plans as $plan ) : ?>
					<?php
					if ( ! in_array( $plan->name, array( 'professional', 'agency' ) ) ) :
						continue;
					endif;
					?>
				<a class="button button-primary upgrade-plan" data-plan="<?php echo esc_attr( $plan->id ); ?>" data-name="<?php echo esc_attr( $plan->name ); ?>"><?php echo esc_html( $plan->title ); ?></a>
				<?php endforeach; ?>
			</p>
			<p><a class="button button-link button-small" href="<?php echo esc_url( mailster_freemius()->pricing_url() ); ?>" target="mailster_pricing"><?php esc_html_e( 'Compare Plans', 'mailster' ); ?></a></p>

		</div>
		<div class="mailster-template-preview-browser">
			<iframe src="data:text/html;base64,<?php echo base64_encode( $content ); ?>" class="theme-screenshot-iframe" scrolling="no" allowTransparency="true" frameBorder="0" sandbox="allow-presentation allow-scripts" loading="lazy"></iframe>
		</div>
		<?php if ( ! $item['is_supported'] && ! $item['installed'] ) : ?>
		<div class="notice inline update-message notice-error notice-alt"><p><?php printf( esc_html__( 'This template requires Mailster version %s or above. Please update first.', 'mailster' ), '<strong>' . $item['requires'] . '</strong>' ); ?></p></div>
		<?php endif; ?>
	</div>
</div>

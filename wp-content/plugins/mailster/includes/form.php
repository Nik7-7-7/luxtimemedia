<?php

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

do_action( 'mailster_form_header' );

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="mailster-embeded-form">
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php echo get_option( 'blog_charset' ); ?>" />
	<meta name='robots' content='noindex,nofollow'>
	<?php do_action( 'mailster_form_head' ); ?>

</head>
<body>
	<div class="mailster-form-body">
		<div class="mailster-form-wrap">
			<div class="mailster-form-inner">
			<?php do_action( 'mailster_form_body' ); ?>
			</div>
		</div>
	</div>
<?php do_action( 'mailster_form_footer' ); ?>
</body>
</html>

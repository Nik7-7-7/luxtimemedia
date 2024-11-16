<?php
$accountURL      = mpapp()->shortcodes()->customerAccount()->getAccountURL();
$accountUsername = $_GET['username'] ?? '';

$args = array(
	'redirect'       => $accountURL,
	'value_username' => sanitize_text_field( wp_unslash( $accountUsername ) ),
);
?>
<div class="mpa-account-login-form">
    <?php wp_login_form( $args ); ?>
    <a href="<?php echo esc_url( wp_lostpassword_url( $accountURL ) ); ?>"><?php esc_html_e( 'Lost your password?', 'motopress-appointment' ); ?></a>
</div>

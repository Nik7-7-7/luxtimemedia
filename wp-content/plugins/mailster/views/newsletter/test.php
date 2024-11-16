<?php

$current_user = wp_get_current_user();

if ( ! ( $test_email = get_user_meta( $current_user->ID, '_mailster_test_email', true ) ) ) {
	$test_email = $current_user->user_email;
}
$test_email = apply_filters( 'mailster_test_email', $test_email );
?>
<input type="text" value="<?php echo esc_attr( $test_email ); ?>" placeholder="<?php echo esc_attr( $current_user->user_email ); ?>" autocomplete="off" id="mailster_testmail" class="widefat" aria-label="<?php esc_attr_e( 'Send Test', 'mailster' ); ?>">
<span class="spinner" id="delivery-ajax-loading"></span>
<input type="button" value="<?php esc_attr_e( 'Run Precheck', 'mailster' ); ?>" class="button mailster_precheck">
<input type="button" value="<?php esc_attr_e( 'Send Test', 'mailster' ); ?>" class="button mailster_sendtest">

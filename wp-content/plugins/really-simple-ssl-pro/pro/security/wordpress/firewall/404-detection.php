<?php
/**
 * The 404 detection file.
 * This file is responsible for detecting 404 errors and blocking the IP address if it is in the blocked IP list.
 * If the IP address is blocked, the user will be redirected to the 403 page.
 * This file is a part of the 'Really Simple Security pro' plugin, which is developed by the company 'Really Simple Plugins'.
 *
 * @package     RSSSL_PRO\Security\WordPress\Firewall  // The categorization of this file.
 */

if ( ( defined( 'RSSSL_DISABLE_REGION_BLOCK' ) && RSSSL_DISABLE_REGION_BLOCK ) || (defined( 'RSSSL_SAFE_MODE' ) && RSSSL_SAFE_MODE) || ! file_exists( $plugin_dir ) ) {
	return;
}
/**
 * Checks if the current IP address is blocked or not based on the blocked IP list and whitelist.
 *
 * @param array  $blocked_ips An array of blocked IP addresses.
 * @param array  $white_list An array of whitelisted IP addresses.
 * @param string $ip_fetcher_file The file path of the IP fetcher class.
 *
 * @return bool Returns true if the current IP address is blocked, false otherwise.
 */
function rsssl_block_404( array $blocked_ips, array $white_list, string $ip_fetcher_file ): bool {
	require_once $ip_fetcher_file;
	$ip_fetcher = new RSSSL\Pro\Security\WordPress\Limitlogin\Rsssl_IP_Fetcher();
	$ip_address = $ip_fetcher->get_ip_address()[0];

	if ( in_array( $ip_address, $white_list, true ) ) {
		return false;
	}
	if ( in_array( $ip_address, $blocked_ips, true ) ) {
		return true;
	}
	return false;
}

if ( rsssl_block_404( $blocked_ips, $white_list, $ip_fetcher_file ) ) {
	$dir       = dirname( __DIR__, 3 );
	$message = $message_404;
	$apology = $apology_404;
	$block_url = "$dir/assets/templates/403-page.php";
	http_response_code( 403 );
	require_once $block_url;
	exit;
}

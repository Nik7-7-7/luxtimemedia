<?php
/**
 * The User Agent detection file.
 * This file is responsible for detecting user agents and blocking the IP address if it is in the blocked user agent list.
 * If the user agent is blocked, the user will be redirected to the 403 page.
 * This file is a part of the 'Really Simple SSL pro' plugin, which is developed by the company 'Really Simple Plugins'.
 *
 * @package     RSSSL_PRO\Security\WordPress\Firewall  // The categorization of this file.
 */

if (!isset($user_agent_detection_file, $ip_fetcher_file, $blocked_user_agents, $white_list, $message_user_agent, $plugin_dir)) {
    return;
}

if ((defined("RSSSL_DISABLE_REGION_BLOCK") && RSSSL_DISABLE_REGION_BLOCK) || ( defined('RSSSL_SAFE_MODE') && RSSSL_SAFE_MODE ) || !file_exists($plugin_dir)) {
    return;
}

use RSSSL\Pro\Security\WordPress\Firewall\Models\Rsssl_User_Agent_Handler;

require_once $user_agent_detection_file;

$uaHandler = new Rsssl_User_Agent_Handler();
if (empty($blocked_user_agents)) {
    return;
}
require_once $ip_fetcher_file;
$ip_fetcher = new RSSSL\Pro\Security\WordPress\Limitlogin\Rsssl_IP_Fetcher();
$ip_address = $ip_fetcher->get_ip_address()[0];
// Check for blocked user agents
if ($uaHandler->blocked_named_user_agent($blocked_user_agents)) {
    if (in_array($ip_address, $white_list, true)) {
        return false;
    }
    $dir = dirname(__DIR__, 3);
    $message = $message_user_agent;
    if($uaHandler->is_curl()) {
        http_response_code(403);
        exit;
    }

	$block_url = "$dir/assets/templates/403-page.php";
	http_response_code(403);
	require_once $block_url;
	exit;
}
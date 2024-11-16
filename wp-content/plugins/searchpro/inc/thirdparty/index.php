<?php
if (!defined('ABSPATH')) exit;

// Hosting
require_once optifer_PATH . '/inc/thirdparty/hosting/Cloudways.php';
require_once optifer_PATH . '/inc/thirdparty/hosting/Siteground.php';
require_once optifer_PATH . '/inc/thirdparty/hosting/WPEngine.php';

// Plugin
require_once optifer_PATH . '/inc/thirdparty/plugin/CloudflarePageCache.php';
require_once optifer_PATH . '/inc/thirdparty/plugin/NginxHelper.php';
require_once optifer_PATH . '/inc/thirdparty/plugin/ObjectCachePro.php';
require_once optifer_PATH . '/inc/thirdparty/plugin/AdminSiteEnhancements.php';
require_once optifer_PATH . '/inc/thirdparty/plugin/HideMyWPGhost.php';

// Server
require_once optifer_PATH . '/inc/thirdparty/server/Nginx.php';
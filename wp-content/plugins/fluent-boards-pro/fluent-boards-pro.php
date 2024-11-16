<?php defined('ABSPATH') or die;

/*
Plugin Name: Fluent Boards Pro
Description: Pro addon for FluentBoards Plugin
Version: 1.30
Author: WPManageNinja
Author URI: https://fluentboards.com
Plugin URI: https://fluentboards.com
License: GPLv2 or later
Text Domain: fluent-boards-pro
Domain Path: /language
*/

define('FLUENT_BOARDS_PRO', 'fluent-boards-pro');
define('FLUENT_BOARDS_PRO_VERSION', '1.30');
define('FLUENT_BOARDS_PRO_DIR_FILE', __FILE__);
define('FLUENT_BOARDS_PRO_URL', plugin_dir_url(__FILE__));
define('FLUENT_BOARDS_PRO_LIVE', true);

require __DIR__ . '/vendor/autoload.php';

/*
 * For Beta Testing purposes
 */
(new \FluentBoardsPro\App\Services\HelperInstaller())->register();

call_user_func(function ($bootstrap) {
    $bootstrap(__FILE__);
}, require(__DIR__ . '/boot/app.php'));

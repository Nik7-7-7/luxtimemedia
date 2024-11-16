<?php

namespace FluentBoardsPro\App\Modules\TimeTracking;

class TimerInit
{

    protected $app;

    public function __construct(\FluentBoards\Framework\Foundation\Application $app)
    {
        $this->app = $app;
    }

    public function register()
    {

        /*
         * Register The API Routes
         */
        $this->app->router->namespace('FluentBoardsPro\App\Modules\TimeTracking\Controllers')->group(function ($router) {
            include __DIR__ . '/routes.php';
        });

        add_filter('fluent_boards/board_find', function ($board) {
            $board->has_timer = TimeTrackingHelper::isTimeTrackingEnabled();
            return $board;
        });

        add_action('fluent_boards/saving_addons', function ($newSettings, $oldSettings) {
            if ($newSettings['timeTracking']['enabled'] == 'yes' && $oldSettings['timeTracking']['enabled'] == 'no') {
                $this->migrateDbTable();
            }
        }, 10, 2);
        
    }


    public function migrateDbTable()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();
        $table = $wpdb->prefix . 'fbs_time_tracks';

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) != $table) {
            $sql = "CREATE TABLE $table (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `user_id` BIGINT UNSIGNED NOT NULL,
                `board_id` INT UNSIGNED NOT NULL,
                `task_id` INT UNSIGNED NOT NULL,
                `started_at` TIMESTAMP NULL,
                `completed_at` TIMESTAMP NULL,
                `message` TEXT NULL,
                `status` VARCHAR(50) NULL DEFAULT 'commited',
                `working_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
                `billable_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
                `is_manual` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                KEY `user_id` (`user_id`),
                KEY `status` (`status`),
                KEY `task_id` (`task_id`),
                KEY `board_id` (`board_id`)
            ) $charsetCollate;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
    }

}

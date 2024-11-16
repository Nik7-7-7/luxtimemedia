<?php

use FluentBoardsPro\App\Core\Application;
use FluentBoardsPro\App\Hooks\Handlers\ProActivationHandler;
use FluentBoardsPro\App\Hooks\Handlers\ProDeactivationHandler;

return function ($file) {
    add_action('fluent_boards_loaded', function ($app) use ($file) {
        new Application($app, $file);

        $features = fbsGetFeaturesConfig();

        // Init Modules
        (new \FluentBoardsPro\App\Modules\TimeTracking\TimerInit($app))->register();
        if (isset($features['cloud_storage']) && $features['cloud_storage'] === 'yes') {
            (new \FluentBoardsPro\App\Modules\CloudStorage\CloudStorageModule())->register($app);
        }
    });

    add_action('fluent_boards/rendering_app', function () {
        if(as_next_scheduled_action('fluent_boards/hourly_scheduler') === false) {
            as_schedule_recurring_action(time() + HOUR_IN_SECONDS, HOUR_IN_SECONDS, 'fluent_boards/hourly_scheduler', [], 'fluent-boards');
        }
        (new \FluentBoardsPro\App\Hooks\Handlers\ProScheduleHandler())->recurringTaskScheduler();
    });

    register_activation_hook($file, function () {
        (new ProActivationHandler())->handle();
    });

    register_deactivation_hook($file, function () {
        (new ProDeactivationHandler())->handle();
    });


    add_action('plugins_loaded', function () {
        $licenseManager
            = new \FluentBoardsPro\App\Services\PluginManager\LicenseManager();
        $licenseManager->initUpdater();

        $licenseMessage = $licenseManager->getLicenseMessages();

        if ($licenseMessage) {
            add_action('admin_notices', function () use ($licenseMessage) {
                if (defined('FLUENT_BOARDS')) {
                    $class   = 'notice notice-error fc_message';
                    $message = $licenseMessage['message'];
                    printf('<div class="%1$s"><p>%2$s</p></div>',
                        esc_attr($class), $message);
                }
            });
        }

        add_filter('fluent_boards/dashboard_notices',
            function ($notices) use ($licenseManager) {
                $details = $licenseManager->getLicenseMessages();
                if ($details && ! empty($details['message'])) {
                    $notices[] = '<div style="padding: 10px;" class="error">'
                                 .$details['message'].'</div>';
                }

                return $notices;
            });

        add_filter('plugin_row_meta', array($licenseManager, 'pluginRowMeta'),
            10, 2);

    }, 0);

};

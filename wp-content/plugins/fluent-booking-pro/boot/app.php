<?php

use FluentBookingPro\App\Core\Application;
use FluentBookingPro\Database\DBMigrator;

return function ($file) {
    add_action('fluent_booking/loaded', function ($app) use ($file) {
        new Application($app, $file);

        $licenseManager = new \FluentBookingPro\App\Services\PluginManager\LicenseManager();
        $licenseManager->initUpdater();

        $licenseMessage = $licenseManager->getLicenseMessages();

        if ($licenseMessage) {
            add_action('admin_notices', function () use ($licenseMessage) {
                $class = 'notice notice-error fc_message';
                $message = $licenseMessage['message'];
                printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), wp_kses_post($message));
            });
        }
    });

    register_activation_hook($file, function () {

        if (defined('FLUENT_BOOKING_DIR')) {
            // Temp Free version Migrator
            (new \FluentBooking\App\Hooks\Handlers\ActivationHandler(\FluentBooking\App\App::getInstance()))->handle();
        }

        DBMigrator::run();
    });

    add_action('admin_notices', function () {
        if (defined('FLUENT_BOOKING_LITE')) {
            return;
        }

        function fluentBookingGetInstallationDetails()
        {
            $activation = (object)[
                'action' => 'install',
                'url'    => ''
            ];

            $allPlugins = get_plugins();

            if (isset($allPlugins['fluent-booking/fluent-booking.php'])) {
                $url = wp_nonce_url(
                    self_admin_url('plugins.php?action=activate&plugin=fluent-booking/fluent-booking.php'),
                    'activate-plugin_fluent-booking/fluent-booking.php'
                );
                $activation->action = 'activate';
            } else {
                $api = (object)[
                    'slug' => 'fluent-booking'
                ];
                $url = wp_nonce_url(
                    self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug),
                    'install-plugin_' . $api->slug
                );
            }

            $activation->url = $url;

            return $activation;
        }

        $pluginInfo = fluentBookingGetInstallationDetails();

        $class = 'notice notice-error booking_notice';

        $install_url_text = 'Click Here to Install the Plugin';

        if ($pluginInfo->action == 'activate') {
            $install_url_text = 'Click Here to Activate the Plugin';
        }

        $message = '<b>HEADS UP:</b> FluentBooking Pro Requires FluentBooking Base Plugin, <b><a href="' . $pluginInfo->url
            . '">' . $install_url_text . '</a></b>';

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
    });

};

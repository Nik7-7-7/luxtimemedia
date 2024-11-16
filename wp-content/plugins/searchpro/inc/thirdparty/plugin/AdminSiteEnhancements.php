<?php

class AdminSiteEnhancements extends berqIntegrations
{
    function __construct()
    {
        add_action('admin_notices', [$this, 'restapi_error']);
    }

    function restapi_error()
    {
        if (!defined('ASENHA_SLUG_U')) {
            return;
        }

        $options = get_option(ASENHA_SLUG_U, []);

        if (array_key_exists('disable_rest_api', $options) && $options['disable_rest_api']) {
            ?>
            <div class="notice notice-error">
                <p><strong>Error:</strong> The WordPress REST API is disabled. BerqWP plugin will not function correctly without the
                    REST API. Please enable the REST API for full functionality.</p>
            </div>
            <?php
        }
    }
}

new AdminSiteEnhancements();
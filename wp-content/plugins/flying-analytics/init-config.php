<?php
// Set default config on plugin load if not set
function flying_analytics_set_default_config() {

    if (FLYING_ANALYTICS_VERSION !== get_option('FLYING_ANALYTICS_VERSION')) {
        
        if (get_option('flying_analytics_method') === false)
            update_option('flying_analytics_method', "minimal-analytics");

        if (get_option('flying_analytics_disable_on_login') === false)
            update_option('flying_analytics_disable_on_login', true);
            
        update_option('FLYING_ANALYTICS_VERSION', FLYING_ANALYTICS_VERSION);
    }
}

add_action('plugins_loaded', 'flying_analytics_set_default_config');

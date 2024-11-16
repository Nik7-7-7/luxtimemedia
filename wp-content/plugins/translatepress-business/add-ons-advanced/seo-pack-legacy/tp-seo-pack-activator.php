<?php

/**
 * The code that runs during plugin activation.
 */


if(!function_exists('trp_in_sp_activator')){
    function trp_in_sp_activator( $addon ){
        if ( $addon === 'tp-add-on-seo-pack/tp-seo-pack.php' ) {
            trp_in_sp_wpseo_clear_sitemap();
        }
    }

    add_action('trp_add_ons_activate', 'trp_in_sp_activator', 10, 1);
    add_action('trp_add_ons_deactivate','trp_in_sp_activator', 10, 1);
}

if(!function_exists('trp_in_sp_wpseo_clear_sitemap')) {
    function trp_in_sp_wpseo_clear_sitemap() {
        global $wpdb;
        // delete all "yst_sm" transients
        $sql = "
            DELETE
            FROM {$wpdb->options}
            WHERE option_name like '\_transient\_yst\_sm%'
            OR option_name like '\_transient\_timeout\_yst\_sm%'
        ";

        $wpdb->query( $sql );
    }
}
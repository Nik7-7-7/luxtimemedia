<?php

namespace FluentBookingPro\Database;

use FluentBookingPro\Database\Migrations\OrdersItemsMigrator;
use FluentBookingPro\Database\Migrations\BookingOrdersMigrator;
use FluentBookingPro\Database\Migrations\BookingTransactionsMigrator;

class DBMigrator
{
    public static function run($network_wide = false)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        if (is_multisite() && $network_wide) {
            global $wpdb;
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                static::migrate();
                restore_current_blog();
            }
        } else {
            static::migrate();
        }
    }

    private static function migrate()
    {
        BookingOrdersMigrator::migrate();
        OrdersItemsMigrator::migrate();
        BookingTransactionsMigrator::migrate();
    }
}

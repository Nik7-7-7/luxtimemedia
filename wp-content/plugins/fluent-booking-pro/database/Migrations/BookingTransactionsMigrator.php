<?php

namespace FluentBookingPro\Database\Migrations;

class BookingTransactionsMigrator
{
    public static string $tableName = "fcal_transactions";

    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();
        $table = $wpdb->prefix . static::$tableName;
        $indexPrefix = $wpdb->prefix . 'fct_ot_';

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) != $table) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $sql = "CREATE TABLE $table (
                `id` BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `object_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                `object_type` VARCHAR(100) NOT NULL DEFAULT '',
                `transaction_type` varchar(255) DEFAULT 'one_time',
                `subscription_id` int(11) NULL,
                `card_last_4` int(4),
                `card_brand` varchar(255),
                `vendor_charge_id` VARCHAR(192) NOT NULL DEFAULT '',
                `payment_method` VARCHAR(100) NOT NULL DEFAULT '',
                `payment_method_type` VARCHAR(100) NOT NULL DEFAULT '',
                `status` VARCHAR(20) NOT NULL DEFAULT '',
                `total` DECIMAL(18,9) NOT NULL DEFAULT '0.000000000',
                `rate` DECIMAL(10,5) NOT NULL DEFAULT '1.00000',
                `uuid` VARCHAR(100) NOT NULL DEFAULT '',
                `meta` json DEFAULT NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,

                INDEX `{$indexPrefix}_ven_charge_id` (`vendor_charge_id`(64) ASC),
                INDEX `{$indexPrefix}_payment_method_idx` (`payment_method` ASC),
                INDEX `{$indexPrefix}_status_idx` (`status` ASC),
                INDEX `{$indexPrefix}_object_id_idx` (`object_id` ASC)
            ) $charsetCollate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            dbDelta($sql);
        }
    }
}

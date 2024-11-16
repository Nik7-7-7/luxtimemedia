<?php

namespace FluentBookingPro\Database\Migrations;

class BookingOrdersMigrator
{
    public static string $tableName = 'fcal_orders';

    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();
        $table = $wpdb->prefix . static::$tableName;
        $indexPrefix = $wpdb->prefix . 'fct_ord_';

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) != $table) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $sql = "CREATE TABLE $table (
                `id` BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
                `parent_id` BIGINT UNSIGNED NOT NULL,
                `order_number` VARCHAR(255) NOT NULL DEFAULT '',
                `type` VARCHAR(20) NOT NULL DEFAULT 'sale',
                `customer_id` BIGINT UNSIGNED NOT NULL,
                `payment_method` VARCHAR(100) NOT NULL,
                `payment_mode` VARCHAR(100) NOT NULL,
                `payment_method_type` VARCHAR(100) NOT NULL,
                `payment_method_title` VARCHAR(100) NOT NULL,
                `currency` VARCHAR(10) NOT NULL,
                `subtotal` DECIMAL(18,9) NOT NULL DEFAULT '0.000000000',
                `discount_tax` DECIMAL(18,9) NOT NULL DEFAULT '0.000000000',
                `discount_total` DECIMAL(18,9) NOT NULL DEFAULT '0.000000000',
                `shipping_tax` DECIMAL(18,9) NOT NULL DEFAULT '0.000000000',
                `shipping_total` DECIMAL(18,9) NOT NULL DEFAULT '0.000000000',
                `tax_total` DECIMAL(18,9) NOT NULL DEFAULT '0.000000000',
                `total_amount` DECIMAL(18,9) NOT NULL DEFAULT '0.000000000',
                `total_paid` DECIMAL(18,9) NOT NULL DEFAULT '0.000000000',
                `rate` DECIMAL(18,9) NOT NULL DEFAULT '1.000000000',
                `note` TEXT NOT NULL,
                `ip_address` TEXT NOT NULL,
                `completed_at` DATETIME NULL DEFAULT NULL,
                `refunded_at` DATETIME NULL DEFAULT NULL,
                `uuid` VARCHAR(100) NOT NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,

                INDEX `{$indexPrefix}_order_number` (`order_number`(191) ASC),
                INDEX `{$indexPrefix}_status_type` (`type` ASC),
                INDEX `{$indexPrefix}_customer_id` (`customer_id` ASC)
            ) $charsetCollate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            dbDelta($sql);
        }
    }
}

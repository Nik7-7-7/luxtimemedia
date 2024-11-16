<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\WP\InstallActions\DB\Tax;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\AbstractDatabaseTable;

/**
 * Class TaxesTable
 *
 * @package AmeliaBooking\Infrastructure\WP\InstallActions\DB\Tax
 */
class TaxesTable extends AbstractDatabaseTable
{

    const TABLE = 'taxes';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function buildTable()
    {
        $table = self::getTableName();

        return "CREATE TABLE {$table} (
                   `id` int(11) NOT NULL AUTO_INCREMENT,
                   `name` VARCHAR(255) NOT NULL COLLATE utf8_bin,
                   `amount` DOUBLE NOT NULL,
                   `type` ENUM('percentage', 'fixed') NOT NULL,
                   `status` ENUM('hidden', 'visible') NOT NULL,
                    PRIMARY KEY (`id`)
                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}

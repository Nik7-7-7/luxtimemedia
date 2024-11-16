<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Services\Placeholder;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Interface PlaceholderServiceInterface
 *
 * @package AmeliaBooking\Application\Services\Placeholder
 */
interface PlaceholderServiceInterface
{
    /**
     *
     * @return array
     *
     * @throws ContainerException
     */
    public function getEntityPlaceholdersDummyData($type);

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param array        $appointment
     * @param int          $bookingKey
     * @param string       $type
     * @param AbstractUser $customer
     * @param array        $allBookings
     *
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    public function getPlaceholdersData($appointment, $bookingKey = null, $type = null, $customer = null, $allBookings = null);

    /**
     * @param array $bookingArray
     * @param array $entity
     *
     * @return array
     */
    public function getAmountData(&$bookingArray, $entity);
}

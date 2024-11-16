<?php

namespace AmeliaBooking\Infrastructure\Repository\Bookable\Service;

use AmeliaBooking\Domain\Entity\Bookable\Service\Package;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomer;
use AmeliaBooking\Domain\Factory\Bookable\Service\PackageCustomerFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\AbstractRepository;

/**
 * Class PackageCustomerRepository
 *
 * @package AmeliaBooking\Infrastructure\Repository\Bookable\Service
 */
class PackageCustomerRepository extends AbstractRepository
{
    const FACTORY = PackageCustomerFactory::class;

    /**
     * @param PackageCustomer $entity
     *
     * @return int
     * @throws QueryExecutionException
     */
    public function add($entity)
    {
        $data = $entity->toArray();

        $params = [
            ':packageId'        => $data['packageId'],
            ':customerId'       => $data['customerId'],
            ':price'            => $data['price'],
            ':tax'              => !empty($data['tax']) ? json_encode($data['tax']) : null,
            ':start'            => $data['start'],
            ':end'              => $data['end'],
            ':purchased'        => $data['purchased'],
            ':bookingsCount'    => $data['bookingsCount'],
            ':couponId'         => $data['couponId'],
        ];

        try {
            $statement = $this->connection->prepare(
                "INSERT INTO {$this->table}
                (`packageId`, `customerId`, `price`, `tax`, `start`, `end`, `purchased`, `status`, `bookingsCount`, `couponId`)
                VALUES
                (:packageId, :customerId, :price, :tax, :start, :end, :purchased, 'approved', :bookingsCount, :couponId)"
            );

            $res = $statement->execute($params);

            if (!$res) {
                throw new QueryExecutionException('Unable to add data in ' . __CLASS__);
            }
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to add data in ' . __CLASS__, $e->getCode(), $e);
        }

        return $this->connection->lastInsertId();
    }


    /**
     * @param Package $package
     * @param int $customerId
     * @param array $limitPerCustomer
     * @param boolean $packageSpecific
     * @return int
     * @throws QueryExecutionException
     */
    public function getUserPackageCount($package, $customerId, $limitPerCustomer, $packageSpecific)
    {
        $params = [
            ':customerId' => $customerId
        ];

        $startDate = DateTimeService::getNowDateTimeObject()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i');

        $intervalString = "interval " . $limitPerCustomer['period'] . " " . $limitPerCustomer['timeFrame'];

        $where = "(STR_TO_DATE('" . $startDate . "', '%Y-%m-%d %H:%i:%s') BETWEEN " .
            "(pc.purchased - " . $intervalString . " + interval 1 second) AND " .
            "(pc.purchased + " . $intervalString . " - interval 1 second))";  //+ interval 2 day

        if ($packageSpecific) {
            $where .= " AND pc.packageId = :packageId";
            $params[':packageId'] = $package->getId()->getValue();
        }

        try {
            $statement = $this->connection->prepare(
                "SELECT COUNT(DISTINCT pc.id) AS count
                    FROM {$this->table} pc
                    WHERE pc.customerId = :customerId AND {$where} AND pc.status = 'approved'
                "
            );

            $statement->execute($params);

            $rows = $statement->fetch()['count'];
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find by id in ' . __CLASS__, $e->getCode(), $e);
        }

        return $rows;
    }

    /**
     * @param array $criteria
     *
     * @return array
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getFiltered($criteria)
    {
        $params = [];

        $where = [];

        if (!empty($criteria['customerId'])) {
            $params[':customerId'] = $criteria['customerId'];

            $where[] = 'pc.customerId = :customerId';
        }

        if (array_key_exists('bookingStatus', $criteria)) {
            $where[] = 'pc.status = :bookingStatus';
            $params[':bookingStatus'] = $criteria['bookingStatus'];
        }

        if (isset($criteria['couponId'])) {
            $where[] = "pc.couponId = {$criteria['couponId']}";
        }

        $where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        try {
            $statement = $this->connection->prepare(
                "SELECT 
                pc.customerId
                FROM {$this->table} pc
                $where"
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();
        } catch (Exception $e) {
            throw new QueryExecutionException('Unable to find by id in ' . __CLASS__, $e->getCode(), $e);
        }

        return $rows;
    }

}

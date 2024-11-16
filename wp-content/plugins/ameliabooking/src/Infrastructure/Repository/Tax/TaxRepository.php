<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\Repository\Tax;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Tax\Tax;
use AmeliaBooking\Domain\Factory\Tax\TaxFactory;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Connection;
use AmeliaBooking\Infrastructure\Repository\AbstractStatusRepository;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\Tax\TaxesToEntitiesTable;

/**
 * Class TaxRepository
 *
 * @package AmeliaBooking\Infrastructure\Repository\Tax
 */
class TaxRepository extends AbstractStatusRepository
{

    const FACTORY = TaxFactory::class;

    /** @var string */
    protected $taxesToEntitiesTable;

    /**
     * @param Connection $connection
     * @param string     $table
     * @throws InvalidArgumentException
     */
    public function __construct(
        Connection $connection,
        $table
    ) {
        parent::__construct($connection, $table);

        $this->taxesToEntitiesTable = TaxesToEntitiesTable::getTableName();
    }

    /**
     * @param Tax $entity
     *
     * @return string|false
     * @throws QueryExecutionException
     */
    public function add($entity)
    {
        $data = $entity->toArray();

        $params = [
            ':name'   => $data['name'],
            ':amount' => $data['amount'],
            ':type'   => $data['type'],
            ':status' => $data['status'],
        ];

        try {
            $statement = $this->connection->prepare(
                "INSERT INTO
                {$this->table} 
                (
                `name`, `amount`, `type`, `status`  
                ) VALUES (
                :name, :amount, :type, :status  
                )"
            );


            $response = $statement->execute($params);
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to add data in ' . __CLASS__, $e->getCode(), $e);
        }

        if (!$response) {
            throw new QueryExecutionException('Unable to add data in ' . __CLASS__);
        }

        return $this->connection->lastInsertId();
    }

    /**
     * @param int $id
     * @param Tax $entity
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function update($id, $entity)
    {
        $data = $entity->toArray();

        $params = [
            ':name'   => $data['name'],
            ':amount' => $data['amount'],
            ':type'   => $data['type'],
            ':status' => $data['status'],
            ':id'     => $id,
        ];

        try {
            $statement = $this->connection->prepare(
                "UPDATE {$this->table}
                SET
                `name`   = :name,
                `amount` = :amount,
                `type`   = :type,
                `status` = :status
                WHERE
                id = :id"
            );

            $response = $statement->execute($params);
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to save data in ' . __CLASS__ . $e->getMessage());
        }

        if (!$response) {
            throw new QueryExecutionException('Unable to save data in ' . __CLASS__);
        }

        return $response;
    }

    /**
     * @param int $id
     *
     * @return Tax
     * @throws QueryExecutionException
     * @throws NotFoundException
     */
    public function getById($id)
    {
        try {
            $statement = $this->connection->prepare(
                "SELECT
                    t.id AS tax_id,
                    t.name AS tax_name,
                    t.amount AS tax_amount,
                    t.type AS tax_type,
                    t.status AS tax_status,
                    te.entityId AS tax_entityId,
                    te.entityType AS tax_entityType
                FROM {$this->table} t
                LEFT JOIN {$this->taxesToEntitiesTable} te ON te.taxId = t.id
                WHERE t.id = :taxId"
            );

            $statement->bindParam(':taxId', $id);

            $statement->execute();

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to find by id in ' . __CLASS__, $e->getCode(), $e);
        }

        if (!$rows) {
            throw new NotFoundException('Data not found in ' . __CLASS__);
        }

        return call_user_func([static::FACTORY, 'createCollection'], $rows)->getItem($id);
    }

    /**
     * @param array $criteria
     *
     * @return Collection
     * @throws QueryExecutionException
     */
    public function getWithEntities($criteria)
    {
        $where = !empty($criteria['ids']) ? "WHERE t.id IN (" . implode(', ', $criteria['ids']) . ")" : '';

        try {
            $statement = $this->connection->prepare(
                "SELECT
                    t.id AS tax_id,
                    t.name AS tax_name,
                    t.amount AS tax_amount,
                    t.type AS tax_type,
                    t.status AS tax_status,
                    te.entityId AS tax_entityId,
                    te.entityType AS tax_entityType
                    FROM {$this->table} t
                    LEFT JOIN {$this->taxesToEntitiesTable} te ON te.taxId = t.id
                    {$where}
                    ORDER BY t.id"
            );

            $statement->execute();

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to get data from ' . __CLASS__, $e->getCode(), $e);
        }

        return call_user_func([static::FACTORY, 'createCollection'], $rows);
    }

    /**
     * @param array $criteria
     * @param int   $itemsPerPage
     *
     * @return Collection
     * @throws QueryExecutionException
     */
    public function getFiltered($criteria, $itemsPerPage)
    {
        $params = [];

        $where = [];

        if (!empty($criteria['search'])) {
            $params[':search'] = "%{$criteria['search']}%";

            $where[] = 'UPPER(t.name) LIKE UPPER(:search)';
        }

        if (!empty($criteria['services'])) {
            $queryServices = [];

            foreach ($criteria['services'] as $index => $value) {
                $param = ':service' . $index;

                $queryServices[] = $param;

                $params[$param] = $value;
            }

            $where[] = "t.id IN (
                    SELECT taxId FROM {$this->taxesToEntitiesTable} 
                    WHERE entityId IN (" . implode(', ', $queryServices) . ") AND entityType = 'service'
                )";
        }

        if (!empty($criteria['extras'])) {
            $queryExtras = [];

            foreach ($criteria['extras'] as $index => $value) {
                $param = ':extra' . $index;

                $queryExtras[] = $param;

                $params[$param] = $value;
            }

            $where[] = "t.id IN (
                    SELECT taxId FROM {$this->taxesToEntitiesTable} 
                    WHERE entityId IN (" . implode(', ', $queryExtras) . ") AND entityType = 'extra'
                )";
        }

        if (!empty($criteria['events'])) {
            $queryEvents = [];

            foreach ($criteria['events'] as $index => $value) {
                $param = ':event' . $index;

                $queryEvents[] = $param;

                $params[$param] = $value;
            }

            $where[] = "t.id IN (
                    SELECT taxId FROM {$this->taxesToEntitiesTable} 
                    WHERE entityId IN (" . implode(', ', $queryEvents) . ") AND entityType = 'event'
                )";
        }

        if (!empty($criteria['packages'])) {
            $queryPackages = [];

            foreach ((array)$criteria['packages'] as $index => $value) {
                $param = ':package' . $index;

                $queryPackages[] = $param;

                $params[$param] = $value;
            }

            $where[] = "t.id IN (
                    SELECT taxId FROM {$this->taxesToEntitiesTable} 
                    WHERE entityId IN (" . implode(', ', $queryPackages) . ") AND entityType = 'package'
                )";
        }


        $where = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $limit = $this->getLimit(
            !empty($criteria['page']) ? (int)$criteria['page'] : 0,
            (int)$itemsPerPage
        );

        try {
            $statement = $this->connection->prepare(
                "SELECT
                t.id AS tax_id,
                t.name AS tax_name,
                t.amount AS tax_amount,
                t.type AS tax_type,
                t.status AS tax_status
                FROM {$this->table} t
                {$where}
                {$limit}"
            );

            $statement->execute($params);

            $rows = $statement->fetchAll();
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to get data from ' . __CLASS__, $e->getCode(), $e);
        }

        return call_user_func([static::FACTORY, 'createCollection'], $rows);
    }

    /**
     * @param array $criteria
     *
     * @return mixed
     * @throws QueryExecutionException
     */
    public function getCount($criteria)
    {
        $params = [];

        $where = [];

        if (!empty($criteria['search'])) {
            $params[':search'] = "%{$criteria['search']}%";

            $where[] = 't.name LIKE :search';
        }

        if (!empty($criteria['services'])) {
            $queryServices = [];

            foreach ((array)$criteria['services'] as $index => $value) {
                $param = ':service' . $index;

                $queryServices[] = $param;

                $params[$param] = $value;
            }

            $where[] = "t.id IN (
                SELECT taxId FROM {$this->taxesToEntitiesTable} 
                WHERE entityId IN (" . implode(', ', $queryServices) . ") AND entityType = 'service'
            )";
        }

        if (!empty($criteria['events'])) {
            $queryEvents = [];

            foreach ((array)$criteria['events'] as $index => $value) {
                $param = ':event' . $index;

                $queryEvents[] = $param;

                $params[$param] = $value;
            }

            $where[] = "t.id IN (
                SELECT taxId FROM {$this->taxesToEntitiesTable} 
                WHERE entityId IN (" . implode(', ', $queryEvents) . ") AND entityType = 'event'
            )";
        }

        if (!empty($criteria['packages'])) {
            $queryPackages = [];

            foreach ((array)$criteria['packages'] as $index => $value) {
                $param = ':package' . $index;

                $queryPackages[] = $param;

                $params[$param] = $value;
            }

            $where[] = "t.id IN (
                SELECT taxId FROM {$this->taxesToEntitiesTable} 
                WHERE entityId IN (" . implode(', ', $queryPackages) . ") AND entityType = 'package'
            )";
        }

        $where = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        try {
            $statement = $this->connection->prepare(
                "SELECT COUNT(*) AS count
                FROM {$this->table} t
                {$where}"
            );

            $statement->execute($params);

            $row = $statement->fetch()['count'];
        } catch (\Exception $e) {
            throw new QueryExecutionException('Unable to get data from ' . __CLASS__, $e->getCode(), $e);
        }

        return $row;
    }
}

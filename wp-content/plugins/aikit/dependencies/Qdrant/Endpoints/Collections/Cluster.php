<?php
/**
 * Cluster
 *
 * https://qdrant.github.io/qdrant/redoc/#tag/cluster/operation/cluster_status
 *
 * @since     Mar 2023
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */
namespace AIKit\Dependencies\Qdrant\Endpoints\Collections;

use AIKit\Dependencies\Qdrant\Endpoints\AbstractEndpoint;
use AIKit\Dependencies\Qdrant\Exception\InvalidArgumentException;
use AIKit\Dependencies\Qdrant\Response;

class Cluster extends AbstractEndpoint
{
    /**
     * # Collection cluster info
     * Get cluster information for a collection
     *
     * @throws InvalidArgumentException
     */
    public function info(): Response
    {
        return $this->client->execute(
            $this->createRequest('GET', '/collections/' . $this->getCollectionName() . '/cluster')
        );
    }

    /**
     * # Update collection cluster setup
     *
     * @throws InvalidArgumentException
     */
    public function update(array $params, array $queryParams = []): Response
    {
        return $this->client->execute(
            $this->createRequest(
                'POST',
                '/collections/' . $this->getCollectionName() . '/cluster' . $this->queryBuild($queryParams),
                $params)
        );
    }
}
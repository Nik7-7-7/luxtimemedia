<?php
/**
 * @since     Mar 2023
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */
namespace AIKit\Dependencies\Qdrant;

use AIKit\Dependencies\Psr\Http\Message\RequestInterface;
use AIKit\Dependencies\Qdrant\Endpoints\Cluster;
use AIKit\Dependencies\Qdrant\Endpoints\Collections;
use AIKit\Dependencies\Qdrant\Endpoints\Service;
use AIKit\Dependencies\Qdrant\Endpoints\Snapshots;
use AIKit\Dependencies\Qdrant\Http\HttpClientInterface;

class Qdrant implements ClientInterface
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function collections(string $collectionName = null): Collections
    {
        return (new Collections($this->client))->setCollectionName($collectionName);
    }

    public function snapshots(): Snapshots
    {
        return new Snapshots($this->client);
    }

    public function cluster(): Cluster
    {
        return new Cluster($this->client);
    }

    public function service(): Service
    {
        return new Service($this->client);
    }

    public function execute(RequestInterface $request): Response
    {
        return $this->client->execute($request);
    }
}
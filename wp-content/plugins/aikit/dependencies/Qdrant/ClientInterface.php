<?php
/**
 * @since     Mar 2023
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */
namespace AIKit\Dependencies\Qdrant;

use AIKit\Dependencies\Qdrant\Endpoints\Cluster;
use AIKit\Dependencies\Qdrant\Endpoints\Collections;
use AIKit\Dependencies\Qdrant\Endpoints\Service;
use AIKit\Dependencies\Qdrant\Endpoints\Snapshots;
use AIKit\Dependencies\Qdrant\Http\HttpClientInterface;

interface ClientInterface extends HttpClientInterface
{
    public function collections(string $collectionName = null): Collections;

    public function snapshots(): Snapshots;

    public function cluster(): Cluster;

    public function service(): Service;
}
<?php
/**
 * Aliases
 *
 * https://qdrant.tech/documentation/collections/#collection-aliases
 *
 * @since     Mar 2023
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */
namespace AIKit\Dependencies\Qdrant\Endpoints\Collections;

use AIKit\Dependencies\Qdrant\Endpoints\AbstractEndpoint;
use AIKit\Dependencies\Qdrant\Exception\InvalidArgumentException;
use AIKit\Dependencies\Qdrant\Models\Request\AliasActions;
use AIKit\Dependencies\Qdrant\Response;

class Aliases extends AbstractEndpoint
{
    /**
     * Update aliases of the collections
     *
     * @throws InvalidArgumentException
     */
    public function actions(AliasActions $actions, array $queryParams = []): Response
    {
        return $this->client->execute(
            $this->createRequest(
                'POST',
                '/collections/aliases' . $this->queryBuild($queryParams),
                ['actions' => $actions->toArray()]
            )
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function aliases(): Response
    {
        return $this->client->execute(
            $this->createRequest('GET', '/collections/'.$this->getCollectionName().'/aliases')
        );
    }
}
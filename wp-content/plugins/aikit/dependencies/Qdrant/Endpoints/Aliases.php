<?php
/**
 * Aliases
 *
 * @since     Apr 2023
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */

namespace AIKit\Dependencies\Qdrant\Endpoints;

use AIKit\Dependencies\Qdrant\Exception\InvalidArgumentException;
use AIKit\Dependencies\Qdrant\Response;

class Aliases extends AbstractEndpoint
{
    /**
     * @throws InvalidArgumentException
     */
    public function all(): Response
    {
        return $this->client->execute($this->createRequest('GET', '/aliases'));
    }
}
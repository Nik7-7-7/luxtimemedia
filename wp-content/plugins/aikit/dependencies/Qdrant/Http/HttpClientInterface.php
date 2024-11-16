<?php
/**
 * @since     Mar 2023
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */

namespace AIKit\Dependencies\Qdrant\Http;

use AIKit\Dependencies\Psr\Http\Message\RequestInterface;
use AIKit\Dependencies\Qdrant\Response;

interface HttpClientInterface
{
    public function execute(RequestInterface $request): Response;
}
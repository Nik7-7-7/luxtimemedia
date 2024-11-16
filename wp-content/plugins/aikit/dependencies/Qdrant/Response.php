<?php
/**
 * @since     Mar 2023
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */

namespace AIKit\Dependencies\Qdrant;

use ArrayAccess;
use Exception;
use JsonException;
use AIKit\Dependencies\Psr\Http\Message\ResponseInterface;
use AIKit\Dependencies\Qdrant\Exception\InvalidArgumentException;
use AIKit\Dependencies\Qdrant\Exception\ServerException;

class Response implements ArrayAccess
{
    protected array $raw;

    protected ResponseInterface $response;

    /**
     * @throws ServerException
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;

        if ($response->getHeaderLine('content-type') === 'application/json') {
            $this->raw = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } else {
            $this->raw = [
                'content' => $response->getBody()->getContents()
            ];
        }

        if ($this->response->getStatusCode() >= 400 && $this->response->getStatusCode() < 500) {
            throw (new InvalidArgumentException($this->raw['status']['error'] ?? 'Invalid argument exception'))->setResponse($this);
        }

        if ($this->response->getStatusCode() >= 500 && $this->response->getStatusCode() < 500) {
            throw (new ServerException())->setResponse($this);
        }
    }

    public function __toArray(): array
    {
        return $this->raw;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->raw[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->raw[$offset];
    }

    /**
     * @throws Exception
     */
    public function offsetSet($offset, $value): void
    {
        throw new Exception('You can not change the response');
    }

    /**
     * @throws Exception
     */
    public function offsetUnset($offset): void
    {
        throw new Exception('You can not change the response');
    }
}
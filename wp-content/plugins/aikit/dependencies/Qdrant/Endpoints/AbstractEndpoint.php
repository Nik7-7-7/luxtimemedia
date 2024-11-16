<?php
/**
 * AbstractEndpoint
 *
 * @since     Mar 2023
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */
namespace AIKit\Dependencies\Qdrant\Endpoints;

use AIKit\Dependencies\GuzzleHttp\Psr7\HttpFactory;
use AIKit\Dependencies\GuzzleHttp\Psr7\Query;
use AIKit\Dependencies\Psr\Http\Message\RequestInterface;
use AIKit\Dependencies\Qdrant\Exception\InvalidArgumentException;
use AIKit\Dependencies\Qdrant\Http\HttpClientInterface;

abstract class AbstractEndpoint
{
    protected HttpClientInterface $client;

    protected ?string $collectionName = null;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function setCollectionName(?string $collectionName)
    {
        $this->collectionName = $collectionName;

        return $this;
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public function getCollectionName(): string
    {
        if ($this->collectionName === null) {
            throw new InvalidArgumentException('You need to specify the collection name');
        }
        return $this->collectionName;
    }

    protected function queryBuild(array $params): string
    {
        if ($params) {
            return '?' . Query::build($params);
        }
        return '';
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function createRequest(string $method, string $uri, array $body = []): RequestInterface
    {
        $httpFactory = new HttpFactory();
        $request = $httpFactory->createRequest($method, $uri);
        if ($body) {
            try {
                $request = $request->withBody(
                    $httpFactory->createStream(json_encode($body, JSON_THROW_ON_ERROR))
                );
            } catch (\JsonException $e) {
                throw new InvalidArgumentException('Json parse error!');
            }
        }

        return $request;
    }
}
<?php

// File generated from our OpenAPI spec

namespace AmeliaStripe\Service;

class MandateService extends \AmeliaStripe\Service\AbstractService
{
    /**
     * Retrieves a Mandate object.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\AmeliaStripe\Util\RequestOptions $opts
     *
     * @throws \AmeliaStripe\Exception\ApiErrorException if the request fails
     *
     * @return \AmeliaStripe\Mandate
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/mandates/%s', $id), $params, $opts);
    }
}

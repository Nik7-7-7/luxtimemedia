<?php

// File generated from our OpenAPI spec

namespace AmeliaStripe\Service;

class SetupAttemptService extends \AmeliaStripe\Service\AbstractService
{
    /**
     * Returns a list of SetupAttempts associated with a provided SetupIntent.
     *
     * @param null|array $params
     * @param null|array|\AmeliaStripe\Util\RequestOptions $opts
     *
     * @throws \AmeliaStripe\Exception\ApiErrorException if the request fails
     *
     * @return \AmeliaStripe\Collection<\AmeliaStripe\SetupAttempt>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/setup_attempts', $params, $opts);
    }
}

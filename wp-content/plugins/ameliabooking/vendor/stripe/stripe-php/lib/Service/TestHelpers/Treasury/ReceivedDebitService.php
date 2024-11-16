<?php

// File generated from our OpenAPI spec

namespace AmeliaStripe\Service\TestHelpers\Treasury;

class ReceivedDebitService extends \AmeliaStripe\Service\AbstractService
{
    /**
     * Use this endpoint to simulate a test mode ReceivedDebit initiated by a third
     * party. In live mode, you can’t directly create ReceivedDebits initiated by third
     * parties.
     *
     * @param null|array $params
     * @param null|array|\AmeliaStripe\Util\RequestOptions $opts
     *
     * @throws \AmeliaStripe\Exception\ApiErrorException if the request fails
     *
     * @return \AmeliaStripe\Treasury\ReceivedDebit
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/test_helpers/treasury/received_debits', $params, $opts);
    }
}

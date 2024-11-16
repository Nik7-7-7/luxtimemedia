<?php

// File generated from our OpenAPI spec

namespace AmeliaStripe\Service\Treasury;

class ReceivedDebitService extends \AmeliaStripe\Service\AbstractService
{
    /**
     * Returns a list of ReceivedDebits.
     *
     * @param null|array $params
     * @param null|array|\AmeliaStripe\Util\RequestOptions $opts
     *
     * @throws \AmeliaStripe\Exception\ApiErrorException if the request fails
     *
     * @return \AmeliaStripe\Collection<\AmeliaStripe\Treasury\ReceivedDebit>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/treasury/received_debits', $params, $opts);
    }

    /**
     * Retrieves the details of an existing ReceivedDebit by passing the unique
     * ReceivedDebit ID from the ReceivedDebit list.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\AmeliaStripe\Util\RequestOptions $opts
     *
     * @throws \AmeliaStripe\Exception\ApiErrorException if the request fails
     *
     * @return \AmeliaStripe\Treasury\ReceivedDebit
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/treasury/received_debits/%s', $id), $params, $opts);
    }
}

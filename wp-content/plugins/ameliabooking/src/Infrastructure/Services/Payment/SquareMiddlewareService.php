<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\Services\Payment;

/**
 * Class SquareMiddlewareService
 */
class SquareMiddlewareService
{

    /**
     *
     * @return boolean
     *
     * @throws \Exception
     */
    public function decrypt($savedAccessToken)
    {
        $ch = curl_init(AMELIA_MIDDLEWARE_API_URL . 'square/decrypt');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            [
                'access_token'  => $savedAccessToken['access_token'],
                'refresh_token' => $savedAccessToken['refresh_token']
            ]
        );

        $response = curl_exec($ch);

        if ($response && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
            $response = json_decode($response, true);
            set_transient('amelia_square_access_token', ['access_token' => $response['result']['access_token'], 'refresh_token' => $response['result']['refresh_token']], 604800);
        } else {
            $response = null;
        }

        curl_close($ch);

        return $response['result'];
    }

    /**
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getAccessToken($savedAccessToken)
    {
        $accessToken = get_transient('amelia_square_access_token');
        if ($accessToken === false) {
            $accessToken = $this->decrypt($savedAccessToken);
        }
        return $accessToken;
    }

    /**
     *
     * @return boolean
     *
     * @throws \Exception
     */
    public function disconnectAccount($savedAccessToken, $testMode)
    {
        $accessToken = $this->getAccessToken($savedAccessToken);

        $ch = curl_init(AMELIA_MIDDLEWARE_API_URL . 'square/revoke?testMode=' . $testMode);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            [
                'access_token' => $accessToken['access_token'],
            ]
        );

        $response = curl_exec($ch);

        curl_close($ch);

        return true;
    }


    /**
     *
     * @param string $authCode
     * @param string $state
     *
     * @return boolean
     *
     * @throws \Exception
     */
    public function refreshAccessToken($savedAccessToken, $testMode)
    {
        $accessToken = $this->getAccessToken($savedAccessToken);

        $ch = curl_init(AMELIA_MIDDLEWARE_API_URL . 'square/refresh?testMode=' . $testMode);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            [
                'refresh_token' => $accessToken['refresh_token'],
            ]
        );

        $response = curl_exec($ch);

        if ($response && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
            $response = json_decode($response, true);
        } else {
            $response = null;
        }

        curl_close($ch);

        return $response;
    }

    public function getAuthUrl($testMode)
    {
        $ch = curl_init(
            AMELIA_MIDDLEWARE_API_URL . 'square/authorization/url?testMode=' . $testMode . '&admin_ajax_url=' . urlencode(admin_url('admin-ajax.php', ''))
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        $url = null;
        if ($response) {
            $response = json_decode($response, true);
            $url      = $response['result'];
        }

        curl_close($ch);

        return $url;
    }
}

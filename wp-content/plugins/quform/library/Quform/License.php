<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_License
{
    /**
     * @var Quform_Api
     */
    protected $api;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param  Quform_Api      $api
     * @param  Quform_Options  $options
     */
    public function __construct(Quform_Api $api, Quform_Options $options)
    {
        $this->api = $api;
        $this->options = $options;
    }

    /**
     * Verify the purchase code via Ajax and set the license status
     */
    public function verifyPurchaseCode()
    {
        $this->validateRequest();

        $response = $this->api->post('/verify', array(
            'site_url' => Quform::base64UrlEncode(site_url()),
            'purchase_code' => $_POST['purchase_code']
        ));

        if (is_array($response)) {
            if (isset($response['type'])) {
                if ($response['type'] == 'success') {
                    $this->setKey($response['license_key']);

                    delete_site_transient('quform_latest_version_info');
                    delete_site_transient('update_plugins');

                    wp_send_json(array(
                        'type' => 'success',
                        'status' => $this->getStatus(),
                        'message' => __('License key successfully verified', 'quform')
                    ));
                } else if ($response['type'] == 'error') {
                    $this->revoke();

                    delete_site_transient('quform_latest_version_info');
                    delete_site_transient('update_plugins');

                    wp_send_json(array(
                        'type' => 'error',
                        'status' => $this->getStatus(),
                        'message' => __('Invalid license key', 'quform')
                    ));
                }
            } else if (isset($response['code'])) {
                switch ($response['code']) {
                    case 'rest_invalid_param':
                        $this->revoke();

                        delete_site_transient('quform_latest_version_info');
                        delete_site_transient('update_plugins');

                        wp_send_json(array(
                            'type' => 'error',
                            'status' => $this->getStatus(),
                            'message' => __('Invalid license key', 'quform')
                        ));
                        break;
                }
            }
        }

        wp_send_json(array(
            'type' => 'error',
            'message' => wp_kses(sprintf(
                /* translators: %1$s: open link tag, %2$s: close link tag */
                __('An error occurred verifying the license key, please try again. If this problem persists, see %1$sthis page%2$s.', 'quform'),
                '<a href="https://support.themecatcher.net/quform-wordpress-v2/troubleshooting/common-problems/an-error-occurred-verifying-the-license-key">',
                '</a>'
            ), array('a' => array('href' => array())))
        ));
    }

    /**
     * Get the license key
     *
     * @return string
     */
    public function getKey()
    {
        return defined('QUFORM_LICENSE_KEY') ? QUFORM_LICENSE_KEY : $this->options->get('licenseKey');
    }

    /**
     * Set the license key
     *
     * @param   string  $key
     * @return  $this
     */
    protected function setKey($key)
    {
        $this->options->set('licenseKey', $key);

        return $this;
    }

    /**
     * Is the current license valid
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->getStatus() != 'unlicensed';
    }

    /**
     * Revoke the license
     *
     * @return $this
     */
    public function revoke()
    {
        $this->setKey('');

        return $this;
    }

    /**
     * Get the status of the license
     *
     * @return string
     */
    public function getStatus()
    {
        if (Quform::isNonEmptyString($this->getKey())) {
            return 'valid';
        } else if (apply_filters('quform_is_bundled', false)) {
            return 'bundled';
        }

        return 'unlicensed';
    }

    /**
     * @return string
     */
    public function getStatusString()
    {
        switch ($this->getStatus()) {
            case 'valid':
                return __('Valid', 'quform');
            case 'bundled':
                return __('Valid (bundled)', 'quform');
            case 'unlicensed':
                return __('Unlicensed', 'quform');
        }
    }

    /**
     * Validate the request to verify the purchase code
     */
    protected function validateRequest()
    {
        if ( ! isset($_POST['purchase_code']) || ! is_string($_POST['purchase_code'])) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ($_POST['purchase_code'] === '') {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Please enter a license key', 'quform')
            ));
        }

        if ( ! current_user_can('quform_settings')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_verify_purchase_code', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }
}

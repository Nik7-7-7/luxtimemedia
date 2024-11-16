<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Updater
{
    /**
     * How long to cache the latest version information
     * @var int
     */
    const CACHE_TIME = 43200; // 12 hours

    /**
     * @var Quform_Api
     */
    protected $api;

    /**
     * @var Quform_License
     */
    protected $license;

    public function __construct(Quform_Api $api, Quform_License $license)
    {
        $this->api = $api;
        $this->license = $license;
    }

    /**
     * Add the update information to the transient object if an update exists
     *
     * @param  object $transient
     * @return object
     */
    public function setUpdateTransient($transient)
    {
        if (empty($transient) || !is_object($transient)) {
            return $transient;
        }

        $latestVersionInfo = $this->getLatestVersionInfo();

        if ($latestVersionInfo && version_compare(QUFORM_VERSION, $latestVersionInfo->new_version, '<')) {
            $transient->response[QUFORM_BASENAME] = $latestVersionInfo;
        } else {
            unset($transient->response[QUFORM_BASENAME]);

            $transient->no_update[QUFORM_BASENAME] = (object) array(
                'id' => QUFORM_NAME,
                'slug' => QUFORM_NAME,
                'plugin' => QUFORM_BASENAME,
                'new_version' => QUFORM_VERSION,
                'url' => 'https://www.quform.com/',
                'package' => ''
            );
        }

        return $transient;
    }

    /**
     * Validate the request to check for an update
     */
    protected function validateCheckForUpdateRequest()
    {
        if ( ! current_user_can('quform_settings')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_manual_update_check', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Checks the for the latest version information for the Settings page
     */
    public function checkForUpdate()
    {
        $this->validateCheckForUpdateRequest();

        delete_site_transient('quform_latest_version_info');
        delete_site_transient('update_plugins');

        $latestVersionInfo = $this->getLatestVersionInfo(false);

        if ( ! $latestVersionInfo || ! isset($latestVersionInfo->new_version)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Could not find an updated version', 'quform')
            ));
        }

        if (version_compare(QUFORM_VERSION, $latestVersionInfo->new_version, '<')) {
            wp_send_json(array(
                'type' => 'success',
                'message' => wp_kses(sprintf(
                    /* translators: %1$s: the new version, %2$s: open link tag, %3$s: close link tag */
                    __('An update to version %1$s is available, %2$svisit the Plugins page%3$s to update.', 'quform'),
                    $latestVersionInfo->new_version,
                    sprintf('<a href="%s">', esc_url(admin_url('plugins.php'))),
                    '</a>'
                ), array('a' => array('href' => array())))
            ));
        } else {
            wp_send_json(array(
                'type' => 'success',
                'message' => __('You are using the latest version.', 'quform')
            ));
        }
    }

    /**
     * Get the latest version information
     *
     * @param   boolean $cache    Whether the information should be fetched from the cache if available
     * @return  boolean|StdClass  The version information object or false on failure
     */
    protected function getLatestVersionInfo($cache = true)
    {
        $latestVersionInfo = $cache ? get_site_transient('quform_latest_version_info') : false;

        if ( ! $latestVersionInfo) {
            // Fetch fresh version info
            $response = $this->api->get('/update-check', array(
                'license_key' => $this->license->getKey(),
                'site_url' => Quform::base64UrlEncode(site_url())
            ));

            if (is_array($response)) {
                if (isset($response['revoke'])) {
                    $this->license->revoke();
                    unset($response['revoke']);
                }

                $latestVersionInfo = (object) $response;
                $latestVersionInfo->slug = QUFORM_NAME;
                $latestVersionInfo->plugin = QUFORM_BASENAME;

                set_site_transient('quform_latest_version_info', $latestVersionInfo, self::CACHE_TIME);
            }
        }

        return $latestVersionInfo;
    }

    /**
     * Get plugin information
     *
     * @param   boolean         $false   Returned if the method call is not for this action or plugin
     * @param   string          $action  The action to perform
     * @param   object          $args    The current plugin data
     * @return  boolean|object           The plugin information data or the $false argument
     */
    public function pluginInformation($false, $action, $args)
    {
        // Do not interfere with other plugins or actions
        if ($action != 'plugin_information' || ! isset($args->slug) || $args->slug != QUFORM_NAME) {
            return $false;
        }

        $response = $this->api->get('/plugin-information', array(
            'license_key' => $this->license->getKey(),
            'site_url' => Quform::base64UrlEncode(site_url())
        ));

        if ( ! is_array($response)) {
            return $false;
        }

        $response['slug'] = QUFORM_NAME;

        return (object) $response;
    }
}

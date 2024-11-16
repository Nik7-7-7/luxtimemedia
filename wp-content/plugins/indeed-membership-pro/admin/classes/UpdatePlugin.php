<?php
namespace Indeed\Ihc\Admin;

class UpdatePlugin
{
    /**
     * @var string
     */
    private $pluginBaseFile                 = 'indeed-membership-pro/indeed-membership-pro.php';
    /**
     * @var string
     */
    private $pluginSlug                     = 'indeed-membership-pro';
    /**
     * @var string
     */
    private $baseApiEndpoint                = 'https://portal.ultimatemembershippro.com/repository/';
    /**
     * @var string
     */
    private $lastCheckOptionName            = 'ihc_update_plugin_last_check';
    /**
     * @var int
     */
    private $timeDiff                       = 24; // hours
    /**
     * @var string
     */
    private $storedDataOptionName           = 'ihc_update_plugin_details';


   /**
    * @param none
    * @return none
    */
    public function __construct()
    {
        $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
        $ol_dL_ogs = new $class();
        if ( (int)$ol_dL_ogs->FGCS() > 0 || $ol_dL_ogs->GCP() === true ){
            return;
        }
        // put plugin custom data into transient
        add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check' ], 999, 1 );

        // plugin data in View Details - Popup
        add_filter( 'plugins_api', [ $this, 'pluginDetails' ], 999, 3 );
    }

    /**
     * @param array
     * @return array
     */
    public function check( $transient=null )
    {
        if ( empty( $transient ) || !is_object( $transient ) ){
            $transient = new \stdClass();
        }

        $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
        $ol_dL_ogs = new $class();
      	if ( (int)$ol_dL_ogs->FGCS() > 0 || $ol_dL_ogs->GCP() === true ){
            return $transient;
        }
        $response = $this->getPluginUpdateDetails();
        $currentVersion = $this->getPluginCurrentVersion();

        // no response
        if ( !isset( $response['new_version'] ) ){
            return $transient;
        }

        // current version is not available
        if ( $currentVersion === 0 ){
            return $transient;
        }

        // we already got the last version of plugin
        if ( version_compare( $response['new_version'], $currentVersion ) !== 1 ){
            return $transient;
        }

        // create package url from where to get the last version of plugin
        $packageUrl = $this->buildPackageURL( $response['new_version'] );
        if ( $packageUrl === false ){
            // something went wrong
            return $transient;
        }

        // save response
        $repositoryData = [
                            'id'            => 'https://ultimatemembershippro.com/',
                            'slug'          => $this->pluginSlug,
                            'plugin'        => $this->pluginBaseFile,
                            'new_version'   => $response['new_version'],
                            'url'           => 'https://ultimatemembershippro.com/',
                            'package'       => $packageUrl . '/',
                            'icons'         => [
                                    '2x' => IHC_URL . 'assets/images/default-logo1.png',
                                    '1x' => IHC_URL . 'assets/images/default-logo1.png',
                            ],
                            'banners'       => [],
                            'banners_rtl'   => [],
                            'requires'      => isset( $response['requires'] ) ? $response['requires'] : '',
                            'tested'        => isset( $response['tested'] ) ? $response['tested'] : '',
                            'requires_php'  => isset( $response['requires_php'] ) ? $response['requires_php'] : '',
        ];


        $repositoryData = (object)$repositoryData;

        $transient->response[ $this->pluginBaseFile ] = $repositoryData;

        $transient->last_checked = time();
        $transient->checked[ $this->pluginBaseFile ] = $response['new_version'];
        return $transient;
    }

    /**
     * @param array
     * @param string
     * @param array
     * @return array
     */
    public function pluginDetails( $result = null, $action = null, $args = null )
    {
        global $wp_version;
        if ( $action !== 'plugin_information' ){
            return $result;
        }
        if ( isset( $args->slug ) && $args->slug === 'indeed-membership-pro' ){
            $storedResponse = get_option( $this->storedDataOptionName, false );
            if ( $storedResponse === false ){
                return $result;
            }
            $targetVersion = isset( $storedResponse['new_version'] ) ? $storedResponse['new_version'] : '';

            // this array will be used in plugin view details pop-up
            $pluginData = [
                'slug'            => 'indeed-membership-pro',
                'name'            => 'Ultimate Membership Pro',
                'author'          =>  IHC_DEV,
                'author_profile'  => 'https://ultimatemembershippro.com/portfolio/',
                'contributors'    => '',
                'homepage'        => 'https://ultimatemembershippro.com/',
                'version'         => isset( $storedResponse['new_version'] ) ? $storedResponse['new_version'] : '',
                'requires'        => isset( $storedResponse['requires'] ) ? $storedResponse['requires'] : '',
                'requires_php'    => isset( $storedResponse['requires_php'] ) ? $storedResponse['requires_php'] : '',
                'tested'          => isset( $storedResponse['tested'] ) ? $storedResponse['tested'] : '',
                'compatibility'   => isset( $storedResponse['requires'] ) ? $storedResponse['requires'] : '',
                'last_updated'    => $this->getPluginCurrentVersion(),
                'download_link'   => $this->buildPackageURL( $targetVersion ), // repository URL
                'sections'        => [
                            'description' => isset( $storedResponse['description'] ) ? $storedResponse['description'] : '',
                            'changelog'   => isset( $storedResponse['change_log'] ) ? $storedResponse['change_log'] : '',
                ],
                'banners' => [
                            'low'  => IHC_URL . 'assets/images/default-logo1.png',
                            'high' => IHC_URL . 'assets/images/login_bg.jpg',
                ],
            ];
            return (object)$pluginData;
        }
        return $result;
    }

    /**
     * @param none
     * @return array
     */
    public function getPluginUpdateDetails()
    {
        $lastChecked = get_option( $this->lastCheckOptionName, 0 );
        $lastChecked = (int)$lastChecked;
        $storedResponse = get_option( $this->storedDataOptionName, false );

        // get plugin update details from db? if we already made a request to server in the last 24hours
        if ( $lastChecked !== 0 && ( $lastChecked + ( $this->timeDiff * 60 * 60 ) > time() )
              && $storedResponse !== false && is_array( $storedResponse ) ){
            return $storedResponse;
        }

        $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
        $ol_dL_ogs = new $class();
        $pc = $ol_dL_ogs->GCP();
        if ( $pc === false || $pc === null || $pc === '' ){
            return false;
        }
        $params = [
                      'action'            => 'check_for_updates',
                      'plugin_name'       => $this->pluginSlug,
                      'domain'            => get_option( 'siteurl' ),
                      'lsn'               => $pc,
                      'v'                 => get_option('iu'.'mp'.'_'.'lnk'.'_v'),
        ];
        $paramsAsString = base64_encode( serialize( $params ) );

        $apiEndpoint = $this->baseApiEndpoint . $paramsAsString . '/' . time() . '/';

        try {
            $response = wp_remote_get( $apiEndpoint );
        } catch ( \Exception $e ){
            return false;
        }

        if ( !is_array( $response ) ){
            return false;
        }

        $responseAsArray = [
            'message'           => 'Error',
            'status'            => 0,
            'data'              => false,
        ];

        if ( isset( $response['body'] ) && $response['body'] !== '' ){
            $jsonDecoded = json_decode( $response['body'], true );
            $responseAsArray = isset( $jsonDecoded['data'] ) ? $jsonDecoded['data'] : false;
            $responseAsArray['message'] = isset( $jsonDecoded['message'] ) ? $jsonDecoded['message'] : 'Error';
            $responseAsArray['status'] = isset( $jsonDecoded['status'] ) ? $jsonDecoded['status'] : 0;
        }
        update_option( $this->storedDataOptionName, $responseAsArray ); // save response in db
        update_option( $this->lastCheckOptionName, time() ); // save current timestamp
        return $responseAsArray;
    }

    /**
     * @param none
     * @return string
     */
    private function getPluginCurrentVersion()
    {
        $pluginData = get_plugin_data( WP_CONTENT_DIR . '/plugins/' . $this->pluginBaseFile );
        return isset( $pluginData['Version'] ) ? $pluginData['Version'] : 0;
    }

    /**
     * @param string
     * @return string
     */
    private function buildPackageURL( $targetVersion='' )
    {
        global $wp_version;
        if ( $targetVersion === '' ){
            return false;
        }
        $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
        $ol_dL_ogs = new $class();
        $pc = $ol_dL_ogs->GCP();
        if ( $pc === false || $pc === null || $pc === '' ){
            return false;
        }
        $params = [
                      'action'            => 'get_file',
                      'plugin_name'       => $this->pluginSlug,
                      'target_version'    => $targetVersion,
                      'lsn'               => $pc,
                      'v'                 => get_option('iu'.'mp'.'_'.'lnk'.'_v'),
                      'domain'            => get_option( 'siteurl' ),
                      'wp_vs'             => $wp_version,
        ];
        $paramsAsString = base64_encode( serialize( $params ) );

        $apiEndpoint = $this->baseApiEndpoint . $paramsAsString . '/' . time();
        return $apiEndpoint;
    }

}

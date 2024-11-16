<?php
namespace Indeed\Ihc\Services;

class ElCheck
{
    /**
     * @var string
     */
    protected $purchaseCode         = '';// input from user
    /**
     * @var string
     */
    protected $pluginId             = '12159253';
    /**
     * @var string
     */
    protected $ajax                 = 'ihc_el_check_get_url_ajax';//
    /**
     * @var string
     */
    protected $hashOptionName       = 'ihc_license_hash';
    /**
     * @var string
     */
    protected $redirectBackUri      = ''; /// dynamicly generated
    /**
     * @var string
     */
    protected $confirmationUri      = '';/// dynamicly generated
    /**
     * @var string
     */
    protected $ajaxRevoke           = 'ihc_revoke_license';//
    /**
     * @var string
     */
    protected $licenseTokenOptionName  = 'ihc_license_token';
    /**
     * @var string
     */
    protected $redirectBackPath        = 'admin.php?page=ihc_manage&tab=help';
    /**
     * @var string
     */
    protected $gateResponse            = 'ihc_elc_response';
    /**
     * @var string
     */
    protected $nonceName               = 'ihc_license_nonce';
    /**
     * @var string
     */
    protected $pluginBaseFile          = IHC_PATH . 'indeed-membership-pro.php';

    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        // ajax to get the link
        add_action( 'wp_ajax_' . $this->ajax, [ $this, 'ajax' ] );
        add_action( 'wp_ajax_' . $this->ajaxRevoke, [ $this, 'unlk' ] );
        // ajax gate for response
        add_action( 'wp_ajax_nopriv_' . $this->gateResponse, [$this, 'response'] );
    		add_action( 'wp_ajax_' . $this->gateResponse, [$this, 'response'] );
        // where to redirect after
        $this->redirectBackUri = admin_url( $this->redirectBackPath );
        $this->confirmationUri = admin_url( 'admin-ajax.php?action=' . $this->gateResponse );
    }

    /**
     * @param none
     * @return none
     */
    public function ajax()
    {
        // check if the call was made by admin
        if ( !current_user_can( 'manage_options' ) ){
            die;
        }
        // check nonce
        if ( empty($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field($_POST['nonce']), $this->nonceName ) ){
            die;
        }
        if ( empty( $_POST['s'] ) ){
            die;
        }
        $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
        $ol_dL_ogs = new $class();
        $ol_dL_ogs->IUCP( sanitize_text_field( $_POST['s'] ) );

        $n = isset( $_POST['n'] ) ? sanitize_text_field( $_POST['n'] ) : '';
        $v = isset( $_POST['v'] ) ? sanitize_text_field( $_POST['v'] ) : '';
        $s = isset( $_POST['s'] ) ? sanitize_text_field( $_POST['s'] ) : '';

        update_option( 'iump_' . 'lnk_' . 'n', $n );
        update_option( 'iump_' . 'lnk_' . 'v', $v );

        // redirect link
        echo esc_ump_content( $this->getRedirectLinkTo( $s, $n, $v ) );
        die;
    }

    /**
     * @param none
     * @return none
     */
    public function response()
    {
        // check token
        if ( !isset( $_POST['token'] ) ){
            return;
        }
        $dbToken = get_option( $this->licenseTokenOptionName );
        if ( $dbToken != (string)$_POST['token'] ){
            return; // wrong token
        }
        //update_option( $this->licenseTokenOptionName , '' );

        $response = isset( $_POST['response'] ) ? (int)$_POST['response'] : 0;
        if ( $response > 0 && !empty( $_POST['hash'] ) ){
            $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
            $ol_dL_ogs = new $class();
            $ol_dL_ogs->SCS( 0 );
            update_option( $this->hashOptionName, sanitize_text_field( $_POST['hash'] ) );
            $ol_dL_ogs->STCO( 0 );
            return;
        }
        $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
        $ol_dL_ogs = new $class();
        $ol_dL_ogs->WECP();
    }

    /**
     * @param none
     * @return int
     */
    public function responseFromGet()
    {
        // check token
        if ( !isset( $_GET['token'] ) ){
            return 0;
        }
        $dbToken = get_option( $this->licenseTokenOptionName );
        if ( $dbToken != (string)$_GET['token'] ){
            return 0; // wrong token
        }
        update_option( $this->licenseTokenOptionName , '' );

        $response = isset( $_GET['response'] ) ? (int)$_GET['response'] : 0;
        if ( $response > 0 ){
            $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
            $ol_dL_ogs = new $class();
            $ol_dL_ogs->SCS( 0 );
            $ol_dL_ogs->STCO( 0 );
            return 1;
        }
        $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
        $ol_dL_ogs = new $class();
        $ol_dL_ogs->WECP();
        $ol_dL_ogs->STCO( 0 );
        return 0;
    }

    /**
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function getRedirectLinkTo( $m='', $n='', $v='' )
    {
        if ( !$m ){
            return;
        }

        // generate token
        $t = bin2hex( random_bytes( 20 ) ) . md5( time() );
        update_option( $this->licenseTokenOptionName, $t );

        // save purchase code into db
        $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
        $ol_dL_ogs = new $class();
        $ol_dL_ogs->IUCP( $m );

        $baseUrl = 'https://portal.ultimatemembershippro.com/link/'. time () . '/';

        $url = add_query_arg( [
                          'pgd'  => $this->pluginId,
                          'rlr'  => urlencode($this->redirectBackUri),
                          'crf'  => urlencode($this->confirmationUri),
                          'rf'   => get_option('siteurl'),
                          'vs'   => $this->pluginVersion(),
                          'tk'   => $t,
                          'v'    => $v,
                          'ph'   => $m,
                          'cn'   => $n

        ], $baseUrl );
        return $url;
    }

    /**
     * @param int
     * @return string
     */
    public function responseCodeToMessage( $code=0, $errorClass='', $successClass='', $langCode=null )
    {
        if ( isset( $_GET['response_message'] ) ){
            $class = ( $code > 0 ) ? $successClass : $errorClass;
            return "<div class='$class'>" . urldecode( stripslashes(sanitize_text_field( $_GET['response_message'] ) ) ) . "</div>";
        }
        switch ( $code ){
            case 1:
              return "<div class='$successClass'>" . esc_html__('Your plugin has been successfully activated the License.', $langCode ) . "</div>";
              break;
            case 0:
              return "<div class='$errorClass'>" . esc_html__('Bad input data. Please try again later!', $langCode ) . "</div>";
              break;
            case -1:
              return "<div class='$errorClass'>" . esc_html__('The API Server may be down for a moment. Please try again later', $langCode ) . "</div>";
              break;
            case -2:
              return "<div class='$errorClass'>" . esc_html__('Submitted Purchase Code is invalid.', $langCode ) . "</div>";
              break;
            case -3:
              return "<div class='$errorClass'>" . esc_html__('Submitted Purchase Code does not match with Current product.', $langCode ) . "</div>";
              break;
        }
    }

    /**
     * @param none
     * @return bool
     */
    public function unlk()
    {
        if ( !current_user_can( 'manage_options' ) ){
            die;
        }
        if ( empty($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field($_POST['nonce']), $this->nonceName ) ){
            die;
        }

        $this->doUnLnk();

        echo 1;
        die;
    }

    public function doUnLnk()
    {
        $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
        $ol_dL_ogs = new $class();
        $p = $ol_dL_ogs->GCP();
        $r = get_option( 'siteurl' );
        $ol_dL_ogs->WECP();
        $ol_dL_ogs->STCO(0);
        update_option( $this->hashOptionName, 0 );
        $ol_dL_ogs->ECP();
        $header= [
          'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
          'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:72.0) Gecko/20100101 Firefox/72.0',
          'Accept: */*',
          'Accept-Language: en-US,en;q=0.5',
          'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With: XMLHttpRequest',
        ];

        $n = get_option( 'iump_' . 'lnk_' . 'n' );
        $v = get_option( 'iump_' . 'lnk_' . 'v' );

        $builder= http_build_query([
           's'      => $p,
           'r'      => $r,
           'v'      => $v,
           'n'      => $n
        ]);

        $endpoint = 'https://portal.ultimatemembershippro.com/unlink/'. time () . '/';
        $ch = curl_init( $endpoint );
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $builder);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        $res = curl_exec($ch);
        curl_close($ch);
    }

    /**
     * @param none
     * @return string
     */
    protected function pluginVersion()
    {
    		require_once ABSPATH . 'wp-admin/includes/plugin.php';
    		$pluginData = get_plugin_data( $this->pluginBaseFile, false, false );
    		return $pluginData['Version'];
    }
}

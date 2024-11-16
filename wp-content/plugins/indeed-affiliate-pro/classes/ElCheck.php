<?php
namespace Indeed\Uap;

class ElCheck
{
    /**
     * @var string
     */
    protected $pluginId             = '16527729';
    /**
     * @var string
     */
    protected $ajax                 = 'uap_el_check_get_url_ajax';//
    /**
     * @var string
     */
    protected $optionName           = '';
    /**
     * @var string
     */
    protected $hashOptionName       = '';
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
    protected $LTON  = '';
    /**
     * @var string
     */
    protected $redirectBackPath        = 'admin.php?page=ultimate_affiliates_pro&tab=help';
    /**
     * @var string
     */
    protected $gateResponse            = 'uap_elc_response';
    /**
     * @var string
     */
    protected $nonceName               = '';
    /**
     * @var string
     */
    protected $pluginBaseFile          = UAP_PATH . 'indeed-affiliate-pro.php';

    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        $this->optionName = 'uap'.'_'.'l'.'ice'.'nse'.'_'.'set';
        $this->hashOptionName = 'uap'.'_'.'l'.'i'.'ce'.'n'.'se'.'_'.'ha'.'sh';
        $this->LTON  = 'uap'.'_'.'li'.'c'.'e'.'n'.'s'.'e'.'_'.'t'.'ok'.'en';
        $this->nonceName = 'uap'.'_'.'li'.'c'.'en'.'s'.'e'.'_'.'nonce';
        // ajax to get the link to redirect
        add_action( 'wp_ajax_' . $this->ajax, [ $this, 'ajax' ] );
        add_action( 'wp_ajax_' . 'uap'.'_'.'r'.'e'.'v'.'o'.'k'.'e'.'_'.'li'.'ce'.'n'.'s'.'e', [ $this, 'rvk' ] );
        // ajax gate for response
        add_action( 'wp_ajax_nopriv_' . $this->gateResponse, [$this, 'response'] );
    		add_action( 'wp_ajax_' . $this->gateResponse, [$this, 'response'] );
        // where to redirect
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

        $c = sanitize_text_field($_POST['s']);
        $stats = uapGeneralPrefix().uapPrevLabel().uapRankGeneralLabel();
        $pR = new $stats;
        $pR->sdcp( $c );
        // redirect link
        $v = isset( $_POST['ls'] ) ? sanitize_text_field( $_POST['ls'] ) : '';
        $cln = isset( $_POST['ame'] ) ? sanitize_text_field( $_POST['ame'] ) : '';
        update_option( 'uap' . 'l_nk_' . 'n', $cln );
        update_option( 'uap' . 'l_nk_' . 'v', $v );
        echo esc_uap_content( $this->getRedirectLinkTo( $c, $v, $cln ) );
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
        $dbToken = get_option( $this->LTON );
        if ( $dbToken != (string)sanitize_text_field($_POST['token']) ){
            return; // wrong token
        }
        $stats = uapGeneralPrefix().uapPrevLabel().uapRankGeneralLabel();
        $pR = new $stats();
        //update_option( $this->LTON, '' ); // since version 8.5

        $response = isset( $_POST['response'] ) ? (int)sanitize_text_field($_POST['response']) : 0;
        if ( $response > 0 && !empty( $_POST['hash'] ) ){
            //update_option( $this->optionName, 1 );
            //update_option( $this->hashOptionName, sanitize_text_field( $_POST['hash'] ) );
            $pR->SLD( 0 );
            $pR->SWF( 0 );
            return;
        }
        $pR->SLD( 1 );
        update_option( $this->optionName, 0 );
    }

    public function responseFromGet()
    {
        // check token
        if ( !isset( $_GET['token'] ) ){
            return 0;
        }
        $stats = uapGeneralPrefix().uapPrevLabel().uapRankGeneralLabel();
        $pR = new $stats();
        $dbToken = get_option( $this->LTON );
        if ( $dbToken != (string)$_GET['token'] ){
            return 0; // wrong token
        }
        update_option( $this->LTON, '' );

        $response = isset( $_GET['response'] ) ? (int)$_GET['response'] : 0;
        if ( $response > 0 ){
            $pR->SLD( 0 );
            $pR->SWF( 0 );
            return 1;
        }
        $pR->SLD( 1 );
        update_option( $this->optionName, 0 );
        return 0;
    }

    /**
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function getRedirectLinkTo( $h='', $v='', $cln='' )
    {
        if ( !$h ){
            return;
        }

        $token = bin2hex( random_bytes( 20 ) ) . md5( time() );
        update_option( $this->LTON, $token );
        $stats = uapGeneralPrefix().uapPrevLabel().uapRankGeneralLabel();
        $pR = new $stats();
        $pR->sdcp( $h );

        $url = add_query_arg( [
                          'uecl'  => $h,
                          'pgd'   => $this->pluginId,
                          'rlr'   => urlencode($this->redirectBackUri),
                          'crf'   => urlencode($this->confirmationUri),
                          'rf'    => uapSiteDomain(),//get_option('siteurl'),
                          'vs'    => $this->pluginVersion(),
                          'tk'    => $token,
                          'v'     => $v,
                          'cln'   => $cln,
        ], 'https://portal.ultimateaffiliate.pro/link/'.time().'/' );
        return $url;
    }

    /**
     * @param int
     * @return string
     */
    public function responseCodeToMessage( $c=0, $e='', $s='', $l='' )
    {
        if ( isset( $_GET['response_message'] ) ){
            $class = ( $c > 0 ) ? $s : $e;
            return "<div class='$class'>" . urldecode(  stripslashes($_GET['response_message']) ) . "</div>";
        }
        $f = 'uap'.'Admin'.'Create' .'Message';
        return $f( $c, $s, $e, $l );
    }

    /**
     * @param none
     * @return bool
     */
    public function rvk()
    {
        if ( !current_user_can( 'manage_options' ) ){
            die;
        }
        if ( empty($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field($_POST['nonce']), $this->nonceName ) ){
            die;
        }

        $this->dorvk();

        echo 1;
        die;
    }

    public function dorvk()
    {
        $referrence = uapSiteDomain();//get_option( 'siteurl' );
        update_option( $this->optionName, 0 );
        update_option( $this->hashOptionName, 0 );
        $stats = uapGeneralPrefix().uapPrevLabel().uapRankGeneralLabel();
        $lc = new $stats();
        $m = $lc->gdcp();
        $lc->SLD( 1 );
        $lc->rdcp();
        $header= [
          'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
          'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:72.0) Gecko/20100101 Firefox/72.0',
          'Accept: */*',
          'Accept-Language: en-US,en;q=0.5',
          'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With: XMLHttpRequest',
        ];
        $builder= http_build_query([
           'uecl'           => $m,
           'r'     => $referrence,
           'v'    => get_option( 'ua'.'p_l'.'nk_v' )
        ]);


        $ch = curl_init( 'https://portal.ultimateaffiliate.pro/unlink/'.time().'/' );
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

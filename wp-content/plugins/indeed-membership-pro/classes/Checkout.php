<?php
namespace Indeed\Ihc;

class Checkout
{
  /**
   * @var int
   */
    private $lid = 0;
    /**
     * @var int
     */
    private $uid = 0;

    private $currency = 'USD';

    private $country =  '';

    private $state = '';

    private $coupon = '';

    private $dynamic_price = '';

    private $selectedPayment = '';

    private $metaData = [];

    private $levelData = [];

    private $isRegistered = FALSE;

    private $OnRegistration = '';

    private $OnRegistrationLevel = '';

    private $attributes = [];


    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        // the shortcode
        add_shortcode( 'ihc-checkout-page', [ $this, 'output' ] );

        // Ajax - update the checkout page
        add_action( 'wp_ajax_ihc_checkout_subscription_details', [ $this, 'subscriptionDetails' ] );
        add_action( 'wp_ajax_nopriv_ihc_checkout_subscription_details', [ $this, 'subscriptionDetails' ] );

        // processing after submit
        add_action( 'init', [ $this, 'processingSubmit' ], 999 );

        // CheckPrettyLinks
        //add_action( 'init', [ $this, 'getMembershipBasedSlug' ], 999 );

        /// CHECKOUT OnRegisterPage
        add_filter('ump_before_submit_form', [ $this, 'CheckoutRegistration' ], 100, 4 );
    }

    /**
     * @param int
     * @return object
     */
    public function setLid( $lid=0 )
    {
        $this->lid = $lid;
        return $this;
    }

    /**
     * @param int
     * @return object
     */
    public function setUid( $uid=0 )
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * @param int
     * @return object
     */
    public function setArgs( $args=[] )
    {
      // set the user id

      global $current_user, $wp_query;
      if ( $this->uid === 0 && isset( $current_user->ID ) ){
          $this->uid = $current_user->ID;
      }

      // set the level id
      if ( $this->lid === 0 && isset( $_GET['lid'] ) ){
          $this->lid = sanitize_text_field( $_GET['lid'] );
      }
      if ( $this->lid === 0 && isset( $_POST['lid'] ) ){
          $this->lid = sanitize_text_field($_POST['lid']);
      }
      if ( $this->lid === 0 && isset( $_REQUEST['lid'] ) ){
          $this->lid = sanitize_text_field($_REQUEST['lid']);
      }
      if ( $this->lid === 0 && isset( $args['lid'] ) ){
          $this->lid = sanitize_text_field($args['lid']);
      }
      if ( $this->lid === 0 && isset( $this->OnRegistrationLevel ) ){
          $this->lid = $this->OnRegistrationLevel;
      }
      if ( $this->lid === 0 || $this->lid === '' ){
          // maybe pretty links
          $membershipSlug = isset( $wp_query->query_vars['iump-membership-slug'] ) ? sanitize_text_field($wp_query->query_vars['iump-membership-slug']) : false;
          $membership = \Indeed\Ihc\Db\Memberships::getOneByName( $membershipSlug );
          if ( isset( $membership['id'] ) ){
              $this->lid = $membership['id'];
          }
      }

      // set Dynamic Price
      if ( isset( $_GET['dynamic_price_set'] ) ){
          $this->dynamic_price = sanitize_text_field($_GET['dynamic_price_set']);
      }
      if ( isset( $_POST['dynamic_price_set'] ) ){
          $this->dynamic_price = sanitize_text_field($_POST['dynamic_price_set']);
      }
      if ( isset( $args['dynamic_price'] ) ){
          $this->dynamic_price = $args['dynamic_price'];
      }

      // set Coupon
      if ( isset( $_GET['coupon_used'] ) ){
          $this->coupon = sanitize_text_field($_GET['coupon_used']);
      }
      if ( isset( $_POST['coupon_used'] ) ){
          $this->coupon = sanitize_text_field($_POST['coupon_used']);
      }
      if ( isset( $args['coupon'] ) ){
          $this->coupon = $args['coupon'];
      }

      // set Default payment
      $this->selectedPayment = get_option('ihc_payment_selected');
      $this->selectedPayment = \Ihc_Db::get_default_payment_gateway_for_level( $this->lid, $this->selectedPayment );
      if ( isset( $_GET['payment_selected'] ) ){
          $this->selectedPayment = sanitize_text_field($_GET['payment_selected']);
      }
      if ( isset( $_POST['payment_selected'] ) ){
          $this->selectedPayment = sanitize_text_field($_POST['payment_selected']);
      }
      if ( isset( $args['payment'] ) ){
          $this->selectedPayment = $args['payment'];
      }
      if ( isset( $_GET['py'] ) ){
         $this->selectedPayment = sanitize_text_field($_GET['py']);
      }

      $this->country = get_user_meta( $this->uid, 'ihc_country', true );
      if ( isset( $_GET['country'] ) ){
          $this->country = sanitize_text_field($_GET['country']);
      }
      if ( isset( $_POST['country'] ) ){
          $this->country = sanitize_text_field($_POST['country']);
      }
      if ( isset( $args['country'] ) ){
          $this->country = $args['country'];
      }

      $this->state = get_user_meta( $this->uid, 'ihc_state', true );
      if ( isset( $_GET['state'] ) ){
          $this->state = sanitize_text_field($_GET['state']);
      }
      if ( isset( $_POST['state'] ) ){
          $this->state = sanitize_text_field($_POST['state']);
      }
      if ( isset( $args['state'] ) ){
          $this->state = $args['state'];
      }

      // set Cuurency
      $this->currency = get_option('ihc_currency');
      if ( isset( $_GET['currency'] ) ){
          $this->currency = sanitize_text_field($_GET['currency']);
      }
      if ( isset( $_POST['currency'] ) ){
          $this->currency = sanitize_text_field($_POST['currency']);
      }
      if ( isset( $args['currency'] ) ){
          $this->currency = $args['currency'];
      }
      if ($this->currency == FALSE){
          $this->currency = 'USD';
      }

    }

    /**
     * @param string
     * @return string
     */
    public function CheckoutRegistration( $output='', $is_public=FALSE, $typeOfForm='', $register_level=0  )
    {
      global $current_user;
      if ( $typeOfForm == 'edit' || (isset( $current_user->ID ) && $current_user->ID > 0 )){
         return $output;
      }
          $this->OnRegistration = TRUE;
          if(isset($register_level) && $register_level > 0){
            $this->OnRegistrationLevel = $register_level;
          }

          return $output.$this->output();

    }

    /**
     * @param array
     * @return string
     */
    public function output( $args=[] , $ajax = FALSE )
    {
        global $wp_version;

        $this->setArgs($args);

        // if we don't have level id out
        if ( $this->lid === 0){
            return '';
        }

        if ( $ajax === false && $this->country === false && $this->uid === 0 ){
            // register page - on first load. no ajax call made
            $this->country = get_option( 'ihc_default_country', false );
        }


        // level details and settings
        $this->levelData = \Indeed\Ihc\Db\Memberships::getOne( $this->lid );

        if(!$this->levelData){
          return '';
        }
        $this->metaData = array(
          'settings'      => ihc_return_meta_arr('checkout-settings'),
          'messages'      => ihc_return_meta_arr('checkout-messages'),
        );

        // the initial settings was not imported to the new workflow
        if ( empty( $this->metaData['settings']['ihc_checkout_inital'] ) ){
          $registerFieldsData = get_option('ihc_user_fields');

      		if ($registerFieldsData){
      			$payment_select_key = ihc_array_value_exists($registerFieldsData, 'payment_select', 'name');
      			$dynamic_price_key = ihc_array_value_exists($registerFieldsData, 'ihc_dynamic_price', 'name');
      			$coupon_key = ihc_array_value_exists($registerFieldsData, 'ihc_coupon', 'name');

      			if (!empty($registerFieldsData[$payment_select_key]['display_public_reg'])){
      					$this->metaData['settings']['ihc_checkout_payment_section'] = $registerFieldsData[$payment_select_key]['display_public_reg'];
      		  }
      			if (!empty($registerFieldsData[$payment_select_key]['theme'])){
      				$this->metaData['settings']['ihc_checkout_payment_theme'] = $registerFieldsData[$payment_select_key]['theme'];
      			}
      			if(!empty($registerFieldsData[$dynamic_price_key]['display_public_reg'])){
      				$this->metaData['settings']['ihc_checkout_dynamic_price'] = $registerFieldsData[$dynamic_price_key]['display_public_reg'];
      			}
      			if(!empty($registerFieldsData[$coupon_key]['display_public_reg'])){
      				$this->metaData['settings']['ihc_checkout_coupon'] = $registerFieldsData[$coupon_key]['display_public_reg'];
      			}
      		}
        }
        // end of the initial settings was not imported to the new workflow

        if(isset($this->OnRegistration) && $this->OnRegistration === TRUE && $this->metaData['settings']['ihc_checkout_avoid_free_membership'] == 1 && $this->levelData['payment_type'] == 'free' ){
          return '';
        }

        if ( $ajax == '' ){
            wp_register_script( 'ihc-checkout-js', IHC_URL . 'assets/js/checkout.js', ['jquery'], '12.8' );
            if ( version_compare ( $wp_version , '5.7', '>=' ) ){
                wp_add_inline_script( 'ihc-checkout-js', "window.ihcCurrentLid='" . $this->lid . "';" );
                wp_add_inline_script( 'ihc-checkout-js', "window.ihcPaymentType='" . $this->selectedPayment . "';" );
            } else {
                wp_localize_script( 'ihc-checkout-js', 'window.ihcCurrentLid', $this->lid );
                wp_localize_script( 'ihc-checkout-js', 'window.ihcPaymentType', $this->selectedPayment );
            }
            wp_enqueue_script( 'ihc-checkout-js' );
        }
        wp_enqueue_style( 'ihc-checkout-css', IHC_URL . 'assets/css/checkout.css', [] );


        //Calculate Product Details and SubTotal values
        $preparePaymentData = $this->preparePaymentData();

        //Payment Method Section
        $paymentMethodData = $this->paymentMethodData( $preparePaymentData );

         // Dynamic Price Section
         $dynamicData = $this->dynamicData( $preparePaymentData );

         //Coupon Section
         $couponData = $this->couponData($preparePaymentData);

         //Taxes Section
         $taxesData = $this->taxesData($preparePaymentData);

         //Privacy Policy Section
         $privacyData = $this->privacyData();

         //Purchase Button Section
         $buttonData = $this->buttonData($preparePaymentData);

         //isRegistered
         $this->isRegistered = $this->isRegistered();


        // params
        $params = [
                            'lid'                       => $this->lid,
                            'uid'                       => $this->uid,

                            'levelData'                 => $this->levelData,
                            'currency'                  => $this->currency,

                            'preparePaymentData'        => $preparePaymentData,

                            'paymentMethodData'         => $paymentMethodData,
                            'dynamicData'               => $dynamicData,
                            'couponData'                => $couponData,
                            'taxesData'                 => $taxesData,
                            'privacyData'               => $privacyData,
                            'buttonData'                => $buttonData,

                            'showUserDetails'           => 0,
                            'fields'                    => array(),

                            'country'                   => $this->country,
                            'state'                     => $this->state,

                            'settings'                  => $this->metaData['settings'],
                            'messages'                  => $this->metaData['messages'],
                            'isRegistered'              => $this->isRegistered,
                            'custom_css'                => $this->metaData['settings']['ihc_checkout_custom_css'],
        ];

        // returing the output
        $view = new \Indeed\Ihc\IndeedView();

        if( $ajax == TRUE){
            //Do something via AJAX
            $filename = 'checkout-subtotal.php';
            $templateFile = IHC_PATH . 'public/views/checkout/checkout-subtotal.php';
            $templateFile = apply_filters( 'ihc_filter_on_load_template', $templateFile, $filename );

            $returnData['subtotal'] = $view->setTemplate( $templateFile )
                                           ->setContentData( $params )
                                           ->getOutput();

             $filename = 'checkout-taxes.php';
             $templateFile = IHC_PATH . 'public/views/checkout/checkout-taxes.php';
             $templateFile = apply_filters( 'ihc_filter_on_load_template', $templateFile, $filename );

            $returnData['taxes'] = $view->setTemplate( $templateFile )
                                        ->setContentData( $params )
                                        ->getOutput();

            $filename = 'checkout-subscription-details.php';
            $templateFile = IHC_PATH . 'public/views/checkout/checkout-subscription-details.php';
            $templateFile = apply_filters( 'ihc_filter_on_load_template', $templateFile, $filename );

            $returnData['subscription_details'] = $view->setTemplate( $templateFile )
                                        ->setContentData( $params )
                                        ->getOutput();

            $filename = 'checkout-purchase-button.php';
            $templateFile = IHC_PATH . 'public/views/checkout/checkout-purchase-button.php';
            $templateFile = apply_filters( 'ihc_filter_on_load_template', $templateFile, $filename );

            $returnData['bttn'] = $view->setTemplate( $templateFile )
                                        ->setContentData( $params )
                                        ->getOutput();

            $filename = 'checkout-coupon-used.php';
            $templateFile = IHC_PATH . 'public/views/checkout/checkout-coupon-used.php';
            $templateFile = apply_filters( 'ihc_filter_on_load_template', $templateFile, $filename );

            $returnData['coupon_success'] = $view->setTemplate( $templateFile )
                                                 ->setContentData( $params )
                                                 ->getOutput();

           $filename = 'checkout-dynamic-price-set.php';
           $templateFile = IHC_PATH . 'public/views/checkout/checkout-dynamic-price-set.php';
           $templateFile = apply_filters( 'ihc_filter_on_load_template', $templateFile, $filename );

            $returnData['dynamic_price_success'] = $view->setTemplate( $templateFile )
                                                        ->setContentData( $params )
                                                        ->getOutput();

            $filename = 'checkout-payment-method.php';
            $templateFile = IHC_PATH . 'public/views/checkout/checkout-payment-method.php';
            $templateFile = apply_filters( 'ihc_filter_on_load_template', $templateFile, $filename );

            $returnData['payment_method_section'] = $view->setTemplate( $templateFile )
                                                        ->setContentData( $params )
                                                        ->getOutput();

            $returnData['coupon_used'] = empty( $preparePaymentData['couponApplied'] ) ? 0 : 1;
            $returnData['dynamic_price_used'] = empty( $preparePaymentData['dynamic_price_used'] ) ? 0 : 1;
            return $returnData;
        }

        // since version 11.7 adding order support for finish payment ( authorize, braintree, stripe connect )
        if ( isset( $_GET['oid'] ) && $_GET['oid'] !== '' && isset( $_GET['ihc-finish-payment'] ) && $_GET['ihc-finish-payment'] === '1' ){
            // we put 2 extra form fields for finish payment functionality
            $params['order_id'] = (int)sanitize_text_field( $_GET['oid'] );
            $params['ihc-finish-payment'] = 1;
        }
        //
        $filename = 'checkout-main-page.php';
        $templateFile = IHC_PATH . 'public/views/checkout/checkout-main-page.php';
        $templateFile = apply_filters( 'ihc_filter_on_load_template', $templateFile, $filename );

        return $view->setTemplate( $templateFile )
                    ->setContentData( $params )
                    ->getOutput();
    }

    /**
     * @param none
     * @return bool
     */
    public function isRegistered( )
    {
      if(isset($this->uid) && $this->uid > 0) {
        return true;
      }
      return false;
    }

    /**
     * @param none
     * @return int
     */
    public function getMembershipBasedSlug( )
    {
      $meta_arr = ihc_return_meta_arr('public_workflow');

      if(isset($meta_arr['ihc_pretty_links']) && $meta_arr['ihc_pretty_links'] == 1 && !isset($_GET['lid']) && !isset($this->lid)){

        $current_url = IHC_PROTOCOL . sanitize_text_field($_SERVER['HTTP_HOST']) . sanitize_text_field($_SERVER['REQUEST_URI']);
        $levelsAll = \Indeed\Ihc\Db\Memberships::getAll();

        if(isset($levelsAll) && is_array($levelsAll) && count($levelsAll) > 0){
          foreach($levelsAll as $key=>$value){
            //Convert
            $levelSlug = str_replace('_','-', $value['name'] );
            if (strpos($current_url, '/' .  $levelSlug . '/')!==FALSE){
              $levelReturnData = \Indeed\Ihc\Db\Memberships::getOneByName($value['name']);

              $this->lid = $levelReturnData['id'];
          }
        }
      }
    }
      return '';
    }

    /**
     * @param string
     * @return string
     */
    public static function getForPeriod( $string ='', $interval = '', $type = '', $multiply = FALSE  )
    {
      $type = strtolower(substr($type, 0, 1 ));
      if($interval == 1){
        switch($type){
          case 'd':
            $type = esc_html__("day", 'ihc');
            break;
          case 'w':
            $type = esc_html__("week", 'ihc');
            break;
          case 'm':
            $type = esc_html__("month", 'ihc');
            break;
          case 'y':
            $type  = esc_html__("year", 'ihc');
            break;
          default:
            $type  = esc_html__("month", 'ihc');
            break;
        }
        if($multiply === TRUE){
          return $string.' '.$type;
        }
        return $string.' '.$interval.' '.$type;
      }else{
        switch($type){
          case 'd':
            $type = esc_html__("days", 'ihc');
            break;
          case 'w':
            $type = esc_html__("weeks", 'ihc');
            break;
          case 'm':
            $type = esc_html__("months", 'ihc');
            break;
          case 'y':
            $type  = esc_html__("years", 'ihc');
            break;
          default:
            $type  = esc_html__("months", 'ihc');
            break;
        }
        return $string.' '.$interval.' '.$type;
      }
    }

    /**
     * @param none
     * @return array
     */
    public function getPaymentServices()
    {
        $allServices = ihc_get_active_payments_services();
        $excludePayments = \Ihc_Db::get_excluded_payment_types_for_level_id( $this->lid );
        if ( empty( $excludePayments ) ){
            return $allServices;
        }
        $excludeArray = explode( ',', $excludePayments );
        foreach ($excludeArray as $ek=>$ev){
            if (isset($allServices[$ev])){
                unset($allServices[$ev]);
            }
        }
        return $allServices;
    }

    /**
     * @param none
     * @return array
     */
    public function preparePaymentData()
    {
      $this->attributes = array(
          'uid'										=> $this->uid,
          'lid'										=> $this->lid,
          'ihc_coupon'	  				=> $this->coupon,
          'ihc_country'						=> $this->country,
          'ihc_state'							=> $this->state,
          'ihc_dynamic_price'			=> $this->dynamic_price,
          'is_register'						=> false,
      );
      switch ($this->selectedPayment){
        case "paypal":
              if (ihc_check_payment_available('paypal')){
                  $paymentGatewayObject = new \Indeed\Ihc\Gateways\PayPalStandard();
                  $preparePayment = $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                                          ->check()
                                                          ->preparePayment();
              }
              break;
        case 'paypal_express_checkout':
              if (ihc_check_payment_available('paypal_express_checkout')){
                  $paymentGatewayObject = new \Indeed\Ihc\Gateways\PayPalExpressCheckout();
                  $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                                          ->check()
                                                          ->preparePayment();
              }
              break;
        case 'stripe_checkout_v2':
              if (ihc_check_payment_available('stripe_checkout_v2')){
                  $paymentGatewayObject = new \Indeed\Ihc\Gateways\StripeCheckout();
                  $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                                          ->check()
                                                          ->preparePayment();
              }
              break;
        case 'mollie':
              if (ihc_check_payment_available('mollie')){
                  $paymentGatewayObject = new \Indeed\Ihc\Gateways\Mollie();
                  $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                                          ->check()
                                                          ->preparePayment();
              }
              break;
        case 'twocheckout':
              if (ihc_check_payment_available('twocheckout')){
                  $paymentGatewayObject = new \Indeed\Ihc\Gateways\TwoCheckout();
                  $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                                          ->check()
                                                          ->preparePayment();
              }
              break;
        case 'pagseguro':
              if (ihc_check_payment_available('pagseguro')){
                  $paymentGatewayObject = new \Indeed\Ihc\Gateways\Pagseguro();
                  $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                                          ->check()
                                                          ->preparePayment();
              }
              break;
        case 'bank_transfer':
              if (ihc_check_payment_available('bank_transfer')){
                  $paymentGatewayObject = new \Indeed\Ihc\Gateways\BankTransfer();
                  $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                       ->check()
                                       ->preparePayment();
              }
              break;
        case 'braintree':
              if (ihc_check_payment_available('braintree')){
                  $paymentGatewayObject = new \Indeed\Ihc\Gateways\Braintree();
                  $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                       ->check()
                                       ->preparePayment();
              }
              break;
        case 'authorize':
              if (ihc_check_payment_available('authorize')){
                  $paymentGatewayObject = new \Indeed\Ihc\Gateways\Authorize();
                  $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                       ->check()
                                       ->preparePayment();
              }
              break;
        case 'stripe_connect':
              if (ihc_check_payment_available('stripe_connect')){
                  $paymentGatewayObject = new \Indeed\Ihc\Gateways\StripeConnect();
                  $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                                       ->check()
                                       ->preparePayment();
              }
              break;
      }// end of switch payment type

      if( !isset($paymentGatewayObject)){
          $paymentGatewayObject = new \Indeed\Ihc\Gateways\VirtualPayment();
          $paymentGatewayObject->setInputData($this->attributes) /// attributes for payment ( lid, uid, coupon, etc)
                               ->check()
                               ->preparePayment();
      }

      if ( isset( $paymentGatewayObject ) ){
          return $paymentGatewayObject->getPaymentOutputData();
      }
      return [];

    }

    /**
     * @param array
     * @return array
     */
    public function paymentMethodData($preparePaymentData=[] )
    {
      $paymentMethodData = array();

      if ( !empty( $this->metaData['settings']['ihc_checkout_payment_section'] )  && ( isset( $preparePaymentData['amount'] ) && $preparePaymentData['amount'] > 0 ) ){
         $paymentMethodData['show'] = 1;
      }
      // payment services
      $paymentMethodData['services'] = $this->getPaymentServices();

       // payment select settings
       $paymentMethodData['theme'] = $this->metaData['settings']['ihc_checkout_payment_theme'];

       // 12.2.1
       if ( !ihc_check_payment_available( $this->selectedPayment ) && is_array( $paymentMethodData['services'] )
          && count( $paymentMethodData['services'] ) > 0 ){
          $firstPaymentKey = array_key_first( $paymentMethodData['services'] );
          if ( isset( $paymentMethodData['services'][$firstPaymentKey] ) ){
              $this->selectedPayment = $firstPaymentKey;
          }
       }
       // 12.2.1

      $paymentMethodData['selected'] = $this->selectedPayment;

      return $paymentMethodData;
    }

    /**
     * @param array
     * @return array
     */
    public function dynamicData( $preparePaymentData=[] )
    {
        $dynamicData  = array();
        if(!empty($this->metaData['settings']['ihc_checkout_dynamic_price']) && ihc_is_magic_feat_active('level_dynamic_price')){
          $temp_dynamic_settings = ihc_return_meta_arr('level_dynamic_price');//getting metas
          if (!empty($temp_dynamic_settings['ihc_level_dynamic_price_levels_on'][$this->lid])){
              $dynamicData['show']  = 1;
          }
          $dynamicData['min'] = isset($temp_dynamic_settings['ihc_level_dynamic_price_levels_min'][$this->lid]) && $temp_dynamic_settings['ihc_level_dynamic_price_levels_min'][$this->lid]!='' ? $temp_dynamic_settings['ihc_level_dynamic_price_levels_min'][$this->lid] : 0;
          $dynamicData['max']  = isset($temp_dynamic_settings['ihc_level_dynamic_price_levels_max'][$this->lid]) && $temp_dynamic_settings['ihc_level_dynamic_price_levels_max'][$this->lid]!='' ? $temp_dynamic_settings['ihc_level_dynamic_price_levels_max'][$this->lid] : $this->levelData['price'];
          $dynamicData['step'] = isset($temp_dynamic_settings['ihc_level_dynamic_price_step']) && $temp_dynamic_settings['ihc_level_dynamic_price_step']!='' ? $temp_dynamic_settings['ihc_level_dynamic_price_step'] : 0.1;
          $dynamicData['used'] = isset($preparePaymentData['dynamic_price_used'] ) ? $preparePaymentData['dynamic_price_used'] : false;
        }
        return $dynamicData;
    }

    /**
     * @param array
     * @return array
     */
    public function couponData($preparePaymentData = [])
    {
      $couponData  = array();
      if(!empty($this->metaData['settings']['ihc_checkout_coupon']) && $preparePaymentData['amount'] > 0){
        $couponData['show']  = 1;
       }

       //Get Additional Details about Used Coupon but only after preparePayment process
       if(isset($preparePaymentData['coupon_used'])&& $preparePaymentData['couponApplied'] == TRUE){
         $couponObject = new \Indeed\Ihc\Payments\Coupons();
          $couponData['details'] = $couponObject->setCode( $preparePaymentData['coupon_used'] )
                                  ->setLid( $this->lid )
                                  ->getData();

         if ( $couponObject->isValid() &&  $couponData['details']  ){
           if( $couponData['details']['discount_type'] === 'price'){
              $couponData['details']['discount_display'] = ihc_format_price_and_currency( $this->currency,  $couponData['details']['discount_value']);
           }else{
              $couponData['details']['discount_display'] =  $couponData['details']['discount_value'].'%';
           }
         }
       }

      return $couponData;
    }

    /**
     * @param array
     * @return array
     */
    public function taxesData($preparePaymentData = [])
    {
      $taxesData = array();
      if(isset($this->metaData['settings']['ihc_checkout_taxes_display_section']) && $this->metaData['settings']['ihc_checkout_taxes_display_section'] == 1 && ihc_is_magic_feat_active('taxes')){
        $taxesData['show'] = 1;
      }
      if(isset($preparePaymentData['taxes_details'])  && is_array($preparePaymentData['taxes_details']) && count($preparePaymentData['taxes_details']) > 0){
        $taxesData['details'] = $preparePaymentData['taxes_details'];
      }
      return $taxesData;
    }

    /**
     * @param none
     * @return string
     */
    public function privacyData()
    {
      $privacyData = '';
      if(!empty($this->metaData['settings']['ihc_checkout_privacy_policy_option']) && !empty($this->metaData['settings']['ihc_checkout_privacy_policy_message'])){
        $privacyData = $this->metaData['settings']['ihc_checkout_privacy_policy_message'];
      }
      return $privacyData;
    }

    /**
     * @param array
     * @return array
     */
    public function buttonData($preparePaymentData = [])
    {
      $buttonData = array();

      if(isset($this->uid) && $this->uid > 0) {
        $buttonData['show'] = 1;
      }


      if($preparePaymentData['amount'] > 0){
        $buttonData['label'] = $this->metaData['messages']['ihc_checkout_purchase_button'];
      }else{
        $buttonData['label'] = $this->metaData['messages']['ihc_checkout_free_button'];
      }
      return $buttonData;
    }

    /**
     * Ajax call
     * @param none
     * @return string
     */
    public function subscriptionDetails()
    {
        global $current_user;

        $args = [
          'uid'										=> sanitize_text_field(isset( $_POST['uid'] ) ? $_POST['uid'] : 0),
          'lid'										=> sanitize_text_field(isset( $_POST['lid'] ) ? $_POST['lid'] : 0),
          'coupon'    	  				=> sanitize_text_field(isset( $_POST['coupon'] ) ? $_POST['coupon'] : ''),
          'dynamic_price'	        => sanitize_text_field(isset( $_POST['dynamicPrice'] ) ? $_POST['dynamicPrice'] : ''),
          'country'	    					=> sanitize_text_field(isset( $_POST['country'] ) ? $_POST['country'] : ''),
          'state'   							=> sanitize_text_field(isset( $_POST['state'] ) ? $_POST['state'] : ''),
          'payment'               => sanitize_text_field(isset( $_POST['paymentType'] ) ? $_POST['paymentType'] : ''),
        ];

        if ( isset( $current_user->ID ) && $current_user->ID !== 0 ){
            if ( !isset( $_POST['country'] ) || $_POST['country'] === '' ){
                $args['country'] = get_user_meta( $current_user->ID, 'ihc_country', true );
            }
            if ( !isset( $_POST['state'] ) || $_POST['state'] === '' ){
                $args['state'] = get_user_meta( $current_user->ID, 'ihc_state', true );
            }
        }

        $callViaAjax = true;
        $response = $this->output( $args, $callViaAjax );
        if ( $response === '' || !is_array( $response ) ){
            // error
            echo json_encode( [ 'status' => 0 ] );
            die;
        }

        if ( sanitize_text_field($_POST['typeOfRequest']) === 'coupon' && !$response['coupon_used'] ){
            $response['status'] = 0;
            echo json_encode( $response );
            die;
        }
        if ( sanitize_text_field($_POST['typeOfRequest']) === 'dynamic_price' && !$response['dynamic_price_used']  ){
            $response['status'] = 0;
            echo json_encode( $response );
            die;
        }

        $response['status'] = 1;
        echo json_encode( $response );
        die;
    }

    /**
     * @param none
     * @return none
     */
    public function processingSubmit()
    {
        if ( ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) && !empty( $_POST['lid'] ) && !empty( $_POST['uid'] ) && !empty( $_POST['checkout-form'] ) ){

            // since version 12.8
            // check if uid has email address
            $email = \Ihc_Db::user_get_email( sanitize_text_field( $_POST['uid'] ) );
            if ( $email === '' || $email === false || $email === null ){
                return;
            }
            // check nonce
            if ( empty( $_POST['ihcfinishpaymentcd'] ) || !wp_verify_nonce( sanitize_text_field($_POST['ihcfinishpaymentcd']), 'ihc_payment_from_account_page' ) ){
                // none its not ok
                return;
            }
            // end of version 12.8

            if ( isset( $_POST['order_id'] ) && $_POST['order_id'] !== '' && isset( $_POST['ihc-finish-payment'] ) && $_POST['ihc-finish-payment'] === '1' ){
                // finish payment
                return $this->doFinishPaymentFromCheckout();
            } else {
                // save the subscription
                $this->saveSubscription();

                // do payment
                $this->doPaymentFromCheckout();
            }

        }// end of checkout-form
    }

    /**
     * @param none
     * @return none
     */
    protected function saveSubscription()
    {
        $uid = isset( $_POST['uid'] ) ? sanitize_text_field($_POST['uid']) : 0;
        $lid = isset( $_POST['lid'] ) ? sanitize_text_field($_POST['lid']) : '';
        \Indeed\Ihc\UserSubscriptions::assign( $uid, $lid );
    }

    /**
     * @param none
     * @return none
     */
    protected function doPaymentFromCheckout()
    {
        $args = array(
            'uid'										=> sanitize_text_field(isset( $_POST['uid'] ) ? $_POST['uid'] : 0),
            'lid'										=> sanitize_text_field(isset( $_POST['lid'] ) ? $_POST['lid'] : 0),
            'ihc_coupon'	  				=> sanitize_text_field(isset( $_POST['coupon_used'] ) ? $_POST['coupon_used'] : ''),
            'ihc_country'						=> sanitize_text_field(isset( $_POST['country'] ) ? $_POST['country'] : ''),
            'ihc_state'							=> sanitize_text_field(isset( $_POST['state'] ) ? $_POST['state'] : ''),
            'ihc_dynamic_price'			=> sanitize_text_field(isset( $_POST['dynamic_price_set'] ) ? $_POST['dynamic_price_set'] : ''),
            'defaultRedirect'				=> '',
            'is_register'						=> false,
        );
        $paymentObject = new \Indeed\Ihc\DoPayment( $args, sanitize_text_field( $_POST['payment_selected'] ) );
        $paymentObject->processing();
    }

    /**
     * @param none
     * @return none
     */
    protected function doFinishPaymentFromCheckout( $uid=0, $lid=0, $orderId=0, $paymentType='' )
    {
        $finishPayment = new \Indeed\Ihc\Payments\FinishUnpaidPayments();
        $finishPayment->setInput([
                                    'payment_type'			=> isset( $_POST['payment_selected'] ) ? sanitize_text_field( $_POST['payment_selected'] ) : '',
                                    'uid'								=> isset( $_POST['uid'] ) ? sanitize_text_field( $_POST['uid'] ) : 0,
                                    'lid'								=> isset( $_POST['lid'] ) ? sanitize_text_field( $_POST['lid'] ) : 0,
                                    'order_id'					=> isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : 0,
                      ])
                      ->doIt();
    }


}

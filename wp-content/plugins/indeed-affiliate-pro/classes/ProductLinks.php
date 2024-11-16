<?php
namespace Indeed\Uap;

class ProductLinks
{
    private $moduleSettings = [];
    private $enableService = true;

    public function __construct()
    {
        global $indeed_db;
        $this->moduleSettings = $indeed_db->return_settings_from_wp_option( 'product_links' );
        if ( !$this->moduleSettings['uap_product_links_enabled'] ){
            $this->enableService = false;
        }
        $uid = indeed_get_uid();
        if ( !$uid ){
            $this->enableService = false;
        }
        $affiliateId = $indeed_db->get_affiliate_id_by_wpuid( $uid );
        if ( !$affiliateId || !$indeed_db->is_affiliate_active( $affiliateId ) ){
            $this->enableService = false;
        }
        $this->registerScripts();
    }

    public function registerScripts()
    {
        if ( !$this->enableService ){
            return;
        }
        wp_enqueue_script( 'uap-public-links-js', UAP_URL . 'assets/js/product_links.js', ['jquery'], 8.3, false );
        wp_enqueue_script( 'indeed-sweet-alert-js', UAP_URL . 'assets/js/sweetalert.js', ['jquery'], 8.3, false );
        wp_enqueue_style( 'indeed-sweet-alert-css', UAP_URL . 'assets/css/sweetalert.css', [], false, 'all' );
    }

    public function getOutput()
    {
        global $indeed_db;
        if ( !$this->enableService ){
            return;
        }
        $typeOfService = get_option( 'uap_product_links_source' );
        $data = [
            'categories'        => $indeed_db->getCategoriesForServiceType( $typeOfService )
        ];
		    $data['showReward'] = get_option( 'uap_product_links_reward_calculation' );
        $viewObject = new \Indeed\Uap\IndeedView();
        return $viewObject->setContentData( $data )->setTemplate( UAP_PATH . 'public/views/product_links/product_links.php' )->getOutput();
    }

}

<?php
namespace Indeed\Uap;

class WooSpecificReferralRates
{
    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        // Admin
        add_filter('woocommerce_product_data_tabs', [ $this, 'addTab' ] );
        add_action('woocommerce_product_data_panels', [ $this, 'tab' ] );
        add_action('woocommerce_process_product_meta_simple', [ $this, 'adminWpSaveCustomSettings' ], 999, 1 );
        add_action('woocommerce_process_product_meta_grouped', [ $this, 'adminWpSaveCustomSettings' ], 999, 1 );
        add_action('woocommerce_process_product_meta_external', [ $this, 'adminWpSaveCustomSettings' ], 999, 1 );
        add_action('woocommerce_process_product_meta_variable', [ $this, 'adminWpSaveCustomSettings' ], 999, 1 );
        // variantions product
        add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'variableProductSettings'], 99, 3 );
        add_action( 'woocommerce_ajax_save_product_variations', [ $this, 'saveVariationProductSettings' ] );

        // Public
        add_filter( 'uap_filter_referral_amount', [ $this, 'filterAmount'], 1, 4 );
    }

    /**
     * @param int
     * @param int
     * @param int
     * @param array
     * @return int
     */
    public function filterAmount( $customAmounts=[], $inputAmount=0, $productId=0, $attr=[] )
    {
        if ( !empty( $attr['variableProductId'] ) ){
            // variable product
            $type = get_post_meta( $attr['variableProductId'], 'uap-woo-wsr-type', true );
            $value = get_post_meta( $attr['variableProductId'], 'uap-woo-wsr-value', true );
        }
        if ( !isset($type) || $type == false ){
            // base product
            $type = get_post_meta( $productId, 'uap-woo-wsr-type', true );
            $value = get_post_meta( $productId, 'uap-woo-wsr-value', true );
        }

        if ( $type == false ){
            return $customAmounts;
        }
        if ( $type == 'default' ){
            return $customAmounts;
        }
        if ( $value === false  || $value == '' ){
            return $customAmounts;
        }
        if ( $type == 'flat' ){
            $customAmounts[] = $value;
        } else if ( $type == 'percentage' ){
            $customAmounts[] = $inputAmount * $value / 100;
        }
        return $customAmounts;
    }


    /**
     * @param array
     * @return array
     */
    public function addTab( $tabs=[] )
    {
        $tabs['uap_woo_wsr'] = array(
                      'label'  => esc_html__( 'Ultimate Affiliate Pro - Custom Referral Rate', 'uap' ),
                      'target' => 'uap_woo_wsr',
                      'class'  => [], // 'hide_if_grouped'
        );
        return $tabs;
    }

    /**
     * @param none
     * @return none
     */
    public function tab()
    {
        global $post;
        $currency = get_option('uap_currency');
        $types = [
                          'flat' 					=> esc_html__( 'Flat ', 'uap') . '(' . $currency .')',
                          'percentage'		=> esc_html__( 'Percentage ', 'uap') . '(%)',
                          'default'       => esc_html__( 'Default Affiliate system Settings', 'uap' ),
        ];
        $data = [
                  'uap-woo-wsr-type'		    => get_post_meta( $post->ID, 'uap-woo-wsr-type', true ),
                  'uap-woo-wsr-value'		    => get_post_meta( $post->ID, 'uap-woo-wsr-value', true ),
                  'uap-woo-excluded-prod'   => get_post_meta( $post->ID, 'uap-woo-excluded-prod', true ),
                  'types'                    => $types
        ];
        if ( $data['uap-woo-wsr-type'] == '' ){
            $data['uap-woo-wsr-type'] = 'default';
        }
        if ( $data['uap-woo-excluded-prod'] === '' || $data['uap-woo-excluded-prod'] === false || $data['uap-woo-excluded-prod'] === null ){
            $data['uap-woo-excluded-prod'] = 0;
        }
        $view = new \Indeed\Uap\IndeedView();
        $output = $view->setTemplate( UAP_PATH . 'admin/views/uap-woo-specific-referral-rate.php' )
                  ->setContentData( $data )
                  ->getOutput();
        echo esc_uap_content( $output );
    }



    public function variableProductSettings( $loop=null, $variationData=null, $variation=null )
    {
        $currency = get_option('uap_currency');
        $types = [
                          'flat' 					=> $currency,
                          'percentage'		=> '%',
                          'default'       => esc_html__( 'Default', 'uap' ),
        ];
        $data = [
                  'uap-woo-wsr-type'		    => get_post_meta( $variation->ID, 'uap-woo-wsr-type', true ),
                  'uap-woo-wsr-value'		    => get_post_meta( $variation->ID, 'uap-woo-wsr-value', true ),
                  'uap-woo-excluded-variable_prod'   => get_post_meta( $variation->ID, 'uap-woo-excluded-variable_prod', true ),
                  'types'                   => $types,
                  'variantion_id'           => $variation->ID,
        ];
        if ( $data['uap-woo-excluded-variable_prod'] === '' || $data['uap-woo-excluded-variable_prod'] === false || $data['uap-woo-excluded-variable_prod'] === null ){
            $data['uap-woo-excluded-variable_prod'] = 0;
        }
        $view = new \Indeed\Uap\IndeedView();
        $output = $view->setTemplate( UAP_PATH . 'admin/views/uap-woo-specific-referral-rate-variable-products.php' )
                  ->setContentData( $data )
                  ->getOutput();
        echo esc_uap_content( $output );
    }


    /**
     * @param int
     * @return none
     */
    public function adminWpSaveCustomSettings( $postId=0 )
    {
        if ( !$postId || !isset($_POST['uap-woo-wsr-type']) ){
            return false;
        }
        update_post_meta( $postId, 'uap-woo-wsr-type', uap_sanitize_array($_POST['uap-woo-wsr-type']) );
        update_post_meta( $postId, 'uap-woo-wsr-value', uap_sanitize_array($_POST['uap-woo-wsr-value']) );
        if ( isset( $_POST['uap-woo-excluded-prod'] ) ){
            update_post_meta( $postId, 'uap-woo-excluded-prod', sanitize_text_field( $_POST['uap-woo-excluded-prod'] ) );
        }
        $this->saveVariationProductSettings(); //if it's case let's save the values for variable product
    }

    public function saveVariationProductSettings()
    {
        if ( empty( $_POST['variable_post_id'] ) ) {
            return;
        }
        if ( !is_array( $_POST['variable_post_id'] ) ){
            return;
        }
        foreach ( $_POST['variable_post_id'] as $variableProductId ) {
            if ( isset( $_POST['uap-woo-wsr-variable-product-type'][$variableProductId] ) ){
                update_post_meta( $variableProductId, 'uap-woo-wsr-type', uap_sanitize_array($_POST['uap-woo-wsr-variable-product-type'][$variableProductId]) );
            }
            if ( isset( $_POST['uap-woo-wsr-variable-product-value'][$variableProductId] ) ){
                update_post_meta( $variableProductId, 'uap-woo-wsr-value', uap_sanitize_array($_POST['uap-woo-wsr-variable-product-value'][$variableProductId]) );
            }
            if ( isset( $_POST['uap-woo-excluded-variable_prod'] ) ){
                update_post_meta( $variableProductId, 'uap-woo-excluded-variable_prod', sanitize_text_field( $_POST['uap-woo-excluded-variable_prod'] ) );
            }
        }
    }
}

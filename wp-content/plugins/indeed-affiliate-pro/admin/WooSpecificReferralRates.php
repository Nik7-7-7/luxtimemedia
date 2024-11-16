<?php
namespace Indeed\Uap\Admin;

class WooSpecificReferralRates
{
    public function __construct()
    {
        add_filter('woocommerce_product_data_tabs', [ $this, 'addTab' ] );
        add_action('woocommerce_product_data_panels', [ $this, 'tabHtml' ] );
        add_action('woocommerce_process_product_meta_simple', [ $this, 'adminWpSaveCustomSettings' ], 999, 1 );
        add_action('woocommerce_process_product_meta_grouped', [ $this, 'adminWpSaveCustomSettings' ], 999, 1 );
        add_action('woocommerce_process_product_meta_external', [ $this, 'adminWpSaveCustomSettings' ], 999, 1 );
        add_action('woocommerce_process_product_meta_variable', [ $this, 'adminWpSaveCustomSettings' ], 999, 1 );

        // variantions
        add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'variableProductSettings'], 99, 2 );
        add_action( 'woocommerce_ajax_save_product_variations', [ $this, 'saveVariationProductSettings' ] );
    }



}

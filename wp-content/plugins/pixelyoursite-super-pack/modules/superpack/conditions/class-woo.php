<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpWooCondition extends SpCondition {

    public function register_sub_conditions()
    {
        $product_archive = new SpWooProductArchiveCondition();

        $product_single = new SpPostCondition( [
            'post_type' => 'product',
        ] );

        $this->sub_conditions[] = $product_archive;
        $this->sub_conditions[] = $product_single;
        SpPixelCondition()->registerCondition($product_archive);
        SpPixelCondition()->registerCondition($product_single);
    }

    public function get_label()
    {
        return 'WooCommerce';
    }

    public function get_name()
    {
        return 'woocommerce';
    }

    public function get_all_label()
    {
        return 'Entire Shop';
    }

    public function check($args)
    {
        $is_product_search = is_search() && 'product' === get_query_var( 'post_type' );
        return is_woocommerce() || $is_product_search || is_cart() || is_checkout()  || is_wc_endpoint_url();

    }

    public function get_controls()
    {
        $options = [];
        foreach ($this->sub_conditions as $condition) {
            $title =  $condition->get_label();
            $items = [$condition->get_name()];
            foreach ($condition->get_sub_conditions() as $sub) {
                $items[] = $sub->get_name();
            }
            $options[] = [
                'title' => $title,
                'items'=>$items
            ];
        }
        return [
            'type'=>'select_titled',
            'name' => 'sub_name',
            'options'=>$options
        ];
        ?>
        <?php
    }
}
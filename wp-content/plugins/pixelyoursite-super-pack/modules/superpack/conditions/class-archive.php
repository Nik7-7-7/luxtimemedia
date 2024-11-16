<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpArchiveCondition extends SpCondition {


    public function register_sub_conditions()
    {
        $condition = new SpSearchCondition();
        $this->sub_conditions[] = $condition;
        SpPixelCondition()->registerCondition($condition);

        $post_types = get_public_post_types();
        // Product form WooCommerce are handled separately.
        if ( class_exists( 'woocommerce' ) ) {
            unset( $post_types['product'] );
        }

        foreach ( $post_types as $post_type => $label ) {
            if ( ! get_post_type_archive_link( $post_type ) ) {
                continue;
            }

            $condition = new SpArchivePostCondition( [
                'post_type' => $post_type,
            ] );
            $this->sub_conditions[] = $condition;
            SpPixelCondition()->registerCondition($condition);
        }
    }

    public function get_label()
    {
        return 'Archives';
    }

    public function get_name()
    {
        return 'archive';
    }

    public function get_all_label()
    {
        return 'All Archives';
    }

    public function check($args)
    {
        $is_archive = is_archive() || is_home() || is_search();

        // WooCommerce is handled by `woocommerce` module.
        if ( $is_archive && class_exists( 'woocommerce' ) && is_woocommerce() ) {
            $is_archive = false;
        }

        return $is_archive;
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
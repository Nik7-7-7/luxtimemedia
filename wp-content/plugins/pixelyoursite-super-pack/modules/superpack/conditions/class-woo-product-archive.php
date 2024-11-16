<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpWooProductArchiveCondition extends SpCondition {
    private $post_type = 'product';
    private $post_taxonomies;

    public function __construct( array $data = [] ) {
        $taxonomies = get_object_taxonomies( $this->post_type, 'objects' );
        $this->post_taxonomies = wp_filter_object_list( $taxonomies, [
            'public' => true,
            'show_in_nav_menus' => true,
        ] );

        parent::__construct( $data );
    }

    public function register_sub_conditions()
    {
        $shop = new SpWooShopCondition();
        $this->sub_conditions[] = $shop;
        SpPixelCondition()->registerCondition($shop);

        $search = new SpWooSearchCondition();
        $this->sub_conditions[] = $search;
        SpPixelCondition()->registerCondition($search);


        foreach ( $this->post_taxonomies as $slug => $object ) {
            $condition = new SpTaxonomyCondition( [
                'object' => $object,
            ] );
            $this->sub_conditions[] = $condition;
            SpPixelCondition()->registerCondition($condition);
        }
    }

    public function get_label()
    {
        return 'Product Archive';
    }

    public function get_name()
    {
        return 'product_archive';
    }

    public function get_all_label() {
        return 'All Product Archives';
    }

    public function check($args)
    {
        $is_product_search = is_search() && 'product' === get_query_var( 'post_type' );
        return is_shop() || is_product_taxonomy() || $is_product_search;
    }
}
<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpEddProductArchiveCondition extends SpCondition {
    private $post_type = 'download';
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
        $is_product_search = is_search() && 'download' === get_query_var( 'post_type' );
        return is_tax( get_object_taxonomies( 'download' ) ) || $is_product_search;
    }
}
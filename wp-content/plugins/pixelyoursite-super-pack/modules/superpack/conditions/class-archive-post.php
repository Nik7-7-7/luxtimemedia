<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpArchivePostCondition extends SpCondition {

    private $post_type;
    private $post_taxonomies;

    public function __construct( $data ) {
        $this->post_type = get_post_type_object( $data['post_type'] );
        $taxonomies = get_object_taxonomies( $data['post_type'], 'objects' );
        $this->post_taxonomies = wp_filter_object_list( $taxonomies, [
            'public' => true,
            'show_in_nav_menus' => true,
        ] );

        parent::__construct();
    }

    public function register_sub_conditions()
    {
        foreach ( $this->post_taxonomies as $slug => $object ) {
            $condition = new SpTaxonomyCondition( [
                'object' => $object,
            ] );

            $this->sub_conditions[] = $condition;
            SpPixelCondition()->registerCondition($condition);

            if ( ! $object->hierarchical ) {
                continue;
            }


            $condition = new SpChildOfTermCondition([ 'object' => $object ]);
            $this->sub_conditions[] = $condition;
            SpPixelCondition()->registerCondition($condition);

            $condition = new SpAnyChildOfTermCondition([ 'object' => $object ]);
            $this->sub_conditions[] = $condition;
            SpPixelCondition()->registerCondition($condition);


        }
    }

    public function get_label()
    {
        return sprintf( '%s Archive', $this->post_type->label );
    }

    public function get_name()
    {
        return $this->post_type->name . '_archive';
    }

    public function get_all_label() {
        return sprintf( '%s Archive', $this->post_type->label );
    }

    public function check($args)
    {
        return is_post_type_archive( $this->post_type->name ) || ( 'post' === $this->post_type->name && is_home() );
    }
}
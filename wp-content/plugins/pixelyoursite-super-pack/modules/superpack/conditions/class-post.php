<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpPostCondition extends SpCondition {

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
            $in_taxonomy = new SpInTaxonomyCondition( [
                'object' => $object,
            ] );
            $this->sub_conditions[] = $in_taxonomy;
            SpPixelCondition()->registerCondition($in_taxonomy);

//            if ( $object->hierarchical ) {
//                $in_sub_term = new SpInSubTermCondition( [
//                    'object' => $object,
//                ] );
//                $this->sub_conditions[] = $in_sub_term;
//                SpPixelCondition()->registerCondition($in_sub_term);
//            }

        }
    }

    public function get_label()
    {
        return $this->post_type->labels->singular_name;
    }

    public function get_name()
    {
        return $this->post_type->name;
    }

    public function check($args)
    {
        if ( isset( $args['id'] ) ) {
            $id = (int) $args['id'];
            if ( $id ) {
                return  apply_filters('pys_conditional_post_id',get_queried_object_id()) === $id;
            }
        }

        return is_singular( $this->post_type->name );
    }
    public function get_all_label()
    {
        return "All";
    }

    public function get_controls()
    {
        return [
            'type' => 'search',
            'query' => [
                'object' => 'post',
                'query' => [
                    'post_type' => $this->get_name(),
                ],
            ],
            'name' => 'sub_id',
        ];
    }


}

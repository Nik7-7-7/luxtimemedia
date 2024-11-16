<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpTaxonomyCondition extends SpCondition {
    private $taxonomy;

    public function __construct( $data ) {
        parent::__construct();

        $this->taxonomy = $data['object'];
    }
    public function register_sub_conditions()
    {
        // empty
    }

    public function get_label()
    {
        return $this->taxonomy->label;
    }

    public function get_name()
    {
        return $this->taxonomy->name;
    }

    public function check($args)
    {
        $taxonomy = $this->get_name();
        $id = (int) $args['id'];

        if ( 'category' === $taxonomy ) {
            return is_category( $id );
        }

        if ( 'post_tag' === $taxonomy ) {
            return is_tag( $id );
        }

        return is_tax( $taxonomy, $id );
    }

    public function get_all_label()
    {
        return 'All';
    }

    public function get_controls()
    {
        return [
            'type' => 'search',
            'query' => [
                'object' => 'tax',
                'by_field' => 'term_id',
                'query' => [
                    'taxonomy' => $this->taxonomy->name,
                ],
            ],
            'name' => 'sub_id',
        ];
    }
}
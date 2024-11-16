<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpInSubTermCondition extends SpCondition {
    /**
     * @var \WP_Taxonomy
     */
    private $taxonomy;

    public function __construct( $data ) {
        parent::__construct();
        $this->taxonomy = $data['object'];
    }

    public function register_sub_conditions()
    {
        return [];
    }

    public function get_label()
    {
        return sprintf( 'In Child %s', $this->taxonomy->labels->name );
    }

    public function get_name()
    {
        return 'in_' . $this->taxonomy->name . '_children';
    }

    public function get_all_label()
    {
        return "All";
    }

    public function check($args)
    {
        $id = (int) $args['id'];
        if ( ! $id ) {
            return true;
        }
        $post_id = apply_filters('pys_conditional_post_id',null);
        $child_terms = get_term_children( $id, $this->taxonomy->name );
        $status = !empty( $child_terms ) && has_term( $child_terms, $this->taxonomy->name,$post_id );
        return $status;
    }

    public function get_controls()
    {
        return [
            'type' => 'search',
            'query' => [
                'object' => 'tax',
                'display' => 'detailed',
                'by_field' => 'term_id',
                'query' => [
                    'taxonomy' => $this->taxonomy->name,
                ],
            ],
            'name' => 'sub_id',
        ];
    }

}
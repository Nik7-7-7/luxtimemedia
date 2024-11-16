<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpInTaxonomyCondition extends SpCondition {
    /**
     * @var \WP_Taxonomy
     */
    private $taxonomy;

    public function __construct( $data ) {
        parent::__construct();
        $this->taxonomy = $data['object'];
    }

    public function get_label() {
        return sprintf( 'In %s', $this->taxonomy->labels->singular_name );
    }

    public function get_name() {
        return 'in_' . $this->taxonomy->name;
    }

    public function check( $args ) {

        if ( $args['id'] == '' ) { // for all
            return true;
        }
        $post_id = apply_filters('pys_conditional_post_id',null);
        return has_term( (int) $args['id'], $this->taxonomy->name ,$post_id);
    }

    public function register_sub_conditions()
    {
        return [];
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
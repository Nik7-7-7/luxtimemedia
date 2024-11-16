<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class SpChildOfTermCondition extends SpTaxonomyCondition  {
    private $taxonomy;
    public function __construct( $data ) {
        parent::__construct( $data );

        $this->taxonomy = $data['object'];
    }

    public function register_sub_conditions()
    {
        // empty
    }

    public function get_label()
    {
        return sprintf( 'Direct Child %s Of', $this->taxonomy->labels->singular_name );
    }

    public function get_name()
    {
        return 'child_of_' . $this->taxonomy->name;
    }

    public function is_term() {
        $taxonomy = $this->taxonomy->name;
        $current = get_queried_object();
        return ( $current && isset( $current->taxonomy ) && $taxonomy === $current->taxonomy );
    }

    public function check( $args ) {
        if($args['id'] == '') return  true;

        $id = (int) $args['id'];
        $current = get_queried_object();

        return $this->is_term() && $id === $current->parent;
    }
}
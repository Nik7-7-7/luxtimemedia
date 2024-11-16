<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
class SpAnyChildOfTermCondition extends SpChildOfTermCondition {
    private $taxonomy;

    public function __construct( $data ) {
        parent::__construct( $data );

        $this->taxonomy = $data['object'];
    }

    public function get_name() {
        return 'any_child_of_' . $this->taxonomy->name;
    }

    public function get_label() {
        return sprintf( 'Any Child %s Of', $this->taxonomy->labels->singular_name );
    }

    public function check( $args ) {
        if($args['id'] == '') return  true;

        $id = (int) $args['id'];
        /**
         * @var \WP_Term $current
         */
        $current = get_queried_object();
        if ( ! $this->is_term() || 0 === $current->parent ) {
            return false;
        }

        while ( $current->parent > 0 ) {
            if ( $id === $current->parent ) {
                return true;
            }
            $current = get_term_by( 'id', $current->parent, $current->taxonomy );
        }

        return $id === $current->parent;
    }
}
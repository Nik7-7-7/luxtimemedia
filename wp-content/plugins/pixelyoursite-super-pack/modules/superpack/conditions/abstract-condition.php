<?php
namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

abstract class SpCondition {

    /**
     * @var SpCondition[]
     */
    protected $sub_conditions = [];
	protected $options = [];

    public function __construct() {
        $this->register_sub_conditions();
    }
    abstract public function register_sub_conditions();

    abstract public function get_label();
    abstract public function get_name();

    abstract public function check( $args );


    /**
     * @return SpCondition[]
     */
    public function get_sub_conditions() {
        return $this->sub_conditions;
    }

	public function get_options() {
		return $this->options;
	}

    public function get_all_label() {
        return $this->get_label();
    }

    public function get_controls(){
        return [];
    }






}
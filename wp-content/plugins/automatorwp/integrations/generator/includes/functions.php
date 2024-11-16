<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Generator\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the algorithms
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_generator_get_algorithms( ) {

    return hash_algos();
}
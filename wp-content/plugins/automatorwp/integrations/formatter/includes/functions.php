<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Formatter\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the number formats
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_formatter_get_number_formats( ) {

    $number_formats = array(
        '1' => __( 'Round to integer', 'automatorwp' ),
        '2' => __( 'Round to one decimal', 'automatorwp' ),
        '3' => __( 'Round to two decimals', 'automatorwp' ),
        '4' => __( 'Round down', 'automatorwp' ),
        '5' => __( 'Round up', 'automatorwp' ),
    );

    return $number_formats;
}

/**
 * Get the formatted number
 *
 * @since 1.0.0
 *
 * @param int       $format_id
 * @param string    $number
 *
 * @return string|null
 */
function automatorwp_formatter_get_formatted_number( $format_id, $number ) {

    // Empty title if no ID provided
    if( absint( $format_id ) === 0 ) {
        return '';
    }

    switch ( $format_id ) {
        case 1:
            return round( $number, 0 );
            break;
        case 2:
            return round( $number, 1 );
            break;
        case 3:
            return round( $number, 2 );
            break;
        case 4:
            return floor( $number );
            break;
        case 5:
            return ceil( $number );
            break;

    }

}

/**
 * Get the string formats
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_formatter_get_string_formats( ) {

    $string_formats = array(
        '1' => __( 'Lowercase', 'automatorwp' ),
        '2' => __( 'Uppercase', 'automatorwp' ),
        '3' => __( 'Capitalize', 'automatorwp' ),
        '4' => __( 'Capitalize all words', 'automatorwp' ),
        '5' => __( 'Reverse', 'automatorwp' ),
        '6' => __( 'Shuffle', 'automatorwp' ),
        '7' => __( 'Shuffle words', 'automatorwp' ),
        '8' => __( 'Slugify', 'automatorwp' ),
    );

    return $string_formats;
}


/**
 * Get the formatted string
 *
 * @since 1.0.0
 *
 * @param int       $format_id
 * @param string    $string
 *
 * @return string|null
 */
function automatorwp_formatter_get_formatted_string( $format_id, $string ) {

    // Empty title if no ID provided
    if( absint( $format_id ) === 0 ) {
        return '';
    }

    switch ( $format_id ) {
        case 1:
            return strtolower( $string );
            break;
        case 2:
            return strtoupper( $string );
            break;
        case 3:
            return ucfirst( $string );
            break;
        case 4:
            return ucwords( $string );
            break;
        case 5:
            return strrev( $string );
            break;
        case 6:
            return str_shuffle( $string );
            break;
        case 7:
            $string_array = explode(' ', trim( $string ) );
            shuffle( $string_array );
            return implode( ' ', $string_array );
        case 8:
            $string = strtolower( $string );
            $string = preg_replace('/[^a-z0-9]+/', '-', $string); 
            $slug = trim($string, '-'); 
            return $slug;
            break;

    }

}
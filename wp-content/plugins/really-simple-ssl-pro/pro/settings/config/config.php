<?php defined('ABSPATH') or die();

/**
 * Add premium fields
 * @param array $fields
 *
 * @return array
 */

function rsssl_pro_add_premium_fields($fields)
{
	return $fields;
}
add_filter('rsssl_fields', 'rsssl_pro_add_premium_fields' );
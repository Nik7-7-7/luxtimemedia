<?php

namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelYourSite;

if ( PixelYourSite\SuperPack()->getOption( 'enabled' ) && PixelYourSite\SuperPack()->getOption( 'remove_pixel_enabled' ) ) {
	add_filter( 'pys_superpack_meta_box_screens', 'PixelYourSite\SuperPack\addRemovePixelMetaBox' );
	add_action( 'pys_superpack_meta_box', 'PixelYourSite\SuperPack\renderRemovePixelMetaBox' );
	add_action( 'pys_superpack_meta_box_save', 'PixelYourSite\SuperPack\saveRemovePixelMetaBox', 10, 2 );
}

if ( PixelYourSite\SuperPack()->configured() && PixelYourSite\SuperPack()->getOption( 'remove_pixel_enabled' )) {
	add_filter( 'pys_pixel_disabled', 'PixelYourSite\SuperPack\maybeRemovePixel', 10, 2 );
}

function addRemovePixelMetaBox( $screens ) {
	
	$screens[] = 'post';
	$screens[] = 'page';
	
	// add custom post types
	foreach ( get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' ) as $post_type ) {
		$screens[] = $post_type->name;
	}
	
	return $screens;
	
}

function renderRemovePixelMetaBox() {
	include 'views/html-remove-pixel-meta-box.php';
}

function saveRemovePixelMetaBox( $post_id, $data ) {

	// Facebook
	if ( isset( $data['pys_super_pack_remove_pixel'] ) ) {
		update_post_meta( $post_id, '_pys_super_pack_remove_pixel', $data['pys_super_pack_remove_pixel'] );
	} else {
		delete_post_meta( $post_id, '_pys_super_pack_remove_pixel' );
	}
	
	// GA
	if ( isset( $data['pys_super_pack_remove_ga_pixel'] ) ) {
		update_post_meta( $post_id, '_pys_super_pack_remove_ga_pixel', $data['pys_super_pack_remove_ga_pixel'] );
	} else {
		delete_post_meta( $post_id, '_pys_super_pack_remove_ga_pixel' );
	}

    // GAds
    if ( isset( $data['pys_super_pack_remove_ads_pixel'] ) ) {
        update_post_meta( $post_id, '_pys_super_pack_remove_ads_pixel', $data['pys_super_pack_remove_ads_pixel'] );
    } else {
        delete_post_meta( $post_id, '_pys_super_pack_remove_ads_pixel' );
    }

    // Bing
    if ( isset( $data['pys_super_pack_remove_bing_pixel'] ) ) {
        update_post_meta( $post_id, '_pys_super_pack_remove_bing_pixel', $data['pys_super_pack_remove_bing_pixel'] );
    } else {
        delete_post_meta( $post_id, '_pys_super_pack_remove_bing_pixel' );
    }
	
	// Pinterest
	if ( isset( $data['pys_super_pack_remove_pinterest_pixel'] ) ) {
		update_post_meta( $post_id, '_pys_super_pack_remove_pinterest_pixel', $data['pys_super_pack_remove_pinterest_pixel'] );
	} else {
		delete_post_meta( $post_id, '_pys_super_pack_remove_pinterest_pixel' );
	}

}

function maybeRemovePixel( $remove, $context ) {
    global $post;
    $current_page = get_queried_object();
    if(is_object($current_page) && property_exists($current_page, 'post_type') && ($current_page->post_type != 'archive' && $current_page->post_type != 'category'))
    {
        switch ( $context ) {
            case 'facebook':
                if ($current_page) {
                    return (array) get_post_meta($current_page->ID, '_pys_super_pack_remove_pixel', true);
                }
                break;

            case 'google_ads':
                if ($current_page) {
                    return (array) get_post_meta($current_page->ID, '_pys_super_pack_remove_ads_pixel', true);
                }
                break;

            case 'ga':
                if ($current_page) {
                    return (array) get_post_meta($current_page->ID, '_pys_super_pack_remove_ga_pixel', true);
                }
                break;

            case 'bing':
                if ($current_page) {
                    return (array) get_post_meta($current_page->ID, '_pys_super_pack_remove_bing_pixel', true);
                }
                break;

            case 'pinterest':
                if ($current_page) {
                    return (array) get_post_meta($current_page->ID, '_pys_super_pack_remove_pinterest_pixel', true);
                }
                break;

            default:
                break;
        }
    }


    return $remove;
}

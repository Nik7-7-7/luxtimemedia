<?php

namespace PixelYourSite\SuperPack;

use PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function maybeMigrate() {
	
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}
	
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	$sp_version = get_option( 'pys_super_pack_version', false );
	
	// migrate from 1.x
	if ( ! $sp_version ) {
		
		migrate_v1_options();

		update_option( 'pys_super_pack_version', PYS_SUPER_PACK_VERSION );
	
	}

    if ( $sp_version && version_compare($sp_version, '3.0.0', '<') ) {

        migrate_v2_1_8_options();

        update_option( 'pys_super_pack_version', PYS_SUPER_PACK_VERSION );

    }

}

function migrate_v2_1_8_options() {

    // facebook
    $options = [];
    $fbPixels = [];
    $oldPixels = PixelYourSite\Facebook()->getOption('pixel_id');
    if (is_array($oldPixels) && count($oldPixels) > 1) {

        $apiTokens = (array)PixelYourSite\Facebook()->getOption('server_access_api_token');
        $apiTestCodes = (array)PixelYourSite\Facebook()->getOption('test_api_event_code');
        $savedLang = (array)PixelYourSite\Facebook()->getOption("pixel_lang");
        foreach ($oldPixels as $index => $pixel_id) {
            if ($index === 0) {
                continue; // skip default ID
            }
            $pixel = new SPPixelId();
            $pixel->pixel = $pixel_id;

            $pixel->wpmlActiveLang = isset($savedLang[$index]) ? explode("_", $savedLang[$index]) : null;
            $pixel->extensions = [
                "api_token" => isset($apiTokens[$index]) ? $apiTokens[$index] : "",
                "api_code" => isset($apiTestCodes[$index]) ? $apiTestCodes[$index] : "",
            ];
            $fbPixels[] = json_encode(SPPixelId::toArray($pixel));
        }
    }

    // fb WOO category pixel
    $categoryPixels = (array)PixelYourSite\Facebook()->getOption('category_pixel_ids');

    if($categoryPixels && is_array($categoryPixels) && PixelYourSite\isWooCommerceActive()) {
        $apiTokens = (array)PixelYourSite\Facebook()->getOption('category_pixel_server_ids');
        $apiTestCodes = (array)PixelYourSite\Facebook()->getOption('category_pixel_server_test_code');
        foreach ($categoryPixels as $index => $pixel_id) {
            $pixel = new SPPixelId();
            $pixel->pixel = $pixel_id;
            $pixel->extensions = [
                "api_token" => isset($apiTokens[$index]) ? $apiTokens[$index] : "",
                "api_code" => isset($apiTestCodes[$index]) ? $apiTestCodes[$index] : "",
            ];
            $name = '';
            if( $term = get_term_by( 'id', $index, 'product_cat' ) ){
                $name = $term->name;
            }
            $pixel->displayConditions=[['name'=>'woocommerce','sub_name' => 'in_product_cat','sub_id'=>$index,'sub_id_name'=>$name]];
            $fbPixels[] = json_encode(SPPixelId::toArray($pixel));
        }
    }

    // fb EDD category pixel
    $categoryPixels = (array)PixelYourSite\Facebook()->getOption('edd_category_pixel_ids');

    if($categoryPixels && is_array($categoryPixels) && PixelYourSite\isEddActive()) {
        $apiTokens = (array)PixelYourSite\Facebook()->getOption('edd_category_pixel_server_ids');
        $apiTestCodes = (array)PixelYourSite\Facebook()->getOption('edd_category_pixel_server_test_code');
        foreach ($categoryPixels as $index => $pixel_id) {
            $pixel = new SPPixelId();
            $pixel->pixel = $pixel_id;
            $pixel->extensions = [
                "api_token" => isset($apiTokens[$index]) ? $apiTokens[$index] : "",
                "api_code" => isset($apiTestCodes[$index]) ? $apiTestCodes[$index] : "",
            ];
            $name = '';
            if( $term = get_term_by( 'id', $index, 'download_category' ) ){
                $name = $term->name;
            }
            $pixel->displayConditions=[['name'=>'edd','sub_name' => 'in_download_category','sub_id'=>$index,'sub_id_name'=>$name]];
            $fbPixels[] = json_encode(SPPixelId::toArray($pixel));
        }
    }

    $options['fb_ext_pixel_id'] = $fbPixels;

    //Google Analytics
    $oldGaPixels = (array)PixelYourSite\GA()->getOption( 'tracking_id' );
    if (is_array($oldGaPixels) && count($oldGaPixels) > 1) {
        $gaPixels = [];
        $isDebug = (array)PixelYourSite\GA()->getOption('is_enable_debug_mode');
        $savedGaLang = (array)PixelYourSite\GA()->getOption("pixel_lang");
        if(!$savedGaLang) $savedGaLang = array();

        foreach ($oldGaPixels as $index => $pixel_id) {
            if ($index === 0) {
                continue; // skip default ID
            }
            $pixel = new SPPixelId();
            $pixel->pixel = $pixel_id;

            $pixel->wpmlActiveLang = isset($savedGaLang[$index]) ? explode("_", $savedGaLang[$index]) : null;
            $pixel->extensions = [
                "debug_mode" => isset($isDebug[$index]) ? $isDebug[$index] : false,
            ];
            $gaPixels[] = json_encode(SPPixelId::toArray($pixel));
        }
        $options['ga_ext_pixel_id'] = $gaPixels;
    }

    //Google Ads Tag:
    $adsPixels = [];
    $oldAdsPixels = PixelYourSite\Ads()->getOption( 'ads_ids' );
    if (is_array($oldAdsPixels) && count($oldAdsPixels) > 1) {
        $savedAdsLang = (array)PixelYourSite\Ads()->getOption("pixel_lang");
        foreach ($oldAdsPixels as $index => $pixel_id) {
            if ($index === 0) {
                continue; // skip default ID
            }
            $pixel = new SPPixelId();
            $pixel->pixel = $pixel_id;
            $pixel->wpmlActiveLang = isset($savedAdsLang[$index]) ? explode("_", $savedAdsLang[$index]) : null;
            $adsPixels[] = json_encode(SPPixelId::toArray($pixel));
        }
        $options['ads_ext_pixel_id'] = $adsPixels;
    }

    // update settings
    if(count($options) > 0) {
        PixelYourSite\SuperPack()->updateOptions( $options );
        PixelYourSite\SuperPack()->reloadOptions();
    }
}

function migrate_v1_options() {

	$v1 = get_option( 'pys_super_pack', array() );
	
	$v2 = array(
		'license_key'     => isset( $v1['license_key'] ) ? $v1['license_key'] : null,
		'license_status'  => isset( $v1['license_status'] ) ? $v1['license_status'] : null,
		'license_expires' => isset( $v1['license_expires'] ) ? $v1['license_expires'] : null,
		
		'additional_ids_enabled'                   => isset( $v1['additional_ids_enabled'] ) ? $v1['additional_ids_enabled'] : null,
		'dynamic_params_enabled'                   => isset( $v1['dynamic_params_enabled'] ) ? $v1['dynamic_params_enabled'] : null,
		
		'custom_thank_you_page_enabled'            => isset( $v1['custom_thank_you_page_enabled'] ) ? $v1['custom_thank_you_page_enabled'] : null,
		'woo_custom_thank_you_page_global_enabled' => isset( $v1['custom_thank_you_page_global_enabled'] ) ? $v1['custom_thank_you_page_global_enabled'] : null,
		'woo_custom_thank_you_page_global_url'     => isset( $v1['custom_thank_you_page_global_url'] ) ? $v1['custom_thank_you_page_global_url'] : null,
		'woo_custom_thank_you_page_global_cart'    => isset( $v1['custom_thank_you_page_global_cart'] ) ? $v1['custom_thank_you_page_global_cart'] : null,
		'edd_custom_thank_you_page_global_enabled' => isset( $v1['edd_custom_thank_you_page_global_enabled'] ) ? $v1['edd_custom_thank_you_page_global_enabled'] : null,
		'edd_custom_thank_you_page_global_url'     => isset( $v1['edd_custom_thank_you_page_global_url'] ) ? $v1['edd_custom_thank_you_page_global_url'] : null,
		'edd_custom_thank_you_page_global_cart'    => isset( $v1['edd_custom_thank_you_page_global_cart'] ) ? $v1['edd_custom_thank_you_page_global_cart'] : null,
		'remove_pixel_enabled'                     => isset( $v1['remove_pixel_enabled'] ) ? $v1['remove_pixel_enabled'] : null,
		'amp_enabled'                              => isset( $v1['amp_enabled'] ) ? $v1['amp_enabled'] : null
	);
	
	// cleanup
	foreach ( $v2 as $key => $value ) {
		if ( $value === null ) {
			unset( $v2[ $key ] );
		}
	}
	
	// update settings
	PixelYourSite\SuperPack()->updateOptions( $v2 );
	PixelYourSite\SuperPack()->reloadOptions();
	
}
<?php

namespace PixelYourSite\SuperPack;

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use PixelYourSite;
use PixelYourSite\SuperPack;

if ( PixelYourSite\SuperPack()->getOption( 'enabled' ) && PixelYourSite\SuperPack()->getOption( 'dynamic_params_enabled' ) ) {
	add_action( 'pys_superpack_dynamic_params_help', 'PixelYourSite\SuperPack\renderDynamicParamsHelp' );
}

if ( PixelYourSite\SuperPack()->configured() && PixelYourSite\SuperPack()->getOption( 'dynamic_params_enabled' ) ) {
	add_filter( 'pys_superpack_dynamic_params', 'PixelYourSite\SuperPack\replaceDynamicParamsPlaceholders', 10, 2 );
}

function renderDynamicParamsHelp() {
	/** @noinspection PhpIncludeInspection */
	require_once PYS_SUPER_PACK_PATH . '/modules/superpack/views/html-dynamic-params-help.php';
}

//@todo: +2.1+ cache values
function replaceDynamicParamsPlaceholders( $params, $context ) {

	foreach ( $params as $key => $value ) {

		if ( $value !== null && false !== strpos( $value, '[id]' ) ) {
			$params[ $key ] = replaceContentID( $value, $context );
		}

		if ( $value !== null && false !== strpos( $value, '[title]' ) ) {
			$params[ $key ] = replaceContentTitle( $value );
		}

		if ( $value !== null && false !== strpos( $value, '[content_type]' ) ) {
			$params[ $key ] = replaceContentType( $value );
		}

		if ( $value !== null && false !== strpos( $value, '[categories]' ) ) {
			$params[ $key ] = replaceContentCategories( $value );
		}

		if ( $value !== null && false !== strpos( $value, '[tags]' ) ) {
			$params[ $key ] = replaceContentTags( $value );
		}

		if ( $value !== null && false !== strpos( $value, '[total]' ) ) {
			$params[ $key ] = replaceTotalParam( $value );
		}

		if ( $value !== null && false !== strpos( $value, '[subtotal]' ) ) {
			$params[ $key ] = replaceSubtotalParam( $value );
		}

		if ( $value !== null && substr( $value, 0, 5 ) == '[url_' ) {
			$query = substr( $value, 5, strlen( $value ) - 6 );
			if ( isset( $_GET[ $query ] ) ) {
				$params[ $key ] = $_GET[ $query ];
			} else {
				$params[ $key ] = '';
			}
		}

		if ( $value && preg_match( '/.*?\[field_(.*)].*/i', $value, $matches ) ) {
			$params[ $key ] = $matches[ 1 ];
		}
	}

	if ( SuperPack\isMembershipActive() ) {
		$params = replaceMembershipParams( $params );
	}

	return $params;

}

function replaceContentID( $value, $context ) {
	global $post;

	$content_id = is_singular() ? $post->ID : '';

	if ( $context == 'facebook' ) {
		return str_replace( '[id]', "['" . $content_id . "']", $value );
	} else {
		return str_replace( '[id]', $content_id, $value );
	}

}

function replaceContentTitle( $value ) {
	global $post;

	if ( is_singular() && !is_page() ) {

		$title = $post->post_title;

	} elseif ( is_page() || is_home() ) {

		$title = is_home() == true ? get_bloginfo( 'name' ) : $post->post_title;

	} elseif ( PixelYourSite\isWooCommerceActive() && is_shop() ) {

		$title = get_the_title( wc_get_page_id( 'shop' ) );

	} elseif ( is_category() || is_tax() || is_tag() ) {

		if ( is_category() ) {

			$cat = get_query_var( 'cat' );
			$term = get_category( $cat );

		} elseif ( is_tag() ) {

			$slug = get_query_var( 'tag' );
			$term = get_term_by( 'slug', $slug, 'post_tag' );

		} else {

			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

		}

		$title = $term->name;

	} else {

		$title = '';

	}

	return str_replace( '[title]', $title, $value );

}

function replaceContentType( $value ) {

	if ( is_singular() ) {
		$content_type = get_post_type();
	} else {
		$content_type = '';
	}

	return str_replace( '[content_type]', $content_type, $value );

}

function replaceContentCategories( $value ) {
	global $post;

	$content_categories = is_single() ? PixelYourSite\getObjectTerms( 'category', $post->ID ) : '';

	if ( is_array( $content_categories ) ) {
		$content_categories = implode( ", ", $content_categories );
	}

	return str_replace( '[categories]', $content_categories, $value );

}

function replaceContentTags( $value ) {
	global $post;

	$content_tags = is_single() ? PixelYourSite\getObjectTerms( 'post_tag', $post->ID ) : '';

	if ( is_array( $content_tags ) ) {
		$content_tags = implode( ", ", $content_tags );
	}
	return str_replace( '[tags]', $content_tags, $value );

}

function replaceTotalParam( $value ) {

	if ( PixelYourSite\isWooCommerceActive() && PixelYourSite\PYS()->getOption( 'woo_enabled' ) ) {
		if ( PixelYourSite\PYS()->woo_is_order_received_page() && isset( $_REQUEST[ 'key' ] ) ) {
			$order_key = sanitize_key( $_REQUEST[ 'key' ] );
			$cache_key = 'order_id_' . $order_key;
			$order_id = get_transient( $cache_key );
			if ( empty( $order_id ) ) {
				$order_id = (int) wc_get_order_id_by_order_key( $order_key );
				set_transient( $cache_key, $order_id, HOUR_IN_SECONDS );
			}
            $order    = wc_get_order( $order_id );

			if ( $order ) {
				$total = $order->get_total( 'edit' );
				return str_replace( '[total]', $total, $value );
			}

		}
	}

	if ( PixelYourSite\isEddActive() && PixelYourSite\PYS()->getOption( 'edd_enabled' ) ) {
		if ( edd_is_success_page() ) {

			$payment_key = PixelYourSite\getEddPaymentKey();
			$payment_id = (int) edd_get_purchase_id_by_key( $payment_key );

			$total = PixelYourSite\getEddOrderTotal( $payment_id );

			return str_replace( '[total]', $total, $value );

		}
	}

	return str_replace( '[total]', null, $value );

}

function replaceSubtotalParam( $value ) {

	if ( PixelYourSite\isWooCommerceActive() && PixelYourSite\PYS()->getOption( 'woo_enabled' ) ) {
		if ( PixelYourSite\PYS()->woo_is_order_received_page() && isset( $_REQUEST[ 'key' ] ) ) {
			$order_key = sanitize_key( $_REQUEST[ 'key' ] );
			$cache_key = 'order_id_' . $order_key;
			$order_id = get_transient( $cache_key );
			if ( empty( $order_id ) ) {
				$order_id = (int) wc_get_order_id_by_order_key( $order_key );
				set_transient( $cache_key, $order_id, HOUR_IN_SECONDS );
			}
            $order    = wc_get_order( $order_id );

			if ( $order ) {
				$subtotal = $order->get_subtotal() + $order->get_total_tax( 'edit' );
				return str_replace( '[subtotal]', $subtotal, $value );
			}

		}
	}

	if ( PixelYourSite\isEddActive() && PixelYourSite\PYS()->getOption( 'edd_enabled' ) ) {
		if ( edd_is_success_page() ) {

			$payment_key = PixelYourSite\getEddPaymentKey();
			$payment_id = (int) edd_get_purchase_id_by_key( $payment_key );

			$subtotal = edd_get_payment_subtotal( $payment_id );

			return str_replace( '[subtotal]', $subtotal, $value );

		}
	}

	return str_replace( '[subtotal]', null, $value );

}

function replaceMembershipParams( $params ) {

	if ( class_exists( 'MeprCheckoutCtrl' ) && method_exists( 'MeprCheckoutCtrl', 'replace_tracking_codes' ) ) {

		$replace_keywords = array(
			'[mp_',
			']',
		);

		$MeprCheckoutCtrl = new \MeprCheckoutCtrl;

		foreach ( $params as $key => $value ) {
			if ( $value !== null && str_contains( $value, '[mp_' ) ) {
				$replaced_value = str_replace( $replace_keywords, '%%', $value );
				$replaced_value = $MeprCheckoutCtrl->replace_tracking_codes( '', $replaced_value );
				if ( str_contains( $replaced_value, '%%' ) ) {
					$params[ $key ] = $value;
				} else {
					$params[ $key ] = $replaced_value;
				}
			}
		}
	}

	return $params;
}
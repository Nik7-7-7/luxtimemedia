<?php
if (!defined('ABSPATH')) exit;

if (get_option('berqwp_enable_sandbox') == null) {
    update_option('berqwp_enable_sandbox', 0);
}

if (get_option('berqwp_webp_max_width') == null) {
    update_option('berqwp_webp_max_width', 1920);
}

if (get_option('berqwp_webp_quality') == null) {
    update_option('berqwp_webp_quality', 80);
}

if (get_option('berqwp_image_lazyloading') == null) {
    update_option('berqwp_image_lazyloading', 1);
}

if (get_option('berqwp_disable_webp') == null) {
    update_option('berqwp_disable_webp', 0);
}

if (get_option('berqwp_enable_cdn') == null) {
    update_option('berqwp_enable_cdn', 1);
}

if (get_option('berqwp_preload_fontfaces') == null) {
    update_option('berqwp_preload_fontfaces', 0);
}

if (get_option('berqwp_disable_emojis') == null) {
    update_option('berqwp_disable_emojis', 1);
}

if (get_option('berqwp_lazyload_youtube_embed') == null) {
    update_option('berqwp_lazyload_youtube_embed', 1);
}

if (get_option('berqwp_javascript_execution_mode') == null) {
    update_option('berqwp_javascript_execution_mode', 1);
}

if (get_option('berqwp_enable_preload_mostly_used_font') == null) {
    update_option('berqwp_enable_preload_mostly_used_font', 0);
}

if (get_option('berqwp_interaction_delay') == null) {
    update_option('berqwp_interaction_delay', '');
}

if (get_option('berq_opt_mode') == null) {
    update_option('berq_opt_mode', 2);
}

if (get_option('berqwp_optimize_post_types') == null) {
    update_option('berqwp_optimize_post_types', ['post', 'page', 'product']);
}

if (get_option('berqwp_optimize_taxonomies') == null) {
    update_option('berqwp_optimize_taxonomies', ['category', 'product_cat']);
}

if (get_option('berq_ignore_urls_params') == null) {
    update_option('berq_ignore_urls_params', ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid', 'msclkid']);
}

// if (get_option('berq_exclude_urls', null) == null) {
//     $exclude_urls = [];

//     if (class_exists('WooCommerce')) {
//         // WooCommerce is active
    
//         // Get cart URL
//         $cart_url = wc_get_cart_url();
    
//         // Get checkout URL
//         $checkout_url = wc_get_checkout_url();
    
//         $exclude_urls[] = esc_url($cart_url);
//         $exclude_urls[] = esc_url($checkout_url);

//     }
    
//     update_option('berq_exclude_urls', $exclude_urls);
    
// }

if (get_option('berq_exclude_urls', []) !== null) {
    $urls = get_option('berq_exclude_urls', []);

    if (class_exists('WooCommerce')) {
        $cart_url = wc_get_cart_url();
        $checkout_url = wc_get_checkout_url();

        if (!empty($cart_url) && !in_array($cart_url, $urls) && trailingslashit($cart_url) !== trailingslashit(home_url())) {
            $urls[] = esc_url($cart_url);
        }

        if (!empty($checkout_url) && !in_array($checkout_url, $urls) && trailingslashit($checkout_url) !== trailingslashit(home_url())) {
            $urls[] = esc_url($checkout_url);
        }

        update_option('berq_exclude_urls', $urls);

    }
}
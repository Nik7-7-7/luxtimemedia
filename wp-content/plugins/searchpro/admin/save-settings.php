<?php

if (!defined('ABSPATH'))
    exit;

if (isset($_POST['berqwp_save_nonce'])) {
    if (!wp_verify_nonce($_POST['berqwp_save_nonce'], 'berqwp_save_settings')) {
        die('Invalid nonce value');
    }

    if (!empty($_POST['berqwp_license_key'])) {
        if (berq_is_localhost() && get_site_url() !== 'http://berq-test.local') {
            return;
        }
        
        $key = sanitize_text_field($_POST['berqwp_license_key']);
        $key_response = $this->verify_license_key($key, 'slm_activate');

        if (!empty($key_response) && $key_response->result == 'success') {
            update_option('berqwp_license_key', $key);

            global $berqNotifications;
            $berqNotifications->success('BerqWP license key has been activated.');

            ?>
            <script>
                location.href = '<?php echo esc_html(get_admin_url() . 'admin.php?page=berqwp'); ?>';
            </script>
            <?php
            exit();
        } elseif ($key_response->result == 'error') {
            $error = $key_response->message;

            global $berqNotifications;
            $berqNotifications->error($error);

            ?>
            <script>
                location.href = '<?php echo esc_html(get_admin_url() . 'admin.php?page=berqwp'); ?>';
            </script>
            <?php
            exit();
        }
    }

    if (isset($_POST['berqwp_enable_sandbox'])) {
        update_option('berqwp_enable_sandbox', 1);
    } else {
        update_option('berqwp_enable_sandbox', 0);
    }

    if (isset($_POST['berqwp_enable_cache_for_loggedin'])) {
        update_option('berqwp_enable_cache_for_loggedin', 1);
    } else {
        update_option('berqwp_enable_cache_for_loggedin', 0);
    }

    if (isset($_POST['berqwp_cache_lifespan'])) {
        $val = (int) sanitize_text_field($_POST['berqwp_cache_lifespan']);
        update_option('berqwp_cache_lifespan', $val);
    }

    if (isset($_POST['berqwp_webp_max_width'])) {
        $val = (int) sanitize_text_field($_POST['berqwp_webp_max_width']);
        update_option('berqwp_webp_max_width', $val);
    }

    if (isset($_POST['berqwp_webp_quality'])) {
        $val = (int) sanitize_text_field($_POST['berqwp_webp_quality']);
        update_option('berqwp_webp_quality', $val);
    }

    if (isset($_POST['berqwp_image_lazyloading'])) {
        update_option('berqwp_image_lazyloading', 1);
    } else {
        update_option('berqwp_image_lazyloading', 0);
    }

    if (isset($_POST['berqwp_disable_webp'])) {
        update_option('berqwp_disable_webp', 1);
    } else {
        update_option('berqwp_disable_webp', 0);
    }

    if (isset($_POST['berqwp_enable_cdn'])) {
        update_option('berqwp_enable_cdn', 1);
    } else {
        update_option('berqwp_enable_cdn', 0);
    }

    if (isset($_POST['berqwp_preload_fontfaces'])) {
        update_option('berqwp_preload_fontfaces', 1);
    } else {
        update_option('berqwp_preload_fontfaces', 0);
    }

    if (isset($_POST['berqwp_disable_emojis'])) {
        update_option('berqwp_disable_emojis', 1);
    } else {
        update_option('berqwp_disable_emojis', 0);
    }

    if (isset($_POST['berqwp_lazyload_youtube_embed'])) {
        update_option('berqwp_lazyload_youtube_embed', 1);
    } else {
        update_option('berqwp_lazyload_youtube_embed', 0);
    }

    if (isset($_POST['berqwp_javascript_execution_mode'])) {
        $val = (int) sanitize_text_field($_POST['berqwp_javascript_execution_mode']);
        update_option('berqwp_javascript_execution_mode', $val);
    }

    $_POST['berqwp_optimize_post_types'] = $_POST['berqwp_optimize_post_types'] ?? [];
    if (isset($_POST['berqwp_optimize_post_types']) && is_array($_POST['berqwp_optimize_post_types'])) {
        update_option('berqwp_optimize_post_types', $_POST['berqwp_optimize_post_types']);
    }

    $_POST['berqwp_optimize_taxonomies'] = $_POST['berqwp_optimize_taxonomies'] ?? [];
    if (isset($_POST['berqwp_optimize_taxonomies']) && is_array($_POST['berqwp_optimize_taxonomies'])) {
        update_option('berqwp_optimize_taxonomies', $_POST['berqwp_optimize_taxonomies']);
    }

    if (isset($_POST['berqwp_interaction_delay'])) {
        $val = sanitize_text_field($_POST['berqwp_interaction_delay']);
        update_option('berqwp_interaction_delay', $val);
    }

    if (isset($_POST['berq_opt_mode'])) {
        
        $val = sanitize_text_field($_POST['berq_opt_mode']);
        
        if ($val !== get_option( 'berq_opt_mode' )) {
            do_action( 'berqwp_before_update_optimization_mode' );
        }

        update_option('berq_opt_mode', $val);
    }

    if (isset($_POST['berqwp_enable_preload_mostly_used_font'])) {
        update_option('berqwp_enable_preload_mostly_used_font', 1);
    } else {
        update_option('berqwp_enable_preload_mostly_used_font', 0);
    }

    /*if (!empty($_POST['berqwp_license_key'])) {
        if (berq_is_localhost()) {
            return;
        }
        $key = sanitize_text_field($_POST['berqwp_license_key']);
        $key_response = $this->verify_license_key($key, 'slm_activate');

    
        if (!empty($key_response) && $key_response->result == 'success') {
            update_option('berqwp_license_key', $key);
            delete_transient( 'berq_lic_response_cache' );
            
        } elseif ($key_response->result == 'error') {
            $error = $key_response->message;
            ?>
            <div class="notice notice-error is-dismissible">
                <?php echo $error; ?>
            </div>
            <?php
        }
    }*/

    if (!empty($_POST['berq_deactivate_key'])) {
        $license_key = get_option('berqwp_license_key');
        $key_response = $this->verify_license_key($license_key, 'slm_deactivate');
        
        delete_transient('berq_lic_response_cache');
        delete_option('berqwp_license_key');
        // if (!empty($key_response) && $key_response->result == 'success') {
        // }

        global $berqNotifications;
        $berqNotifications->success('BerqWP license key has been deactivated.');

        ?>
        <script>
            location.href = '<?php echo esc_html(get_admin_url() . 'admin.php?page=berqwp'); ?>';
        </script>
        <?php
        exit;
    }

    if (isset($_POST['berq_exclude_urls'])) {
        $urls = sanitize_textarea_field($_POST['berq_exclude_urls']);
        $urls_array = explode("\n", $urls);

        // Add trailing slash to each URL
        $urls_array = array_map(function ($url) {
            if (!empty ($url)) {
                return trailingslashit(trim($url));
            }
        }, $urls_array);

        if (isset($urls_array)) {
            update_option('berq_exclude_urls', $urls_array);
        }

    }


    if (!empty($_POST['berq_ignore_urls_params'])) {
        $urls = sanitize_textarea_field($_POST['berq_ignore_urls_params']);
        $urls_array = explode("\n", $urls);

        if (!empty($urls_array)) {
            update_option('berq_ignore_urls_params', $urls_array);
        }

    }

    if (isset($_POST['berq_exclude_js_css'])) {
        $urls = sanitize_textarea_field($_POST['berq_exclude_js_css']);
        $urls_array = explode("\n", $urls);

        if (isset($urls_array)) {
            update_option('berq_exclude_js_css', $urls_array);
        }

    }

    global $berqNotifications;
    $berqNotifications->success('Changes have been saved! Please flush the cache to make changes visible for the visitors.');

    
    ?>
        <script>
            location.href = '<?php echo esc_html(get_admin_url() . 'admin.php?page=berqwp'); ?>';
        </script>
        <?php
        exit;

}

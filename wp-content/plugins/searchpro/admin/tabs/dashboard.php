<?php
if (!defined('ABSPATH')) exit;

$post_types = get_option('berqwp_optimize_post_types');
$paged = 1;
$posts_per_page = 500; // Number of posts to process per batch
$total_pages = 0;
$cached_pages = 0;
$optimized_pages = [];

do {
    $args = array(
        'post_type' => $post_types,
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
    );

    $query = new WP_Query($args);

    while ($query->have_posts()) {
        $query->the_post();

        $url = trailingslashit(get_permalink());
        $slug = str_replace(home_url(), '', $url);

        if (berqwp_is_slug_excludable($slug)) {
            continue;
        }

        $cache_directory = optifer_cache . '/html/';
        $cache_key = md5($slug);
        $cache_file = $cache_directory . $cache_key . '.html';

        if (file_exists($cache_file)) {
            $cached_pages++;

            $page_arr = [
                'url' => $url,
                'last_modified' => filemtime($cache_file)
            ];

            array_push($optimized_pages, $page_arr);
        }

        $total_pages++;
    }

    // Reset post data
    wp_reset_postdata();

    // Increase the paged value to get the next set of posts
    $paged++;

} while ($query->have_posts());

// $optimized_pages = [
//     ["url" => "test url", "last_modified" => "1721741554"],
//     ["url" => "test url", "last_modified" => "1721741554"],
//     ["url" => "test url", "last_modified" => "1721741554"],
//     ["url" => "test url", "last_modified" => "1721741554"],
//     ["url" => "test url", "last_modified" => "1721741554"],
//     ["url" => "test url", "last_modified" => "1721741554"],
//     ["url" => "test url", "last_modified" => "1721741554"],
//     ["url" => "test url", "last_modified" => "1721741554"],
//     ["url" => "test url", "last_modified" => "1721741554"],
//     ["url" => "test url", "last_modified" => "1721741554"],
//     ["url" => "test url", "last_modified" => "1721741554"],
//     ["url" => "test url", "last_modified" => "1721741554"],
//     ["url" => "test url", "last_modified" => "1721741554"],
//     ["url" => "test url", "last_modified" => "1721741554"],
//     ["url" => "test url", "last_modified" => "1721741554"],
// ];

wp_reset_postdata();

$cached_percentage = round((count($optimized_pages) / $total_pages) * 100, 2);

?>
<div id="dashboard">
    <h2 class="berq-tab-title">Dashboard</h2>
    <div class="berq-info-box guide">
        <div class="berq-box-content">
            <p><?php esc_html_e('Guide:', 'searchpro'); ?> <a href="https://berqwp.com/help-center/get-started-with-berqwp/" target="_blank"><?php esc_html_e('Get Started With BerqWP', 'searchpro'); ?></a></p>
        </div>
            
    </div>

    <?php 
    if (bwp_cached_pages_count() <= 0 ) {
        bwp_dash_notification("We're currently building the cache for this website, which may take up to 5 minutes. Thank you for your patience, good things are worth the wait.", 'warning');
    }
    ?>

    <div class="berq-info-box">
        <h3 class="berq-box-title"><?php esc_html_e('Optimization Mode', 'searchpro'); ?></h3>
        <div class="berq-box-content">
            <p style="margin-bottom:40px"><?php esc_html_e("Optimization modes are optimization presets that allow you to balance your website between the best optimization score and website functionality stability.", 'searchpro'); ?> <a href="https://berqwp.com/help-center/berqwp-optimization-modes/" target="_blank"><?php esc_html_e("Learn more", 'searchpro'); ?></a> </p>
            <div class="optimzation-slider">
                <input id="berq_opt_mode" name="berq_opt_mode" type="text" value="<?php echo esc_attr( get_option('berq_opt_mode') ); ?>" style="display:none" />
            </div>

        </div>
    </div>
    <div class="berq-info-box before-after-comparision">
    <h3 class="berq-box-title"><?php esc_html_e('Google PageSpeed Score', 'searchpro'); ?></h3>
        <div class="without-berqwp">
            <?php
            if (get_option('berqwp_enable_sandbox')) {
                echo '<div class="berqw-sandbox">Sandbox Optimization</div>';
            }
            ?>
            <div class="berq-speed-score"></div>
            <p class="device-type"><?php esc_html_e('Device: Mobile', 'searchpro'); ?></p>
            <p class="website-url">
                <?php 
                $cache_directory = optifer_cache . '/html/';
                $is_home_ready = file_exists($cache_directory . md5('/') . '.html');
                $msg = '';

                if (get_option('berqwp_enable_sandbox')) {
                    $msg .= '/?berqwp';
                }

                if ($is_home_ready == false) {
                    $msg .= '<br>This page isn\'t cached yet';
                }
                echo wp_kses_post(home_url() . $msg); ?>
            </p>
            <h4><?php esc_html_e('Mobile Score', 'searchpro'); ?></h4>
        </div>
        <div class="with-berqwp">
            <?php
            if (get_option('berqwp_enable_sandbox')) {
                echo '<div class="berqw-sandbox">Sandbox Optimization</div>';
            }
            ?>
            <div class="berq-speed-score"></div>
            <p class="device-type"><?php esc_html_e('Device: Desktop', 'searchpro'); ?></p>
            <p class="website-url">
                <?php 
                $cache_directory = optifer_cache . '/html/';
                $is_home_ready = file_exists($cache_directory . md5('/') . '.html');
                $msg = '';

                if (get_option('berqwp_enable_sandbox')) {
                    $msg .= '/?berqwp';
                }

                if ($is_home_ready == false) {
                    $msg .= '<br>This page isn\'t cached yet';
                }
                echo wp_kses_post(home_url() . $msg); ?>
            </p>
            <h4><?php esc_html_e('Desktop Score', 'searchpro'); ?></h4>
        </div>
    </div>

    <div class="berq-info-box">
        <h3 class="berq-box-title"><?php esc_html_e('Cached Pages', 'searchpro'); ?></h3>
        <div class="berq-box-content">
            <div class="cache-percentage"><p><b><?php echo $cached_percentage; ?>%</b> (<?php echo $cached_pages; ?>) of your pages are currently cached.</p></div>
            <div class="cached-pages-bar">
                <div class="progress-bar" style="width:<?php echo $cached_percentage; ?>%"></div>
            </div>

            <?php
            if ($this->key_response->product_ref == 'Free Account' && bwp_cached_pages_count() >= 10) {
                bwp_dash_notification("You've reached the limit of 10 optimized pages for your free BerqWP account. Upgrade now to optimize unlimited pages and get the best performance for your entire site!", "warning");
            }
            ?>

            <div class="optimized-pages">
                <table>
                    <thead>
                        <tr>
                            <th>Page URL</th>
                            <th>Last Optimized Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if (!empty($optimized_pages)) {
                        foreach ($optimized_pages as $page) {
                            $row_html = "<tr>";
                            $row_html .= "<td>";
                            $row_html .= $page['url'];
                            $row_html .= "</td>";
                            $row_html .= "<td>";
                            $row_html .= date('Y-m-d H:i:s', $page['last_modified']);
                            $row_html .= "</td>";
                            $row_html .= "</tr>";
                            echo $row_html;
                        }
                    }
                    ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="berq-dual-box">
        <div class="berq-info-box">
            <h3 class="berq-box-title"><?php esc_html_e('My Account', 'searchpro'); ?></h3>
            <div class="berq-box-content">

                <?php if ($this->key_response->product_ref !== 'AppSumo Deal') { ?>
                <p>
                    <?php esc_html_e('License:', 'searchpro'); ?>
                    <?php echo esc_html( $this->key_response->product_ref ); ?>
                </p>
                <?php } ?>

                <p><?php esc_html_e('License status:', 'searchpro'); ?>
                    <?php echo esc_html( $this->key_response->status ); ?>
                </p>

                <?php if ($this->key_response->product_ref !== 'AppSumo Deal' && $this->key_response->product_ref !== 'Free Account') { ?>
                <p><?php esc_html_e('Expiration date:', 'searchpro'); ?>
                    <?php echo esc_html( $this->key_response->date_expiry ); ?>
                </p>
                <?php } ?>
                
            </div>
        </div>
        <div class="berq-info-box">
            <h3 class="berq-box-title"><?php esc_html_e('Quick Actions', 'searchpro'); ?></h3>
            <div class="berq-box-content">
                <a href="<?php echo esc_attr(wp_nonce_url(admin_url('admin-post.php?action=clear_cache'), 'clear_cache_action')); ?>" class="berq-btn"><?php esc_html_e('Flush cache', 'searchpro'); ?></a>
                <a href=https://berqwp.com/help-center/" target="_blank" class="berq-btn"><?php esc_html_e('Visit help center', 'searchpro'); ?></a>
            </div>
        </div>

    </div>
    <div class="berq-info-box">
        <h3 class="berq-box-title"><?php esc_html_e('Sandbox', 'searchpro'); ?></h3>
        <div class="berq-box-content">
            <p><?php esc_html_e("The Sandbox feature allows you to test BerqWP's optimizations without impacting real visitors. Note that pages will load slower when sandbox mode is enabled.", 'searchpro'); ?> <a href="https://berqwp.com/help-center/sandbox-mode-and-how-to-use-it/" target="_blank"><?php esc_html_e("Learn more", 'searchpro'); ?></a> </p>
            <label class="berq-check">
                <input type="checkbox" name="berqwp_enable_sandbox" <?php checked(1, get_option('berqwp_enable_sandbox'), true); ?>>
                <?php esc_html_e('Enable sandbox', 'searchpro'); ?>
            </label>
        </div>
    </div>

    <div class="berq-info-box">
        <h3 class="berq-box-title"><?php esc_html_e('BerqWP CDN', 'searchpro'); ?></h3>
        <div class="berq-box-content">
            <p><?php esc_html_e("BerqWP CDN delivers static files instantly to enhance website performance and user experience.", 'searchpro'); ?></p>
            <label class="berq-check">
                <input type="checkbox" name="berqwp_enable_cdn" <?php checked(1, get_option('berqwp_enable_cdn'), true); ?>>
                <?php esc_html_e('Enable BerqWP CDN', 'searchpro'); ?>
            </label>
        </div>
    </div>

    <button type="submit" class="berqwp-save"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M4.16663 10.8333L7.49996 14.1667L15.8333 5.83334" stroke="white" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <?php esc_html_e('Save changes', 'searchpro'); ?></button>
</div>
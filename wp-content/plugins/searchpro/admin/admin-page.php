<?php
if (!defined('ABSPATH'))
    exit;

$cached_pages = bwp_cached_pages_count();

?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500&display=swap" rel="stylesheet">
<link href="//cdn.datatables.net/2.1.0/css/dataTables.dataTables.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_attr(optifer_URL . '/admin/css/style.css?v=' . BERQWP_VERSION); ?>">
<link rel="stylesheet"
    href="<?php echo esc_attr(optifer_URL . '/admin/css/bootstrap-slider.min.css?v=' . BERQWP_VERSION); ?>">
<link rel="stylesheet" href="<?php echo esc_attr(optifer_URL . '/admin/css/bootstrap.min.css?v=' . BERQWP_VERSION); ?>">

<div class="wrap">
    <h1 style="display:none">BerqWP</h1>
    <div></div>
    <div class="berqwp-dashbaord">
        <div class="berqwp-header">
            <img src="<?php echo esc_attr(optifer_URL . '/admin/img/berqwp-logo-light.png'); ?>" alt="BerqWP Logo">
            <div class="berqwp-header-right">
                <a href="https://wordpress.org/support/plugin/searchpro/reviews/#new-post" target="_blank"
                    class="berqwp-support">
                    <?php esc_html_e('Write a review', 'searchpro'); ?>
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M11.0001 3C10.7349 3 10.4805 3.10536 10.293 3.29289C10.1054 3.48043 10.0001 3.73478 10.0001 4C10.0001 4.26522 10.1054 4.51957 10.293 4.70711C10.4805 4.89464 10.7349 5 11.0001 5H13.5861L7.29308 11.293C7.19757 11.3852 7.12139 11.4956 7.06898 11.6176C7.01657 11.7396 6.98898 11.8708 6.98783 12.0036C6.98668 12.1364 7.01198 12.2681 7.06226 12.391C7.11254 12.5139 7.18679 12.6255 7.28069 12.7194C7.37458 12.8133 7.48623 12.8875 7.60913 12.9378C7.73202 12.9881 7.8637 13.0134 7.99648 13.0123C8.12926 13.0111 8.26048 12.9835 8.38249 12.9311C8.50449 12.8787 8.61483 12.8025 8.70708 12.707L15.0001 6.414V9C15.0001 9.26522 15.1054 9.51957 15.293 9.70711C15.4805 9.89464 15.7349 10 16.0001 10C16.2653 10 16.5197 9.89464 16.7072 9.70711C16.8947 9.51957 17.0001 9.26522 17.0001 9V4C17.0001 3.73478 16.8947 3.48043 16.7072 3.29289C16.5197 3.10536 16.2653 3 16.0001 3H11.0001Z"
                            fill="#465774" />
                        <path
                            d="M5 5C4.46957 5 3.96086 5.21071 3.58579 5.58579C3.21071 5.96086 3 6.46957 3 7V15C3 15.5304 3.21071 16.0391 3.58579 16.4142C3.96086 16.7893 4.46957 17 5 17H13C13.5304 17 14.0391 16.7893 14.4142 16.4142C14.7893 16.0391 15 15.5304 15 15V12C15 11.7348 14.8946 11.4804 14.7071 11.2929C14.5196 11.1054 14.2652 11 14 11C13.7348 11 13.4804 11.1054 13.2929 11.2929C13.1054 11.4804 13 11.7348 13 12V15H5V7H8C8.26522 7 8.51957 6.89464 8.70711 6.70711C8.89464 6.51957 9 6.26522 9 6C9 5.73478 8.89464 5.48043 8.70711 5.29289C8.51957 5.10536 8.26522 5 8 5H5Z"
                            fill="#465774" />
                    </svg>

                </a>
                <?php if ($this->key_response->product_ref !== 'Free Account') { ?>
                    <a href="https://berqwp.com/support/" target="_blank" class="berqwp-support">
                        <?php esc_html_e('Contact support', 'searchpro'); ?>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M11.0001 3C10.7349 3 10.4805 3.10536 10.293 3.29289C10.1054 3.48043 10.0001 3.73478 10.0001 4C10.0001 4.26522 10.1054 4.51957 10.293 4.70711C10.4805 4.89464 10.7349 5 11.0001 5H13.5861L7.29308 11.293C7.19757 11.3852 7.12139 11.4956 7.06898 11.6176C7.01657 11.7396 6.98898 11.8708 6.98783 12.0036C6.98668 12.1364 7.01198 12.2681 7.06226 12.391C7.11254 12.5139 7.18679 12.6255 7.28069 12.7194C7.37458 12.8133 7.48623 12.8875 7.60913 12.9378C7.73202 12.9881 7.8637 13.0134 7.99648 13.0123C8.12926 13.0111 8.26048 12.9835 8.38249 12.9311C8.50449 12.8787 8.61483 12.8025 8.70708 12.707L15.0001 6.414V9C15.0001 9.26522 15.1054 9.51957 15.293 9.70711C15.4805 9.89464 15.7349 10 16.0001 10C16.2653 10 16.5197 9.89464 16.7072 9.70711C16.8947 9.51957 17.0001 9.26522 17.0001 9V4C17.0001 3.73478 16.8947 3.48043 16.7072 3.29289C16.5197 3.10536 16.2653 3 16.0001 3H11.0001Z"
                                fill="#465774" />
                            <path
                                d="M5 5C4.46957 5 3.96086 5.21071 3.58579 5.58579C3.21071 5.96086 3 6.46957 3 7V15C3 15.5304 3.21071 16.0391 3.58579 16.4142C3.96086 16.7893 4.46957 17 5 17H13C13.5304 17 14.0391 16.7893 14.4142 16.4142C14.7893 16.0391 15 15.5304 15 15V12C15 11.7348 14.8946 11.4804 14.7071 11.2929C14.5196 11.1054 14.2652 11 14 11C13.7348 11 13.4804 11.1054 13.2929 11.2929C13.1054 11.4804 13 11.7348 13 12V15H5V7H8C8.26522 7 8.51957 6.89464 8.70711 6.70711C8.89464 6.51957 9 6.26522 9 6C9 5.73478 8.89464 5.48043 8.70711 5.29289C8.51957 5.10536 8.26522 5 8 5H5Z"
                                fill="#465774" />
                        </svg>

                    </a>
                <?php } else { ?>
                    <a href="https://berqwp.com/dashboard/" target="_blank" class="upgrade-btn">
                        <?php esc_html_e('Upgrade to Premium', 'searchpro'); ?>
                    </a>
                <?php } ?>
            </div>
        </div>
        <div class="berqwp-dashbord-body">
            <div class="berqwp-tabs">
                <div class="berqwp-tab active" data-tab="dashboard">
                    <?php esc_html_e('Dashboard', 'searchpro'); ?>
                    <p>
                        <?php esc_html_e('Flush Cache, Sandbox Mode, Cached Pages', 'searchpro'); ?>
                    </p>
                </div>
                <div class="berqwp-tab" data-tab="cache-management">
                    <?php esc_html_e('Cache Management', 'searchpro'); ?>
                    <p>
                        <?php esc_html_e('Exclude Pages, Ignore URL Parameters', 'searchpro'); ?>
                    </p>
                </div>
                <div class="berqwp-tab" data-tab="image-optimization">
                    <?php esc_html_e('Image Optimization', 'searchpro'); ?>
                    <p>
                        <?php esc_html_e('WebP Images, Image Resize, LazyLoad', 'searchpro'); ?>
                    </p>
                </div>
                <div class="berqwp-tab" data-tab="script-manager">
                    <?php esc_html_e('Script Manager', 'searchpro'); ?>
                    <p>
                        <?php esc_html_e('JavaScript Modes, Emojis, YouTube', 'searchpro'); ?>
                    </p>
                </div>
                <div class="berqwp-tab" data-tab="activate-license">
                    <?php esc_html_e('License', 'searchpro'); ?>
                    <p>
                        <?php esc_html_e('Deactivate License Key', 'searchpro'); ?>
                    </p>
                </div>
            </div>
            <div class="berqwp-tab-content">
                <form action="" method="post">
                    <?php
                    wp_nonce_field('berqwp_save_settings', 'berqwp_save_nonce');
                    require_once optifer_PATH . '/admin/tabs/dashboard.php';
                    require_once optifer_PATH . '/admin/tabs/cache-management.php';
                    require_once optifer_PATH . '/admin/tabs/image-optimization.php';
                    require_once optifer_PATH . '/admin/tabs/script-manager.php';
                    require_once optifer_PATH . '/admin/tabs/activate-license.php';
                    ?>
                </form>
            </div>
        </div>
    </div>
</div>
<p class="below-settings-panel">Explore <a href="https://berqier.com" target="_blank">Berqier Ltd</a>, the creators of
    BerqWP. Want to know more? Reach out—we love talking WordPress!</p>
<script src="<?php echo esc_attr(optifer_URL . '/admin/js/bootstrap-slider.js?v=' . BERQWP_VERSION); ?>"></script>
<script src="//cdn.datatables.net/2.1.0/js/dataTables.min.js"></script>
<script>
    (function ($) {
        $(document).ready(function () {
            $('.bwp_feedback').hide();
            let berq_nounce = '<?php echo esc_html(wp_create_nonce('wp_rest')); ?>';

            function numberWithCommas(x) {
                return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            function berq_clear_cache() {
                $.ajax({
                    url: '<?php echo esc_html(get_site_url()); ?>/wp-json/optifer/v1/clear-cache',
                    method: 'POST',
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', berq_nounce);
                    },
                    success: function (response) {

                    }
                })
            }

            let opt_slider = new Slider('#berq_opt_mode', {
                // min: 5, 
                // max: 30, 
                value: '<?php echo esc_html(get_option('berq_opt_mode', 4)); ?>',
                ticks_labels: ['Basic', 'Medium', 'Blaze', 'Aggressive'],
                ticks: [1, 2, 3, 4],
                // ticks_positions: [0, 50, 100],
                ticks_snap_bounds: 20,
                tooltip_position: 'bottom',
                // formatter: function (value) {
                //     if (value == 1) {
                //         // return '<div class="tooltip-title">Basic (stable)</div> <div class="tooltip-content">Basic optimizations like image lazy loading, page cache etc</div>';
                //         $("div.tooltip-inner").html('<div class="tooltip-title">Basic (stable)</div> <div class="tooltip-content">Basic optimizations like image lazy loading, page cache etc</div>')

                //         return '';
                //     }
                // },
                ticks_tooltip: true,
                // ticks_tooltip: true,
                // lock_to_ticks: true,
                // step: 1
            });

            $(".optimzation-slider").mousemove(function (event) {
                let val = $("#berq_opt_mode").val();

                if ($(".slider-tick:nth-child(1)").is(":hover") || $('.tooltip-inner').html() == '1') {
                    $("div.tooltip-inner").html('<div class="tooltip-title">Basic</div> <div class="tooltip-content">Basic optimizations like image lazy loading, page cache, URL prefectch etc.</div>')
                }

                if ($(".slider-tick:nth-child(2)").is(":hover") || $('.tooltip-inner').html() == '2') {
                    $("div.tooltip-inner").html('<div class="tooltip-title">Medium</div> <div class="tooltip-content">Highly stable optimization mode for many cases.</div>')
                }

                if ($(".slider-tick:nth-child(3)").is(":hover") || $('.tooltip-inner').html() == '3') {
                    $("div.tooltip-inner").html('<div class="tooltip-title">Blaze</div> <div class="tooltip-content">Balance between optimization and stability.</div>')
                }

                if ($(".slider-tick:nth-child(4)").is(":hover") || $('.tooltip-inner').html() == '4') {
                    $("div.tooltip-inner").html('<div class="tooltip-title">Aggressive</div> <div class="tooltip-content">Provide the best possible speed scores.</div>')
                }
            })

            $("#berq_opt_mode").on("slide slideStop", function (slideEvt) {
                let val = $("#berq_opt_mode").val();

                if (val == 1) {
                    $("div.tooltip-inner").html('<div class="tooltip-title">Basic (stable)</div> <div class="tooltip-content">Basic optimizations like image lazy loading, page cache etc</div>')
                }

                if (val == 2) {
                    $("div.tooltip-inner").html('<div class="tooltip-title">Medium (stable)</div> <div class="tooltip-content">Basic optimizations like image lazy loading, page cache etc</div>')
                }

                if (val == 3) {
                    $("div.tooltip-inner").html('<div class="tooltip-title">Blaze</div> <div class="tooltip-content">Basic optimizations like image lazy loading, page cache etc</div>')
                }

                if (val == 4) {
                    $("div.tooltip-inner").html('<div class="tooltip-title">Aggressive</div> <div class="tooltip-content">Basic optimizations like image lazy loading, page cache etc</div>')
                }
            })

            $('.berqwp-tab').click(function () {
                let tab = $(this).attr('data-tab');

                $('.berqwp-tab-content > form > div').hide();
                $('.berqwp-tab').removeClass('active');
                $(this).addClass('active');
                $(`.berqwp-tab-content #${tab}`).show();

            })

            <?php
            $cache_directory = optifer_cache . '/html/';
            $is_home_ready = file_exists($cache_directory . md5('/') . '.html');
            if ($is_home_ready) { ?>

                $.ajax({
                    method: 'GET',
                    url: 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed',
                    data: {
                        url: '<?php echo get_option('berqwp_enable_sandbox') ? esc_html(home_url() . '/?berqwp') : esc_html(home_url()); ?>',
                        strategy: 'mobile'
                    },
                    success: function (data) {
                        let mobileSpeedScore = data.lighthouseResult.categories.performance.score * 100;
                        if (mobileSpeedScore >= 90) {
                            $('.without-berqwp .berq-speed-score').css('background', `radial-gradient(closest-side, white 79%, transparent 80% 100%), conic-gradient(#2eb91e ${mobileSpeedScore}%, #caffd1 0)`);
                            $('.without-berqwp .berq-speed-score').css('color', '#2eb91e');

                        } else if (mobileSpeedScore >= 50) {
                            $('.without-berqwp .berq-speed-score').css('background', `radial-gradient(closest-side, white 79%, transparent 80%, transparent 100%), conic-gradient(rgb(246 174 76) ${mobileSpeedScore}%, rgb(255 248 202) 0deg)`);
                            $('.without-berqwp .berq-speed-score').css('color', 'rgb(246 174 76)');

                        } else if (mobileSpeedScore <= 49) {
                            $('.without-berqwp .berq-speed-score').css('background', `radial-gradient(closest-side, white 79%, transparent 80%, transparent 100%), conic-gradient(rgb(246 76 76) ${mobileSpeedScore}%, rgb(255 202 202) 0deg)`);
                            $('.without-berqwp .berq-speed-score').css('color', 'rgb(246 76 76)');
                        }

                        $('.without-berqwp .berq-speed-score').html(Math.round(mobileSpeedScore) + `<svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <g clip-path="url(#clip0_405_102)">
                                                                <path
                                                                    d="M29.2683 0H10.7317C4.80475 0 0 4.80475 0 10.7317V29.2683C0 35.1952 4.80475 40 10.7317 40H29.2683C35.1952 40 40 35.1952 40 29.2683V10.7317C40 4.80475 35.1952 0 29.2683 0Z"
                                                                    fill="#1F71FF" />
                                                                <path
                                                                    d="M14.3327 21.2558L26.3146 6.82928L21.3023 17.3L27.3171 18.3704L11.7073 34.1464L20.5862 21.2558H14.3327Z"
                                                                    fill="white" />
                                                            </g>
                                                            <defs>
                                                                <clipPath id="clip0_405_102">
                                                                    <rect width="40" height="40" fill="white" />
                                                                </clipPath>
                                                            </defs>
                                                        </svg>`);
                    }
                });

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'berqwp_fetch_remote_html',
                        nonce: berq_nounce,
                    },
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', berq_nounce);
                    },
                    success: function (data) {
                        // 'data' contains the HTML content from the specified URL
                        if ($(data).is('[data-berqwp]')) {
                            console.log('HTML contains data-berqwp attribute.');
                        } else {
                            $('.with-berqwp').addClass('cache-not-deleted');
                            console.log('HTML does not contain data-berqwp attribute.');
                        }
                    },
                    error: function () {
                        console.log('Error in AJAX request.');
                    }
                });


                $.ajax({
                    method: 'GET',
                    url: 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed',
                    data: {
                        url: '<?php echo get_option('berqwp_enable_sandbox') ? esc_html(home_url() . '/?berqwp') : esc_html(home_url()); ?>',
                        strategy: 'desktop'
                    },
                    success: function (data) {
                        let mobileSpeedScore = data.lighthouseResult.categories.performance.score * 100;

                        if (mobileSpeedScore >= 80 && $('.bwp_feedback')) {
                            $('.bwp_feedback').show();
                        }

                        if (mobileSpeedScore >= 90) {
                            $('.with-berqwp .berq-speed-score').css('background', `radial-gradient(closest-side, white 79%, transparent 80% 100%), conic-gradient(#2eb91e ${mobileSpeedScore}%, #caffd1 0)`);
                            $('.with-berqwp .berq-speed-score').css('color', '#2eb91e');
                        } else if (mobileSpeedScore >= 50) {
                            $('.with-berqwp .berq-speed-score').css('background', `radial-gradient(closest-side, white 79%, transparent 80%, transparent 100%), conic-gradient(rgb(246 174 76) ${mobileSpeedScore}%, rgb(255 248 202) 0deg)`);
                            $('.with-berqwp .berq-speed-score').css('color', 'rgb(246 174 76)');
                        } else if (mobileSpeedScore <= 49) {
                            $('.with-berqwp .berq-speed-score').css('background', `radial-gradient(closest-side, white 79%, transparent 80%, transparent 100%), conic-gradient(rgb(246 76 76) ${mobileSpeedScore}%, rgb(255 202 202) 0deg)`);
                            $('.with-berqwp .berq-speed-score').css('color', 'rgb(246 76 76)');
                        }
                        $('.with-berqwp .berq-speed-score').html(Math.round(mobileSpeedScore) + `<svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <g clip-path="url(#clip0_405_102)">
                                                                <path
                                                                    d="M29.2683 0H10.7317C4.80475 0 0 4.80475 0 10.7317V29.2683C0 35.1952 4.80475 40 10.7317 40H29.2683C35.1952 40 40 35.1952 40 29.2683V10.7317C40 4.80475 35.1952 0 29.2683 0Z"
                                                                    fill="#1F71FF" />
                                                                <path
                                                                    d="M14.3327 21.2558L26.3146 6.82928L21.3023 17.3L27.3171 18.3704L11.7073 34.1464L20.5862 21.2558H14.3327Z"
                                                                    fill="white" />
                                                            </g>
                                                            <defs>
                                                                <clipPath id="clip0_405_102">
                                                                    <rect width="40" height="40" fill="white" />
                                                                </clipPath>
                                                            </defs>
                                                        </svg>`);
                    }
                });
            <?php } ?>

            $.ajax({
                method: 'POST',
                url: '<?php echo esc_html(get_site_url()); ?>/wp-json/optifer/v1/media-ids',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', berq_nounce);
                },
                success: function (data) {
                    let unoptimized_images = data['unoptimized'];
                    let optimized_images = data['optimized'];
                    let total_images = unoptimized_images.length + optimized_images.length;
                    let optimized_images_count = optimized_images.length;
                    let unoptimized_images_count = unoptimized_images.length;
                    let loader = $('.berq-generating-images');

                    $('.berq-optimized-images span').html(optimized_images_count);
                    $('.berq-unoptimized-images span').html(unoptimized_images_count);
                    let wheel_per = (optimized_images_count / total_images) * 100;

                    $(".berq-progress-optimized-images").css('background', `radial-gradient(closest-side, white 79%, transparent 80% 100%), conic-gradient(#2763c9 ${wheel_per}%, #cadeff 0)`);
                    $(".berq-progress-optimized-images").html(`${Math.round(wheel_per)}%`);


                    function sendBatch(products, deleting = false) {
                        var batchSize = 5;
                        var totalBatches = Math.ceil(products.length / batchSize);
                        var currentBatchIndex = 0;

                        function processBatch() {
                            if (currentBatchIndex < totalBatches) {
                                var start = currentBatchIndex * batchSize;
                                var end = start + batchSize;
                                var currentBatch = products.slice(start, end);
                                var url = '<?php echo esc_html(get_site_url()); ?>/wp-json/optifer/v1/optimize-images';

                                if (deleting) {
                                    var url = '<?php echo esc_html(get_site_url()); ?>/wp-json/optifer/v1/delete-images';
                                }

                                $.ajax({
                                    url: url,
                                    method: 'POST',
                                    data: {
                                        images: JSON.stringify(currentBatch)
                                    },
                                    beforeSend: function (xhr) {
                                        xhr.setRequestHeader('X-WP-Nonce', berq_nounce);
                                    },
                                    success: function (response) {
                                        // Handle the successful response from the server
                                        if (!deleting) {
                                            if (unoptimized_images_count => batchSize) {
                                                unoptimized_images_count = unoptimized_images_count - batchSize;
                                                optimized_images_count = optimized_images_count + batchSize;
                                            }

                                            if (unoptimized_images_count < batchSize) {
                                                optimized_images_count = optimized_images_count + unoptimized_images_count;
                                                unoptimized_images_count = 0;
                                            }

                                            currentBatchIndex++;
                                            let per = (optimized_images_count / total_images) * 100;
                                            $(".berq-progress-optimized-images").css('background', `radial-gradient(closest-side, white 79%, transparent 80% 100%), conic-gradient(#2763c9 ${per}%, #cadeff 0)`);
                                            $(".berq-progress-optimized-images").html(`${Math.round(per)}%`);

                                            $('.berq-optimized-images span').html(numberWithCommas(optimized_images_count));
                                            $('.berq-unoptimized-images span').html(numberWithCommas(unoptimized_images_count));
                                        } else {
                                            currentBatchIndex++;
                                            let per = (currentBatchIndex / totalBatches) * 100;
                                            $(".berq-progress-optimized-images").css('background', `radial-gradient(closest-side, white 79%, transparent 80% 100%), conic-gradient(#2763c9 ${per}%, #cadeff 0)`);

                                            $(".berq-progress-optimized-images").css('font-size', '30px');
                                            $(".berq-progress-optimized-images").html(`Deleting...`);

                                            $('.berq-optimized-images').hide();
                                            $('.berq-unoptimized-images').hide();
                                        }


                                        processBatch(); // Process the next batch
                                    },
                                    error: function (xhr, status, error) {
                                        loader.hide();
                                        // Handle errors that occur during the AJAX request
                                        alert('<?php esc_html_e("Error: Unable to connect to the server. If the issue persists, please contact our support team.", "searchpro"); ?>');
                                    }
                                });
                            } else {
                                loader.hide();
                                berq_clear_cache();
                                if (deleting) {
                                    alert('<?php esc_html_e("All WebP images have been deleted.", "searchpro"); ?>');
                                    location.reload();
                                } else {
                                    alert('<?php esc_html_e("Image optimization completed!", "searchpro"); ?>');
                                }
                            }
                        }

                        processBatch(); // Start processing the batches
                    }

                    $('.berq-convert-webp').click(function () {
                        if (unoptimized_images.length > 0) {
                            loader.show();
                            sendBatch(unoptimized_images);
                        } else {
                            alert('<?php esc_html_e("All images have already been optimized for speed.", "searchpro"); ?>');
                        }
                    })

                    $('.berq-delete-webp').click(function () {
                        if (confirm('Are you sure?')) {
                            if (optimized_images.length > 0) {
                                sendBatch(optimized_images, true);
                            }
                        }
                    })



                }
            });
        })
    })(jQuery)
</script>
<script>
        (function ($) {
            $(document).ready(function () {
                let table = new DataTable('.optimized-pages > table', {
                    // "paging": false, // Disable pagination
                    "searching": false, // Disable the search box
                    "pageLength": 5,
                    "info": false, // Disable the info text (e.g., "Showing 1 to 10 of 57 entries")
                    "lengthChange": false
                });
            })
        })(jQuery)
</script>
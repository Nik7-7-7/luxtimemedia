<?php
if (!defined('ABSPATH'))
    exit;

?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_attr(optifer_URL . '/admin/css/style.css?v=' . BERQWP_VERSION); ?>">

<div class="wrap">
    <h1 style="display:none">BerqWP</h1>
    <div></div>
    <div id="berqwp-intro">
        <h2 class="title">Automate PageSpeed optimization with just a few clicks</h2>

        <form class="license-verification" action="" method="post">
            <?php wp_nonce_field('berqwp_save_settings', 'berqwp_save_nonce'); ?>
            <input type="password" name="berqwp_license_key" placeholder="Enter your license key" required>
            <input type="submit" value="Activate" disabled>
        </form>
        <?php
        if (!empty($error)) {
            echo wp_kses_post('<p style="color:red">'.$error.'</p>');
        }
        ?>
        <p class="license-msg">Create an account to obtain a BerqWP license key.</p>
        <div class="cta-btns">
            <a href="http://berqwp.com/pricing/" class="btn" target="_blank">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12 8L15 13.2L18 10.5L17.3 14H6.7L6 10.5L9 13.2L12 8ZM12 4L8.5 10L3 5L5 16H19L21 5L15.5 10L12 4ZM19 18H5V19C5 19.6 5.4 20 6 20H18C18.6 20 19 19.6 19 19V18Z"
                        fill="white" />
                </svg>
                Purchase Premium
            </a>
            <a href="https://berqwp.com/free-account/" class="btn" target="_blank">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2ZM20 16H5.17L4 17.17V4H20V16Z"
                        fill="white" />
                    <path d="M12 15L13.57 11.57L17 10L13.57 8.43L12 5L10.43 8.43L7 10L10.43 11.57L12 15Z"
                        fill="white" />
                </svg>

                Get Free Account
            </a>
        </div>
    </div>
</div>
<script src="<?php echo esc_attr(optifer_URL . '/admin/js/bootstrap-slider.js?v=' . BERQWP_VERSION); ?>"></script>
<script>
    (function($){
        $(document).ready(function() {

            $('input[name="berqwp_license_key"]').on('change, input', function () {
                if ($(this).val().length > 5) {
                    $('input[value="Activate"]').prop('disabled', false);                    
                } else {
                    $('input[value="Activate"]').prop('disabled', true);                    
                }
            })


        })
    })(jQuery)
</script>
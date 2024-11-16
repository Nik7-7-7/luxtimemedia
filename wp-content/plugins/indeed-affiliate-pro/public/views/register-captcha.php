<?php if ( $type !== false && $type == 'v3' ):?>
    <div class="js-uap-recaptcha-v3-item"></div>
    <span class="uap-js-register-captcha-key" data-value="<?php echo esc_attr($key);?>"></span>
    <?php
        wp_enqueue_script( 'uap-recaptcha-v3', 'https://www.google.com/recaptcha/api.js?render='.$key );
        wp_enqueue_script( 'uap-register-captcha', UAP_URL . 'assets/js/register-captcha.js', ['jquery'], 8.3 );
    ?>
<?php else :?>

    <div class="g-recaptcha-wrapper" class="<?php echo esc_attr($class);?>">
        <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($key);?>"></div>
    </div>
<?php wp_enqueue_script( 'uap-recaptcha-v2', 'https://www.google.com/recaptcha/api.js?hl='.indeed_get_current_language_code() );?>
<?php endif;?>

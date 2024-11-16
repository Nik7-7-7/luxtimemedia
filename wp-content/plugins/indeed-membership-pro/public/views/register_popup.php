<?php if ( !$isRegisterPage && empty( $uid ) ):?>
    <?php
        wp_enqueue_style( 'ihc_iziModal' );
        wp_enqueue_script( 'ihc_iziModal_js' );
        wp_enqueue_script( 'ihc_register_modal', IHC_URL . 'assets/js/IhcRegisterModal.js', ['jquery'], 10.1 );
    ?>

    <?php if ( $content ):?>
        <div class="ihc-register-modal-trigger">
            <?php echo esc_ump_content($content);?>
        </div>
    <?php endif;?>

    <div class="ihc-display-none" id="ihc_register_modal"  data-title="<?php esc_html_e('Register', 'ihc');?>" >
        <?php echo do_shortcode( '[ihc-register-form-for-popup is_modal=true]' );?>
    </div>

<?php endif;?>
<?php
$preventDefault = empty($trigger) ? 0 : 1;
$triggerSelector = empty($trigger) ? '.ihc-register-modal-trigger' : '.' . $trigger;
?>
<span class="ihc-js-register-popup-data"
      data-is_register_page="<?php if ( $isRegisterPage ) {echo 1;} else {echo 0;} ?>"
      data-is_registered="<?php
      if ( !empty( $uid ) ) {echo 1;} else if( !empty($_GET['ihc_register']) && $_GET['ihc_register'] === 'create_message' ){echo 1;} else {echo 0;} ?>"
      data-trigger_selector="<?php echo esc_attr($triggerSelector);?>"
      data-trigger_default="<?php echo esc_attr($preventDefault);?>"
></span>

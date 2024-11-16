
<?php wp_enqueue_style( 'ihc-croppic_css', IHC_URL . 'assets/css/croppic.css', array(), 10.1 );?>
<?php wp_enqueue_script( 'ihc-jquery_mousewheel', IHC_URL . 'assets/js/jquery.mousewheel.min.js', ['jquery'], 10.1 );?>
<?php wp_enqueue_script( 'ihc-croppic', IHC_URL . 'assets/js/croppic.js', ['jquery'], 10.1 );?>
<?php wp_enqueue_script( 'ihc-image_croppic', IHC_URL . 'assets/js/image_croppic.js', ['jquery'], 10.1 );?>

<?php
//$ajaxURL = IHC_URL . 'public/ajax-upload.php?ihcpublicn=' . wp_create_nonce( 'ihcpublicn' );
$ajaxURL = get_site_url() . '/wp-admin/admin-ajax.php?action=ihc_ajax_public_upload_file&ihcpublicn=' . wp_create_nonce( 'ihcpublicn' );
?>
<span class="ihc-js-upload-image-data"
      data-rand="<?php echo esc_attr($data['rand']);?>"
      data-url="<?php echo esc_url($ajaxURL);?>"
      data-bttn_label="<?php echo esc_html__('Upload', 'ihc');?>"
      data-name="<?php echo esc_attr($data['name']);?>"
    ></span>

<div class="ihc-upload-image-wrapper">

    <div class="ihc-upload-image-wrapp ihc-js-upload-image-wrapp" >
        <?php if ( !empty($data['imageUrl']) ):?>
            <img src="<?php echo esc_url($data['imageUrl']);?>" class="<?php echo esc_attr($data['imageClass']);?>" />
        <?php else:?>
            <?php if ( $data['name']=='ihc_avatar' ):?>
                <div class="ihc-no-avatar ihc-member-photo"></div>
            <?php endif;?>
        <?php endif;?>
        <div class="ihc-clear"></div>
    </div>
    <div class="ihc-content-left">
         <?php $extraClass = ( empty($data['imageUrl']) ) ? '' : 'ihc-display-none' ;?>
    	 <div class="ihc-avatar-trigger <?php echo esc_attr($extraClass);?>" id="<?php echo esc_attr('js_ihc_trigger_avatar' . $data['rand']);?>" >
            <div id="ihc-avatar-button" class="ihc-upload-avatar"><?php esc_html_e('Upload', 'ihc');?></div>
         </div>
        <?php $extraClass = ( empty($data['imageUrl']) ) ? '' : 'ihc-visibility-visible' ;?>
        <span class="ihc-upload-image-remove-bttn <?php echo esc_attr($extraClass);?>" id="<?php echo esc_attr('ihc_upload_image_remove_bttn_' . $data['rand']);?>"><?php esc_html_e('Remove', 'ihc');?></span>
    </div>
    <input type="hidden" value="<?php echo esc_attr($data['value']);?>" name="<?php echo esc_attr($data['name']);?>" id="<?php echo esc_attr('ihc_upload_hidden_' . $data['rand']);?>" data-new_user="<?php echo ( $data['user_id'] == -1 ) ? 1 : 0;?>" />

    <?php if (!empty($data['sublabel'])):?>
        <label class="iump-form-sublabel"><?php echo ihc_correct_text($data['sublabel']);?></label>
    <?php endif;?>
</div>

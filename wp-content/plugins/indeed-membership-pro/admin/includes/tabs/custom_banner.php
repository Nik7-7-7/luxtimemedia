<?php
$fieldName = 'ihc_user_custom_banner_src';
$fieldValue = isset( $data['userData']['ihc_user_custom_banner_src'] ) ? $data['userData']['ihc_user_custom_banner_src'] : '';
$rand = rand( 1, 10000000);
// $ajaxURL = IHC_URL . 'public/ajax-upload.php?ihcpublicn='. wp_create_nonce( 'ihcpublicn' );
$ajaxURL = get_site_url() . '/wp-admin/admin-ajax.php?action=ihc_ajax_public_upload_file&ihcpublicn=' . wp_create_nonce( 'ihcpublicn' );
?>

<?php wp_enqueue_style( 'ihc-croppic_css', IHC_URL . 'assets/css/croppic.css', array(), 10.1 );?>
<?php wp_enqueue_script( 'ihc-jquery_mousewheel', IHC_URL . 'assets/js/jquery.mousewheel.min.js', ['jquery'], 10.1 );?>
<?php wp_enqueue_script( 'ihc-croppic', IHC_URL . 'assets/js/croppic.js', ['jquery'], 10.1 );?>
<?php wp_enqueue_script( 'ihc-account_page-banner', IHC_URL . 'admin/assets/js/ihc_image_upload.js', ['jquery'], 10.1 );?>

<span class="ihc-js-custom-banner-data"
data-trigger_id="<?php echo esc_attr('ihc_js_upload_image_' . $rand);?>"
data-url="<?php echo esc_url($ajaxURL);?>"
data-field_name="<?php echo esc_attr($fieldName);?>"
data-remove_image_bttn="<?php echo esc_attr( '#ihc_upload_image_remove_bttn_' . $rand);?>"
data-bttn_label="<?php echo esc_html__('Upload', 'ihc');?>"
></span>

<div class="iump-form-line">
	<h4><?php esc_html_e('Member Portal Banner', 'ihc');?></h4>
	<p><?php esc_html_e('Customize customer Banner image or leave empty if you wish to be loaded the Plugin template default image.', 'ihc');?></p>


<div class="ihc-edit-banner-wrapper ihc-upload-image-wrapp">

    <div class="ihc-upload-top-banner-wrapper" >
        <?php if ( $fieldValue != '' ):?>
            <img src="<?php echo esc_url($fieldValue);?>" />
        <?php endif;?>
    </div>
    <div class="ihc-clear"></div>

    <div class="ihc-content-left">
       <?php if ( $fieldValue == '' ){
         $upload = 'ihc-display-block';
         $remove = 'ihc-visibility-hidden';
       } else {
        $upload = 'ihc-display-none';
        $remove = 'ihc-visibility-visible';
       } ?>
       <div class="ihc-upload-bttn-wrapp ihc-avatar-trigger <?php echo esc_attr($upload);?>" id="<?php echo esc_attr('ihc_js_upload_image_' . $rand);?>">
           <div id="ihc_top_custom_banner_js_bttn" class="ihc-upload-avatar"><?php esc_html_e('Upload', 'ihc');?></div>
       </div>
       <span class="ihc-upload-image-remove-bttn <?php echo esc_attr($remove);?>" id="<?php echo esc_attr('ihc_upload_image_remove_bttn_' . $rand);?>"><?php esc_html_e('Remove', 'ihc');?></span>
    </div>

    <input type="hidden" value="<?php echo esc_attr($fieldValue);?>" name="<?php echo esc_attr($fieldName);?>" id="<?php echo  esc_attr('ihc_upload_hidden_' .$rand);?>" data-new_user="<?php echo ( $data['uid'] == -1 ) ? 1 : 0;?>" />

</div>

</div>

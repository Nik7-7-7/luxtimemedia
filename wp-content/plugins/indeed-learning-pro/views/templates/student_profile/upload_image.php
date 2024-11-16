
<?php wp_enqueue_style( 'ulp-croppic_css', ULP_URL . 'assets/css/croppic.css' );?>
<?php wp_enqueue_script( 'ulp-jquery_mousewheel', ULP_URL . 'assets/js/jquery.mousewheel.min.js', array('jquery'), '3.7' );?>
<?php wp_enqueue_script( 'ulp-croppic', ULP_URL . 'assets/js/croppic.js', array('jquery'), '3.7' );?>
<?php wp_enqueue_script( 'ulp-image_croppic', ULP_URL . 'assets/js/image_croppic.js', array('jquery'), '3.7' );?>
<span class="ulp-js-student-profile-upload-image"
      data-trigger_id="<?php echo esc_attr('js_ulp_trigger_avatar' . $data['rand']);?>"
      data-url="<?php echo ULP_URL . "ajax_upload.php";?>"
      data-hidden_input_selector="[name=<?php echo esc_attr($data['name']);?>]"
      data-remove_image_selector="<?php echo esc_attr('#ulp_upload_image_remove_bttn_' . $data['rand']);?>"
      data-bttn_label="<?php echo esc_html__('Upload', 'ihc');?>"
></span>

<div >
    <div class="ulp-upload-image-wrapp" >
        <?php if ( !empty($data['imageUrl']) ):?>
            <img src="<?php echo esc_url($data['imageUrl']);?>" class="<?php echo esc_attr($data['imageClass']);?>" />
        <?php else:?>
            <?php if ( $data['name']=='ulp_avatar' ):?>
                <div class="ulp-no-avatar ulp-member-photo"></div>
            <?php endif;?>
        <?php endif;?>
        <div class="ulp-clear"></div>
    </div>
    <div >
    	<div class="ulp-avatar-trigger" id="<?php echo esc_attr('js_ulp_trigger_avatar' . $data['rand']);?>" >
        	<div id="ulp-avatar-button" class="ulp-upload-avatar"><?php esc_html_e('Upload', 'uap');?></div>
        </div>
        <span  class="ulp-upload-image-remove-bttn ulp-visibility-hidden" id="<?php echo esc_attr('ulp_upload_image_remove_bttn_' . $data['rand']);?>"><?php esc_html_e('Remove', 'ulp');?></span>
    </div>
    <input type="hidden" value="<?php echo esc_attr($data['value']);?>" name="<?php echo esc_attr($data['name']);?>" id="<?php echo esc_attr('ulp_upload_hidden_' . $data['rand']);?>" />

    <?php if (!empty($data['sublabel'])):?>
        <label class="ulp-form-sublabel"><?php echo indeed_correct_text($data['sublabel']);?></label>
    <?php endif;?>
</div>

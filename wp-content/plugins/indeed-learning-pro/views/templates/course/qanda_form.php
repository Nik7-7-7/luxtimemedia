<div class="ulp-quanda-button-wrapper">
	<span id="ulp_qanda_show_form" class="ulp-general-bttn"><?php esc_html_e('Add new question', 'ulp');?></span>
</div>
<form id="ulp_qanda_form"
      data-course="<?php echo esc_attr($data['course']);?>"
      data-hash="<?php echo md5($data['course'] . 'ulp_secret');?>"
      class="ulp_qanda_form-wrapper ulp-display-none"
			data-course-id="<?php echo esc_attr($data['courseId']);?>"
			data-redirect="<?php echo esc_url($data['uri']);?>"
>
		<input type="hidden" name="ulp_public_t" value="<?php echo wp_create_nonce( 'ulp_public_t' );?>" />
    <div class="ulp_qanda_form-line ulp_qanda_form-title">
        <input type="text" name="title" value="" placeholder="<?php esc_html_e('Question title', 'ulp');?>" />
    </div>
    <div class="ulp_qanda_form-line ulp_qanda_form-content">
        <textarea name="content" placeholder="<?php esc_html_e('Describe what you are trying to achieve and where you are getting stuck.', 'ulp');?>" ></textarea>
    </div>
    <div class="ulp_qanda_form-line  ulp_qanda_form-submission">
    	<span id="ulp_qanda_hide_form" class="ulp-general-bttn ulp-form-cancel"><?php esc_html_e('Cancel', 'ulp');?></span>
        <input type="submit" id="ulp_submit_qanda_question"  class="ulp-general-bttn" value="<?php esc_html_e('Post Question', 'ulp');?>" />
    </div>
</form>

<?php wp_enqueue_script('ulp-qanda_form', ULP_URL . 'assets/js/qanda_form.js', ['jquery'], null);?>

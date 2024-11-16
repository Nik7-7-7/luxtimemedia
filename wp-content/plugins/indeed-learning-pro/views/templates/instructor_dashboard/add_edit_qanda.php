<?php wp_enqueue_script('ulp_jquery_form_module', ULP_URL . 'assets/js/jquery.form.js', array('jquery'), '3.7' );?>
<?php wp_enqueue_script('ulp-jquery.uploadfile', ULP_URL . 'assets/js/jquery.uploadfile.min.js', array('jquery'), '3.7' );?>
<?php wp_enqueue_script( 'ulp_comments_box', ULP_URL . 'assets/js/comments_box.js', ['jquery'], '3.7', false );?>
<?php wp_enqueue_script( 'ulp_ckeditor', ULP_URL . 'assets/js/ckeditor/ckeditor.js', ['jquery'], '3.7', false);?>

<span class="ulp-js-add-edit-include-ckeditor" data-base_path="<?php echo ULP_URL . 'assets/js/ckeditor/';?>" ></span>

<div class="ulp-instructor-edit ulp-instructor-edit-qanda">
<h3><?php esc_html_e('Add/Edit Question', 'ulp');?></h3>

<form action="<?php echo esc_url($data['saveLink']);?>" method="post">
		<input type="hidden" name="ulp_public_t" value="<?php echo wp_create_nonce( 'ulp_public_t' );?>" />

		<input type="hidden" name="ID" value="<?php echo (isset($data['post_data']['ID'])) ? $data['post_data']['ID'] : '';?>" />
        <div class="ulp-instructor-edit-row">
  	<div class="ulp-inst-col-12">
    <div class="ulp-form-section">
        <h4><?php esc_html_e('Question Title', 'ulp');?></h4>
        <input type="text" name="post_title"  class="ulp-form-control" value="<?php echo esc_attr($data['post_data']['post_title']);?>" />
 </div>
   </div>
 </div>
 <div class="ulp-instructor-edit-line-break"></div>
<div class="ulp-instructor-edit-row">
  	<div class="ulp-inst-col-4">
    <div class="ulp-form-section">
				<h4><?php esc_html_e('Privacy', 'ulp');?></h4>
				<select name="post_status" class="ulp-form-control" >
						<?php if ($data['is_senior'] || $data['post_data']['post_status']=='publish'):?>
						<option value="publish" <?php echo ($data['post_data']['post_status']=='publish') ? 'selected' : '';?> ><?php esc_html_e('Publish', 'ulp');?></option>
						<?php endif;?>
						<option value="pending" <?php echo ($data['post_data']['post_status']=='pending') ? 'selected' : '';?> ><?php esc_html_e('Pending', 'ulp');?></option>
						<option value="draft" <?php echo ($data['post_data']['post_status']=='draft') ? 'selected' : '';?> ><?php esc_html_e('Draft', 'ulp');?></option>
				</select>
 </div>
   </div>
 </div>
 <div class="ulp-instructor-edit-line-break"></div>

  <div class="ulp-instructor-edit-row">
  	<div class="ulp-inst-col-12">
    <div class="ulp-form-section">
        <h4><?php esc_html_e('Question Content', 'ulp');?></h4>
        <textarea name="post_content" id="post_content"  class="ulp-form-control ulp-instrutor-ann-post-content"><?php echo esc_ulp_content($data['post_data']['post_content']);?></textarea>
            </div>
   </div>
 </div>

 <div class="ulp-instructor-edit-line-break"></div>

		<?php if ($data['categories']):?>

		 <div class="ulp-instructor-edit-row">
  	<div class="ulp-inst-col-5">
    <div class="ulp-form-section">
						<label><?php esc_html_e('Categories', 'ulp');?></label>
								<?php foreach ($data['categories'] as $category):?>
										<?php $checked = in_array($category->term_id, $data['post_cats']) ? 'checked' : '';?>
										<div class="ulp-instructor-multiple-choice">
												<input type="checkbox" name="categories[]" value="<?php echo esc_attr($category->term_id);?>" <?php echo esc_attr($checked);?> /> <?php echo esc_ulp_content($category->name);?>
										</div>
								<?php endforeach;?>

            </div>
   </div>
 </div>

 <div class="ulp-instructor-edit-line-break"></div>
		<?php endif;?>

 <div class="ulp-instructor-edit-row">
  	<div class="ulp-inst-col-5">
    <div class="ulp-form-section">
        <h4><?php esc_html_e('Course assignation', 'ulp');?></h4>
        <select name="ulp_qanda_course_id" class="ulp-form-control">
            <?php foreach ($data['courses'] as $courseObject):?>
                <option value="<?php echo esc_attr($courseObject->post_id);?>" <?php echo ($courseObject->post_id==$data['ulp_qanda_course_id']) ? 'selected' : '';?> ><?php echo esc_ulp_content($courseObject->post_title);?></option>
            <?php endforeach;?>
        </select>
    	</div>
   </div>
 </div>
  <div class="ulp-instructor-edit-line-break"></div>
 <div class="ulp-instructor-edit-row">
  	<div class="ulp-inst-col-12">
    <div class="ulp-form-section">
		<?php if ($data['commentsBox']):?>
				<?php echo esc_ulp_content($data['commentsBox']);?>
		<?php endif;?>
	</div>
   </div>
 </div>
 <div class="ulp-instructor-edit-line-break"></div>
 <div class="ulp-instructor-edit-row">
  	<div class="ulp-inst-col-12">
    <div class="ulp-form-section">
        <input type="submit" name="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" class="btn btn-primary pointer ulp-submit-button" />
   </div>
 </div>
 </div>
</form>
</div>

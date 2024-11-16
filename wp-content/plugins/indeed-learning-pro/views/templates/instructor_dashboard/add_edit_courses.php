<?php
global $wp_version;
wp_register_script('ulp_courses_modules', ULP_URL . 'assets/js/courses_modules.js', array(), '3.7' );
if ( version_compare ( $wp_version , '5.7', '>=' ) ){
    wp_add_inline_script( 'ulp_courses_modules', "var ulp_url='" . get_site_url() . "';" );
		wp_add_inline_script( 'ulp_courses_modules', "var ulp_plugin_url='" . ULP_URL . "';" );
} else {
		wp_localize_script('ulp_courses_modules', 'ulp_url', get_site_url());
		wp_localize_script('ulp_courses_modules', 'ulp_plugin_url', ULP_URL);
}
wp_enqueue_script('ulp_courses_modules');
wp_enqueue_script('ulp_ui_multiselect_js', ULP_URL . 'assets/js/ui.multiselect.js', array('jquery'), '3.7' );
wp_enqueue_style('ulp_course_modules', ULP_URL . 'assets/css/course_modules.css', array(), '3.7' );
wp_enqueue_script('ulp_ckeditor', ULP_URL . 'assets/js/ckeditor/ckeditor.js', ['jquery'], '3.7', false);
wp_enqueue_script('ulp_jquery_form_module', ULP_URL . 'assets/js/jquery.form.js', array('jquery'), '3.7' );

///croppic
wp_register_script('ulp_uploadFeatureImage', ULP_URL . 'assets/js/uploadFeatureImage.js', [], null);
if ( version_compare ( $wp_version , '5.7', '>=' ) ){
    wp_add_inline_script( 'ulp_uploadFeatureImage', "var ulp_plugin_url='" . ULP_URL . "';" );
} else {
		wp_localize_script('ulp_uploadFeatureImage', 'ulp_plugin_url', ULP_URL);
}
wp_enqueue_script('ulp_mouse_wheel', ULP_URL . 'assets/js/jquery.mousewheel.min.js', array('jquery'), '3.7' );
wp_enqueue_script('ulp_croppic', ULP_URL . 'assets/js/croppic.js', array('jquery'), '3.7' );
wp_enqueue_script('ulp_uploadFeatureImage');
wp_enqueue_style('ulp_croppic', ULP_URL . 'assets/css/croppic.css', array(), '3.7' );

?>


<span class="ulp-js-add-edit-include-ckeditor" data-base_path="<?php echo ULP_URL . 'assets/js/ckeditor/';?>" ></span>

<div class="ulp-instructor-edit ulp-instructor-edit-course">
<h2><?php esc_html_e('Add/Edit Course', 'ulp');?></h2>

<form action="<?php echo esc_url($data['saveLink']);?>" method="post">
<input type="hidden" name="ID" value="<?php echo esc_attr($data['post_id']);?>" />
<input type="hidden" name="ulp_public_t" value="<?php echo wp_create_nonce( 'ulp_public_t' );?>" />

<div class="ulp-instructor-edit-row">
  <div class="ulp-inst-col-12">
    <div class="ulp-form-section">
        <h4><?php esc_html_e('Course Title', 'ulp');?></h4>
        <input type="text" name="post_title" class="ulp-form-control"  value="<?php echo (is_array ($data['post_data']) ? $data['post_data']['post_title'] : '');?>" />
				<?php if ($data['post_id']):?>
						<div><?php echo esc_html__('Post ID: ', 'ulp') . $data['post_id'];?></div>
				<?php endif;?>

    </div>
 </div>
</div>
<div class="ulp-instructor-edit-line-break"></div>
<div class="ulp-instructor-edit-row">
  <div class="ulp-inst-col-6">
    <div class="ulp-form-section">
					<h4><?php esc_html_e('Privacy', 'ulp');?></h4>
					<select name="post_status"  class="ulp-form-control">
							<?php if ($data['is_senior'] || (is_array($data['post_data']) && $data['post_data']['post_status']=='publish')):?>
							<option value="publish" <?php echo is_array($data['post_data']) && $data['post_data']['post_status']=='publish' ? 'selected' : '';?> ><?php esc_html_e('Publish', 'ulp');?></option>
							<?php endif;?>
							<option value="pending" <?php echo  is_array($data['post_data']) && $data['post_data']['post_status']=='pending' ? 'selected' : '';?> ><?php esc_html_e('Pending', 'ulp');?></option>
							<option value="draft" <?php echo  is_array($data['post_data']) && $data['post_data']['post_status']=='draft' ? 'selected' : '';?> ><?php esc_html_e('Draft', 'ulp');?></option>
					</select>
    </div>
 </div>
</div>
<div class="ulp-instructor-edit-line-break"></div>
<div class="ulp-instructor-edit-row">
  <div class="ulp-inst-col-12">
    <div class="ulp-form-section">
        <h4><?php esc_html_e('Course Description', 'ulp');?></h4>
        <textarea name="post_content" class="ulp-js-summernote ulp-form-control" id="post_content" ><?php echo (is_array ($data['post_data']) ? $data['post_data']['post_content'] : '');?></textarea>
    </div>
 </div>
</div>
<div class="ulp-instructor-edit-line-break"></div>

<?php if ($data['tags']):?>
<div class="ulp-instructor-edit-row">
  <div class="ulp-inst-col-12">
    <div class="ulp-form-section">
		<h4><?php esc_html_e('Course Tags', 'ulp');?></h4>
						<div class="ulp-instructor-tags-wrapper">
								<?php foreach ($data['tags'] as $tag):?>
										<?php $checked = in_array($tag->term_id, $data['post_tags']) ? 'checked' : '';?>
										<div class="ulp-instructor-multiple-choice ulp-instructor-tags">
												<input type="checkbox" name="tags[]" value="<?php echo esc_attr($tag->term_id);?>" <?php echo esc_attr($checked);?> /> <?php echo esc_html($tag->name);?>
										</div>
								<?php endforeach;?>
						</div>
    </div>
 </div>
</div>
<div class="ulp-instructor-edit-line-break"></div>
<?php endif;?>

<?php if ($data['categories']):?>
<div class="ulp-instructor-edit-row">
  <div class="ulp-inst-col-12">
    <div class="ulp-form-section">
						<h4><?php esc_html_e('Categories', 'ulp');?></h4>
						<div class="ulp-instructor-categories-wrapper">
								<?php foreach ($data['categories'] as $category):?>
										<?php $checked = in_array($category->term_id, $data['post_cats']) ? 'checked' : '';?>
										<div class="ulp-instructor-multiple-choice">
												<input type="checkbox" name="categories[]" value="<?php echo esc_attr($category->term_id);?>" <?php echo esc_attr($checked);?> /> <?php echo esc_html($category->name);?>
										</div>
								<?php endforeach;?>
						</div>
    </div>
 </div>
</div>
<div class="ulp-instructor-edit-line-break"></div>
<?php endif;?>

<div class="ulp-instructor-edit-row">
  <div class="ulp-inst-col-12">
    <div class="ulp-form-section">
	         <h3  class="ulp-instructor-edit-top-title"><?php esc_html_e('Feature image', 'ulp');?></h3>

	           <?php if (!empty($data['featureImage'])):?>
               		<div id="js_ulp_upload_image_trigger" class="ulp-upload-feat-image-wrapp ulp-edit-image">
	               <img src="<?php echo esc_url($data['featureImage']);?>" class="ulp-feat-img-old-pic"/>
                   </div>
	           <?php else:?>
               		<div id="js_ulp_upload_image_trigger" class="ulp-upload-feat-image-wrapp ulp-add-image"></div>
			   <?php endif;?>

	         <input type="hidden" id="ulp_feat_image_input" name="ulp_feat_image_input" value="<?php echo esc_attr($data['featureImage']);?>" />
    </div>
 </div>
</div>
<div class="ulp-instructor-edit-line-break"></div>

<div id="modulesMetaBox">
	<div class="ulp-instructor-edit-row">
	  <div class="ulp-inst-col-12">
	  <h3  class="ulp-instructor-edit-top-title"><?php esc_html_e('Course Curriculum', 'ulp');?></h3>
	    <div class="ulp-instructor-modules">
	        		<div  id="ulp_course_modules" data-post_id="<?php echo esc_attr($data['post_id']);?>"></div>
	        		<button type="button" class="ulp-add-module-button" id="ulp_add_module_button" ><i title="<?php esc_html_e('Add Module', 'ulp');?>" class="fa-ulp fa-add_module-ulp"></i></button>
			</div>
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

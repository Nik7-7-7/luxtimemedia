<?php wp_enqueue_script('ulp_courses_modules', ULP_URL . 'assets/js/courses_modules.js', array('jquery'), '3.7' );?>


<div  id="ulp_course_modules" data-post_id="<?php echo esc_attr($data['post_id']);?>"></div>
<button type="button" class="ulp-add-module-button" id="ulp_add_module_button" ><i title="<?php esc_html_e('Add New Section', 'ulp');?>" class="fa-ulp fa-add_module-ulp"></i></button>

<?php wp_enqueue_script('ulp_ui_multiselect_js');?>

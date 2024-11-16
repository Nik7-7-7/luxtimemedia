<div class="ulp-course-qanda-search-bar-wrapper">
    <input type="text" id="ulp_course_qanda_search_bar" class="ulp-course-qanda-search-bar"
    data-course="<?php echo esc_ulp_content($data['course']);?>"
    data-hash="<?php echo md5($data['course'] . 'ulp_secret');?>"
    placeholder="<?php esc_html_e('Search for a Question', 'ulp');?>"
    />
</div>
<?php wp_enqueue_script('ulp-qanda_search_bar', ULP_URL . 'assets/js/qanda_search_bar.js', ['jquery'], null);?>

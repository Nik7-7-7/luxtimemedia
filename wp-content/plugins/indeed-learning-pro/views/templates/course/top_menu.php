<ul>
    <?php foreach ($tabs as $slug=>$label):?>
        <?php
            $extraClass = ($slug == 'overview') ? 'ulp-menu-tab-active' : '';
        ?>
        <li data-target="<?php echo esc_attr($slug);?>" class="js-ulp-single-course-menu-item <?php echo esc_attr($extraClass);?>"><i class="fa-ulp fa-course-menu-<?php echo esc_attr($slug);?>-ulp"></i><?php echo esc_html($label);?></li>
    <?php endforeach;?>
</ul>
<?php
global $wp_version;

$subtab = isset($_GET['subtab']) ? sanitize_text_field($_GET['subtab']) : '';
wp_register_script('ulp_single_course_menu', ULP_URL . 'assets/js/single_course_menu.js', ['jquery'], '3.7' );
if ( version_compare ( $wp_version , '5.7', '>=' ) ){
    wp_add_inline_script( 'ulp_single_course_menu', "var ulpCurrentSubtab='" . $subtab . "';" );
} else {
    wp_localize_script( 'ulp_single_course_menu', 'ulpCurrentSubtab', $subtab );
}

wp_enqueue_script('ulp_single_course_menu');
?>

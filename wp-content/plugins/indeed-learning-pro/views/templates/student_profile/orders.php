<?php wp_enqueue_script( 'ulp_printThis' );?>
<div class="ulp-student-profile-tab-the-title"><?php echo do_shortcode($data ['title']);?></div>
<div class="ulp-student-profile-tab-the-content"><?php echo do_shortcode($data ['content']);?></div>
<div class="row">
    <?php echo do_shortcode('[ulp_view_orders]');?>
</div>
<span class="ulp-js-init-print-this" data-load_css="<?php echo ULP_URL . 'assets/css/public.css';?>"></span>

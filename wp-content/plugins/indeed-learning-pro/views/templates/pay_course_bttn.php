<?php
wp_enqueue_style('ulp_sweet_alert_css', ULP_URL . 'assets/css/sweetalert.css');
wp_enqueue_script('ulp_sweet_alert', ULP_URL . 'assets/js/sweetalert.js', array('jquery'), '3.7' );
?>
<div class="ulp-pay-bttn-wrapp">
    <div class="ulp-pay-bttn js-ulp-pay-course-bttn" data-course_id="<?php echo esc_attr($data['course_id']);?>" data-payment_type="<?php echo esc_attr($data['payment_type']);?>">
	<?php echo Ulp_Global_Settings::get('ulp_messages_buy_course_bttn');?>
    </div>
</div>

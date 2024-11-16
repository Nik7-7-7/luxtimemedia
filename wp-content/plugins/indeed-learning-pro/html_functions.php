<?php
if (!function_exists('ulp_quiz_next_bttn')):
function ulp_quiz_next_bttn(){
  return '<div class="ulp-quiz-next-button" id="ulp_quiz_next_question">' . esc_html__('Next Question', 'ulp') . '</div>';
}
endif;
if (!function_exists('ulp_quiz_prev_bttn')):
function ulp_quiz_prev_bttn(){
  return '<div class="ulp-quiz-prev-button" id="ulp_quiz_prev_question">' . esc_html__('Previous Question', 'ulp') . '</div>';
}
endif;
if (!function_exists('ulp_quiz_submit_bttn')):
function ulp_quiz_submit_bttn(){
  return '<div class="ulp-quiz-submit-via-ajax" id="ulp_quiz_submit_via_ajax">' . esc_html__('Complete Question', 'ulp') . '</div>';
}
endif;
if (!function_exists('ulp_generate_stars')):
function ulp_generate_stars($num=0){
    $str = '';
    for ($i=1; $i<6; $i++){
        $class = ($i<=$num) ? 'fa-star-ulp' : 'fa-star-o-ulp';
        $str .= '<span class="ulp-star-item"><i class="fa-ulp ' . $class . ' ulp-star-unselected" ></i></span>';
    }
    return $str;
}
endif;
if (!function_exists('ulp_default_payment_type')):
function ulp_default_payment_type(){
    $value = get_option('ulp_default_payment_type');
    ?>
    <div>
        <label><?php esc_html_e('Default Payment:', 'ulp');?></label>
        <select name="ulp_default_payment_type">
            <option value="woo" <?php echo ($value=='woo') ? 'selected' : '';?> >WooCommerce</option>
            <option value="edd" <?php echo ($value=='edd') ? 'selected' : '';?> >Easy Download Digital</option>
            <option value="ump" <?php echo ($value=='ump') ? 'selected' : '';?> >Ultimate Membership Pro</option>
            <option value="checkout" <?php echo ($value=='checkout') ? 'selected' : '';?> >Checkout Page (Bank Transfer, PayPal, Stripe)</option>
        </select>
    </div>
    <?php
}
endif;

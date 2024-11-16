<?php
wp_enqueue_style('ulp_sweet_alert_css', ULP_URL . 'assets/css/sweetalert.css');
wp_enqueue_script('ulp_sweet_alert', ULP_URL . 'assets/js/sweetalert.js', array('jquery'), '3.7' );
?>
<?php if (empty($data['uid'])):?>
    <div class="ulp-additional-message"><?php echo esc_html($data['userNotLoggedErrorMessage']);?></div>
<?php else:?>
    <?php if ($data['submited']):?>

    <?php else:?>
    <form method="post"  id="ulp_checkout">
        <input type="hidden" name="course_id" value="<?php echo esc_attr($data['course_id']);?>" />
        <input type="hidden" name="ulp_public_t" value="<?php echo wp_create_nonce( 'ulp_public_t' );?>" />

        <div class="ulp-checkout-main-title"><?php esc_html_e('Your Order', 'ulp');?></div>
        <table class="ulp-table-general ulp-list-checkout">
         <thead>
            <tr>
              <th><?php esc_html_e('Course', 'ulp');?></th>
              <th><?php echo Ulp_Global_Settings::get('ulp_messages_checkout_amount');?></th>
            </tr>
         </thead>
         <tbody>
           <tr>
             <td class="ulp-special-column"><?php echo esc_html($data ['course_label']);?></td>
             <td><?php echo ulp_format_price($data ['amount']);?></td>
          </tr>
         </tbody>
       </table>

        <div class="ulp-checkout-main-title"><?php echo Ulp_Global_Settings::get('ulp_messages_checkout_payment_type');?></div>
        <div class="ulp-checkout-payment-select-wrapper">
            <div class="ulp-checkout-payment-select">
            <?php foreach ($data['payment_types'] as $slug => $object):?>
                <div class="ulp-checkout-payment-option" id="ulp_checkout_payment_select">
                    <input type="radio" name="payment_type" value="<?php echo esc_attr($slug);?>" /> <?php echo esc_ulp_content($object['label']);?>
                    <span><?php echo esc_ulp_content($object['description']);?></span>
                </div>
            <?php endforeach;?>
            </div>
        </div>

        <?php do_action('ulp_checkout_page_print_string_before_pay_bttn');?>

        <input type="submit" name="ulp_pay" value="<?php esc_html_e('Pay Order', 'ulp');?>" /><div class="ulp-spinner" id="js_ulp_checkout_loading_gif"></div>
    </form>
    <?php endif;?>
<?php endif;?>

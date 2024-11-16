<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('Ulp_Payment_Services_Details')){
   return;
}
class Ulp_Payment_Services_Details{
    private static $_data = null;
    public static function get_all(){
        if (empty($_data)){
            self::_set_data();
        }
        return self::$_data;
    }
    private static function _set_data(){
        self::$_data = [
  						'bt' => esc_html__('Bank transfer', 'ulp'),
  						'paypal' => esc_html__('PayPal', 'ulp'),
             	'stripe' => esc_html__("Stripe", 'ulp'),
              '2checkout' => esc_html__('2Checkout', 'ulp'),
  			];
        /// set labels and order
  			$paypal_settings = DbUlp::getOptionMetaGroup('paypal');
  			$paypal_settings = $paypal_settings + DbUlp::getOptionMetaGroup('paypal_magic_feat');
  			if (empty($paypal_settings['ulp_paypal_enable'])){
  					unset(self::$_data['paypal']);
  			} else {
            self::$_data['paypal'] = array('label' => stripslashes($paypal_settings['ulp_paypal_label']),
										   'description' => stripslashes($paypal_settings['ulp_paypal_description']));
            $key = $paypal_settings['ulp_paypal_multipayment_order'];
            $order_items [$key] = 'paypal';
        }
  			$bt_settings = DbUlp::getOptionMetaGroup('bt');
  			if (empty($bt_settings['ulp_bt_enable'])){
  					unset(self::$_data['bt']);
  			} else {
            self::$_data['bt'] = array('label' => stripslashes($bt_settings['ulp_bt_label']),
									   'description' => stripslashes($bt_settings['ulp_bt_description']));
            $key = $bt_settings['ulp_bt_multipayment_order'];
            if (!empty($order_items [$key])){
               $key++;
            }
            $order_items [$key] = 'bt';
        }
        $stripe_settings = DbUlp::getOptionMetaGroup('stripe');
  			$stripe_settings = $stripe_settings + DbUlp::getOptionMetaGroup('stripe_magic_feat');
        if (empty($stripe_settings['ulp_stripe_payment_enable'])){
            unset(self::$_data['stripe']);
        } else {
            self::$_data['stripe'] = array('label' => stripslashes($stripe_settings['ulp_stripe_label']),
										   'description' => stripslashes($stripe_settings['ulp_stripe_description']));
			      $key = $stripe_settings['ulp_stripe_multipayment_order'];
            if (!empty($order_items [$key])){
               $key++;
            }
            $order_items [$key] = 'stripe';
        }

        /// 2checkout
        $twoCheckoutSettings = DbUlp::getOptionMetaGroup('2checkout');
        $twoCheckoutSettings = $twoCheckoutSettings + DbUlp::getOptionMetaGroup('2checkout_magic_feat');
        if (empty($twoCheckoutSettings['ulp_2checkout_payment_enable'])){
            unset(self::$_data['2checkout']);
        } else {
            self::$_data['2checkout'] = [
                  'label'       => stripslashes($twoCheckoutSettings['ulp_2checkout_label']),
                  'description' => stripslashes($twoCheckoutSettings['ulp_2checkout_description'])
            ];
            $key = $twoCheckoutSettings['ulp_2checkout_multipayment_order'];
            if (!empty($order_items [$key])){
               $key++;
            }
            $order_items [$key] = '2checkout';
        }
        /// re-order
        if (count($order_items)>1){
            ksort($order_items);
            $switch = self::$_data;
            self::$_data = null;
            foreach ($order_items as $k=>$v){
                self::$_data [$v] = $switch [$v];
            }
        }
    }
}

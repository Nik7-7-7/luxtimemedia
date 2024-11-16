<?php

namespace Indeed\Ihc;

/*
@since 7.4
$attributes = [
                'uid',
                'lid',
                'amount_type',
                'amount',
                'status',
                'payment_gateway',
                'extra_fields',
                'ihc_coupon',
                'ihc_state',
                'ihc_country',
];
$createOrder = new \Indeed\Ihc\CreateOrder($attributes);
$orderId = $createOrder->proceed()->getOrderId();
@deprecated since version 9.3
*/

class CreateOrder
{
    private $attributes         = array();
    private $paymentGateway     = '';
    private $orderId            = 0;
    private $currency           = '';

    public function __construct($params=array(), $paymentGateway='')
    {
        $this->attributes       = $params;
        $this->paymentGateway   = $paymentGateway;
        $this->currency         = get_option('ihc_currency');
    }


    // this will calculate for the first payment
    public function calculateAmount()
    {
        if (empty($this->attributes['extra_fields'])){
            $this->attributes['extra_fields'] = array();
        }
        //$levels = \Indeed\Ihc\Db\Memberships::getAll();
        $levelData = \Indeed\Ihc\Db\Memberships::getOne( $this->attributes['lid'] );
      	$amount = $levelData['price'];

        // check for trial
        if ( $levelData['access_type']=='regular_period' ){
            $amount = $this->calculateAmountForTrial( $levelData, $amount );
        }

        $amount = $this->applyCoupon($amount);
        $amount = $this->addTaxes($amount);

      	if ($this->paymentGateway=='stripe' && $amount<0.50){
      		$amount = 0.50;/// minimum for stripe.
      	}
        return $amount;
    }

    private function calculateAmountForTrial( $levelData=array(), $amount=0 )
    {
        if ( $levelData['access_trial_type']==1 && !empty($levelData['access_trial_time_value']) ){
            // certain period
            $amount = $levelData['access_trial_price'];
        } else if ( $levelData['access_trial_type']==2 && !empty($levelData['access_trial_couple_cycles']) ){
            // couple of cycles
            $amount = $levelData['access_trial_price'];
        }
        return $amount;
    }

    public function proceed()
    {
        if (empty($this->attributes['uid']) || empty($this->attributes['lid'])){
            return $this;
        }
        $amount = isset($this->attributes['amount']) ? $this->attributes['amount'] : $this->calculateAmount();

        require_once IHC_PATH . 'classes/Orders.class.php';
        $object = new \Ump\Orders();
        $this->orderId = $object->do_insert(array(
                  'uid'               => $this->attributes['uid'],
                  'lid'               => $this->attributes['lid'],
                  'amount_type'       => $this->currency,
                  'amount'            => $amount,
                  'status'            => isset($this->attributes['status']) ? $this->attributes['status'] : 'pending',
                  'ihc_payment_type'  => $this->paymentGateway,
                  'extra_fields'      => $this->attributes['extra_fields'],
        ));
        return $this;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    private function applyCoupon($amount=0)
    {
        if (empty($this->attributes['ihc_coupon'])){
            return $amount;
        }
        $couponData = ihc_check_coupon($this->attributes['ihc_coupon'], $this->attributes['lid']);
        $this->attributes['extra_fields']['discount_value'] = ihc_get_discount_value($amount, $couponData);
        $this->attributes['extra_fields']['coupon_used'] = $this->attributes['ihc_coupon'];
        $amount = ihc_coupon_return_price_after_decrease($amount, $couponData, true, $this->attributes['uid'], $this->attributes['lid']);
        return $amount;
    }

    private function addTaxes($amount=0)
    {
        $state = get_user_meta($this->attributes['uid'], 'ihc_state', TRUE);
        $country = ($this->attributes['ihc_country']==FALSE) ? '' : $this->attributes['ihc_country'];
        $taxesData = ihc_get_taxes_for_amount_by_country($country, $state, $amount);
        if ($taxesData && !empty($taxesData['total'])){
          $amount += $taxesData['total'];
          $this->attributes['extra_fields']['tax_value'] = $taxesData['total'];
        }
        return $amount;
    }


}

<?php
namespace Indeed\Ihc\Gateways\Libraries\PayPalExpress;
/*
@since 7.4
*/
class PayPalExpressCheckoutHandleTemporaryTokens
{
    private $optionName = 'ihc_paypal_express_temp_tokens';

    public function save($token='')
    {
        $data = get_option($this->optionName);
        if ( is_array( $data ) && in_array($token, $data) ){
            return false;
        }
        $data[] = $token;
        update_option($this->optionName, $data);
    }

    public function exists($token='')
    {
        $data = get_option($this->optionName);
        if ( is_array( $data ) && in_array($token, $data)){
            return true;
        }
        return false;
    }

    public function remove($token='')
    {
        $data = get_option($this->optionName);
        if ( !is_array( $data ) || !in_array($token, $data) ){
            return false;
        }
        $key = array_search($token, $data);
        if ($key===FALSE || !isset($data[$key])){
            return false;
        }
        unset($data[$key]);
        update_option($this->optionName, $data);
    }
}

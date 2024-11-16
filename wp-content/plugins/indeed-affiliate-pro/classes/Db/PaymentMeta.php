<?php
namespace Indeed\Uap\Db;

class PaymentMeta
{
    /**
     * @param none
     * @return none
     */
    public function __construct(){}

    /**
      * @param int
      * @param string
      * @param string
      * @return bool
      */
    public function save( $paymentId=0, $metaKey='', $metaValue='' )
    {
        global $wpdb;
        if ( $paymentId === 0 ){
            return false;
        }
        if ( $metaKey === '' ){
            return false;
        }
        if ( $this->getOne( $paymentId, $metaKey ) === false ){
            // insert
            return $this->insert( $paymentId, $metaKey, $metaValue );
        } else {
            // update
            return $this->update( $paymentId, $metaKey, $metaValue );
        }
    }

    /**
      * @param int
      * @param string
      * @param string
      * @return bool
      */
    public function insert( $paymentId=0, $metaKey='', $metaValue='' )
    {
        global $wpdb;
        $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}uap_payments_meta VALUES( NULL, %d, %s, %s );", $paymentId, $metaKey, $metaValue );
        return $wpdb->query( $query );
    }

    /**
      * @param int
      * @param string
      * @param string
      * @return bool
      */
    public function update( $paymentId=0, $metaKey='', $metaValue='' )
    {
        global $wpdb;
        $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}uap_payments_meta SET meta_value=%s WHERE meta_name=%s AND payment_id=%d;", $metaValue, $metaKey, $paymentId );
        return $wpdb->query( $query );
    }

    /**
      * @param int
      * @param string
      * @return mixed ( string or bool )
      */
    public function getOne( $paymentId=0, $metaKey='' )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}uap_payments_meta WHERE meta_name=%s AND payment_id=%d;", $metaKey, $paymentId );
        $value = $wpdb->get_var( $query );
        if ( $value === null || $value === false ){
            return false;
        }
        return $value;
    }

    /**
      * @param int
      * @return mixed ( string or bool )
      */
    public function getAllForPayment( $paymentId=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->prefix}uap_payments_meta WHERE payment_id=%d;", $paymentId );
        $data = $wpdb->get_var( $query );
        if ( $data === null || $data === false ){
            return false;
        }
        return $data;
    }

    /**
      * @param string
      * @param string
      * @return mixed ( string or bool )
      */
    public function getAllPaymentIdsForMetaNameMetaValue( $metaName=null, $metaValue=null )
    {
        global $wpdb;
        if ( $metaName === null || $metaName === '' || $metaValue === null ){
            return false;
        }
        $query = $wpdb->prepare( "SELECT payment_id FROM {$wpdb->prefix}uap_payments_meta WHERE meta_name=%s AND meta_value=%s;", $metaName, $metaValue );
        $data = $wpdb->get_results( $query, ARRAY_A );
        if ( $data === null || $data === false ){
            return false;
        }
        $response = [];
        foreach ($data as $array ){
            $response[] = $array['payment_id'];
        }
        return $response;
    }

    /**
      * @param int
      * @param string
      * @return bool
      */
    public function deleteOne( $paymentId=0, $metaKey='' )
    {
        global $wpdb;
        if ( $this->getOne( $paymentId, $metaKey) === false ){
            // option doesnt exists
            return false;
        }
        $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}uap_payments_meta WHERE payment_id=%d AND meta_name=%s;", $paymentId, $metaKey );
        return $wpdb->query( $query );
    }

    /**
     * @param int
     * @return bool
     */
    public function deleteAllForPaymentId( $id=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}uap_payments_meta WHERE payment_id=%d;", $id );
        return $wpdb->query( $query );
    }
}

<?php
namespace Indeed\Uap\Db;
/**
columns:
id
method
amount
currency
date_range_type
start_time
end_time
details
created_time
updated_time
status
*/

class Payouts
{
    /**
     * @param none
     * @return none
     */
    public function __construct(){}

    /**
      * @param array
      * @return array
      */
    public function save( $params=[] )
    {
        global $wpdb;
        if (isset( $params['id'] ) && (int)$params['id'] !== 0 ){
            // maybe update
            $temporary = $this->getOne( $params['id'] );
            if ( !empty($temporary) && count( $temporary ) > 1 ){
                return $this->update( $params );
            }
        }
        return $this->insert( $params );
    }

    /**
      * @param array
      * @return array
      */
    public function insert( $params=[] )
    {
        global $wpdb;
        $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}uap_payouts VALUES( NULL, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW(), %s);",
              $params['method'],
              $params['amount'],
              $params['currency'],
              $params['date_range_type'],
              $params['start_time'],
              $params['end_time'],
              $params['details'],
              $params['status']
        );
        $wpdb->query( $query );
				return $wpdb->insert_id;
    }

    /**
      * @param array
      * @return array
      */
    public function update( $params=[] )
    {
        global $wpdb;
        $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}uap_payouts
                                      SET
                                      method=%s,
                                      amount=%s,
                                      currency=%s,
                                      date_range_type=%s,
                                      start_time=%s,
                                      end_time=%s,
                                      details=%s,
                                      updated_time=NOW(),
                                      status=%s
                                      WHERE
                                      id=%d;",
              $params['method'],
              $params['amount'],
              $params['currency'],
              $params['date_range_type'],
              $params['start_time'],
              $params['end_time'],
              $params['details'],
              $params['status'],
              $params['id']
        );
        $wpdb->query( $query );
				return $wpdb->insert_id;
    }

    /**
     * @param int
     * @param int
     * @return int
     */
    public function updateStatus( $id=0, $status=null )
    {
        global $wpdb;
        if ( $id === 0 || $status===null){
            return false;
        }
        if ( $this->getOne( $id ) ){
            return false;
        }
        $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}uap_payouts
                                      SET
                                      status=%s,
                                      updated_time=NOW()
                                      WHERE
                                      id=%d;",
              $params['status'],
              $params['id']
        );
        $wpdb->query( $query );
    }

    /**
      * @param int
      * @return array
      */
    public function getOne( $id=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT id,
                                        method,
                                        amount,
                                        currency,
                                        date_range_type,
                                        start_time,
                                        end_time,
                                        details,
                                        created_time,
                                        updated_time,
                                        status
                                        FROM
                                        {$wpdb->prefix}uap_payouts
                                        WHERE
                                        id=%d
        ", $id );
        $data = $wpdb->get_row( $query, ARRAY_A );
        if ( $data === null || $data === false ){
            return false;
        }
        return $data;
    }

    /**
      * @param int
      * @return int
      */
    public function getOneStatus( $id=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT status
                                        FROM
                                        {$wpdb->prefix}uap_payouts
                                        WHERE
                                        id=%d
        ", $id );
        $status = $wpdb->get_var( $query );
        if ( $status === null || $status === false ){
            return false;
        }
        return (int)$status;
    }

    /**
     * @param array
     * @return array
     */
    public function getManyWithFilters( $params=[] )
    {
        global $wpdb;
        if ( empty( $params['count'] ) ){
            $select = " p.id,p.method,p.amount,p.currency,p.date_range_type,p.start_time,
                        p.end_time,p.details,p.created_time,p.updated_time,p.status
                        ";
        } else {
            $select = " COUNT(p.id) as num ";
        }
        $query = "SELECT $select FROM
                                {$wpdb->prefix}uap_payouts p
                                WHERE
                                1=1 ";


        if ( isset( $params['status_in'] ) && is_array( $params['status_in'] ) && count( $params['status_in'] ) > 0 ){
              $query .= " AND p.status IN (" . indeedEscapeArrayForQuery( $params['status_in'] ) . ") ";
        }

        // start time
        if ( isset( $params['start_time'] ) && $params['start_time'] !== '' ){
            $query .= $wpdb->prepare( " AND UNIX_TIMESTAMP(p.created_time) > UNIX_TIMESTAMP(%s) ", $params['start_time'] );
        }
        // end time
        if ( isset( $params['end_time'] ) && $params['end_time'] !== '' ){
            $query .= $wpdb->prepare( " AND UNIX_TIMESTAMP(p.created_time) < UNIX_TIMESTAMP(%s) ", $params['end_time'] );
        }

        if ( isset( $params['search_phrase'] ) && $params['search_phrase'] !== '' ){
            $directDeposit = esc_html__( 'Direct Deposit', 'uap' );
            switch ( $params['search_phrase'] ){
                case 'bank':
                case 'direct':
                case 'direct deposit':
                case $directDeposit:
                  $params['search_phrase'] = 'bt';
                  break;
                case 'Stripe':
                case 'stripe':
                  $params['search_phrase'] = 'stripe';
                  break;
                case 'paypal':
                case 'Paypal':
                  $params['search_phrase'] = 'paypal';
                  break;
            }
            $query .= $wpdb->prepare( " AND
                  ( p.amount=%d OR p.method=%s OR p.currency=%s  "
            , $params['search_phrase'], $params['search_phrase'], $params['search_phrase'] );
            if ( is_numeric( $params['search_phrase'] ) ){
                $q .= $wpdb->prepare( "  OR p.amount_value=%s  ", $params['q'] );
            }
            $query .= " ) ";
        }

        if ( empty( $params['count'] ) && isset( $params['order_by'] ) && $params['order_by'] !== '' && isset( $params['asc_or_desc'] ) && $params['asc_or_desc'] !== '' ){
            $query .= $wpdb->prepare( " ORDER BY %1s %1s ", $params['order_by'], $params['asc_or_desc'] );
        }

        if ( empty( $params['count'] ) && isset( $params['offset'] ) && $params['offset'] !== '' && $params['offset'] !== false && isset( $params['limit'] ) && $params['limit'] !== '' && $params['limit'] !== false ){
            $query .= $wpdb->prepare( " LIMIT %d OFFSET %d ", $params['limit'], $params['offset'] );
        }

        if ( empty( $params['count'] ) ){
            $data = $wpdb->get_results( $query, ARRAY_A );

            $paymentMetaModel = new \Indeed\Uap\Db\PaymentMeta();
            foreach ( $data as $key => $array ){
                $data[$key]['number_of_payments'] = false;
                $payments = $paymentMetaModel->getAllPaymentIdsForMetaNameMetaValue( 'payout_id', $array['id'] );
                if ( is_array( $payments ) && count( $payments ) > 0 ){
                    $data[$key]['number_of_payments'] = count( $payments );
                }
            }
            return $data;
        } else {
            $data = $wpdb->get_var( $query );
            return $data;
        }
    }

    /**
      * @param int
      * @return array
      */
    public function deleteOne( $id=0 )
    {
        global $wpdb;
        if ( $this->getOne( $id ) === false ){
            return false;
        }
        $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}uap_payouts WHERE id=%d;", $id );
        return $wpdb->query( $query );
    }

    /**
      * @param int
      * @return fload
      */
    public function getCompletedPercentage( $id=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT up.status as status FROM {$wpdb->prefix}uap_payments up
                                        INNER JOIN {$wpdb->prefix}uap_payments_meta upm on upm.payment_id=up.id
                                        WHERE upm.meta_name='payout_id' AND upm.meta_value=%d ", $id );
        $data = $wpdb->get_results( $query, ARRAY_A );
        if ( $data === null || $data === false || $data === '' ){
            return false;
        }
        $completed = 0;
        $totalPayments = count( $data );
        foreach ( $data as $array ){
            if ( (int)$array['status'] === 2 ){
                $completed++;
            }
        }
        if ( $completed === 0 ){
            return 0;
        }
        $percentage = 100 / $totalPayments * $completed;
        return round( $percentage, 2 );
    }

    /**
      * @param none
      * @return bool
      */
    public function haveEntries()
    {
        global $wpdb;
        $results = $wpdb->get_var( "SELECT id from {$wpdb->prefix}uap_payouts ORDER BY id ASC LIMIT 1;" );
        if ( $results === null || $results === false ){
            return false;
        }
        return true;
    }
}

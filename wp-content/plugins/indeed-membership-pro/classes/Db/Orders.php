<?php
namespace Indeed\Ihc\Db;

class Orders
{
    private $id             = 0;
    private $data           = null;

    public function setData( $data = array() )
    {
        if ( !$data ){
            return;
        }
        foreach ( $data as $key => $value ){
            $this->data[ $key ] = $value;
        }
        return $this;
    }

    public function setId( $id=0 )
    {
        $this->id = $id;
        return $this;
    }

    public function fetch()
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT id, uid, lid, amount_type, amount_value, automated_payment, status, create_date FROM {$wpdb->prefix}ihc_orders WHERE id=%d;", $this->id );
        $this->data = $wpdb->get_row( $query );
        $this->data = $this->data;
        return $this;
    }

    public function get()
    {
        return $this->data;
    }

    public function save()
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT id, uid, lid, amount_type, amount_value, automated_payment, status, create_date FROM {$wpdb->prefix}ihc_orders WHERE id=%d;", $this->id );
        $writeData = $wpdb->get_row( $query );
        if ( $writeData ){
            /// update
            $writeData = (array)$writeData;
            foreach ( $this->data as $key => $value ){
                $writeData[$key] = $value;
            }
            $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_orders SET
                                          uid=%d,
                                          lid=%d,
                                          amount_type=%s,
                                          amount_value=%s,
                                          automated_payment=%s,
                                          status=%s,
                                          create_date=%s
                                          WHERE id=%d;",
            $writeData['uid'], $writeData['lid'], $writeData['amount_type'], $writeData['amount_value'], $writeData['automated_payment'],
            $writeData['status'], $writeData['create_date'], $writeData['id'] );
            $wpdb->query( $query );
            do_action( 'ump_payment_check', $writeData['id'], 'update' );
            return $writeData['id'];
        } else {
            /// insert

            /// since version 8.6, before we used NOW() function in mysql
            $createDate = indeed_get_current_time_with_timezone();
            if ( isset( $this->data['create_date'] ) && $this->data['create_date'] != '' ){
                $createDate = $this->data['create_date'];
            }

            $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ihc_orders
                                          VALUES( NULL, %d, %d, %s, %s, %d, %s, %s );",
            $this->data['uid'], $this->data['lid'], $this->data['amount_type'], $this->data['amount_value'], $this->data['automated_payment'],
            $this->data['status'], $createDate );
            $wpdb->query( $query );
            $orderId = $wpdb->insert_id;

            do_action( 'ihc_action_after_order_placed', $this->data['uid'], $this->data['lid'], $orderId );
            do_action( 'ump_payment_check', $orderId, 'insert' );
            return $wpdb->insert_id;
        }

    }

    public function getStatus()
    {
        return isset( $this->data->status ) ? $this->data->status : false;
    }

    public function update( $colName='', $value='' )
    {
        global $wpdb;
        if ( !$colName || !$value || empty($this->id) ){
            return false;
        }
        $colName = sanitize_text_field( $colName );
        $queryString = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_orders SET $colName=%s WHERE id=%d;", $value, $this->id );

        $result = $wpdb->query( $queryString );
        do_action( 'ump_payment_check', $this->id, 'update' );
        return $result;
    }

    /**
     * @param int
     * @param int
     * @return none
     */
    public function getCountInInterval( $start=0, $end=0  )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT COUNT( id ) FROM {$wpdb->prefix}ihc_orders
                                      WHERE
                                      IFNULL( UNIX_TIMESTAMP( create_date ), 0 ) > %d
                                      AND
                                      IFNULL( UNIX_TIMESTAMP( create_date ), 0 ) < %d
                                      AND
                                      status='Completed';", $start, $end );
        $count = $wpdb->get_var( $query );
        if ( $count == false ){
            return 0;
        }
        return $count;
    }

    /**
     * @param none
     * @return none
     */
    public function getCountAll()
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT COUNT( id ) FROM {$wpdb->prefix}ihc_orders ;";
        $count = $wpdb->get_var( $query );
        if ( $count == false ){
            return 0;
        }
        return $count;
    }

    /**
     * @param none
     * @return none
     */
    public function getTotalAmount()
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT SUM( amount_value ) FROM {$wpdb->prefix}ihc_orders ;";
        $data = $wpdb->get_var( $query );
        if ( $data == false ){
            return 0;
        }
        return $data;
    }

    /**
     * @param none
     * @return none
     */
    public function getLastOrders( $limit=5 )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT uid, lid, amount_type, amount_value, create_date
                                        FROM {$wpdb->prefix}ihc_orders
                                        ORDER BY create_date DESC LIMIT %d;", $limit );
        $data = $wpdb->get_results( $query );
        if ( $data == false ){
            return [];
        }
        return $data;
    }

    /**
     * @param none
     * @return none
     */
    public function getTotalAmountInInterval( $start=0, $end=0 )
    {
      global $wpdb;
      $query = $wpdb->prepare( "SELECT SUM( amount_value ) FROM {$wpdb->prefix}ihc_orders
                                    WHERE
                                    IFNULL( UNIX_TIMESTAMP( create_date ), 0 ) > %d
                                    AND
                                    IFNULL( UNIX_TIMESTAMP( create_date ), 0 ) < %d
                                    AND status='Completed';", $start, $end );
      $total = $wpdb->get_var( $query );

      if ( $total == false ){
            return 0;
        }
        return $total;
    }

    public function getFirstOrderDaysPassed()
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT UNIX_TIMESTAMP() - UNIX_TIMESTAMP(create_date) FROM {$wpdb->prefix}ihc_orders
                                    WHERE
                                    IFNULL( UNIX_TIMESTAMP( create_date ), 0 ) > 0
                                    ORDER BY create_date
                                    ASC
                                    LIMIT 1;
        ";
        $days = $wpdb->get_var( $query );
        if ( $days > 0 ){
            $days = $days / (24 * 60 * 60);
            return (int)$days;
        }
        return 0;
    }

    /**
     * @param none
     * @return none
     */
    public function getTotalAmountInLastTime( $startTime=0, $groupBy='days' )
    {
        global $wpdb;
        switch ( $groupBy ){
            case 'days':
              //No query parameters required, Safe query. prepare() method without parameters can not be called
              $query = "SELECT DATE_FORMAT( create_date, '%Y-%m-%d' ) as the_time, SUM(amount_value) as sum_value
              																	FROM {$wpdb->prefix}ihc_orders ";
              break;
            case 'weeks':
              //No query parameters required, Safe query. prepare() method without parameters can not be called
              $query = "SELECT DATE_FORMAT( create_date, 'week %U' ) as the_time, SUM(amount_value) as sum_value
              																	FROM {$wpdb->prefix}ihc_orders ";
              break;
            case 'months':
              //No query parameters required, Safe query. prepare() method without parameters can not be called
              $query = "SELECT DATE_FORMAT( create_date, '%M %Y' ) as the_time, SUM(amount_value) as sum_value
                                                FROM {$wpdb->prefix}ihc_orders ";
              break;
            case 'years':
              //No query parameters required, Safe query. prepare() method without parameters can not be called
              $query = "SELECT DATE_FORMAT( create_date, '%Y' ) as the_time, SUM(amount_value) as sum_value
                                                FROM {$wpdb->prefix}ihc_orders ";
              break;
        }

        $query .= $wpdb->prepare( " WHERE
                                          IFNULL( UNIX_TIMESTAMP( create_date ), 0 ) > %d
        																	GROUP BY the_time
                                          ORDER BY create_date ASC;", $startTime );
        $data = $wpdb->get_results( $query );
        return $data;
    }


    /**
     * @param int
     * @param array
     * @return array
     */
    public function getMany( $uid=0, $params=[] )
    {
       global $wpdb;
       $array = array();
       $table = $wpdb->prefix . 'ihc_orders';
       $q = "SELECT o.id,o.uid,o.lid,o.amount_type,o.amount_value,o.automated_payment,o.status,o.create_date, u.user_login as user,
       orders_meta.meta_value as code
       FROM $table AS o ";

       //if ( !empty( $params['q'] ) ){
         $q .= " INNER JOIN {$wpdb->users} u ON u.ID=o.uid ";// ON u.ID=o.uid since version 12.01
       //}
       $q .= " INNER JOIN {$wpdb->prefix}ihc_orders_meta orders_meta ON o.id=orders_meta.order_id ";

       if ( !empty( $params['payment_gateway'] ) ){
          $q .= " INNER JOIN {$wpdb->prefix}ihc_orders_meta AS om_pg ON o.id=om_pg.order_id ";
       }

       if ( !empty( $params['subscription_type'] ) ){
          $q .= " INNER JOIN {$wpdb->prefix}ihc_memberships_meta AS imm ON o.lid=imm.membership_id ";
       }

       $q .= " WHERE 1=1";
       if ( $uid!== false && $uid !== 0 ){
            $q .= $wpdb->prepare( " AND o.uid=%d ", $uid );
       }

       $q .= " AND orders_meta.meta_key='code' ";

       // status
       if ( !empty( $params['status'] ) ){
            $q .= $wpdb->prepare( " AND o.status=%s ", $params['status'] );
       }

       if ( !empty( $params['status_in'] ) ){
            $q .= " AND o.status IN (" . iumpEscapeArrayForQuery($params['status_in']) . ") ";
       }

       // search keyword
       if ( !empty( $params['q'] ) ){
            $q .= $wpdb->prepare( " AND ( u.user_login LIKE %s OR u.user_nicename LIKE %s OR u.user_email LIKE %s ",
                  '%' . $params['q'] . '%',
                  '%' . $params['q'] . '%',
                  '%' . $params['q'] . '%');
          if ( is_numeric( $params['q'] ) ){
            $q .= $wpdb->prepare( "  OR o.amount_value=%s  ", $params['q'] );
          }
          $q .= " ) ";
       }

       // start time
       if ( !empty( $params['start_time'] ) ){
         $params['start_time'] = date( 'Y-m-d', strtotime( $params['start_time'] ) );
         $q .= $wpdb->prepare( " AND o.create_date>%s ", $params['start_time'] );
       }

       // end time
       if ( !empty( $params['end_time'] ) ){
         $params['end_time'] = date( 'Y-m-d', strtotime( $params['end_time'] ) );
         $q .= $wpdb->prepare( " AND o.create_date<%s ", $params['end_time'] );
       }

       if ( !empty( $params['payment_gateway'] ) ){
          $q .= " AND ( om_pg.meta_key='ihc_payment_type' AND om_pg.meta_value IN (" . iumpEscapeArrayForQuery( $params['payment_gateway'] ) . ") ) ";
       }

       if ( !empty( $params['subscription_type'] ) ){
          $q .= " AND ( imm.meta_key='access_type' AND imm.meta_value IN (" . iumpEscapeArrayForQuery( $params['subscription_type'] ) . ") ) ";
       }

       // order by
       if ( !empty( $params['order_by'] ) && !empty( $params['order_type'] ) ){
          $q .= $wpdb->prepare( "  ORDER BY %1s %1s ", $params['order_by'], $params['order_type'] );
       } else {
          $q .= " ORDER BY o.id DESC ";
       }

       // limit
       if ( !empty( $params['limit'] ) && isset( $params['offset'] ) ){
            $q .= $wpdb->prepare( "  LIMIT %d OFFSET %d;", $params['limit'], $params['offset'] );
       } else {
            $q .= " LIMIT 30 OFFSET 0;";
       }


       $data = $wpdb->get_results($q);
       if ( !$data ){
          return [];
       }
       foreach ($data as $object){
         $temp = (array)$object;
         $temp['metas'] = \Ihc_Db::get_all_order_metas($temp['id']);
         //$temp['user'] = \Ihc_Db::get_username_by_wpuid($temp['uid']);
         $temp['transaction_id'] = (empty($temp['metas']) || empty($data['metas']['transaction_id'])) ? \Ihc_Db::get_transaction_id_by_order_id($temp['id']) : $temp['metas']['transaction_id'];
         if (empty($temp['user'])){
           $temp['user'] = '-';
         }
         ///payment type
         if (empty($temp['metas']['ihc_payment_type'])){
           $temp['metas']['ihc_payment_type'] = \Ihc_Db::get_payment_type_by_transaction_id($temp['transaction_id']);
         }
         $temp['level'] = \Ihc_Db::get_level_name_by_lid($temp['lid']);
         $array[] = $temp;
       }
       return $array;
    }

    /**
     * modified on 12.1
     * @param array
     * @return int
     */
    public function countWithFilter( $uid=0, $params=[] )
    {
      global $wpdb;
      $array = array();
      $table = $wpdb->prefix . 'ihc_orders';
      $q = "SELECT COUNT(o.id) as number_of_orders
      FROM $table AS o ";

      //if ( !empty( $params['q'] ) ){
        $q .= " INNER JOIN {$wpdb->users} u ON u.ID=o.uid ";// ON u.ID=o.uid since version 12.01
      //}
      $q .= " INNER JOIN {$wpdb->prefix}ihc_orders_meta orders_meta ON o.id=orders_meta.order_id ";

      if ( !empty( $params['payment_gateway'] ) ){
         $q .= " INNER JOIN {$wpdb->prefix}ihc_orders_meta AS om_pg ON o.id=om_pg.order_id ";
      }

      if ( !empty( $params['subscription_type'] ) ){
         $q .= " INNER JOIN {$wpdb->prefix}ihc_memberships_meta AS imm ON o.lid=imm.membership_id ";
      }

      $q .= " WHERE 1=1";
      if ( $uid!== false && $uid !== 0 ){
           $q .= $wpdb->prepare( " AND o.uid=%d ", $uid );
      }

      $q .= " AND orders_meta.meta_key='code' ";

      // status
      if ( !empty( $params['status'] ) ){
           $q .= $wpdb->prepare( " AND o.status=%s ", $params['status'] );
      }

      if ( !empty( $params['status_in'] ) ){
           $q .= " AND o.status IN (" . iumpEscapeArrayForQuery($params['status_in']) . ") ";
      }

      // search keyword
      if ( !empty( $params['q'] ) ){
            $q .= $wpdb->prepare( " AND ( u.user_login LIKE %s OR u.user_nicename LIKE %s OR u.user_email LIKE %s ",
                  '%' . $params['q'] . '%',
                  '%' . $params['q'] . '%',
                  '%' . $params['q'] . '%');
          if ( is_numeric( $params['q'] ) ){
            $q .= $wpdb->prepare( "  OR o.amount_value=%s  ", $params['q'] );
          }
          $q .= " ) ";
      }

      // start time
      if ( !empty( $params['start_time'] ) ){
        $params['start_time'] = date( 'Y-m-d', strtotime( $params['start_time'] ) );
        $q .= $wpdb->prepare( " AND o.create_date>%s ", $params['start_time'] );
      }

      // end time
      if ( !empty( $params['end_time'] ) ){
        $params['end_time'] = date( 'Y-m-d', strtotime( $params['end_time'] ) );
        $q .= $wpdb->prepare( " AND o.create_date<%s ", $params['end_time'] );
      }

      if ( !empty( $params['payment_gateway'] ) ){
         $q .= " AND ( om_pg.meta_key='ihc_payment_type' AND om_pg.meta_value IN (" . iumpEscapeArrayForQuery( $params['payment_gateway'] ) . ") ) ";
      }

      if ( !empty( $params['subscription_type'] ) ){
         $q .= " AND ( imm.meta_key='access_type' AND imm.meta_value IN (" . iumpEscapeArrayForQuery( $params['subscription_type'] ) . ") ) ";
      }

      $data = $wpdb->get_var($q);
      if ( !$data ){
         return 0;
      }

      return $data;
    }
}

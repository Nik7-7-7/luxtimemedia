<?php
/**
 * @since version 9.4
 getting all the memberships ( levels in old ump ):
 $memberships = \Indeed\Ihc\Db\Memberships::getAll();
 get one membership by name :
 $memberships = \Indeed\Ihc\Db\Memberships::getOneByName( $name='' );
 get one membership by id:
 $memberships = \Indeed\Ihc\Db\Memberships::getOne( $id=0 );
 delete one membership:
 \Indeed\Ihc\Db\Memberships::deleteOne( $id=0 );
 get membership name:
 \Indeed\Ihc\Db\Memberships::getMembershipName( $id=0 );
 get membership label:
 \Indeed\Ihc\Db\Memberships::getMembershipLabel( $id=0 );
 get membership short-description:
 \Indeed\Ihc\Db\Memberships::getMembershipShortDescription( $id=0 );
 */
namespace Indeed\Ihc\Db;

require_once IHC_PATH . 'utilities.php';
class Memberships
{
    private static $tablePrefix = '';

    /**
     * @param string
     * @return none
     */
    public static function setTablePrefix( $prefix='' )
    {
        self::$tablePrefix = $prefix;
    }

    /**
     * @param string
     * @return bool
     */
    public static function createTables( $engine='MyISAM' )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "show tables like '{$dbPrefix}ihc_memberships'";
  			if ($wpdb->get_var( $query ) != $dbPrefix . 'ihc_memberships' ){
    				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
    				$sql = "CREATE TABLE {$dbPrefix}ihc_memberships (
    															id int(11) NOT NULL AUTO_INCREMENT,
    															name VARCHAR(200) NOT NULL,
    															label VARCHAR(200) NOT NULL,
                                  short_description VARCHAR(400),
                                  payment_type VARCHAR(50),
                                  price DECIMAL(12, 2) DEFAULT 0,
    															status TINYINT(1) DEFAULT 1,
                                  the_order INT(6),
    															created_at INT(11),
    															PRIMARY KEY (`id`),
    															INDEX idx_ihc_memberships_id (`id`)
    				)
            ENGINE=$engine
						CHARACTER SET utf8 COLLATE utf8_general_ci;
    				";
    				dbDelta ( $sql );
  			}
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "show tables like '{$dbPrefix}ihc_memberships_meta'";
        if ($wpdb->get_var( $query ) != $dbPrefix . 'ihc_memberships_meta' ){
            require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
            $sql = "CREATE TABLE {$dbPrefix}ihc_memberships_meta (
                                  id INT(11) NOT NULL AUTO_INCREMENT,
                                  membership_id INT(11) NOT NULL,
                                  meta_key VARCHAR(300) NOT NULL,
                                  meta_value TEXT,
                                  PRIMARY KEY (`id`),
                                  INDEX idx_ihc_memberships_meta_membership_id (`membership_id`)
            )
            ENGINE=$engine
						CHARACTER SET utf8 COLLATE utf8_general_ci;
            ";
            dbDelta ( $sql );
        }
    }

    /**
     * @param none
     * @return bool
     */
    public static function importLevels()
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SHOW TABLES LIKE '{$dbPrefix}ihc_memberships';";
        $exists = $wpdb->get_var( $query );
        if ( $exists === null ){
            return;
        }
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SHOW TABLES LIKE '{$dbPrefix}ihc_memberships_meta';";
        $exists = $wpdb->get_var( $query );
        if ( $exists === null ){
            return;
        }

        // backup old data
        $query = "SELECT id, name, label, short_description, payment_type, price, status, the_order, created_at
                                          FROM {$dbPrefix}ihc_memberships;";
        $data = $wpdb->get_results( $query );
        if ( $data ){
            update_option( 'ihc_memberships_backup', serialize( $data ) );
        }
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT id, membership_id, meta_key, meta_value FROM {$dbPrefix}ihc_memberships_meta;";
        $data = $wpdb->get_results( $query );
        if ( $data ){
            update_option( 'ihc_memberships_meta_backup', serialize( $data ) );
        }
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "DELETE FROM {$dbPrefix}ihc_memberships;";
        $wpdb->query( $query );
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "DELETE FROM {$dbPrefix}ihc_memberships_meta;";
        $wpdb->query( $query );
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "ALTER TABLE {$dbPrefix}ihc_memberships AUTO_INCREMENT=1;";
        $wpdb->query( $query );
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "ALTER TABLE {$dbPrefix}ihc_memberships_meta AUTO_INCREMENT=1;";
        $wpdb->query( $query );
        $memberships = get_option( 'ihc_levels' );
        if ( !$memberships || !is_array( $memberships ) ){
            return false;
        }
        foreach ( $memberships as $id => $membershipData ){
            $membershipData['level_id'] = $id;
            self::save( $membershipData, true );
        }
    }

    /**
     * @param array
     * @param bool
     * @return array
     */
    public static function save( $data=[], $isImport=false )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        if ( !$data ){
            return [
                      'success'     => false,
                      'reason'      => esc_html__( 'No data provided.', 'ihc' ),
            ];
        }
        $class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
        $ol_dL_ogs = new $class();
        if ( $isImport === false && self::getCount() >=3 && ( (int)$ol_dL_ogs->FGCS() > 0 || $ol_dL_ogs->GCP() === true ) ){
            return [
                      'success'     => false,
                      'reason'      => esc_html__( 'You cannot add more Memberships on Trial Version!', 'ihc' ),
            ];
        }

        $membershipDefaultValues = [
                      'level_id'            => -1,
                      'name'                => '',
                      'label'               => '',
                      'short_description'   => '',
                      'payment_type'        => '',
                      'price'               => '',
                      'status'              => '',
                      'order'               => '',
                      'created_at'          => time(),
        ];
        foreach ( $membershipDefaultValues as $key => $value ){
           if ( !isset( $data[$key] ) ){
              $data[$key] = $value;
           }
        }

        $data['name'] = ihc_make_string_simple( $data['name'] );

        // no slug provided
        if ( $data['name'] === '' ){
            return [
                      'success'     => false,
                      'reason'      => esc_html__( 'Unique Membership Slug is required.', 'ihc' ),
            ];
        }

        if ( $data['label'] === '' ){
            return [
                      'success'     => false,
                      'reason'      => esc_html__( 'Membership Name is required.', 'ihc' ),
            ];
        }

        // no access type provided
        if ( !isset( $data['access_type'] ) ){
            $data['billing_type'] = 'bl_onetime';
        }

        $levelDataForName = self::getOneByName( $data['name'] );
        if ( isset( $levelDataForName['id'] ) && $levelDataForName['id'] !== $data['level_id'] ){
            return [
                      'success'     => false,
                      'reason'      => esc_html__( 'A Membership with this Slug "', 'ihc') . $data['name'] . esc_html__( '" already exists.', 'ihc' ),
            ];
        }

        if ( !empty( $data['level_id'] ) && self::getOne( $data['level_id'] ) ){
            // update
            $query = $wpdb->prepare( "UPDATE {$dbPrefix}ihc_memberships
                                        SET
                                        name=%s,
                                        label=%s,
                                        short_description=%s,
                                        payment_type=%s,
                                        price=%s,
                                        status=%s,
                                        the_order=%s
                                        WHERE
                                        id=%d
            ", $data['name'], $data['label'], $data['short_description'], $data['payment_type'],
               $data['price'], $data['status'], $data['order'], $data['level_id'] );
            $result = $wpdb->query( $query );
        } else {
            // create
            if ( $data['level_id'] > -1 ){
              $query = $wpdb->prepare( "INSERT INTO {$dbPrefix}ihc_memberships
                                          VALUES( %s, %s, %s, %s, %s, %s, %s, %s, %s )
              ", $data['level_id'],
                 $data['name'],
                 $data['label'],
                 $data['short_description'],
                 $data['payment_type'],
                 $data['price'],
                 $data['status'],
                 $data['order'],
                 $data['created_at']
              );
            } else {
              $query = $wpdb->prepare( "INSERT INTO {$dbPrefix}ihc_memberships
                                          VALUES( null, %s, %s, %s, %s, %s, %s, %s, %s )
              ",
                 $data['name'],
                 $data['label'],
                 $data['short_description'],
                 $data['payment_type'],
                 $data['price'],
                 $data['status'],
                 $data['order'],
                 $data['created_at']
              );
            }
            $result = $wpdb->query( $query );
            $data['level_id'] = $wpdb->insert_id;
        }
        if ( $result === false || $data['level_id'] == 0 ){
          return [
                    'success'     => false,
                    'reason'      => esc_html__( 'Something went wrong. Please try again.', 'ihc' ),
          ];
        }
        // meta
        foreach ( $data as $key => $value ){
            if ( isset( $membershipDefaultValues[$key] ) ){
                continue;
            }
            self::saveMeta( $data['level_id'], $key, $value );
        }
        do_action( 'ihc_action_admin_save_membership', $data );
        return [
                  'success'     => true,
                  'reason'      => esc_html__( 'Membership has been saved.', 'ihc' ),
                  'id'          => $data['level_id'],
        ];
    }

    public static function setOrderForMembership( $id=0, $order=0 )
    {
        global $wpdb;
        if ( !$id ){
            return false;
        }
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        $query = $wpdb->prepare( "UPDATE {$dbPrefix}ihc_memberships SET the_order=%d WHERE id=%d;", $order, $id );
        return $wpdb->query( $query );
    }

    /**
     * @param int
     * @return bool
     */
    public static function deleteOne( $id=0 )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        if ( !$id ){
            return false;
        }
        $query = $wpdb->prepare( "DELETE FROM {$dbPrefix}ihc_memberships WHERE id=%d;", $id );
        $wpdb->query( $query );
        $query = $wpdb->prepare( "DELETE FROM {$dbPrefix}ihc_memberships_meta WHERE membership_id=%d;", $id );
        $response = $wpdb->query( $query );
        do_action( 'ihc_action_after_delete_membership', $id, $response );
        return $response;
    }

    /**
     * @param int
     * @return array
     */
    public static function getOne( $id=0 )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        if ( !$id ){
            return false;
        }
        $query = $wpdb->prepare( "SELECT id, name, label, short_description, payment_type, price, status, the_order, created_at
                                      FROM {$dbPrefix}ihc_memberships
                                      WHERE id=%d
        ", $id );
        $membership = $wpdb->get_row( $query );
        if ( !$membership ){
            return false;
        }
        $membership = (array)$membership;
        $metas = self::getAllMetaForMembership( $id );
        return array_merge( $membership, $metas );
    }


      /**
        * @param string
        * @return array
        */
    public static function getOneByName( $name='' )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        if ( $name == '' ){
          return false;
        }
        $query = $wpdb->prepare( "SELECT id, name, label, short_description, payment_type, price, status, the_order, created_at
                                        FROM {$dbPrefix}ihc_memberships WHERE name=%s;", $name );
        $membership = $wpdb->get_row( $query );
        if ( !$membership ){
          return false;
        }
        $membership = (array)$membership;
        $metas = self::getAllMetaForMembership( $membership['id'] );
        return array_merge( (array)$membership, $metas );
    }

    /**
     * @param int
     * @return string
     */
    public static function getMembershipName( $id=0 )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        if ( !$id ){
            return '';
        }
        $query = $wpdb->prepare( "SELECT name FROM {$dbPrefix}ihc_memberships WHERE id=%d", $id );
        return $wpdb->get_var( $query );
    }

   /**
    * @param int
    * @return string
    */
    public static function getMembershipLabel( $id=0 )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        if ( !$id ){
          return '';
        }
        $query = $wpdb->prepare( "SELECT label FROM {$dbPrefix}ihc_memberships WHERE id=%d", $id );
        return $wpdb->get_var( $query );
    }

    /**
     * @param int
     * @return string
     */
    public static function getMembershipShortDescription( $id=0 )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        if ( !$id ){
          return '';
        }
        $query = $wpdb->prepare( "SELECT short_description FROM {$dbPrefix}ihc_memberships WHERE id=%d", $id );
        $shortDescription = $wpdb->get_var( $query );
        if ( $shortDescription !== null && $shortDescription != '' ){
            $shortDescription = stripslashes( $shortDescription );
        }
        return $shortDescription;
    }

    /**
     * @param int
     * @return string
     */
    public static function getMembershipGracePeriod( $id=0 )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        if ( !$id ){
          return false;
        }

        $gracePeriod = self::getOneMeta( $id , 'grace_period' );

        if ( $gracePeriod !== false && $gracePeriod != '' ){
          return $gracePeriod;
        } else {
          $gracePeriod = get_option('ihc_grace_period');
    			if ( $gracePeriod !== false && $gracePeriod != '' ){
              return $gracePeriod;
            }
        }
        return false;
    }

    /**
     * @param none
     * @return array
     */
    public static function getAll()
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT id, name, label, short_description, payment_type, price, status, the_order, created_at
                        FROM {$dbPrefix}ihc_memberships
                        WHERE status=0
                        ORDER BY id ASC
        ";
        $memberships = $wpdb->get_results( $query );
        if ( !$memberships ){
            return false;
        }
        foreach ( $memberships as $object ){
            $metas = self::getAllMetaForMembership( $object->id );
            $returnData[$object->id] = array_merge( (array)$object, $metas );
        }
        return $returnData;
    }

    public static function getCount( $search='' )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT COUNT( m.id ) as number_of_memberships
                        FROM {$dbPrefix}ihc_memberships as m
                        WHERE m.status=0 ";
        if ( $search !== '' ){
            $query .= $wpdb->prepare( " AND ( m.name LIKE %s OR m.label LIKE %s OR m.short_description LIKE %s ) ", '%' . $search . '%', '%' . $search . '%', '%' . $search . '%' );
        }
        $results = $wpdb->get_var( $query );
        if ( !$results ){
            return 0;
        }
        return $results;
    }

    /**
     * @param int
     * @param string
     * @return bool
     */
    public static function getAllMetaForMembership( $membershipId=0 )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        if ( !$membershipId ){
            return false;
        }
        $query = $wpdb->prepare( "SELECT meta_key, meta_value
                                      FROM {$dbPrefix}ihc_memberships_meta
                                      WHERE membership_id=%d;", $membershipId );
        $all = $wpdb->get_results( $query );
        if ( !$all ){
            return [];
        }
        foreach ( $all as $object ){
            $meta[ $object->meta_key ] = $object->meta_value;
        }
        return $meta;
    }


    /**
     * @param int
     * @param string
     * @return bool
     */
    public static function getOneMeta( $membershipId=0, $metaKey='' )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        if ( !$membershipId || $metaKey == '' ){
            return false;
        }
        $query = $wpdb->prepare( "SELECT id,meta_value
                                      FROM {$dbPrefix}ihc_memberships_meta
                                      WHERE membership_id=%d
                                      AND meta_key=%s
                                      ORDER BY id DESC LIMIT 1;", $membershipId, $metaKey );
        $data = $wpdb->get_row( $query );
        if ( isset( $data->id ) ){
            return $data->meta_value;
        }
        return null;
    }

    /**
     * @param int
     * @param string
     * @return bool
     */
    public static function deleteOneMeta( $membershipId=0, $metaKey='' )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        if ( !$membershipId ){
            return false;
        }
        $query = $wpdb->prepare( "DELETE FROM
                                    {$dbPrefix}ihc_memberships_meta
                                    WHERE membership_id=%d AND meta_key=%s;", $membershipId, $metaKey );
        return $wpdb->query( $query );
    }

    /**
     * @param int
     * @param string
     * @return bool
     */
    public static function deleteAllMetaForMembership( $membershipId=0 )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        if ( !$membershipId ){
            return false;
        }
        $query = $wpdb->prepare( "DELETE FROM
                                    {$dbPrefix}ihc_memberships_meta
                                    WHERE membership_id=%d;", $membershipId );
        return $wpdb->query( $query );
    }

    /**
     * @param int
     * @param string
     * @param string
     * @return bool
     */
    public static function saveMeta( $membershipId=0, $metaKey='', $metaValue='' )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        if ( !$membershipId || $metaKey == '' ){
            return false;
        }
        if ( self::getOneMeta( $membershipId, $metaKey ) != null ){
            // update
            $query = $wpdb->prepare( "UPDATE {$dbPrefix}ihc_memberships_meta
                                        SET meta_value=%s
                                        WHERE
                                        membership_id=%d
                                        AND
                                        meta_key=%s
            ", $metaValue, $membershipId, $metaKey );
        } else {
            // create
            $query = $wpdb->prepare( "INSERT INTO {$dbPrefix}ihc_memberships_meta
                                        VALUES( NULL, %d, %s, %s );
            ", $membershipId, $metaKey, $metaValue );
        }
        return $wpdb->query( $query );
    }

    /**
     * @param int
     * @param string
     * @return string
     */
    public static function getEndTime( $lid=0, $currentTime='' )
    {
        $levelData = self::getOne( $lid );
        switch ($levelData['access_type']){
            case 'unlimited':
              $endTime = strtotime('+10 years', $currentTime );//unlimited will be ten years
              break;
            case 'limited':
              if (!empty($levelData['access_limited_time_type']) && !empty($levelData['access_limited_time_value'])){
                //Deprecated
                $multiply = ihc_get_multiply_time_value($levelData['access_limited_time_type']);
                $endTime = $currentTime + $multiply * $levelData['access_limited_time_value'];

                $multiplyToAdd = $levelData['access_limited_time_type'];
                $endTime = self::getExtraTime($multiplyToAdd, $levelData['access_limited_time_value'], $currentTime);

              }
              break;
            case 'date_interval':
              if (!empty($levelData['access_interval_end'])){
                $endTime = strtotime($levelData['access_interval_end']);
              }
              break;
            case 'regular_period':
              if (!empty($levelData['access_regular_time_type']) && !empty($levelData['access_regular_time_value'])){
                //Deprecated
                $multiply = ihc_get_multiply_time_value($levelData['access_regular_time_type']);
                $endTime = $currentTime + $multiply * $levelData['access_regular_time_value'];

                $multiplyToAdd = $levelData['access_regular_time_type'];

                $endTime = self::getExtraTime($multiplyToAdd, $levelData['access_regular_time_value'], $currentTime);
              }
              break;
        }
        return $endTime;
    }

    /**
     * @param int
     * @param string
     * @return string
     */
    public static function getEndTimeForTrial( $lid=0, $currentTime='' )
    {
        $levelData = self::getOne( $lid );
        if ( empty( $levelData['access_trial_type'] ) ){
            return false;
        }

        if ( $levelData['access_trial_type'] == 1 ){
            //Deprecated
            $multiply = ihc_get_multiply_time_value( $levelData['access_trial_time_type'] );

            $multiplyToAdd = $levelData['access_trial_time_type'];

            $timeToAdd = $levelData['access_trial_time_value'];
        } else {
            ///couple of circles
            //Deprecated
            $multiply = ihc_get_multiply_time_value( $levelData['access_regular_time_type'] );

            $multiplyToAdd = $levelData['access_regular_time_type'];

            if ( $levelData['access_trial_couple_cycles'] != '' && $levelData['access_trial_couple_cycles'] > 1 ){
                $timeToAdd = $levelData['access_regular_time_value'] * $levelData['access_trial_couple_cycles'];
            } else {
                $timeToAdd = $levelData['access_regular_time_value'];
            }
        }
        //Deprecated
        $endTime = $currentTime + $multiply * (int)$timeToAdd;

        $endTime = self::getExtraTime($multiplyToAdd, $timeToAdd, $currentTime);

        return $endTime;
    }

    /**
     * @param string
     * @param int
     * @param string
     * @return int
     */
    public static function getExtraTime( $time_type='', $timeToAdd=0, $currentTime='' )
    {
      /*
       * @param string D,W,M,Y
       * @return time in seconds
       */
       if(!isset($currentTime) || $currentTime == ''){
         $currentTime = indeed_get_unixtimestamp_with_timezone();
       }

       $extraTime = FALSE;

      if(!isset($time_type) || $time_type == '' && !isset($timeToAdd) && $timeToAdd == 0){
        return $extraTime;
      }

      $date = new \DateTime();
  		$date->setTimestamp( $currentTime);

      switch ($time_type){
        case 'D':
          $date->modify( '+'.$timeToAdd.' days');
        break;
        case 'W':
          $date->modify( '+'.$timeToAdd.' weeks');
        break;
        case 'M':
          $date->modify( '+'.$timeToAdd.' months');
        break;
        case 'Y':
          $date->modify( '+'.$timeToAdd.' years');
        break;
      }

       $time = $date->format('Y-m-d H:i:s');
       return strtotime( $time );
    }

    public static function isTrial( $lid=0 )
    {
        $levelData = self::getOne( $lid );
        if ( empty( $levelData['access_trial_type'] ) ){
            return false;
        }
        if ( !isset( $levelData['access_trial_price'] ) || $levelData['access_trial_price'] === '' ){
            return false;
        }
        if ( !isset( $levelData['access_trial_type'] ) ){
            return false;
        }

        if ( $levelData['access_trial_type'] == 1 && $levelData['access_trial_time_value'] != '' ){
            return true;
        } else if ( $levelData['access_trial_type'] == 2 && $levelData['access_trial_couple_cycles'] >= 1 ) {
            return true;
        }
        return false;
    }

    /**
     * @param int
     * @return string
     */
    public static function getAccessTypeAsLabel( $lid=0 )
    {
        $levelData = self::getOne( $lid );
        if ( !$levelData ){
            return '';
        }
        $string = '';
        switch ( $levelData['access_type'] ){
            case 'regular_period':
              $string = esc_html__( 'Subscription - ', 'ihc' );
              switch ( $levelData['access_regular_time_type'] ){
                  case 'D':
                    $string .= esc_html__( 'Daily', 'ihc' );
                    break;
                  case 'W':
                    $string .= esc_html__( 'Weekly', 'ihc' );
                    break;
                  case 'M':
                    $string .= esc_html__( 'Monthly', 'ihc' );
                    break;
                  case 'Y':
                    $string .= esc_html__( 'Yearly', 'ihc' );
                    break;
              }
              break;
            case 'unlimited':
              $string .= esc_html__( 'LifeTime', 'ihc' );
              break;
            case 'limited':
              $string .= esc_html__( 'Limited Time', 'ihc' );
              break;
            case 'date_interval':
              $string .= esc_html__( 'Date Range', 'ihc' );
              break;
        }
        return $string;
    }

    /**
     * @param int
     * @return bool
     */
    public static function isRecurring( $lid=0 )
    {
        $levelData = self::getOne( $lid );
        if ( !$levelData ){
            return false;
        }
        if ( isset( $levelData['access_type'] ) && $levelData['access_type'] == 'regular_period' ){
            return true;
        }
        return false;
    }

    /**
     * @param int
     * @return bool
     */
    public static function getMany( $search='', $offset=0, $limit=0, $orderBy='the_order', $orderType='ASC' )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT m.id as id, m.name as name, m.label as label, m.short_description as short_description, m.payment_type as payment_type, m.price as price,
                        m.status as status, m.the_order as the_order, m.created_at as created_at, COUNT( DISTINCT(um.user_id) ) as users_per_membership
                        FROM {$dbPrefix}ihc_memberships as m
                        LEFT JOIN {$dbPrefix}ihc_user_levels as um ON m.id=um.level_id
                        WHERE m.status=0 ";
        if ( $search !== '' ){
            $query .= $wpdb->prepare( " AND ( m.name LIKE %s OR m.label LIKE %s OR m.short_description LIKE %s ) ", '%' . $search . '%', '%' . $search . '%', '%' . $search . '%' );
        }
        $query .= " GROUP BY id ";
        if ( $orderBy !== '' && $orderType !== '' ){
            $query .= $wpdb->prepare( " ORDER BY %1s %1s ", $orderBy, $orderType);
        }
        if ( $limit > 0 ){
            $query .= $wpdb->prepare( " LIMIT %1s OFFSET %1s ", $limit, $offset );
        }
        $memberships = $wpdb->get_results( $query );
        if ( !$memberships ){
            return false;
        }
        foreach ( $memberships as $object ){
            $metas = self::getAllMetaForMembership( $object->id );
            $returnData[$object->id] = array_merge( (array)$object, $metas );
        }
        return $returnData;
    }

    /**
     * @param none
     * @return array
     */
    public static function countAsStats()
    {
        $stats = [
                    'free' => [
                                'onetime'      => 0,
                                'recurring'    => 0,
                                'date_range'   => 0,
                                'limited'      => 0,
                    ],
                    'paid' => [
                                'onetime'      => 0,
                                'recurring'    => 0,
                                'date_range'   => 0,
                                'limited'      => 0,
                    ],
        ];
        $allMemberships = self::getAll();
        if ( !$allMemberships ){
            return $stats;
        }

        foreach ( $allMemberships as $membership ){
            if ( !isset( $membership['access_type'] ) || !isset( $membership['payment_type'] ) ){
                continue;
            }
            switch ( $membership['access_type'] ){
                case 'unlimited':
                  $key = 'onetime';
                  break;
                case 'date_interval':
                  $key = 'date_range';
                  break;
                case 'limited':
                  $key = 'limited';
                  break;
                case 'regular_period':
                  $key = 'recurring';
                  break;
            }
            if ( $membership['payment_type'] === 'free' ){
                $stats['free'][$key]++;
            } else {
                $stats['paid'][$key]++;
            }
        }
        return $stats;
    }

}

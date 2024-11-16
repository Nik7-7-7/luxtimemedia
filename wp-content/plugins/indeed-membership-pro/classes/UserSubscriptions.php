<?php
namespace Indeed\Ihc;
/*
Add a Subscription to User:
\Indeed\Ihc\UserSubscriptions::assign( $uid, $lid );
Make User-Subscription completed
\Indeed\Ihc\UserSubscriptions::makeComplete( $uid, $lid );
Delete User Subscription:
\Indeed\Ihc\UserSubscriptions::deleteOne( $uid, $lid );
*/
class UserSubscriptions
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
     * Add Subscription to User.
     * @param int
     * @param int
     * @param array
     * @return bool
     */
    public static function assign( $uid=0, $lid=0, $args=[] )
    {
        global $wpdb;
        if ( !$uid || !$lid ){
            return false;
        }
        do_action('ihc_before_new_subscription_action', $uid, $lid, $args );

        $levelData = \Indeed\Ihc\Db\Memberships::getOne( $lid );
        if ( !$levelData ){
            return false;
        }
        $currentTime = indeed_get_unixtimestamp_with_timezone();
        //set start time
        if ( $levelData['access_type']=='date_interval' && !empty($levelData['access_interval_start']) ){
          $startTime = strtotime($levelData['access_interval_start']);
        } else {
          $startTime = $currentTime;
          if ( ihc_is_magic_feat_active('subscription_delay') ){
            $delayTime = \Ihc_Db::level_get_delay_time( $lid );
            if ( $delayTime !== false ){
              $startTime = $startTime + $delayTime;
            }
          }
        }

        $endTime = '0000-00-00 00:00:00';
        $updateTime = indeed_timestamp_to_date_without_timezone( $currentTime );
        $startTime = indeed_timestamp_to_date_without_timezone( $startTime );

        /// start v 10.10 - this is for renew subscription, when the level is not expired yet. it will not update the payment_due
        $oldData = \Indeed\Ihc\UserSubscriptions::getOne( $uid, $lid  );
        if ( isset( $oldData['expire_time'] ) && $oldData['expire_time'] !== ''  ){
            $temporaryEndTime = strtotime( $oldData['expire_time'] );
            if ( $temporaryEndTime > $currentTime ){
                $endTime = $oldData['expire_time'];
            }
        }
        if ( isset( $oldData['start_time'] ) && $oldData['start_time'] !== ''  ){
            $temporaryStartTime = strtotime( $oldData['start_time'] );
            if ( $temporaryStartTime > 0 ){
                $startTime = $oldData['start_time'];
            }
        }
        // end version 10.10

        if ( isset( $args['start_time'] ) ){
            $startTime = $args['start_time'];
        }
        if ( isset( $args['update_time'] ) ){
            $updateTime = $args['update_time'];
        }
        if ( isset( $args['expire_time'] ) ){
            $endTime = $args['expire_time'];
        }

        $id = self::getIdForUserSubscription( $uid, $lid );
        if ( $id  ){
          /// UPDATE
          $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_user_levels SET
              start_time=%s,
              update_time=%s,
              expire_time=%s,
              notification=0,
              status=1
              WHERE
              id=%d;", $startTime, $updateTime, $endTime, $id );

          $wpdb->query( $query );
          $args['subscription_id'] = $id;
        } else {
          /// INSERT
          $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ihc_user_levels VALUES(null, %d, %d, %s, %s, %s, 0, 1);",
                      $uid, $lid, $startTime, $updateTime, $endTime
          );
          $wpdb->query( $query );
          $args['subscription_id'] = $wpdb->insert_id;
        }

        do_action('ihc_new_subscription_action', $uid, $lid, $args );
        return true;
    }

    /**
     * Activate the Subscription for User.
     * @param int
     * @param int
     * @param bool
     * @param array [ 'start_time' => '', 'expire_time' => '', 'manual' => false, 'payment_gateway' => '' ]
     * @return bool
     */
    public static function makeComplete( $uid=0, $lid=0, $isTrial=false, $args=[] )
    {
        global $wpdb;
        if ( !$uid || !$lid ){
            return false;
        }

        if ( !self::getIdForUserSubscription( $uid, $lid ) ){
            return false;
        }

        $currentTime = indeed_get_unixtimestamp_with_timezone();
        $userSubscription = self::getOne( $uid, $lid );
        $firstTime = false;

        if ( $userSubscription && !empty( $userSubscription['expire_time'] ) ){
            $expireTime = strtotime( $userSubscription['expire_time'] );
            if ( $expireTime < 0 ){
                $firstTime = true;
            } else if ( $expireTime > $currentTime ) {
                $currentTime = $expireTime;
            }
        }
        $args['is_trial'] = $isTrial;
        do_action( 'ihc_action_before_subscription_activated', $uid, $lid, $firstTime, $args );

        if ( isset( $args['expire_time'] ) && $args['expire_time'] != '' ){
            $endTime = $args['expire_time'];
        } else {
            if ( $isTrial ){
                $endTime = \Indeed\Ihc\Db\Memberships::getEndTimeForTrial( $lid, $currentTime );
            } else {
                $endTime = \Indeed\Ihc\Db\Memberships::getEndTime( $lid, $currentTime );
            }
            $endTime = indeed_timestamp_to_date_without_timezone( $endTime );
        }

        $updateTime = indeed_timestamp_to_date_without_timezone( indeed_get_unixtimestamp_with_timezone() );

        $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_user_levels
                                    SET
                                    update_time=%s,
                                    expire_time=%s
        ", $updateTime, $endTime );
        if ( isset( $args['start_time'] ) && $args['start_time'] != '' ){
            $query .= $wpdb->prepare( " , start_time=%s ", $args['start_time'] );
        }
        $query .= $wpdb->prepare( "
                                    WHERE
                                    user_id=%d
                                    AND
                                    level_id=%d", $uid, $lid );
        $result = $wpdb->query( $query );


        $args['expire_time'] = $endTime;
        if ( $firstTime ){
            self::SubscriptionOnFirstTime( $uid, $lid, $args );
        }else{
            self::SubscriptionRenew( $uid, $lid, $args );
        }

        $currentTime = indeed_get_unixtimestamp_with_timezone();


        if ( isset( $userSubscription['expire_time'] ) && strtotime( $userSubscription['expire_time'] ) < $currentTime && strtotime( $endTime ) > $currentTime ){
            do_action( 'ihc_action_after_subscription_activated', $uid, $lid, $firstTime, $args );
        }

        return $result;
    }

    /**
     * @param int
     * @param int
     * @param string
     * @param array
     * @return bool
     */
    public static function updateSubscriptionTime( $uid=0, $lid=0, $startTime='', $endTime='', $args=[] )
    {
        global $wpdb;
        if ( !$uid || !$lid ){
            return false;
        }
        $oldData = \Indeed\Ihc\UserSubscriptions::getOne( $uid, $lid  );
        if ( !$oldData ){
            // create
            return false;
        }
        $firstTime = false;
        if ( !empty( $oldData['expire_time'] ) ){
            $expireTime = strtotime( $oldData['expire_time'] );
            if ( $expireTime < 0 ){
                $firstTime = true;
            }
        }
        if ( $startTime != '' ){
            $array[] = $wpdb->prepare( "start_time=%s", $startTime );
        }
        if ( $endTime != '' ){
            $array[] = $wpdb->prepare( "expire_time=%s", $endTime );
        }

        if ( empty( $array ) ){
            return false;
        }
        $array[] = $wpdb->prepare( "update_time=%s", indeed_timestamp_to_date_without_timezone() );
        $query = "UPDATE {$wpdb->prefix}ihc_user_levels SET " . implode( ',', $array );
        $query .= $wpdb->prepare( " WHERE user_id=%d AND level_id=%d ", $uid, $lid );
        $result = $wpdb->query( $query );
        $currentTime = indeed_get_unixtimestamp_with_timezone();

        if ( $firstTime ){
            self::SubscriptionOnFirstTime( $uid, $lid );
        }else{
            self::SubscriptionRenew( $uid, $lid, $args );
        }

        if ( isset( $oldData['expire_time'] ) && strtotime( $oldData['expire_time'] ) < $currentTime && strtotime( $endTime ) > $currentTime ){
            /// activate level
            do_action( 'ihc_action_after_subscription_activated', $uid, $lid, $firstTime, $args );
            // @description Action that run after a subscription(level) is activated. @param user id (integer), level id (integer), flag if it's first time activated (boolean).
        }
        return $result;
    }

    /**
     * @param int
     * @param int
     * @return int
     */
    public static function getIdForUserSubscription( $uid=0, $lid=0 )
    {
        global $wpdb;
        if ( !$uid || !$lid ){
            return false;
        }
        $query = $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}ihc_user_levels
                                    WHERE user_id=%d AND level_id=%d ORDER BY id DESC;",
        $uid, $lid );
        return $wpdb->get_var( $query );
    }

    /**
     * @param int
     * @param int
     * @return array
     */
    public static function getOne( $uid=0, $lid=0 )
    {
        global $wpdb;
        if ( !$uid || !$lid ){
            return false;
        }
        $query = $wpdb->prepare( "SELECT id, user_id, level_id, start_time, update_time, expire_time, notification, status
                                    FROM {$wpdb->prefix}ihc_user_levels
                                    WHERE user_id=%d AND level_id=%d ORDER BY id DESC;",
        $uid, $lid );
        $result = $wpdb->get_row( $query );
        if ( $result === null ){
            return false;
        }
        return (array)$result;
    }

    /**
     * @param int
     * @return array
     */
    public static function getOneById( $id=0 )
    {
        global $wpdb;
        if ( !$id ){
            return false;
        }
        $query = $wpdb->prepare( "SELECT id, user_id, level_id, start_time, update_time, expire_time, notification, status
                                    FROM {$wpdb->prefix}ihc_user_levels
                                    WHERE id=%d;", $id );
        $result = $wpdb->get_row( $query );
        if ( $result === null ){
            return false;
        }
        return (array)$result;
    }

    /**
     * @param int
     * @return mixed
     */
    public static function getAllForUser( $uid=0, $check_expire=false )
    {
        global $wpdb;
        if ( !$uid ){
            return false;
        }
        $array = [];
   			$levels = \Indeed\Ihc\Db\Memberships::getAll();
   			$query = $wpdb->prepare("SELECT id,user_id,level_id,start_time,update_time,expire_time,notification,status FROM {$wpdb->prefix}ihc_user_levels
                                      WHERE user_id=%d", $uid);
   			$data = $wpdb->get_results( $query );
   			if ( !$data ){
            return [];
   			}
        foreach ($data as $object){
           $temp = (array)$object;
           if (isset($levels[$object->level_id]['label'])){
             $temp['label'] = $levels[$object->level_id]['label'];
           } else {
             continue;
           }
           $temp['level_slug'] = $levels[$object->level_id]['name'];
           if (!empty($levels[$object->level_id]['badge_image_url'])){
             $temp['badge_image_url'] = $levels[$object->level_id]['badge_image_url'];
           } else {
             $temp['badge_image_url'] = '';
           }

           if ( self::isActive( $uid, $object->level_id ) ){
             $temp['is_expired'] = FALSE;
           } else {
             $temp['is_expired'] = TRUE;
             if ($check_expire){
               continue;
             }
           }
           $array[$object->level_id] = $temp;
        }
   		  return $array;
    }


    /**
     * @param int
     * @param int
     * @return bool
     */
    public static function isActive($uid=0, $lid=0)
    {
      global $wpdb;
      $grace_period = \Indeed\Ihc\UserSubscriptions::getGracePeriod( $uid, $lid );

      $q = $wpdb->prepare("SELECT expire_time, start_time FROM {$wpdb->prefix}ihc_user_levels WHERE user_id=%d AND level_id=%d;", $uid, $lid);
      $data = $wpdb->get_row($q);
      $current_time = indeed_get_unixtimestamp_with_timezone();

      if (!empty($data->start_time)){
        $start_time = strtotime($data->start_time);
        if ($current_time<$start_time){
          //it's not available yet
          return FALSE;
        }
      }
      if (!empty($data->expire_time)){
        $expire_time = strtotime($data->expire_time) + ((int)$grace_period * 24 * 60 *60);
        if ($current_time>$expire_time){
          //it's expired
          return FALSE;
        }
      }
      return TRUE;
    }

    /**
     * @param int
     * @param int
     * @return string
     */
    public static function getExpireTimeForSubscription( $uid=0, $lid=0 )
    {
        global $wpdb;
        if ( !$uid || !$lid ){
            return '';
        }
        $query = $wpdb->prepare( "SELECT expire_time FROM {$wpdb->prefix}ihc_user_levels
                                      WHERE
                                      user_id=%d
                                      AND
                                      level_id=%d;",
        $uid, $lid
        );
        return $wpdb->get_var( $query );
    }

    /**
     * @param int
     * @param int
     * @return string
     */
    public static function getStartAndExpireForSubscription( $uid=0, $lid=0 )
    {
        global $wpdb;
        if ( !$uid || !$lid ){
            return '';
        }
        $query = $wpdb->prepare( "SELECT start_time,expire_time FROM {$wpdb->prefix}ihc_user_levels
                                      WHERE
                                      user_id=%d
                                      AND
                                      level_id=%d;",
        $uid, $lid
        );
        $data = $wpdb->get_row( $query );
        return [
          'start_time'  => (isset($data->start_time)) ? $data->start_time : false,
        	'expire_time' => (isset($data->expire_time)) ? $data->expire_time : false,
        ];
    }

    /**
     * @param int
     * @param int
     * @return bool
     */
    public static function deleteOne( $uid=0, $lid=0 )
    {
        global $wpdb;
        if ( !$uid || !$lid ){
            return false;
        }
        $id = self::getIdForUserSubscription( $uid, $lid );
        if ( !$id ){
            return false;
        }
        do_action( 'ihc_action_before_delete_user_subscription', $uid, $lid );
        $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ihc_user_levels WHERE id=%d;", $id );
        $result = $wpdb->query( $query );
        do_action( 'ihc_action_after_subscription_delete', $uid, $lid );
        return $result;
    }

    /**
     * @param int
     * @return bool
     */
    public static function deleteAllForUser( $uid=0 )
    {
        global $wpdb;
        if ( !$uid ){
            return false;
        }
        do_action( 'ihc_action_before_delete_all_user_subscription', $uid );
        $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ihc_user_levels WHERE user_id=%d;", $uid );
        do_action( 'ihc_action_afters_delete_all_user_subscription', $uid );
        return $wpdb->query( $query );
    }

    /**
     * @param int
     * @param int
     * @param array
     * @return array
     */
    public static function SubscriptionOnFirstTime( $uid=0, $lid=0, $args=[]  )
    {
        do_action( 'ihc_action_after_subscription_first_time_activated', $uid, $lid, $args );
    }

    /**
     * @param int
     * @param int
     * @param array
     * @return array
     */
    public static function SubscriptionRenew( $uid=0, $lid=0, $args=[]  )
    {
        do_action( 'ihc_action_after_subscription_renew_activated', $uid, $lid, $args );
    }

    /**
     * get all subscriptions that expired in the last hour
     * @param int ( Subscription id)
     * @return array
     */
  	public static function getMembersWithExpiredSubscription( $id=0, $lastRunTime=0, $timeDiff=3600 )
  	{
  			global $wpdb;

        $currentTime = indeed_get_unixtimestamp_with_timezone(); // current time
        $startTime = $lastRunTime;
        if ( $startTime === 0 ){
            $startTime = $currentTime - 24 * 3600; // current time - one day
        }
        $endTime = $currentTime;

        $gracePeriod = \Indeed\Ihc\Db\Memberships::getMembershipGracePeriod( $id );
  			if ( $gracePeriod !== false && $gracePeriod != '' ){
            $timeDiff = $gracePeriod * 24 * 60 * 60;
  			}

        $startTime = $startTime - $timeDiff;
        $endTime = $endTime - $timeDiff;

        if ( $id ){
          $query = $wpdb->prepare( "SELECT id,user_id,level_id,start_time,update_time,expire_time,notification,status
                                        FROM {$wpdb->prefix}ihc_user_levels
                                        WHERE
                                        UNIX_TIMESTAMP( expire_time ) > %s
                                        AND UNIX_TIMESTAMP( expire_time ) <= %s
                                        AND level_id=%d
          ;", $startTime, $endTime, $id );
        } else {
          $query = $wpdb->prepare( "SELECT id,user_id,level_id,start_time,update_time,expire_time,notification,status
                                          FROM {$wpdb->prefix}ihc_user_levels
                                          WHERE
                                          UNIX_TIMESTAMP( expire_time ) > %s
                                          AND UNIX_TIMESTAMP( expire_time ) <= %s
          ;", $startTime, $endTime );
        }
        $objects = $wpdb->get_results( $query );

  			if ( !$objects ){
  					return [];
  			}
  			foreach ( $objects as $object ){
  					$data[] = (array)$object;
  			}
  			return $data;
  	}

    /**
     * @param int
     * @return array
     */
    public static function getMembersThatEnterInGracePeriod( $lid=0, $defaultGracePeriod=0, $startTime=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT a.user_id as uid, a.level_id as lid,
        																	IFNULL( b.meta_value, %d ) as grace_period,
        																	a.expire_time as expire_time
        																	FROM {$wpdb->prefix}ihc_user_levels a
        																	LEFT JOIN {$wpdb->prefix}ihc_user_subscriptions_meta b ON a.id=b.subscription_id
        																	WHERE
        																	b.meta_key='grace_period'
                                          AND
                                          a.level_id = %d
        																	HAVING
        																	UNIX_TIMESTAMP( expire_time ) <= UNIX_TIMESTAMP() + ( grace_period * 24*60*60 )
        																	AND
        																	UNIX_TIMESTAMP( expire_time ) >= %d
        ", $defaultGracePeriod , $lid, $startTime );
        $objects = $wpdb->get_results( $query );

        if ( !$objects ){
            return [];
        }
        foreach ( $objects as $object ){
            $data[] = (array)$object;
        }
        return $data;
    }

    /**
     * @param int ( id of membership )
     * @param int ( number of seconds before expire )
     * @return array
     */
    public static function getMembersThatWillExpireSoon( $id=0, $currentTime=0, $timeDiff=3600 )
    {
        global $wpdb;

        $gracePeriod = \Indeed\Ihc\Db\Memberships::getMembershipGracePeriod( $id );
        if ( $gracePeriod == false ){
            return false;
        }
        $lowerThan = $currentTime;
        if ( $lowerThan === 0 ){
            $lowerThan = indeed_get_unixtimestamp_with_timezone(); // current time
        }
        $biggerThan = $lowerThan - $timeDiff; // current time - timeBeforeExpire

        if ( $gracePeriod !== false && $gracePeriod != '' ){
            $gracePeriodValue = $gracePeriod * 24 * 60 * 60;
            $lowerThan = $lowerThan + $gracePeriodValue;
            $biggerThan = $biggerThan + $gracePeriodValue;
        }
        $query = $wpdb->prepare( "SELECT id,user_id,level_id,start_time,update_time,expire_time,notification,status
                                      FROM {$wpdb->prefix}ihc_user_levels
                                      WHERE
                                      UNIX_TIMESTAMP( expire_time ) <= %s
                                      AND UNIX_TIMESTAMP( expire_time ) >= %s
                                      AND level_id=%d
        ;", $lowerThan, $biggerThan, $id );
        $objects = $wpdb->get_results( $query );

        if ( !$objects ){
            return [];
        }
        foreach ( $objects as $object ){
            $data[] = (array)$object;
        }
        return $data;
    }

    /**
     * @param int
     * @param bool
     * @return string
     */
    public static function getAllForUserAsList( $uid=0, $checkExpire=false )
    {
        global $wpdb;
        if ( !$uid ){
            return '';
        }
        $query = $wpdb->prepare( "SELECT level_id FROM {$wpdb->prefix}ihc_user_levels WHERE user_id=%d", $uid );
        $data = $wpdb->get_results( $query );
        if ( !$data ){
            return '';
        }
        $levels = [];
        foreach ( $data as $object ){
            if ( $checkExpire && !self::isActive( $uid, $object->level_id ) ){
                continue;
            }
            $levels[] = $object->level_id;
        }
        if ( $levels ){
            return implode( ',', $levels );
        }
        return '';
    }

    /**
     * @param int
     * @param int
     * @return boolean
     */
    public static function userHasSubscription($uid=0, $lid=0)
    {
         global $wpdb;
         if ( !$uid || $lid === false ){
            return false;
         }
         $q = $wpdb->prepare("SELECT id,user_id,level_id,start_time,update_time,expire_time,notification,status FROM {$wpdb->prefix}ihc_user_levels
                                  WHERE user_id=%d AND level_id=%d;", $uid, $lid);
         $data = $wpdb->get_row($q);
         if ($data && isset($data->start_time)){
           return true;
         }
         return false;
    }

    /**
     * @param int ( Subscription id)
     * @param bool
     * @return array
     */
    public static function getSubscriptionsUsersList( $lid=-1, $only_active=false )
    {
       global $wpdb;
       $data = [];
       if ( $lid < -1 ){
          return $data;
       }
       $table = $wpdb->prefix . 'ihc_user_levels';
       $q = $wpdb->prepare("SELECT user_id FROM $table WHERE level_id=%d", $lid);
       $data = $wpdb->get_results($q);
       if ( !$data ){
          return $data;
       }
       foreach ($data as $object){
         $do_it = TRUE;
         if ($only_active){
           /// only active users
           if ( !self::isActive( $object->user_id, $lid ) ){
             $do_it = FALSE;
           }
         }
         if ($do_it){
           $array['username'] = \Ihc_Db::get_username_by_wpuid($object->user_id);
           $array['user_id'] = $object->user_id;
           $data[] = array(
                   'username' => \Ihc_Db::get_username_by_wpuid($object->user_id),
                   'user_id' => $object->user_id,
           );
         }
       }
       return $data;
    }

    /**
     * Status can be :
                               0 - Cancelled
                               1 - Active
                               2 - Expired
                               3 - Hold
                               4 - Pause
                               5 - Cancellation pending
     * @param int
     * @param int
     * @return bool
     */
    public static function updateStatus( $uid=0, $lid=0, $newStatus=0 )
    {
        global $wpdb;
  			if ( !$uid || $lid < 0 ){
  					return false;
  			}
  			$queryString = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_user_levels SET status=%d
  																				WHERE user_id=%d AND level_id=%d;", $newStatus, $uid, $lid );
  			return $wpdb->query( $queryString );
    }

    /**
     * Status can be :
                               0 - Cancelled
                               1 - Active
                               2 - Expired
                               3 - Hold
                               4 - Pause
                               5 - Cancellation pending
     * @param int
     * @return bool
     */
    public static function updateStatusBySubscriptionId( $id=0, $newStatus=0 )
    {
        global $wpdb;
        if ( !$id ){
            return false;
        }
        $queryString = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_user_levels SET status=%d
                                          WHERE id=%d;", $newStatus, $id);
        return $wpdb->query( $queryString );
    }

    /**
     * @param int
     * @param int ( Subscription id)
     * @return string
     */
    public static function getStatusAsString( $uid=0, $lid=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT expire_time,start_time,status
                                    FROM {$wpdb->prefix}ihc_user_levels WHERE user_id=%d AND level_id=%d ", $uid, $lid );
        $data = $wpdb->get_row( $query );
        if ( !$data ){
            return '';
        }
        if ($data->status==0){
          $status =  esc_html__('Canceled', 'ihc');
        } else {
          $status = esc_html__('Active', 'ihc');
          $grace_period = self::getGracePeriod( $uid, $lid );

          $expire_time_after_grace = strtotime($data->expire_time) + (int)$grace_period * 24 * 60 * 60;
          if ($expire_time_after_grace<0){
            $status = esc_html__("Hold", 'ihc');
          } else if (indeed_get_unixtimestamp_with_timezone()>$expire_time_after_grace){
            $status = esc_html__("Expired", 'ihc');
          } else if (strtotime($data->start_time)>indeed_get_unixtimestamp_with_timezone()){
            $status = esc_html__("Inactive", 'ihc');
          }
        }
        return $status;
    }

    /**
     * @param int
     * @param int
     * @param int
     * @return array
     */
    public static function getStatus( $uid=0, $lid=0, $subscriptionId=0 )
    {
        global $wpdb;
        if ( !$uid && !$lid && $subscriptionId ){
            return false;
        }
        if ( $subscriptionId ){
          $query = $wpdb->prepare( "SELECT id,expire_time,start_time,status
                                      FROM {$wpdb->prefix}ihc_user_levels WHERE id=%d", $subscriptionId );
        } else {
          $query = $wpdb->prepare( "SELECT id,expire_time,start_time,status
                                      FROM {$wpdb->prefix}ihc_user_levels WHERE user_id=%d AND level_id=%d ", $uid, $lid );
        }

        $data = $wpdb->get_row( $query );
        if ( !$data ){
            return false;
        }

        switch ( $data->status ){
            case 0:
              $returnData = [
                          'status'              => 0,
                          'status_as_string'    => 'Cancelled',
                          'label'               => esc_html__( 'Cancelled', 'ihc' ),
              ];
              break;
            case 1:
              $returnData = [
                          'status'              => 1,
                          'status_as_string'    => 'Active',
                          'label'               => esc_html__( 'Active', 'ihc' ),
              ];

              // let's be sure it's not an older version
              $expireTime = $data->expire_time;
              $currentTime = indeed_get_unixtimestamp_with_timezone();

              if ( $expireTime == '0000-00-00 00:00:00' ){
                  // it's on hold
                  $returnData = [
                              'status'              => 3,
                              'status_as_string'    => 'Hold',
                              'label'               => esc_html__( 'Hold', 'ihc' ),
                  ];
              } else if ( strtotime( $expireTime ) < $currentTime ){
                  // it's expired
                  $returnData = [
                              'status'              => 2,
                              'status_as_string'    => 'Expired',
                              'label'               => esc_html__( 'Expired', 'ihc' ),
                  ];
              }
              break;
            case 2:
              $returnData = [
                          'status'              => 2,
                          'status_as_string'    => 'Expired',
                          'label'               => esc_html__( 'Expired', 'ihc' ),
              ];
              break;
            case 3:
              $returnData = [
                          'status'              => 3,
                          'status_as_string'    => 'Hold',
                          'label'               => esc_html__( 'Hold', 'ihc' ),
              ];
              break;
            case 4:
              $returnData = [
                        'status'              => 4,
                        'status_as_string'    => 'Paused',
                        'label'               => esc_html__( 'Paused', 'ihc' ),
              ];
              break;
            case 5:
              $returnData = [
                        'status'              => 5,
                        'status_as_string'    => 'Cancellation pending',
                        'label'               => esc_html__( 'Cancellation pending', 'ihc' ),
              ];
              break;
        }

        $returnData['id'] = $data->id;
        return $returnData;
    }

    /**
     * @param int
     * @param int
     * @param int
     * @return string
     */
    public static function getAccessTypeAsString( $uid=0, $lid=0, $subscriptionId=0 )
    {
        $membershipData = \Indeed\Ihc\Db\Memberships::getOne( $lid );
        $subscriptionMetas = \Indeed\Ihc\Db\UserSubscriptionsMeta::getAllForSubscription( $subscriptionId );
        $accessType = ihcGetValueFromTwoPossibleArrays( $subscriptionMetas, $membershipData, 'access_type' );
        $string = '';
        switch ( $accessType ){
            case 'regular_period':
              $accessRegularTimeType = ihcGetValueFromTwoPossibleArrays( $subscriptionMetas, $membershipData, 'access_regular_time_type' );
              $string .= esc_html__( 'Subscription - ', 'ihc' );
              switch ( $accessRegularTimeType ){
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
     * @param int ( Subscription id)
     * @return bool
     */
    public static function isOnHold( $uid=0, $lid=0 )
    {
        global $wpdb;
        $isOnHold = false;
        $query = $wpdb->prepare( "SELECT expire_time,status
                                    FROM {$wpdb->prefix}ihc_user_levels WHERE user_id=%d AND level_id=%d ", $uid, $lid );
        $data = $wpdb->get_row( $query );
        if ( !$data ){
            return $isOnHold;
        }
        if ( $data->status==0 ){
          return $isOnHold;
        } else {
          $grace_period = \Indeed\Ihc\Db\Memberships::getMembershipGracePeriod( $lid );
          if ( $grace_period !== false && $grace_period != '' ){
            $expire_time_after_grace = strtotime($data->expire_time) + (int)$grace_period * 24 * 60 * 60;
          }else{
            $expire_time_after_grace = strtotime($data->expire_time);
          }

          if ($expire_time_after_grace<0){
            return true;
          }
        }
        return $isOnHold;
    }

    /**
     * @param int
     * @param int ( Subscription id)
     * @return bool
     */
    public static function isFirstTime( $uid=0, $lid=0 )
    {
        global $wpdb;
        $current_time = indeed_get_unixtimestamp_with_timezone();
        $q = $wpdb->prepare("SELECT expire_time FROM {$wpdb->prefix}ihc_user_levels WHERE user_id=%d AND level_id=%d ", $uid, $lid);
        $data = $wpdb->get_row($q);
        if ($data && !empty($data->expire_time)){
          $time = strtotime($data->expire_time);
          if ($time<0){
            return true;
          }
          return false;
        }
        return true;
    }

    /**
     * @param string
     * @param string
     * @param string
     * @return object
     */
    public static function selectByExpireTime( $lowerThan='', $graterThan='', $now='' )
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT id,user_id,level_id,start_time,update_time,expire_time,notification,status
                        FROM {$wpdb->prefix}ihc_user_levels
                        WHERE
                        UNIX_TIMESTAMP(expire_time) <= %d
                        AND UNIX_TIMESTAMP(expire_time) > %d ", $lowerThan, $graterThan );
        if ( $now != '' ){
            $query .= $wpdb->prepare(" AND UNIX_TIMESTAMP(expire_time) > %d ", $now );
        }
        return $wpdb->get_results( $query );
    }

    /**
     * @param none
     * @return array
     */
    public static function getTopSubscription()
    {
        global $wpdb;
        $return_value = '';
        $levels_data = \Indeed\Ihc\Db\Memberships::getAll();
        $level_arr = array();
        if ($levels_data){
          $table = $wpdb->prefix . 'ihc_user_levels';
          $table_u = $wpdb->base_prefix . 'users';
          foreach ($levels_data as $lid=>$level_data){
            $query = $wpdb->prepare( "SELECT COUNT(a.id) as num FROM $table a INNER JOIN $table_u u ON a.user_id=u.ID WHERE a.level_id=%d;", $lid );
            $data = $wpdb->get_row( $query );
            $level_arr[$lid] = isset($data->num) ? $data->num : 0;
          }
          asort($level_arr);
          end($level_arr);
          $return_value = key($level_arr);
          $return_value = $levels_data[$return_value]['name'];
        }
        return $return_value;
    }

    /**
     * @param none
     * @return array
     */
    public static function getCountsForeachSubscription()
    {
        global $wpdb;
      	$levels_data = \Indeed\Ihc\Db\Memberships::getAll();
      	$level_arr = array();

      	if ( !$levels_data ){
            return $level_arr;
      	}

        $table = $wpdb->prefix . 'ihc_user_levels';
        $table_u = $wpdb->base_prefix . 'users';

        foreach ($levels_data as $lid=>$level_data){
            $query = $wpdb->prepare( "SELECT COUNT(a.id) as num FROM $table a INNER JOIN $table_u u ON a.user_id=u.ID WHERE a.level_id=%d;", $lid );
        		$data = $wpdb->get_row( $query );
        		$level_arr[$level_data['label']] = isset($data->num) ? $data->num : 0;
        }
      	return $level_arr;
    }

    /**
     * @param none
     * @return array
     */
    public static function getCountsMembersPerSubscription()
    {
        global $wpdb;

      	$levels_data = \Indeed\Ihc\Db\Memberships::getAll();
      	$level_arr = array();

      	if ($levels_data){
      		$table = $wpdb->prefix . 'ihc_user_levels';
      		$table_u = $wpdb->base_prefix . 'users';
      		foreach ($levels_data as $lid=>$level_data){
            $query = $wpdb->prepare( "SELECT COUNT(a.id) as num FROM $table a INNER JOIN $table_u u ON a.user_id=u.ID WHERE a.level_id=%d;", $lid );
      			$data = $wpdb->get_row( $query );
      			$level_arr[$lid] = isset($data->num) ? $data->num : 0;
      		}
      	}
      	return $level_arr;
    }

    /**
     * @param int
     * @return bool
     */
    public static function deleteAllForSubscription( $id=0 )
    {
        global $wpdb;
        if ( !$id ){
            return false;
        }
        $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ihc_user_levels WHERE level_id=%d ;", $id );
        return $wpdb->query( $query );
    }

    /**
     * @param string
     * @param int
     * @return object
     */
    public static function searchMembersForDripContent($level_ids='', $x_days_after_subscription=0)
    {
  		global $wpdb;
  		$users_table = $wpdb->base_prefix . 'users';
  		if (strpos($level_ids, '-1')!==FALSE){
  			/// all users
  			$q = "SELECT ID as uid, user_email FROM $users_table;";
  			$data = $wpdb->get_results($q);
  			return $data;
  		} else {
  			/// only for some levels
  			$user_level_table = $wpdb->prefix . 'ihc_user_levels';
  			$q = "
  				SELECT a.user_id as uid, a.level_id as lid, b.user_email as user_email
  					FROM $user_level_table a
  					INNER JOIN $users_table b
  					ON a.user_id=b.ID
  			";
  			$q .= "	WHERE 1=1 ";
  			$q .= " AND UNIX_TIMESTAMP(a.expire_time)>UNIX_TIMESTAMP(NOW()) ";
  			$levels = explode(',', $level_ids);
  			if ($levels){
  				$q .= " AND (";
  				foreach ($levels as $lid){
  					if (!empty($or)){
  						$q .= " OR ";
  					}
  					$q .= $wpdb->prepare(" a.level_id=%d ", $lid);
  					$or = TRUE;
  				}
  				$q .= " ) ";
  			}
  			if ($x_days_after_subscription>0){
  				$date = date('Y-m-d', strtotime("-$x_days_after_subscription days"));
  				$start = $date . ' 00:00:00';
  				$end = $date . ' 23:59:59';
  				$q .= $wpdb->prepare(" AND
  						( UNIX_TIMESTAMP(a.start_time)>UNIX_TIMESTAMP( %s ) AND UNIX_TIMESTAMP(a.start_time)<UNIX_TIMESTAMP(%s) )
  				", $start, $end );
  			}
  			$user_data = $wpdb->get_results($q);

  			return $user_data;
  		}
  		return [];
  	}

    /**
     * @param int ( timestamp )
     * @param int ( timestamp )
     * @return array
     */
    public static function getMemberWithExpiredTrial( $startTime='', $endTime='' )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT a.user_id as uid, a.level_id as lid
        																	FROM {$wpdb->prefix}ihc_user_levels a
        																	INNER JOIN {$wpdb->prefix}ihc_user_subscriptions_meta b ON a.id=b.subscription_id
        																	WHERE
        																	b.meta_key='expire_trial_time'
        																	AND
        																	UNIX_TIMESTAMP( b.meta_value ) >= %d
        																	AND
        																	UNIX_TIMESTAMP( b.meta_value ) <= %d
        ", $startTime, $endTime );
        $data = $wpdb->get_results( $query );
        if ( !$data ){
            return false;
        }
        $array = [];
        foreach ( $data as $object ){
            $array[] = (array)$object;
        }
        return $array;
    }

    /**
     * @param int
     * @param int
     * @return array
     */
    public static function getMembersWithPaymentDue( $startTime=0, $endTime=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT a.user_id as uid, a.level_id as lid
                                          FROM {$wpdb->prefix}ihc_user_levels a
                                          INNER JOIN {$wpdb->prefix}ihc_user_subscriptions_meta b ON a.id=b.subscription_id
                                          WHERE
                                          b.meta_key='payment_due_time'
                                          AND
                                          UNIX_TIMESTAMP( b.meta_value ) >= %d
                                          AND
                                          UNIX_TIMESTAMP( b.meta_value ) <= %d
        ", $startTime, $endTime );
        $data = $wpdb->get_results( $query );
        if ( !$data ){
            return false;
        }
        $array = [];
        foreach ( $data as $object ){
            $array[] = (array)$object;
        }
        return $array;
    }

    public static function getGracePeriod( $uid=0, $lid=0 )
    {
        $gracePeriod = false;
        $subscriptionId = self::getIdForUserSubscription( $uid, $lid );
        if ( $subscriptionId ){
            $gracePeriod = \Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, 'grace_period' );
        }
        if ( $gracePeriod === false || $gracePeriod == '' ){
            $gracePeriod = \Indeed\Ihc\Db\Memberships::getMembershipGracePeriod( $lid );
        }
      	if ( $gracePeriod === false || $gracePeriod == ''){
      		  $gracePeriod = 0;
      	}
        return $gracePeriod;
    }

    /**
     * @param none
     * @return int
     */
    public static function getCount()
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT COUNT( id ) FROM {$wpdb->prefix}ihc_user_levels
                                      WHERE
                                      IFNULL( UNIX_TIMESTAMP( expire_time ), 0 ) > 0
                                      AND
                                      IFNULL( UNIX_TIMESTAMP( update_time ), 0 ) > 0;";
        return $wpdb->get_var( $query );
    }

    /**
     * @param int
     * @return int
     */
    public static function countInInterval( $start=0, $end=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT COUNT( id ) FROM {$wpdb->prefix}ihc_user_levels
                                      WHERE
                                      IFNULL( UNIX_TIMESTAMP( update_time ), 0 ) > %d
                                      AND
                                      IFNULL( UNIX_TIMESTAMP( update_time ), 0 ) < %d
                                      AND
                                      IFNULL( UNIX_TIMESTAMP( expire_time ), 0 ) > 0;", $start, $end );
        return $wpdb->get_var( $query );
    }

    /**
     * @param array
     * @return boolean
     */
    public static function updateUserSubscriptionExpireManually( $data=[] )
    {
        if ( !isset( $data['expire_levels'] ) || !is_array( $data['expire_levels'] ) ){
            return;
        }

        foreach ( $data['expire_levels'] as $lid => $expire ){
            if ( $expire == '' ){
                $expire = '0000-00-00 00:00:00';
            }
            $start = (isset( $data['start_time_levels'][$lid] ) ) ? $data['start_time_levels'][$lid] : '';
            $args = [
                      'expire_time'					=> $expire,
                      'start_time'					=> $start,
                      'manual'							=> true,
            ];
            self::makeComplete( $data['uid'], $lid, false, $args );
        }
    }

    /**
     * @param int
     * @param string
     * @return none
     */
    public static function assignSubscriptionToUserManually( $uid=0, $levels=[] )
    {
        if ( !$uid || empty( $levels ) ){
            return false;
        }
        $args['manual'] = true;

        foreach ( $levels as $lid ){
            if ( self::getOne( $uid, $lid )
                && strtotime( self::getExpireTimeForSubscription( $uid, $lid ) ) > current_time( 'timestamp' ) ){
                continue;
            }
            self::assign( $uid, $lid );
            self::makeComplete( $uid, $lid, false, $args );
        }
    }

    /**
     * @param int
     * @param string
     * @param array
     * @return array
     */
    public static function getLastForUid( $uid=0, $exclude='', $levelsIn=[] )
    {
        global $wpdb;
        if ( !$uid ){
            return false;
        }
        $query = $wpdb->prepare( "SELECT level_id
                                    FROM {$wpdb->prefix}ihc_user_levels
                                    WHERE user_id=%d AND status=1 ", $uid );
        if ( $exclude !== '' ){
            $query .= $wpdb->prepare( " AND level_id!=%d ", $exclude );
        }
        if ( $levelsIn !== [] ){
            $levelsIn = implode( ',', $levelsIn );
            $levelsIn = sanitize_text_field( $levelsIn );
            $query .= " AND level_id IN (" . $levelsIn . ") ";
        }
        $query .= " ORDER BY id DESC LIMIT 1;";
        $result = $wpdb->get_var( $query );
        if ( $result === null ){
            return false;
        }
        return $result;
    }

}

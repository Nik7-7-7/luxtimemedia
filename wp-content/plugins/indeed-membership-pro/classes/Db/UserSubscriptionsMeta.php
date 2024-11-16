<?php
namespace Indeed\Ihc\Db;

/*
Save:
\Indeed\Ihc\Db\UserSubscriptionsMeta::save( $subscriptionId, $metaKey, $metaValue );
Get meta for subscription:
\Indeed\Ihc\Db\UserSubscriptionsMeta::getOne( $subscriptionId, $metaKey );
Get all metas for subscription:
\Indeed\Ihc\Db\UserSubscriptionsMeta::getAllForSubscription( $subscriptionId );
Delete one meta:
\Indeed\Ihc\Db\UserSubscriptionsMeta::deleteOne( $subscriptionId, $metaKey );
Delete all metas for subscription:
\Indeed\Ihc\Db\UserSubscriptionsMeta::deleteAllForSubscription( $subscriptionId );
*/

class UserSubscriptionsMeta
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
    public static function createTable( $engine='MyISAM' )
    {
        global $wpdb;
        $dbPrefix = self::$tablePrefix == '' ? $wpdb->prefix : self::$tablePrefix;
        $engine = \Ihc_Db::tableEngine();

        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "show tables like '{$dbPrefix}ihc_user_subscriptions_meta'";
        if ($wpdb->get_var( $query ) != $dbPrefix . 'ihc_user_subscriptions_meta' ){
            require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
            $sql = "CREATE TABLE {$dbPrefix}ihc_user_subscriptions_meta (
                                  id BIGINT(20) NOT NULL AUTO_INCREMENT,
                                  subscription_id BIGINT(20) NOT NULL,
                                  meta_key VARCHAR(300) NOT NULL,
                                  meta_value TEXT,
                                  PRIMARY KEY (`id`),
                                  INDEX idx_ihc_user_subscriptions_meta_subscription_id (`subscription_id`)
            )
            ENGINE=$engine
						CHARACTER SET utf8 COLLATE utf8_general_ci;
            ";
            dbDelta ( $sql );
        }
    }

    /**
     * @param int
     * @param string
     * @param string
     * @return bool
     */
    public static function save( $subscriptionId=0, $metaKey='', $metaValue='' )
    {
        global $wpdb;
        if ( !$subscriptionId || $metaKey == '' ){
            return false;
        }
        if ( self::getOne( $subscriptionId, $metaKey ) !== false ){
            // update
            $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_user_subscriptions_meta SET
                                        meta_value=%s
                                        WHERE
                                        meta_key=%s
                                        AND
                                        subscription_id=%d
            ", $metaValue, $metaKey, $subscriptionId );
        } else {
            // create
            $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ihc_user_subscriptions_meta
                                        VALUES( null, %d, %s, %s );", $subscriptionId, $metaKey, $metaValue );
        }
        $wpdb->query( $query );
    }

    /**
     * @param int
     * @param string
     * @return mixed
     */
    public static function getOne( $subscriptionId=0, $metaKey='' )
    {
        global $wpdb;
        if ( !$subscriptionId ){
            return false;
        }
        $query = $wpdb->prepare( "SELECT meta_value
                                      FROM {$wpdb->prefix}ihc_user_subscriptions_meta
                                      WHERE
                                      subscription_id=%d
                                      AND
                                      meta_key=%s
                                      ORDER BY id DESC LIMIT 1
        ", $subscriptionId, $metaKey );
        $metaValue = $wpdb->get_var( $query );
        if ( $metaValue === null ){
            // we search into membership
            $subscriptionData = \Indeed\Ihc\UserSubscriptions::getOneById( $subscriptionId );
            if ( isset( $subscriptionData['level_id'] ) ){
                $metaValue = \Indeed\Ihc\Db\Memberships::getOneMeta( $subscriptionData['level_id'], $metaKey );
                if ( $metaValue === null ){
                    return false;
                }
            }
        }
        return $metaValue;
    }

    /**
     * @param int
     * @return array
     */
    public static function getAllForSubscription( $subscriptionId=0 )
    {
        global $wpdb;
        if ( !$subscriptionId ){
            return [];
        }
        $query = $wpdb->prepare( "SELECT meta_key, meta_value
                                      FROM {$wpdb->prefix}ihc_user_subscriptions_meta
                                      WHERE
                                      subscription_id=%d
        ", $subscriptionId );
        $allMetas = $wpdb->get_results( $query );
        if ( $allMetas ){
            foreach ( $allMetas as $object ){
                $metas[$object->meta_key] = $object->meta_value;
            }
        }

        $subscriptionData = \Indeed\Ihc\UserSubscriptions::getOneById( $subscriptionId );
        if ( isset( $subscriptionData['level_id'] ) ){
            $membershipMetas = \Indeed\Ihc\Db\Memberships::getOne( $subscriptionData['level_id'] );
            foreach ( $membershipMetas as $key => $value ){
                if ( !isset( $metas[$key] ) ){
                    $metas[$key] = $value;
                }
            }
        }
        return $metas;
    }

    /**
     * @param int
     * @param string
     * @return bool
     */
    public static function deleteOne( $subscriptionId=0, $metaKey='' )
    {
        global $wpdb;
        if ( !$subscriptionId || $metaKey == '' ){
            return false;
        }
        $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ihc_user_subscriptions_meta WHERE id=%d AND meta_key=%s;", $subscriptionId, $metaKey );
        return $wpdb->query( $query );
    }

    /**
     * @param int
     * @return bool
     */
    public static function deleteAllForSubscription( $subscriptionId=0 )
    {
      global $wpdb;
      if ( !$subscriptionId ){
          return false;
      }
      $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ihc_user_subscriptions_meta WHERE subscription_id=%d;", $subscriptionId );
      return $wpdb->query( $query );
    }

    /**
     * @param string
     * @param string
     * @return int
     */
    public static function getSubscriptionIdByMeta( $metaKey='', $metaValue='' )
    {
        global $wpdb;
        if ( $metaKey === '' ){
            return false;
        }
        $query = $wpdb->prepare( "SELECT subscription_id
                                      FROM {$wpdb->prefix}ihc_user_subscriptions_meta
                                      WHERE
                                      meta_key=%s
                                      AND
                                      meta_value=%s
                                      ORDER BY id DESC LIMIT 1
        ", $metaKey, $metaValue );
        $id = $wpdb->get_var( $query );
        if ( $id === null ){
            return false;
        }
        return $id;
    }
}

<?php
namespace Indeed\Uap\Db;

class NotificationLogs
{
    public function __construct()
    {

    }

    /**
     * @param array
     * @return bool
     */
    public static function save( $data=[] )
    {
        global $wpdb;
        if ( !isset( $data['notification_type'] ) || !isset( $data['email_address'] ) || !isset( $data['message'] ) || !isset( $data['uid'] ) ){
            return false;
        }
        if ( !isset( $data['subject'] ) ){
            $data['subject'] = '';
        }
        if ( !isset( $data['affiliate_id'] ) ){
            $data['affiliate_id'] = '';
        }
        if ( !isset( $data['rank_id'] ) ){
            $data['rank_id'] = '';
        }
        $time = time();
        $date = new \DateTime();
    		$date->setTimestamp( $time );
    		$date->setTimezone( new \DateTimeZone('UTC') );
    		$time = $date->format('Y-m-d H:i:s');
        $time = get_date_from_gmt( $time, 'Y-m-d H:i:s' );
        $currentDate = $time;
        $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}uap_notifications_logs VALUES(
          null,
          %s,
          %s,
          %s,
          %s,
          %s,
          %s,
          %s,
          %s
        );",
            $data['notification_type'],
            $data['email_address'],
            $data['subject'],
            $data['message'],
            $data['uid'],
            $data['affiliate_id'],
            $data['rank_id'],
            $currentDate
        );
        return $wpdb->query( $query );
    }

    public static function getOneByEmail()
    {

    }

    public static function getMany( $uid=0, $limit=30, $offset=0, $onlyUser=false )
    {
        global $wpdb;
        if ( $uid ){
            $query = $wpdb->prepare( "SELECT id, notification_type, email_address, subject, message, uid, affiliate_id, rank_id, create_date
                                          FROM {$wpdb->prefix}uap_notifications_logs WHERE uid=%d ORDER BY id DESC LIMIT %d OFFSET %d;",
                                          $uid, $limit, $offset );
        } else {
            $query = $wpdb->prepare( "SELECT id, notification_type, email_address, subject, message, uid, affiliate_id, rank_id, create_date
                                          FROM {$wpdb->prefix}uap_notifications_logs ORDER BY id DESC LIMIT %d OFFSET %d;", $limit, $offset );
        }

        $data = $wpdb->get_results( $query );
        if ( !$data ){
            return [];
        }

        return $data;
    }

    public static function getCount( $uid=0 )
    {
        global $wpdb;
        if ( $uid ){
          $query = $wpdb->prepare( "SELECT COUNT(*) as c FROM {$wpdb->prefix}uap_notifications_logs WHERE uid=%d;", $uid );
          $data = $wpdb->get_var( $query );
        } else {
            //No query parameters required, Safe query. prepare() method without parameters can not be called
            $query = "SELECT COUNT(*) as c FROM {$wpdb->prefix}uap_notifications_logs;";
            $data = $wpdb->get_var( $query );
        }
        return $data;
    }

    /**
     * @param none
     * @return int
     */
     public static function countAll()
     {
         global $wpdb;
         $query = "SELECT COUNT(id) as c FROM {$wpdb->prefix}uap_notifications_logs ";
         $count = $wpdb->get_var( $query );
         return (int)$count;
     }

    public static function getManyByNotificationType()
    {

    }

    /**
     * @param string
     * @return bool
     */
    public static function deleteMany($olderThen='')
    {
        global $wpdb;
        if ( $olderThen === '' ){
            return false;
        }
        $table = $wpdb->prefix . 'uap_notifications_logs';
        $query = $wpdb->prepare("DELETE FROM $table WHERE unix_timestamp(create_date)<%d ", $olderThen );
        return $wpdb->query( $query );
    }


}

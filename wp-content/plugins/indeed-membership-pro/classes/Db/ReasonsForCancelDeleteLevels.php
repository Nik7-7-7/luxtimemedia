<?php
namespace Indeed\Ihc\Db;

class ReasonsForCancelDeleteLevels
{
    private $tableName = '';

    public function __construct()
    {
        global $wpdb;
        $this->tableName = $wpdb->prefix . 'ihc_reason_for_cancel_delete_levels';
    }

    public function get( $limit=30, $offset=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT a.id, a.uid, a.lid, a.reason, a.action_type, a.action_date, b.user_login
                                      FROM {$this->tableName} a
                                      INNER JOIN {$wpdb->users} b
                                      ON a.uid=b.ID
                                      ORDER BY action_date ASC LIMIT %d OFFSET %d;", $limit, $offset );
        return $wpdb->get_results( $query );
    }

    public function count()
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT COUNT(id) FROM {$this->tableName};";
        return $wpdb->get_var( $query );
    }

    public function save( $attr=array() )
    {
        global $wpdb;
        if ( empty($attr['uid']) || empty($attr['lid']) || $attr['action_type']=='' || $attr['reason']=='' ){
            return false;
        }
        $currentDate = indeed_get_unixtimestamp_with_timezone();

        $query = $wpdb->prepare( "INSERT INTO {$this->tableName} VALUES(null, %d, %d, %s, %s, %d);", $attr['uid'], $attr['lid'], $attr['reason'], $attr['action_type'], $currentDate );
        return $wpdb->query( $query );
    }

    public function getForUser( $uid=0, $limit=30, $offset=0 )
    {
        global $wpdb;
        if ( !$uid ){
            return [];
        }
        $query = $wpdb->prepare( "SELECT a.id, a.uid, a.lid, a.reason, a.action_type, a.action_date, b.user_login
                                      FROM {$this->tableName} a
                                      INNER JOIN {$wpdb->users} b
                                      ON a.uid=b.ID
                                      WHERE
                                      a.uid=%d
                                      ORDER BY action_date ASC LIMIT %d OFFSET %d;", $uid, $limit, $offset );
        return $wpdb->get_results( $query );
    }

}

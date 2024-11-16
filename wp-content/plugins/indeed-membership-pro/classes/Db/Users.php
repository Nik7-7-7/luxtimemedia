<?php
namespace Indeed\Ihc\Db;

class Users
{
    public function __construct()
    {

    }

    /**
     * @param none
     * @return int
     */
    public static function countAll()
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT COUNT(*) FROM {$wpdb->users};";
        return $wpdb->get_var( $query );
    }

    /**
     * @param int
     * @return int
     */
    public static function countInInterval( $start=0, $end=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->users} WHERE UNIX_TIMESTAMP(user_registered) > %d
                                  AND UNIX_TIMESTAMP(user_registered) < %d;", $start, $end );
        return $wpdb->get_var( $query );
    }


}

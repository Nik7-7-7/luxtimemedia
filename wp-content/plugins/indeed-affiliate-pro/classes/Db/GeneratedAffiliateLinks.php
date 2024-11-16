<?php
namespace Indeed\Uap\Db;

class GeneratedAffiliateLinks
{

    /**
     * @param none
     * @return none
     */
    public function __construct(){}

    /**
     * @param int
     * @return array
     */
    public static function getOne( $id=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT id,aid,base_url,affiliate_url,campaign,create_date
                                    FROM {$wpdb->prefix}uap_generated_affiliate_links WHERE id=%d;", $id );
        $data = $wpdb->get_row( $query, ARRAY_A );
        if ( $data === null ){
            return false;
        }
        return $data;
    }

    /**
     * @param int
     * @return array
     */
    public static function getAllForAId( $aid=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare( "SELECT id,aid,base_url,affiliate_url,campaign,create_date
                                    FROM {$wpdb->prefix}uap_generated_affiliate_links WHERE aid=%d;", $aid );
        $data = $wpdb->get_results( $query, ARRAY_A );
        if ( $data === null ){
            return false;
        }
        return $data;
    }

    /**
     * @param array
     * @return bool
     */
    public static function save( $params=[] )
    {
        global $wpdb;
        $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}uap_generated_affiliate_links
                                    VALUES ( NULL, %d, %s, %s, %s, %s );",
                                    $params['aid'], $params['base_url'], $params['affiliate_url'], $params['campaign'], indeed_get_unixtimestamp_with_timezone() );
        return $wpdb->query( $query, ARRAY_A );
    }

    /**
     * @param int
     * @return bool
     */
    public static function delete( $id=0 )
    {
        global $wpdb;
        $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}uap_generated_affiliate_links WHERE id=%d;", $id );
        return $wpdb->query( $query );
    }

}

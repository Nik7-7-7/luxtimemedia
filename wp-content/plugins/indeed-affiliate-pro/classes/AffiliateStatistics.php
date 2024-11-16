<?php
namespace Indeed\Uap;

class AffiliateStatistics
{

    public function __construct(){}

    public function getGeneralVisitsForPermalink( $permalink='' )
    {
        global $wpdb;
        if ( !$permalink ){
            return 0;
        }
        $query = $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}uap_visits WHERE url=%s;", $permalink );
        return $wpdb->get_var( $query );
    }

    public function getGeneralReferralsForPermalink( $permalink='' )
    {
		global $wpdb;
        if ( !$permalink ){
            return 0;
        }
        $query = $wpdb->prepare( "SELECT COUNT(b.id) FROM
                                        {$wpdb->prefix}uap_visits a
                                        INNER JOIN {$wpdb->prefix}uap_referrals b
                                        ON a.id=b.visit_id
                                        WHERE a.url=%s
        ", $permalink);
        return $wpdb->get_var( $query );
    }

    public function getPersonalVisitsForPermalink( $permalink='', $affiliateId=0 )
    {
		global $wpdb;
        if ( !$permalink || !$affiliateId ){
            return 0;
        }
        $query = $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}uap_visits WHERE url=%s AND affiliate_id=%d;", $permalink, $affiliateId );
        return $wpdb->get_var( $query );
    }

    public function getPersonalReferralsForPermalink( $permalink='', $affiliateId=0 )
    {
		global $wpdb;
        if ( !$permalink || !$affiliateId ){
            return 0;
        }
        $query = $wpdb->prepare( "SELECT COUNT(b.id) FROM
                                        {$wpdb->prefix}uap_visits a
                                        INNER JOIN {$wpdb->prefix}uap_referrals b
                                        ON a.id=b.visit_id
                                        WHERE
                                        a.url=%s
                                        AND
                                        b.affiliate_id=%d
        ", $permalink, $affiliateId );
        return $wpdb->get_var( $query );
    }

}

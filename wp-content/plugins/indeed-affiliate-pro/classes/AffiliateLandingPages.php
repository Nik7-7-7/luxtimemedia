<?php
namespace Indeed\Uap;

class AffiliateLandingPages
{
    private $postMetaKey = 'uap_landing_page_affiliate_id';

    public function __construct()
    {
        if ( !get_option('uap_landing_pages_enabled') ){
            return;
        }
        add_filter('uap_init_affiliate_id_value', array($this, 'setAffiliateId'), 99, 2);
    }

    public function setAffiliateId($affiliateId=0, $currentUrl='')
    {
        global $indeed_db;
        if ( !empty($_COOKIE['uap_referral']) && $this->canRewrite() ){
            return '';
        }
        if (empty($currentUrl)){
            return $affiliateId;
        }
        $postId = $indeed_db->getPostIdByUrl($currentUrl);
        if (empty($postId)){
            return $affiliateId;
        }
        $affiliateNewId = get_post_meta($postId, $this->postMetaKey, true);
        if (empty($affiliateNewId)){
            return $affiliateNewId;
        }

        return $affiliateNewId;
    }

    /**
     * @since version 4.6
     */
    private function canRewrite()
    {
        $rewrite_referrals_enable = get_option('uap_rewrite_referrals_enable');
        if ( empty($rewrite_referrals_enable) ){
            return true;
        }
        return false;
    }

}

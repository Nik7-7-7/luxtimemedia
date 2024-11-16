<?php
namespace Indeed\Uap\Admin;

class UserProfile
{
    public $affiliate_id      = null;
    public $uid               = null;
    public $general_settings  = null;
    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        add_action( 'uap_print_admin_page', [ $this, 'page' ], 10, 1 );
    }

    /**
     * @param string
     * @return string
     */
    public function page( $tab='' )
    {
        global $indeed_db, $wp_roles;
        if ( $tab != 'user_profile' ){
            return;
        }
        $this->affiliate_id = empty( $_GET['affiliate_id'] ) ? 0 : sanitize_text_field( $_GET['affiliate_id'] );
        $uid = $indeed_db->get_uid_by_affiliate_id( $this->affiliate_id );
		    $this->uid = $uid;
        require_once UAP_PATH . 'classes/MLMGetChildren.class.php';
        $mlm = new \MLMGetChildren( $this->affiliate_id);
        $mlmParent = $indeed_db->mlm_get_parent( $this->affiliate_id );
        if ( empty( $mlmParent ) ){
            $mlmParent = '';
        } else {
            $parentUid = $indeed_db->get_uid_by_affiliate_id( $mlmParent );
            $mlmParent = $indeed_db->get_username_by_wpuid( $parentUid );
        }
		    $this->general_settings = $indeed_db->return_settings_from_wp_option('general-settings');
        $currentRank = $indeed_db->get_affiliate_rank(0, $uid);
        $allRanks = $indeed_db->get_ranks( true );
        $allRanks = uap_reorder_ranks( (array)$allRanks );
        if ( $allRanks ){
            foreach ( $allRanks as $rankArrayId => $rankData ){
                if ( $rankData->id == $currentRank ){
                    $nextRankId = $rankArrayId + 1;
                    break;
                }
            }
        }
        if ( isset($nextRankId) && isset( $allRanks[$nextRankId] ) ){
            $nextRankData = (array)$allRanks[$nextRankId];
        } else {
            $nextRankData = false;
        }

        $publicProfilePageID = get_option('uap_general_user_page');
        $affiliatePublicProfile = get_permalink($publicProfilePageID);
        $affiliatePublicProfile = add_query_arg('aid', $this->affiliate_id, $affiliatePublicProfile );
        $previewPublicProfile = $affiliatePublicProfile;

        $data = [
                  'uid'                 => $uid,
				          'affiliate_id'        => $this->affiliate_id,
                  'user_name'           => $indeed_db->get_username_by_wpuid( $uid ),
                  'user_email'          => $indeed_db->get_email_by_uid( $uid ),
                  'custom_slug'         => ($indeed_db->is_magic_feat_enable('custom_affiliate_slug')) ? $indeed_db->get_custom_slug_for_uid( $uid ) : false,
                  'full_name'           => $indeed_db->get_wp_full_name( $uid ),
                  'member_since'        => uap_convert_date_to_us_format( $indeed_db->getRegisterDate( $uid ) ),
                  'description'         => get_user_meta( $uid, 'description', true ),
				          'role'				        => isset( $wp_roles->roles[$indeed_db->get_user_first_role( $uid )]['name'] ) ? $wp_roles->roles[$indeed_db->get_user_first_role( $uid )]['name'] : '',
                  'avatar'              => uap_get_avatar_for_uid( $uid ),
                  'current_rank_data'   => $indeed_db->get_rank( $currentRank ),
                  'next_rank_data'      => $nextRankData,
                  'stats'               => $indeed_db->get_stats_for_reports( '', $this->affiliate_id ),
                  'user_meta'           => $indeed_db->getAllUserMeta( $uid ),
                  'mlm'                 => $this->printMlm( $this->affiliate_id, $indeed_db->get_username_by_wpuid( $uid ) ),
                  'coupons'             => ($indeed_db->is_magic_feat_enable('coupons')) ? $indeed_db->get_coupons_for_affiliate( $this->affiliate_id ) : false,
                  'last_ten_referrals'  => $indeed_db->get_referrals( $limit=10, 0, false, 'r.date', 'DESC', [ 'r.affiliate_id = ' . $this->affiliate_id]),
                  'cpm'                 => $indeed_db->getCPMForAffiliate( $this->affiliate_id ),
				          'genera_settings'     => $this->general_settings,
				          'affiliate_link'		  => $this->getAffiliateLink(),
                  'currency'            => uapCurrency(),
                  'bonus_enabled'       => $indeed_db->is_magic_feat_enable('bonus_on_rank'),
                  'sign_up_enabled'     => $indeed_db->is_magic_feat_enable('sign_up_referrals'),
                  'default_sign_up_referrals'  => get_option( 'uap_sign_up_amount_default' ),
                  'lifetime_commission' => $indeed_db->is_magic_feat_enable('lifetime_commissions'),
                  'reccuring_referrals' => $indeed_db->is_magic_feat_enable('reccuring_referrals'),
                  'pay_per_click'       => $indeed_db->is_magic_feat_enable('pay_per_click'),
                  'cpm_commission'      => $indeed_db->is_magic_feat_enable('cpm_commission'),
                  'referrals_stats'     => $indeed_db->get_stats_for_referrals('', $this->affiliate_id) + $indeed_db->getReferralsReports( $this->affiliate_id ),
                  'payment_stats'       => $indeed_db->get_stats_for_payments($this->affiliate_id),
                  'count_payments_pending'    => $indeed_db->getCountPendingPaymentsForAffiliate( $this->affiliate_id ),
                  'count_payments_completed'  => $indeed_db->getCountCompletePaymentsForAffiliate( $this->affiliate_id ),
                  'payments_settings'   => $indeed_db->get_affiliate_payment_settings( $uid ),
                  'landing_pages'       => $indeed_db->is_magic_feat_enable( 'landing_pages' ) ? $indeed_db->getLandingPagesForAffiliate( $this->affiliate_id ) : false,
                  'referrer_links'      => $indeed_db->is_magic_feat_enable( 'simple_links' ) ? $indeed_db->simple_links_get_items_for_affiliate( $this->affiliate_id ) : false,
                  'ranking_possition'   => $indeed_db->affiliateGetRanking( $this->affiliate_id),
                  'number_of_affiliates' => $indeed_db->countAffiliates(),
                  'campaigns'             => $indeed_db->get_campaigns_reports_for_affiliate_id( $this->affiliate_id, 10, -1, false, 'id', 'DESC' ),
                  'statsForLast30'      => $indeed_db->getReferralsCountsForLastDays( $this->affiliate_id, 30 ),
                  'public_profile_preview'      => $previewPublicProfile,
        ];

        $view = new \Indeed\Uap\IndeedView();
        $output = $view->setTemplate( UAP_PATH . 'admin/views/user-profile.php' )
                  ->setContentData( $data )
                  ->getOutput();
        echo esc_uap_content( $output );
    }

    /**
     * @param int
     * @param string
     * @return string
     */
    private function printMlm( $affiliateId=0, $affiliateUsername='' )
    {
        global $indeed_db;
        require_once UAP_PATH . 'classes/MLMGetChildren.class.php';
        $children_object = new \MLMGetChildren($affiliateId);
        $data = [
                  'items'         => $children_object->get_results(),
                  'parent'        => $indeed_db->mlm_get_parent($affiliateId),
                  'affiliate_id'  => $affiliateId,
        ];
        if (empty($data['parent'])){
            $data['parent'] = '';
        } else {
            $parentUid = $indeed_db->get_uid_by_affiliate_id($data['parent']);
            $data['parent'] = $indeed_db->get_username_by_wpuid($parentUid);
        }
        $view = new \Indeed\Uap\IndeedView();
        return $view->setTemplate( UAP_PATH . 'admin/views/mlm-view_affiliate_children.php' )
                  ->setContentData( [ 'data' => $data, 'affiliate_name' => $affiliateUsername ], true )
                  ->getOutput();
    }

    /**
     * @param none
     * @return string
     */
  	private function getAffiliateLink()
    {
    		$link_for_aff = get_option('uap_referral_custom_base_link');
    		if (empty($link_for_aff)){
    				$link_for_aff = get_home_url();
    		}

    	  $link = '';
    	  $value = $this->affiliate_id;
    	  $param = 'ref';

    	  if (!empty($this->general_settings['uap_referral_variable'])){
    		  $param = $this->general_settings['uap_referral_variable'];
    	  }
    	  if (!empty($this->general_settings['uap_default_ref_format']) && $this->general_settings['uap_default_ref_format']=='username'){
    		    $user_info = get_userdata($this->uid);
    		    if (!empty($user_info->user_login)){

    			      $value = urlencode($user_info->user_login);
    		    }
    	  }
    	  $link = uap_create_affiliate_link($link_for_aff, $param, $value);
    		return $link;
  	}

}

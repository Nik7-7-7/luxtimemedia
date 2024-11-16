<?php
namespace Indeed\Uap;

class AffiliateMarketingBuilder
{
    private $uid                    = 0;
    private $affiliateId            = 0;
    private $currentPermalink       = '';

    public function __construct()
    {}

    public function setUid( $uid=0 )
    {
        $this->uid = $uid;
        return $this;
    }

    public function setAffiliateId( $affiliateId=0 )
    {
        $this->affiliateId = $affiliateId;
        return $this;
    }

    public function setCurrentPermalink( $currentPermalink='' )
    {
        $this->currentPermalink = $currentPermalink;
        $this->currentPermalink = urldecode( $this->currentPermalink );
        return $this;
    }

    public function getBannerForPermalink( $size='thumbnail' )
    {
        global $indeed_db;
        $postId = $indeed_db->getPostIdByUrl( $this->currentPermalink );
        if ( !$postId ){
            return '';
        }
        $image = get_the_post_thumbnail_url( $postId, $size );
        if ( !$image ){
            return '';
        }
        $url = $this->getPermalinkForAffiliate();
        $postName = empty( $postId ) ? '' : $indeed_db->ulp_get_label_by_id( $postId );

        return '<a href="' . $url . '" target="_blank"><img src="' . $image . '" alt="' . $postName . '" class="uap-info-affiliate-bar-image"/></a>';

    }

    public function getDefaultBAnnerForPermalink()
    {
        global $indeed_db;
		$postName = '';
        $postId = $indeed_db->getPostIdByUrl( $this->currentPermalink );
        if ( $postId ){
            $postName = empty( $postId ) ? '' : $indeed_db->ulp_get_label_by_id( $postId );
        }
        $image = get_option( 'uap_info_affiliate_bar_banner_default_value' );
        if ( !$image ){
            return '';
        }
        $url = $this->getPermalinkForAffiliate();

        return '<a href="' . $url . '" target="_blank"><img src="' . $image . '" alt="' . $postName . '" class="uap-info-affiliate-bar-image"/></a>';
    }

    public function getPermalinkForAffiliate( $withSlug=false )
    {
        global $indeed_db;
    		if ( (!$this->uid && !$this->affiliateId) || !$this->currentPermalink ){
    				return '';
    		}
    		if ( !$this->affiliateId ){
    				$this->affiliateId = $indeed_db->get_affiliate_id_by_wpuid( $this->uid );
    		} else if ( !$this->uid ){
    				$this->uid = $indeed_db->get_uid_by_affiliate_id( $this->affiliateId );
    		}
    		$value = $this->affiliateId;
    		$param = get_option( 'uap_referral_variable' );
    		$format = get_option( 'uap_default_ref_format' );
    		if ( !$param ){
    				$param = 'ref';
    		}

    		$custom_affiliate_slug = $indeed_db->is_magic_feat_enable('custom_affiliate_slug');
    		if ( $custom_affiliate_slug && $withSlug ){
    				$theSlug = $indeed_db->get_custom_slug_for_uid( $this->uid );
    		}

    		if ( !empty( $theSlug ) ){
    				$value = $theSlug;
    		} else if ( !empty( $format ) && $format=='username' ) {
    				$userInfo = get_userdata( $this->uid );
    				if ( !empty($userInfo->user_login) ){
    					$value = urlencode( $userInfo->user_login );
    				}
    		}

    		return uap_create_affiliate_link( $this->currentPermalink, $param, $value );
    }

    public function getSocial( $optionName='uap_info_affiliate_bar_social_shortcode' )
    {
        $output = '';
    		if ( !uap_is_social_share_intalled_and_active() ){ //  || !get_option('uap_social_share_enable')
    				return $output;
    		}
    		$shortcode = get_option( $optionName );
    		if ( !$shortcode ){
    				return $output;
    		}
        $url = $this->getPermalinkForAffiliate();
        
    		$shortcode = stripslashes($shortcode);
    		$shortcode = str_replace(']', '', $shortcode);
    		$shortcode .= " is_affiliates=1"; ///just for safe
    		$shortcode .= " custom_description='" . get_option('uap_social_share_message') . "'";
    		$shortcode .= " custom_url='" . $url ."']";
    		return do_shortcode($shortcode);
    }

    public function getSocialForCreatives( $shortcode='', $description='', $permalink='', $featureImage='' )
    {
        $shortcode = stripslashes( $shortcode );
        $shortcode = str_replace( ']', '', $shortcode );
        $shortcode .= " is_affiliates=1"; ///just for safe
        $shortcode .= " feat_img='" . $featureImage . "'";
        $shortcode .= " custom_description='" . $description . "'";
        $shortcode .= " custom_url='" . $permalink ."' is_banner=1 ]";
        return do_shortcode($shortcode);
    }


}

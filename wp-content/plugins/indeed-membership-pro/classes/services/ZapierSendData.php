<?php
namespace Indeed\Ihc\Services;
/*
@since 7.4
*/
class ZapierSendData
{
    private $settings = array();

    public function __construct()
    {
        $this->settings = ihc_return_meta_arr('zapier');
        if ( !$this->settings['ihc_zapier_enabled'] ){
            return false;
        }
        add_action( 'ihc_action_create_user_review_request', array( $this, 'onUserRegister' ), 99, 2 );
        add_action( 'ihc_action_create_user_register', array( $this, 'onUserRegister' ), 99, 2 );
        add_action( 'ihc_action_after_order_placed', array( $this, 'onOrderCreated' ), 99, 2 );
        add_action( 'ihc_payment_completed', array($this, 'onOrderCompleted'), 99, 2 );
    }

    public function onUserRegister( $uid=0, $lid=0 )
    {
        if ( !$uid || !$this->settings['ihc_zapier_new_user_enabled'] ){
            return false;
        }
        $userData = \Ihc_Db::user_get_all_data($uid);
        $endpoint = $this->settings['ihc_zapier_new_user_webhook'];
        return $this->send( $endpoint, $userData );
    }

    public function onOrderCreated( $uid=0, $lid=0 )
    {
        if ( !$uid || !$lid || !$this->settings['ihc_zapier_new_order_enabled'] ){
            return false;
        }
        $data = array(
            'user_id'             => $uid,
            'user_email'          => \Ihc_Db::user_get_email($uid),
            'user_full_name'      => \Ihc_Db::getUserFulltName($uid),
            'membership_id'       => $lid,
            'membership_name'     => \Ihc_Db::get_level_name_by_lid($lid)
        );
        $data = $data + \Ihc_Db::getLastOrderDataByUserAndLevel( $uid, $lid );
        $endpoint = $this->settings['ihc_zapier_new_order_webhook'];
        return $this->send( $endpoint, $data );
    }

    public function onOrderCompleted( $uid=0, $lid=0 )
    {
        if ( !$uid || !$lid || !$this->settings['ihc_zapier_order_completed_enabled'] ){
            return false;
        }
        $data = array(
            'user_id'             => $uid,
            'user_email'          => \Ihc_Db::user_get_email($uid),
            'user_full_name'      => \Ihc_Db::getUserFulltName($uid),
            'membership_id'       => $lid,
            'membership_name'     => \Ihc_Db::get_level_name_by_lid($lid)
        );
        $data = $data + \Ihc_Db::getLastOrderDataByUserAndLevel( $uid, $lid );
        $endpoint = $this->settings['ihc_zapier_order_completed_webhook'];
        return $this->send( $endpoint, $data );
    }

    private function send( $endpoint='', $content=array() )
    {
        if ( !$endpoint || !$content ){
            return false;
        }
        $attr = array(
                        'headers'                 => array( 'Content-Type:' => 'application/json' ),
                        'body'                    => json_encode( $content )
        );
        return wp_remote_post( $endpoint, $attr );
    }

}

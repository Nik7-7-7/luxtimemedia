<?php
namespace Indeed\Ihc\Services;
/*
@since 7.4
*/
class Kissmetrics
{
    private $settings = array();

    public function __construct()
    {
        $this->settings = ihc_return_meta_arr('kissmetrics');
        if ( !$this->settings['ihc_kissmetrics_enabled'] || !$this->settings['ihc_kissmetrics_apikey'] ){
            return false;
        }
        /// actions
        add_action( 'ump_on_register_action', array( $this, 'onUserRegister' ), 99, 1 );
        add_action( 'ihc_action_after_subscription_activated', array( $this, 'onAssignLevel' ), 99, 2 );
        add_action( 'ihc_payment_completed', array($this, 'onOrderCompleted'), 99, 2 );
        add_action( 'wp_login', array($this, 'onUserLogin'), 99, 2 );
        add_action( 'ihc_action_after_subscription_delete', array( $this, 'onRemoveLevelFromUser' ), 99, 2 );
    }

    private function kmObject()
    {
        require_once IHC_PATH . 'classes/services/kissmetrics/autoload.php';
        return new \KISSmetrics\Client( $this->settings['ihc_kissmetrics_apikey'], \KISSmetrics\Transport\Sockets::initDefault() ); // Initialize
    }

    public function onUserRegister( $uid=0 )
    {
        if ( !$this->settings['ihc_kissmetrics_events_user_register'] ){
            return false;
        }
        if ( !$uid ){
            return false;
        }
        $message = empty($this->settings['ihc_kissmetrics_events_user_register_label']) ? esc_html__( 'Registered!', 'ihc' ) : $this->settings['ihc_kissmetrics_events_user_register_label'];
        $email = \Ihc_Db::user_get_email( $uid );
        $kmObject = $this->kmObject();
        $kmObject->identify($email)->record( $message )->submit();
    }

    public function onAssignLevel( $uid=0, $lid=0 )
    {
        if ( !$this->settings['ihc_kissmetrics_events_user_get_level'] ){
            return false;
        }
        if ( !$uid || !$lid ){
            return false;
        }
        $email = \Ihc_Db::user_get_email( $uid );
        $levelName = \Ihc_Db::get_level_name_by_lid( $lid );

        $message = empty( $this->settings['ihc_kissmetrics_events_user_get_level_label'] ) ? esc_html__( 'User get level ', 'ihc' ) . '%level%' : $this->settings['ihc_kissmetrics_events_user_get_level_label'] ;
        $message = str_replace( '%level%' , $levelName, $message );

        $kmObject = $this->kmObject();
        $kmObject->identify($email)->record( $message )->submit();
    }


    public function onOrderCompleted( $uid=0, $lid=0 )
    {
        if ( !$uid || !$lid || !$this->settings['ihc_kissmetrics_events_user_finish_payment'] ){
            return false;
        }
        $email = \Ihc_Db::user_get_email( $uid );
        $levelName = \Ihc_Db::get_level_name_by_lid( $lid );

        $message = empty( $this->settings['ihc_kissmetrics_events_user_finish_payment_label'] ) ? esc_html__( 'User has finish the payment for level ', 'ihc' ) . '%level%' : $this->settings['ihc_kissmetrics_events_user_finish_payment_label'];
        $message = str_replace( '%level%' , $levelName, $message );

        $kmObject = $this->kmObject();
        $kmObject->identify($email)->record( $message )->submit();
    }

    public function onUserLogin( $userName='', $userObject=null )
    {
        if ( !$userObject || empty($userObject->ID) || !$this->settings['ihc_kissmetrics_events_user_login'] ){
            return false;
        }
        $email = \Ihc_Db::user_get_email( $userObject->ID );
        $message = empty($this->settings['ihc_kissmetrics_events_user_login_label']) ? esc_html__( 'Login!', 'ihc' ) : $this->settings['ihc_kissmetrics_events_user_login_label'];
        $kmObject = $this->kmObject();
        $kmObject->identify($email)->record( $message )->submit();
    }

    public function onRemoveLevelFromUser( $uid=0, $lid=0 )
    {
        if ( !$uid || !$lid || !$this->settings['ihc_kissmetrics_events_remove_user_level']  ){
            return false;
        }
        $email = \Ihc_Db::user_get_email( $uid );
        $levelName = \Ihc_Db::get_level_name_by_lid( $lid );
        if ( empty($email) ){
            return false;
        }
        $message = empty( $this->settings['ihc_kissmetrics_events_remove_user_level_label'] ) ? esc_html__( 'Level ', 'ihc') . '%level%' . esc_html__( ' has been removed from this user.', 'ihc') : $this->settings['ihc_kissmetrics_events_remove_user_level_label'];
        $message = str_replace( '%level%' , $levelName, $message );

        $kmObject = $this->kmObject();
        $kmObject->identify($email)->record( $message )->submit();
    }


}

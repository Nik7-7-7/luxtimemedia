<?php
namespace Indeed\Ihc;

class Restrictions
{
    /**
     * @var int
     */
    private static $currentPost             = null;
    /**
     * @var int
     */
    private static $userId                  = null;
    /**
     * @var string
     */
    private static $userType                = 'unreg';
    /**
     * @var array
     */
    private static $userMemberships         = [];
    /**
     * @var array
     */
    private static $posts                   = [];

    /**
     * @param none
     * @return none
     */
    public function __construct(){}

    /**
     * @param int
     * @return none
     */
    public static function setPostId( $input=0 )
    {
        if ( $input === 0 ){
            return;
        }
        self::$currentPost = $input;
    }

    /**
     * @param int
     * @return none
     */
    public static function setUser( $input=0 )
    {
        // set user id
        /*
        global $current_user;
        if ( $input > 0 ){
            self::$userId = $input;
        } else {
            self::$userId = isset( $current_user->ID ) ? $current_user->ID : 0;
        }

        if ( self::$userId === 0 ){
            self::$userType = 'unreg';
            return;
        }
        if ( user_can( self::$userId, 'manage_options' ) ){
            self::$userType = 'admin';
            return;
        }

        self::$userType = 'reg';
        self::$userMemberships = \Indeed\Ihc\UserSubscriptions::getAllForUserAsList( self::$userId, true );
        self::$userMemberships = apply_filters( 'ihc_public_get_user_levels', self::$userMemberships, self::$userId );
        if ( self::$userMemberships !== '' && self::$userMemberships !== false && self::$userMemberships !== null ){
            self::$userMemberships = explode( ',', self::$userMemberships );
        }
        */
    }

    public static function getResult()
    {
        return self::$userMemberships;
    }

}

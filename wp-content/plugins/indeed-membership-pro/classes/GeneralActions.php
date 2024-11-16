<?php

namespace Indeed\Ihc;

class GeneralActions
{

    public function __construct()
    {
        // since version 12.5
        add_action( 'init', [ $this, 'rewriteRulesForPublicProfilePage' ], 2 );

        add_action( 'ihc_before_user_save_custom_field', array( $this, 'changeAvatar' ), 999, 3 );

        add_action( 'ihc_action_general_init', [ $this, 'restrictEntireWebsiteWithoutLogin' ], 999, 1 );

        // checkout page - redirect
        add_action( 'ihc_action_general_init', [ $this, 'checkoutPageRestriction' ], 9999, 1 );

    }

    /**
     * since version 12.5
     * @param none
     * @return none
     */
    public function rewriteRulesForPublicProfilePage()
    {
        $viewUser = get_option( 'ihc_general_register_view_user' );
        if ( $viewUser === false || $viewUser === null || (int)$viewUser === 0 ){
            return;
        }
        $post_name = get_post_field('post_name', $viewUser);
        if ( empty($post_name) ){
            return;
        }
        add_rewrite_rule("$post_name/([^/]+)/?", 'index.php?pagename=' . $post_name . '&ihc_name=$matches[1]', 'top');
        add_rewrite_rule("$post_name/([^/]+)/?",'index.php?page_id=' . $viewUser . '&ihc_name=$matches[1]', 'top');
    }

    /**
     * @param string
     * @param string
     * @param string
     * @return none
     */
    public function changeAvatar( $uid='', $metaKey='', $metaValue='' )
    {
        if ( $metaKey != 'ihc_avatar' || $metaValue == '' ){
            return;
        }
        $oldValue = get_user_meta( $uid, 'ihc_avatar', true );
        if ( !$oldValue || $oldValue == $metaValue ){
            return;
        }
        if ( strpos( $oldValue, "http" ) === 0 ){
            return;
        }
        /// delete old avatar
        wp_delete_attachment( $oldValue, true );

    }

    /**
     * @param int
     * @return none
     */
    public function restrictEntireWebsiteWithoutLogin( $postId=0 )
    {
        global $current_user;
        if ( isset( $current_user->ID ) &&  $current_user->ID > 0 ){
            return;
        }
        $restrictionOn = get_option( 'ihc_security_restrict_everything' );
        if ( !$restrictionOn ){
            return ;
        }
        $login = get_option( 'ihc_general_login_default_page' );
        $lostpass = get_option( 'ihc_general_lost_pass_page' );
        $subscriptionpage = get_option( 'ihc_subscription_plan_page' );
        $registerpage = get_option( 'ihc_general_register_default_page' );
        $tospage = get_option( 'ihc_general_tos_page' );
        $home = get_option('page_on_front');
        if ( $home == $postId || $postId == $login || $postId == $lostpass || $postId == $subscriptionpage || $postId == $registerpage || $postId == $tospage ){
            return;
        }
        if ( $login === false || $login == '' || $login == -1 ){
            return;
        }
        $loginPermalink = get_permalink( $login );
        if ( $loginPermalink === false || $loginPermalink == '' ){
            return;
        }
        $except = get_option( 'ihc_security_restrict_everything_except' );
        $allow = explode( ',', preg_replace('/\s+/', '', $except ) );

        if ( count( $allow ) > 0 && in_array( $postId, $allow ) ){
            return;
        }
        wp_redirect( $loginPermalink );
        exit;
    }

    /**
     * @param int
     * @return none
     */
    public function checkoutPageRestriction( $postId=0 )
    {
        global $current_user;
        if ( isset( $current_user->ID ) &&  $current_user->ID > 0 ){
            // user is logged in
            return;
        }
        $checkoutPageId = get_option( 'ihc_checkout_page' );
        $checkoutPageId = (int)$checkoutPageId;
        if ( !$checkoutPageId ){
            // checkout page doesnt exists
            return;
        }
        if ( $checkoutPageId === $postId ){
            $register = get_option('ihc_general_register_default_page');
            $registerPage = get_permalink( $register );
            if ( $registerPage === false ){
                // current page is checkout page and user is not logged in so do the redirect
                return;
            }
            wp_redirect( $registerPage );
            exit;
        }
    }

}

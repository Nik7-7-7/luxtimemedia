<?php
namespace Indeed\Ihc;

class PrettyLinks
{
    private $stopIt = false;
    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        add_action( 'init', [ $this, 'updateRules' ], 1 );// only for admin


        if ( (int)get_option( 'ihc_pretty_links' ) === 1 && $this->stopIt === false ){
            add_action( 'init', [ $this, 'rewriteRules' ], 99999 ); // run everytime

            //add_action( 'after_switch_theme', [ $this, 'onThemeChange'], 99999, 2 );// hook on theme change

            // register links
            add_filter('ihc_filter_public_subscription_link', [ $this, 'registerPrettyLink' ], 999, 3 );

            // account page Links
            add_filter( 'ihc_filter_public_account_page_tab_link', [ $this, 'accountPageTabLink' ], 999, 3 );

            // register form filter. by default it will not find the membership id in url.
            add_filter( 'ihc_public_filter_register_form_no_lid_redirect', [ $this, 'stopRedirectFromRegisterWithoutLid' ], 999, 2 );

            add_filter( 'query_vars', [ $this, 'filterQueryVars'], 10, 1 );
        }
    }

    /**
     * @param array
     * @return array
     */
    public function filterQueryVars( $args=[] )
    {
        $args[] = 'iump-membership-slug';
        $args[] = 'iump-account-tab';
        return $args;
    }

    /**
     * @param none
     * @return none
     */
    public function updateRules()
    {
        if ( !current_user_can( 'administrator' ) ){
            return; // only administrator
        }
        if ( !isset( $_POST['ihc_save'] ) || empty($_POST['ihc_admin_general_options_nonce']) || !wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_general_options_nonce']), 'ihc_admin_general_options_nonce' ) ){
            return;// no workflow restrictions page - out
        }
        if ( isset( $_POST['ihc_pretty_links'] ) && $_POST['ihc_pretty_links'] !== '' ){
            if ( (int)sanitize_text_field($_POST['ihc_pretty_links']) === 1 ){
                $this->rewriteRules();
            }
            flush_rewrite_rules();
            $this->stopIt = true;
        }
    }

    /**
     * @param none
     * @return none
     */
    public function rewriteRules()
    {
        // register page
        $register = get_option( 'ihc_general_register_default_page' );
        if ( $register !== false && (int)$register > 0 ){
            $post = get_post( $register );
            add_rewrite_rule( $post->post_name .  '/([^/]+)/?$', 'index.php?page_id=' . $register . '&iump-membership-slug=$matches[1]', 'top' );
        }

        // account page
        $accountPage = get_option( 'ihc_general_user_page' );
        if ( $accountPage !== false && (int)$accountPage > 0 ){
            $post = get_post( $accountPage );
            add_rewrite_rule( $post->post_name .  '/([^/]+)/?$', 'index.php?page_id=' . $accountPage . '&iump-account-tab=$matches[1]', 'top' );
        }

        // checkout page
        $checkoutPage = get_option( 'ihc_checkout_page' );
        if ( $checkoutPage !== false && (int)$checkoutPage > 0 ){
            $post = get_post( $checkoutPage );
            add_rewrite_rule( $post->post_name .  '/([^/]+)/?$', 'index.php?page_id=' . $checkoutPage . '&iump-membership-slug=$matches[1]', 'top' );
        }

    }

    /**
     * @param string
     * @param object
     * @return none
     */
    public function onThemeChange( $old_theme_name='', $old_theme=false )
    {
        $this->rewriteRules();
        flush_rewrite_rules();
    }

    /**
     * @param string
     * @param string
     * @return string
     */
    public function registerPrettyLink( $url='', $baseUrl='', $membershipId=0 )
    {
        if ( (int)$membershipId === 0 ){
            return $url;
        }
        $membership = \Indeed\Ihc\Db\Memberships::getOne( $membershipId );
        $membershipSlug = isset( $membership['name'] ) ? $membership['name'] : '';
        if ( $membershipSlug === '' ){
            return $url;
        }
        if ( $baseUrl[strlen($baseUrl)-1] !== '/' ){
            $baseUrl .= '/';
        }
        return $baseUrl . $membershipSlug . '/';
    }

    /**
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function accountPageTabLink( $url='', $baseUrl='', $tab='' )
    {
        if ( $tab === '' ){
            return $url;
        }
        if ( $baseUrl[strlen($baseUrl)-1] !== '/' ){
            $baseUrl .= '/';
        }
        return $baseUrl . $tab . '/';
    }

    /**
     * @param none
     * @return none
     */
    public function stopRedirectFromRegisterWithoutLid( $doRedirect=true, $url='' )
    {
        $allMemberships = \Indeed\Ihc\Db\Memberships::getAll();
        if ( !$allMemberships ){
            return $doRedirect;
        }
        foreach ( $allMemberships as $membership ){
            if ( !isset( $membership['name'] ) || $membership['name'] === '' ){
                continue;
            }
            if ( strpos( $url, '/' . $membership['name'] . '/' ) !== false ){
                return false;
            }
        }
        return $doRedirect;
    }

}

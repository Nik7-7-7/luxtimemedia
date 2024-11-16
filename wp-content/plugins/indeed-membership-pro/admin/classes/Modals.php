<?php
namespace Indeed\Ihc\Admin;

class Modals
{
    /**
     * @var array
     */
    private $jobs                 = [];

    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        // modified since version 12.1
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'ihc_manage' && isset($_GET['tab']) && $_GET['tab'] === 'wizard' ){
            // out, its wizard page
            return;
        }
        // end of modified since version 12.1

        // ajax action for tracking
        add_action( 'wp_ajax_ihc_admin_confirm_tracking', [ $this, 'confirmTracking' ] );// confirm
        add_action( 'wp_ajax_ihc_admin_refuse_tracking', [ $this, 'refuseConfirmTracking' ] );// refuse

        // search for modals
        $this->maybeAllowTrackingNotice();

        // any modals to show
        if ( !$this->jobs || !isset( $this->jobs[0] ) ){
            return;
        }
        // fire the first modal notification
        add_action( 'ihc_filter_admin_dashboard_after_footer_html', [ $this, $this->jobs[0] ] );
    }

    /**
     * @param none
     * @return none
     */
    public function maybeAllowTrackingNotice()
    {
        if ( (int)get_option( 'ihc_allow_tracking', 0 ) === 1 ){
            // already enabled. out
            return;
        }
        if ( get_option( 'ihc_allow_tracking-admin-notice', 0 ) + ( 60 * 60 * 24 * 30 ) > time() ){
            // the notification has been showed in the last 30 days. out
            return;
        }
        $this->jobs[] = 'AllowTrackingNotice';
    }

    /**
     * @param none
     * @return none
     */
     public function AllowTrackingNotice( $content='' )
     {
         $view = new \Indeed\Ihc\IndeedView();
         $output = $view->setTemplate( IHC_PATH . 'admin/includes/tabs/tracking_modal.php' )
                        ->setContentData( [] )
                        ->getOutput();
         echo esc_ump_content( $output );
     }

     /**
      * @param none
      * @return none
      */
     public function confirmTracking()
     {
          if ( !ihcIsAdmin() ){
               die;
          }
          if ( !ihcAdminVerifyNonce() ){
               die;
          }
          update_option( 'ihc_allow_tracking', 1 );
          die;
     }


     public function refuseConfirmTracking()
     {
         if ( !ihcIsAdmin() ){
              die;
         }
         if ( !ihcAdminVerifyNonce() ){
              die;
         }
         // update the modal notification time
         update_option( 'ihc_allow_tracking-admin-notice', time() );
         die;
     }


}

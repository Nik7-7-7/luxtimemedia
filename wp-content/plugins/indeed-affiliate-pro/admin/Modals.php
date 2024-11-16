<?php
namespace Indeed\Uap\Admin;

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
        add_action( 'admin_init', [ $this, 'hooks'] );
    }

    public function hooks()
    {
      if ( isset( $_GET['page'] ) && $_GET['page'] === 'ultimate_affiliates_pro' && isset($_GET['tab']) && $_GET['tab'] === 'wizard' ){
          // out, its wizard page
          return;
      }

      // ajax action for tracking
      add_action( 'wp_ajax_uap_admin_confirm_tracking', [ $this, 'confirmTracking' ] );// confirm
      add_action( 'wp_ajax_uap_admin_refuse_tracking', [ $this, 'refuseConfirmTracking' ] );// refuse

      // search for modals
      $this->maybeAllowTrackingNotice();

      // any modals to show
      if ( !$this->jobs || !isset( $this->jobs[0] ) ){
          return;
      }
      // fire the first modal notification
      add_action( 'uap_action_admin_dashboard_after_footer_html', [ $this, $this->jobs[0] ] );
    }

    /**
     * @param none
     * @return none
     */
    public function maybeAllowTrackingNotice()
    {
        if ( (int)get_option( 'uap_allow_tracking', 0 ) === 1 ){
            // already enabled. out
            return;
        }
        if ( get_option( 'uap_allow_tracking-admin-notice', 0 ) + ( 60 * 60 * 24 * 30 ) > time() ){
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
         $view = new \Indeed\Uap\IndeedView();
         $output = $view->setTemplate( UAP_PATH . 'admin/views/tracking_modal.php' )
                        ->setContentData( [] )
                        ->getOutput();
         echo esc_uap_content( $output );
     }

     /**
      * @param none
      * @return none
      */
     public function confirmTracking()
     {
          if ( !indeedIsAdmin() ){
               die;
          }
          if ( !uapAdminVerifyNonce() ){
               die;
          }
          update_option( 'uap_allow_tracking', 1 );
          die;
     }


     public function refuseConfirmTracking()
     {
         if ( !indeedIsAdmin() ){
              die;
         }
         if ( !uapAdminVerifyNonce() ){
              die;
         }
         // update the modal notification time
         update_option( 'uap_allow_tracking-admin-notice', time() );
         die;
     }


}

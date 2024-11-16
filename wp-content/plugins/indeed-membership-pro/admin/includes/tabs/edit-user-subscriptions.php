<?php
if ( isset( $_POST['ihc_save'] ) ){
    $uid = isset( $_POST['uid'] ) ? sanitize_text_field( $_POST['uid'] ) : 0;
    // save levels
    \Indeed\Ihc\UserSubscriptions::assignSubscriptionToUserManually( $uid, (isset($_POST['ihc_assign_user_levels'])) ? indeed_sanitize_array($_POST['ihc_assign_user_levels']) : '' );
    // update level expire
    \Indeed\Ihc\UserSubscriptions::updateUserSubscriptionExpireManually( indeed_sanitize_array($_POST) );

    if ( isset( $_POST['ihc_delete_levels'] ) ){
        foreach ( $_POST['ihc_delete_levels'] as $key => $lid ){
            \Indeed\Ihc\UserSubscriptions::deleteOne( $uid, sanitize_text_field( $lid ) );
        }
    }

    /// subscriptions metas
    if ( isset( $_POST['subscription_meta'] ) ){
        foreach ( $_POST['subscription_meta'] as $subscriptionId => $subscriptionMetaArray ){
            foreach ( $subscriptionMetaArray as $metaKey => $metaValue ){
                \Indeed\Ihc\Db\UserSubscriptionsMeta::save( sanitize_text_field($subscriptionId), sanitize_text_field($metaKey), sanitize_text_field($metaValue) );
            }
        }
    }


    // update status if it's case
    if ( isset( $_POST['subscription_status'] ) ){
        foreach ( $_POST['subscription_status'] as $subscriptionId => $subscriptionStatus ){
            \Indeed\Ihc\UserSubscriptions::updateStatusBySubscriptionId( sanitize_text_field($subscriptionId), sanitize_text_field($subscriptionStatus) );
        }
    }

}
$uid = isset( $_GET['uid'] ) ? sanitize_text_field($_GET['uid']) : 0;
if ( !$uid ){
    $html = '<h4>' . esc_html__( 'No subscriptions for this user.', 'ihc' ) . '</h4>';
} else {
    $userSubscriptions = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid, false );
    $attributes = [
                    'userLevelsArray'		=> $userSubscriptions,
                    'uid'								=> $uid,
                    'subscriptions'     => \Indeed\Ihc\Db\Memberships::getAll(),
    ];
    $view = new \Indeed\Ihc\IndeedView();
    $html = $view->setTemplate( IHC_PATH . 'admin/includes/tabs/user-membership-plans-management.php')
                 ->setContentData( $attributes, true )
                 ->getOutput();
}
?>
<div class="iump-wrapper">
    <div class="col-right">
        <div class="ihc-stuffbox">
            <h3><?php esc_html_e( 'Manage Membership Plans', 'ihc' );?></h3>
            <div class="inside">
                <form method="post">
                    <h2><?php echo \Ihc_Db::getUserFulltName( $uid );?> <strong>(<?php echo \Ihc_Db::get_username_by_wpuid( $uid );?>)</strong></h2>
                    <input type="hidden" name="uid" value="<?php echo esc_attr($uid);?>" />
                    <?php echo esc_ump_content($html);?>
                    <div class="ihc-wrapp-submit-bttn iump-submit-form ihc-manage-user-expire-wrapper-submit-bttn">
                        <input type="submit" name="ihc_save" id="ihc_submit_bttn" class="button button-primary button-large" value="<?php esc_html_e( 'Save Changes', 'ihc');?>" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

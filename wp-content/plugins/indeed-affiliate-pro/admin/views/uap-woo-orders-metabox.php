<?php if ( $data['referrals'] ):?>
    <?php global $indeed_db;?>
    <?php foreach ( $data['referrals'] as $referralObject ):?>
        <div><?php echo esc_uap_content('<b>Affiliate user: </b><a href="'.admin_url('admin.php?page=ultimate_affiliates_pro&tab=user_profile&affiliate_id='.$referralObject->affiliate_id).' " target="_blank">' . $indeed_db->get_username_by_wpuid( $indeed_db->get_uid_by_affiliate_id( $referralObject->affiliate_id ) ).'</a>');?></div>
        <div><?php echo esc_uap_content("<b>Amount: </b>" . uap_format_price_and_currency( $referralObject->currency, $referralObject->amount ));?></div>
        <div><?php echo esc_uap_content("<b>Client Username: </b>" .  $indeed_db->get_username_by_wpuid( $referralObject->refferal_wp_uid ));?></div>
        <div><?php echo esc_uap_content("<b>Description: </b>" . $referralObject->description);?></div>
        <div><?php echo esc_uap_content("<b>Referral Status: </b>");
          switch ($referralObject->payment){
            case 0:
              esc_html_e('Unpaid', 'uap');
              break;
            case 1:
              esc_html_e('Pending', 'uap');
              break;
            case 2:
              esc_html_e('Complete', 'uap');
              break;
          }?>
        </div>
    <?php endforeach;?>
<?php else :?>
    <?php esc_html_e( 'No referrals for this order.', 'uap' );?>
<?php endif;?>

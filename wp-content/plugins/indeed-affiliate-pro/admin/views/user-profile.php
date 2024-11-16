<div class="uap-wrapper uap-affiliate-userprofile">
    <div class="uap-stuffbox">
        <div class="uap-h3"><?php esc_html_e( 'Affiliate Profile', 'uap' );?></div>
        <div class="inside">
            <?php if ( empty($data) ):?>
                <h4><?php esc_html_e( 'No details for this Affiliate user.', 'uap' );?></h4>
            <?php else:?>
				<div class="row">
                	<div class="col-xs-12">
                    	<div class="uap-userprofile-mainname-wrapper">
                          <div class="row">
                           <div class="col-xs-1">
                           <?php if ( $data['ranking_possition'] > -1 ):?>
                           		<div class="uap-userprofile-top-wrapper">
                                		<img src="<?php echo UAP_URL;?>assets/images/uap_trophy.png" alt="<?php echo esc_attr($data['ranking_possition']);?>"/>
                                        <div class="uap-userprofile-top-position">
                                        	#<?php echo esc_html($data['ranking_possition']);?>
                                        </div>
                                        <div class="uap-userprofile-top-details">
                                        <?php esc_html_e( "based on performance", 'uap' );?>
                                        </div>
                                </div>
                            <?php endif;?>
                           </div>
                            <div class="col-xs-5">
                        	<div class="uap-userprofile-mainname">
                        	 <?php if ( !empty( $data['user_meta']['first_name']) && !empty( $data['user_meta']['last_name'] ) ):?>
                   				 <?php echo esc_html($data['user_meta']['first_name'].' '.$data['user_meta']['last_name']).' ('.esc_html($data['user_meta']['nickname']).')';?>
               				 <?php endif;?>
                       		</div>
                            <div class="uap-userprofile-links">
                            	<span class="uap-userprofile-links-label"><?php esc_html_e( "Affiliate Link", 'uap' );?>: </span><a href="<?php echo esc_url($data['affiliate_link']);?>" target="_blank"><?php echo esc_html($data['affiliate_link']);?></a> <span class="js-uap-copy uap-userprofile-copyclipboard"><?php esc_html_e( 'Copy to Clipboard', 'uap' );?></span> <div class="div-notice"><?php esc_html_e( '(for testing purpose use the affiliate link into a fresh incognito browser window)', 'uap' );?></div>
                            </div>
                            <div class="uap-userprofile-links">
                            	<span class="uap-userprofile-links-label"><?php esc_html_e( "Affiliate ID", 'uap' );?>: </span><?php echo esc_html($data['affiliate_id']); ?>
                            </div>
                            <?php if ( $data['custom_slug'] ):?>
                                <div class="uap-userprofile-links">
                                	<span class="uap-userprofile-links-label"><?php esc_html_e( "Custom Slug", 'uap' );?>: </span><?php echo esc_html($data['custom_slug']);?>
                                </div>
                            <?php endif;?>

                           </div>
                           <div class="col-xs-6">
                           	<div class="row uap-userprofile-rank-row">
                            	<div class="col-xs-5">
                                	<div class="uap-userprofile-rank uap-currentrank">
                                    	<div class="uap-userprofile-rank-title"><?php echo isset( $data['current_rank_data']['label'] ) ? esc_html($data['current_rank_data']['label']) : '';?>
                                        <?php if ( isset( $data['current_rank_data']['amount_type'] ) ):?>
                                         <div class="uap-userprofile-rank-reward">(<?php if ( $data['current_rank_data']['amount_type'] == 'percentage' ){
                                           echo esc_html($data['current_rank_data']['amount_value'].'%');
                                         }else{
                                           echo uap_format_price_and_currency($data['currency'], $data['current_rank_data']['amount_value']);
                                         } ?>
											  <?php esc_html_e( 'reward', 'uap' );?>)</div>
                                        <?php endif;?>
                                              </div>
                                        <ul>
                                            <?php if ( $data['bonus_enabled'] && !empty( $data['current_rank_data']['bonus'] ) ):?>
                                                <li><?php esc_html_e( 'Achievement Bonus', 'uap' );?>: <?php echo uap_format_price_and_currency($data['currency'], $data['current_rank_data']['bonus']);?></li>
                                            <?php endif;?>

                                            <?php if ( $data['sign_up_enabled'] ):?>

                                                <?php if ( empty( $data['current_rank_data']['sign_up_amount_value'] ) || $data['current_rank_data']['sign_up_amount_value'] < 0 ):?>
                                                    <li><?php esc_html_e( 'SignUp Referrals', 'uap' );?>: <?php echo uap_format_price_and_currency( $data['currency'], $data['default_sign_up_referrals'] ) . esc_html__( '(default value)', 'uap');?></li>
                                                <?php else : ?>
                                                    <li><?php esc_html_e( 'SignUp Referrals', 'uap' );?>: <?php echo uap_format_price_and_currency( $data['currency'], $data['current_rank_data']['sign_up_amount_value'] );?></li>
                                                <?php endif;?>

                                            <?php endif;?>

                                            <?php if ( $data['lifetime_commission'] ):?>
                                                <li>

                                                    <?php esc_html_e( 'LifeTime Comission', 'uap' );?>:
                                                    <?php if ( !empty( $data['current_rank_data']['lifetime_amount_value'] ) && $data['current_rank_data']['lifetime_amount_value'] > -1.00 ):?>
                                                        <?php if ( $data['current_rank_data']['lifetime_amount_type'] == 'percentage' ):?>
                                                            <?php echo esc_html($data['current_rank_data']['lifetime_amount_value']);?>%
                                                        <?php else :?>
                                                            <?php echo uap_format_price_and_currency( $data['currency'], $data['current_rank_data']['lifetime_amount_value'] );?>
                                                        <?php endif;?>
                                                    <?php else :?>
                                                      <?php if ( isset( $data['current_rank_data']['amount_type'] ) ):?>
                                                      <?php if ( $data['current_rank_data']['amount_type'] == 'percentage' ):?>
                                                          <?php echo esc_html($data['current_rank_data']['amount_value']);?>%
                                                      <?php else :?>
                                                          <?php echo uap_format_price_and_currency( $data['currency'], $data['current_rank_data']['amount_value'] );?>
                                                      <?php endif;?>
                                                      <?php endif;?>
                                                      <?php esc_html_e( '(Rank amount)', 'uap' );?>
                                                    <?php endif;?>

                                                </li>
                                            <?php endif;?>

                                            <?php if ( $data['reccuring_referrals'] ):?>
                                                <li>

                                                    <?php esc_html_e( 'Reccurring Referrals', 'uap' );?>:
                                                    <?php if ( !empty( $data['current_rank_data']['reccuring_amount_value'] ) && $data['current_rank_data']['reccuring_amount_value'] > -1.00 ):?>
                                                        <?php if ( $data['current_rank_data']['reccuring_amount_type'] == 'percentage' ):?>
                                                            <?php echo esc_html($data['current_rank_data']['reccuring_amount_value']);?>%
                                                        <?php else :?>
                                                            <?php echo uap_format_price_and_currency( $data['currency'], $data['current_rank_data']['reccuring_amount_value'] );?>
                                                    <?php endif;?>

                                                    <?php else :?>
                                                        <?php if ( isset( $data['current_rank_data']['amount_type'] ) ):?>
                                                          <?php if ( $data['current_rank_data']['amount_type'] == 'percentage' ):?>
                                                              <?php echo esc_html($data['current_rank_data']['amount_value']);?>%
                                                          <?php else :?>
                                                              <?php echo uap_format_price_and_currency( $data['currency'], $data['current_rank_data']['amount_value'] );?>
                                                          <?php endif;?>
                                                          <?php esc_html_e( '(Rank amount)', 'uap' );?>
                                                        <?php endif;?>
                                                    <?php endif;?>

                                                </li>
                                            <?php endif;?>

                                            <?php if ( $data['pay_per_click'] && !empty( $data['current_rank_data']['pay_per_click'] ) && $data['current_rank_data']['pay_per_click'] > -1.00 ):?>
                                                <li><?php esc_html_e( 'PPC Amount', 'uap' );?>: <?php echo uap_format_price_and_currency( $data['currency'], $data['current_rank_data']['pay_per_click'] );?></li>
                                            <?php endif;?>

                                            <?php if ( $data['cpm_commission'] && !empty( $data['current_rank_data']['cpm_commission'] ) && $data['current_rank_data']['cpm_commission'] > -1.00 ):?>
                                                <li><?php esc_html_e( 'CPM Amount', 'uap' );?>: <?php echo uap_format_price_and_currency( $data['currency'], $data['current_rank_data']['cpm_commission'] );?></li>
                                            <?php endif;?>

                                        </ul>
                                    </div>
                                </div>

                                <?php if ( $data['next_rank_data'] ):?>

                                <div class="uap-achievenextrank">
                                        	<?php esc_html_e( 'Next Rank', 'uap' );?>
                                            <div class="uap-achievement-condition">
                                            <div><?php esc_html_e('Achievement condition', 'uap');?></div>
                                            <?php
                                            $achieve_types= array(-1=>'...', 'referrals_number'=>'Number of Referrals', 'total_amount'=>'Total Amount');
;                                           $achieve = json_decode( $data['next_rank_data']['achieve'], TRUE);
                                            if ($achieve):
                                            for ($i=1; $i<=$achieve['i']; $i++):?>
                                              <div class="uap-admin-listing-ranks-achieve">
                                                <div><strong><?php echo esc_html($achieve_types[$achieve['type_' . $i]]);?></strong></div>
                                                <div><?php echo esc_html__('From: ', 'uap');
                                                  if ($achieve['type_' . $i]=='total_amount'){
                                                    echo uap_format_price_and_currency( $data['currency'], $achieve['value_' . $i] );
                                                  } else {
                                                      echo esc_html($achieve['value_' . $i]) . '';
                                                  }
                                                  ?></div>
                                              </div>
                                            <?php
                                              endfor;
                                            else:
                                              ?>
                                              <div class="uap-admin-listing-ranks-achieve">
                                                <?php esc_html_e('None', 'uap');?>
                                              </div>
                                              <?php
                                            endif;
                                            ?>
                                          </div>
                                        </div>
                                <div class="col-xs-2">

                                </div>
                                <div class="col-xs-5">
                                    <div class="uap-userprofile-rank uap-nextrank">
                                    	<div class="uap-userprofile-rank-title"><?php echo esc_html($data['next_rank_data']['label']);?>
                                        <div class="uap-userprofile-rank-reward">(<?php if ( $data['next_rank_data']['amount_type'] == 'percentage' ){
                                          echo esc_html($data['next_rank_data']['amount_value'].'%');
                                        }else{
                                          echo uap_format_price_and_currency( $data['currency'], $data['next_rank_data']['amount_value'] );
                                        }?>
											  <?php esc_html_e( 'reward', 'uap' );?>)</div>
                                        </div>
                                        <ul>

                                          <?php if ( $data['bonus_enabled'] && !empty( $data['next_rank_data']['bonus'] ) ):?>
                                              <li><?php esc_html_e( 'Achievement Bonus', 'uap' );?>: <?php echo uap_format_price_and_currency( $data['currency'], $data['next_rank_data']['bonus'] );?></li>
                                          <?php endif;?>

                                          <?php if ( $data['sign_up_enabled'] ):?>
                                              <?php if ( !empty( $data['next_rank_data']['sign_up_amount_value'] ) && $data['next_rank_data']['sign_up_amount_value'] > -1.00 ):?>
                                                  <li><?php esc_html_e( 'SignUp Referrals', 'uap' );?>: <?php echo uap_format_price_and_currency( $data['currency'], $data['next_rank_data']['sign_up_amount_value'] );?></li>
                                              <?php else :?>
                                                  <li><?php esc_html_e( 'SignUp Referrals', 'uap' );?>: <?php echo uap_format_price_and_currency( $data['currency'], $data['next_rank_data']['amount_value'] );?></li>
                                              <?php endif;?>
                                          <?php endif;?>

                                          <?php if ( $data['lifetime_commission'] ):?>
                                              <li>

                                                  <?php esc_html_e( 'LifeTime Comission', 'uap' );?>:
                                                  <?php if ( !empty( $data['next_rank_data']['lifetime_amount_value'] ) && $data['next_rank_data']['lifetime_amount_value'] > -1.00 ):?>
                                                      <?php if ( $data['next_rank_data']['lifetime_amount_type'] == 'percentage' ):?>
                                                          <?php echo esc_html($data['next_rank_data']['lifetime_amount_value']);?>%
                                                      <?php else :?>
                                                          <?php echo uap_format_price_and_currency( $data['currency'], $data['next_rank_data']['lifetime_amount_value'] );?>
                                                      <?php endif;?>
                                                  <?php else :?>
                                                      <?php if ( $data['next_rank_data']['amount_type'] == 'percentage' ):?>
                                                          <?php echo esc_html($data['next_rank_data']['amount_value']);?>%
                                                      <?php else :?>
                                                          <?php echo uap_format_price_and_currency( $data['currency'], $data['next_rank_data']['amount_value'] );?>
                                                      <?php endif;?>
                                                      <?php esc_html_e( '(Rank amount)', 'uap' );?>
                                                  <?php endif;?>

                                              </li>
                                          <?php endif;?>

                                          <?php if ( $data['reccuring_referrals'] ):?>
                                              <li>
                                                  <?php esc_html_e( 'Reccurring Referrals', 'uap' );?>:
                                                  <?php if ( !empty( $data['next_rank_data']['reccuring_amount_value'] ) && $data['current_rank_data']['reccuring_amount_value'] > -1.00 ):?>
                                                      <?php if ( $data['next_rank_data']['reccuring_amount_type'] == 'percentage' ):?>
                                                          <?php echo esc_html($data['next_rank_data']['reccuring_amount_value']);?>%
                                                      <?php else :?>
                                                          <?php echo uap_format_price_and_currency( $data['currency'], $data['next_rank_data']['reccuring_amount_value'] );?>
                                                      <?php endif;?>
                                                  <?php else :?>
                                                      <?php if ( $data['next_rank_data']['amount_type'] == 'percentage' ):?>
                                                          <?php echo esc_html($data['next_rank_data']['amount_value']);?>%
                                                      <?php else :?>
                                                          <?php echo uap_format_price_and_currency( $data['currency'], $data['next_rank_data']['amount_value'] );?>
                                                      <?php endif;?>
                                                      <?php esc_html_e( '(Rank amount)', 'uap' );?>
                                                  <?php endif;?>
                                              </li>
                                          <?php endif;?>

                                          <?php if ( $data['pay_per_click'] && !empty( $data['next_rank_data']['pay_per_click'] ) && $data['next_rank_data']['pay_per_click'] > -1.00 ):?>
                                              <li><?php esc_html_e( 'PPC Amount', 'uap' );?>: <?php echo uap_format_price_and_currency( $data['currency'], $data['next_rank_data']['pay_per_click'] );?></li>
                                          <?php endif;?>

                                          <?php if ( $data['cpm_commission'] && !empty( $data['next_rank_data']['cpm_commission'] ) && $data['next_rank_data']['cpm_commission'] > -1.00 ):?>
                                              <li><?php esc_html_e( 'CPM Amount', 'uap' );?>: <?php echo uap_format_price_and_currency( $data['currency'], $data['next_rank_data']['cpm_commission'] );?></li>
                                          <?php endif;?>


                                        </ul>
                                    </div>

                                </div>
                                <?php endif;?>

                            </div>
                           </div>
                         </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                	<div class="col-xs-7">
                    	<div class="uap-userprofile-profiledetails">
                        	<div class="uap-userprofile-sectiontitle"><?php esc_html_e( 'Profile details', 'uap' );?></div>
                            <div class="row">
                            <div class="col-xs-4">
                                  <div class="uap-userprofile-avatar">
                                  <?php if ( !empty( $data['avatar'] ) ):?>
                                      <img src="<?php echo esc_url($data['avatar']);?>" class="uap-avatar" alt="<?php echo esc_attr($data['user_name']);?>" />
                                  <?php endif;?>
                                  </div>
                                  <div class="uap-userprofile-buttons">
                                      <div class="uap-userprofile-button">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=ultimate_affiliates_pro&tab=affiliates&subtab=add_edit').'&id=' . $data['uid']);?>" target="_blank" class="button button-primary button-large">
                <?php esc_html_e( 'Edit Affiliate Profile', 'uap' );?></a>
                                      </div>
                                      <div class="uap-userprofile-button">
                                        <a href="<?php echo esc_url($data['public_profile_preview']);?>" target="_blank" class="button button-primary button-large">
                <?php esc_html_e( 'Access Affiliate Portal', 'uap' );?></a>
                                      </div>
                                  </div>
                            </div>
                            <div class="col-xs-8">
                        	<table class="form-table">
                            	<tbody>
                                	<tr class="form-field">
                                    	<th><?php esc_html_e( 'Username', 'uap' );?>:</th>
                                        <td> <?php echo esc_html($data['user_name']);?></td>
                                    </tr>
                                    <tr class="form-field">
                                    	<th><?php esc_html_e( 'Email', 'uap' );?>:</th>
                                        <td> <?php echo esc_html($data['user_email']);?></td>
                                    </tr>
                                    <tr class="form-field">
                                    	<th><?php esc_html_e( 'First Name', 'uap' );?>:</th>
                                        <td> <?php echo esc_html($data['user_meta']['first_name']);?></td>
                                    </tr>
                                    <tr class="form-field">
                                    	<th><?php esc_html_e( 'Last Name', 'uap' );?>:</th>
                                        <td> <?php echo esc_html($data['user_meta']['last_name']);?></td>
                                    </tr>
                                    <tr class="form-field">
                                    	<th><?php esc_html_e( 'WP Role', 'uap' );?>:</th>
                                        <td> <?php echo esc_html($data['role']);?></td>
                                    </tr>
                                    <tr class="form-field">
                                    	<th><?php esc_html_e( 'Member Since', 'uap' );?>:</th>
                                        <td> <?php echo esc_html($data['member_since']);?></td>
                                    </tr>
                                    <tr class="form-field">
                                    	<th><?php esc_html_e( 'Nickname', 'uap' );?>:</th>
                                        <td> <?php echo esc_html($data['user_meta']['nickname']);?></td>
                                    </tr>
                                    <tr class="form-field">
                                    	<th><?php esc_html_e( 'Biographical Info', 'uap' );?>:</th>
                                        <td> <?php echo esc_html($data['description']);?></td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-5">
                    	<div class="uap-userprofile-sectiontitle"><?php esc_html_e( 'Overall Perfromance', 'uap' );?></div>
                         <div class="uap-userprofile-stats">
                         		<table class="form-table">
                            	<tbody>
                                	<tr class="form-field">
                                    	<th>
                                        	<div><?php esc_html_e( 'Referrals', 'uap' );?></div>
                                          <?php $totalAmount = $data['referrals_stats']['verified_referrals_amount'] + $data['referrals_stats']['unverified_referrals_amount'];?>
                                          <?php $totalReferrals = $data['referrals_stats']['verified_referrals'] + $data['referrals_stats']['unverified_referrals'];?>
                                        	<div> <a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=referrals&affiliate_id=' . $data['affiliate_id'] );?>" target="_blank"><?php echo esc_html($totalReferrals);?> (<?php echo uap_format_price_and_currency( $data['currency'], $totalAmount );?>)</a></div>
                                        </th>
                                        <td>
                                        	<div><?php esc_html_e( 'Approved:', 'uap' );?>: <?php echo esc_html($data['referrals_stats']['verified_referrals']);?> (<?php echo uap_format_price_and_currency( $data['currency'], $data['referrals_stats']['verified_referrals_amount'] );?>)</div>
                                        	<div><?php esc_html_e( 'Pending:', 'uap' );?>: <?php echo esc_html($data['referrals_stats']['unverified_referrals']);?> (<?php echo uap_format_price_and_currency( $data['currency'], $data['referrals_stats']['unverified_referrals_amount'] );?>)</div>
                                        </td>
                                    </tr>
                                    <tr class="form-field">
                                        <?php $totalEarnings = $data['stats']['total_paid_referrals']  + $data['stats']['total_unpaid_referrals'];?>
                                        <?php
                                            if ( !isset( $data['stats']['total_paid'] ) ){
                                              $data['stats']['total_paid'] = '';
                                            }
                                            if ( !isset( $data['stats']['total_unpaid_referrals'] ) ){
                                              $data['stats']['total_unpaid_referrals'] = '';
                                            }
                                        ?>
                                        <th>
                                        	<div><?php esc_html_e( 'Earnings', 'uap' );?></div>
                                        	<div> <?php echo uap_format_price_and_currency( $data['currency'], $totalEarnings );?></div>
                                        </th>
                                        <td>
                                        	<div><?php esc_html_e( 'Paid:', 'uap' );?>: <a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=paid_referrals&affiliate=' . $data['affiliate_id'] );?>" target="_blank"><?php echo uap_format_price_and_currency( $data['currency'], $data['stats']['total_paid'] );?></a></div>
                                        	<div><?php esc_html_e( 'Unpaid:', 'uap' );?>: <a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=unpaid&affiliate=' . $data['affiliate_id'] );?>" target="_blank"><?php echo uap_format_price_and_currency( $data['currency'], $data['stats']['total_unpaid_referrals'] );?></a></div>
                                        </td>
                                    </tr>
                                    <tr class="form-field">
                                    	<th>
                                        	<div><?php esc_html_e( 'Clicks', 'uap' );?></div>
                                        	<div> <a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=visits&affiliate_id=' . $data['affiliate_id'] );?>" target="_blank"><?php echo esc_html($data['stats']['visits']);?></a></div>
                                        </th>
                                        <td>
                                        	<div><?php esc_html_e( 'Conversion:', 'uap' );?>: <?php echo esc_html($data['stats']['conversions']);?></div>
                                        	<div><?php esc_html_e( 'Success Rate:', 'uap' );?>: <?php echo esc_html($data['stats']['success_rate']);?>%</div>
                                        </td>
                                    </tr>
                                    <tr class="form-field">
                                    	<th>
                                        	<div><?php esc_html_e( 'Payout', 'uap' );?></div>
                                        	<div> <?php echo uap_format_price_and_currency( $data['currency'], $data['payment_stats']['paid_payments_value'] );?></div>
                                        </th>
                                        <td>
                                        	<div><?php esc_html_e( 'Completed Transactions:', 'uap' );?>: <?php echo esc_html($data['count_payments_completed']);?></div>
                                        	<div><?php esc_html_e( 'Pending Transactions:', 'uap' );?>: <?php echo esc_html($data['count_payments_pending']);?></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                         </div>

                         <?php if ( !empty( $data['statsForLast30'] ) ):?>
                            <div class="uap-userprofile-chart-wrapper" ><canvas id="chart-1" ></canvas></div>
                         <?php endif;?>
                    </div>
                </div>
                <div class="row">

                	<div class="col-xs-8">
                  	<div class="uap-userprofile-sectiontitle"><?php esc_html_e( 'Payout Details', 'uap' );?></div>
                  	<table class="form-table">
                      <tbody>
                          <tr class="form-field">
                              <th><?php esc_html_e( 'Payout Method', 'uap' );?>:</th>
                              <td>
                                  <?php switch( $data['payments_settings']['uap_affiliate_payment_type'] ){
                                          case 'bt':?>
                                          <?php esc_html_e( 'Direct Deposit', 'uap' );?>
                                        <?php break;
                                          case 'paypal':?>
                                          <?php esc_html_e( 'PayPal', 'uap' );?>
                                        <?php break;
                                          case 'stripe':?>
                                          <?php esc_html_e( 'Stripe', 'uap' );?>
                                        <?php break;
                                          case 'stripe_v2':?>
                                            <?php esc_html_e( 'Stripe', 'uap' );?>
                                        <?php break;?>
                                        <?php case 'stripe_v3':?>
                                        <?php esc_html_e( 'Stripe', 'uap' );?>
                                      <?php break;?>
                                  <?php }?>
                              </td>
                          </tr>
                          <tr class="form-field">
                              <?php switch( $data['payments_settings']['uap_affiliate_payment_type'] ){
                                      case 'bt':?>
                                      <th><?php esc_html_e( 'Account:', 'uap' );?></th>
                                      <td><?php echo esc_html($data['payments_settings']['uap_affiliate_bank_transfer_data']);?></td>
                                    <?php break;
                                      case 'paypal':?>
                                      <th><?php esc_html_e( 'E-mail address:', 'uap' );?></th>
                                      <td><?php echo esc_html($data['payments_settings']['uap_affiliate_paypal_email']);?></td>
                                    <?php break;
                                      case 'stripe':?>
                                      <th><?php esc_html_e( 'Stripe Name:', 'uap' );?></th>
                                      <td><?php echo esc_html($data['payments_settings']['uap_affiliate_stripe_name']);?></td>
                                      </tr>
                                      <tr class="form-field">
                                        <th><?php esc_html_e( 'Card number:', 'uap' );?></th>
                                        <td><?php echo esc_html($data['payments_settings']['uap_affiliate_stripe_card_number']);?></td>
                                      </tr>
                                      <tr class="form-field">
                                        <th><?php esc_html_e( 'Expiration month:', 'uap' );?></th>
                                        <td><?php echo esc_html($data['payments_settings']['uap_affiliate_stripe_expiration_month']);?></td>
                                      </tr>
                                      <tr class="form-field">
                                        <th><?php esc_html_e( 'Expiration year:', 'uap' );?></th>
                                        <td><?php echo esc_html($data['payments_settings']['uap_affiliate_stripe_expiration_year']);?></td>
                                      </tr>
                                      <tr class="form-field">
                                        <th><?php esc_html_e( 'Card type:', 'uap' );?></th>
                                        <td><?php echo esc_html($data['payments_settings']['uap_affiliate_stripe_card_type']);?></td>
                                    <?php break;?>
                                    <?php case 'stripe_v2':?>
                                      <th></th>
                                      <td></td>
                                    <?php break;?>
                                    <?php case 'stripe_v3':
                                        ?>
                                        <th><?php esc_html_e( 'Stripe Account:', 'uap' );?></th>
                                        <td>
                                        <?php
                                        $accountId = get_user_meta( $data['uid'], 'uap_stripe_v3_user_account_id', true );
                            						if ( $accountId != false && $accountId != '' ):
                            							$stripe_link = '';
                            							$sandbox = get_option( 'uap_stripe_v3_sandbox' );
                            							if ( $sandbox ){
                            									$stripe_link = 'https://dashboard.stripe.com/test/connect/accounts/'.$accountId;
                            							}else{
                            									$stripe_link = 'https://dashboard.stripe.com/connect/accounts/'.$accountId;
                            							}
                            							?>
                            							<div class="uap-payment-details-do-payment">
                            									<a href="<?php echo esc_url($stripe_link);?>" target="_blank"><?php
                            									_e( 'View Stripe Affiliate Account', 'uap');
                            							?></a></div>
                            					<?php else :?>
                            						<div class="uap-payment-details-do-payment"><?php esc_html_e('Incomplete Payment Settings', 'uap');?></div>
                                      <?php endif;?>
                                      </td>
                                    <?php break;?>
                              <?php }?>
                          </tr>
                      </tbody>
                    </table>
                  </div>


                    <?php if ( !empty( $data['campaigns'] ) ):?>
                      <div class="col-xs-4">
                    	<div class="uap-userprofile-sectiontitle"><?php esc_html_e( 'Campaigns', 'uap' );?></div>
                        <table class="form-table">
                          <tbody>
                              <?php foreach ( $data['campaigns'] as $campaignObject ):?>
                                  <tr class="form-field">
                                      <th width="40%" class="uap-text-align-center"><?php echo esc_html($campaignObject->name);?></th>
                                      <td width="20%"><?php esc_html_e('Visists: ', 'uap');?><?php echo esc_html($campaignObject->visit_count);?></td>
                                      <td width="20%"><?php esc_html_e('Unique visits: ', 'uap');?><?php echo esc_html($campaignObject->unique_visits_count);?></td>
                                      <td width="20%"><?php esc_html_e('Referrals: ', 'uap');?><?php echo esc_html($campaignObject->referrals);?></td>
                                  </tr>
                              <?php endforeach;?>
                          </tbody>
                        </table>
                	  </div>
                    <?php endif;?>
                </div>
 				<div class="row uap-userprofile-specialrow">

                  <?php if ( $data['coupons'] ):?>
                      <div class="col-xs-4 uap-userprofile-box">
                          <div class="uap-userprofile-sectiontitle"><?php esc_html_e( 'Assigned Coupons', 'uap' );?> <span>(<a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=coupons' );?>" target="_blank"><?php esc_html_e( 'check settings', 'uap' );?></a>)</span></div>
                          <ul class="uap-userprofile-list">
                              <?php foreach ( $data['coupons'] as $couponData ):?>
                                  <?php $couponSettings = unserialize( $couponData['settings'] );?>
                                  <li><?php echo esc_html($couponData['code']);?>
                                      <?php if ( isset( $couponSettings['amount_type'] ) && $couponSettings['amount_type'] == 'percentage' ):?>
                                          (<?php echo esc_html($couponSettings['amount_value']);?>%) -
                                      <?php else :?>
                                          (<?php echo uap_format_price_and_currency( $data['currency'], $couponSettings['amount_value'] );?>) -
                                      <?php endif;?>
                                  <a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=coupons&add_edit=' . $couponData['code'] );?>" target="_blank"><?php esc_html_e( 'Edit', 'uap' );?></a> |
                                  <span class='uap-js-delete-coupons-link uap-delete-span' data-id='<?php echo esc_attr($couponData['id']);?>' ><?php esc_html_e( 'Delete', 'uap' );?></span>
                                  </li>
                              <?php endforeach;?>
                              <li><a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=coupons&add_edit=0' );?>" target="_blank"><?php esc_html_e( 'Add New', 'uap' );?></a></li>
                          </ul>
                      </div>
                  <?php endif;?>

                  <?php if ( $data['referrer_links'] ):?>
                      <div class="col-xs-4 uap-userprofile-box">
                            	<div class="uap-userprofile-sectiontitle"><?php esc_html_e( 'Referrer Links', 'uap' );?> <span>(<a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=simple_links' );?>" target="_blank"><?php esc_html_e( 'check settings', 'uap' );?></a>)</span></div>
                              <ul class="uap-userprofile-list">
                                <?php foreach ( $data['referrer_links'] as $referrerLinks ):?>
                                     <li><a href="<?php echo esc_url($referrerLinks['url']);?>" target="_blank"><?php echo esc_url($referrerLinks['url']);?></a> - <span class='uap-js-delete-referrals-link uap-delete-span' data-id='<?php echo esc_attr($referrerLinks['id']);?>' ><?php esc_html_e( 'Delete', 'uap' );?></span></li>
                                <?php endforeach;?>
                              </ul>
                      </div>
                  <?php endif;?>

                  <?php if ( $data['landing_pages'] ):?>
                      <div class="col-xs-4 uap-userprofile-box">
                          	<div class="uap-userprofile-sectiontitle"><?php esc_html_e( 'Landing Page', 'uap' );?> <span>(<a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=landing_pages' );?>" target="_blank"><?php esc_html_e( 'check settings', 'uap' );?></a>)</span></div>
                            <ul class="uap-userprofile-list">
                              <?php foreach ( $data['landing_pages'] as $landingPage ):?>
                            	     <li><a href="<?php echo get_permalink($landingPage->ID);?>" target="_blank"><?php echo esc_html($landingPage->post_title);?></a> - <a href="<?php echo admin_url('post.php?post='.$landingPage->ID.'&action=edit');?>" target="_blank"><?php esc_html_e( 'Edit', 'uap' );?></a> |
                                        <span class='uap-js-delete-landing-page uap-delete-span' data-id='<?php echo esc_attr($landingPage->ID);?>' ><?php esc_html_e( 'Delete', 'uap' );?></span>
                                   </li>
                              <?php endforeach;?>
                            </ul>
                      </div>
                  <?php endif;?>

          </div>





            <?php endif;?>
        </div>
    </div>

    <?php if ( $data['mlm'] ):?>
        <?php echo esc_uap_content($data['mlm']);?>
    <?php endif;?>
</div>

<span class="uap-js-user-profile-copy-message" data-value='<?php esc_html_e( 'Copied to clipboard.', 'uap' );?>'></span>

<?php if ( !empty( $data['statsForLast30'] ) ):?>
<?php wp_enqueue_script( 'uap-moment.js', UAP_URL . 'assets/js/moment.min.js', ['jquery'], false );?>
<?php wp_enqueue_script( 'uap-chart.js', UAP_URL . 'assets/js/chart.min.js', ['jquery'], false );?>
<?php wp_enqueue_script( 'uap-public-overview', UAP_URL . 'assets/js/public-overview.js', ['jquery'], false );?>

<span class="uap-js-overview-earnings-received-label" data-value="<?php echo esc_html__( 'Earnings received', 'uap' ) . ' ('.$data['currency'].')';?>"></span>
<span class="uap-js-overview-earnings-label" data-value="<?php esc_html_e('Earnings', 'uap');?>"></span>

<?php
foreach( $data['statsForLast30'] as $date => $amount ):?>
		<span class="uap-js-overview-stats-last-30"
		data-date="<?php echo uap_convert_date_to_us_format($date);?>"
		data-amount="<?php echo uap_format_price_and_currency($data['currency'], $amount );?>"
		data-base_amount="<?php echo esc_attr($amount);?>"
		<?php
				$temporaryDate = explode( '-', $date);
				$day = isset($temporaryDate[2]) ? $temporaryDate[2] : $date;
				echo esc_attr("data-label='$day' ");
		?>
		></span>
<?php endforeach;?>


<?php endif;?>

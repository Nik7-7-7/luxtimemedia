<div class="uap-ap-wrap">

<?php if (!empty($data['title'])):?>
	<h3><?php echo esc_uap_content($data['title']);?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>

<div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e("Your Discount Coupons", 'uap');?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                <?php esc_html_e("Whenever a buyer makes a purchase with that coupon, you will automatically receive revenue. Promote available coupons to get more rewards.", 'uap');?>
                </div>
             </div>
                <div class="uap-row ">
            	<div class="uap-col-xs-12">
					<?php if (!empty($data['codes'])) : ?>
                        <table class="uap-account-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Discount Code', 'uap');?></th>
                                    <th><?php esc_html_e('Discount value', 'uap');?></th>
                                    <th><?php esc_html_e('Source', 'uap');?></th>
                                    <th><?php esc_html_e('Reward', 'uap');?></th>
                                </tr>
                            </thead>
                            <tbody class="uap-alternate">
                                <?php foreach ($data['codes'] as $arr) : ?>
                                    <tr>
                                        <td><?php echo esc_html($arr['code']);?></td>
																				<td><?php
																						if ( isset( $arr['customer_discount_type'] ) && isset( $arr['customer_discount_value'] ) ){
																								if ( $arr['customer_discount_type'] == 'percentage' || $arr['customer_discount_type'] =='percent' ){
																										echo esc_uap_content($arr['customer_discount_value'] . '%');
																								} else {
																										echo uap_format_price_and_currency(uapCurrency($data['currency']), $arr['customer_discount_value'] );
																								}
																						}
																				?></td>
                                        <td><?php echo uap_service_type_code_to_title($arr['type']);?></td>
                                        <td><?php
                                            $settings = unserialize($arr['settings']);
                                            if ($settings && isset($settings['amount_value']) && $settings['amount_value'] != '' ){
                                                if ($settings['amount_type']=='flat'){
                                                    echo uap_format_price_and_currency(uapCurrency($data['currency']), $settings['amount_value']);
                                                } else {
                                                    echo esc_uap_content($settings['amount_value'] . '%');
                                                }
                                            } else if ( isset( $data['rank_data']['amount_type'] ) ) {
																								if ( $data['rank_data']['amount_type']=='percentage'){
																									//rank percentage
																								  echo esc_uap_content($data['rank_data']['amount_value'] . '%');
																								} else {
																									//rank flat
																									echo uap_format_price_and_currency( uapCurrency($data['currency']), $data['rank_data']['amount_value'] );
																								}
																						}
                                        ?></td>
                                    </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>

                    <?php else :?>
                     <div class="uap-warning-box uap-extra-margin-top"><?php esc_html_e('No Coupons have been assigned to your account yet.', 'uap');?></div>
                    <?php endif;?>
         </div>
    </div>
    </div>
</div>
</div>
<?php

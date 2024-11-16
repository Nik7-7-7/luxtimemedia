<div class="uap-account-wallet-tab">
<div class="uap-ap-wrap">

<?php if (!empty($data['title'])):?>
	<h3><?php echo esc_uap_content($data['title']);?></h3>
<?php endif;?>
<?php
	$stats = 0;
	if(isset($data['stats']['wallet'])){
		$stats = $data['stats']['wallet'];
	}
?>



<div class="uap-row">

	<div class="uapcol-md-2 uap-account-wallet-tab1">
		<div class="uap-account-no-box uap-account-box-lightgray">
			<div class="uap-account-no-box-inside">
				<div class="uap-count"> <?php echo uap_format_price_and_currency($data['currency'], round($stats, 2) ); ?> </div>
				<div class="uap-detail"><?php echo esc_html__('Available Wallet Credit', 'uap'); ?></div>
			</div>
		</div>
	</div>

	<div class="uapcol-md-2 uap-account-wallet-tab2">
		<div class="uap-account-no-box uap-account-box-red">
			<div class="uap-account-no-box-inside">
				<div class="uap-count"> <?php echo uap_format_price_and_currency($data['currency'], round($data['stats']['unpaid_payments_value'], 2));?> </div>
				<div class="uap-detail"><?php echo esc_html__('Available Deposit based on your Earnings', 'uap'); ?></div>
                <div class="uap-subnote"><?php echo esc_html__('what can be converted in Credits into your Wallet', 'uap'); ?></div>
			</div>
		</div>
	</div>

</div>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>
<div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php echo esc_html__('Wallet Credit', 'uap'); ?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                   <div class="uap-account-detault-message">
                      <div><?php esc_html_e('You can deposit your unpaid referrals in your wallet. You can select a service and an amount to deposit, then you will receive a coupon for that service for that amount, which you can use to purchase items etc', 'uap');?></div>
                  </div>
          		</div>
             </div>
        </div>
     </div>
<?php if ($data['stats']['unpaid_payments_value'] && $data['stats']['unpaid_payments_value']>=$settings['uap_wallet_minimum_amount']):?>
<div class="uap-profile-box-wrapper">
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                   <div class="uap-account-detault-message">
                     <a href="<?php echo esc_url($data['add_new']);?>" class="uap-addd-to-wallet"><?php esc_html_e('Add New Credit', 'uap');?></a>
                  </div>
          		</div>
             </div>
        </div>
     </div>

<?php endif;?>

<?php if ($data['items']):?>
	<table class="uap-account-table">
		<thead>
			<tr>
				<th><?php esc_html_e('Coupon Code', 'uap');?></th>
				<th><?php esc_html_e('Service', 'uap');?></th>
				<th><?php esc_html_e('Amount', 'uap');?></th>
				<th><?php esc_html_e('Action', 'uap');?></th>
			</tr>
		</thead>
	<?php foreach ($data['items'] as $k=>$v):?>
		<tr>
			<td><strong><?php echo esc_uap_content($v['code']);?></strong></td>
			<td><?php echo uap_service_type_code_to_title($v['type']);?></td>
			<td><?php echo uap_format_price_and_currency($data['currency'], $v['amount']);?></td>
			<td><span onClick="uapRemoveWalletItem('<?php echo esc_attr($v['type']);?>', '<?php echo esc_attr($v['code']);?>');" class="uap-pointer"><?php esc_html_e('Remove', 'uap');?></span></td>
		</tr>
	<?php endforeach;?>
	</table>
    <?php else: ?>
     <div class="uap-profile-box-wrapper">
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                   <div class="uap-account-detault-message">
                      <div><?php esc_html_e('Once you will convert your first Earnings into your Wallet you will find listed here all your credits.', 'uap');?></div>
                  </div>
          		</div>
             </div>
        </div>
     </div>
<?php endif;?>
</div>
</div>

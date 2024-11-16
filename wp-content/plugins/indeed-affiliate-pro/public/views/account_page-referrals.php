<div class="uap-account-referrals-tab">
<div class="uap-ap-wrap">

<?php if (!empty($data['title'])):?>
	<h3><?php echo esc_uap_content($data['title']);?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>

<?php if ((!empty($data['items']) && is_array($data['items'])) || !empty($data['filtered'])): ?>
	<div>
	<?php if (!empty($data['items']) && is_array($data['items'])): ?>
	<div class="uap-row">
		<div class="uapcol-md-3 uap-account-referrals-tab1">
			<div class="uap-account-no-box uap-account-box-lightgray"><div class="uap-account-no-box-inside"><div class="uap-count"><?php echo uap_format_price_and_currency(uapCurrency($data['currency']), $data['stats']['verified_referrals_amount']);?></div><div class="uap-detail"><?php esc_html_e("Approved Referrals Amount", 'uap');?></div></div></div>
		</div>
		<div class="uapcol-md-3 uap-account-referrals-tab2">
			<div class="uap-account-no-box uap-account-box-lightyellow"><div class="uap-account-no-box-inside"><div class="uap-count"><?php echo uap_format_price_and_currency(uapCurrency($data['currency']), $data['stats']['unverified_referrals_amount']);?></div><div class="uap-detail"><?php esc_html_e("Pending Referrals Amount", 'uap');?></div></div></div>
		</div>
		<div class="uapcol-md-3 uap-account-referrals-tab3">
			<div class="uap-account-no-box uap-account-box-lightblue"><div class="uap-account-no-box-inside"><div class="uap-count"><?php echo esc_uap_content($data['stats']['referrals']);?></div><div class="uap-detail"><?php esc_html_e('Total Number of Referrals', 'uap');?></div></div></div>
		</div>
	</div>
	<?php endif; ?>
    <div class="uap-profile-box-wrapper">
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                   <div class="uap-account-detault-message">
                   		<?php esc_html_e('Here are listed only Unpaid Referrals that have not been withdrawn yet. For a complete list of referrals check ', 'uap');?>
              					<a href="<?php echo esc_url($data['full_referrals_url']);?>">
			  						<?php esc_html_e('this section', 'uap');?>
              					</a>
                  </div>
          		</div>
             </div>
        </div>
    <div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e("Rewards and Commissions", 'uap');?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                <div class="uap-account-referrals-filter">
					<?php echo esc_uap_content($data['filter']);?>
    			</div>
		<?php if (!empty($data['items']) && is_array($data['items'])): ?>
		<table class="uap-account-table">
			  <thead>
				<tr>
					<th class="uap-account-referrals-table-col1"><?php esc_html_e("ID", 'uap');?></th>
					<th class="uap-account-referrals-table-col2"><?php esc_html_e("Campaign", 'uap');?></th>
					<th class="uap-account-referrals-table-col3"><?php esc_html_e("Amount", 'uap');?></th>
					<th class="uap-account-referrals-table-col4"><?php esc_html_e("From", 'uap');?></th>
					<?php if (!empty($data['print_source_details'])):?>
						<th class="uap-account-referrals-table-col5"><?php esc_html_e('Source Details', 'uap');?></th>
					<?php endif;?>
					<th class="uap-account-referrals-table-col6"><?php esc_html_e("Description", 'uap');?></th>
					<th class="uap-account-referrals-table-col7"><?php esc_html_e("Received on", 'uap');?></th>
					<th class="uap-account-referrals-table-col8"><?php esc_html_e("Status", 'uap');?></th>
				</tr>
			  </thead>
			  <tbody class="uap-alternate">
			<?php foreach ($data['items'] as $array) : ?>
				<tr>
					<td class="uap-account-referrals-table-col1"><?php echo esc_uap_content($array['id']);?></td>
					<td class="uap-account-referrals-table-col2"><?php
						if ($array['campaign']) {
							echo esc_uap_content($array['campaign']);
						} else {
							echo esc_uap_content('-');
						}
					?></td>
					<td  class="uap-account-referrals-table-col3"><strong><?php echo uap_format_price_and_currency(uapCurrency($array['currency']), $array['amount']);?></strong></td>
					<td class="uap-account-referrals-table-col4"><?php echo (empty($array['source'])) ? '' : uap_service_type_code_to_title($array['source']);?></td>
					<?php if (!empty($data['print_source_details'])):?>
						<td class="uap-account-referrals-table-col5"><?php
							if ($indeed_db->referral_has_source_details($array['id'])):
								$url = add_query_arg('reference', $array['id'], $data['source_details_url']);
								?>
								<a href="<?php echo esc_url($url);?>" target="_blank"><?php esc_html_e('View', 'uap');?></a>
								<?php
							else :
								echo esc_html('-');
							endif;
						?></td>
					<?php endif;?>
					<td class="uap-account-referrals-table-col6"><?php echo esc_uap_content($array['description']);?></td>
					<td class="uap-account-referrals-table-col7"><?php echo uap_convert_date_to_us_format($array['date']);?></td>
					<td class="uap-special-label uap-account-referrals-table-col8"><?php
						if ($array['status']==0){
							echo '<div class="uap-status uap-status-failed">'.esc_html__('Rejected', 'uap').'</div>';
						} else if ($array['status']==1){
							echo '<div class="uap-status uap-status-inactive">'.esc_html__('Pending', 'uap').'</div>';
						} else if ($array['status']==2){
							echo '<div class="uap-status uap-status-active">'.esc_html__('Approved', 'uap').'</div>';
						}
					?></td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
		<?php else: ?>
			 <div class="uap-account-detault-message">
						<div><?php esc_html_e('No Referrals found for your selection.', 'uap');?></div>
				</div>
		<?php endif;?>
			<?php if (!empty($data['pagination'])):?>
                <?php echo esc_uap_content($data['pagination']);?>
            <?php endif;?>
        </div>
        </div>
        </div>
        </div>
	</div>
    <?php else: ?>
    	   <div class="uap-account-detault-message">
              <div><?php esc_html_e('Here you will see all your Rewards and Commission that will be received based on your activity. Start your Affiliate campaign to earn commission.', 'uap');?></div>
          </div>
<?php endif;?>

</div>
</div>
</div>

<div class="uap-ap-wrap">

<?php if (!empty($data['title'])):?>
	<h3><?php echo esc_uap_content($data['title']);?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>

<?php if ((!empty($data['items']) && is_array($data['items'])) || !empty($data['filtered'])): ?>
	<div>
    <div class="uap-profile-box-wrapper">
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                <div class="uap-account-referrals-filter">
					<?php echo esc_uap_content($data['filter']);?>
    			</div>
                </div>
            </div>
        </div>
    </div>
		<?php if (!empty($data['items']) && is_array($data['items'])): ?>
    <table class="uap-account-table">
			  <thead>
				<tr>
					<th><?php esc_html_e("Campaign", 'uap');?></th>
					<th><?php esc_html_e("Amount", 'uap');?></th>
					<th><?php esc_html_e("From", 'uap');?></th>
					<th><?php esc_html_e("Description", 'uap');?></th>
					<th><?php esc_html_e("Date", 'uap');?></th>
					<th><?php esc_html_e('Payment', 'uap');?></th>
					<th><?php esc_html_e("Status", 'uap');?></th>
				</tr>
			  </thead>
			  <tbody class="uap-alternate">
			<?php foreach ($data['items'] as $array) : ?>
				<tr>
					<td><?php
						if ($array['campaign']) {
							echo esc_uap_content($array['campaign']);
						} else {
							echo esc_html('-');
						}
					?></td>
					<td><strong><?php echo uap_format_price_and_currency(uapCurrency($array['currency']), $array['amount']);?></strong></td>
					<td><?php echo (empty($array['source'])) ? '' : uap_service_type_code_to_title($array['source']);?></td>
					<td><?php echo esc_uap_content($array['description']);?></td>
					<td><?php echo uap_convert_date_to_us_format($array['date']);?></td>
					<td><?php
						switch ($array['payment']){
							case 0:
								echo '<div class="uap-status uap-status-processing">'.esc_html__('Unpaid', 'uap').'</div>';
								break;
							case 1:
								echo '<div class="uap-status uap-status-inactive">'.esc_html__('Pending', 'uap').'</div>';
								break;
							case 2:
								echo '<div class="uap-status uap-status-active">'.esc_html__('Paid', 'uap').'</div>';
								break;
						}
					?></td>
					<td class="uap-special-label"><?php
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
	</div>
    <?php else: ?>
    	   <div class="uap-account-detault-message">
              <div><?php esc_html_e('Here you will see all your Rewards and Commission that will be received based on your activity. Start your Affiliate campaing to earn commission.', 'uap');?></div>
          </div>
<?php endif;?>

<?php if (!empty($data['pagination'])):?>
	<?php echo esc_uap_content($data['pagination']);?>
<?php endif;?>
</div>

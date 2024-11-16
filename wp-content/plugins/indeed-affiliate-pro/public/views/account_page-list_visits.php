<div class="uap-account-clicks-tab">
<div class="uap-ap-wrap">
<?php if (!empty($data['title'])):?>
	<h3><?php echo esc_uap_content($data['title']);?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>
	<div class="uap-row">
		<div class="uapcol-md-3 uap-account-visits-tab1">
			<div class="uap-account-no-box uap-account-box-lightgray"><div class="uap-account-no-box-inside"><div class="uap-count"><?php echo esc_html($data['stats']['visits']);?></div><div class="uap-detail"><?php esc_html_e('Total Numbers of Clicks', 'uap');?></div>
            <div class="uap-subnote"><?php echo esc_html__('How many times your affiliate link have been used', 'uap'); ?></div></div></div>
		</div>
		<div class="uapcol-md-3 uap-account-visits-tab2">
			<div class="uap-account-no-box uap-account-box-red"><div class="uap-account-no-box-inside"><div class="uap-count"><?php echo esc_html($data['stats']['conversions']);?></div><div class="uap-detail"><?php esc_html_e('Conversions', 'uap');?></div>
                <div class="uap-subnote"><?php echo esc_html__('If customer successfully completes a certain action', 'uap'); ?></div></div></div>
		</div>
		<div class="uapcol-md-3 uap-account-visits-tab3">
			<div class="uap-account-no-box uap-account-box-lightblue"><div class="uap-account-no-box-inside"><div class="uap-count"><?php echo esc_uap_content($data['stats']['success_rate'] . '%');?></div><div class="uap-detail"><?php esc_html_e('Success Rate', 'uap');?></div></div></div>
		</div>
	</div>
	<?php if ((!empty($data['items']) && is_array($data['items'])) || !empty($data['filtered'])):?>
    <div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e("Clicks History", 'uap');?></span></div>
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
					<th><?php esc_html_e("Landing Page", 'uap');?></th>
					<th><?php esc_html_e("From Page", 'uap');?></th>
					<th><?php esc_html_e("Browser", 'uap');?></th>
					<th><?php esc_html_e("Device", 'uap');?></th>
					<th><?php esc_html_e("Date", 'uap');?></th>
					<th><?php esc_html_e("Status", 'uap');?></th>
				</tr>
			</thead>
			<tbody class="uap-alternate">
			<?php foreach ($data['items'] as $array) : ?>
				<tr>
					<td><a href="<?php echo esc_url($array['url']);?>" target="_blank"><?php echo esc_url($array['url']);?></a></td>
					<?php if ( isset( $array['ref_url'] ) && $array['ref_url'] !== '' ):?>
							<td><a href="<?php echo esc_url($array['ref_url']);?>" target="_blank"><?php echo esc_url($array['ref_url']);?></a></td>
					<?php else :?>
							<td>-</td>
					<?php endif;?>
					<td><?php echo esc_uap_content($array['browser']);?></td>
					<td><?php echo esc_uap_content($array['device']);?></td>
					<td><?php echo uap_convert_date_to_us_format($array['visit_date']);?></td>
					<td class="uap-special-label uap-text-align-center"><?php
						if ($array['referral_id']){
								echo '<div class="uap-status uap-status-active">'.esc_html__('Converted', 'uap').'</div>';

						}else{
								echo '<div class="uap-status uap-status-inactive">'.esc_html__('Just Visit', 'uap').'</div>';
						}
					?></td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
		<?php else: ?>
			 <div class="uap-account-detault-message">
						<div><?php esc_html_e('No Clicks found for your selection.', 'uap');?></div>
				</div>
		<?php endif;?>
        </div>
        </div>
        </div>
        </div>
			<?php else: ?>
				 <div class="uap-account-detault-message">
							<div><?php esc_html_e('Here you will see all your Clicks that will be received based on your activity. Start your Affiliate campaing to earn commission.', 'uap');?></div>
					</div>
	<?php endif;?>
</div>

<?php if (!empty($data['pagination'])):?>
	<?php echo esc_uap_content($data['pagination']);?>
<?php endif;?>
</div>

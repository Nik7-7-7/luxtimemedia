<?php
wp_enqueue_script( 'indeed_sweetalert_js', UAP_URL . 'assets/js/sweetalert.js', [], 8.3 );
wp_enqueue_style( 'indeed_sweetalert_css', UAP_URL . 'assets/css/sweetalert.css' );
?>
<div class="uap-ap-wrap uap-js-list-affiliate-links-wrapp">

	<?php if ((!empty($data['affiliate_links']) && is_array($data['affiliate_links'])) || !empty($data['affiliate_links'])): ?>
    <div class="uap-profile-box-wrapper">
    		<div class="uap-profile-box-title"><span><?php esc_html_e("Generated Affiliate Links", 'uap');?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
								<table class="uap-account-table">
									<thead>
										<tr>
											<th><?php esc_html_e("Landing Page", 'uap');?></th>
											<th><?php esc_html_e("Affiliate Link", 'uap');?></th>
											<th><?php esc_html_e("Campaign", 'uap');?></th>
											<th><?php esc_html_e("Created Date", 'uap');?></th>
											<th><?php esc_html_e("Actions", 'uap');?></th>
										</tr>
									</thead>
									<tbody class="uap-alternate">
									<?php foreach ($data['affiliate_links'] as $array) : ?>
										<tr>
											<td><a href="<?php echo esc_url($array['base_url']);?>" target="_blank"><?php echo esc_url($array['base_url']);?></a></td>
											<td><?php echo esc_url($array['affiliate_url']);?></td>
											<td><?php echo esc_uap_content($array['campaign']);?></td>
											<td><?php
											$date = new \DateTime();
											$date->setTimestamp( $array['create_date'] );
											$date->setTimezone( new \DateTimeZone('UTC') );
											$time = $date->format('Y-m-d H:i:s');
											echo uap_convert_date_to_us_format($time);?></td>
											<td><span class="uap-js-list-affiliate-links-copy" data-confirm="<?php esc_html_e( 'copied to clipboard', 'uap' );?>" data-link="<?php echo esc_url($array['affiliate_url']);?>" ><?php esc_html_e("Copy Link", 'uap');?></span> <span class="uap-js-list-affiliate-links-remove" data-id="<?php echo esc_html($array['id']);?>"><?php esc_html_e("Remove", 'uap');?></span></td>
										</tr>
									<?php endforeach;?>
									</tbody>
								</table>
        </div>
        </div>
        </div>
        </div>

	<?php endif;?>
</div>

<?php if (!empty($data['pagination'])):?>
	<?php echo esc_uap_content($data['pagination']);?>
<?php endif;?>

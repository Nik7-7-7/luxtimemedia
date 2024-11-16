<div class="uap-js-general-messages"
data-referrals="<?php esc_html_e('Are you sure you want to delete this Referral?', 'uap');?>"
data-general_delete="<?php esc_html_e('Delete This ', 'uap');?>"
data-affiliates="<?php esc_html_e('Delete selected Affiliates', 'uap');?>"
data-email_server_check="<?php esc_html_e('An E-mail was sent to your Admin address. Check your inbox or Spam/Junk Folder!', 'uap');?>"
></div>

<?php $includeAnnouncement = $data['show_announcement'] ? 'uap-dashboard-show-announcement' : '';?>

<div class="uap-dashboard-wrap <?php echo $includeAnnouncement;?>" >
	<?php $announcementClass = $data['show_announcement'] ? 'uap-display-block' : 'uap-display-none';?>
	<div class="uap-dashboard-announcement-wrapper <?php echo $announcementClass;?>">
		<div class="uap-dashboard-announcement">
			<?php esc_html_e('Get the ', 'uap');?><a href="https://ultimateaffiliate.pro/get-bundle-pack" target="_blank"><?php esc_html_e('Add-on Bundle', 'uap');?></a><?php esc_html_e(' Pack to gain access to a such of powerful extensions and to unlock even more features. ', 'uap');?><a href="https://ultimateaffiliate.pro/get-bundle-pack" target="_blank"><?php esc_html_e('Upgrade Now', 'uap');?></a><?php esc_html_e(' and save big!', 'uap');?>
		</div>
		<div class="uap-close-button uap-admin-js-pause-notification pointer">
			X
		</div>
	</div>
	<div class="uap-admin-header">
		<div class="uap-top-menu-section">
			<div class="uap-dashboard-logo">
			<a href="<?php echo esc_url($data['base_url'] . '&tab=dashboard');?>">
				<img src="<?php echo esc_url(UAP_URL . 'assets/images/dashboard-logo.jpg');?>" alt="Ultimate Affiliate Pro"/>
				<div class="uap-plugin-version"><?php echo esc_html($plugin_vs); ?></div>
			</a>
			</div>
			<div class="uap-dashboard-menu">
				<div class="uap-admin-mobile-bttn-wrapp"><i class="uap-admin-mobile-bttn"></i></div>
				<ul class="uap-dashboard-menu-items">
					<?php foreach ($data['menu_items'] as $k=>$v) :?>
						<?php $selected = ($data['tab']==$k) ? 'selected' : '';?>
								<li class="<?php echo esc_attr($selected);?>">
									<?php
										$dezactivated_class ='';
										$url = $data['base_url'] . '&tab=' . $k;
										$statsObject = uapGeneralPrefix().uapPrevLabel().uapRankGeneralLabel();
										$stats = new $statsObject();
										if ($k=='banners' && ( $stats->gdcp() === true || $stats->gdcp() === '' || $stats->GLD() === '1' ) ):
											$url = '#';
											$dezactivated_class = 'uap-inactive-tab';
										endif;
										if ($k=='affiliates' && !empty($data['affiliates_notification_count'])){
											echo esc_uap_content('<div class="uap-dashboard-notification-top">' . esc_html($data['affiliates_notification_count']) . '</div>');
										} else if ($k=='referrals' && !empty($data['referrals_notification_count'])){
											echo esc_uap_content('<div class="uap-dashboard-notification-top">' . esc_html($data['referrals_notification_count']) . '</div>');
										}
									?>
									<a href="<?php echo esc_url($url);?>" title="<?php echo esc_attr($v);?>">
										<div class="uap-page-title link-<?php echo esc_attr($k); ?>  <?php echo esc_attr($dezactivated_class);?>">
											<i class="fa-uap fa-uap-menu fa-<?php echo esc_attr($k);?>-uap"></i>
											<div><?php echo esc_html($v);?></div>
										</div>
									</a>
								</li>
					<?php endforeach;?>
				</ul>
			</div>
		</div>
	</div>


<div class="uap-right-menu">
	<?php
		foreach ($data['right_tabs'] as $k=>$v){
		?>
		<div class="uap-right-menu-item">
			<a href="<?php echo esc_url($data['base_url']  . '&tab=' . $k);?>" title="<?php echo esc_attr($v);?>">
				<div class="uap-page-title-right-menu">
					<i class="fa-uap fa-uap-menu fa-<?php echo esc_attr($k);?>-uap"></i>
					<div class="uap-right-menu-title"><?php echo esc_html($v);?></div>
				</div>
			</a>
		</div>
		<?php
		}
	?>
</div>
<?php do_action('uap_admin_action_dashboard_after_header');?>

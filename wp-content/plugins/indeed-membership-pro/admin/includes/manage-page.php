<?php
$dashboard_notifications = get_option('ihc_admin_workflow_dashboard_notifications');
if ($dashboard_notifications!==FALSE && $dashboard_notifications!=0){
	$new_users = Ihc_Db::get_dashboard_notification_value('users');
	$new_orders = Ihc_Db::get_dashboard_notification_value('orders');
}

$url = get_admin_url() . 'admin.php?page=ihc_manage';

wp_enqueue_script( 'ihc-manage-page', IHC_URL . 'admin/assets/js/manage-page.js', ['jquery'], 10.1 );
$tab = 'dashboard';
if(isset($_REQUEST['tab'])){
	 $tab = sanitize_text_field($_REQUEST['tab']);
}

$tabs_arr = array(
					'users' => esc_html__('Members', 'ihc'),
					'affiliates' => esc_html__('Ultimate Affiliates', 'ihc'),
					'levels' => esc_html__('Memberships', 'ihc'),
					'payment_settings' => esc_html__('Payment Gateways', 'ihc'),
					'locker' => esc_html__('Inside Lockers', 'ihc'),
					'showcases' => esc_html__('Showcases', 'ihc'),
					'social_login' => esc_html__("Social Connect", 'ihc'),
					'coupons' => esc_html__("Coupons", "ihc"),
					'block_url' => esc_html__('Content Access Rules', 'ihc'),
					'orders' => esc_html__('Payment History', 'ihc'),
					'notifications' => esc_html__('Email Notifications', 'ihc'),
					'magic_feat' => esc_html__( 'Extensions', 'ihc'),
					'general' => esc_html__('General Settings', 'ihc'),
				  );

$tabs_arr = apply_filters( 'ihc_filter_admin_dashboard_tabs', $tabs_arr );
?>
<span class="ihc-js-admin-messages"
			data-delete_user="<?php esc_html_e( 'Are you sure that you want to remove this Member?', 'ihc' );?>"
			data-delete_level="<?php esc_html_e( 'Are you sure that you want to remove this Membership?', 'ihc' );?>"
			data-delete_transaction="<?php esc_html_e( 'Are you sure that you want to remove this Transaction?', 'ihc' );?>"
			data-delete_item="<?php esc_html_e( 'Are you sure that you want to remove this Item?', 'ihc' );?>"
			data-delete_items="<?php esc_html_e( 'Are you sure that you want to remove selected Items?', 'ihc' );?>"
			data-delete_order="<?php esc_html_e( 'Are you sure that you want to remove this Order?', 'ihc' );?>"
			data-hold='<?php esc_html_e('Hold');?>'
			data-expired='<?php esc_html_e( 'Expired', 'ihc' );?>'
			data-active='<?php esc_html_e( 'Active', 'ihc');?>'
			data-show_more='<?php esc_html_e( 'Show More', 'ihc' );?>'
			data-show_less='<?php esc_html_e( 'Show Less', 'ihc' );?>'
			data-search_cats='<?php esc_html_e('Search Categories', 'ihc');?>'
			data-search_products='<?php esc_html_e('Search Products', 'ihc');?>'
			data-email_server_check="<?php esc_html_e('An Email was sent to your Admin address. Check your inbox or Spam/Junk Folder!', 'ihc');?>"
			data-confirm_label="<?php esc_html_e('Ok', 'ihc');?>"
></span>

<?php $plugin_vs = get_ump_version(); ?>
<?php $includeAnnouncement = $show_announcement ? 'ump-dashboard-show-announcement' : '';?>
<div class="ihc-dashboard-wrap  <?php echo $includeAnnouncement;?>">
	<?php $announcementClass = $show_announcement ? 'ihc-display-block' : 'ihc-display-none';?>
	<div class="ump-dashboard-announcement-wrapper <?php echo $announcementClass;?>">
		<div class="ump-dashboard-announcement">
			<?php esc_html_e('Get the ', 'ihc');?><a href="https://ultimatemembershippro.com/get-bundle-pack" target="_blank"><?php esc_html_e('Add-on Bundle', 'ihc');?></a><?php esc_html_e(' Pack to gain access to a such of powerful extensions and to unlock even more features. ', 'ihc');?><a href="https://ultimatemembershippro.com/get-bundle-pack" target="_blank"><?php esc_html_e('Upgrade Now', 'ihc');?></a><?php esc_html_e(' and save big!', 'ihc');?>
		</div>
		<div class="ump-close-button ump-admin-js-pause-notification pointer">
			X
		</div>
	</div>
	<div class="ihc-admin-header">
		<div class="ihc-top-menu-section">
			<div class="ihc-dashboard-logo">
			<a href="<?php echo esc_url( $url . '&tab=dashboard' ) ;?>">
				<img alt="Ultimate Membership Pro" src="<?php echo IHC_URL;?>admin/assets/images/dashboard-logo.jpg"/>
				<div class="ihc-plugin-version"><?php echo esc_html( $plugin_vs ); ?></div>
			</a>
			</div>
			<div class="ihc-dashboard-menu">
            	<div class="ihc-admin-mobile-bttn-wrapp"><i class="ihc-admin-mobile-bttn"></i></div>
				<ul  class="ihc-dashboard-menu-items">
				<?php
					foreach($tabs_arr as $k=>$v){
						$selected = '';
						$menu_tab = $tab;
						switch($tab){
							case 'register':
											$menu_tab='showcases';
											break;
							case 'login':
											$menu_tab='showcases';
											break;
							case 'subscription_plan':
											$menu_tab='showcases';
											break;
							case 'account_page':
											$menu_tab='showcases';
											break;
						}


						if($menu_tab==$k){
							 $selected = 'selected';
						}

						$class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
		        $ol_dL_ogs = new $class();

						if ((int)$ol_dL_ogs->FGCS() > 0 && $k=='coupons'){
							$tab_url = '';
							$dezactivated_class = 'ihc-inactive-tab';
						} else {
							$dezactivated_class = '';
							$tab_url = $url . '&tab=' . $k;
						}
						if($k=='affiliates'){
							$dezactivated_class = 'ihc-affiliates_menu '.$dezactivated_class;
						}
						if($k=='magic_feat'){
							$dezactivated_class = 'ihc-magic_feat_menu '.$dezactivated_class;
						}
							?>
								<li class="<?php echo esc_attr($selected);?>">
									<?php
									if ($k=='users' && !empty($new_users)){
										echo esc_ump_content('<div class="ihc-dashboard-notification-top">' . $new_users . '</div>');
									} else if ($k=='orders' && !empty($new_orders)){
										echo esc_ump_content('<div class="ihc-dashboard-notification-top">' . $new_orders . '</div>');
									}
									?>
									<a href="<?php echo esc_url($tab_url);?>" title="<?php echo esc_attr($v);?>">
										<div class="ihc-page-title <?php echo esc_attr($dezactivated_class);?>">
											<i class="fa-ihc fa-ihc-menu fa-<?php echo esc_attr($k);?>-ihc"></i>
											<div><?php echo esc_html($v);?></div>
										</div>
									</a>
								</li>
							<?php

					}
				?>

				</ul>
			</div>
		</div>
	</div>
	<?php
		//tabs
		switch($tab){
			case 'dashboard':
				include_once IHC_PATH . 'admin/includes/tabs/dashboard.php';
			break;
			case 'users':
				include_once IHC_PATH . 'admin/includes/tabs/users.php';
			break;
			case 'levels':
				include_once IHC_PATH . 'admin/includes/tabs/levels.php';
			break;
			case 'locker':
				include_once IHC_PATH . 'admin/includes/tabs/locker.php';
			break;
			case 'register':
				include_once IHC_PATH . 'admin/includes/tabs/register.php';
			break;
			case 'login':
				include_once IHC_PATH . 'admin/includes/tabs/login.php';
			break;
			case 'general':
				include_once IHC_PATH . 'admin/includes/tabs/general.php';
			break;
			case 'block_url':
				include_once IHC_PATH . 'admin/includes/tabs/block_url.php';
			break;
			case 'opt_in':
				include_once IHC_PATH . 'admin/includes/tabs/opt_in.php';
			break;
			case 'payment_settings':
				include_once IHC_PATH . 'admin/includes/tabs/payment_settings.php';
			break;
			case 'help':
				include_once IHC_PATH . 'admin/includes/tabs/help.php';
			break;
			case 'notifications':
				include_once IHC_PATH . 'admin/includes/tabs/notifications.php';
			break;
			case 'showcases':
				include_once IHC_PATH . 'admin/includes/tabs/showcases.php';
			break;
			case 'subscription_plan':
				include_once IHC_PATH . 'admin/includes/tabs/subscription_plan.php';
			break;
			case 'social_login':
				include_once IHC_PATH . 'admin/includes/tabs/social_login.php';
			break;
			case 'account_page':
				include_once IHC_PATH . 'admin/includes/tabs/account_page.php';
			break;
			case 'coupons':
				include_once IHC_PATH . 'admin/includes/tabs/coupons.php';
			break;
			case 'user_shortcodes':
				include_once IHC_PATH . 'admin/includes/tabs/user_shortcodes.php';
			break;
			case 'listing_users':
				include_once IHC_PATH . 'admin/includes/tabs/listing_users.php';
			break;
			case 'affiliates':
				include_once IHC_PATH . 'admin/includes/tabs/affiliates.php';
			break;
			case 'new_transaction':
				include_once IHC_PATH . 'admin/includes/tabs/new_transaction.php';
				break;
			case 'magic_feat':
				require_once IHC_PATH . 'admin/includes/tabs/magic_feat.php';
				break;
			case 'taxes':
				require_once IHC_PATH . 'admin/includes/tabs/taxes.php';
				break;
			case 'add_edit_taxes':
				require_once IHC_PATH . 'admin/includes/tabs/add_edit_taxes.php';
				break;
			case 'orders':
				require_once IHC_PATH . 'admin/includes/tabs/orders.php';
				break;
			case 'order-edit':
				require_once IHC_PATH . 'admin/includes/tabs/order-edit.php';
				break;
			case 'payments':
				include_once IHC_PATH . 'admin/includes/tabs/list_payments.php';
				break;
			case 'redirect_links':
				require_once IHC_PATH . 'admin/includes/tabs/redirect_links.php';
				break;
			case 'custom_currencies':
				require_once IHC_PATH . 'admin/includes/tabs/custom_currencies.php';
				break;
			case 'bp_account_page':
				require_once IHC_PATH . 'admin/includes/tabs/bp_account_page.php';
				break;
			case 'woo_account_page':
				require_once IHC_PATH . 'admin/includes/tabs/woo_account_page.php';
				break;
			case 'membership_card':
				require_once IHC_PATH . 'admin/includes/tabs/membership_card.php';
				break;
			case 'cheat_off':
				require_once IHC_PATH . 'admin/includes/tabs/cheat_off.php';
				break;
			case 'invitation_code':
				require_once IHC_PATH . 'admin/includes/tabs/invitation_code.php';
				break;
			case 'invitation_code-add_new':
				require_once IHC_PATH . 'admin/includes/tabs/invitation_code_add_new.php';
				break;
			case 'download_monitor_integration':
				require_once IHC_PATH . 'admin/includes/tabs/download_monitor_integration.php';
				break;
			case 'register_lite':
				require_once IHC_PATH . 'admin/includes/tabs/register_lite.php';
				break;
			case 'individual_page':
				require_once IHC_PATH . 'admin/includes/tabs/individual_page.php';
				break;
			case 'level_restrict_payment':
				require_once IHC_PATH . 'admin/includes/tabs/level_restrict_payment.php';
				break;
			case 'level_subscription_plan_settings':
				require_once IHC_PATH . 'admin/includes/tabs/level_subscription_plan_settings.php';
				break;
			case 'gifts':
				require_once IHC_PATH . 'admin/includes/tabs/gifts.php';
				break;
			case 'add_new_gift':
				require_once IHC_PATH . 'admin/includes/tabs/add_new_gift.php';
				break;
			case 'generated-gift-code':
				require_once IHC_PATH . 'admin/includes/tabs/list_gift_codes.php';
				break;
			case 'login_level_redirect':
				require_once IHC_PATH . 'admin/includes/tabs/login_level_redirect.php';
				break;
			case 'register_redirects_by_level':
				require_once IHC_PATH . 'admin/includes/tabs/magic_feat_custom_register_redirects.php';
				break;
			case 'wp_social_login':
				require_once IHC_PATH . 'admin/includes/tabs/wp_social_login.php';
				break;
			case 'list_access_posts':
				require_once IHC_PATH . 'admin/includes/tabs/list_access_posts.php';
				break;
			case 'invoices':
				require_once IHC_PATH . 'admin/includes/tabs/invoices.php';
				break;
			case 'woo_payment':
				require_once IHC_PATH . 'admin/includes/tabs/woo_payment.php';
				break;
			case 'badges':
				require_once IHC_PATH . 'admin/includes/tabs/badges.php';
				break;
			case 'login_security':
				require_once IHC_PATH . 'admin/includes/tabs/login_security.php';
				break;
			case 'workflow_restrictions':
				require_once IHC_PATH . 'admin/includes/tabs/workflow_restrictions.php';
				break;
			case 'subscription_delay':
				require_once IHC_PATH . 'admin/includes/tabs/subscription_delay.php';
				break;
			case 'level_dynamic_price':
				require_once IHC_PATH . 'admin/includes/tabs/level_dynamic_price.php';
				break;
			case 'view_user_logs':
				require_once IHC_PATH . 'admin/includes/tabs/view_user_logs.php';
				break;
			case 'user_reports':
				require_once IHC_PATH . 'admin/includes/tabs/user_reports.php';
				break;
			case 'pushover':
				require_once IHC_PATH . 'admin/includes/tabs/pushover.php';
				break;
			case 'account_page_menu':
				require_once IHC_PATH . 'admin/includes/tabs/account_page_menu.php';
				break;
			case 'mycred':
				require_once IHC_PATH . 'admin/includes/tabs/mycred.php';
				break;
			case 'api':
				require_once IHC_PATH . 'admin/includes/tabs/api.php';
				break;
			case 'import_export':
				require_once IHC_PATH . 'admin/includes/tabs/import_export.php';
				break;
			case 'woo_product_custom_prices':
				$temp_subtab = isset($_GET['subtab']) ? sanitize_text_field($_GET['subtab']) : 'manage';
				switch ($temp_subtab){
					case 'manage':
						require_once IHC_PATH . 'admin/includes/tabs/woo_product_custom_prices-manage.php';
						break;
					case 'add_edit':
						require_once IHC_PATH . 'admin/includes/tabs/woo_product_custom_prices-add_edit.php';
						break;
					case 'settings':
						require_once IHC_PATH . 'admin/includes/tabs/woo_product_custom_prices-settings.php';
						break;
				}
				break;
			case 'drip_content_notifications':
				require_once IHC_PATH . 'admin/includes/tabs/drip_content_notifications.php';
				break;
			case 'view_drip_content_notifications_logs':
				require_once IHC_PATH . 'admin/includes/tabs/drip_content_notifications_logs.php';
				break;
			case 'user_sites':
				require_once IHC_PATH . 'admin/includes/tabs/user_sites.php';
				break;
			case 'import_users':
				require_once IHC_PATH . 'admin/includes/tabs/import_users.php';
				break;
			case 'add_new_order':
				require_once IHC_PATH . 'admin/includes/tabs/add_new_order.php';
				break;
			case 'zapier':
				require_once IHC_PATH . 'admin/includes/tabs/zapier.php';
				break;
			case 'infusionSoft':
				require_once IHC_PATH . 'admin/includes/tabs/infusionSoft.php';
				break;
		  case 'kissmetrics':
				require_once IHC_PATH . 'admin/includes/tabs/kissmetrics.php';
				break;
			case 'direct_login':
				require_once IHC_PATH . 'admin/includes/tabs/direct_login.php';
				break;
			case 'reason_for_cancel':
				require_once IHC_PATH . 'admin/includes/tabs/reason_for_cancel.php';
				break;
			case 'weekly_summary_email':
				require_once IHC_PATH . 'admin/includes/tabs/weekly_summary_email.php';
				break;
			case 'checkout':
				require_once IHC_PATH . 'admin/includes/tabs/checkout.php';
				break;
			case 'order-details':
				require_once IHC_PATH . 'admin/includes/tabs/order_details.php';
				break;
			case 'notification-logs':
				require_once IHC_PATH . 'admin/includes/tabs/notification-logs.php';
				break;
			case 'user-details':
				require_once IHC_PATH . 'admin/includes/tabs/user-details.php';
				break;
			case 'edit-user-subscriptions':
				require_once IHC_PATH . 'admin/includes/tabs/edit-user-subscriptions.php';
				break;
			case 'manage_subscription_table':
				require_once IHC_PATH . 'admin/includes/tabs/manage_subscription_table.php';
				break;
			case 'manage_order_table':
				require_once IHC_PATH . 'admin/includes/tabs/manage_order_table.php';
				break;
			case 'profile-form':
				require_once IHC_PATH . 'admin/includes/tabs/profile-form.php';
				break;
			case 'thank-you-page':
				require_once IHC_PATH . 'admin/includes/tabs/thank-you-page.php';
				break;
			case 'prorate_subscription':
				require_once IHC_PATH . 'admin/includes/tabs/prorate_subscription.php';
				break;
			case 'prorate_add_edit':
				require_once IHC_PATH . 'admin/includes/tabs/prorate-add-edit.php';
				break;
			default :
				do_action( 'ump_print_admin_page', $tab );
				break;
		}

	?>
</div>

<?php do_action( 'ihc_filter_admin_dashboard_after_footer_html' );?>

<div class="ihc-footer-wrap">
	<div class="ihc-additional-help">
	<div class="ihc-footer-text"><strong>Ultimate Membership Pro v. <?php echo esc_html($plugin_vs); ?></strong> Wordpress Plugin by <a href="https://ultimatemembershippro.com/portfolio/" target="_blank"><?php echo IHC_DEV; ?></a></div>
	<a href="https://ultimatemembershippro.com/rating/" target="_blank" title="Support us with 5-stars Rating for further development" class="button float_right ihc-black-button"><i class="fa-ihc fa-star"></i><i class="fa-ihc fa-star"></i><i class="fa-ihc fa-star"></i><i class="fa-ihc fa-star"></i><i class="fa-ihc fa-star"></i> 5-stars Rating </a>
	<a href="https://ultimatemembershippro.com/documentation/" target="_blank" title="Documentation" class="button float_right ihc-green-button"><i class="fa-ihc fa-book"></i> Knowledge Base</a>
  <a href="https://ultimatemembershippro.com/videos/" target="_blank" title="Video Tutorials" class="button float_right ihc-red-button"><i class="fa-ihc fa-video"></i> Video Tutorials</a>
  <a href="https://ultimatemembershippro.com/pro-addons/" target="_blank" title="Video Tutorials" class="button float_right ihc-blue-button"><i class="fa-ihc fa-cart-plus"></i> Pro AddOns</a>
	<a href="https://ultimatemembershippro.com/download-plugin/" target="_blank" title="Download Item" class="button float_right"><i class="fa-ihc fa-download"></i> Download</a>
	</div>
</div>
<div class="ihc-right-menu">
	<?php
		$right_menu = array(
							'user_shortcodes' => 'Shortcodes',
							'import_export' => esc_html__('Export/Import', 'ihc'),
							'help' => esc_html__('Help', 'ihc')
		);
		$right_menu = apply_filters( 'ihc_filter_admin_dashboard_right_tabs', $right_menu );

		foreach ($right_menu as $k=>$v){
		?>
		<div class="ihc-right-menu-item">
			<a href="<?php echo esc_url( $url . '&tab=' . $k );?>" title="<?php echo esc_attr($v);?>">
				<div class="ihc-page-title <?php echo esc_attr($dezactivated_class);?>">
					<i class="fa-ihc fa-ihc-menu fa-<?php echo esc_attr($k);?>-ihc"></i>
					<div class="ihc-right-menu-title"><?php echo esc_html($v);?></div>
				</div>
			</a>
		</div>
		<?php
		}
	?>
</div>
<?php wp_enqueue_script('ihc-back_end');?>

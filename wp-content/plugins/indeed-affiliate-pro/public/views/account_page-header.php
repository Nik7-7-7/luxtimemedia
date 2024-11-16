<?php
	$custom_css = ''
 ?>

<?php foreach ($data['available_tabs'] as $k=>$v):
        if ( !isset( $v['uap_tab_' . $k . '_icon_code'] ) || $v['uap_tab_' . $k . '_icon_code'] === ''|| $v['uap_tab_' . $k . '_icon_code'] === false ){ continue;}
	$custom_css .= ".fa-" . $k . "-account-uap:before{".
		"content:'\\".$v['uap_tab_' . $k . '_icon_code']."';".
	"}";
endforeach;?>

<?php   if (!empty($data['uap_account_page_custom_css'])){
	 $custom_css .= $data['uap_account_page_custom_css'];
}?>
<?php wp_enqueue_style( 'uap-croppic_css', UAP_URL . 'assets/css/croppic.css', array(), 8.3 );?>
<?php wp_enqueue_script( 'uap-jquery_mousewheel', UAP_URL . 'assets/js/jquery.mousewheel.min.js', ['jquery'], 8.3 );?>
<?php wp_enqueue_script( 'uap-croppic', UAP_URL . 'assets/js/croppic.js', ['jquery'], 8.3 );?>
<?php wp_enqueue_script( 'uap-account_page-banner', UAP_URL . 'assets/js/account_page-banner.js', ['jquery'], 8.3 );?>
<?php wp_enqueue_style('uap_font_awesome', UAP_URL . 'assets/css/font-awesome.min.css');?>

<div class="uap-js-account-page-header-details" data-uap_url="<?php echo UAP_URL;?>" data-nonce="<?php echo wp_create_nonce( 'publicn' );?>"></div>


<?php if(isset($data['rank']['color'])){
	$custom_css .= "
	.uap-user-page-top-wrapper .uap-top-rank-box{
		background-color:#".$data['rank']['color'].";
	}";
 }
if(isset($data['achieved'])){
	$custom_css .= "
	.uap-user-page-top-wrapper .uap-top-achievement .uap-achieved{
		width:".$data['achieved']."%;
	}";
}
?>
<?php
wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );
 ?>

<div class="uap-user-page-wrapper">
<?php
if(isset($data['preview'])){?>
	<div class="uap-warning-box">
		<?php echo esc_html__('This is a Preview of the Affiliate Portal, as if the affiliate themselves were viewing their own portal. As an administrator, you cannot submit or change data through preview.', 'uap'); ?>
	</div>
<?php }
	$top_class='';
	if (!empty($data['avatar']) && ($data['uap_ap_top_theme'] == 'uap-ap-top-theme-2')){
		 $top_class .=' uap-ap-top-theme-2-avatar-extra-padding';
	}
	if (!empty($data['top-background']) && ($data['uap_ap_top_theme'] == 'uap-ap-top-theme-2')){
		 $top_class .=' uap-ap-top-theme-2-banner-extra-padding';
	}
	if (!empty($data['avatar']) && ($data['uap_ap_top_theme'] == 'uap-ap-top-theme-3' )){
		 $top_class .=' uap-ap-top-theme-3-avatar-extra-padding';
	}
	if (!empty($data['top-background']) && ($data['uap_ap_top_theme'] == 'uap-ap-top-theme-3' )){
		 $top_class .=' uap-ap-top-theme-3-banner-extra-padding';
	}
	 ?>
<div class="uap-user-page-top-wrapper  <?php echo (!empty($data['uap_ap_top_theme']) ? $data['uap_ap_top_theme'] : '');?> <?php echo esc_attr($top_class);?> ">

  <div class="uap-left-side">
	<div class="uap-user-page-details">
		<?php if (!empty($data['avatar'])):?>
			<div class="uap-user-page-avatar"><img src="<?php echo esc_attr($data['avatar']);?>" alt="Avatar" class="uap-member-photo"/></div>
		<?php endif;?>
	 </div>
	</div>
	<?php
	$top_class ='';
	if (empty($data['top-background']) &&!empty($data['avatar'])){
		$top_class =' uap-ap-top-theme-3-middle-padding';
	}
		?>
	<div class="uap-middle-side <?php echo esc_attr($top_class);?>">
		<div class="uap-account-page-top-mess"><?php echo do_shortcode($data['message']);?></div>
		<?php if (!empty($data['top-rank']) && !empty($data['rank'])):?>
		<div class="uap-top-rank">
			<?php
					$data['rank']['label'] = apply_filters(
													'wpml_translate_single_string',
													$current_rank['label'],
													'uap',
													'rank_name_' . $data['rank']['id'],
													apply_filters( 'wpml_current_language', NULL )
					);
					$atype = '%';
					if($data['rank']['amount_type'] == 'flat'):
						$atype = uapCurrency($data['stats']['currency']);
					endif;
			?>
			<div class="uap-top-rank-box" title="<?php echo esc_attr($data['rank']['amount_value'].$atype).' '.esc_html__('reward', 'uap');?>"><?php echo esc_html($data['rank']['label']);?></div>
		</div>
		<?php endif;?>
	</div>
	<?php
	if (!empty($data['uap_ap_top_theme']) && ($data['uap_ap_top_theme'] == 'uap-ap-top-theme-3' )){
		 echo '<div class="uap-clear uap-special-clear"></div>';
	}
	 ?>
		<?php if (!empty($data['top-earning']) || !empty($data['top-referrals']) || !empty($data['top-achievement']) || !empty($data['top-rank']) || !empty($data['uap_ap_edit_show_metrics'])):?>
	<div class="uap-right-side">
		<?php if (!empty($data['top-earning'])):?>
			<div class="uap-top-earnings">
				<div class="uap-stats-label"><?php echo esc_html__('Earnings', 'uap'); ?></div>
				<div class="uap-stats-content"> <?php echo uap_format_price_and_currency(uapCurrency($data['stats']['currency']), round($data['stats']['paid_payments_value']+$data['stats']['unpaid_payments_value'], 2)); ?></div>
			</div>
		<?php endif;?>
		<?php if (!empty($data['top-referrals'])):?>
			<div class="uap-top-referrals">
				<div class="uap-stats-label"><?php echo esc_html__('Referrals', 'uap'); ?></div>
				<div class="uap-stats-content"> <?php echo esc_html($data['stats']['referrals']); ?></div>
			</div>
		<?php endif;?>


		<?php if (!empty($data['top-achievement']) && $data['achieved']>-1 && isset($data['next_rank'])):?>
			<div class="uap-clear uap-special-clear"></div>
            <?php if(!empty($data['next_rank'])){ ?>
                <div class="uap-top-achievement">
                <?php
                    $atype = '%';
                        if($data['next_rank']->amount_type == 'flat'):
                            $atype = uapCurrency($data['stats']['currency']);
                        endif;
                ?>
                    <div class="uap-stats-label"><?php echo esc_html__('Until', 'uap'); ?> <strong title="<?php echo esc_attr($data['next_rank']->amount_value.$atype).' '.esc_html__('reward', 'uap');?>"> <?php echo esc_attr($data['next_rank']->label); ?></strong> <?php echo esc_html__('Rank...', 'uap'); ?></div>
                    <div class="uap-achievement-line" title="<?php echo esc_attr($data['achieved'].'% ').esc_html__('achieved', 'uap'); ?>">
                        <div class="uap-achieved"></div>
                    </div>
                </div>
            <?php } ?>
            <div class="uap-clear uap-special-clear"></div>
		<?php endif;?>
        <?php if (!empty($data['uap_ap_edit_show_metrics'])):?>
			<div class="uap-top-metrics">
				<div class="uap-stats-content">
					<div class="uap-metris-rightside">
						<div>
								<?php echo esc_html__('3 months EPC: ', 'uap');
								echo uap_format_price_and_currency(uapCurrency($data['stats']['currency']), $data['metrics'][3]); ?>
						</div>
						<div>
								<?php echo esc_html__('7 days EPC: ', 'uap');
								echo uap_format_price_and_currency(uapCurrency($data['stats']['currency']), $data['metrics'][7]); ;?>
						</div>
					</div>
				</div>
			</div>
            <div class="uap-clear uap-special-clear"></div>
		<?php endif;?>

		<div class="uap-clear"></div>
	</div>
<?php endif;?>

	<div class="uap-clear"></div>
	<?php if (!empty($data['top-background'])):

		///
		$bkStyl = '';
		$banner = '';
		if (!empty($data['uap_account_page_personal_header'])):
				$banner = $data ['uap_account_page_personal_header'];
		endif;

		if (empty($banner) && !empty($data ['top_banner'])):
			$banner = $data ['top_banner'];
		elseif (empty($banner) && !empty($data ['top-background-image'])):
			$banner = $data ['top-background-image'];
		endif;

		if (!empty($banner)){
			wp_add_inline_style( 'dummy-handle', ".uap-user-page-top-background{
						background-image:url('" . esc_url($banner) . "') !important;
			}" );
		}



		///
	?>
  <div class="uap-user-page-top-background" data-banner="<?php echo esc_attr($banner);?>">
			<div class="uap-edit-top-ap-banner" id="js_uap_edit_top_ap_banner"></div>
	</div>
  <?php endif;?>
</div>
<div class="uap-user-page-content-wrapper <?php echo esc_attr($data['uap_ap_theme']);?>">


<?php //=================================== TABS ====================================//?>

<?php
$tabs = array(
					'overview' => array(
						'type' => 'tab',
						'label' => esc_html__('Dashboard', 'uap'),
						'slug' => 'overview',
						'print_link' => TRUE,
						'icon_code' => '',
					),
					'profile' => array(
						'type' => 'tab',
						'label' => esc_html__('Profile', 'uap'),
						'slug' => 'profile',
						'icon_code' => '',
						'print_link' => FALSE,
						'children' => array(
											'edit_account' => array(
												'type' => 'subtab',
												'label' => esc_html__('Edit Account', 'uap'),
												'slug' => 'edit_account',
												'icon_code' => '',
												'print_link' => TRUE,
												'parent' => 'profile',
											),
											'change_pass' => array(
												'type' => 'subtab',
												'label' => esc_html__('Change Password', 'uap'),
												'slug' => 'change_pass',
												'icon_code' => '',
												'print_link' => TRUE,
												'parent' => 'profile',
											),
											'payments_settings' => array(
												'type' => 'subtab',
												'label' => esc_html__('Payment Settings', 'uap'),
												'slug' => 'payments_settings',
												'icon_code' => '',
												'print_link' => TRUE,
												'parent' => 'profile',
											),
											'custom_affiliate_slug' => array(
												'type' => 'subtab',
												'label' => esc_html__('Custom Affiliate Slug', 'uap'),
												'slug' => 'custom_affiliate_slug',
												'icon_code' => '',
												'print_link' => TRUE,
												'parent' => 'profile',
											),
											'pushover_notifications' => array(
												'type' => 'subtab',
												'label' => esc_html__('Pushover Notifications', 'uap'),
												'slug' => 'pushover_notifications',
												'icon_code' => '',
												'print_link' => TRUE,
												'parent' => 'profile',
											),
						),
					),
					'marketing' => array(
						'type' => 'tab',
						'label' => esc_html__('Marketing', 'uap'),
						'slug' => 'marketing',
						'print_link' => FALSE,
						'icon_code' => '',
						'children' =>	array(
											'affiliate_link' => array(
												'type' => 'subtab',
												'label' => esc_html__('Affiliate Links', 'uap'),
												'slug' => 'affiliate_link',
												'icon_code' => '',
												'print_link' => TRUE,
												'parent' => 'marketing',
											),
					  						'simple_links' => array(
												'type' => 'subtab',
												'label' => esc_html__('Referrer Links', 'uap'),
												'slug' => 'simple_links',
												'icon_code' => '',
												'print_link' => TRUE,
												'parent' => 'marketing',
											),
					  						'campaigns' => array(
												'type' => 'subtab',
												'label' => esc_html__('Campaigns', 'uap'),
												'slug' => 'campaigns',
												'icon_code' => '',
												'print_link' => TRUE,
												'parent' => 'marketing',
											),
											'banners' => array(
												'type' => 'subtab',
												'label' => esc_html__('Banners', 'uap'),
												'slug' => 'banners',
												'icon_code' => '',
												'print_link' => TRUE,
												'parent' => 'marketing',
											),
											'landing_pages' => array(
												'type' => 'subtab',
												'label' => esc_html__('Your Landing Pages', 'uap'),
												'slug' => 'landing_pages',
												'print_link' => true,
												'icon_code' => '',
												'parent' => 'marketing',
											),
											'coupons' => array(
												'type' => 'subtab',
												'label' => esc_html__('Coupons', 'uap'),
												'slug' => 'coupons',
												'icon_code' => '',
												'print_link' => TRUE,
												'parent' => 'marketing',
											),
											'product_links'	=> array(
												'type' 				=> 'subtab',
												'label' 			=> esc_html__('Product Links', 'uap'),
												'slug' 				=> 'product_links',
												'icon_code' 	=> '',
												'print_link' 	=> TRUE,
												'parent' 			=> 'marketing',
											),
						)
					),
					'referrals' => array(
						'type' => 'tab',
						'label' => esc_html__('Statements', 'uap'),
						'slug' => 'referrals',
						'print_link' => TRUE,
						'icon_code' => '',
					),
					'payments' => array(
						'type' => 'tab',
						'label' => esc_html__('Earnings', 'uap'),
						'slug' => 'payments',
						'print_link' => TRUE,
						'icon_code' => '',
					),
					'wallet' => array(
						'type' => 'tab',
						'label' => esc_html__('Wallet', 'uap'),
						'slug' => 'wallet',
						'print_link' => TRUE,
						'icon_code' => '',
					),
					'main_reports' => array(
						'type' => 'tab',
						'label' => esc_html__('Reports', 'uap'),
						'slug' => 'main_reports',
						'icon_code' => '',
						'print_link' => FALSE,
						'children' => array(
					  						'reports' => array(
												'type' => 'subtab',
												'label' => esc_html__('Reports', 'uap'),
												'slug' => 'reports',
												'print_link' => TRUE,
												'icon_code' => '',
												'parent' => 'reports',
											),
					  					  	'visits' => array(
												'type' => 'subtab',
												'label' => esc_html__('Traffic Log', 'uap'),
												'slug' => 'visits',
												'print_link' => TRUE,
												'icon_code' => '',
												'parent' => 'reports',
											),
										  	'campaign_reports' => array(
												'type' => 'subtab',
												'label' => esc_html__('Campaign Reports', 'uap'),
												'slug' => 'campaign_reports',
												'print_link' => TRUE,
												'icon_code' => '',
												'parent' => 'reports',
											),
										  	'referrals_history' => array(
												'type' => 'subtab',
												'label' => esc_html__('Referrals History', 'uap'),
												'slug' => 'referrals_history',
												'print_link' => TRUE,
												'icon_code' => '',
												'parent' => 'reports',
											),
										  	'mlm' => array(
												'type' => 'subtab',
												'label' => esc_html__('Multi-Level Marketing', 'uap'),
												'slug' => 'mlm',
												'print_link' => TRUE,
												'icon_code' => '',
												'parent' => 'reports',
											),
						)
					),
					'info_affiliate_bar' => array(
								'type' => 'tab',
								'label' => get_option('uap_tab_info_affiliate_bar_menu_label') ? get_option('uap_tab_info_affiliate_bar_menu_label') : esc_html__('Affiliate FlashBar', 'uap'),
								'slug' => 'info_affiliate_bar',
								'icon_code' => '',
								'print_link' => FALSE,
								'children' => array(
										'iab_settings'     => array(
														'type'        => 'subtab',
														'label'       => esc_html__('Settings', 'uap'),
														'slug'        => 'iab_settings',
														'print_link'  => true,
														'icon_code'   => '',
														'parent'      => 'info_affiliate_bar',
										),
										'iab_tips'     => array(
														'type'        => 'tips',
														'label'       => esc_html__('Tips', 'uap'),
														'slug'        => 'iab_tips',
														'print_link'  => true,
														'icon_code'   => '',
														'parent'      => 'info_affiliate_bar',
										),
								),
					),
				    'referral_notifications' => array(
								'type' => 'tab',
								'label' => esc_html__('Notifications', 'uap'),
								'slug' => 'referral_notifications',
								'print_link' => TRUE,
								'icon_code' => '',
					),
					'help' => array(
						'type' => 'tab',
						'label' => esc_html__('Help', 'uap'),
						'slug' => 'help',
						'print_link' => TRUE,
						'icon_code' => '',
					),
					'logout' => array(
						'type' => 'tab',
						'label' => esc_html__('LogOut', 'uap'),
						'slug' => 'logout',
						'print_link' => TRUE,
						'icon_code' => '',
					),
);

foreach ($custom_menu as $temp_k=>$temp_arr){
	if (emptY($temp_arr['type'])){
		$custom_menu[$temp_k]['type'] = 'tab';
	}
}
$tabs = array_merge($tabs, $custom_menu);
$tabs = uap_reorder_menu_items($order, $tabs);


$selected_parent = '';
foreach ($tabs as $first_slug => $array){
	/// exclude item

	if (in_array($first_slug, $exclude_tabs)){
		unset($tabs[$first_slug]);
		continue;
	} else if (isset($array['children'])){
		/// parent check
		foreach ($array['children'] as $children_slug => $children_array){
			if (in_array($children_slug, $exclude_tabs)){
				unset($tabs[$first_slug]['children'][$children_slug]);
			} else if (!in_array($children_slug, $data['show_tab_list'])){
				unset($tabs[$first_slug]['children'][$children_slug]);
			}
		}
		if (count($tabs[$first_slug]['children'])==0){
			unset($tabs[$first_slug]);
			continue;
		}
	} else if (!in_array($first_slug, $data['show_tab_list'])){
		unset($tabs[$first_slug]);
		continue;
	}

	///// UPDATE MENU LABEL
	if (!empty($this->account_page_settings['uap_tab_' . $first_slug . '_menu_label'])){
		$tabs[$first_slug]['label'] = $this->account_page_settings['uap_tab_' . $first_slug . '_menu_label'];
	}

	if (!empty($tabs[$first_slug]['children'])){
		foreach ($tabs[$first_slug]['children'] as $second_slug => $second_array){

			/// exclude item
			if (in_array($second_slug, $exclude_tabs)){
				unset($tabs[$first_slug]['children'][$second_slug]);
				continue;
			} else if (!in_array($second_slug, $data['show_tab_list'])){
				unset($tabs[$first_slug]['children'][$second_slug]);
			}

			//// SET THE SELECTED PARENT
			if ($second_slug==$data['selected_tab']){
				$selected_parent = $second_array['parent'];
			}

			///// UPDATE MENU LABEL
			if (!empty($this->account_page_settings['uap_tab_' . $second_slug . '_menu_label'])){
				$tabs[$first_slug]['children'][$second_slug]['label'] = $this->account_page_settings['uap_tab_' . $second_slug . '_menu_label'];
			}
		}
	}
}

//// DO REORDER MENU ITEMS
?>
		<div class="uap-ap-menu">
			<ul>
				<?php foreach ($tabs as $slug => $array) : ?>
			        <?php if ($array['type'] == 'tab'):
							if (!empty($array['children'])){
			        			if ($selected_parent==$slug){
			        				$extra_styl = 'uap-display-block';
			        				$i_class = 'fa-account-down-uap';
									$tab_selected = ' uap-ap-menu-tab-item-selected';
			        			} else {
			        				$extra_styl = '';
									$tab_selected = '';
			        				$i_class = 'fa-account-right-uap';
			        			}
								if ($data['uap_ap_theme']=='uap-ap-theme-1'){
									$action = "onClick=uapShowSubtabs('" . $slug . "');";
								} else {
									$action = "";
								}
			        ?>
								<li class="uap-ap-submenu-item<?php echo esc_attr($tab_selected);?>"><div class="uap-ap-menu-tab-item" <?php echo esc_attr($action);?> ><a href="javascript:void(0);"><i class="uap-ap-menu-sign fa-uap <?php echo esc_attr($i_class);?>" id="<?php echo esc_attr('uap_fa_sign-' . $slug);?>"></i><?php echo esc_html($array['label']);?></a></div>
									<ul class="uap-public-ap-menu-subtabs <?php echo esc_attr($extra_styl);?>" id="<?php echo esc_attr('uap_public_ap_' . $slug);?>">
										<?php foreach ($array['children'] as $second_slug => $second_array): ?>
											<?php $extra_class = ($data['selected_tab']==$second_slug) ? 'uap-ap-menu-item-selected' : '';?>
											<li class="uap-ap-menu-item <?php echo esc_attr($extra_class);?>"><a href="<?php echo esc_url($data['urls'][$second_slug]);?>"><i class="<?php echo esc_attr('fa-uap fa-' . $second_slug . '-account-uap');?>"></i><?php
			        						  	echo esc_html($second_array['label']);
											?></a></li>
										<?php endforeach;?>
									</ul>
								</li>
						<?php } else { ?>

					  		<?php $extra_class = ($data['selected_tab']==$slug) ? 'uap-ap-menu-tab-item-selected' : '';?>
						  	<li class="uap-ap-menu-tab-item <?php echo esc_attr($extra_class);?>"><a href="<?php
										if ( empty( $array['uap_tab_' . $slug . '_url'] ) ){
												echo esc_url($data['urls'][$slug]);
										} else {
												echo esc_url($array['uap_tab_' . $slug . '_url']);
										}
								?>"><i class="<?php echo esc_attr('fa-uap fa-' . $slug . '-account-uap');?>"></i><?php
						  		echo esc_html($array['label']);
						  	?></a></li>
						<?php }?>

					<?php endif; ?>

				<?php endforeach;?>
			</ul>
		</div>

<?php //=================================== TABS ====================================//?>



<div class="uap-user-page-content">

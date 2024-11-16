<?php if (isset($data['custom_css'])):
	wp_register_style( 'dummy-handle', false );
	wp_enqueue_style( 'dummy-handle' );
	wp_add_inline_style( 'dummy-handle', stripslashes($data['custom_css']) );
 endif;?>

<?php wp_enqueue_style( 'ihc-croppic_css', IHC_URL . 'assets/css/croppic.css', array(), '12.8' );?>
<?php wp_enqueue_script( 'ihc-jquery_mousewheel', IHC_URL . 'assets/js/jquery.mousewheel.min.js', [ 'jquery' ], '12.8' );?>
<?php wp_enqueue_script( 'ihc-croppic', IHC_URL . 'assets/js/croppic.js', [ 'jquery' ], '12.8' );?>
<?php wp_enqueue_script( 'ihc-account_page-banner', IHC_URL . 'assets/js/account_page-banner.js', [ 'jquery' ], '12.8' );?>

<?php
$ajaxURL = get_site_url() . '/wp-admin/admin-ajax.php?action=ihc_ajax_public_upload_file&ihcpublicn=' . wp_create_nonce( 'ihcpublicn' );
?>
<span class="ihc-js-account-page-account-banner-data"
			data-url_target="<?php echo esc_url($ajaxURL);?>" ></spam>

<div class="ihc-account-page-wrapp" id="ihc_account_page_wrapp">

	<?php
		$top_styl='';
		if (empty($this->settings['ihc_ap_edit_background']) && ($this->settings['ihc_ap_top_template'] == 'ihc-ap-top-theme-2' || $this->settings['ihc_ap_top_template'] == 'ihc-ap-top-theme-3' )){
			$top_styl .='ihc-no-background';
		}
	?>
	<?php if (!empty($this->settings['ihc_ap_edit_background']) || !empty($data['avatar']) || !empty($data['welcome_message']) || !empty($data['levels']) ){ ?>
		<div class="ihc-user-page-top-ap-wrapper <?php echo (!empty($this->settings['ihc_ap_top_template']) ? $this->settings['ihc_ap_top_template'] : '');?> <?php echo esc_attr($top_styl);?>"  >

		  	<div class="ihc-left-side">
				<div class="ihc-user-page-details">
					<?php if (!empty($data['avatar'])):?>
						<div class="ihc-user-page-avatar"><img alt="<?php echo esc_attr($this->current_user->ID); ?>" src="<?php echo esc_url($data['avatar']);?>" class="ihc-member-photo"/></div>
					<?php endif;?>
				</div>
			</div>

			<div class="ihc-middle-side">
				<div class="ihc-account-page-top-mess">
                <?php if ($this->settings['ihc_ap_top_template'] == 'ihc-ap-top-theme-4' ){ ?>
                	<div class="iump-user-page-name"><?php echo esc_html($first_name) . ' ' . esc_html($last_name);?></div>
                <?php } ?>
                   <div class="ihc-account-page-top-extra-mess">
					<?php if (!empty($data['welcome_message'])):?>
						<?php echo do_shortcode($data['welcome_message']);?>
					<?php endif;?>
                    </div>
				</div>
				<?php if (!empty($data['levels'])):?>
					<div class="ihc-top-levels">
						<?php foreach ($data['levels'] as $lid => $level):?>
							<?php
				    			$time_arr = \Indeed\Ihc\UserSubscriptions::getStartAndExpireForSubscription($this->current_user->ID, $lid);
						    	$is_expired_class = '';
									if ( !isset( $time_arr['expire_time'] ) ){
											$time_arr['expire_time'] = '';
									}
									$time_arr['expire_time'] = apply_filters( 'ump_public_account_page_level_expire_time', $time_arr['expire_time'], $this->current_user->ID, $lid );
									// @description

								if (isset($time_arr['expire_time']) && indeed_get_unixtimestamp_with_timezone()>strtotime( $time_arr['expire_time'] ) ){
						    		$is_expired_class = 'ihc-expired-level';
						    	}
							?>
							<?php if (!empty($data['badges_metas']['ihc_badges_on']) && !empty($level['badge_image_url'])):?>
								<div class="iump-badge-wrapper <?php echo esc_attr($is_expired_class);?>"><img src="<?php echo esc_url($level['badge_image_url']);?>" class="iump-badge" title="<?php echo esc_attr($level['label']);?>" /></div>
							<?php elseif (!empty($level['label'])):?>
								<div class="ihc-top-level-box <?php echo esc_attr($is_expired_class);?>"><?php echo esc_html($level['label']);?></div>
							<?php endif;?>
						<?php endforeach;?>
					</div>
				<?php endif;?>
				<?php if (!empty($data['sm'])):?>
					<div class="ihc-ap-top-sm">
						<?php echo esc_ump_content($data['sm']);?>
					</div>
				<?php endif;?>
			</div>

			<div class="ihc-clear"></div>
				<?php
					if (!empty($this->settings['ihc_ap_edit_background'])):
						$bk_styl = '';
						$banner = '';

						if (!empty($this->settings['ihc_ap_top_background_image'])):
								$banner = $this->settings['ihc_ap_top_background_image'];
						endif;

						if (!empty($data['top_banner'])):
							$banner = $data['top_banner'];
						endif;
						if ( $banner && $banner != '' ){
								wp_add_inline_style( 'dummy-handle', ".ihc-user-page-top-ap-background{ background-image:url('" . esc_url($banner) . "') !important; }" );
						}

						/*if (!empty($banner)){
								$bk_styl = ' style = " background-image:url('.$banner.');"';
						}*/
			 	?>

            <div class="ihc-background-overlay"></div>
				  	<div class="ihc-user-page-top-ap-background" data-banner="<?php echo esc_attr($banner);?>" >


						</div>
                    <div class="ihc-edit-top-ap-banner" id="js_ihc_edit_top_ap_banner"></div>
		  <?php endif;?>

		</div>
	<?php } ?>
		<div class="ihc-user-page-content-wrapper  <?php echo (isset($this->settings['ihc_ap_theme'])) ? $this->settings['ihc_ap_theme'] : '';?>">

<?php

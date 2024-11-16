	<div class="ihc-stuffbox">
		<h3>
			<label>
				<?php esc_html_e('Main ShortCodes', 'ihc');?>
			</label>
		</h3>
		<div class="inside">
			<div class="ihc-popup-content help-shortcodes ihc-text-aling-center">
        	<div class="ihc-display-inline">
	            <div class="ihc-popup-shortcodevalue"> <i class="fa-ihc fa-user-plus-ihc"></i><?php esc_html_e('Register Form', 'ihc');?><span>[ihc-register]</span></div>
	            <div class="ihc-popup-shortcodevalue"> <i class="fa-ihc fa-sign-in-ihc"></i><?php esc_html_e('Login Form', 'ihc');?><span>[ihc-login-form]</span></div>
	            <div class="ihc-popup-shortcodevalue"> <i class="fa-ihc fa-sign-out-ihc"></i><?php esc_html_e('Logout Button', 'ihc');?><span>[ihc-logout-link]</span></div>
	            <div class="ihc-popup-shortcodevalue"> <i class="fa-ihc fa-unlock-ihc"></i><?php esc_html_e('Password Recovery', 'ihc');?><span>[ihc-pass-reset]</span></div>
	            <div class="ihc-popup-shortcodevalue"> <i class="fa-ihc fa-user-ihc"></i><?php esc_html_e('Member Portal', 'ihc');?><span>[ihc-user-page]</span></div>
	            <div class="ihc-popup-shortcodevalue"> <i class="fa-ihc fa-levels-ihc"></i><?php esc_html_e('Subscriptions Plan', 'ihc');?><span>[ihc-select-level]</span></div>
	            <div class="ihc-popup-shortcodevalue"> <i class="fa-ihc fa-checkout-ihc"></i><?php esc_html_e('Checkout Page', 'ihc');?><span>[ihc-checkout-page]</span></div>
	            <div class="ihc-popup-shortcodevalue"> <i class="fa-ihc fa-thank-you-ihc"></i><?php esc_html_e('Thank You Page', 'ihc');?><span>[ihc-thank-you-page]</span></div>
	            <div class="ihc-popup-shortcodevalue ihc-shortcode-visitor"> <i class="fa-ihc fa-user-ihc"></i><?php esc_html_e('Public Individual Page', 'ihc');?><span>[ihc-visitor-inside-user-page]</span></div>
				<div class="ihc-clear"></div>
        	</div>
    	</div>
			<div class="clear"></div>
		</div>
	</div>

	<div class="ihc-stuffbox ihc-admin-user-data-list">
		<h3>
			<label><?php esc_html_e('Member Portal Shortcodes', 'ihc');?></label>
		</h3>
		<div class="inside">
			<table class="wp-list-table widefat fixed tags ihc-manage-user-expire">
				<thead>
					<tr>
						<th><?php esc_html_e('Section', 'ihc');?></th>
						<th><?php esc_html_e('Shortcode', 'ihc');?></th>
						<th><?php esc_html_e('Details', 'ihc');?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e('Dashboard', 'ihc');?></td>
						<td>[ihc-account-page-overview]</td>
						<td><?php esc_html_e('Dashboard default content', 'ihc');?></td>
					</tr>
					<tr>
						<td><?php esc_html_e('Profile Form', 'ihc');?></td>
						<td>[ihc-edit-profile-form]</td>
						<td><?php esc_html_e('Profile Form display', 'ihc');?></td>
					</tr>
					<tr>
						<td><?php esc_html_e('Change Password', 'ihc');?></td>
						<td>[ihc-change-password-form]</td>
						<td><?php esc_html_e('Change Password Form display', 'ihc');?></td>
					</tr>
					<tr>
						<td><?php esc_html_e('Subscriptions Table', 'ihc');?></td>
						<td>[ihc-account-page-subscriptions-table]</td>
						<td><?php esc_html_e('List of user Subscriptions with available details and actions', 'ihc');?></td>
					</tr>
					<tr>
						<td><?php esc_html_e('Orders Table', 'ihc');?></td>
						<td>[ihc-account-page-orders-table]</td>
						<td><?php esc_html_e('Customizable showcase for Table with all Orders received by current user', 'ihc');?></td>
					</tr>
					<tr>
						<td><?php esc_html_e('Social Login', 'ihc');?></td>
						<td>[ihc-social-links-profile]</td>
						<td><?php esc_html_e('Allows Users to link their WP Account with a Social Account for next login process', 'ihc');?></td>
					</tr>
					<tr>
						<td><?php esc_html_e('PushOver Form', 'ihc');?></td>
						<td>[ihc-account-page-pushover-form]</td>
						<td><?php esc_html_e('Available when PushOver Notifications module is enabled', 'ihc');?></td>
					</tr>
					<tr>
						<td><?php esc_html_e('User Sites Table', 'ihc');?></td>
						<td>[ihc-user-sites-table]</td>
						<td><?php esc_html_e('Available when MultiSite Subscriptions module is enabled', 'ihc');?></td>
					</tr>
					<tr>
						<td><?php esc_html_e('User Sites Form', 'ihc');?></td>
						<td>[ihc-user-sites-add-new-form]</td>
						<td><?php esc_html_e('Available when MultiSite Subscriptions module is enabled', 'ihc');?></td>
					</tr>
					<tr>
						<td><?php esc_html_e('Member Banner Image', 'ihc');?></td>
						<td>[ihc-user-banner]</td>
						<td><?php esc_html_e('Default or custom banner image uploaded by User', 'ihc');?></td>
					</tr>
					<tr>
						<td><?php esc_html_e('Member Avatar Image', 'ihc');?></td>
						<td>[ihc-user field="ihc_avatar"]</td>
						<td><?php esc_html_e('Avatar Photo for current logged user', 'ihc');?></td>
					</tr>
					<?php
					if (ihc_is_magic_feat_active('gifts')){ ?>
						<tr>
							<td><?php esc_html_e('Membership Gift', 'ihc');?></td>
							<td>[ihc-list-gifts]</td>
							<td><?php esc_html_e('Table list with all received membership gifts for current logged user', 'ihc');?></td>
						</tr>
					<?php } ?>
					<?php
					if (ihc_is_magic_feat_active('membership_card')){ ?>
						<tr>
							<td><?php esc_html_e('Membership Card', 'ihc');?></td>
							<td>[ihc-membership-card]</td>
							<td><?php esc_html_e('All Membership Cards available for current user based on his Memberships', 'ihc');?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>

	<?php
	$additional_shortcodes['[ihc-suspend-account]'] = array(
		'label' => esc_html__('Suspend Account Button', 'ihc'),
		'details' => esc_html__('User will have the option to suspend his Account.', 'ihc')
	);

	if (ihc_is_magic_feat_active('register_lite')){
		$additional_shortcodes['[ihc-register-lite]'] =  array(
			'label' => esc_html__('Register Lite', 'ihc'),
			'details' => esc_html__('Extra Register Form with minimal required fields when "Register Lite" Module is active', 'ihc')
		);
	}

	if (ihc_is_magic_feat_active('individual_page')){
		$additional_shortcodes['[ihc-individual-page-link]'] =  array(
			'label' =>  esc_html__('Individual Page Link', 'ihc'),
			'details' => esc_html__('Access link to private Individual Page when "Individual Page" Module is active', 'ihc')
		);
	}

		if (ihc_is_magic_feat_active('list_access_posts')){
			$additional_shortcodes['[ihc-list-all-access-posts]'] =  array(
				'label' => esc_html__('List Access Posts', 'ihc'),
				'details' => esc_html__('List of all restricted Posts where current user has access based on his Memberships', 'ihc')
			);
		}

	if (ihc_is_magic_feat_active('badges')){
		$additional_shortcodes['[ihc-list-user-levels badges=0 exclude_expire=0]'] =   array(
			'label' =>  esc_html__('Listing User Badges', 'ihc'),
			'details' => esc_html__('Custom Badges assigned to Users based on their active Memberships', 'ihc')
		);
	}
	$additional_shortcodes['[ihc-login-popup] ... [/ihc-login-popup]'] = array(
		'label' =>  esc_html__( 'Login form inside a Modal', 'ihc' ),
		'details' => esc_html__('Link to open Popup with Login Modal form', 'ihc')
	);
	$additional_shortcodes['[ihc-register-popup] ... [/ihc-register-popup]'] = array(
		'label' =>   esc_html__( 'Register form inside a Modal', 'ihc' ),
		'details' => esc_html__('Link to open Popup with Register Modal form', 'ihc')
	);

	?>

	<?php if (!empty($additional_shortcodes)):?>

	<div class="ihc-stuffbox ihc-admin-user-data-list">
		<h3>
			<label><?php esc_html_e('Additional ShortCodes', 'ihc');?></label>
		</h3>
		<div class="inside">
			<table class="wp-list-table widefat fixed tags ihc-manage-user-expire">
				<thead>
					<tr>
						<th><?php esc_html_e('Type', 'ihc');?></th>
						<th><?php esc_html_e('Shortcode', 'ihc');?></th>
						<th><?php esc_html_e('Details', 'ihc');?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($additional_shortcodes as $code=>$v):?>
					<tr>
						<td><?php echo esc_html($v['label']);?></td>
						<td><?php echo esc_html($code);?></td>
						<td><?php echo esc_html($v['details']);?></td>
					</tr>
					<?php endforeach;?>
				</tbody>
			</table>
		</div>
	</div>

	<?php endif;?>

	<div class="ihc-stuffbox ihc-admin-user-data-list">
		<h3>
			<label><?php esc_html_e('Compound ShortCodes', 'ihc');?></label>
		</h3>
		<div class="inside">
			<table class="wp-list-table widefat fixed tags ihc-manage-user-expire">
				<thead>
					<tr>
						<th><?php esc_html_e('Type', 'ihc');?></th>
						<th><?php esc_html_e('Shortcode Example', 'ihc');?></th>
						<th><?php esc_html_e('Details', 'ihc');?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e('Members Directory', 'ihc');?></td>
						<td>[ihc-list-users num_of_entries='50' entries_per_page='20' order_by='user_registered' order_type='desc' user_fields='user_login,user_email,first_name,last_name,ihc_avatar' theme='ihc-theme_1' columns='4']</td>
						<td><?php esc_html_e('Shortcode Generator for Members Listing Showcase. Check for more details', 'ihc');?> <a target='_blank' href='<?php echo esc_url($url ."&tab=listing_users" );?>'><?php esc_html_e('here', 'ihc');?></a></td>
					</tr>
					<tr>
						<td><?php esc_html_e('Inside Locker', 'ihc');?></td>
						<td>[ihc-hide-content ihc_mb_type="show" ihc_mb_who="reg" ihc_mb_template="-1" ][/ihc-hide-content]</td>
						<td><?php esc_html_e('Shortcode Generator from WP editing Page section to restrict partial content. Check for more details on default WP Editor.', 'ihc');?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>


<div class="ihc-stuffbox ihc-admin-user-data-list">
	<h3>
		<label><?php esc_html_e('Member Data ShortCodes', 'ihc');?></label>
	</h3>
	<div class="inside">
		<div class="ihc-popup-content help-shortcodes">
			<table class="wp-list-table widefat fixed tags ihc-manage-user-expire">
			<thead>
				<tr>
					<th><?php esc_html_e('Member Field', 'ihc');?></th>
					<th><?php esc_html_e('Shortcode', 'ihc');?></th>
				</tr>
			</thead>
			<tbody>
	       	<?php
	       	$data = ihc_get_user_reg_fields();
	       	$constants = array('username'=>'', 'user_email'=>'', 'first_name'=>'', 'last_name'=>'', 'account_page'=>'',
	       			'login_page'=>'', 'level_list'=>'',// 'current_level'=>'', 'current_level_expire_date'=>'',
	       			'blogname'=>'', 'blogurl'=>'', 'verify_email_address_link'=>'', 'level_name'=>'', 'ihc_avatar' => '' );
	       	foreach ($constants as $k=>$v){
	       		?>
				<tr>
					<td><?php echo esc_html($k);?></td>
					<td>[ihc-user field="<?php echo esc_html($k);?>"]</td>
				</tr>
	       		<?php
	       	}
	       	$custom_fields = ihc_get_custom_constant_fields();
	       	foreach ($custom_fields as $k=>$v){
	       		$k = str_replace('{', '', $k);
	       		$k = str_replace('}', '', $k);
	       		?>
	       			<tr>
	       				<td><?php echo esc_html($v);?></td>
	       				<td>[ihc-user field="<?php echo esc_html($k);?>"]</td>
	       			</tr>
	       		<?php
	       	}
	       	//ihc_get_custom_constant_fields();
	       	?>
	       	</tbody></table>
    	</div>
		<div class="ihc-clear"></div>
	</div>
</div>
<div class="ihc-stuffbox ihc-admin-user-data-list">
	<h3>
		<label><?php esc_html_e('Memberships Shortcodes', 'ihc');?></label>
	</h3>
	<div class="inside">
		<?php
		$levels = \Indeed\Ihc\Db\Memberships::getAll();
		$levels = ihc_reorder_arr($levels);
		if ($levels && count($levels)){
			?>
			<table class="wp-list-table widefat fixed tags ihc-manage-user-expire">
			<thead>
				<tr>
					<th><?php esc_html_e('Membership Name', 'ihc');?></th>
					<th><?php esc_html_e('Membership direct Purchase Link', 'ihc');?></th>
					<th><?php esc_html_e('Direct Restriction', 'ihc');?></th>
				</tr>
			</thead>
			<tbody>
	       	<?php
				foreach ($levels as $k=>$v){
					?>
						<tr>
							<td><?php echo esc_html($v['name']);?></td>
							<td>
								[ihc-purchase-link id=<?php echo esc_html($k);?>] <span><?php esc_html_e('SignUp', 'ihc');?></span> [/ihc-purchase-link]
							</td>
							<td>
								[ihc-hide-content membership=<?php echo esc_html($k);?>] <span><?php esc_html_e('Your Content Here', 'ihc');?></span> [/ihc-hide-content]
							</td>
						</tr>
					<?php
				}
	       	?>
	       	</tbody></table>
			<?php
		}
		?>

	</div>
</div>

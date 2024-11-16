<div class="ihc-subtab-menu">
	<?php ?>
	<a class="ihc-subtab-menu-item  <?php echo ( isset($_REQUEST['ihc-new-user']) && $_REQUEST['ihc-new-user']  == 'true') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url . '&tab=users&ihc-new-user=true');?>"><?php esc_html_e('Add New Member', 'ihc');?></a>
	<a class="ihc-subtab-menu-item  <?php echo ( !isset($_REQUEST['ihc-new-user'])) ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url . '&tab=' . $tab );?>"><?php esc_html_e('Manage Members', 'ihc');?></a>

	<div class="ihc-clear"></div>
</div>
<?php
wp_enqueue_script( 'ihcAdminSendEmail', IHC_URL . 'admin/assets/js/ihcAdminSendEmail.js', ['jquery'], 10.1 );
wp_enqueue_script( 'ihcSearchUsers', IHC_URL . 'admin/assets/js/search_users.js', ['jquery'], 10.1 );

echo ihc_inside_dashboard_error_license();
echo iump_is_wizard_uncompleted_but_not_skiped();
$is_uap_active = ihc_is_uap_active();

//
if (isset($_POST['delete_users']) && !empty( $_POST['ihc_du'] ) && wp_verify_nonce( sanitize_text_field($_POST['ihc_du']), 'ihc_delete_users' ) ){
	$postDataDelete = indeed_sanitize_array($_POST['delete_users']);
	ihc_delete_users(0, $postDataDelete);
}

// save user
if ( isset( $_POST['ihc_save_member'] ) ){
		$memberObject = new \Indeed\Ihc\Admin\MemberAddEdit();
		$postData = indeed_sanitize_array($_POST);
		$userId = $memberObject->save( $postData );
		if ( $userId == 0 ){
				$errors = $memberObject->getErrors();
		}
}

// print errors from save user if its case
if (!empty($errors) && count($errors)>0){
	if ( isset( $errors['general'] ) && $errors['general'] !== '' ){
			echo esc_ump_content('<div class="ihc-wrapp-the-errors">' . $errors['general'] . '</div>');
			unset( $errors['general'] );
	}
	if (!empty($errors) && count($errors)>0){
			echo esc_ump_content('<div class="ihc-wrapp-the-errors">');
			foreach ( $errors as $key=>$err ){
					echo esc_html__('Field ', 'ihc') . $key . ': ' . $err;
			}
			echo esc_ump_content('</div>');
	}
}


//set default pages message
echo ihc_check_default_pages_set();
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

	if (isset($_REQUEST['ihc-edit-user']) || isset($_REQUEST['ihc-new-user'])){
		//add edit user
		$memberObject = new \Indeed\Ihc\Admin\MemberAddEdit();
		if ( isset( $_GET['ihc-edit-user'] ) ){
				$memberObject->setUid( sanitize_text_field($_GET['ihc-edit-user']) );
		}
		$form = $memberObject->form();

		?>
			<div class="ihc-stuffbox ihc-add-new-user-wrapper">
				<h3><?php esc_html_e('Add/Update Membership Members', 'ihc');?></h3>
				<div class="inside">
                	<div class="ihc-admin-edit-user">
                     <div class="ihc-admin-user-form-wrapper">
                   			 <h2><?php esc_html_e('Member Profile details', 'ihc');?></h2>
					 		<p><?php esc_html_e('Manage what fields are available for Admin setup from "Showcases->Register Form->Custom Fields" section ', 'ihc');?></p>
                    </div>
						<?php echo esc_ump_content($form);?>
					</div>
                </div>
			</div>
		<?php
	} else {
$directLogin = get_option( 'ihc_direct_login_enabled' );
$individual_page = get_option( 'ihc_individual_page_enabled' );
?>
<div class="iump-wrapper">
	<div id="col-right" class="ihc-admin-listing-users">
		<!--div class="iump-page-title">Ultimate Membership Pro -
			<span class="second-text">
				<?php esc_html_e('Membership Members', 'ihc');?>
			</span>
		</div-->
		<div class="iump-page-headline">
			<?php esc_html_e('Manage Members', 'ihc');?>
		</div>
		<a href="<?php echo esc_url($url.'&tab=users&ihc-new-user=true');?>" class="indeed-add-new-like-wp">
			<i class="fa-ihc fa-add-ihc"></i><?php esc_html_e('Add New Member', 'ihc');?>
		</a>

		<div class="ihc-special-buttons-users">
					<div class="ihc-special-button ihc-list-user-make-csv" id="ihc_make_user_csv_file" data-filters="" data-get_variables=""><i class="fa-ihc fa-export-csv"></i><?php esc_html_e( 'Export CSV', 'ihc' );?></div>
					<div class="ihc-hidden-download-link"><a href="" target="_blank"><?php esc_html_e( "Click on this if download doesn't start automatically in 20 seconds!", 'ihc');?></a></div>
					<div class="ihc-clear"></div>
		</div>

		<div class="iump-datatable-filters-wrapper">
				<br/>
						<input type="text" value="" placeholder="<?php esc_html_e('Search Members', 'ihc');?>" class="iump-js-search-phrase ump-max-width-300">

						<div class="iump-datatable-multiselect-wrapp">
								<?php
										$levels_arr = \Indeed\Ihc\Db\Memberships::getAll();
										$getValues = isset( $_GET['levels'] ) ? sanitize_text_field($_GET['levels']) : '';
										if ( stripos( $getValues, ',' ) !== false ) {
												$getValues = explode( ',', $getValues);
										} else {
												$getValues = array( $getValues );
										}
								?>
								<select name="memberships[]" class="iump-datatable-filter-users-memberships" multiple data-placeholder="<?php esc_html_e("Choose Memberships", 'ihc');?>" >
										<?php if ( $levels_arr ):?>
												<?php foreach ( $levels_arr as $id => $levelData ):?>
														<?php $selected = in_array( $id, $getValues ) ? 'selected' : '';?>
														<option value="<?php echo $id;?>" <?php echo $selected;?> ><?php echo $levelData['label'];?></option>
												<?php endforeach;?>
										<?php endif;?>
								</select>
						</div>

						<div class="iump-datatable-multiselect-wrapp">
								<?php
								$statusArray = [
									'active'			  => esc_html__( 'Active', 'ihc' ),
									'expired'			  => esc_html__( 'Expired', 'ihc' ),
									'hold'				  => esc_html__( 'On hold', 'ihc' ),
									'expire_soon'   => esc_html__( 'Expire soon', 'ihc' ),
								];
								?>
								<?php if ( $statusArray ):?>
										<?php
												$getValues = isset( $_GET['levelStatus'] ) ? sanitize_text_field($_GET['levelStatus']) : '';
												if ( stripos( $getValues, ',' ) !== false ) {
														$getValues = explode( ',', $getValues);
												} else {
														$getValues = array( $getValues );
												}
										?>
								<?php endif;?>
								<select name="membership_status[]" class="iump-datatable-filter-users-membership-status" multiple data-placeholder="<?php esc_html_e("Memberships status", 'ihc');?>" >
											<?php foreach ( $statusArray as $key => $label ): ?>
													<?php $selected = in_array( $key, $getValues ) ? 1 : 0;?>
													<option value="<?php echo $key;?>" <?php echo $selected;?> ><?php echo $label;?></option>
											<?php endforeach;?>
								</select>
						</div>
						<?php $roles = ihc_get_wp_roles_list();?>
						<?php
								$getValues = isset( $_GET['roles'] ) ? sanitize_text_field($_GET['roles']) : '';
								if ( stripos( $getValues, ',' ) !== false ) {
										$getValues = explode( ',', $getValues);
								} else {
										$getValues = array( $getValues );
								}
						?>
						<div class="iump-datatable-multiselect-wrapp">
								<select name="roles[]" class="iump-datatable-filter-users-roles" multiple data-placeholder="<?php esc_html_e("WordPress Roles", 'ihc');?>" >
									<?php foreach ( $roles as $key => $label ):?>
											<?php $selected = in_array( $key, $getValues ) ? 1 : 0;?>
											<option value="<?php echo $key;?>" <?php echo $selected;?> ><?php echo $label;?></option>
									<?php endforeach;?>
								</select>
						</div>
						<div class="iump-datatable-multiselect-wrapp">
								<?php
										$adminRequests = [
																				1 				=> esc_html__( 'Approval Request', 'ihc' ),
																				2					=> esc_html__( 'Pending Email Verification', 'ihc' ),
										];
								?>
								<select name="administrator_requests" class="iump-datatable-filter-users-extra_conditions" multiple data-placeholder="<?php esc_html_e("Manager Requests", 'ihc');?>" >
									<?php foreach ( $adminRequests as $type => $label ):?>
											<option value="<?php echo $type;?>" ><?php echo $label;?></option>
									<?php endforeach;?>
								</select>
						</div>
						<div class="iump-datatable-date-input-wrapp">
								<button type="button" class="button button-primary button-small iump-js-admin-users-submit-filters-bttn iump-filters-bttn" ><?php esc_html_e('Filter', 'ihc');?></button>
						</div>


		</div>
<?php
// datatable
$tableDataType = 'members';
$columns = [
				[
						'data'        => 'checkbox',
						'orderable'   => false,
						'sortable'		=> false,
						'className'		=> 'ihc-users-table-col1',
				],
			  [
			      'data'        => 'uid',
						'className'		=> 'ihc-users-table-col2',
						'orderable'   => true,
						'sortable'		=> true,
						'render'  		=> [
															'display'   => 'display',
															'sort'      => 'value',
						]
			  ],
			  [
			      'data'        => 'full_name',
						'className'		=> 'ihc-users-table-col3 ump-max-width-250',
						'orderable'   => true,
						'sortable'		=> true,
						'render'  		=> [
															'display'   => 'display',
															'sort'      => 'value',
						]
			  ],
			  [
			      'data'      => 'user_email',
						'className'		=> 'ihc-users-table-col4 ump-max-width-150',
						'orderable'   => true,
						'sortable'		=> true,
						'render'  		=> [
															'display'   => 'display',
															'sort'      => 'value',
						]
			  ],
			  [
			      'data'      => 'memberships',
			      'orderable'   => false,
			      'sortable'		=> false,
						'className'		=> 'ihc-users-table-col5 ump-max-width-250',
			  ],
			  [
			      'data'      => 'total_spend',
			      'orderable' => false,
						'className'		=> 'ihc-users-table-col6',
			  ],
				[
						'data'			=> 'user_sites',
						'orderable' => false,
						'sortable'  => false,
				],
			  [
			      'data'      => 'wp_role',
			      'orderable' => false,
						'className'		=> 'ihc-users-table-col7',
			      'orderable' => false,
			  ],
			  [
			      'data'        => 'email_status',
						'className'		=> 'ihc-users-table-col8',
			  ],
			  [
			      'data'        => 'user_registered',
						'className'		=> 'ihc-users-table-col9',
						'orderable'   => true,
						'sortable'		=> true,
						'render'  		=> [
															'display'   => 'display',
															'sort'      => 'value',
						]
			  ],
			  [
			      'data'        => 'details',
						'className'		=> 'ihc-users-table-col10',
						'orderable' => false,
						'sortable'  => false,
			  ],

			];

			$magic_feat_user_sites = ihc_is_magic_feat_active('user_sites');
			if ( !$magic_feat_user_sites ){
					unset( $columns[6] );
					$columns = array_values( $columns );
			}

			$columns = apply_filters( 'ihc_admin_filter_members_datatable_column_names', $columns );

			$labels = [
			    'search'				=> esc_html__( "Search&nbsp;:", 'ihc'),
			    'lengthMenu'		=> esc_html__( "Show _MENU_ entries", 'ihc'),
			    'info'					=> esc_html__( "Showing _START_ to _END_ of _TOTAL_ entries", 'ihc'),
			    'infoEmpty'			=> esc_html__( "No results available", 'ihc'),
			    'infoFiltered'	=> esc_html__( "", 'ihc'),
			    'loadingRecords'=> esc_html__( "Loading", 'ihc'),
			    'zeroRecords'		=> esc_html__( "No results available", 'ihc'),
			    'emptyTable'		=> esc_html__( "No results available", 'ihc'),
			    'paginate'			=> [
			          'first'					=> esc_html__( "First", 'ihc'),
			          'previous'			=> esc_html__( "Previous", 'ihc'),
			          'next'					=> esc_html__( "Next", 'ihc'),
			          'last'					=> esc_html__( "Last", 'ihc'),
			    ],
			    'aria'					=> [
			          'sortAscending'		=> esc_html__( "Ascending", 'ihc'),
			          'sortDescending'	=> esc_html__( "Descending", 'ihc'),
			    ],
			    'searchPlaceholder'			=> esc_html__( "Search", 'ihc'),
			    'show_hide_cols_label'	=> esc_html__( "Show / Hide columns", 'ihc'),
			];
			wp_enqueue_style( 'ihcmultiselect', IHC_URL . 'admin/assets/css/jquery.multiselect.css');
			wp_enqueue_script( 'ihcmultiselectfunctions', IHC_URL . 'admin/assets/js/jquery.multiselect.js', ['jquery'], '12.7' );

			// css
			wp_enqueue_style( 'ihcdatabse', IHC_URL . 'admin/assets/css/datatables/datatables.min.css');
			wp_enqueue_style( 'ihcdatabse-buttons', IHC_URL . 'admin/assets/css/datatables/buttons.dataTables.min.css');

			// js
			wp_enqueue_script( 'ihcdatabse', IHC_URL . 'admin/assets/js/datatables/datatables.min.js', ['jquery'], '12.7' );
			wp_enqueue_script( 'ihcdatabse-buttons', IHC_URL . 'admin/assets/js/datatables/dataTables.buttons.min.js', ['jquery'], '12.7' );
			wp_enqueue_script( 'ihcdatabse-colvis', IHC_URL . 'admin/assets/js/datatables/buttons.colVis.min.js', ['jquery'], '12.7' );
			// iump datatable functions
			wp_register_script( 'ihc-table', IHC_URL . 'admin/assets/js/table.js', ['jquery'], '12.7' );
			// setting up the variables
			global $wp_version;
			if ( version_compare ( $wp_version , '5.7', '>=' ) ){
			    wp_add_inline_script( 'ihc-table', "var iump_datatable_cols='" . json_encode( $columns ) . "';" );
			    wp_add_inline_script( 'ihc-table', "var iump_datatable_labels='" . json_encode( $labels ) . "';" );
			    wp_add_inline_script( 'ihc-table', "var iump_datatable_type='$tableDataType';" );
			} else {
			    wp_localize_script( 'ihc-table', 'iump_datatable_cols', json_encode( $columns ) );
			    wp_localize_script( 'ihc-table', 'iump_datatable_labels', json_encode( $labels ) );
			    wp_localize_script( 'ihc-table', "var iump_datatable_type='$tableDataType';" );
			}
			wp_enqueue_script( 'ihc-table' );

?>
<!-- Page State -->
<?php $pageState = get_option( 'ihc_datatable_state_for-members', false );?>

<?php if ( $pageState !== false ):?>
	<div class="iump-js-datatable-state" data-value='<?php echo stripslashes( $pageState );?>'></div>
<?php endif;?>
<!-- End of Page State -->

<table id="iump-dashboard-table" class="display iump-dashboard-table ihc-display-none iump-js-orders-table"  >
		<thead>
				<tr>
						<th class=""><input type="checkbox" class="iump-js-select-all-checkboxes" data-target="iump-dashboard-table"/></th>
						<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('ID', 'ihc');?></th>
						<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Member Name', 'ihc');?></th>
						<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Email Address', 'ihc');?></th>
						<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Membership Plans', 'ihc');?></th>
						<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Total Spend', 'ihc');?></th>
						<?php do_action('ump_action_admin_list_user_column_name_after_total_spend');?>
						<?php if ( $magic_feat_user_sites ):?>
								<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Sites', 'ihc');?></th>
						<?php endif;?>
						<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('WordPress Role', 'ihc');?></th>
						<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Email Status', 'ihc');?></th>
						<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Member Since', 'ihc');?></th>
						<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Details', 'ihc');?></th>
		</thead>
</table>

<div class="iump-datatable-actions-wrapp-copy ihc-display-none">
		<select name="iump-action" class="iump-datatable-select-field iump-js-bulk-action-select">
				<option value="" disabled selected ><?php esc_html_e( 'Bulk Actions', 'ihc' );?></option>
				<option value="remove"><?php esc_html_e('Remove', 'ihc');?></option>
		</select>
		<input type="submit" name="iump-datatable-submit" value="<?php esc_html_e('Apply', 'ihc');?>" class="button button-primary button-small iump-js-members-apply-bttn" />
</div>

	</div>
</div>
<div class="clear"></div>

<?php if ( !empty( $userIds ) ):?>
	<span class="ihc-js-users-list-users-spent-values" data-value="<?php echo esc_attr(implode(',', $userIds));?>"></span>
<?php endif;?>
<?php
}

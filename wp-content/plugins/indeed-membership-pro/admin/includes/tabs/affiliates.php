<?php
$is_uap_active = ihc_is_uap_active();
if ($is_uap_active):
?>
<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=list');?>"><?php esc_html_e('Affiliates', 'ihc');?></a>
	<a class="ihc-subtab-menu-item" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=options');?>"><?php esc_html_e('Account Page', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>
<?php endif;?>
<?php
echo ihc_inside_dashboard_error_license();
echo iump_is_wizard_uncompleted_but_not_skiped();
?>

<div class="iump-wrapper">
	<?php if ($is_uap_active): ?>
		<!--div class="ihc-dashboard-title">
			Ultimate Membership Pro -
			<span class="second-text">
				<?php esc_html_e('Affiliates', 'ihc');?>
			</span>
		</div-->
		<div class="iump-page-headline"><?php esc_html_e('Manage Affiliates', 'ihc');?></div>
	<?php endif; ?>

		<?php if ($is_uap_active):?>
				<?php
				if (empty($_GET['subtab']) || sanitize_text_field($_GET['subtab'])=='list'):

					global $indeed_db;
					/*
					if ( isset($_POST['iump-datatable-submit'] ) && isset( $_POST['iump-action'] )
						&& $_POST['iump-action'] !== '' && isset( $_POST['users'] ) && $_POST['users'] !== '' ){
							$targetUsers = indeed_sanitize_array($_POST['users']);
							if ( $_POST['iump-action'] === 'remove' ){
									foreach ( $targetUsers as $uID ){
											// remove from affiliate list
											$indeed_db->remove_user_from_affiliate( $uID );
									}
							} else if ( $_POST['iump-action'] === 'add' ){
									// make the selected users affiliates
									$default_rank = get_option('uap_register_new_user_rank');
									foreach ( $targetUsers as $uID ){
										$inserted = $indeed_db->save_affiliate( $uID );
										if ( $inserted ){
												/// put default rank on this new affiliate
												$indeed_db->update_affiliate_rank_by_uid( $uID, $default_rank);
										}
									}
							}

					}
					*/
					$tableDataType = 'affiliates';
					$columns = [
				    [
				        'data'        => 'id',
				        'orderable'   => false,
								'sortable'		=> false,
				    ],
				    [
				        'data'        => 'user_login',
				        'render'      => [
				                  'display' => 'display',
				                  'sort'    => 'value',
				        ]
				    ],
				    [
				        'data'      => 'name',
				        'orderable' => false
				    ],
				    [
				        'data'      => 'user_email'
				    ],
				    [
				        'data'      => 'affiliate',
				        'orderable' => false
				    ],
				    [
				        'data'    => 'registered',
				        'render'  => [
				                      'display'   => 'display',
				                      'sort'      => 'value',
				        ]
				    ]
				  ];

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
					wp_enqueue_script( 'ihcdatabsescrolltop', IHC_URL . 'admin/assets/js/datatables/dataTables.scrollToTop.min.js', ['jquery'], '12.7' );
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
						  wp_add_inline_script( 'ihc-table', "var iump_datatable_type='$tableDataType';" );
				  }
				  wp_enqueue_script( 'ihc-table' );

					?>
					<!-- Page State -->
					<?php $pageState = get_option( 'ihc_datatable_state_for-affiliates', false );?>
					<?php if ( $pageState !== false ):?>
						<div class="iump-js-datatable-state" data-value='<?php echo stripslashes( $pageState );?>'></div>
					<?php endif;?>
					<!-- End of Page State -->
			<form action="" method="post" class="ihc-affiliates-lists-wrapper" data-remove_many_affiliates="<?php esc_html_e( 'Are You sure You wish to remove the selected users from affiliate list?', 'ihc' );?>" data-add_many_affiliates="<?php esc_html_e( 'Are You sure You wish to add the selected users to affiliate list?', 'ihc' );?>" >

					<div class="iump-datatable-filters-wrapper">
				          <input type="text" value="" placeholder="Search" class="iump-js-search-phrase">
									<?php $roles = ihc_get_wp_roles_list();?>
									<div class="iump-datatable-multiselect-wrapp">
						          <select name="role[]" class="iump-datatable-filter-show-only-role " multiple data-placeholder="<?php esc_html_e("Target Role", 'ihc');?>">
													<?php foreach ( $roles as $value => $label ):?>
						              <option value="<?php echo $value;?>"><?php echo $label;?></option>
													<?php endforeach;?>
						          </select>
									</div>
									<select name="is_affiliate" class="iump-js-datatable-is-affiliate">
											<option value="all"><?php esc_html_e('All Members', 'ihc');?></option>
											<option value="users"><?php esc_html_e('Only non-affiliates', 'ihc');?></option>
											<option value="affiliates"><?php esc_html_e('Only affiliates', 'ihc');?></option>
									</select>
									<!--button class="iump-datatable-filter-bttn"><?php esc_html_e('Filter', 'ihc');?></button-->

				  </div>

					<table id="iump-dashboard-table" class="display iump-dashboard-table ihc-display-none" >
					        <thead>
					            <tr>
				                  <th class=""><input type="checkbox" class="iump-js-select-all-checkboxes" data-target="iump-dashboard-table"/></th>
					                <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Username', 'ihc');?></th>
					                <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Name', 'ihc');?></th>
					                <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Email Address', 'ihc');?></th>
					                <th><?php esc_html_e('Status', 'ihc');?></th>
					                <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Join date', 'ihc');?></th>
					            </tr>
					        </thead>
					        <!--tfoot>
					            <tr>
													<th><?php esc_html_e('Username', 'ihc');?></th>
													<th><?php esc_html_e('Name', 'ihc');?></th>
													<th><?php esc_html_e('Email Address', 'ihc');?></th>
													<th><?php esc_html_e('Affiliate', 'ihc');?></th>
													<th><?php esc_html_e('Join date', 'ihc');?></th>
					            </tr>
					        </tfoot-->
					    </table>

							<div class="iump-datatable-actions-wrapp-copy ihc-display-none">
									<select name="iump-action" class="iump-datatable-select-field iump-js-bulk-action-select">
											<option value="" disabled selected ><?php esc_html_e( 'Bulk Actions', 'ihc' );?></option>
											<option value="remove"><?php esc_html_e('Remove from affiliate', 'ihc');?></option>
											<option value="add"><?php esc_html_e('Make affiliate', 'ihc');?></option>
									</select>
									<input type="submit" name="iump-datatable-submit" value="<?php esc_html_e('Apply', 'ihc');?>" class="button button-primary button-small iump-js-affiliates-apply-bttn" />
							</div>
				</form>
				<?php
					else :
						///////////////////////// OPTIONS
						if (!empty( $_POST['ihc_save'] ) ){
							ihc_save_update_metas('affiliate_options');
						}
						$meta_arr = ihc_return_meta_arr('affiliate_options');
				?>
					<form method="post" >
						<div class="ihc-stuffbox">
							<h3><?php esc_html_e('Account Page - Affiliate Tab', 'ihc');?></h3>
							<div class="inside">
								<div class="row ihc-row-no-margin">
								<div class="col-xs-10">
								<div class="iump-form-line iump-no-border">
									<h2><?php esc_html_e('Show/Hide Affiliate Tab', 'ihc');?></h2>
									<label class="iump_label_shiwtch iump-onbutton">
										<?php $checked = ($meta_arr['ihc_ap_show_aff_tab']) ? 'checked' : ''; ?>
										<input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_ap_show_aff_tab');" <?php echo esc_attr($checked);?>>
										<div class="switch  ihc-display-inline"></div>
									</label>

									<p><?php esc_html_e('Choose if you wish to show the Affiliate tab directly in Ultimate Membership Pro Account Page section', 'ihc');?></p>
									<input type="hidden" name="ihc_ap_show_aff_tab" id="ihc_ap_show_aff_tab" value="<?php echo esc_attr($meta_arr['ihc_ap_show_aff_tab']);?>" />
								</div>
								<div class="iump-form-line iump-no-border">
									<h4><?php esc_html_e('Default Tab Content', 'ihc');?></h4>
									<div  class="iump-wp_editor">
										<?php wp_editor(stripslashes($meta_arr['ihc_ap_aff_msg']), 'ihc_ap_aff_msg', array('textarea_name'=>'ihc_ap_aff_msg', 'editor_height'=>200));?>
									</div>
								</div>
								<div class="iump-form-line iump-no-border">
									<?php echo esc_html__("You can add 'Become Button' with the following shortcode: ", 'ihc') . '<b>[uap-user-become-affiliate]</b>';?>
								</div>
							</div>
						</div>
								<div class="ihc-wrapp-submit-bttn iump-submit-form">
									<input type="submit" value="Save Changes" name="ihc_save" class="button button-primary button-large">
								</div>
							</div>
						</div>
					</form>
				<?php endif;?>

		<?php else:?>
		<div class="metabox-holder indeed">
		<div class="ihc-stuffbox ihc-aff-message">
			<div class="ihc-warning-box">
					To get this section Available <a href="https://ultimateaffiliate.pro" target="_blank">Ultimate Affiliate Pro</a> Plugin needs to be activated on your WordPress website.
			</div>
			<div class="ihc-aff-message-name">Ultimate Affiliate Pro </div>
			<div class="ihc-aff-message-title">The most Complete Affiliate Program Plugin for WordPress</div>
			<div class="ihc-aff-message-description">
				<p><strong>Ultimate Affiliate Pro</strong> is the newest and most completed Affiliate WordPress Plugin that allow you provide a premium platform for your Affiliates with different rewards and amounts based on Ranks or special Offers.</p>
				<p>You can turn on your Website into a REAL business and an income machine where you just need to sit down and let the others to work for you!</p>
				<p>Each Affiliate can creates his own marketing Campaign and brings more Affiliates via the <strong>“Multi-Level-Marketing”</strong> strategy.</p>

				<div><a href="https://ultimateaffiliate.pro" target="_blank"  id="ihc_submit_bttn">Get Ultimate Affiliate Pro Now</a></div>
			</div>
			<div class="ihc-aff-message-additional">
				<a href="https://ultimateaffiliate.pro" target="_blank">
					<img src="<?php echo IHC_URL;?>admin/assets/images/uap-image-preview.jpg" class="ihc-display-block"/>
				</a>
				<a href="https://ultimateaffiliate.pro" target="_blank">
					<img src="<?php echo IHC_URL;?>admin/assets/images/uap_prev1.png" class="ihc-display-block"/>
				</a>
				<a href="https://ultimateaffiliate.pro" target="_blank">
					<img src="<?php echo IHC_URL;?>admin/assets/images/uap_prev2.png" class="ihc-display-block"/>
				</a>
				<a href="https://ultimateaffiliate.pro" target="_blank">
					<img src="<?php echo IHC_URL;?>admin/assets/images/uap_prev3.png" class="ihc-display-block"/>
				</a>
				<a href="https://ultimateaffiliate.pro" target="_blank">
					<img src="<?php echo IHC_URL;?>admin/assets/images/uap_prev4.png" class="ihc-display-block"/>
				</a>
				<a href="https://ultimateaffiliate.pro" target="_blank">
					<img src="<?php echo IHC_URL;?>admin/assets/images/uap_prev5.png" class="ihc-display-block"/>
				</a>

			</div>

		</div>
		</div>
		<?php endif;?>
	<div class="ihc-clear"></div>
</div>
<?php

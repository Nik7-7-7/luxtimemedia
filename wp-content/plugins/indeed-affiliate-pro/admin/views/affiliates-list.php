<?php
		wp_enqueue_script( 'uapAdminSendEmail', UAP_URL . 'assets/js/uapAdminSendEmail.js', array('jquery'), null );
		wp_enqueue_script( 'indeed_csv_export', UAP_URL . 'assets/js/csv_export.js', array('jquery'), null  );
		$ranks = $indeed_db->get_rank_list();

		$defaultRank = isset( $_GET['rank_id'] ) ? sanitize_text_field( $_GET['rank_id'] ) : false;
?>


<div class="uap-wrapper uap-affiliate-list-wrapper">
		<div class="uap-page-title"><?php esc_html_e('Manage Affiliates', 'uap');?></div>
		<div class="uap-page-top-options">
			<a href="<?php echo esc_url($data['url-add_edit']);?>" class="uap-add-new-like-wp"><i class="fa-uap fa-add-uap"></i><span><?php esc_html_e('Add new Affiliate', 'uap');?></span></a>
			<a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=new_payout' );?>" target="_blank" class="uap-add-new-like-wp uap-second-main-button"><span><?php esc_html_e('Pay Affiliates', 'uap');?></span></a>
		</div>
		<?php echo esc_uap_content($data['errors']);?>

		<div class="uap-special-buttons-users">
			<?php
					$filters = [
						'rank' 									=> empty($_REQUEST['ordertype_rank']) ? '' : $_REQUEST['ordertype_rank'],
					];
			?>
			<div class="uap-special-button js-uap-export-csv"  data-filters='<?php echo ( isset($_REQUEST['ordertype_rank']) ) ? json_encode($filters) : '';?>' data-export_type="affiliates"  >
					<i class="fa-uap fa-export-csv"></i><?php esc_html_e( 'Export CSV', 'uap' );?>
			</div>

		</div>



					<!-- Start DataTable -->
					<?php
					// 1. Datatable - define table name. used in js.
					$tableDataType = 'affiliates';

					// 2. Datatable - define columns
					$columns = [
												[
															'data'				=> 'checkbox',
															'title'				=> '<input type=checkbox class=uap-js-select-all-checkboxes />',
															'orderable'		=> false,
															'sortable'		=> false,
												],
												[
															'data' 				=> 'id',
															'title'				=> esc_html__('ID', 'uap'),
															'orderable'   => true,
															'sortable'		=> true,
											        'render'  		=> [
													                      'display'   => 'display',
													                      'sort'      => 'value',
											        ]
												],
												[
															'data' 				=> 'top',
															'title'				=> esc_html__('Top', 'uap'),
															'orderable'   => false,
															'sortable'		=> false,
											        'render'  		=> [
													                      'display'   => 'display',
													                      'sort'      => 'value',
											        ]
												],
												[
															'data' 				=> 'name',
															'title'				=> esc_html__('Name', 'uap'),
															'orderable'   => true,
															'sortable'		=> true,
											        'render'  		=> [
													                      'display'   => 'display',
													                      'sort'      => 'value',
											        ],
															'className'		=> 'uap-max-width-200',
												],
												[
															'data' 				=> 'email',
															'title'				=> esc_html__('Email Address', 'uap'),
															'orderable'   => true,
															'sortable'		=> true,
															'className'		=> 'uap-max-width-150',
											        'render'  		=> [
													                      'display'   => 'display',
													                      'sort'      => 'value',
											        ]
												],
												[
															'data' 				=> 'rank',
															'title'				=> esc_html__('Rank', 'uap'),
															'orderable'   => false,
															'sortable'		=> false,
															'className'		=> 'uap-max-width-100',
												],
												[
															'data'				=> 'rate',
															'title'				=> esc_html__('Rate', 'uap'),
															'orderable'		=> false,
															'sortable'		=> false,
												],
												[
															'data'				=> 'clicks',
															'title'				=> esc_html__('Clicks', 'uap'),
															'orderable'   => false,
															'sortable'		=> false,
											        'render'  		=> [
													                      'display'   => 'display',
													                      'sort'      => 'value',
											        ]
												],
												[
															'data'				=> 'referrals',
															'title'				=> esc_html__('Referrals', 'uap'),
															'orderable'   => false,
															'sortable'		=> false,
											        'render'  		=> [
													                      'display'   => 'display',
													                      'sort'      => 'value',
											        ]
												],
												[
															'data'				=> 'paid_earnings',
															'title'				=> esc_html__('Paid Earnings', 'uap'),
															'orderable'   => false,
															'sortable'		=> false,
											        'render'  		=> [
													                      'display'   => 'display',
													                      'sort'      => 'value',
											        ]
												],
												[
															'data'				=> 'unpaid_earnings',
															'title'				=> esc_html__('Unpaid Earnings', 'uap'),
															'orderable'   => false,
															'sortable'		=> false,
											        'render'  		=> [
													                      'display'   => 'display',
													                      'sort'      => 'value',
											        ]
												],
												[
															'data'				=> 'metrics',
															'title'				=> esc_html__('Metrics', 'uap'),
															'orderable'		=> false,
															'sortable'		=> false,
															'visible'	  	=> false,
												],
												[
															'data'				=> 'role',
															'title'				=> esc_html__('Role', 'uap'),
															'orderable'		=> false,
															'sortable'		=> false,
															'visible'	  	=> false,
												],
												[
															'data'				=> 'email_verification',
															'title'				=> esc_html__('Email Status', 'uap'),
															'orderable'		=> false,
															'sortable'		=> false,
															'visible'	  	=> false,
												],
												[
															'data'				=> 'register_date',
															'title'				=> esc_html__('Affiliate Since', 'uap'),
															'orderable'   => true,
															'sortable'		=> true,
											        'render'  		=> [
													                      'display'   => 'display',
													                      'sort'      => 'value',
											        ]
												],
												[
															'data'				=> 'details',
															'title'				=> esc_html__('Details', 'uap'),
															'orderable'		=> false,
															'sortable'		=> false,
												],
					];
					// End of 2. Datatable - define columns

					$email_verification = $indeed_db->is_magic_feat_enable('email_verification');
					if ( !$email_verification ){
							unset( $columns[13] );
							$columns = array_values( $columns );
					}


					// 3. Datatable - Js and CSS for datatable
					\Indeed\Uap\Admin\DataTable::Scripts( $columns, $tableDataType );

					?>

					<!-- 4. Datatable - Js confirm messages -->
					<div class="uap-js-messages-for-datatable"
							data-remove_one_item="<?php esc_html_e('Are You sure You want to remove this affiliate?', 'uap');?>"
							data-remove_many_items="<?php esc_html_e('Are You sure You want to remove selected affiliates?', 'uap');?>" ></div>
					<!-- End of 4. Datatable - Js confirm messages -->

							<!-- 5. Datatable - Custom Search + Filter -->
							<div class="uap-datatable-filters-wrapper">
											<input type="text" value="" placeholder="<?php esc_html_e("Search Affiliates", 'uap');?>" class="uap-js-search-phrase uap-max-width-300">
											<div class="uap-datatable-multiselect-wrapp">
													<select name="ranks[]" class="uap-js-datatable-filter-ranks" multiple data-placeholder="<?php esc_html_e("Ranks", 'uap');?>">
															<?php if ( $ranks ):?>
																	<?php foreach ( $ranks as $rankId => $rankLabel ):?>
																			<?php $selected = $defaultRank && (int)$defaultRank === $rankId ? 'selected' : '';?>
																			<option <?php echo $selected;?> value="<?php echo $rankId;?>"><?php echo $rankLabel;?></option>
																	<?php endforeach;?>
															<?php endif;?>

													</select>
											</div>
											<button class="uap-datatable-filter-bttn"><?php esc_html_e('Filter', 'uap');?></button>
							</div>
							<!-- End of 5. Datatable - Custom Search + Filter -->

							<!-- 6. Datatable - the table html -->
							<table id="uap-dashboard-table" class="display uap-dashboard-table" >

							</table>
							<!-- End of 6. Datatable - the table html -->

							<!-- 7. Datatable - Bulk actions -->
							<div class="uap-datatable-actions-wrapp-copy uap-display-none">
									<select name="uap-action" class="uap-datatable-select-field uap-js-bulk-action-select">
											<option value="" disabled selected ><?php esc_html_e( 'Bulk Actions', 'uap' );?></option>
											<option value="remove"><?php esc_html_e('Remove', 'uap');?></option>
											<option value="update_rank"><?php esc_html_e('Update Rank', 'uap');?></option>
									</select>
									<input type="submit" name="uap-datatable-submit" value="<?php esc_html_e('Apply', 'uap');?>" class="button button-primary button-small uap-js-items-apply-bttn" />
							</div>
							<!-- End of 7. Datatable - Bulk actions -->

							<!-- 8. Page State -->
							<?php $pageState = get_option( 'uap_datatable_state_for-affiliates', false );?>
							<?php if ( $pageState !== false && !empty( $pageState )  ):?>
									<div class="uap-js-datatable-state" data-value='<?php echo stripslashes( $pageState );?>' ></div>
							<?php endif;?>
							<!-- End of 8. Page State -->

							<div class="uap-js-datatable-listing-delete-nonce" data-value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>"></div>
					<!-- End DataTable -->



</div>

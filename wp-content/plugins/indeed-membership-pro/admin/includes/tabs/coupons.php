<?php
	$subtab = isset( $_GET['subtab'] ) ? sanitize_text_field($_GET['subtab']) : 'manage';
?>
<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item <?php echo ( $subtab =='add_edit') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url( $url . '&tab='. $tab . '&subtab=add_edit' );?>"><?php esc_html_e('Add Single Coupon', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ( $subtab =='multiple_coupons') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url ( $url . '&tab=' . $tab . '&subtab=multiple_coupons' );?>"><?php esc_html_e('Add Bulk Coupons', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ( $subtab =='manage' ) ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url( $url . '&tab=' . $tab . '&subtab=manage' );?>"><?php esc_html_e('Manage Coupons', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>
<?php
	echo ihc_check_default_pages_set();//set default pages message
	echo ihc_check_payment_gateways();
	echo ihc_is_curl_enable();
	do_action( "ihc_admin_dashboard_after_top_menu" );


	if ($subtab=='manage'){
		/// save
		if (isset($_POST['ihc_bttn'])  && !empty($_POST['ihc_admin_coupons_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_coupons_nonce']), 'ihc_admin_coupons_nonce' ) ){

			if ( !isset( $_POST['target_level'] ) ){
					$_POST['target_level'][] = -1;
			}
			if ( in_array( -1, $_POST['target_level'] ) ){
					$_POST['target_level'] = [ -1 ];
			}
			$_POST['target_level'] = implode( ',', indeed_sanitize_array($_POST['target_level']) );

			if (empty($_POST['id'])){
				//create
				ihc_create_coupon( indeed_sanitize_array( $_POST ) );
			} else {
				//update
				ihc_update_coupon( indeed_sanitize_array($_POST) );
			}
		}
		///print the coupons
		?>
		<div class="iump-wrapper">
		<div class="iump-page-headline">
							<?php esc_html_e('Manage Coupons', 'ihc');?>
		</div>
		<div class="imup-page-top-options">
			<a href="<?php echo esc_url( $url . '&tab='. $tab . '&subtab=add_edit' );?>" class="indeed-add-new-like-wp"><i class="fa-ihc fa-add-ihc"></i><?php esc_html_e('Add New Coupon', 'ihc');?></a>
			<span class="ihc-top-message"><?php esc_html_e('...create your Discount Code!', 'ihc');?></span>
		</div>
		<?php
		$coupons = ihc_get_all_coupons();
		if ($coupons){

			/*
			// bulk action
			if ( isset( $_POST['iump-datatable-submit'] ) && isset( $_POST['coupon_id'] ) ){
					$target = indeed_sanitize_array( $_POST['coupon_id'] );
					if ( count($target) && $_POST['iump-action'] === 'remove' ){
							foreach ( $target as $couponId ){
									// delete each order
									ihc_delete_coupon( $couponId );
							}
					}
			}
			*/

			$tableDataType = 'coupons';
			$columns = [
						[
								'data'        => 'select_item',
								'orderable'   => false,
								'sortable'		=> false,
						],
						[
								'data'        => 'code',
						],
						[
								'data'        => 'target_membership',
								'orderable'   => false,
								'sortable'		=> false,
						],
						[
								'data'        => 'discount',
								'orderable'   => false,
								'sortable'		=> false,
						],
						[
								'data'        => 'period_type',
								'orderable'   => false,
								'sortable'		=> false,
						],
						[
								'data'        => 'submited_coupons',
								'orderable'   => false,
								'sortable'		=> false,
						],
						[
								'data'        => 'recurring_behavior',
								'orderable'   => false,
								'sortable'		=> false,
						],
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
					wp_add_inline_script( 'ihc-table', "var iump_datatable_type='$tableDataType';" );
			}
			wp_enqueue_script( 'ihc-table' );


			?>
			<!-- Page State -->
			<?php $pageState = get_option( 'ihc_datatable_state_for-coupons', false );?>
			<?php if ( $pageState !== false ):?>
				<div class="iump-js-datatable-state" data-value='<?php echo stripslashes( $pageState );?>'></div>
			<?php endif;?>
			<!-- End of Page State -->

			<div class="iump-rsp-table">
				<form action="" method="post" class="ihc-coupons-lists-wrapper" data-delete_many_coupons="<?php esc_html_e( 'Are You sure You wish to remove the selected coupons?', 'ihc' );?>" >
				<table id="iump-dashboard-table" class="display iump-dashboard-table ihc-display-none iump-js-coupons-table"  >
								<thead>
										<tr>
												<th class=""><input type="checkbox" class="iump-js-select-all-checkboxes" data-target="iump-dashboard-table"/></th>
												<th class="iump-dashboard-table-head-col iump-dashboard-table-coupon-code iump-dashboard-table-col-sorting"><?php esc_html_e('Code', 'ihc');?></th>
												<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Target Memberships', 'ihc');?></th>
												<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Discount', 'ihc');?></th>
												<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Available Time', 'ihc');?></th>
												<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Usage / Limit', 'ihc');?></th>
												<!--th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Short Description', 'ihc');?></th-->
												<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Recurring Subscriptions Behaviour', 'ihc');?></th>

										</tr>
								</thead>
						</table>
						<div class="iump-datatable-actions-wrapp-copy ihc-display-none">
								<select name="iump-action" class="iump-datatable-select-field iump-js-bulk-action-select">
										<option value="" disabled selected ><?php esc_html_e( 'Bulk Actions', 'ihc' );?></option>
										<option value="remove"><?php esc_html_e('Remove', 'ihc');?></option>
								</select>
								<input type="submit" name="iump-datatable-submit" value="<?php esc_html_e('Apply', 'ihc');?>" class="button button-primary button-small iump-js-coupons-apply-bttn" />
						</div>
				</form>
		</div>
	</div>
			<?php
			/*
			$base_edit_url = $url.'&tab='.$tab.'&subtab=add_edit';
			foreach ($coupons as $id => $coupon){
				ihc_generate_coupon_box($id, $coupon, $base_edit_url);
			}
			*/
		} else {
			?>
			<div class="ihc-warning-message"><?php esc_html_e(" No Coupons available! Please create your first Coupon", "ihc");?></div>
			<?php
		}
	} else {
		$meta_arr = ihc_get_coupon_by_id((isset($_GET['id'])) ? sanitize_text_field($_GET['id']) : 0);
		?>

		<div class="iump-page-headline">
							<?php esc_html_e('Add Single Coupon', 'ihc');?>
		</div>
			<form method="post" action="<?php echo esc_url($url . '&tab=' . $tab . '&subtab=manage');?>">

				<input type="hidden" name="ihc_admin_coupons_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_coupons_nonce' );?>" />

				<div class="ihc-stuffbox">
					<?php if (!empty($_GET['id'])){?>
					<h3><?php esc_html_e("Coupon Settings", 'ihc');?></h3>
					<?php
						$idOfCoupon = (int)sanitize_text_field($_GET['id']);
					?>
					<input type="hidden" name="id" value="<?php echo $idOfCoupon;?>" />
					<?php } else { ?>
					<h3><?php esc_html_e("Add New Coupon", 'ihc');?></h3>
					<?php } ?>
					<div class="inside ump-coupon-add">
						<?php
							if ($subtab=='multiple_coupons'){
								//////////////// MULTIPLE COUPONS ////////////
								?>
								<div class="iump-form-line">
									<h2><?php esc_html_e("Generate Bulk Discount Codes", 'ihc');?></h2>
									<p><?php esc_html_e("Choose the Discount Code format and how many you wish to generate and Ultimate Membership Pro will generate them for you", 'ihc');?></p>
								</div>
								<div class="iump-form-line">
									<h4><?php esc_html_e("Initial Discount Code prefix", 'ihc');?></h4>
									<div class="row">
								      <div class="col-xs-4">
								                 <div class="input-group">
								                    <span class="input-group-addon"><?php esc_html_e('Code prefix', 'ihc');?></span>
								                    <input class="form-control"  type="text" value="" name="code_prefix">
								                 </div>
								         </div>
								     </div>
									<h4><?php esc_html_e("Discount Code Length", 'ihc');?></h4>
									<div class="row">
								      <div class="col-xs-4">
								                 <div class="input-group">
								                    <span class="input-group-addon"><?php esc_html_e('Length', 'ihc');?></span>
								                    <input class="form-control"  type="number" min="2" value="10" name="code_length" />
								                 </div>
								         </div>
								     </div>
									<h4><?php esc_html_e("Number of Generated Discount Codes", 'ihc');?></h4>
									<div class="row">
								      <div class="col-xs-4">
								                 <div class="input-group">
								                    <span class="input-group-addon"><?php esc_html_e('Number of Codes', 'ihc');?></span>
								                    <input class="form-control"  type="number" min="2" value="2" max="100" name="how_many_codes" />
								                 </div>
								         </div>
								     </div>
								</div>
								<?php
							} else {
								/////////////// ONE /////////////
								?>
								<div class="iump-form-line">
									<h4 class="iump-option-title"><?php esc_html_e("Coupon Code", 'ihc');?></h4>
									<p><?php esc_html_e("Choose the Coupon Code that will be used on Checkout Page for getting discounted price. Only alphanumeric characters are allowed", 'ihc');?></p>
									<input type="text" value="<?php echo esc_attr($meta_arr['code']);?>" name="code" id="ihc_the_coupon_code" /> <span class="ihc-generate-coupon-button iump-second-button" onClick="ihcGenerateCode('#ihc_the_coupon_code', 10);"><?php esc_html_e("Generate Code", "ihc");?></span>
								</div>
								<?php
							}
						?>

						<div class="iump-form-line">
							<h4><?php esc_html_e("Short Description", 'ihc');?></h4>
							<textarea name="description" class="ihc-coupon-description"><?php echo (isset($meta_arr['description'])) ? $meta_arr['description'] : '';?></textarea>
						</div>

						<div class="iump-special-line">
							<div class=" iump-form-line">
								<h2><?php esc_html_e("Discount Management", 'ihc');?></h2>
								<p><?php esc_html_e("Choose how discount will be calculated based on Membership price or Flat Amount and the value of it", 'ihc');?></p>
							</div>
							<div class=" iump-form-line">
							<div class="row">
						      <div class="col-xs-8">
										<h4><?php esc_html_e("Type of Discount", 'ihc');?></h4>
										<select name="discount_type" onChange="ihcDiscountType(this.value);" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select"><?php
											$arr = array('price' => esc_html__("Price", 'ihc'), 'percentage'=>"Percentage (%)");
											foreach ($arr as $k=>$v){
												$selected = ($meta_arr['discount_type']==$k) ? 'selected' : '';
												?>
													<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
												<?php
											}
										?></select>
									</div>
								</div>
						</div>
						<div class=" iump-form-line">
							<h4><?php esc_html_e("Discount Value", 'ihc');?></h4>
							<input type="number" step="0.01" value="<?php echo esc_attr($meta_arr['discount_value']);?>" name="discount_value"/>

							<span id="discount_currency" class="<?php if ($meta_arr['discount_type']=='price'){
								 echo esc_attr('ihc-display-inline');
							}else{
								 echo esc_attr('ihc-display-none');
							}
							?>">
								<?php echo get_option('ihc_currency');?>
							</span>
							<span id="discount_percentage" class="<?php if ($meta_arr['discount_type']=='percentage'){
								 echo esc_attr('ihc-display-inline');
							}else{
								 echo esc_attr('ihc-display-none');
							}
							?>">%</span>
						</div>
						</div>
						<div class="iump-form-line">
							<h2><?php esc_html_e("Discount Campaign", 'ihc');?></h2>
							<p><?php esc_html_e("You may have the Discount Coupon available only for certain period of time, between specific Dates and how many times may be used", 'ihc');?></p>
						</div>

						<div class="iump-form-line">
						<div class="row">
								<div class="col-xs-6">
									<h4><?php esc_html_e("Available Time", 'ihc');?></h4>
									<select name="period_type" onChange="ihcSelectShDiv(this, '#the_date_range', 'date_range');" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select"><?php
										$arr = array('date_range' => esc_html__("Date Range", 'ihc'), 'unlimited'=>esc_html__("Unlimited", 'ihc'));
										foreach ($arr as $k=>$v){
											$selected = ($meta_arr['period_type']==$k) ? 'selected' : '';
											?>
												<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
											<?php
										}
									?></select>
								</div>
							</div>
						</div>

						<div id="the_date_range" class="iump-form-line <?php if (isset($meta_arr['period_type']) && $meta_arr['period_type']=='date_range'){
							 echo esc_attr('ihc-display-block');
						}else{
							 echo esc_attr('ihc-display-none');
						}
						?>">
						<div class="row">
								<div class="col-xs-6">
									<h4><?php esc_html_e("Date Range", 'ihc');?></h4>
									<input type="text" name="start_time" id="ihc_start_time" value="<?php echo esc_attr($meta_arr['start_time']);?>" /> - <input type="text" name="end_time" id="ihc_end_time" value="<?php echo esc_attr($meta_arr['end_time']);?>" />
								</div>
							</div>
						</div>

						<div class="iump-form-line">
						<div class="row">
								<div class="col-xs-6">
							<h4><?php esc_html_e("Usage Limit", 'ihc');?></h4>
							<p><?php esc_html_e("The maximum number of times this Discount Code can be used. Leave blank for unlimited", 'ihc');?></p>


						                 <div class="input-group">
						                    <span class="input-group-addon"><?php esc_html_e('Limit', 'ihc');?></span>
						                    <input class="form-control"type="number" value="<?php echo esc_attr($meta_arr['repeat']);?>" name="repeat" min="1"/>
						                 </div>
						         </div>
						     </div>
						</div>
						<div class="iump-form-line">
						<div class="row">
								<div class="col-xs-6">
										<h2><?php esc_html_e("Memberships Requirement", 'ihc');?></h2>
										<p><?php esc_html_e("Select Membership targeted to this discount. If is selected All, this Discount code can be used on any Membership", 'ihc');?></p>
										<select name="target_level[]" multiple class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select" ><?php
											$levels = \Indeed\Ihc\Db\Memberships::getAll();
											if ($levels && count($levels)){
												$levels_arr[-1] = esc_html__("All", 'ihc');
												foreach ($levels as $k=>$v){
													$levels_arr[$k] = $v['name'];
												}
											}
											if ( strpos( $meta_arr['target_level'], ',') === false ){
												foreach ($levels_arr as $k=>$v){
													$selected = ($meta_arr['target_level']==$k) ? 'selected' : '';
													?>
														<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
													<?php
												}
											} else {
													$meta_arr['target_level'] = explode( ',', $meta_arr['target_level'] );
													foreach ($levels_arr as $k=>$v){
														$selected = in_array( $k, $meta_arr['target_level'] ) ? 'selected' : '';
														?>
															<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
														<?php
													}
											}

										?></select>
									</div>
								</div>
						</div>
						<div class="iump-form-line">
						<div class="row">
								<div class="col-xs-6">
							<h4><?php esc_html_e("On Recurring Subscriptions Behaviour", 'ihc');?></h4>
							<p><?php esc_html_e("Choose if you wish to apply discount only for Initial Payment or entire Billing Recurrence period", 'ihc');?></p>
							<select name="reccuring" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select"><?php
								$arr = array(0 => esc_html__("Just for Initial Payment", 'ihc'), 1 => esc_html__("Entire Billing Period", 'ihc'));
								foreach ($arr as $k=>$v){
									$selected = ($meta_arr['reccuring']==$k) ? 'selected' : '';
									?>
										<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
									<?php
								}
							?></select>
						</div>
					</div>
						</div>
						<input type="hidden" name="box_color" value="<?php echo esc_attr($meta_arr['box_color']);?>" />
						<div class="ihc-wrapp-submit-bttn">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_bttn" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</form>
		<?php
	}
?>

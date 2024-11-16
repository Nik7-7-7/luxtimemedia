<?php
	$posible_values = array('all'=>esc_html__('All', 'ihc'), 'reg'=>esc_html__('Registered Users', 'ihc'), 'unreg'=>esc_html__('Unregistered Users', 'ihc') );

	$levels = \Indeed\Ihc\Db\Memberships::getAll();
	if($levels){
		foreach($levels as $id=>$level){
			$posible_values[$id] = $level['label'];
		}
	}
	$pages = ihc_get_all_pages();//getting pages
$subtab = isset( $_GET['subtab'] ) ? sanitize_text_field($_GET['subtab']) : 'post_types';
?>
<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item <?php echo ( $subtab === 'post_types' ) ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=post_types');?>"><?php esc_html_e('All Posts', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab === 'cats') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=cats');?>"><?php esc_html_e('All Posts based on Categories', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab === 'files') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=files');?>"><?php esc_html_e('Specific Files', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab === 'entire_url') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=entire_url');?>"><?php esc_html_e('Entire URL', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ($subtab === 'keyword') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=keyword');?>"><?php esc_html_e('All Pages (based on Keywords)', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>

<?php
	echo ihc_inside_dashboard_error_license();
	echo iump_is_wizard_uncompleted_but_not_skiped();
	echo ihc_check_default_pages_set();//set default pages message
	echo ihc_check_payment_gateways();
	echo ihc_is_curl_enable();
	do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<div class="iump-wrapper">
<!--div class="iump-page-title">Ultimate Membership Pro -
							<span class="second-text">
								<?php esc_html_e('Access Rules', 'ihc');?>
							</span>
</div-->
<div class="iump-page-headline"><?php esc_html_e('Content Access Rules', 'ihc');?></div>
<form method="post"  id="block_url_form">

	<input type="hidden" name="ihc_admin_block_url_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_block_url_nonce' );?>" />

	<?php
		$subtab = isset($_REQUEST['subtab']) ? sanitize_text_field($_REQUEST['subtab']) : 'post_types';
		switch ($subtab):
			case 'entire_url':
				ihc_save_block_urls();//save/update block url
				ihc_delete_block_urls();//delete block url
			?>
			<div class="ihc-stuffbox">
				<h3><?php esc_html_e('Add new Restriction', 'ihc');?></h3>
				<div class="inside">
						<div class="iump-form-line">
							<h2><?php esc_html_e('Restrict Access Based on Entire URL', 'ihc');?></h2>
							<p><?php esc_html_e('Even if it is not about a static Post or Page, you can restrict any URL passing through your WordPress website', 'ihc');?></p>
						</div>
						<div class="iump-form-line">
							<div class="row">
                	<div class="col-xs-8">
                             <div class="input-group">
                                <span class="input-group-addon"><?php esc_html_e('Entire URL', 'ihc');?></span>
                                <input class="ihc-block-url-full-url form-control" type="text"  value="" name="ihc_block_url_entire-url" placeholder="<?php esc_html_e('copy the entire Link from your browser', 'ihc');?>">
                             </div>
                     </div>
                 </div>
						</div>

						<div class="iump-form-line iump-special-line">

							<div class="iump-form-line">
								<?php
									$type_values = array(
															'show' =>esc_html__('Show Only for...', 'ihc'),
															'block' =>esc_html__('Block Only for...', 'ihc')

									);

								?>
								<h4><?php esc_html_e('Restriction type', 'ihc'); ?></h4>
								<select name="block_or_show" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
									<?php foreach ($type_values as $k=>$v):?>
										<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
									<?php endforeach;?>
								</select>
							</div>

							<div class="iump-form-line">
								<h4><?php esc_html_e('Target Members', 'ihc');?></h4>
								<select id="ihc-change-target-user-set" onChange="ihcWriteTagValue(this, '#ihc_block_url_entire-target_users', '#ihc_tags_field1', 'ihc_select_tag_' );ihcRemoveNoticeAfterWriteTag();" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select ihc-block-url-select">
									<option value="-1" selected>...</option>
									<?php
										foreach($posible_values as $k=>$v){
										?>
											<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
										<?php
										}
									?>
								</select>
								<input type="hidden" value="" name="ihc_block_url_entire-target_users" id="ihc_block_url_entire-target_users" />
								<div id="ihc_tags_field1"></div>
								<div id="iump_admin_car_target_message" class="iump-admin-car-target-message" data-notice="<?php esc_html_e( 'Please complete this field', 'ihc');?>"></div>
							</div>
						</div>

						<div class="iump-form-line">
							<h4><?php esc_html_e('Redirect After', 'ihc');?></h4>
							<p><?php esc_html_e('Choose the location to which members will be redirected if access is restricted. The Default Redirect Page will be utilized if no specific option is chosen', 'ihc');?></p>
							<select name="ihc_block_url_entire-redirect" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" selected >...</option>
								<?php
									$pages = $pages + ihc_get_redirect_links_as_arr_for_select();
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>" ><?php echo esc_html($v);?></option>
											<?php
										}
									}
								?>
							</select>
						</div>

					<input type="hidden" value="" name="delete_block_url" id="delete_block_url" />

					<div class="ihc-wrapp-submit-bttn">
						<input type="submit" value="<?php esc_html_e('Add New Access Rule', 'ihc');?>" name="ihc_save_block_url" class="button button-primary button-large ihc_submit_bttn" />
					</div>
				</div>
			</div>
			<?php
				$data = get_option('ihc_block_url_entire');
				if ($data && count($data)){

											$tableDataType = 'car_entire_url';
											$columns = [
														[
																'data'        	=> 'id',
																'orderable'   	=> false,
																'sortable'			=> false,
														],
														[
																'data'        	=> 'entire_url',
														],
														[
																'data'        	=> 'restriction_type',
																'orderable'   	=> false,
																'sortable'			=> false,
														],
														[
																'data'        	=> 'target_members',
																'orderable'   	=> false,
																'sortable'			=> false,
														],
														[
																'data'        	=> 'redirect',
																'orderable'   	=> false,
																'sortable'			=> false,
														],
														[
																'data'        	=> 'actions',
																'orderable'   	=> false,
																'sortable'			=> false,
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
							    <?php $pageState = get_option( 'ihc_datatable_state_for-car_entire_url', false );?>
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
															<th class="iump-dashboard-table-head-col iump-dashboard-table-coupon-code iump-dashboard-table-col-sorting"><?php esc_html_e('Entire URL', 'ihc');?></th>
															<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Restriction Type', 'ihc');?></th>
															<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Target Members', 'ihc');?></th>
															<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Redirect After', 'ihc');?></th>
															<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Remove', 'ihc');?></td>
													</tr>
											</thead>
									</table>
									<div class="iump-datatable-actions-wrapp-copy ihc-display-none">
											<select name="iump-action" class="iump-datatable-select-field iump-js-bulk-action-select">
													<option value="" disabled selected ><?php esc_html_e( 'Bulk Actions', 'ihc' );?></option>
													<option value="remove"><?php esc_html_e('Remove', 'ihc');?></option>
											</select>
											<input type="submit" name="iump-datatable-submit" value="<?php esc_html_e('Apply', 'ihc');?>" class="button button-primary button-small iump-js-car-apply-bttn" />
									</div>
							</form>
					</div>

					<?php
				}
			break;
		case 'keyword':
				ihc_save_block_urls();//save/update block url
				ihc_delete_block_urls();//delete block url
			?>
				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Add new Restriction', 'ihc');?></h3>
					<div class="inside">
						<div class="iump-form-line">
							<h2><?php esc_html_e('Restrict any Page based on Keywords', 'ihc');?></h2>
							<p><?php esc_html_e('Based on a particular keyword located inside the Link, you can restrict a number of Pages that are running through your WordPress website', 'ihc');?></p>
						</div>
						<div class="iump-form-line">
							<div class="row">
									<div class="col-xs-4">
														 <div class="input-group">
																<span class="input-group-addon"><?php esc_html_e('Keyword', 'ihc');?></span>
																<input class="form-control" type="text" value="" name="ihc_block_url_word-url">
														 </div>
										 </div>
								 </div>
						</div>


							<div class="iump-form-line iump-special-line">
								<div class="iump-form-line">
									<?php
										$type_values = array(
																'show' =>esc_html__('Show Only for...', 'ihc'),
																'block' =>esc_html__('Block Only for...', 'ihc')

										);


									?>
									<h4><?php esc_html_e('Restriction type', 'ihc');?></h4>
									<select name="block_or_show" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
										<?php foreach ($type_values as $k=>$v):?>
											<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
										<?php endforeach;?>
									</select>
								</div>

								<div class="iump-form-line">
									<h4><?php esc_html_e('Target Members', 'ihc');?></h4>
									<select id="ihc-change-target-user-set-regex" onChange="ihcWriteTagValue(this, '#ihc_block_url_word-target_users', '#ihc_tags_field2', 'ihc_select_tag_regex_' );ihcRemoveNoticeAfterWriteTag();" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select ihc-block-url-select">
										<option value="-1" selected>...</option>
										<?php
											foreach($posible_values as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
											<?php
											}
										?>
									</select>
									<input type="hidden" value="" name="ihc_block_url_word-target_users" id="ihc_block_url_word-target_users" />
									<div id="ihc_tags_field2"></div>
									<div id="iump_admin_car_target_message" class="iump-admin-car-target-message" data-notice="<?php esc_html_e( 'Please complete this field', 'ihc');?>"></div>
								</div>
							</div>

							<div class="iump-form-line">
								<h4><?php esc_html_e('Redirect After', 'ihc');?></h4>
								<p><?php esc_html_e('Choose the location to which members will be redirected if access is restricted. The Default Redirect Page will be utilized if no specific option is chosen', 'ihc');?></p>
								<select name="ihc_block_url_word-redirect" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
									<option value="-1" selected >...</option>
									<?php
										$pages = $pages + ihc_get_redirect_links_as_arr_for_select();
										if ($pages){
											foreach($pages as $k=>$v){
												?>
													<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
												<?php
											}
										}
									?>
								</select>
							</div>
							<input type="hidden" value="" name="delete_block_regex" id="delete_block_regex" />
						<div class="ihc-wrapp-submit-bttn">
							<input type="submit" value="<?php esc_html_e('Add New Access Rule', 'ihc');?>" name="ihc_save_block_url" class="button button-primary button-large ihc_submit_bttn" />
						</div>
					</div>
				</div>
		<?php
				$data = get_option('ihc_block_url_word');
				if ($data && count($data)){
											$tableDataType = 'car_url_word';
											$columns = [
														[
																'data'        	=> 'id',
																'orderable'   	=> false,
																'sortable'			=> false,
														],
														[
																'data'        	=> 'url',
														],
														[
																'data'        	=> 'restriction_type',
																'orderable'   	=> false,
																'sortable'		=> false,
														],
														[
																'data'        	=> 'target_members',
																'orderable'   	=> false,
																'sortable'		=> false,
														],
														[
																'data'        	=> 'redirect',
																'orderable'   	=> false,
																'sortable'		=> false,
														],
														[
																'data'        	=> 'actions',
																'orderable'   	=> false,
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
													'emptyTable'		=> esc_html__( "Empty", 'ihc'),
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

						<div class="iump-rsp-table">
							<form action="" method="post" class="ihc-coupons-lists-wrapper" data-delete_many_coupons="<?php esc_html_e( 'Are You sure You wish to remove the selected coupons?', 'ihc' );?>" >
							<table id="iump-dashboard-table" class="display iump-dashboard-table ihc-display-none iump-js-coupons-table"  >
											<thead>
													<tr>
															<th class=""><input type="checkbox" class="iump-js-select-all-checkboxes" data-target="iump-dashboard-table"/></th>
															<th class="iump-dashboard-table-head-col iump-dashboard-table-coupon-code iump-dashboard-table-col-sorting"><?php esc_html_e('Target Pages with Keyword', 'ihc');?></th>
															<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Restriction Type', 'ihc');?></th>
															<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Target Members', 'ihc');?></th>
															<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Redirect After', 'ihc');?></th>
															<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Remove', 'ihc');?></td>
													</tr>
											</thead>
									</table>
									<div class="iump-datatable-actions-wrapp-copy ihc-display-none">
											<select name="iump-action" class="iump-datatable-select-field iump-js-bulk-action-select">
													<option value="" disabled selected ><?php esc_html_e( 'Bulk Actions', 'ihc' );?></option>
													<option value="remove"><?php esc_html_e('Remove', 'ihc');?></option>
											</select>
											<input type="submit" name="iump-datatable-submit" value="<?php esc_html_e('Apply', 'ihc');?>" class="button button-primary button-small iump-js-car-apply-bttn" />
									</div>
							</form>
					</div>

			<?php
				}
			break;
		case 'post_types':
		  /*
			// deprecated since version 12.0
			if (isset($_POST['delete_block']) && $_POST['delete_block']!='' && !empty($_POST['ihc_admin_block_url_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_block_url_nonce']), 'ihc_admin_block_url_nonce' ) ){
				/// ======================== DELETE
				ihc_delete_block_group('ihc_block_posts_by_type', sanitize_text_field($_POST['delete_block']));
			}
			*/
			if ( !empty($_POST['ihc_save']) && !empty($_POST['ihc_admin_block_url_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_block_url_nonce']), 'ihc_admin_block_url_nonce' ) ){
				/// ========================= ADD NEW
				unset($_POST['ihc_save']);
				ihc_save_block_group('ihc_block_posts_by_type', indeed_sanitize_array($_POST), sanitize_text_field($_POST['post_type']));
			}
			?>
			<form method="post" >

				<input type="hidden" name="ihc_admin_block_url_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_block_url_nonce' );?>" />

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Block All Posts By Type', 'ihc');?></h3>
					<div class="inside">
						<div class="iump-form-line iump-no-border">
							<h4><?php esc_html_e('Custom Post Type', 'ihc');?></h4>
							<p><?php esc_html_e('Choose one of the custom post types that is currently registered on your WordPress website', 'ihc');?></p>
							<select name="post_type" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
							<?php
								global $wp_post_types;
								$post_types = ihc_get_all_post_types();
								foreach ($post_types as $key):
									if (isset($wp_post_types[$key])){
										$obj = $wp_post_types[$key];
										$label =  $obj->labels->name;
									} else {
										$label = ucfirst($key);
									}
							?>
								<option value="<?php echo esc_attr($key);?>"><?php echo esc_html($label) . ' (' . esc_html($key) . ')';?></option>
							<?php
								endforeach;
							?>
							</select>
						</div>

						<div class="iump-form-line">
							<div><?php esc_html_e('Excluding', 'ihc');?></div>
							<input type="text" name="except" value="" class="form-control ihc-form-element-text" />
							<p><i><?php esc_html_e('Submit post IDs separated by commas. For Example: 30, 55, 102');?></i></p>
						</div>

						<div class="iump-form-line iump-special-line">
							<div class="iump-form-line">
								<?php
									$type_values = array(
															'show' =>esc_html__('Show Only for...', 'ihc'),
															'block' =>esc_html__('Block Only for...', 'ihc')

									);

								?>
								<h4><?php esc_html_e('Restriction Type', 'ihc');?></h4>
								<select name="block_or_show" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
									<?php foreach ($type_values as $k=>$v):?>
										<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
									<?php endforeach;?>
								</select>
							</div>

							<div class="iump-form-line">
								<h4><?php esc_html_e('Target Members', 'ihc');?></h4>
								<select id="ihc-change-target-user-set-regex" onChange="ihcWriteTagValue(this, '#target_users', '#ihc_tags_field2', 'ihc_select_tag_regex_' );ihcRemoveNoticeAfterWriteTag();" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select ihc-block-url-select">
									<option value="-1" selected>...</option>
									<?php
										foreach($posible_values as $k=>$v){
										?>
											<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
										<?php
										}
									?>
								</select>
								<input type="hidden" value="" name="target_users" id="target_users" />
								<div id="ihc_tags_field2"></div>
								<div id="iump_admin_car_target_message" class="iump-admin-car-target-message" data-notice="<?php esc_html_e( 'Please complete this field', 'ihc');?>"></div>
							</div>

						</div>


						<div class="iump-form-line"><h4><?php esc_html_e('Redirect After', 'ihc');?></h4>
						<p><?php esc_html_e('Choose the location to which members will be redirected if access is restricted. The Default Redirect Page will be utilized if no specific option is chosen', 'ihc');?></p>
							<select name="redirect" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" selected >...</option>
								<?php
									$pages = $pages + ihc_get_redirect_links_as_arr_for_select();
									if ($pages){
										foreach($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
										<?php
										}
									}
								?>
							</select>
						</div>

						<div class="ihc-wrapp-submit-bttn">
							<input type="submit" value="<?php esc_html_e('Add New Access Rule', 'ihc');?>" name="ihc_save" class="button button-primary button-large ihc_submit_bttn">
						</div>
					</div>
				</div>

			</form>
			<?php
				$data = get_option('ihc_block_posts_by_type');
				if ($data && count($data)){
								$tableDataType = 'car_posts';
								$columns = [
											[
													'data'        	=> 'id',
													'orderable'   	=> false,
													'sortable'		=> false,
											],
											[
													'data'        	=> 'target_post_type',
											],
											[
													'data'        	=> 'restriction_type',
													'orderable'   	=> false,
													'sortable'		=> false,
											],
											[
													'data'        	=> 'target_members',
													'orderable'   	=> false,
													'sortable'		=> false,
											],
											[
													'data'        	=> 'except',
													'orderable'   	=> false,
													'sortable'		=> false,
											],
											[
													'data'        	=> 'redirect',
													'orderable'   	=> false,
													'sortable'		=> false,
											],
											[
													'data'        	=> 'actions',
													'orderable'   	=> false,
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
										'emptyTable'		=> esc_html__( "Empty", 'ihc'),
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

									<div class="iump-rsp-table">
										<form action="" method="post" class="ihc-coupons-lists-wrapper" data-delete_many_coupons="<?php esc_html_e( 'Are You sure You wish to remove the selected coupons?', 'ihc' );?>" >
										<table id="iump-dashboard-table" class="display iump-dashboard-table ihc-display-none iump-js-coupons-table"  >
														<thead>
																<tr>
																		<th class=""><input type="checkbox" class="iump-js-select-all-checkboxes" data-target="iump-dashboard-table"/></th>
																		<th class="iump-dashboard-table-head-col iump-dashboard-table-coupon-code iump-dashboard-table-col-sorting"><?php esc_html_e('Target Post Type', 'ihc');?></th>
																		<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Restriction Type', 'ihc');?></th>
																		<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Target Members', 'ihc');?></th>
																		<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Excluding', 'ihc');?></th>
																		<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Redirect After', 'ihc');?></th>
																		<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Remove', 'ihc');?></td>
																</tr>
														</thead>
												</table>
												<div class="iump-datatable-actions-wrapp-copy ihc-display-none">
														<select name="iump-action" class="iump-datatable-select-field iump-js-bulk-action-select">
																<option value="" disabled selected ><?php esc_html_e( 'Bulk Actions', 'ihc' );?></option>
																<option value="remove"><?php esc_html_e('Remove', 'ihc');?></option>
														</select>
														<input type="submit" name="iump-datatable-submit" value="<?php esc_html_e('Apply', 'ihc');?>" class="button button-primary button-small iump-js-car-apply-bttn" />
												</div>
										</form>
								</div>

		<?php }
		break;
	case 'cats':
			/*
			// deprecated since version 12.0
			if (isset($_POST['delete_block']) && $_POST['delete_block']!='' && !empty($_POST['ihc_admin_block_url_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_block_url_nonce']), 'ihc_admin_block_url_nonce' ) ){
				/// ======================== DELETE
				ihc_delete_block_group('ihc_block_cats_by_name', sanitize_text_field($_POST['delete_block']));
			}
			*/
			if (!empty($_POST['ihc_save']) && !empty($_POST['ihc_admin_block_url_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_block_url_nonce']), 'ihc_admin_block_url_nonce' ) ){
				/// ========================= ADD NEW
				unset($_POST['ihc_save']);
				ihc_save_block_group('ihc_block_cats_by_name', indeed_sanitize_array($_POST), sanitize_text_field($_POST['cat_id']) );
			}
			?>
			<form method="post" >

				<input type="hidden" name="ihc_admin_block_url_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_block_url_nonce' );?>" />

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Block All Posts By Category Name', 'ihc');?></h3>
					<div class="inside">
						<div class="iump-form-line">
						  <h2><?php esc_html_e('Restrict all Posts from certain Category', 'ihc');?></h2>
						  <p><?php esc_html_e('Set a general restriction for all Posts in a particular Category. Any post type, including WordPress posts and products, can use this feature. Use the Entire URL section to limit the Category page if you want to.', 'ihc');?></p>
						</div>
						<div class="iump-form-line">
							<h4><?php esc_html_e('Category', 'ihc');?></h4>
							<p><?php esc_html_e('Choose one of the categories that is currently registered on your WordPress website', 'ihc');?></p>
							<select name="cat_id" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
							<?php
								$terms = ihc_get_all_terms_with_names();
								foreach ($terms as $key=>$label):
							?>
								<option value="<?php echo esc_attr($key);?>"><?php echo esc_html($label);?></option>
							<?php
								endforeach;
							?>
							</select>
						</div>

						<div class="iump-form-line">
							<div><?php esc_html_e('Excluding', 'ihc');?></div>
							<input type="text" name="except" value="" class="form-control ihc-form-element-text" />
							<p><i><?php esc_html_e('Submit post IDs separated by commas. For Example: 30, 55, 102');?></i></p>
						</div>

						<div class="iump-form-line iump-special-line">
							<div class="iump-form-line">
								<?php
									$type_values = array(
															'show' =>esc_html__('Show Only for...', 'ihc'),
															'block' =>esc_html__('Block Only for...', 'ihc')

									);

								?>
								<h4><?php esc_html_e('Restriction type', 'ihc');?></h4>
								<select name="block_or_show" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
									<?php foreach ($type_values as $k=>$v):?>
										<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
									<?php endforeach;?>
								</select>
							</div>
							<div class="iump-form-line">
								<h4><?php esc_html_e('Target Members', 'ihc');?></h4>
								<select id="ihc-change-target-user-set-regex" onChange="ihcWriteTagValue(this, '#target_users', '#ihc_tags_field2', 'ihc_select_tag_regex_' );ihcRemoveNoticeAfterWriteTag();" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select ihc-block-url-select">
									<option value="-1" selected>...</option>
									<?php
										foreach($posible_values as $k=>$v){
										?>
											<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
										<?php
										}
									?>
								</select>
								<input type="hidden" value="" name="target_users" id="target_users" />
								<div id="ihc_tags_field2"></div>
								<div id="iump_admin_car_target_message" class="iump-admin-car-target-message" data-notice="<?php esc_html_e( 'Please complete this field', 'ihc');?>"></div>
							</div>
						</div>

						<div class="iump-form-line"><h4><?php esc_html_e('Redirect After', 'ihc');?></h4>
						<p><?php esc_html_e('Choose the location to which members will be redirected if access is restricted. The Default Redirect Page will be utilized if no specific option is chosen
', 'ihc');?></p>
							<select name="redirect" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" selected >...</option>
								<?php
									$pages = $pages + ihc_get_redirect_links_as_arr_for_select();
									if ($pages){
										foreach($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
										<?php
										}
									}
								?>
							</select>
						</div>

						<div class="ihc-wrapp-submit-bttn">
							<input type="submit" value="<?php esc_html_e('Add New Access Rule', 'ihc');?>" name="ihc_save" class="button button-primary button-large ihc_submit_bttn">
						</div>
					</div>
				</div>

			</form>
			<?php
				$data = get_option('ihc_block_cats_by_name');
				if ($data && count($data)){


			$tableDataType = 'car_cats';
			$columns = [
						[
								'data'        	=> 'id',
								'orderable'   	=> false,
								'sortable'			=> false,
						],
						[
								'data'        	=> 'target_cats',
						],
						[
								'data'        	=> 'restriction_type',
								'orderable'   	=> false,
								'sortable'			=> false,
						],
						[
								'data'        	=> 'target_members',
								'orderable'   	=> false,
								'sortable'			=> false,
						],
						[
								'data'        	=> 'except',
								'orderable'   	=> false,
								'sortable'			=> false,
						],
						[
								'data'        	=> 'redirect',
								'orderable'   	=> false,
								'sortable'			=> false,
						],
						[
								'data'        	=> 'actions',
								'orderable'   	=> false,
								'sortable'			=> false,
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
					'emptyTable'		=> esc_html__( "Empty", 'ihc'),
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

				<div class="iump-rsp-table">
					<form action="" method="post" class="ihc-coupons-lists-wrapper" data-delete_many_coupons="<?php esc_html_e( 'Are You sure You wish to remove the selected coupons?', 'ihc' );?>" >
					<table id="iump-dashboard-table" class="display iump-dashboard-table ihc-display-none iump-js-coupons-table"  >
									<thead>
											<tr>
													<th class=""><input type="checkbox" class="iump-js-select-all-checkboxes" data-target="iump-dashboard-table"/></th>
													<th class="iump-dashboard-table-head-col iump-dashboard-table-coupon-code iump-dashboard-table-col-sorting"><?php esc_html_e('Target Category Name', 'ihc');?></th>
													<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Restriction Type', 'ihc');?></th>
													<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Target Members', 'ihc');?></th>
													<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Excluding', 'ihc');?></th>
													<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Redirect After', 'ihc');?></th>
													<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Remove', 'ihc');?></td>
											</tr>
									</thead>
							</table>
							<div class="iump-datatable-actions-wrapp-copy ihc-display-none">
									<select name="iump-action" class="iump-datatable-select-field iump-js-bulk-action-select">
											<option value="" disabled selected ><?php esc_html_e( 'Bulk Actions', 'ihc' );?></option>
											<option value="remove"><?php esc_html_e('Remove', 'ihc');?></option>
									</select>
									<input type="submit" name="iump-datatable-submit" value="<?php esc_html_e('Apply', 'ihc');?>" class="button button-primary button-small iump-js-car-apply-bttn" />
							</div>
					</form>
			</div>


		<?php }

		break;
	case 'files':
			/*
			// deprecated since version 12.0
			if (isset($_POST['delete_block']) && $_POST['delete_block']!=''){
				/// ======================== DELETE
				ihc_delete_block_group('ihc_block_files_by_url', sanitize_text_field($_POST['delete_block']));
			}
			*/
			if (!empty($_POST['ihc_save']) && !empty($_POST['ihc_admin_block_url_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_block_url_nonce']), 'ihc_admin_block_url_nonce' ) ){
				/// ========================= ADD NEW
				unset($_POST['ihc_save']);
				ihc_save_block_group('ihc_block_files_by_url', indeed_sanitize_array($_POST), sanitize_text_field($_POST['file_url']) );
				ihc_do_write_into_htaccess();
			}
			?>
			<form method="post" >

				<input type="hidden" name="ihc_admin_block_url_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_block_url_nonce' );?>" />

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Block Files By Link', 'ihc');?></h3>
					<div class="inside">
						<div class="iump-form-line">
						  <h2><?php esc_html_e('Restrict Physical Files stored on your WordPress', 'ihc');?></h2>
						  <p><?php esc_html_e('Restriction rule is applied only on additional media files stored inside  your WordPress with mp3|mp4|avi|pdf|zip|rar|doc|gz|tar|docx|xls|xlsx|PDF extension. ', 'ihc');?></p>
						</div>
						<div class="iump-form-line">
						  <div class="row">
						      <div class="col-xs-8">
						                 <div class="input-group">
						                    <span class="input-group-addon"><?php esc_html_e('Full File Link', 'ihc');?></span>
						                    <input class="ihc-block-url-file-url form-control" type="text"  value="" name="file_url" placeholder="<?php esc_html_e('copy the entire File Link from your browser', 'ihc');?>">
						                 </div>
						         </div>
						     </div>
						</div>

						<div class="iump-form-line iump-special-line">

							<div class="iump-form-line">
								<?php
									$type_values = array(
															'show' =>esc_html__('Show Only for...', 'ihc'),
															'block' =>esc_html__('Block Only for...', 'ihc')

									);

								?>
								<h4><?php esc_html_e('Restriction type', 'ihc');?></h4>
								<select name="block_or_show" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
									<?php foreach ($type_values as $k=>$v):?>
										<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
									<?php endforeach;?>
								</select>
							</div>

							<div class="iump-form-line">
								<h4><?php esc_html_e('Target Members', 'ihc');?></h4>
								<select id="ihc-change-target-user-set-regex" onChange="ihcWriteTagValue(this, '#target_users', '#ihc_tags_field2', 'ihc_select_tag_regex_' );ihcRemoveNoticeAfterWriteTag();" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select ihc-block-url-select">
									<option value="-1" selected>...</option>
									<?php
										foreach($posible_values as $k=>$v){
										?>
											<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
										<?php
										}
									?>
								</select>
								<input type="hidden" value="" name="target_users" id="target_users" />
								<div id="ihc_tags_field2"></div>
								<div id="iump_admin_car_target_message" class="iump-admin-car-target-message" data-notice="<?php esc_html_e( 'Please complete this field', 'ihc');?>"></div>
							</div>

						</div>

						<div class="iump-form-line">

							<h4><?php esc_html_e('Redirect After', 'ihc');?></h4>
							<p><?php esc_html_e('Choose the location to which members will be redirected if access is restricted. The Default Redirect Page will be utilized if no specific option is chosen', 'ihc');?></p>
							<select name="redirect" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
								<option value="-1" selected >...</option>
								<?php
									$pages = $pages + ihc_get_redirect_links_as_arr_for_select();
									if ($pages){
										foreach($pages as $k=>$v){
											?>
												<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
										<?php
										}
									}
								?>
							</select>
						</div>

						<div class="ihc-wrapp-submit-bttn">
							<input type="submit" value="<?php esc_html_e('Add New Access Rule', 'ihc');?>" name="ihc_save" class="button button-primary button-large ihc_submit_bttn">
						</div>
					</div>
				</div>

			</form>
			<?php
				$data = get_option('ihc_block_files_by_url');
				if ($data && count($data)){

						$tableDataType = 'car_files';
						$columns = [
									[
											'data'        	=> 'id',
											'orderable'   	=> false,
											'sortable'			=> false,
									],
									[
											'data'        	=> 'target_files',
									],
									[
											'data'        	=> 'restriction_type',
											'orderable'   	=> false,
											'sortable'		=> false,
									],
									[
											'data'        	=> 'target_members',
											'orderable'   	=> false,
											'sortable'		=> false,
									],
									[
											'data'        	=> 'redirect',
											'orderable'   	=> false,
											'sortable'		=> false,
									],
									[
											'data'        	=> 'actions',
											'orderable'   	=> false,
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
								'emptyTable'		=> esc_html__( "Empty", 'ihc'),
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
						wp_enqueue_script( 'ihcdatabse-colvis', IHC_URL . 'admin/assets/js/datatables/buttons.colVis.min.js', ['jquery'],'12.7' );
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

	<div class="iump-rsp-table">
		<form action="" method="post" class="ihc-coupons-lists-wrapper" data-delete_many_coupons="<?php esc_html_e( 'Are You sure You wish to remove the selected coupons?', 'ihc' );?>" >
		<table id="iump-dashboard-table" class="display iump-dashboard-table ihc-display-none iump-js-coupons-table"  >
						<thead>
								<tr>
										<th class=""><input type="checkbox" class="iump-js-select-all-checkboxes" data-target="iump-dashboard-table"/></th>
										<th class="iump-dashboard-table-head-col iump-dashboard-table-coupon-code iump-dashboard-table-col-sorting"><?php esc_html_e('Target File Link', 'ihc');?></th>
										<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Restriction Type', 'ihc');?></th>
										<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Target Members', 'ihc');?></th>
										<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Redirect After', 'ihc');?></th>
										<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Remove', 'ihc');?></td>
								</tr>
						</thead>
				</table>
				<div class="iump-datatable-actions-wrapp-copy ihc-display-none">
						<select name="iump-action" class="iump-datatable-select-field iump-js-bulk-action-select">
								<option value="" disabled selected ><?php esc_html_e( 'Bulk Actions', 'ihc' );?></option>
								<option value="remove"><?php esc_html_e('Remove', 'ihc');?></option>
						</select>
						<input type="submit" name="iump-datatable-submit" value="<?php esc_html_e('Apply', 'ihc');?>" class="button button-primary button-small iump-js-car-apply-bttn" />
				</div>
		</form>
</div>

			<?php }
		break;
endswitch;
?>
</div>
<?php

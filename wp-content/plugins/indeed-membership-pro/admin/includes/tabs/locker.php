<?php
ihc_delete_template();//DELETE
if (isset($_POST['ihc_bttn']) && !empty( $_POST['ihc_admin_locker_nonce'] ) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_locker_nonce']), 'ihc_admin_locker_nonce' )){
    Ihc_Db::save_update_locker_template( indeed_sanitize_textarea_array($_POST) );//SAVE, UPDATE
}
$subtab = isset( $_REQUEST['subtab'] ) ? sanitize_text_field($_REQUEST['subtab'])  : 'lockers_list';
?>
<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item <?php echo ( $subtab =='add_new') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url . '&tab=' . $tab . '&subtab=add_new');?>"><?php esc_html_e('Add New Locker Template', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ( $subtab =='lockers_list' ) ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=lockers_list');?>"><?php esc_html_e('Manage Lockers', 'ihc');?></a>
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
<?php

	$subtab = 'lockers_list';
	if (isset($_REQUEST['subtab'])){
		$subtab = sanitize_text_field($_REQUEST['subtab']);
	}
	if ($subtab=='add_new'){
    wp_enqueue_script( 'wp-theme-plugin-editor' );
    wp_enqueue_style( 'wp-codemirror' );
    wp_enqueue_script( 'code-editor' );
    wp_enqueue_style( 'code-editor' );


		if (isset($_REQUEST['ihc_edit_id']) && $_REQUEST['ihc_edit_id']){
			//edit
			$meta_arr = ihc_return_meta('ihc_lockers', sanitize_text_field($_REQUEST['ihc_edit_id']) );
		} else {
			//new
			$meta_arr = ihc_locker_meta_keys();
		}

		///////////////////// ADD NEW/edit SETION
		?>
			<form method="post" action="<?php echo esc_url($url.'&tab='.$tab.'&subtab=lockers_list');?>">
				<?php
					if(isset($_REQUEST['ihc_edit_id']) && $_REQUEST['ihc_edit_id']!=''){
						echo esc_ump_content('<input type="hidden" value="' . sanitize_text_field($_REQUEST['ihc_edit_id']) . '" name="template_id" />');//for update
					}
				?>

				<input type="hidden" name="ihc_admin_locker_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_locker_nonce' );?>" />

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Add New Locker Template', 'ihc');?></h3>
					<div class="inside">
						<div class="iump-form-line iump-no-border">
							<h2><?php esc_html_e('Locker Template Name', 'ihc');?></h2>
							<p><?php esc_html_e('Name of the Locker Template will be used for Administration purpose only when you will use a such template to restrict partial content inside WordPress Pages.', 'ihc');?></p>
							<div class="row">
                	<div class="col-xs-6">
                             <div class="input-group">
                                <span class="input-group-addon"><?php esc_html_e('Template Name', 'ihc');?></span>
                                <input class="form-control" type="text" value="<?php echo esc_attr($meta_arr['ihc_locker_name']);?>" name="ihc_locker_name" placeholder="<?php esc_html_e('suggestive Locker Template Name', 'ihc');?>">
                             </div>
                     </div>
                 </div>
						</div>

						<div class="iump-special-line">
							<h2><?php esc_html_e('Locker Predefined Theme', 'ihc');?></h2>
							<p><?php esc_html_e('Choose the best Theme for your website or particular page. You can customize it further with using Custom CSS Box.', 'ihc');?></p>
							<?php
								$templates = array(1=>'Default', 2=>'Basic', 3=>'Zipped', 4=>'Zone', 5=>'Majic Transparent', 6=>'Star', 7=>'Clouddy', 8=>'Darks');
							?>
							<select name="ihc_locker_template" id="ihc_locker_template" onChange="setAddVal(this, '#ihc_locker_login_template');ihcLockerPreview();" class="ihc_profile_form_template-st">
								<?php
									foreach($templates as $k=>$v){
										?>
											<option value="<?php echo esc_attr($k);?>" <?php if($k==$meta_arr['ihc_locker_template']){
												echo esc_attr('selected');
											}
											?> >
												<?php echo esc_html($v);?>
											</option>
										<?php
									}
								?>
							</select>
							<input type="hidden" id="ihc_locker_login_template" name="ihc_locker_login_template" value="<?php echo esc_attr($meta_arr['ihc_locker_login_template']);?>" />
						</div>

						<div class="iump-form-line iump-no-border">
							<h2><?php esc_html_e('Additional Display Options', 'ihc');?></h2>
							<p><?php esc_html_e('Choose what options will be available inside Locker box for who has no access to the content. Uncheck all if you wish just to hide the content from Page.', 'ihc');?></p>
						</div>
						<div class="iump-form-line iump-no-border">
							<input type="checkbox" class="iump-dashboard-checkbox" onClick="checkAndH(this, '#ihc_locker_login_form');ihcLockerPreview();" <?php if($meta_arr['ihc_locker_login_form']==1){
								echo esc_attr('checked');
							}?>
							/>
							<strong><?php esc_html_e('Login Form', 'ihc');?></strong>
							<input type="hidden" id="ihc_locker_login_form" name="ihc_locker_login_form" value="<?php echo esc_attr($meta_arr['ihc_locker_login_form']);?>" />
						</div>
						<div class="iump-form-line iump-no-border">
							<input type="checkbox" class="iump-dashboard-checkbox" onClick="checkAndH(this, '#ihc_locker_additional_links');ihcLockerPreview();" <?php if($meta_arr['ihc_locker_additional_links']==1){
								echo esc_attr('checked');
							}
							?>
							/><strong><?php esc_html_e('Additional Links', 'ihc');?></strong>
							<input type="hidden" id="ihc_locker_additional_links" name="ihc_locker_additional_links" value="<?php echo esc_attr($meta_arr['ihc_locker_additional_links']);?>" />
						</div>
						<div class="iump-form-line iump-no-border">
							<input type="checkbox" class="iump-dashboard-checkbox" onClick="checkAndH(this, '#ihc_locker_display_sm');ihcLockerPreview();" <?php if ($meta_arr['ihc_locker_display_sm']==1){
								 echo esc_attr('checked');
							}
							?>
							/><strong><?php esc_html_e('Display Social Media Login', 'ihc');?></strong>
							<input type="hidden" id="ihc_locker_display_sm" name="ihc_locker_display_sm" value="<?php echo (isset($meta_arr['ihc_locker_display_sm'])) ? $meta_arr['ihc_locker_display_sm']  : '';?>" />
						</div>

						<div class="iump-form-line iump-no-border">
							<h2><?php esc_html_e('Locker Messsage', 'ihc');?></h2>
							<p><?php esc_html_e('This Message will show up on the top of Locker Box. You can inform members why this content is restrict and what they should do to access it.', 'ihc');?></p>
							<?php
								$settings = array(
										'media_buttons' => true,
										'textarea_name' => 'ihc_locker_custom_content',
										'textarea_rows' => 5,
										'tinymce' => true,
										'quicktags' => true,
										'teeny' => true,
								);
								$meta_arr['ihc_locker_custom_content'] = ihc_correct_text($meta_arr['ihc_locker_custom_content']);
								wp_editor( $meta_arr['ihc_locker_custom_content'], 'ihc_locker_custom_content', $settings );
							?>

						</div>

						<div class="iump-form-line">
								<input type="button" onClick="ihcUpdateTextarea()" id="ihc-update-bttn-show-edit" value="<?php esc_html_e('Update Message in Locker Preview', 'ihc');?>" class="button button-primary button-large ihc-remove-group-button ihc-display-none"/>
						</div>
						<div class="ihc-wrapp-submit-bttn ihc-stuffbox-submit-wrap">
							<input id="ihc_submit_bttn_locker" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_bttn" class="button button-primary button-large" />
						</div>
					</div>
				</div>

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Locker Preview', 'ihc');?></h3>
					<div class="inside">
						<div id="locker-preview"></div>
					</div>
				</div>

				<div class="ihc-stuffbox iump-custom-css-box-wrapper">
					<h3><?php esc_html_e('Custom CSS', 'ihc');?></h3>
					<div class="inside">
            <div class="iump-form-line iump-no-border">
						        <textarea id="ihc_locker_custom_css" name="ihc_locker_custom_css" onBlur="ihcLockerPreview();" class="ihc-dashboard-textarea-full"><?php echo stripslashes($meta_arr['ihc_locker_custom_css']);?></textarea>
            </div>
            <div class="ihc-wrapp-submit-bttn">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_bttn" class="button button-primary button-large" />
						</div>
					</div>
				</div>

			</form>

		<?php
	}else{
		?>
		<div class="clear"></div>

		<!--div class="iump-page-title">Ultimate Membership Pro -
							<span class="second-text">
								<?php esc_html_e('Inside Lockers', 'ihc');?>
							</span>
						</div-->
    <div class="iump-page-headline">
        			<?php esc_html_e('Inside Lockers', 'ihc');?>
    </div>
    <div class="imup-page-top-options">
  		<a href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=add_new');?>" class="indeed-add-new-like-wp">
  			<i class="fa-ihc fa-add-ihc"></i><?php esc_html_e('Add New Locker Template', 'ihc');?>
  		</a>
  		<span class="ihc-top-message"><?php esc_html_e('...create Locker templates for further use!', 'ihc');?></span>
    </div>

		<div class="clear"></div>
		<?php
		////////////////// LIST LOCKER
		$templates = ihc_return_meta('ihc_lockers');
		if($templates){

    // bulk action
    if ( isset( $_POST['iump-datatable-submit'] ) && isset( $_POST['locker_id'] ) ){
        $target = indeed_sanitize_array( $_POST['locker_id'] );
        if ( count($target) && $_POST['iump-action'] === 'remove' ){
            $lokerData = get_option( 'ihc_lockers' );
            foreach ( $target as $lockerId ){
                // delete each locker
              	if ( $lokerData === false || !isset( $lokerData[$lockerId] ) ){
                    continue;
              	}
              	unset( $lokerData[$lockerId] );
            }
            update_option( 'ihc_lockers', $lokerData );
        }
    }

    $tableDataType = 'inside_locker';
    $columns = [
          [
              'data'        => 'select_item',
              'orderable'   => false,
              'sortable'		=> false,
          ],
          [
              'data'        => 'id',
          ],
          [
              'data'        => 'name',
          ],
          [
              'data'        => 'theme',
              'orderable'   => false,
              'sortable'		=> false,
          ],
          [
              'data'        => 'edit',
              'orderable'   => false,
              'sortable'		=> false,
          ],
          [
              'data'        => 'preview',
              'orderable'   => false,
              'sortable'		=> false,
          ],
          [
              'data'        => 'remove',
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
    <?php $pageState = get_option( 'ihc_datatable_state_for-locker_items', false );?>
    <?php if ( $pageState !== false ):?>
      <div class="iump-js-datatable-state" data-value='<?php echo stripslashes( $pageState );?>'></div>
    <?php endif;?>
    <!-- End of Page State -->

    <div class="iump-rsp-table">
      <form action="" method="post" class="ihc-lockers-lists-wrapper" data-delete_many_lockers="<?php esc_html_e( 'Are You sure You wish to remove the selected inside lockers?', 'ihc' );?>" >
      <table id="iump-dashboard-table" class="display iump-dashboard-table ihc-display-none iump-js-inside-locker-table"  >
              <thead>
                  <tr>
                      <th class="iump-checkbox-col-width"><input type="checkbox" class="iump-js-select-all-checkboxes" data-target="iump-dashboard-table"/></th>
                      <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Id', 'ihc');?></th>
                      <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Name', 'ihc');?></th>
                      <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Theme', 'ihc');?></th>
                      <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Edit', 'ihc');?></th>
                      <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Preview', 'ihc');?></th>
                      <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Remove', 'ihc');?></th>
                  </tr>
              </thead>
          </table>
          <div class="iump-datatable-actions-wrapp-copy ihc-display-none">
              <select name="iump-action" class="iump-datatable-select-field iump-js-bulk-action-select">
                  <option value="" disabled selected ><?php esc_html_e( 'Bulk Actions', 'ihc' );?></option>
                  <option value="remove"><?php esc_html_e('Remove', 'ihc');?></option>
              </select>
              <input type="submit" name="iump-datatable-submit" value="<?php esc_html_e('Apply', 'ihc');?>" class="button button-primary button-small iump-js-lockers-apply-bttn" />
          </div>
      </form>
  </div>


			<div id="locker-preview"></div>
		<?php
		}else{
			?>
				<div class="ihc-warning-message"> <?php esc_html_e('No Inside Lockers Templates available! Please create your first Inside Locker.', 'ihc');?></div>
			<?php
		}
	}
?>

</div>

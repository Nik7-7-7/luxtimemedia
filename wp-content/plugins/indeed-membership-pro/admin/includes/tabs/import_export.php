<?php
if (!empty($_POST['import']) && !empty($_FILES['import_file']) && !empty($_FILES['import_file']['tmp_name'])
&& !empty( $_POST['ihc_import_users_nonce'] ) && wp_verify_nonce( sanitize_text_field($_POST['ihc_import_users_nonce']), 'ihc_import_users_nonce' ) ){
	////////////////// IMPORT
	$filename = IHC_PATH . 'import.xml';
	move_uploaded_file( sanitize_text_field( $_FILES['import_file']['tmp_name'] ), $filename );
	require_once IHC_PATH . 'classes/import-export/IndeedImport.class.php';
	require_once IHC_PATH . 'classes/import-export/Ihc_Indeed_Import.class.php';
	$import = new Ihc_Indeed_Import();
	$import->setFile($filename);
	$import->run();
} else if ( !empty( $_POST['import_members_via_csv'] ) && wp_verify_nonce( sanitize_text_field($_POST['ihc_import_members_nonce']), 'ihc_import_members_nonce' )
&& !empty( $_FILES['import_file']['tmp_name'] ) ){
		// import members via csv
		$filename = IHC_PATH . 'temporary/import.csv';
		move_uploaded_file( $_FILES['import_file']['tmp_name'], $filename);
		require_once IHC_PATH . 'classes/import-export/IumpImportCsv.php';
		$ImportCSV = new \IumpImportCsv();
		$responseFromImportCSV = $ImportCSV->setFile( $filename )
													->setType( 'members' )
													->proceed();
}
wp_enqueue_style( 'ihcmultiselect', IHC_URL . 'admin/assets/css/jquery.multiselect.css');
wp_enqueue_script( 'ihcmultiselectfunctions', IHC_URL . 'admin/assets/js/jquery.multiselect.js', ['jquery'], '12.5.3' );
wp_enqueue_script( 'ump-admin-import-export', IHC_URL . 'admin/assets/js/csv_export.js', [ 'jquery' ], '12.5.3' );
?>
<?php
if ( isset( $responseFromImportCSV ) && count( $responseFromImportCSV ) > 0 && isset( $responseFromImportCSV['status'] ) && isset( $responseFromImportCSV['message'] ) ):?>
		<?php
			switch ( $responseFromImportCSV['status'] ){
					case 0:
						$messageClass = 'ihc-danger-box';
						break;
					case 1:
						$messageClass = 'ihc-success-box';
						break;
					case -1:
						$messageClass = 'ihc-warning-box';
						break;
			}
		?>
		<div class="<?php echo $messageClass;?>"><?php echo $responseFromImportCSV['message'];?></div>
<?php endif;?>

<div class="ump-export-import-section-wrap">
	<form  method="post">
		<div class="ihc-stuffbox">
			<h3><?php esc_html_e('Export CSV Files', 'ihc');?></h3>
			<div class="inside">
				<div class="iump-form-line">
					<h2><?php esc_html_e('Export Members', 'ihc');?></h2>
					<p><?php esc_html_e('Export your members list to a CSV file to access detailed information about your members in a convenient format.', 'ihc');?></p>
				</div>
				<div class="iump-form-line ump-export-csv-filters-wrap">

					<div class="iump-datatable-filters-wrapper"  id="ump_js_import_export_wrapp_for_members">

							<input type="text" value="" placeholder="<?php esc_html_e('Search Members', 'ihc');?>" class="ump-js-search-phrase">

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
									<select name="memberships[]" class="iump-export-filter-users-memberships ms-list-1" multiple data-placeholder="<?php esc_html_e("Choose Memberships", 'ihc');?>" >
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
									<select name="membership_status[]" class="iump-export-filter-users-membership-status" multiple data-placeholder="<?php esc_html_e("Memberships status", 'ihc');?>" >
												<?php foreach ( $statusArray as $key => $label ): ?>
														<option value="<?php echo $key;?>" ><?php echo $label;?></option>
												<?php endforeach;?>
									</select>
							</div>


							<div class="button button-primary button-small  iump-filters-bttn js-ump-export-csv" data-filters="" data-export_type="members"><?php esc_html_e( 'Export CSV', 'ihc' );?>

					</div>

				</div>

				</div>
			</div>
		</div>
	</form>

<div class="ihc-stuffbox">
	<h3><?php esc_html_e('Export Settings', 'ihc');?></h3>
	<div class="inside">
			<span class="iump-labels-special"></span>
			<!---div class="iump-form-line">
				<h4><?php esc_html_e('Export Members', 'ihc');?></h4>
				<p><?php esc_html_e('Export the Members for this site as a .xml file. This allows you to easily import them into another site.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#import_users');" />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="import_users" value=0 id="import_users"/>
			</div-->
			<div class="iump-form-line">
				<h4><?php esc_html_e('Export Settings', 'ihc');?></h4>
				<p><?php esc_html_e('Export the Ultimate Membership Pro settings for this site as a .xml file. This allows you to easily import the configuration into another site.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#import_settings');" />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="import_settings" value=0 id="import_settings"/>
			</div>
			<div class="iump-form-line">
				<h4><?php esc_html_e('Posts Settings', 'ihc');?></h4>
				<p><?php esc_html_e('Export the Posts restricitons settings for this site as a .xml file. This allows you to easily import them into another site.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#import_postmeta');" />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="import_postmeta" value=0 id="import_postmeta"/>
			</div>
		<div class="iump-form-line iump-no-border">
			<div class="ump-hidden-download-results">
			<div class="ihc-hidden-download-link ihc-display-none" >
				<h4><?php esc_html_e('Export File', 'ihc');?></h4>
				<?php esc_html_e('Your Export file is ready to download through this link:', 'ihc');?>
				<a href="" target="_blank" download>export.xml</a>
			</div>
			<div class="ihc-display-inline" id="ihc_loading_gif" ><span class="spinner"></span></div>
		</div>
		</div>
			<div class="ihc-wrapp-submit-bttn">
					<input type="submit"  class="button button-primary button-large"  onClick="ihcMakeExportFile();" value="<?php esc_html_e('Generate XML File', 'ihc');?>" />
		</div>
	</div>
</div>

<form method="post" enctype="multipart/form-data">
	<input type="hidden" name="ihc_import_users_nonce" class="button button-primary button-large" value="<?php echo wp_create_nonce( 'ihc_import_users_nonce' );?>" />
	<div class="ihc-stuffbox">
		<h3><?php esc_html_e('Import Settings', 'ihc');?></h3>
		<div class="inside">
			<div class="iump-form-line">
				<h4><?php esc_html_e('Import XML File', 'ihc');?></h4>
				<p><?php esc_html_e('Import the .xml file from another WordPress website that was created using the Ultimate Membership Pro plugin.', 'ihc');?></p>
			</div>
			<div class="iump-form-line">
				<input type="file" name="import_file" />
			</div>

			<div class="ihc-wrapp-submit-bttn">
				<input type="submit" value="<?php esc_html_e('Import XML File', 'ihc');?>" name="import" class="button button-primary button-large">
			</div>
		</div>
	</div>
</form>

<form method="post" enctype="multipart/form-data">
	<input type="hidden" name="ihc_import_members_nonce" class="button button-primary button-large" value="<?php echo wp_create_nonce( 'ihc_import_members_nonce' );?>" />
	<div class="ihc-stuffbox">
		<h3><?php esc_html_e('Import Members', 'ihc');?></h3>
		<div class="inside">
			<div class="iump-form-line">
				<h4><?php esc_html_e('Import CSV File', 'ihc');?></h4>
				<p><?php esc_html_e('Import the .csv file from another WordPress website that was created using the Ultimate Membership Pro plugin.', 'ihc');?></p>
			</div>
			<div class="iump-form-line">
				<input type="file" name="import_file" />
			</div>

			<div class="ihc-wrapp-submit-bttn">
				<input type="submit" value="<?php esc_html_e('Import CSV File', 'ihc');?>" name="import_members_via_csv" class="button button-primary button-large">
			</div>
		</div>
	</div>
</form>
</div>
<?php

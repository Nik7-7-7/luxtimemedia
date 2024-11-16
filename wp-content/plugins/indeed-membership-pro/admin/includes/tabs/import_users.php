<?php
if (!empty($_POST['import']) && !empty($_FILES['import_file']) && !empty($_FILES['import_file']['tmp_name'])
&& isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
	////////////////// IMPORT
	$filename = IHC_PATH . 'import-users.csv';


	move_uploaded_file( sanitize_text_field( $_FILES['import_file']['tmp_name'] ), $filename);
	require_once IHC_PATH . 'classes/IhcUsersImport.class.php';
	$object = new IhcUsersImport();
	$object->setFile($filename);
	$object->setDoRewrite( sanitize_text_field($_POST['rewrite']) );
	$object->run();
	$updatedUsers = $object->getUpdatedUsers();
	$totalUsers = $object->getTotalUsers();
}

?>
<form method="post" enctype="multipart/form-data">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div>
	<?php if ( !empty( $totalUsers ) ):?>
		<div class="ihc-succes-message"><?php echo esc_html__( 'Total users affected: ', 'ihc') . $totalUsers;?></div>
	<?php endif;?>

	<?php if ( !empty( $updatedUsers ) ):?>
		<div class="ihc-succes-message"><?php echo esc_html__( 'Updated users: ', 'ihc') . $updatedUsers;?></div>
	<?php endif;?>
</div>

	<div class="ihc-clear"></div>

	<div class="ihc-stuffbox">

		<h3 class="ihc-h3"><?php esc_html_e('Import Users & Memberships', 'ihc');?></h3>
		<div class="inside">
			<div class="iump-form-line">
            	<p><strong><?php esc_html_e('Allows to import new Members, update current Members main data or to assign/change Members Memberships and update their start and expire time. Only main Members data and memberships are handled via specific CSV file format.', 'ihc');?></strong></p>
            </div>
            <div class="iump-form-line">
				<h2><?php esc_html_e('Rewrite Membership start time & expire time', 'ihc');?></h2>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#do_rewrite');" />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="rewrite" value="0" id="do_rewrite" />
			</div>
            <div class="iump-form-line">
           		<h2><?php esc_html_e('CSV Sample File', 'ihc');?></h2>
                <p><?php esc_html_e('Download and use this sample by keeping the columns and following the examples inside it.', 'ihc');?></p>
            	<a class="button button-primary button-large" href="<?php echo IHC_URL . 'admin/assets/others/sample.csv';?>" target="_blank"><?php esc_html_e('Download CSV Sample', 'ihc');?></a>
            </div>
			<div class="iump-form-line">
            	<h2><?php esc_html_e('Import procedure', 'ihc');?></h2>
                <p><?php esc_html_e('If any data inside the file will be found in the database the content will not be overwritten, except for Membership Time if the above option is enabled. For users with multiple memberships, just add an additional row for each membership using the same user_id.', 'ihc');?></p>
								<p><b><?php esc_html_e( 'User Email is required!', 'ihc');?></b></p>
								<p><b><?php esc_html_e( 'Some Apps may alter the CSV data format. Be sure that the started_date and expired_date have the right format: YYYY-MM-DD HH:MM:SS, ex: 2021-07-08 10:24:43', 'ihc');?></b></p>
				<span class="iump-labels-special"><?php esc_html_e('File ready for import', 'ihc');?></span>
				<input type="file" name="import_file" />
            </div>
			 <div class="ihc-wrapp-submit-bttn">
				<input type="submit" value="<?php esc_html_e('Import', 'ihc');?>" name="import" class="button button-primary button-large">
			</div>
		</div>
	</div>
</form>

<?php

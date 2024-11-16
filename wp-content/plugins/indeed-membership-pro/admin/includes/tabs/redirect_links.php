<?php
if ( !empty($_POST['url']) && isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
	ihc_add_new_redirect_link( indeed_sanitize_array($_POST) );
} else if (isset($_POST['delete_redirect_link']) && isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
	ihc_delete_redirect_link( sanitize_text_field($_POST['delete_redirect_link']) );
}
?>
<div>
<form method="post"  id="redirect_links_form">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<input type="hidden" value="" name="delete_redirect_link" id="delete_redirect_link" />
	<div class="ihc-stuffbox">
		<h3><?php esc_html_e('Redirect Links', 'ihc');?></h3>
		<div class="inside">
		<h2><?php esc_html_e('Redirect Links', 'ihc');?></h2>
		<p><?php esc_html_e('Add custom links from inside or outside of your website that can be used for redirects inside the membership system.', 'ihc');?></p>
		<p><?php esc_html_e('If user is not allowed to see the content of a page or post, he may be redirected to a "Custom Link" created on this page.','ihc');?></p>

		<div class="iump-form-line">
			<div class="row">
				<div class="col-xs-5">
					<div class="input-group">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Name:', 'ihc');?></span>
						<input type="text" class="form-control" name="name"value="" />
					</div>
				</div>
				</div>
			</div>
			<div class="iump-form-line">
			<div class="row">
				<div class="col-xs-5">
					<div class="input-group">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Custom Link:', 'ihc');?></span>
						<input type="text" class="form-control" name="url"value="" />
					</div>
				</div>
				</div>
			</div>
			<p><?php esc_html_e('Whenever a page or post is created, administrator may have the option to redirect the user to a "Custom Link" in Ultimate Membership Pro Locker box on the right-side of the window.','ihc');?></p>
			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Add New', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>
</form>
<?php
$data = get_option('ihc_custom_redirect_links_array');
if ($data && count($data)){
	?>
	<div class="ihc-dashboard-form-wrap">
	<div class="iump-rsp-table ihc-admin-user-data-list">
		<table class="wp-list-table widefat fixed tags" id="ihc-levels-table">
			<thead>
				<tr>
					<th class="manage-column"><?php esc_html_e('Name', 'ihc');?></th>
					<th class="manage-column"><?php esc_html_e('Link', 'ihc');?></th>
					<th class="manage-column" width="50px"><?php esc_html_e('Delete', 'ihc');?></th>
				</tr>
			</thead>
			<?php
				$i = 1;
				foreach ($data as $key=>$url){
				?>
				<tr class="<?php if ($i%2==0){
					 echo esc_attr('alternate');
				}
				?>
				">
					<td><?php echo esc_html($key);?></td>
					<td><a href="<?php echo esc_url($url);?>" target="_blank"><?php echo esc_url($url);?></a></td>
					<td align="center">
						<i class="fa-ihc ihc-icon-remove-e ihc-js-admin-redirect-links-do-delete" data-key="<?php echo esc_attr($key);?>" ></i>
					</td>
				</tr>
				<?php
				$i++;
				}
				?>
		</table>
	</div>
</div>
<?php
}
?>
</div>
<?php

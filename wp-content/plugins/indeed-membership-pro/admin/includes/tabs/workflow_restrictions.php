<?php
$levels = array();
$levels = \Indeed\Ihc\Db\Memberships::getAll();

if(!empty($levels) && is_array($levels)){
	$levels = array('reg' => array('label' => esc_html__('Users with no active Membership', 'ihc'))) + $levels;
}else{
	$levels = array('reg' => array('label' => esc_html__('Users with no active Membership', 'ihc')));
}

if (!empty($_POST['ihc_save']) && isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
	update_option('ihc_workflow_restrictions_timelimit', sanitize_text_field($_POST['ihc_workflow_restrictions_timelimit']) );
	update_option('ihc_workflow_restrictions_on', sanitize_text_field($_POST['ihc_workflow_restrictions_on']) );
	if (isset($_POST['ihc_workflow_restrictions_post_views'])){
		$ihc_workflow_restrictions_post_views = array();
		$ihc_workflow_restrictions_post_views['unreg'] = (isset($_POST['ihc_workflow_restrictions_post_views']['unreg'])) ? sanitize_text_field($_POST['ihc_workflow_restrictions_post_views']['unreg']) : '';

		foreach ($levels as $id=>$leveldata){
			$ihc_workflow_restrictions_post_views[$id] = (isset($_POST['ihc_workflow_restrictions_post_views'][$id])) ? sanitize_text_field($_POST['ihc_workflow_restrictions_post_views'][$id]) : '';
		}
		update_option('ihc_workflow_restrictions_post_views', $ihc_workflow_restrictions_post_views);
	}
	if (isset($_POST['ihc_workflow_restrictions_posts_created'])){
		$ihc_workflow_restrictions_posts_created = array();
		foreach ($levels as $id=>$leveldata){
			$ihc_workflow_restrictions_posts_created[$id] = (isset($_POST['ihc_workflow_restrictions_posts_created'][$id])) ? sanitize_text_field($_POST['ihc_workflow_restrictions_posts_created'][$id]) : '';
		}
		update_option('ihc_workflow_restrictions_posts_created', $ihc_workflow_restrictions_posts_created);
	}
	if (isset($_POST['ihc_workflow_restrictions_comments_created'])){
		$ihc_workflow_restrictions_comments_created = array();
		foreach ($levels as $id=>$leveldata){
			$ihc_workflow_restrictions_comments_created[$id] = (isset($_POST['ihc_workflow_restrictions_comments_created'][$id])) ? sanitize_text_field($_POST['ihc_workflow_restrictions_comments_created'][$id]) : '';
		}
		update_option('ihc_workflow_restrictions_comments_created', $ihc_workflow_restrictions_comments_created);
	}
}
$data['metas'] = ihc_return_meta_arr('workflow_restrictions');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

?>
<form  method="post">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('WP Workflow Restrictions', 'ihc');?></h3>
		<div class="inside">
			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold this WP WorkFlow Restrictions', 'ihc');?></h2>
				<p><?php esc_html_e('You can restrict how many posts can be viewed, released and how many comments can be submitted for each membership / subscription.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_workflow_restrictions_on']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_workflow_restrictions_on');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_workflow_restrictions_on" value="<?php echo esc_attr($data['metas']['ihc_workflow_restrictions_on']);?>" id="ihc_workflow_restrictions_on" />
			<p><?php esc_html_e('If a user has multiple memberships assigned, it will be take in consideration the membership with the highest number of views / submissions.', 'ihc');?></p>
			</div>

			<div class="iump-form-line">
				<h4><?php esc_html_e('Time Limit', 'ihc');?></h4>
					<p><?php esc_html_e('When the timer expires, the internal count for each user is reset.', 'ihc');?></p>
				<div class="row">
					<div class="col-xs-5">
						<div class="input-group">
							<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Days', 'ihc');?></span>
							<input type="number" min="0" class="form-control" value="<?php echo esc_attr($data['metas']['ihc_workflow_restrictions_timelimit']);?>" name="ihc_workflow_restrictions_timelimit" />
						</div>
					</div>
				</div>
			</div>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>

	<?php if ($levels):?>
		<div class="ihc-stuffbox">
			<h3 class="ihc-h3"><?php esc_html_e('Restrict Posts Views', 'ihc');?></h3>
			<div class="inside">
				<h4><?php esc_html_e('Memberships Limits', 'ihc');?></h4>
				<p><?php esc_html_e('Set for each membership how many posts can be viewed by a user with that membership. Leave blank for unlimited views.', 'ihc');?></p>
				<div class="iump-form-line">
						<div class="row">
							<div class="col-xs-5">
								<?php $value = (isset($data['metas']['ihc_workflow_restrictions_post_views']['unreg'])) ? $data['metas']['ihc_workflow_restrictions_post_views']['unreg'] : '';?>
								<div class="input-group">
									<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Unregistered Users', 'ihc');?></span>
									<input type="number" min="1" class="form-control" value="<?php echo esc_attr($value);?>" name="ihc_workflow_restrictions_post_views[unreg]" />
								</div>
							</div>
						</div>
					<?php foreach ($levels as $id=>$level):?>
						<?php $value = (isset($data['metas']['ihc_workflow_restrictions_post_views'][$id])) ? $data['metas']['ihc_workflow_restrictions_post_views'][$id] : '';?>
						<div class="row">
							<div class="col-xs-5">
								<div class="input-group">
									<span class="input-group-addon" id="basic-addon1"><?php echo esc_html($level['label']);?></span>

									<input type="number" min="0" class="form-control" value="<?php echo esc_attr($value);?>" name="ihc_workflow_restrictions_post_views[<?php echo esc_attr($id);?>]" />
								</div>
							</div>
						</div>
				  <?php endforeach;?>
				</div>
				<div class="ihc-wrapp-submit-bttn ihc-submit-form">
					<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
				</div>
			</div>
		</div>
		<div class="ihc-stuffbox">
			<h3 class="ihc-h3"><?php esc_html_e('Restrict Posts Created', 'ihc');?></h3>
			<div class="inside">
				<h4><?php esc_html_e('Memberships Limits', 'ihc');?></h4>
				<p><?php esc_html_e('Set for each membership how many WP posts can be submitted by a user with that membership. Leave blank for unlimited submissions.', 'ihc');?></p>
				<p><strong><?php esc_html_e('The Submitted Posts that are not allowed to become Public because of this restriction will be set with a Pending Review status', 'ihc');?></strong></p>

				<div class="iump-form-line">
					<?php foreach ($levels as $id=>$level):?>
						<?php $value = (isset($data['metas']['ihc_workflow_restrictions_posts_created'][$id])) ? $data['metas']['ihc_workflow_restrictions_posts_created'][$id] : '';?>
						<div class="row">
						<div class="col-xs-5">
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon1"><?php echo esc_html($level['label']);?></span>

								<input type="number" min="0" class="form-control" value="<?php echo esc_attr($value);?>" name="ihc_workflow_restrictions_posts_created[<?php echo esc_attr($id);?>]" />
							</div>
						</div>
						</div>
					<?php endforeach;?>
				</div>
				<div class="ihc-wrapp-submit-bttn ihc-submit-form">
					<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
				</div>
			</div>
		</div>
		<div class="ihc-stuffbox">
			<h3 class="ihc-h3"><?php esc_html_e('Restrict Comments Created', 'ihc');?></h3>
			<div class="inside">
				<h4><?php esc_html_e('Memberships Limits', 'ihc');?></h4>
				<p><?php esc_html_e('Set for each membership how many WP comments can be submitted by a user with that membership. Leave blank for unlimited comments.', 'ihc');?></p>

				<div class="iump-form-line">
					<?php foreach ($levels as $id=>$level):?>
						<?php $value = (isset($data['metas']['ihc_workflow_restrictions_comments_created'][$id])) ? $data['metas']['ihc_workflow_restrictions_comments_created'][$id] : '';?>
						<div class="row">
						<div class="col-xs-5">
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon1"><?php echo esc_html($level['label']);?></span>

								<input type="number" min="0" class="form-control" value="<?php echo esc_attr($value);?>" name="ihc_workflow_restrictions_comments_created[<?php echo esc_attr($id);?>]" />
							</div>
						</div>
						</div>
					<?php endforeach;?>
				</div>
				<div class="ihc-wrapp-submit-bttn ihc-submit-form">
					<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
				</div>
			</div>
		</div>
	<?php endif;?>

</form>

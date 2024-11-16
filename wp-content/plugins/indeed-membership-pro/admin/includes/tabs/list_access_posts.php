<?php
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
		ihc_save_update_metas('list_access_posts');//save update metas
}

$data['metas'] = ihc_return_meta_arr('list_access_posts');
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

$post_types = ihc_get_all_post_types();
$levels = \Indeed\Ihc\Db\Memberships::getAll();

wp_enqueue_script( 'wp-theme-plugin-editor' );
wp_enqueue_style( 'wp-codemirror' );
wp_enqueue_script( 'code-editor' );
wp_enqueue_style( 'code-editor' );
?>
<form method="post">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('List Access Posts', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold List Access Posts', 'ihc');?></h2>
				<p><?php esc_html_e('Display all the posts that a user can see based on his subscriptions.', 'ihc'); ?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = empty($data['metas']['ihc_list_access_posts_on']) ? '' : 'checked';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_list_access_posts_on');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_list_access_posts_on" value="<?php echo esc_attr($data['metas']['ihc_list_access_posts_on']);?>" id="ihc_list_access_posts_on" />
			</div>
<?php if(!empty($levels)){ ?>
			<div class="iump-form-line">
				<h4><?php esc_html_e('Specific Memberships', 'ihc');?></h4>
				<p><?php esc_html_e('The user needs to have the membership(s) assigned and active to see the available posts in his list.', 'ihc');?></p>
				<p><?php esc_html_e('For posts with "Show page Only for Registered Users" restriction, this extension will block the content even the membership is activated.', 'ihc');?></p>
				<?php $excluded = explode(',', $data['metas']['ihc_list_access_posts_order_exclude_levels']);?>
				<?php foreach ($levels as $lid=>$larr):?>
					<div class="ihc-list-access-posts-memberships">
						<?php $checked = (!in_array($lid, $excluded)) ? 'checked' : '';?>
						<input type="checkbox" <?php echo esc_attr($checked);?> onClick="ihcAddToHiddenWhenUncheck(this, '<?php echo esc_attr($lid);?>', '#ihc_list_access_posts_order_exclude_levels');" /> <span> <?php echo esc_attr($larr['label']);?></span>
					</div>
				<?php endforeach;?>
				<input type="hidden" name="ihc_list_access_posts_order_exclude_levels" id="ihc_list_access_posts_order_exclude_levels" value="<?php echo esc_attr($data['metas']['ihc_list_access_posts_order_exclude_levels']);?>" />
			</div>
<?php } ?>
			<div class="iump-form-line">
				<h4><?php esc_html_e('Post Type', 'ihc');?></h4>
				<p><?php esc_html_e('Select which post types show up, you can select specific ones or all of them.', 'ihc');?></p>
				<?php $post_type_in = explode(',', $data['metas']['ihc_list_access_posts_order_post_type']);?>
				<?php foreach ($post_types as $value):?>
					<div class="ihc-list-access-posts-memberships">
						<?php $checked = (in_array($value, $post_type_in)) ? 'checked' : '';?>
						<input type="checkbox" <?php echo esc_attr($checked);?> onClick="ihcMakeInputhString(this, '<?php echo esc_attr($value);?>', '#ihc_list_access_posts_order_post_type');" /> <span><?php echo ucfirst($value);?></span>
					</div>
				<?php endforeach;?>
				<input type="hidden" name="ihc_list_access_posts_order_post_type" id="ihc_list_access_posts_order_post_type" value="<?php echo esc_attr($data['metas']['ihc_list_access_posts_order_post_type']);?>" />
			</div>
			<div class="iump-form-line">
			</div>
			<div class="iump-register-select-template">
				<div class="row ihc-row-no-margin">
				 <div class="col-xs-5 ihc-col-no-padding">
				<div class="iump-form-line">
				<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Template', 'ihc');?></span>
				<select name="ihc_list_access_posts_template" class="form-control"><?php
					foreach (array('iump-list-posts-template-1'=> esc_html__('Template 1', 'ihc'), 'iump-list-posts-template-2'=> esc_html__('Template 2', 'ihc')) as $k=>$v){
						$selected = ($data['metas']['ihc_list_access_posts_template']==$k) ? 'selected' : '';
						?>
						<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
						<?php
					}
				?></select>
			</div>
			</div>
		</div>
	</div>
	<div class="row ihc-row-no-margin">
	 <div class="col-xs-5 ihc-col-no-padding">
			<div class="iump-form-line">
				<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Showcase Title', 'ihc');?></span>
				<input type="text" name="ihc_list_access_posts_title" value="<?php echo esc_attr($data['metas']['ihc_list_access_posts_title']);?>"  class="form-control"/></div>
			</div>
		</div>
	</div>

			<div class="iump-form-line">
				<h4><?php esc_html_e('Item Details', 'ihc');?></h4>
				<?php $item_details = explode(',', $data['metas']['ihc_list_access_posts_item_details']);?>
				<?php
					$details_arr = array(
											'post_title' => esc_html__('Title', 'ihc'),
											'post_excerpt' => esc_html__('Excerpt', 'ihc'),
											'feature_image' => esc_html__('Feature Image', 'ihc'),
											'post_date' => esc_html__('Post Date', 'ihc'),
											'post_author' => esc_html__('Post Author', 'ihc'),
					);
				?>
				<div class="iump-form-line">
				<?php foreach ($details_arr as $value => $label):?>
					<div class="ihc-list-access-posts-memberships">
						<?php $checked = (in_array($value, $item_details)) ? 'checked' : '';?>
						<input type="checkbox" <?php echo esc_attr($checked);?> onClick="ihcMakeInputhString(this, '<?php echo esc_attr($value);?>', '#ihc_list_access_posts_item_details');" /> <span>  <?php echo esc_html('  ').$label;?></span>
					</div>
				<?php endforeach;?>
			</div>
				<input type="hidden" name="ihc_list_access_posts_item_details" id="ihc_list_access_posts_item_details" value="<?php echo esc_attr($data['metas']['ihc_list_access_posts_item_details']);?>" />
			</div>
			</div>

			<div class="row ihc-row-no-margin">
			 <div class="col-xs-5 ihc-col-no-padding">

			<div class="iump-form-line">
				<div class="input-group"><span class="input-group-addon"><?php esc_html_e('No. of Posts per page', 'ihc');?></span>
				<input type="number" name="ihc_list_access_posts_per_page_value" value="<?php echo esc_attr($data['metas']['ihc_list_access_posts_per_page_value']);?>" min="1" class="form-control"/>
			</div>
			</div>

			<div class="iump-form-line">
				<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Max. No. of Posts', 'ihc');?></span>
				<input type="number" name="ihc_list_access_posts_order_limit" value="<?php echo esc_attr($data['metas']['ihc_list_access_posts_order_limit']);?>" min="1" class="form-control"/>
			</div>
		</div>

			<div class="iump-form-line">
				<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Posts Order by', 'ihc');?></span>
				<select name="ihc_list_access_posts_order_by" class="form-control"><?php
					foreach (array('post_title'=> esc_html__('Post Title', 'ihc'), 'post_date'=> esc_html__('Post Date', 'ihc')) as $k=>$v){
						$selected = ($data['metas']['ihc_list_access_posts_order_by']==$k) ? 'selected' : '';
						?>
						<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
						<?php
					}
				?></select>
			</div>
			</div>

			<div class="iump-form-line">
				<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Posts Order Type', 'ihc');?></span>
				<select name="ihc_list_access_posts_order_type"><?php
					foreach (array('asc'=> esc_html__('ASC', 'ihc'), 'desc'=> esc_html__('DESC', 'ihc')) as $k=>$v){
						$selected = ($data['metas']['ihc_list_access_posts_order_type']==$k) ? 'selected' : '';
						?>
						<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
						<?php
					}
				?></select>
			</div>
			</div>
		</div>
		</div>
			<div class="iump-form-line iump-custom-css-box-wrapper">
				<h2><?php esc_html_e('Custom CSS', 'ihc');?></h2>
				<textarea name="ihc_list_access_posts_custom_css" id="ihc_list_access_posts_custom_css" class="ihc-custom-css-box"><?php echo stripslashes($data['metas']['ihc_list_access_posts_custom_css']);?></textarea>
			</div>

			<h2><?php esc_html_e('Shortcode: ', 'ihc');?></h2>
			<div class="ihc-user-list-shortcode-wrapp">
				<div class="content-shortcode">
					<span class="the-shortcode">[ihc-list-all-access-posts]</span>
				</div>
			</div>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>
</form>

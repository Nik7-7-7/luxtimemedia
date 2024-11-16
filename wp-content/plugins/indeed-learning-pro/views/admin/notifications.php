
<div class="ulp-wrapper">
		<div class="ulp-page-title"><?php esc_html_e('Manage Email Notifications', 'ulp');?></div>
		<a href="<?php echo esc_url($data['url-add_edit']);?>" class="ulp-add-new-like-wp"><i class="fa-ulp fa-add-ulp"></i><?php esc_html_e('Activate New Notification', 'ulp');?></a>
		<span class="ulp-top-message"><?php esc_html_e('...create your notification Templates!', 'ulp');?></span>
		<a href="javascript:void(0)" class="button button-primary button-large ulp-admin-notification-add-new" onClick="ulpCheckEmailServer();"><?php esc_html_e('Check Mail Server', 'ulp');?></a>
		<div class="ulp-clear"></div>
		<?php if (isset($data['success']) && $data['success']==FALSE):?>
			<div ><?php esc_html_e('Something is not working properly. Please try again.', 'ulp');?></div>
		<?php endif;?>
	<div class="ulp-admin-notification-list-wrapper">
		<?php if (!empty($data['items'])) : ?>
		<form  method="post" id="form_notification">
			<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />

			<table class="wp-list-table widefat fixed tags ulp-admin-tables">
				<thead>
					<tr>
						    <th><?php esc_html_e('Subject', 'ulp');?></th>
							<th><?php esc_html_e('Action', 'ulp');?></th>
							<th><?php esc_html_e('Goes to', 'ulp');?></th>
                            <th><?php esc_html_e('Course', 'ulp');?></th>
                            <?php if ($data['pushover']):?>
									<th><?php esc_html_e('Mobile Notification', 'ulp');?></th>
							<?php endif;?>
							<th><?php esc_html_e('Options', 'ulp');?></th>
					</tr>
				</thead>
				<tfoot>
						<tr>
                        <th><?php esc_html_e('Subject', 'ulp');?></th>
                        <th><?php esc_html_e('Action', 'ulp');?></th>
                        <th><?php esc_html_e('Goes to', 'ulp');?></th>
                        <th><?php esc_html_e('Course', 'ulp');?></th>
                        <?php if ($data['pushover']):?>
                        <th><?php esc_html_e('Mobile Notification', 'ulp');?></th>
						<?php endif;?>
												<th><?php esc_html_e('Options', 'ulp');?></th>
                        </tr>
                 </tfoot>
                        <tbody class="ui-sortable ulp-alternate">
						<?php $admin_notifications = array();?>
							<?php foreach ($data['items'] as $array) : ?>
                            <tr onmouseover="ulpDhSelector('#notification_<?php echo esc_attr($array['id']);?>', 1);" onmouseout="ulpDhSelector('#notification_<?php echo esc_attr($array['id']);?>', 0);">
                            <td><?php echo esc_html($array['subject']);?>
                            <div id="notification_<?php echo esc_attr($array['id']);?>" class="ulp-visibility-hidden">
                            <a href="<?php echo esc_url($data['url-add_edit'] . '&id=' . $array['id']);?>"><?php esc_html_e('Edit', 'ulp');?></a>
                            |
                            <a onclick="ulpDeleteFromTable(<?php echo esc_attr($array['id']);?>, 'Notification', '#delete_notification_id', '#form_notification');" href="javascript:return false;" class="ulp-delete-link"><?php esc_html_e('Delete', 'ulp');?></a>
                            </div>
                            </td>
                            <td class="ulp-admin-notification-actions-warpper">
                            <div class="ulp-list-affiliates-name-label ulp-admin-notification-actions">
							<?php
							if (!empty($data['action_types']['admin'][$array['type']])){
								 echo esc_html($data['action_types']['admin'][$array['type']]);
							}
							elseif (!empty($data['action_types']['student'][$array['type']])){
								 echo esc_html($data['action_types']['student'][$array['type']]);
							}
							elseif (!empty($data['action_types']['announcements'][$array['type']])){
								 echo esc_html($data['action_types']['announcements'][$array['type']]);
							}
							elseif (!empty($data['action_types']['qanda'][$array['type']])){
								 echo esc_html($data['action_types']['qanda'][$array['type']]);
							}
									?>
                            </div>
                            </td>
								<td>
								<?php if (!empty($data['action_types']['admin'][$array['type']])):?>
								<?php esc_html_e('Administrator', 'ulp');?>
								<?php elseif (!empty($data['action_types']['student'][$array['type']])):?>
								<?php esc_html_e('Student', 'ulp');?>
								<?php endif;?>
                                </td>
                                <td>
								<?php if ($array['course_id']>-1):?>
                                <div class="ulp-property">
                                <a href="<?php echo admin_url('post.php?post=' . $array['course_id'] . '&action=edit');?>" target="_blank"><?php echo esc_html($array['course_label']);?></a>
                                </div>
								<?php else:?>
								<?php echo esc_html($array['course_label']);?>
								<?php endif;?>
                                </td>
								<?php if ($data['pushover']):?>
                                <td>
								<?php if ($array['pushover_status']):?>
                                <i class="fa-ulp fa-pushover-on-ulp"></i>
								<?php endif;?>
                                </td>
								<?php endif;?>
								<!-- deveoper-->
								<td>

									<div class="ulp-js-notifications-fire-notification-test ulp-notifications-list-send"
													data-notification_id="<?php echo esc_attr($array['id']);?>"
													data-email="<?php echo get_option( 'admin_email' );?>"
											><?php esc_html_e('Send Test Email', 'ulp');?></div>

								</td>
								<!-- end dev -->
                                </tr>
								<?php endforeach;?>
                                </tbody>
                                </table>
                                <input type="hidden" name="delete_notification" value="" id="delete_notification_id" />
                     </form>
					 <?php else :?>
              <h5><?php esc_html_e('No Notification Available!', 'ulp');?></h5>
			  <?php endif;?>
              </div>

    </div><!-- end of ulp-dashboard-wrap -->
<div class="ulp-clear"></div>
<?php

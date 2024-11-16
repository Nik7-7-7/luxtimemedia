			<form  method="post" id="custom_fields_form">

				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

				<span class="uap-add-new-like-wp">
					<i class="fa-uap fa-add-uap"></i>
					<a href="<?php echo esc_url($data['url_edit_custom_fields']);?>" class="uap-add-new-like-wp"><?php  esc_html_e('Add New Form Field', 'uap');?></a>
				</span>
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php  esc_html_e('Form Fields', 'uap');?></h3>
					<div class="inside">
						<div class="uap-submit-form">
							<input id="uap-submit-reg" type="submit" value="<?php  esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
								<div class="uap-sortable-table-wrapp">

									<table class="wp-list-table widefat fixed tags uap-alternate" id='uap-register-fields-table'>
										    <thead>
												<tr>
													<th class="manage-column"><?php  esc_html_e('Slug', 'uap');?></th>
													<th class="manage-column"><?php  esc_html_e('Label', 'uap');?></th>
													<th class="manage-column"><?php  esc_html_e('Field Type', 'uap');?></th>
													<th class="manage-column"><?php  esc_html_e('On Admin', 'uap');?></th>
													<th class="manage-column"><?php  esc_html_e('On Register Page', 'uap');?></th>
													<th class="manage-column"><?php  esc_html_e('On Affiliate Portal', 'uap');?></th>
													<th class="manage-column"><?php  esc_html_e('Required', 'uap');?></th>
													<th class="manage-column"><?php  esc_html_e('WP Native', 'uap');?></th>
													<th class="manage-column uap-table-check-col"></th>
													<th class="manage-column uap-table-check-col"></th>
												</tr>
											</thead>
											<tbody>
									<?php
									$no_edit = array(
														'uap_social_media',
														'user_email',
														'first_name',
														'last_name',
														'user_url',
														'pass1',
														'pass2',
														'description',
														'user_login',
									);

									$no_delete_fields = array(
														'user_login',
														'uap_avatar',
														'recaptcha',
														'tos',
														'uap_country',
														'uap_social_media',
														'uap_optin_accept',
														'user_email',
														'first_name',
														'last_name',
														'user_url',
														'pass1',
														'pass2',
														'description',
														'uap_affiliate_paypal_email',
														'uap_affiliate_bank_transfer_data'
									);
									foreach ($data['register_fields'] as $k=>$v){

										switch ($v['name']){
											case 'uap_avatar':
												$tr_extra_class = "uap-avatar-tr-dashboard";
												break;
											default:
												$tr_extra_class = '';
												break;
										}
										?>
										<tr class="<?php echo esc_attr($tr_extra_class);?>" id="tr_<?php echo esc_attr($k);?>">
											<td>
												<?php echo esc_html($v['name']);?>
												<input type="hidden" value="<?php echo esc_attr($k);?>" name="uap-order-<?php echo esc_attr($k);?>" class="uap-order" />
											</td>
											<td><?php
													if ($v['native_wp']){
														 esc_html_e($v['label'], 'uap');
													} else {
														echo esc_html($v['label']);
													}
												?>
											</td>
											<td><?php echo esc_html($v['type']);?></td>
											<td>
												<?php
													$notAvailableForAdminSection = [ 'uap_social_media', 'payment_select', 'confirm_email', 'pass2' ];
													if( in_array( $v['name'], $notAvailableForAdminSection ) ){
														echo esc_html('-');
													} else if ($v['display_admin']==2){
														 esc_html_e('Always', 'uap');
													} else {
														?><label class="uap-checkbox-wrapp">
														<input type="checkbox" onClick="checkAndH(this, '#uap-field-display-admin<?php echo esc_attr($k);?>');" <?php echo ($v['display_admin']) ?  'checked' : '';?> />
														<input type="hidden" value="<?php echo esc_attr($v['display_admin']);?>" name="uap-field-display-admin<?php echo esc_attr($k);?>" id="uap-field-display-admin<?php echo esc_attr($k);?>" />
														<?php
													}
												?>
												<span class="uap-checkmark"></span>
											</label></td>
											<td>
												<?php
													if ($v['display_public_reg']==2){
														 esc_html_e('Always', 'uap');
													} else {
														?><label class="uap-checkbox-wrapp">
														<input type="checkbox" onClick="checkAndH(this, '#uap-field-display-public-reg<?php echo esc_attr($k);?>');" <?php echo ($v['display_public_reg']) ?  'checked' : '';?> />
														<input type="hidden" value="<?php echo esc_attr($v['display_public_reg']);?>" name="uap-field-display-public-reg<?php echo esc_attr($k);?>" id="uap-field-display-public-reg<?php echo esc_attr($k);?>" />
														<?php
													}
												?>
												<span class="uap-checkmark"></span>
											</label>
											</td>
											<td>
												<?php
													if ($v['display_public_ap']==2){
														 esc_html_e('Always', 'uap');
													} else if (in_array($v['name'], array('uap_social_media', 'pass2', 'pass1'))){
														echo esc_html('-');
													} else {
														?><label class="uap-checkbox-wrapp">
														<input type="checkbox" onClick="checkAndH(this, '#uap-field-display-public-ap<?php echo esc_attr($k);?>');" <?php echo ($v['display_public_ap']) ?  'checked' : '';?> />
														<input type="hidden" value="<?php echo esc_attr($v['display_public_ap']);?>" name="uap-field-display-public-ap<?php echo esc_attr($k);?>" id="uap-field-display-public-ap<?php echo esc_attr($k);?>" />
														<?php
													}
												?>
												<span class="uap-checkmark"></span>
											</label>
											</td>
											<td>
												<?php
													if ($v['display_public_reg']==2){
														 esc_html_e('Always', 'uap');
													} else if ($v['req']==2){
														 esc_html_e('Required When Selected', 'uap');
													} else if ($v['name']=='uap_social_media'){
														echo esc_html('-');
													} else {
														?><label class="uap-checkbox-wrapp">
														<input type="checkbox" onClick="checkAndH(this, '#uap-require-<?php echo esc_attr($k);?>');" <?php echo ($v['req']) ?  'checked' : '';?> id="req-check-<?php echo esc_attr($k);?>" />
														<input type="hidden" value="<?php echo esc_attr($v['req']);?>" name="uap-require-<?php echo esc_attr($k);?>" id="uap-require-<?php echo esc_attr($k);?>" />
														<?php
													}
												?>
												<span class="uap-checkmark"></span>
											</label>
											</td>
											<td>
												<?php
													if ($v['native_wp']){
														 esc_html_e('Yes', 'uap');
													} else {
														 esc_html_e('No', 'uap');
													}
												?>
											</td>
											<td>
												<?php
													if(in_array($v['name'], $no_edit) ){
														echo esc_html('-');
													} else {
														?>
														<a href="<?php echo esc_url($data['url_edit_custom_fields'] . '&id='.$k);?>">
															<i class="fa-uap fa-edit-uap"></i>
														</a>
														<?php
													}
												?>
											</td>
											<td>
												<?php
													if ( in_array($v['name'], $no_delete_fields)){
														echo esc_html('-');
													} else {
														?>
															<a href="javascript: return false;" onClick="uapDeleteFromTable(<?php echo esc_attr($k);?>, 'custom_field', '#delete_custom_field', '#custom_fields_form');">
																<i class="fa-uap fa-remove-uap"></i>
															</a>
														<?php
													}
												?>
											</td>
										</tr>
										<?php
										}
									?>
										</tbody>

										<tfoot>
											<tr>
												<th class="manage-column"><?php  esc_html_e('Slug', 'uap');?></th>
												<th class="manage-column"><?php  esc_html_e('Label', 'uap');?></th>
												<th class="manage-column"><?php  esc_html_e('Field Type', 'uap');?></th>
												<th class="manage-column"><?php  esc_html_e('On Admin', 'uap');?></th>
												<th class="manage-column"><?php  esc_html_e('On Register Page', 'uap');?></th>
												<th class="manage-column"><?php  esc_html_e('On Affiliate Portal', 'uap');?></th>
												<th class="manage-column"><?php  esc_html_e('Required', 'uap');?></th>
												<th class="manage-column"><?php  esc_html_e('WP Native', 'uap');?></th>
												<th class="manage-column"></th>
												<th class="manage-column"></th>
											</tr>
										</tfoot>
									</table>
								</div>
						<input type="hidden" value="" name="delete_custom_field" id="delete_custom_field" />
						<div class="uap-submit-form">
							<input id="uap-submit-reg" type="submit" value="<?php  esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</form>

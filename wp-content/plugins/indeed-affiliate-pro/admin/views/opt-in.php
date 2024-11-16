<div class="uap-wrapper">
			<form  method="post">

				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Opt-In Integrations Settings', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/how-to-activate-optin-subscriptions/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
					<div class="inside">
						<div class="uap-form-line">
							<h2><?php esc_html_e('Enable Opt-In Integrations', 'uap');?></h2>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_register_opt-in']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_register_opt-in');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_register_opt-in" value="<?php echo esc_attr($data['metas']['uap_register_opt-in']);?>" id="uap_register_opt-in" />
						</div>
						<div class="uap-form-line">
								<h2><?php esc_html_e('Opt-In Destination', 'uap');?></h2>
			                	<select name="uap_register_opt-in-type">
			                    	    <?php
			                        	    $subscribe_types = array(
			                                                                    'aweber' => 'AWeber',
																																					'active_campaign' => 'Active Campaign',
			                                                                    'campaign_monitor' => 'CampaignMonitor',
			                                                                    'constant_contact' => 'Constant Contact',
			                                                                    'email_list' => esc_html__('E-mail List', 'uap'),
			                                                                    'get_response' => 'GetResponse',
			                                                                    'icontact' => 'IContact',
			                                                                    'madmimi' => 'Mad Mimi',
			                                                                    'mailchimp' => 'MailChimp',
			                                                                    'mymail' => 'Mailster (MyMail)',
			                                                                    'wysija' => 'Wysija',
			                                                                 );
			                                foreach ($subscribe_types as $k=>$v){
			                                    $selected = ($data['metas']['uap_register_opt-in-type']==$k) ? 'selected' : '';
			                                    ?>
			                                    <option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php
			                            	     	echo esc_html($v);
			                                    ?></option>
			                                    <?php
			                                }
			                                ?>
			                    </select>
						<p><?php esc_html_e('During the registration process, the affiliate email address is sent to your OptIn destination.', 'uap');?></p>
						</div>
						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>

				<div class="uap-stuffbox">
					<h3 class="uap-h3">Active Campaign</h3>
					<div class="uap-form-line">
					<div class="inside">
						<div class="row">
							<div class="col-xs-6">
					   		<div class="input-group">
					           <span class="input-group-addon"> <?php esc_html_e('Api URL', 'uap');?></span>

					            <input type="text" value="<?php echo esc_attr($data['metas']['uap_active_campaign_apiurl']);?>" name="uap_active_campaign_apiurl" class="form-control">
									</div>
									<div class="uap-form-line"></div>
								<div class="input-group">
					           <span class="input-group-addon"> <?php esc_html_e('Api Key:', 'uap');?></span>
					            <input type="text" value="<?php echo esc_attr($data['metas']['uap_active_campaign_apikey']);?>" name="uap_active_campaign_apikey" class="form-control">
								</div>
								<div class="uap-form-line"></div>
								<div class="input-group">
					           <span class="input-group-addon"> <?php esc_html_e('List ID:', 'uap');?></span>
					            <input type="text" value="<?php echo esc_attr($data['metas']['uap_active_campaign_listId']);?>" name="uap_active_campaign_listId" class="form-control">
								</div>
							</div>
						</div>
						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</div>
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Additional Main E-Mail', 'uap');?></h3>
					<div class="inside">
						<input type="text" name="uap_main_email" value="<?php echo esc_attr($data['metas']['uap_main_email']);?>"  class="uap-optin-input" />
						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>


				<div class="uap-stuffbox">
					<h3 class="uap-h3">Aweber</h3>
					<div class="inside">
						<div class="row">
						<div class="col-xs-6">
							<div class="input-group">
									 <div class="uap-labels-special"><?php esc_html_e('Auth Code', 'uap');?></div>
					            <textarea id="uap_aweber_auth_code" name="uap_aweber_auth_code" class="uap-dashboard-textarea"><?php
					            	echo esc_uap_content($data['metas']['uap_aweber_auth_code']);
					            ?></textarea>

								</div>
								<a href="https://auth.aweber.com/1.0/oauth/authorize_app/751d27ee" target="_blank" class="uap-info-link">
									<?php esc_html_e('Get Your Auth Code From Here', 'uap');?>
								</a>
								<div class="uap-form-line"></div>
								<div class="input-group">
  									 <span class="input-group-addon"><?php esc_html_e('Unique List ID:', 'uap');?></span>
					            <input type="text" value="<?php echo esc_attr($data['metas']['uap_aweber_list']);?>" name="uap_aweber_list" class="form-control">
											</div>
					            <a href="https://www.aweber.com/users/settings/" target="_blank" class="uap-info-link">
					              <?php esc_html_e('Get Unique List ID', 'uap');?><br>
					            </a>
									<div class="uap-form-line"></div>
					            <div onclick="uapConnectAweber( '#uap_aweber_auth_code' );" class="button button-primary button-large">
					              <?php esc_html_e('Connect', 'uap');?>
					            </div>


					    <div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</div>
		</div>

				<div class="uap-stuffbox">
					<h3 class="uap-h3">Mailchimp</h3>
					<div class="inside">
						<div class="row">
						  <div class="col-xs-6">
						    <div class="input-group">
						         <span class="input-group-addon"><?php esc_html_e('API Key', 'uap');?></span>
					            <input type="text" value="<?php echo esc_attr($data['metas']['uap_mailchimp_api']);?>" name="uap_mailchimp_api" class="form-control">
								</div>
					            <a href="http://kb.mailchimp.com/article/where-can-i-find-my-api-key" target="_blank" class="uap-info-link">
					              <?php esc_html_e('Where can I find my API Key?', 'uap');?>
					            </a>
									<div class="uap-form-line"></div>
								<div class="input-group">
					            <span class="input-group-addon"><?php esc_html_e('ID List', 'uap');?></span>
					            <input type="text" value="<?php echo esc_attr($data['metas']['uap_mailchimp_id_list']);?>" name="uap_mailchimp_id_list" class="form-control">
								</div>
					            <a href="http://kb.mailchimp.com/article/how-can-i-find-my-list-id/" target="_blank" class="uap-info-link">
					              <?php esc_html_e('Where can I find List ID?', 'uap');?>
					            </a>
					    <div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</div>
		</div>

				<div class="uap-stuffbox">
					<h3 class="uap-h3">Get Response</h3>
					<div class="inside">
						<div class="row">
							<div class="col-xs-6">
								<div class="input-group">
										 <span class="input-group-addon">GetResponse <?php esc_html_e('API Key', 'uap');?></span>
					            <input type="text" value="<?php echo esc_attr($data['metas']['uap_getResponse_api_key']);?>" name="uap_getResponse_api_key" class="form-control">
					        </div>
					            <a href="http://www.getresponse.com/learning-center/glossary/api-key.html" target="_blank" class="uap-info-link">
					              <?php esc_html_e('Where can I find my API Key?', 'uap');?></a>

										<div class="uap-form-line"></div>

									<div class="input-group">
											<span class="input-group-addon">GetResponse <?php esc_html_e('List Token', 'uap');?></span>
					            <input type="text" value="<?php echo esc_attr($data['metas']['uap_getResponse_token']);?>" name="uap_getResponse_token" class="form-control">
									</div>
					            <a href="https://app.getresponse.com/campaign_list.html " target="_blank" class="uap-info-link">
					              <?php esc_html_e('Where can I find List Token?', 'uap');?>
					            </a>

					    <div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</div>
		</div>


				<div class="uap-stuffbox">
					<h3 class="uap-h3">Campaign Monitor</h3>
					<div class="inside">
						<div class="row">
							<div class="col-xs-6">
								<div class="input-group">
										 <span class="input-group-addon">CampaignMonitor <?php esc_html_e('API Key', 'uap');?></span>
					            <input type="text" value="<?php echo esc_attr($data['metas']['uap_cm_api_key']);?>" name="uap_cm_api_key" class="form-control">
					        </div>
					            <a href="https://www.campaignmonitor.com/api/getting-started/#apikey" target="_blank" class="uap-info-link">
					              <?php esc_html_e('Where can I find API Key ?', 'uap');?>
					            </a>
					          <div class="uap-form-line"></div>
								<div class="input-group">
					         		<span class="input-group-addon">CampaignMonitor <?php esc_html_e('List ID', 'uap');?></span>
					            <input type="text" value="<?php echo esc_attr($data['metas']['uap_cm_list_id']);?>" name="uap_cm_list_id" class="form-control">
					       </div>
					            <a href="https://www.campaignmonitor.com/api/clients/#subscriber_lists" target="_blank" class="uap-info-link">
					              <?php esc_html_e('Where can I find List ID?', 'uap');?>
					            </a>

					    <div id="uap_save_changes"  class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</div>
		</div>

				<div class="uap-stuffbox">
					<h3 class="uap-h3">IContact</h3>
					<div class="inside">
						<div class="row">
							<div class="col-xs-6">
								<div class="input-group">
										 <span class="input-group-addon">iContact <?php esc_html_e('Username', 'uap');?></span>
					            <input type="text" value="<?php echo esc_attr($data['metas']['uap_icontact_user']);?>" name="uap_icontact_user" class="form-control">
								</div>
								<div class="uap-form-line"></div>
								<div class="input-group">
					           <span class="input-group-addon"> iContact <?php esc_html_e('App ID', 'uap');?></span>
					            <input type="text" value="<?php echo esc_attr($data['metas']['uap_icontact_appid']);?>" name="uap_icontact_appid" class="form-control">
								</div>
					            <a href="http://www.icontact.com/developerportal/documentation/register-your-app/" target="_blank" class="uap-info-link">
					              <?php esc_html_e('Where can I get my App ID?', 'uap');?>
					            </a>
								<div class="uap-form-line"></div>
					       <div class="input-group">
					            <span class="input-group-addon">iContact <?php esc_html_e('App Password', 'uap');?></span>
					            <input type="text" value="<?php echo esc_attr($data['metas']['uap_icontact_pass']);?>" name="uap_icontact_pass" class="form-control">
								 </div>
								 <div class="uap-form-line"></div>
					     	 <div class="input-group">
					           <span class="input-group-addon"> iContact <?php esc_html_e('List ID', 'uap');?></span>
					            <input type="text" value="<?php echo esc_attr($data['metas']['uap_icontact_list_id']);?>" name="uap_icontact_list_id" class="form-control">
					      	</div>
					              <a href="https://app.icontact.com/icp/core/mycontacts/lists" target="_blank" class="uap-info-link">
					                <?php esc_html_e('Click on the list name:', 'uap');?>
					              </a>

					</div>
				</div>
				<div><?php esc_html_e('Click on the list name and get the ID from the URL', 'uap');?> (ex:  https://app.icontact.com/icp/core/mycontacts/lists/edit/
				<b>
					ID_LIST
				</b>
				/?token=f155cba025333b071d49974c96ae0894 )</div>
				<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
				</div>
			</div>

			<div class="uap-stuffbox">
				<h3 class="uap-h3">Constant Contact</h3>
				<div class="inside">
					<div class="row">
						<div class="col-xs-6">
							<div class="input-group">
								<span class="input-group-addon">Constant Contact <?php esc_html_e('Username', 'uap');?></span>
				            <input type="text" value="<?php echo esc_attr($data['metas']['uap_cc_user']);?>" id="uap_cc_user" name="uap_cc_user" class="form-control">
							</div>

							<div class="uap-form-line"></div>

							<div class="input-group">
									<span class="input-group-addon">Constant Contact <?php esc_html_e('Password', 'uap');?></span>
				            <input type="password" value="<?php echo esc_attr($data['metas']['uap_cc_pass']);?>" id="uap_cc_pass" name="uap_cc_pass" class="form-control">
							</div>
							<div class="uap-form-line"></div>
				            <div onclick="uapGetCcList( '#uap_cc_user', '#uap_cc_pass' );" class="button button-primary button-large">
				              <?php esc_html_e('Get Lists', 'uap');?>
				            </div>
							<div class="uap-form-line"></div>

								<div class="input-group">
										 <span class="uap-labels-special">Constant Contact <?php esc_html_e('List', 'uap');?></span>
				            <select id="uap_cc_list" name="uap_cc_list" class="uap-optin-input">
				            	<?php
				            		$list_name = '';
				            		if (isset($data['metas']['uap_cc_list']) && $data['metas']['uap_cc_list']){
				            			//getting list name by id
				            			include_once UAP_PATH . 'classes/services/email_services/constantcontact/class.cc.php';
				            			$cc = new cc($data['metas']['uap_cc_user'], $data['metas']['uap_cc_pass']);
				            			$list_arr= $cc->get_list($data['metas']['uap_cc_list']);
				            			if(isset($list_arr['Name'])){
														 $list_name = $list_arr['Name'];
													}
				            		}
				            	?>
				            	<option value="<?php echo esc_attr($data['metas']['uap_cc_list']);?>"><?php echo esc_html($list_name);?></option>
				            </select>
									</div>
					<div id="uap_save_changes" class="uap-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
					</div>
					</div>
				</div>
			</div>
		</div>


			<div class="uap-stuffbox">
				<h3 class="uap-h3">Wysija Contact</h3>
				<div class="inside">
				    <table>
				      <tbody>
				        <tr>
				          <td>
				            <?php esc_html_e('Select Wysija List:', 'uap');?>
				          </td>
				          <td>
		                  	<?php
		                        $wysija_list = $obj->indeed_returnWysijaList();
		                        if ($wysija_list && count($wysija_list)>0){
		                        	?>
		                            <select name="uap_wysija_list_id">
		                            	<?php
		                                	foreach ($wysija_list as $k=>$v){
		                                		$selected = '';
		                                		if($data['metas']['uap_wysija_list_id']==$k){
																					 $selected = 'selected="selected"';
																				}
		                                        ?>
		                                        	<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
		                                        <?php
		                                    }
		                                ?>
		                            </select>
		                     <?php
		                     	}else echo esc_html__("No List available ", 'uap') . "<input type='hidden' name='uap_wysija_list_id' value=''/> ";
		                     ?>
				          </td>
				        </tr>
				      </tbody>
				    </table>
					<div id="uap_save_changes" class="uap-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
					</div>
				</div>
			</div>

			<div class="uap-stuffbox">
				<h3 class="uap-h3">Mailster (MyMail)</h3>
				<div class="inside">
				            <div class="uap-labels-special"><?php esc_html_e('Select MyMail List:', 'uap');?></div>
							<?php
		                    	$mymailList = $obj->indeed_getMyMailLists();
		                        if ($mymailList){
		                        	?>
		                            <select name="uap_mymail_list_id">
		                            	<?php
		                                foreach ($mymailList as $k=>$v){
		                                	$selected = '';
		                                	if ($data['metas']['uap_mymail_list_id']==$k){
																				 $selected = 'selected="selected"';
																			}
		                                    ?>
		                                    	<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
		                                <?php
		                                }
		                                ?>
		                            </select>
		                    <?php
		                    	}else echo esc_html__('No List available', 'uap') . " <input type='hidden' name='uap_mymail_list_id' value=''/> ";
				          	?>

					<div id="uap_save_changes" class="uap-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
					</div>
				</div>
			</div>

			<div class="uap-stuffbox">
				<h3 class="uap-h3">Mad Mimi</h3>
				<div class="inside">
					<div class="row">
						<div class="col-xs-6">
							<div class="input-group">
									 <span class="input-group-addon"> <?php esc_html_e('Username Or Email:', 'uap');?></span>
				            <input type="text" value="<?php echo esc_attr($data['metas']['uap_madmimi_username']);?>" name="uap_madmimi_username" class="form-control">
				       </div>
							 	<div class="uap-form-line"></div>
							<div class="input-group">
				             <span class="input-group-addon"><?php esc_html_e('Api Key:', 'uap');?></span>
				            <input type="text" value="<?php echo esc_attr($data['metas']['uap_madmimi_apikey']);?>" name="uap_madmimi_apikey" class="form-control">
								</div>
								<div class="uap-form-line"></div>
				       <div class="input-group">
				             <span class="input-group-addon"><?php esc_html_e('List Name:', 'uap');?></span>
				            <input type="text" value="<?php echo esc_attr($data['metas']['uap_madmimi_listname']);?>" name="uap_madmimi_listname" class="form-control">
				     		</div>
					<div id="uap_save_changes" class="uap-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
					</div>
				</div>
			</div>
		</div>
	</div>

			<div class="uap-stuffbox">
				<h3 class="uap-h3"><?php esc_html_e('Saved E-mail List', 'uap');?></h3>
				<div class="inside">
				    <textarea disabled  class="uap-custom-css-box"><?php
				    	echo esc_uap_content($data['metas']['uap_email_list']);
				    ?></textarea>
				</div>
			</div>
		</form>
</div>

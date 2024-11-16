<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">

		<h3 class="ulp-h3"><?php esc_html_e('2Checkout Payment Integration', 'ulp');?></h3>

		<div class="inside">
			<div class="ulp-inside-item">
          <div class="row">
              <div class="col-xs-6">
                  <h4 class="ulp-input-group-space"><?php esc_html_e('SandBox Mode', 'ulp');?></h4>
                  <label class="ulp_label_shiwtch ulp-switch-button-margin">
                    <?php $checked = ($data['metas']['ulp_2checkout_sandbox_on']) ? 'checked' : '';?>
                    <input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_2checkout_sandbox_on');" <?php echo esc_attr($checked);?> />
                    <div class="switch ulp-display-inline"></div>
                  </label>
                  <input type="hidden" name="ulp_2checkout_sandbox_on" value="<?php echo esc_attr($data['metas']['ulp_2checkout_sandbox_on']);?>" id="ulp_2checkout_sandbox_on" />

    				      <h2><?php esc_html_e('Main 2Checkout settings', 'ulp');?></h2>

                  <div class="input-group ulp-input-group-max ulp-input-group-space">
                			<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('API Username:', 'ulp');?></span>
                			<input type="text" class="form-control" name="ulp_2checkout_api_username" value="<?php echo esc_attr($data ['metas']['ulp_2checkout_api_username']);?>" id="ulp_2checkout_api_username">
                	</div>

                  <div class="input-group ulp-input-group-max ulp-input-group-space">
                			<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('API Password:', 'ulp');?></span>
                			<input type="text" class="form-control" name="ulp_2checkout_api_password" value="<?php echo esc_attr($data ['metas']['ulp_2checkout_api_password']);?>" id="ulp_2checkout_api_password">
                	</div>

                  <div class="input-group ulp-input-group-max ulp-input-group-space">
                			<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('API Private Key:', 'ulp');?></span>
                			<input type="text" class="form-control" name="ulp_2checkout_api_private_key" value="<?php echo esc_attr($data ['metas']['ulp_2checkout_api_private_key']);?>" id="ulp_2checkout_api_private_key">
                	</div>

                  <div class="input-group ulp-input-group-max ulp-input-group-space">
                			<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Merchant Code:', 'ulp');?></span>
                			<input type="text" class="form-control" name="ulp_2checkout_account_number" value="<?php echo esc_attr($data ['metas']['ulp_2checkout_account_number']);?>" id="ulp_2checkout_account_number">
                	</div>

                  <div class="input-group ulp-input-group-max ulp-input-group-space">
                			<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Secret word:', 'ulp');?></span>
                			<input type="text" class="form-control" name="ulp_2checkout_secret_word" value="<?php echo esc_attr($data ['metas']['ulp_2checkout_secret_word']);?>" id="ulp_2checkout_secret_word">
                	</div>

									<div>
										<div class="ulp-form-line"><strong><?php echo esc_html__("Important: set your 'Webhook' to: ", 'ulp') . $data ['webhook'];?></strong></div>
										<div class="ulp-setup-steps-wrapper">
											<div><?php esc_html_e('1. Go to', 'ulp');?> <a href="https://www.2checkout.com/" target="_blank">https://www.2checkout.com/</a> <?php echo esc_html__(' and login with username and password.', 'ihc');?> </div>
											<div><?php esc_html_e('2. After you login go to "Dashboard" section and then click on "Integration". Here you will find, "Merchant Code", "API Private Key" and "Secret Word".', 'ulp');?></div>
											<div><?php echo esc_html__('3. In Instant Notification System (INS) section make sure that Enable INS is checked. ', 'ulp');?></div>
											<div><?php echo esc_html__('4. Go to "INS settings" tab and add endpoint ', 'ulp') . '<b>'. $data ['webhook'] .'</b>.' . esc_html__(' In "Trigger list" section enable Order Created. However, additional triggers may be used depending on the situation.', 'ulp');?></div>
											<div><?php echo esc_html__('5. In "IPN settings" add your IPN URL with ','ulp') . '<b>'. $data ['webhook'] . '</b>' . esc_html__(' and make sure that "Completed orders" is checked. Additional triggers may be used depending on the situation.', 'ulp');?></div>
											</div>
									</div>

									<div class="">
						          <h2><?php esc_html_e('For Test/Sandbox mode use the next credentials available:', 'ulp');?></h2>
						          <a href="https://knowledgecenter.2checkout.com/Documentation/09Test_ordering_system/01Test_payment_methods" target="_blank">https://knowledgecenter.2checkout.com/Documentation/09Test_ordering_system/01Test_payment_methods</a>

											<div class="ulp-admin-payment-service-wrapp-table-data-exemple" ></div>
											  <table class="ulp-test-crdl">
											    <tr>
											      <th><?php esc_html_e('Description', 'ulp');?></th>
											      <th><?php esc_html_e('Number', 'ulp');?></th>
											    </tr>

											    <tr>
											      <td><?php esc_html_e('Credit Card:', 'ulp');?></td>
											      <td><code>4111111111111111</code></td>
											     </tr>
											     <tr>
											     <td><?php esc_html_e('Expire Time:', 'ulp');?></td>
											     <td><code>12/<?php echo substr( date("Y") + 1, - 2 );?></code></td>
												 	</tr>
													<tr>
													 <td>CVV:</td>
													 <td><code>123</code></td>
											     </tr>
													 <tr>
														 <td>Name:</td>
														 <td><code>John Doe</code></td>
													 </tr>
											  </table>
									</div>
              	</div>
          </div>
      </div>



      <div class="ulp-line-break"></div>

      <div class="ulp-inside-item ulp-input-group-space">
          <div class="row">
            <div class="col-xs-6">
                <h2><?php esc_html_e('Display options', 'ulp');?></h2>
          			<div class="input-group ulp-input-group-max ulp-input-group-space">
          					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Label', 'ulp');?></span>
          					<input type="text" class="form-control" name="ulp_2checkout_label" value="<?php echo stripslashes($data['metas']['ulp_2checkout_label']);?>" id="ulp_2checkout_label" />
          			</div>

    						<div class="input-group ulp-input-group-max ulp-input-group-space">
          					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Description', 'ulp');?></span>
          					<input type="text" class="form-control" name="ulp_2checkout_description" value="<?php echo stripslashes($data['metas']['ulp_2checkout_description']);?>" id="ulp_2checkout_description" />
                </div>

                <div class="input-group ulp-input-group-max ulp-input-group-space">
          					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Multi-Payment order', 'ulp');?></span>
          					<input type="number" min="1" class="form-control" name="ulp_2checkout_multipayment_order" value="<?php echo esc_attr($data['metas']['ulp_2checkout_multipayment_order']);?>" id="ulp_2checkout_multipayment_order" />
                </div>
            </div>
          </div>
      </div>

			<div class="ulp-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
			</div>
		</div>
	</div>
</form>

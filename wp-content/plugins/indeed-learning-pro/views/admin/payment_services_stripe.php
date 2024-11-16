<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />

	<div class="ulp-stuffbox">

		<h3 class="ulp-h3"><?php esc_html_e('Stripe Payment Integration', 'ulp');?></h3>

		<div class="inside">



			<div class="ulp-inside-item">

          <div class="row">

              <div class="col-xs-6">

    				      <h2><?php esc_html_e('Main Stripe settings', 'ulp');?></h2>


                  <div class="input-group ulp-input-group-space">

                			<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Publishable Key:', 'ulp');?></span>

                			<input type="text" class="form-control" name="ulp_stripe_publishable_key" value="<?php echo esc_attr($data ['metas']['ulp_stripe_publishable_key']);?>" id="ulp_stripe_publishable_key">

                	</div>

                  <div class="input-group ulp-input-group-space">

                			<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Secret Key:', 'ulp');?></span>

                			<input type="text" class="form-control" name="ulp_stripe_secret_key" value="<?php echo esc_attr($data ['metas']['ulp_stripe_secret_key']);?>" id="ulp_stripe_secret_key">

                	</div>
									<div>

										<div class="ulp-form-line"><strong><?php echo esc_html__("Important: set your 'Webhook' to: ", 'ulp') . $data ['webhook'];?></strong></div>



                                      <div class="ulp-setup-steps-wrapper">

                                        <div><?php echo esc_html__('1. Go to ', 'ulp') . '<a href="http://stripe.com" target="_blank">http://stripe.com</a>'  . esc_html__(' and login with username and password.', 'ulp');?></div>

										<div><?php esc_html_e('2. After that click on "Dashboard", and then select "Your account" - "Account settings".', 'ulp');?></div>

										<div><?php esc_html_e('3. A popup will appear and you must go to API Keys, here you will find the "Secret Key" and	"Publishable Key".', 'ulp');?></div>

										<div><?php echo esc_html__("4. Set your Web Hook URL to: ", "ulp") . "<a href='" . $data ['webhook'] . "' target='_blank'>"  . $data ['webhook'] . "</a>" .  esc_html__(" and choose receive all events", "ulp");?></div>

                                       </div>

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

            					<input type="text" class="form-control" name="ulp_stripe_label" value="<?php echo stripslashes($data['metas']['ulp_stripe_label']);?>" id="ulp_stripe_label" />

            			</div>

						<div class="input-group ulp-input-group-max ulp-input-group-space">

            					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Description', 'ulp');?></span>

            					<input type="text" class="form-control" name="ulp_stripe_description" value="<?php echo stripslashes($data['metas']['ulp_stripe_description']);?>" id="ulp_stripe_description" />

            			</div>

            			<div class="input-group ulp-input-group-max ulp-input-group-space">

            					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Multi-Payment order', 'ulp');?></span>

            					<input type="number" min="1" class="form-control" name="ulp_stripe_multipayment_order" value="<?php echo esc_attr($data['metas']['ulp_stripe_multipayment_order']);?>" id="ulp_stripe_multipayment_order" />

            			</div>


								<div class="ulp-form-line">
									<h2><?php esc_html_e('Test Credentials', 'ulp');?></h2>
									<div><?php esc_html_e('For Test/Sandbox mode use the next credentials available:', 'ulp'); ?></div>
									<div><?php echo esc_ulp_content( '<a href="https://stripe.com/docs/testing" target="_blank">https://stripe.com/docs/testing</a>'); ?></div>
									<div><?php esc_html_e('Example:', 'ulp'); ?></div>
									<div><?php esc_html_e('Credit Card: 4242424242424242', 'ulp'); ?></div>
									<div><?php echo esc_html__('Expire Time: 12', 'ulp') . ( 1 + date('y', time() ) ); ?></div>

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

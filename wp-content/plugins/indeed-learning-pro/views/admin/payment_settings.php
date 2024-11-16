<form  method="post" role="form">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">

		<h3 class="ulp-h3"><?php esc_html_e('Payments Settings', 'ulp');?></h3>



	<div class="inside">

	<div class="ulp-inside-item">

      <div class="row">

          <div class="col-xs-4">



          <h2><?php esc_html_e('Additional Payment options', 'ulp');?></h2>



          <h4 class="ulp-input-group-space"><?php esc_html_e('System Currency', 'ulp');?></h4>

				<div class="form-group row">

					<select name="ulp_currency" class="form-control m-bot15">

							<?php foreach ($data['currencies'] as $k=>$v):?>

									<?php

											if ($data['custom_currencies'] && isset($data ['custom_currencies'][$k])){

													$v .= esc_html__(' (custom currency)', 'ulp');

											}

									 ?>

									<option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['metas']['ulp_currency']) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>

							<?php endforeach;?>

		      </select>

				</div>


				<h4 class="ulp-input-group-space"><?php esc_html_e('Custom Currency Symbol', 'ulp');?></h4>
				<div class="form-group row ulp-payment-currency-row">
					<input type="text" class="form-control ulp-payment-currency-input" name="ulp_custom_currency_code" value="<?php echo esc_attr($data['metas']['ulp_custom_currency_code']);?>" />
				</div>



			<h4 class="ulp-input-group-space"><?php esc_html_e('Currency position', 'ulp');?></h4>

				<div class="form-group row">

					<select name="ulp_currency_position" class="form-control m-bot15">

							<?php foreach (array('left' => esc_html__('Left', 'ulp'), 'right' => esc_html__('Right', 'ulp')) as $k=>$v):?>

									<option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['metas']['ulp_currency_position']) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>

							<?php endforeach;?>

		      </select>

				</div>



         		</div>

      </div>

    </div>

    <div class="ulp-line-break"></div>

	<div class="ulp-inside-item">

      <div class="row">

          <div class="col-xs-6">

			<h4 class="ulp-input-group-space"><?php esc_html_e('Separators', 'ulp');?></h4>

				<div class="input-group ulp-input-group-max ulp-input-group-space">

					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Thousands Separator', 'ulp');?></span>

					<input type="text" class="form-control" name="ulp_thousands_separator" value="<?php echo esc_attr($data['metas']['ulp_thousands_separator']);?>" />

				</div>



				<div class="input-group ulp-input-group-max ulp-input-group-space">

					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Decimals Separator', 'ulp');?></span>

					<input type="text" class="form-control" name="ulp_decimals_separator" value="<?php echo esc_attr($data['metas']['ulp_decimals_separator']);?>" />

				</div>



				<div class="input-group ulp-input-group-max ulp-input-group-space">

					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Number of Decimals', 'ulp');?></span>

					<input type="number" class="form-control" min=0 name="ulp_num_of_decimals" value="<?php echo esc_attr($data['metas']['ulp_num_of_decimals']);?>" />

				</div>

		</div>

      </div>

    </div>

    <div class="ulp-line-break"></div>

		<div class="ulp-inside-item">

	      <div class="row">

	          <div class="col-xs-6">

	              <div class="ulp-form-line">

	                      <?php ulp_default_payment_type();?>

	              </div>

						</div>

      	</div>

    </div>



		<div class="ulp-line-break"></div>



		<div class="ulp-inside-item">

	      <div class="row">

	          <div class="col-xs-6">

              <h4 class="ulp-input-group-space"><?php esc_html_e('Order Prefix Code', 'ulp');?></h4>

	              <div class="input-group ulp-input-group-max ulp-input-group-space">

										<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Code:', 'ulp');?></span>

										<input type="text" class="form-control" name="ulp_order_prefix_code" value="<?php echo esc_attr($data['metas']['ulp_order_prefix_code']);?>" />

	              </div>

						</div>

      	</div>

    </div>



    <div class="ulp-line-break"></div>

	<div class="ulp-inside-item">

      <div class="row">

          <div class="col-xs-6">

				<div class="form-group row">

						<div class="col-4">

								<input type="submit" name="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" class="btn btn-primary pointer">

					  </div>

				</div>

		</div>

      </div>

    </div>

	</div>







<div class="clear"></div></div>

</form>

<form action="<?php echo admin_url('admin.php?page=ihc_manage&tab=orders');?>" method="post">

	<input type="hidden" name="ihc_admin_add_new_order_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_add_new_order_nonce' );?>" />

	<div class="ihc-stuffbox ihc-add-new-order-wrapper">
		<h3><?php esc_html_e('Add Manual Payment', 'ihc');?></h3>
		<div class="inside">
			<div class="iump-form-line">
				<h5><?php esc_html_e('Manually Create and assign a New Payment to specific Customer. Keep in mind that a such Payment will not charge him and makes any changes inside his Account.', 'ihc');?></h5>
			</div>
			<div class="iump-form-line">
      <div class="row">
      		<div class="col-xs-10">
      		    <div class="input-group">
          				<span class="input-group-addon" id="basic-addon1" ><?php esc_html_e('Username', 'ihc');?></span>
                  <input type="text" name="username"/>
      				</div>
      		</div>
      </div>
		</div>
		<div class="iump-form-line">
      <div class="row">
      		<div class="col-xs-10">
      		    <div class="input-group">
          				<span class="input-group-addon" id="basic-addon1" ><?php esc_html_e('Membership Plan', 'ihc');?></span>
                  <select name="lid">
                    <?php
											$levels = \Indeed\Ihc\Db\Memberships::getAll();
                      foreach ($levels as $k=>$v){
                        ?>
                        <option value="<?php echo esc_attr($k);?>" >
                          <?php echo esc_html($v['label']);?>
                        </option>
                        <?php
                      }
                    ?>
                  </select>
      				</div>
      		</div>
      </div>
		</div>
		<div class="iump-form-line">
            <div class="row">
            		<div class="col-xs-10">
            		    <div class="input-group">
                				<span class="input-group-addon" id="basic-addon1" ><?php esc_html_e('Amount', 'ihc');?></span>
                        <input type="number" min=0 name="amount_value" step="0.01" />
            				</div>
            		</div>
            </div>
					</div>
					<div class="iump-form-line">
            <div class="row">
      				<div class="col-xs-10">
      					<div class="input-group">
          					<span class="input-group-addon" id="basic-addon1" ><?php esc_html_e('Currency', 'ihc');?></span>
                    <select name="amount_type">
                      <?php
        								$currency_arr = ihc_get_currencies_list('all');
        								$custom_currencies = ihc_get_currencies_list('custom');
                        $ihc_currency = get_option('ihc_currency');
        								foreach ($currency_arr as $k=>$v){
        									?>
        									<option value="<?php echo esc_attr($k);?>" <?php if ($k==$ihc_currency){
														 echo esc_attr('selected');
													}?> >
        										<?php echo esc_html($v);?>
        										<?php if (is_array($custom_currencies) && in_array($v, $custom_currencies)){
															  esc_html_e(" (Custom Currency)");
														}?>
        									</option>
        									<?php
        								}
        							?>
                    </select>
      					</div>
      				</div>
      			</div>
					</div>
					<div class="iump-form-line">
            <div class="row">
      				<div class="col-xs-10">
      					<div class="input-group">
          					<span class="input-group-addon" id="basic-addon1" ><?php esc_html_e('Created Date', 'ihc');?></span>
                    <input type="text" id="created_date_ihc" name="create_date" />
      					</div>
      				</div>
      			</div>
					</div>
					<div class="iump-form-line">
			<div class="row">
				<div class="col-xs-10">
					<div class="input-group">
					<span class="input-group-addon" id="basic-addon1" ><?php esc_html_e('Payment Service', 'ihc');?></span>
          <select name="ihc_payment_type">
            <?php
  						$payments = ihc_get_active_payment_services();
  						if ($payments):
  							foreach ($payments as $k=>$v):
  								$selected = ($k=='bank_transfer') ? 'selected' : '';
  								?>
  								<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_attr($v);?></option>
  								<?php
  							endforeach;
  						endif;
  					?>
          </select>
					</div>
				</div>
			</div>
		</div>

			<div class="ihc-wrapp-submit-bttn">
				<input type="submit" value="<?php esc_html_e('Add New Payment', 'ihc');?>" name="save_order" class="button button-primary button-large ihc_submit_bttn" />
			</div>
		</div>

	</div>
</form>

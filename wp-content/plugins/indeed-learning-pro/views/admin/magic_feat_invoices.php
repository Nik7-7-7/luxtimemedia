<form  method="post" id="invoice_form">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />

	<div class="ulp-stuffbox">


		<h3 class="ulp-h3"><?php esc_html_e('Invoices', 'ulp');?></h3>


		<div class="inside">





			<div class="ulp-form-line">


					<h2><?php esc_html_e('Activate/Hold Invoices', 'ulp');?></h2>


				<label class="ulp_label_shiwtch ulp-switch-button-margin">


					<?php $checked = ($data['metas']['ulp_invoices_enable']) ? 'checked' : '';?>


					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_invoices_enable');" <?php echo esc_attr($checked);?> />


					<div class="switch ulp-display-inline"></div>


				</label>


				<input type="hidden" name="ulp_invoices_enable" value="<?php echo esc_attr($data['metas']['ulp_invoices_enable']);?>" id="ulp_invoices_enable"/>


			</div>





      <div class="ulp-form-line">


        <p><?php esc_html_e('Show only for \'Completed\' Payment', 'ulp'); ?></p>


      	<label class="ulp_label_shiwtch ulp-switch-button-margin">


      	   <?php $checked = ($data['metas']['ulp_invoices_only_completed_payments']) ? 'checked' : '';?>


      		 <input type="checkbox" class="ulp-switch" onClick="ulpCheckAndH(this, '#ulp_invoices_only_completed_payments');" <?php echo esc_attr($checked);?> />


      		 <div class="switch ulp-display-inline"></div>


      	</label>


  			<input type="hidden" name="ulp_invoices_only_completed_payments" value="<?php echo esc_attr($data['metas']['ulp_invoices_only_completed_payments']);?>" id="ulp_invoices_only_completed_payments" />


      </div>





        <div class="ulp-form-line">


					<h4><?php esc_html_e('Invoice Template', 'ulp');?></h4>


					<select name="ulp_invoices_template" onChange="ulpAdminPreviewInvoice();"><?php


						foreach (['ulp-invoice-template-1' => esc_html__('Template 1', 'ulp'), 'ulp-invoice-template-2' => esc_html__('Template 2', 'ulp')] as $k=>$v){


							$selected = ($data['metas']['ulp_invoices_template']==$k) ? 'selected' : '';


							?>


							<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>


							<?php


						}


					?></select>


				</div>





				<div class="ulp-form-line">


					<h4><?php esc_html_e('Invoice Logo', 'ulp');?></h4>


					<input type="text" onblur="ulpAdminPreviewInvoice();" class="ulp-admin-invoice-logo" name="ulp_invoices_logo" value="<?php echo esc_attr($data['metas']['ulp_invoices_logo']);?>" onClick="openMediaUp(this);" />	<i class="fa-ulp ulp-icon-remove-e ulp-pointer ulp-js-magic-feat-invoice-remove" ></i>


				</div>





				<div class="ulp-form-line">


					<h4><?php esc_html_e('Invoice main Title', 'ulp');?></h4>


					<input type="text" onblur="ulpAdminPreviewInvoice();" name="ulp_invoices_title" value="<?php echo esc_attr($data['metas']['ulp_invoices_title']);?>"/>


				</div>








        <div class="ulp-form-line">


          <h2><?php esc_html_e('Additional Invoice Details', 'ulp');?></h2>


        </div>





        <div class="row ulp-admin-invoice-row-nomargin">


          <div class="col-xs-5">


            <h4><?php esc_html_e('Company Field', 'ulp');?></h4>


            <div class="ulp-admin-invoice-company-editor">


              <?php wp_editor( stripslashes($data['metas']['ulp_invoices_company_field']), 'ulp_invoices_company_field', array('textarea_name'=>'ulp_invoices_company_field', 'quicktags'=>TRUE) );?>


            </div>


          </div>





          <div class="col-xs-7">


            <h4><?php esc_html_e('Bill to', 'ulp');?></h4>


            <div class="ulp-admin-invoice-bill-editor">


              <?php wp_editor( stripslashes($data['metas']['ulp_invoices_bill_to']), 'ulp_invoices_bill_to', array('textarea_name'=>'ulp_invoices_bill_to', 'quicktags'=>TRUE) );?>


            </div>


            <div class="ulp-admin-invoice-constants-wrapper">


                <?php


                  echo esc_ulp_content("<h4>" . esc_html__('Standard Fields constants', 'ulp')."</h4>");


                  $constants = [


                            '{username}'=>'',


                            '{user_email}'=>'',


                            '{first_name}'=>'',


                            '{last_name}'=>'',


                            '{account_page}'=>'',


                            '{blogname}'=>'',


                            '{blogurl}'=>'',


                            '{currency}'=>'',


                            '{amount}'=>'',


                            '{current_date}' => '',


                            '{course_name}' => ''


                  ];


                  foreach ($constants as $k=>$v):?>


                    <div><?php echo esc_html($k);?></div>


                  <?php endforeach;?>


              </div>


          </div>





          <div class="ulp-clear"></div>


        </div>





        <div class="ulp-form-line">


          <h2><?php esc_html_e('Footer Invoice Info', 'ulp');?></h2>


        </div>





        <div class="ulp-admin-invoice-footer-editor">


          <?php wp_editor( stripslashes($data['metas']['ulp_invoices_footer']), 'ulp_invoices_footer', array('textarea_name'=>'ulp_invoices_footer', 'quicktags'=>TRUE) );?>


        </div>





        <div class="ulp-form-line">


          <h2><?php esc_html_e('Custom CSS', 'ulp');?></h2>


          <textarea name="ulp_invoices_custom_css" onblur="ulpAdminPreviewInvoice();" class="ulp-admin-custom-css"><?php echo stripslashes($data['metas']['ulp_invoices_custom_css']);?></textarea>


        </div>








			<div class="ulp-submit-form">


				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />


			</div>





		</div>


	</div>





</form>





<div class="ulp-stuffbox">


	<h3 class="ulp-h3"><?php esc_html_e('Preview', 'ulp');?></h3>


	<div class="inside" id="preview_container">


	</div>


</div>


<span class="ulp-js-magic-feat-invoices" ></span>

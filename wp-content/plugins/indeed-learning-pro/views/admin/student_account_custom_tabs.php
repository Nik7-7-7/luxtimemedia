<?php
$custom_css = '';
foreach ($data ['menu_items'] as $slug => $item):

	$custom_css .= ".fa-" . $slug . "-account-ulp:before{".
		"content: '\\".$item['icon']."';".
    "font-size: 20px;".
	"}";

endforeach;
if ($data ['font_awesome']):
  foreach ($data ['font_awesome'] as $base_class => $code):
    $custom_css .= ".". $base_class .":before{".
      "content: '\\".$code."';".
      "}";
  endforeach;
endif;
wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );

 ?>
<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />

	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Account custom Tabs', 'ulp');?></h3>
		<div class="inside">
    		<div class="ulp-inside-item">
		          <div class="row">
		              <div class="col-xs-6">
		            			<div class="ulp-form-line">
		            					<h2><?php esc_html_e('Activate account custom Tabs', 'ulp');?></h2>
		            				<label class="ulp_label_shiwtch ulp-switch-button-margin">
		            					<?php $checked = ($data['metas']['ulp_student_account_custom_tabs_enabled']) ? 'checked' : '';?>
		            					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_student_account_custom_tabs_enabled');" <?php echo esc_attr($checked);?> />
		            					<div class="switch ulp-display-inline"></div>
		            				</label>
		            				<input type="hidden" name="ulp_student_account_custom_tabs_enabled" value="<?php echo esc_attr($data['metas']['ulp_student_account_custom_tabs_enabled']);?>" id="ulp_student_account_custom_tabs_enabled" />
		            			</div>

		            			<div class="ulp-submit-form">
		            				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
		            			</div>
		        			</div>

		        </div>
				</div>
		</div>
	</div>
		<div class="ulp-stuffbox">
			<h3 class="ulp-h3"><?php esc_html_e('Add new Menu Item', 'ulp');?></h3>
			<div class="inside">
					<div class="ulp-inside-item">
								<div class="row">
										<div class="col-xs-6">
			            			<div class="ulp-form-line">
														<div class="inside">

														  				<div class="input-group ulp-input-group-max">
															  					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Slug:', 'ulp');?></span>
															  					<input type="text" class="form-control" value="" name="slug" />
														  				</div>

														  				<div class="input-group ulp-input-group-max ulp-input-group-space ulp-margin-bottom">
															  					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Label:', 'ulp');?></span>
															  					<input type="text" class="form-control" value="" name="label" />
														  				</div>

																			<div class="input-group ulp-input-group-max ulp-input-group-space">
															  					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Link:', 'ulp');?></span>
															  					<input type="text" class="form-control" value="" name="url" />
														  				</div>
																			<span class="ulp-margin-bottom ulp-display-block"><?php esc_html_e('(optional)', 'ulp');?></span>

																			<label><?php esc_html_e('Icon', 'ulp');?></label>
																			<div class="ulp-icon-select-wrapper">
																					<div class="ulp-icon-input">
																						<div id="indeed_shiny_select" class="ulp-shiny-select-html"></div>
																					</div>
																					<div class="ulp-icon-arrow"><i class="fa-ulp fa-arrow-ulp"></i></div>
																				<div class="ulp-clear"></div>
																			</div>

														  				<div class="ulp-submit-form">
														  					<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
														  				</div>
														 </div>
												</div>
									</div>
			    		  </div>
							</div>
						</div>
		</div>

		<div class="ulp-stuffbox">
			<h3 class="ulp-h3"><?php esc_html_e('ReOrder Menu Items', 'ulp');?></h3>
			<div class="inside">
				<div class="ulp-sortable-table-wrapp">
					<table class="wp-list-table widefat fixed tags ulp-admin-tables ulp-custom-tabs-table" id="ulp_reorder_menu_items">
						<thead>
							<tr>
								<th class="manage-column"><?php esc_html_e('Slug', 'ulp');?></th>
								<th class="manage-column"><?php esc_html_e('Label', 'ulp');?></th>
								<th class="manage-column"><?php esc_html_e('Icon', 'ulp');?></th>
								<th class="manage-column"><?php esc_html_e('Link', 'ulp');?></th>
								<th class="manage-column ulp-table-delete-col"><?php esc_html_e('Delete', 'ulp');?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th class="manage-column"><?php esc_html_e('Slug', 'ulp');?></th>
								<th class="manage-column"><?php esc_html_e('Label', 'ulp');?></th>
								<th class="manage-column"><?php esc_html_e('Icon', 'ulp');?></th>
								<th class="manage-column"><?php esc_html_e('Link', 'ulp');?></th>
								<th class="manage-column ulp-table-delete-col"><?php esc_html_e('Delete', 'ulp');?></th>
							</tr>
						</tfoot>
						<tbody>
							<?php $k = 0;?>
							<?php foreach ($data ['menu_items'] as $slug=>$item):?>
								<tr class="<?php echo ($k%2==0) ? 'alternate' : '';?>" id="tr_<?php echo esc_attr($slug);?>">

									<td class="ulp-custom-tabs-table-col1"><input type="hidden" value="<?php echo esc_attr($k);?>" name="ulp_account_page_menu_order[<?php echo esc_attr($slug);?>]" class="ulp_account_page_menu_order" /><?php echo esc_attr($slug);?></td>
									<td class="ulp-custom-tabs-table-col2"><?php echo esc_html($item['label']);?></td>
									<td class="ulp-custom-tabs-table-col3"><i class="<?php echo esc_attr('fa-ulp fa-' . esc_attr($slug) . '-account-ulp');?>"></i></td>
									<td>
											<?php if (!empty($item ['url'])):?>
													<?php echo esc_url($item ['url']);?>
											<?php else :?>
													-
											<?php endif;?>
									</td>
									<td class="ulp-custom-tabs-table-col4">
										<?php
											if (isset($data ['standard_tabs'][$slug])){
												echo esc_html('-');
											} else {
												?>
												<a href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=student_account_custom_tabs&delete=') . $slug;?>">
													<i class="fa-ulp fa-remove-ulp"></i>
												<?php
											}
										?>
									</a></td>
								</tr>
								<?php $k++;?>
							<?php endforeach;?>
						</tbody>
					</table>
				</div>

				<div class="ulp-submit-form">
					<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
				</div>

			</div>
		</div>

</form>



<span class="ulp-js-student-account-custom-tabs"></span>

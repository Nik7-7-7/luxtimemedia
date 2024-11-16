<?php
wp_enqueue_script( 'wp-theme-plugin-editor' );
wp_enqueue_style( 'wp-codemirror' );
wp_enqueue_script( 'code-editor' );
wp_enqueue_style( 'code-editor' );
 ?>

		<div class="uap-page-title"><span class="second-text"><?php esc_html_e('Additional Settings', 'uap');?></span>
	</div>
	<div class="uap-wrapper">
		<form  method="post">
			<div class="uap-stuffbox">
				<h3 class="uap-h3"><?php esc_html_e('Responsive Settings', 'uap');?></h3>
				<div class="inside">
					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Screen Max-Width:', 'uap');?> 479px</span>
						<div class="uap-general-options-link-pages"><select name="uap_listing_users_responsive_small"><?php
							$arr = array( '1' => 1 . esc_html__(' Columns', 'uap'),
										  '2' => 2 . esc_html__(' Columns', 'uap'),
										  '3' => 3 . esc_html__(' Columns', 'uap'),
										  '4' => 4 . esc_html__(' Columns', 'uap'),
									 	  '5' => 5 . esc_html__(' Columns', 'uap'),
									 	  '6' => 6 . esc_html__(' Columns', 'uap'),
										  '0' => esc_html__('Auto', 'uap'),
							);
							foreach ($arr as $k=>$v){
								$selected = ($data['metas']['uap_listing_users_responsive_small']==$k) ? 'selected' : '';
								?>
									<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
								<?php
							}
						?>
						</select></div>
					</div>
					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Screen Min-Width:', 'uap');?> 480px <?php esc_html_e(" and Screen Max-Width:");?> 767px</span>
						<div class="uap-general-options-link-pages"><select name="uap_listing_users_responsive_medium"><?php
							foreach ($arr as $k=>$v){
								$selected = ($data['metas']['uap_listing_users_responsive_medium']==$k) ? 'selected' : '';
								?>
									<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
								<?php
							}
						?>
						</select></div>
					</div>
					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Screen Min-Width:', 'uap');?> 768px <?php esc_html_e(" and Screen Max-Width:");?> 959px</span>
						<div class="uap-general-options-link-pages"><select name="uap_listing_users_responsive_large"><?php
							foreach ($arr as $k=>$v){
								$selected = ($data['metas']['uap_listing_users_responsive_large']==$k) ? 'selected' : '';
								?>
									<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
								<?php
							}
						?>
						</select></div>
					</div>
					<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
		            	<input type="submit" value="<?php esc_html_e('Save changes', 'uap');?>" name="save" class="button button-primary button-large">
		            </div>
				</div>
			</div>

			<div class="uap-stuffbox uap-custom-css-box-wrapper">
				<h3 class="uap-h3"><?php esc_html_e('Custom CSS', 'uap');?></h3>
					<div class="uap-form-line">
						<textarea id="uap_listing_users_custom_css" name="uap_listing_users_custom_css" class="uap-custom-css-box"><?php echo stripslashes($data['metas']['uap_listing_users_custom_css']);?></textarea>

					<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
		            	<input type="submit" value="<?php esc_html_e('Save changes', 'uap');?>" name="save" class="button button-primary button-large">
		            </div>
				</div>
			</div>
		</form>
	</div>

<div class="uap-wrapper">

		<form  method="post">
			<div class="uap-stuffbox">
				<h3 class="uap-h3"><?php esc_html_e('Product Links', 'uap');?></h3>
				<div class="inside">
				<div class="row">
					<div class="col-xs-6">
					<div class="uap-form-line">
						<h2><?php esc_html_e('Activate/Hold Product Links', 'uap');?></h2>
                        <p><?php esc_html_e('Affiliates can easily search for products and analyze them for generating custom affiliate links. The module is ready and compatible with WooCommerce, Ultimate Learning Pro and Easy Digital Downloads.', 'uap');?></p>
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_product_links_enabled']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_product_links_enabled');" <?php echo esc_attr($checked);?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_product_links_enabled" value="<?php echo esc_attr($data['metas']['uap_product_links_enabled']);?>" id="uap_product_links_enabled" />

                        <p><?php esc_html_e('Once the Module is enabled an extra sub-tab will be available on Affiliate Portal under "Marketing" main tab.', 'uap');?></p>
                       </div>
              </div>
						</div>

					<div class="row">
					<div class="uap-form-line">
					<div class="col-xs-4">

						<h2><?php esc_html_e('Show Reward calculation', 'uap');?></h2>
                        <p><?php esc_html_e('Besides the Product price, affiliates may see how actually the receives if the product is promoted. The system is able to take in consideration any available Offers for each product if a such value is set', 'uap');?></p>
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_product_links_reward_calculation']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_product_links_reward_calculation');" <?php echo esc_attr($checked);?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_product_links_reward_calculation" value="<?php echo esc_attr($data['metas']['uap_product_links_reward_calculation']);?>" id="uap_product_links_reward_calculation" />
         </div>
         </div>
					</div>

					<div class="row">
					<div class="uap-form-line">
					<div class="col-xs-4">
						<h4><?php esc_html_e( 'Products Source', 'uap' );?></h4>
						<?php $services = uap_get_active_services();?>
						<?php if ( isset( $services['ump'] ) ){
							 unset( $services['ump'] );
						}?>
						<select name="uap_product_links_source" >
								<option value="">...</option>
								<?php foreach ( $services as $serviceSlug => $serviceName ):?>
										<option value="<?php echo esc_attr($serviceSlug);?>" <?php echo ( $serviceSlug == $data['metas']['uap_product_links_source'] ) ? 'selected' : '';?> ><?php echo esc_html($serviceName);?></option>
								<?php endforeach;?>
						</select>
          </div>
          </div>
						</div>

			<div>
            <p><?php esc_html_e('You may place the showcase into any other place than is by default by copying the available shortcode.', 'uap');?></p>
          <div class="uap-admin-shortcode-wrap">[uap-product-links]</div>
			</div>
					<div id="uap_save_changes" class="uap-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
					</div>

				</div>
			</div>
		</form>
</div>

<?php

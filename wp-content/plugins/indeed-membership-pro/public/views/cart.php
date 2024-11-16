<div id="ihc_cart_wrapper">
	<?php if (!empty($data['show_full_cart'] )):?>
	<div class="iump-level-details-register <?php echo esc_attr($data['template']);?>">
		<div class="ihc-order-title"><?php esc_html_e('Payment details', 'ihc');?></div>



		<?php if ($trialObject->isTrialActive()):?>
				<div>
						<span class="iump-level-details-register-name"><?php echo esc_html__('Trial Subscription ', 'ihc');?>(<?php echo esc_html($trialObject->getDurationValue()) . ' ' . $trialObject->getDurationType(false);?>)</span>
						<span class="iump-level-details-register-price"><?php echo esc_html($trialObject->getInitialTrialPrice(false));?></span>
						<div class="iump-clear"></div>
				</div>
				<!-- DISCOUNT VALUE --->
				<?php if (isset($data['trial_discount_value'])):?>
						<div class="iump-discount-wrapper">
								<span class="iump-level-details-register-name"><?php echo esc_html__('Trial Discount: ', 'ihc');?></span>
								<span class="iump-level-details-register-price"><?php echo esc_html($data['trial_discount_value']);?></span>
						</div>
				<?php endif;?>
				<div class="iump-clear"></div>
				<?php if (isset($data['total_taxes']) && !empty($trialObject->getTaxes())):?>
					<div id="ihc_taxes_wrapper" class="iump-tax-wrapper">
						<?php if (!empty($trialObject->getTaxes()['items'] )): ?>
							<div class="ihc-taxes-content">
								<?php foreach ($trialObject->getTaxes()['items'] as $k=>$v):?>
									<div class="iump-level-subdetails-register-name"><?php echo esc_html($v['label']); ?></div>
									<div class="iump-level-subdetails-register-price"><?php echo esc_html($v['print_value']);?></div>
		                            <div class="iump-clear"></div>
								<?php endforeach;?>
								<div class="iump-clear"></div>

							</div>
						<?php endif;?>
						<span class="iump-level-details-register-name"><?php echo esc_html__('Total Trial Taxes:', 'ihc');?></span>
						<span class="iump-level-details-register-price">+<?php echo esc_html($trialObject->getTaxes()['print_total']);?></span>
						<div class="iump-clear"></div>


					</div>
				<?php endif;?>

				<div class="iump-totalprice-wrapper">
						<span class="iump-level-details-register-name"><?php echo esc_html__('Full Trial price: ', 'ihc');?></span>
						<span class="iump-level-details-register-price"><?php echo esc_html($trialObject->getTrialPrice(false));?></span>
						<div class="iump-clear"></div>
				</div>

                    <!-- LEVEL PRICE -->
                <span class="iump-level-details-register-name"><?php echo sprintf( esc_html__( '%s' ), $data['level_label'] );?></span>
                <span class="iump-level-details-register-price"><?php echo esc_html($data['level_price']);?></span>
                <div class="iump-clear"></div>

                <!-- DISCOUNT VALUE --->
                <?php if (isset($data['discount_value'])):?>
                    <div class="iump-discount-wrapper">
                        <span class="iump-level-details-register-name"><?php echo esc_html__('Discount: ', 'ihc');?></span>
                        <span class="iump-level-details-register-price"><?php echo esc_html($data['discount_value']);?></span>
                    </div>
                <?php endif;?>
                <div class="iump-clear"></div>

                <!-- TAXES VALUE -->
                <?php if (isset($data['total_taxes']) && !empty($data['print_taxes'])):?>
                    <div id="ihc_taxes_wrapper" class="iump-tax-wrapper" >
                        <?php if (!empty($data['taxes_details'])):?>
                            <div class="ihc-taxes-content">
                                <?php foreach ($data['taxes_details'] as $k=>$v):?>
                                    <div class="iump-level-subdetails-register-name aa"><?php echo esc_html($v['label']); ?></div>
                                    <div class="iump-level-subdetails-register-price"><?php echo esc_html($v['print_value']);?></div>
                                    <div class="iump-clear"></div>
                                <?php endforeach;?>
                                <div class="iump-clear"></div>

                            </div>
                        <?php endif;?>
                        <span class="iump-level-details-register-name"><?php echo esc_html__('Total Taxes:', 'ihc');?></span>
                        <span class="iump-level-details-register-price">+<?php echo esc_html($data['total_taxes']);?></span>
                        <div class="iump-clear"></div>
                    </div>
                <?php endif;?>
                <div class="iump-clear"></div>


				<?php if (isset($data['final_price'])):?>
					  <div class="iump-totalprice-wrapper">
								<span class="iump-level-details-register-name"><?php echo esc_html__('Price after trial period: ', 'ihc');?></span>
								<span class="iump-level-details-register-price"><?php echo esc_html($data['final_price']);?></span>
								<div class="iump-clear"></div>
					  </div>
				<?php endif;?>
		<?php else :?>

        	                    <!-- LEVEL PRICE -->
                <span class="iump-level-details-register-name"><?php echo sprintf( esc_html__( '%s' ), esc_html($data['level_label']) );?></span>
                <span class="iump-level-details-register-price"><?php echo esc_html($data['level_price']);?></span>
                <div class="iump-clear"></div>

                <!-- DISCOUNT VALUE --->
                <?php if (isset($data['discount_value'])):?>
                    <div class="iump-discount-wrapper">
                        <span class="iump-level-details-register-name"><?php echo esc_html__('Discount: ', 'ihc');?></span>
                        <span class="iump-level-details-register-price"><?php echo esc_html($data['discount_value']);?></span>
                    </div>
                <?php endif;?>
                <div class="iump-clear"></div>

                <!-- TAXES VALUE -->
                <?php if (isset($data['total_taxes']) && !empty($data['print_taxes'])):?>
                    <div id="ihc_taxes_wrapper" class="iump-tax-wrapper" >
                        <?php if (!empty($data['taxes_details'])):?>
                            <div class="ihc-taxes-content">
                                <?php foreach ($data['taxes_details'] as $k=>$v):?>
                                    <div class="iump-level-subdetails-register-name"><?php echo esc_html($v['label']); ?></div>
                                    <div class="iump-level-subdetails-register-price"><?php echo esc_html($v['print_value']);?></div>
                                    <div class="iump-clear"></div>
                                <?php endforeach;?>
                                <div class="iump-clear"></div>

                            </div>
                        <?php endif;?>
                        <span class="iump-level-details-register-name"><?php echo esc_html__('Total Taxes:', 'ihc');?></span>
                        <span class="iump-level-details-register-price">+<?php echo esc_html($data['total_taxes']);?></span>
                        <div class="iump-clear"></div>
                    </div>
                <?php endif;?>
                <div class="iump-clear"></div>

			<!-- FINAL PRICE -->
			<?php if (isset($data['final_price'])):?>
			  <div class="iump-totalprice-wrapper">
						<span class="iump-level-details-register-name"><?php echo esc_html__('Final Price: ', 'ihc');?></span>
						<span class="iump-level-details-register-price"><?php echo esc_html($data['final_price']);?></span>
						<div class="iump-clear"></div>
			  </div>
			<?php endif;?>
			<div class="iump-clear"></div>
		<?php endif;?>



	</div>
	<?php endif;?>
<input type="hidden" id="iumpfinalglobalp" value="<?php echo (isset($data['price_number'])) ? esc_attr($data['price_number']) : '';?>" />
<input type="hidden" id="iumpfinalglobalc" value="<?php echo (isset($currency)) ? esc_attr($currency) : '';?>" />
<input type="hidden" id="iumpfinalglobal_ll" value="<?php echo (isset($data['level_label'])) ? sprintf( esc_html__( '%s' ), esc_attr($data['level_label']) ) : '';?>" />
<input type="hidden" id="iump_site_name" value="<?php echo esc_attr(get_option('blogname'));?>" />
</div>

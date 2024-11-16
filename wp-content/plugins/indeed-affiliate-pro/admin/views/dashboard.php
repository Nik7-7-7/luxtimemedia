
<div class="uap-dashboard-wrapper-section">
 <div class="uap-dashboard-top-section">
	 <div class="uap-hs__left">
		 <img src="<?php echo esc_url(UAP_URL . 'assets/images/wordpress-affiliate-dashboard.png');?>" alt="Ultimate Affiliate Pro">
	 </div>
	 <div class="uap-hs__right">
					<h2><?php esc_html_e('Welcome &', 'uap');?> <strong><?php esc_html_e('Thank You', 'uap');?></strong> <?php esc_html_e('for Choosing Us!', 'uap');?></h2>
					<p><?php esc_html_e('Build & manage an affiliate program with one easy-to-use & most-favored affiliate plugin for WordPress and WooCommerce', 'uap');?></p>
					<?php if((int)get_option( 'uap_wizard_complete', -1 ) !== 1){ ?>
						<a class="uap-first-button uap-big-button uap-text-center"  href="<?php echo esc_url($data['base_url'] . '&tab=wizard');?>"><?php esc_html_e('Getting Started With Setup', 'uap');?>
						<img src="<?php echo esc_url(UAP_URL . 'assets/images/right-arrow-icon.png');?>" alt="Ultimate Affiliate Pro"/></a>
					<?php } ?>
				</div>
 </div>
 <div class="uap-dashboard-links-section">
	 <h2><?php esc_html_e('What to do', 'uap');?> <strong><?php esc_html_e('Next', 'uap');?></strong>?</h2>
            <a href="<?php echo esc_url($data['base_url'] . '&tab=affiliates');?>"  target="_blank" class="uap-dashboard-page-link"><i class="fa-uap fa-affiliates-uap" aria-hidden="true"></i><?php esc_html_e('Manage Affiliates', 'uap');?></a>
            <a href="https://ultimateaffiliate.pro/docs" target="_blank" class="uap-dashboard-page-link"><i class="fa-uap fa-book" aria-hidden="true"></i><?php esc_html_e('Check our Documentation', 'uap');?></a>
            <a href="https://ultimateaffiliate.pro/videos" target="_blank" class="uap-dashboard-page-link"><i class="fa-uap fa-video" aria-hidden="true"></i><?php esc_html_e('See our video Tutorials', 'uap');?></a>
            <a href="https://ultimateaffiliate.pro/pro-addons/" target="_blank" class="uap-dashboard-page-link"><i class="fa-uap fa-cart-plus" aria-hidden="true"></i><?php esc_html_e('Explore Pro Addons', 'uap');?></a>
  </div>
	<div class="uap-dashboard-bundle-section">
		<div class="uap-dashboard-bundle-wrapp">
		<div class="uap-dashboard-bundle-details">
			<h2>Ultimate Affiliate Pro <span><?php esc_html_e('Pro Add-ons Bundle', 'uap');?></span> <?php esc_html_e('Pack', 'uap');?></h2>
			<p><?php esc_html_e('Unlock all Ultimate Affiliate Pro add-ons offering access to every extension in a single purchase. Save significantly compared to buying add-ons individually and enhance your Ultimate Affiliate Pro experience', 'uap');?></p>
			<div class="uap-dashboard-bundle-headline">28+ <span>Premium Addons</span> Included</div>
				<a class="uap-first-button uap-big-button uap-text-center"  href="https://ultimateaffiliate.pro/get-bundle-pack/"><?php esc_html_e('Get it Now', 'uap');?>
				<img src="<?php echo esc_url(UAP_URL . 'assets/images/right-arrow-icon.png');?>" alt="Ultimate Affiliate Pro"/></a>
		</div>
		<div class="uap-dashboard-bundle-price-details">
			<div class="uap-price-box">
				<h2>65%</h2>
				<p>Discount</p>
			</div>
		</div>
	</div>
	</div>
	<div class="uap-dashboard-features-section">
		<h2><?php esc_html_e('At last, an effortless yet robust', 'uap');?> <strong><?php esc_html_e('WordPress Affiliate Plugin', 'uap');?></strong></h2>
		<p><?php esc_html_e('Explore the remarkable features that make Ultimate Affiliate Pro the unparalleled choice for powerful and user-friendly affiliate tracking software in the market', 'uap');?></p>
		<div class="uap-features-row">
			<div class="uap-features-col">
				<div class="uap-features-icon"><i class="fa-uap fa-mlm-account-uap"></i></div>
				<div class="uap-features-title"><?php esc_html_e('Affiliate MLM Network', 'uap');?></div>
			</div>
			<div class="uap-features-col">
				<div class="uap-features-icon"><i class="fa-uap fa-simple_links-uap"></i></div>
				<div class="uap-features-title"><?php esc_html_e('Smart Sales Tracking', 'uap');?></div>
			</div>
			<div class="uap-features-col">
				<div class="uap-features-icon"><i class="fa-uap fa-reports-uap"></i></div>
				<div class="uap-features-title"><?php esc_html_e('Detailed Affiliate Reports', 'uap');?></div>
			</div>
			<div class="uap-features-col">
				<div class="uap-features-icon"><i class="fa-uap fa-payments-uap"></i></div>
				<div class="uap-features-title"><?php esc_html_e('Integrated Online Payouts', 'uap');?></div>
			</div>
		</div>
		<div class="uap-features-row">
			<div class="uap-features-col">
				<div class="uap-features-icon"><i class="fa-uap fa-offers-uap"></i></div>
				<div class="uap-features-title"><?php esc_html_e('Powerful Commission Rules', 'uap');?></div>
			</div>
			<div class="uap-features-col">
				<div class="uap-features-icon"><i class="fa-uap fa-showcases-uap"></i></div>
				<div class="uap-features-title"><?php esc_html_e('Affiliate Portal', 'uap');?></div>
			</div>
			<div class="uap-features-col">
				<div class="uap-features-icon"><i class="fa-uap fa-custom_affiliate_slug-uap"></i></div>
				<div class="uap-features-title"><?php esc_html_e('Fraud Detection And Prevention', 'uap');?></div>
			</div>
			<div class="uap-features-col">
				<div class="uap-features-icon"><i class="fa-uap fa-source_details-uap"></i></div>
				<div class="uap-features-title"><?php esc_html_e('Commission Management', 'uap');?></div>
			</div>
		</div>
	</div>
	<div class="uap-dashboard-rating-section">
		<div class="uap-dashboard-rating-wrapp">
			<div class="uap-dashboard-rating-icons">
				<i class="fa-uap fa-star"></i><i class="fa-uap fa-star"></i><i class="fa-uap fa-star"></i><i class="fa-uap fa-star"></i><i class="fa-uap fa-star"></i>
			</div>
			<div class="uap-dashboard-rating-details">
				<h2><?php esc_html_e('Rate your experience with', 'uap');?> <strong>Ultimate Affiliate Pro</strong></h2>
				<p><?php esc_html_e('Help other users make the best decision and promote their websites with a premium Affiliate program.', 'uap');?></p>
				<a class="uap-first-button uap-big-button uap-text-center"  href="https://ultimateaffiliate.pro/rating/" target="_blank"><?php esc_html_e('Rate the Plugin', 'uap');?>
				<img src="<?php echo esc_url(UAP_URL . 'assets/images/right-arrow-icon.png');?>" alt="Ultimate Affiliate Pro"/></a>
			</div>
		</div>
	</div>
</div>
<!--div class="uap-dashboard-wrapper">
	<div class="uap-dashboard-title">
		Ultimate Affiliate Pro -
		<span class="second-text">
			<?php //esc_html_e('Dashboard Overall', 'uap');?>
		</span>
	</div>
	<div class="row">
	<div class="col-xs-3">
		<div class="uap-dashboard-top-box">
			<i class="fa-uap fa-dashboard-visits-uap"></i>
			<div class="stats">
				<h4><?php //echo esc_html($data['stats']['affiliates']);?></h4>
				<?php //esc_html_e('Total Registered Affiliates', 'uap');?>
			</div>
		</div>
	</div>

	<div class="col-xs-3">
		<div class="uap-dashboard-top-box">
			<i class="fa-uap fa-dashboard-referrals-uap"></i>
			<div class="stats">
				<h4><?php //echo esc_html($data['stats']['referrals']);?></h4>
				<?php //esc_html_e('Total Generated Referrals', 'uap');?>
			</div>
		</div>
	</div>

	<div class="col-xs-3">
		<div class="uap-dashboard-top-box">
			<i class="fa-uap fa-dashboard-payments-unpaid-uap"></i>
			<div class="stats">
				<h4><?php //echo uap_format_price_and_currency($data['currency'], round($data['stats']['unpaid_payments_value'], 2));?></h4>
				<?php //esc_html_e('Total Unpaid Referrals', 'uap');?>
			</div>
		</div>
	</div>

	<div class="col-xs-3">
		<div class="uap-dashboard-top-box">
			<i class="fa-uap fa-dashboard-rank-uap"></i>
			<div class="stats">
				<h4><?php //echo esc_html($data['stats']['top_rank']);?></h4>
				<?php //esc_html_e('Most Assigned Rank', 'uap');?>
			</div>
		</div>
 	</div>
  </div>



<div class="row">
   <div class="col-xs-8">
	<div class="uap-box-content-dashboard" >
	 <div class="uap-dashboard-box-padded">
		<h4><?php //esc_html_e('Total Affiliates per Rank', 'uap');?></h4>
		<?php //if (!empty($data['rank_arr'])):?>
			<div id="uap_chart_1" class='uap-flot'></div>
		<?php //endif;?>
	 </div>
	</div>

	<?php //if (!empty($data['last_referrals'])):?>
	<div class="uap-box-content-dashboard uap-last-five uap-dashboard-box-padded">
		<div class="info-title"><i class="fa-uap fa-list-uap"></i><?php //esc_html_e('Last Five Referrals received', 'uap');?></div>
		<?php //foreach ($data['last_referrals'] as $array):?>
			<div class="uap-dashboard-las-reff">
				<i class="fa-uap fa-icon-pop-list-uap"></i>
				<span><?php //echo esc_html('  ') . uap_format_price_and_currency($array['currency'], $array['amount']) . esc_html__(' for ', 'uap') .  '<strong>'.$array['affiliate_username'] .'</strong><br/>'. esc_html__(' on ', 'uap') . uap_convert_date_to_us_format($array['date']); ?></span>
			</div>
		<?php //endforeach;?>
	</div>
	<?php //endif;?>
   </div>

   <div class="col-xs-4">
		<?php //if (!empty($data['top_affiliates'])) : ?>
			<div class="uap-box-right-dashboard">
			<div class="uap-dashboard-top-affiliate">
					<span class="uap-big-cunt">10</span>
					<span><?php //esc_html_e('Top', 'uap');?><br/><?php //esc_html_e('Affiliates', 'uap');?></span>
				</div>
				<?php //$i = 1;?>
				<?php //foreach ($data['top_affiliates'] as $key=>$value): ?>
					<div class="uap-dashboard-top-affiliate-single">
					 <div class="uap-top-name"><?php //echo esc_uap_content('<span>' . $i . '</span> ' . $value['name'] . ' (' . $key . ')');?> </div>
					 <div class="uap-top-count"><?php //esc_html_e('Referrals', 'uap');?> <?php //echo esc_html($value['referrals']);?> | <?php //esc_html_e('Total Amount', 'uap');?> <?php //echo uap_format_price_and_currency($data['currency'], $value['sum']);?> </div>
					</div>
					<?php //$i++;?>
				<?php //endforeach;?>
			</div>
		<?php //endif;?>
   </div>
</div>
</div-->
<?php /* if ( $data['rank_arr'] ):?>
		<?php foreach ( $data['rank_arr'] as $key => $value ):?>
				<span class="uap-js-dashboard-rank-data" data-label="<?php echo esc_attr($key);?>" data-value="<?php echo esc_attr($value);?>"></span>
		<?php endforeach;?>
<?php endif;  */?>

<?php

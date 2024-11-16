<?php
$currency = get_option('ihc_currency');
echo ihc_inside_dashboard_error_license();
echo iump_is_wizard_uncompleted_but_not_skiped();
do_action( "ihc_admin_dashboard_after_top_menu" );
$ordersObject = new \Indeed\Ihc\Db\Orders();
$currency = get_option( 'ihc_currency' );

do_action( "ihc_action_admin_dashboard", null );
?>
<div class="ump-dashboard-wrapper-section">


	 <div class="ump-dashboard-top-section">
		 <div class="ump-hs__left">
			 <img src="<?php echo esc_url(IHC_URL . 'admin/assets/images/ump-dashboard-image.png');?>" alt="Ultimate Membership Pro">
		 </div>
		 <div class="ump-hs__right">
						<h2><?php esc_html_e('Welcome &', 'ihc');?> <strong><?php esc_html_e('Thank You', 'ihc');?></strong> <?php esc_html_e('for Choosing Us!', 'ihc');?></h2>
						<p><?php esc_html_e('All-In-One WordPress Membership Plugin with endless membership features to manage member subscriptions for valuable content with one-time or recurring Payments', 'ihc');?></p>
						<?php if((int)get_option( 'iump_wizard_complete', -1 ) !== 1){ ?>
							<a class="iump-first-button ump-big-button ihc-text-center"  href="<?php echo admin_url('admin.php?page=ihc_manage&tab=wizard');?>"><?php esc_html_e('Getting Started With Setup', 'ihc');?>
							<img src="<?php echo esc_url(IHC_URL . 'admin/assets/images/right-arrow-icon.png');?>" alt="Ultimate Membership Pro"/></a>
						<?php } ?>
					</div>
	 </div>
	 <div class="ump-dashboard-links-section">
		 <h2><?php esc_html_e('What to do', 'ihc');?> <strong><?php esc_html_e('Next', 'ihc');?></strong>?</h2>
	            <a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=users');?>"  target="_blank" class="ump-dashboard-page-link"><i class="fa-ihc fa-users-ihc" aria-hidden="true"></i><?php esc_html_e('Manage Members', 'ihc');?></a>
	            <a href="https://ultimatemembershippro.com/docs/" target="_blank" class="ump-dashboard-page-link"><i class="fa-ihc fa-book" aria-hidden="true"></i><?php esc_html_e('Check our Documentation', 'ihc');?></a>
	            <a href="https://ultimatemembershippro.com/videos/" target="_blank" class="ump-dashboard-page-link"><i class="fa-ihc fa-video" aria-hidden="true"></i><?php esc_html_e('See our video Tutorials', 'ihc');?></a>
	            <a href="https://ultimatemembershippro.com/pro-addons/" target="_blank" class="ump-dashboard-page-link"><i class="fa-ihc fa-cart-plus" aria-hidden="true"></i><?php esc_html_e('Explore Pro Addons', 'ihc');?></a>
	  </div>
		<div class="ump-dashboard-bundle-section">
			<div class="ump-dashboard-bundle-wrapp">
			<div class="ump-dashboard-bundle-details">
				<h2>Ultimate Membership Pro <span><?php esc_html_e('Pro Add-ons Bundle', 'ihc');?></span> <?php esc_html_e('Pack', 'ihc');?></h2>
				<p><?php esc_html_e('Gain access to all Ultimate Membership Pro add-ons with our Bundle Pack, providing a comprehensive suite of extensions in one convenient purchase. Save big compared to purchasing add-ons individually and enhance your Ultimate Membership Pro experience.', 'ihc');?></p>
				<div class="ump-dashboard-bundle-headline">47+ <span>Premium Addons</span> Included</div>
					<a class="iump-first-button ump-big-button ihc-text-center"  href="https://ultimatemembershippro.com/get-bundle-pack/"><?php esc_html_e('Get it Now', 'ihc');?>
					<img src="<?php echo esc_url(IHC_URL . 'admin/assets/images/right-arrow-icon.png');?>" alt="Ultimate Membership Pro"/></a>
			</div>
			<div class="ump-dashboard-bundle-price-details">
				<div class="ump-price-box">
					<h2>70%</h2>
					<p>Discount</p>
				</div>
			</div>
		</div>
		</div>
		<div class="ump-dashboard-features-section">
			<h2><?php esc_html_e('Finally, a', 'ihc');?> <strong><?php esc_html_e('WordPress Membership Plugin', 'ihc');?></strong> <?php esc_html_e('that is both Easy to Use and Powerful', 'ihc');?> </h2>
			<p><?php esc_html_e('The ultimate solution for creating and managing a premium membership program. Unlock exclusive content, manage subscriptions effortlessly, and empower user experience.', 'ihc');?></p>
			<div class="ump-features-row">
				<div class="ump-features-col">
					<div class="ump-features-icon"><i class="fa-ihc fa-locker-ihc"></i></div>
					<div class="ump-features-title"><?php esc_html_e('Restrict Access to Everything', 'ihc');?></div>
				</div>
				<div class="ump-features-col">
					<div class="ump-features-icon"><i class="fa-ihc fa-user_reports-ihc"></i></div>
					<div class="ump-features-title"><?php esc_html_e('Unlimited Membership Levels', 'ihc');?></div>
				</div>
				<div class="ump-features-col">
					<div class="ump-features-icon"><i class="fa-ihc fa-coupons-ihc"></i></div>
					<div class="ump-features-title"><?php esc_html_e('Discount Codes', 'ihc');?></div>
				</div>
				<div class="ump-features-col">
					<div class="ump-features-icon"><i class="fa-ihc fa-payments-ihc"></i></div>
					<div class="ump-features-title"><?php esc_html_e('Integrated Payment Services', 'ihc');?></div>
				</div>
			</div>
			<div class="ump-features-row">
				<div class="ump-features-col">
					<div class="ump-features-icon"><i class="fa-ihc fa-notifications-ihc"></i></div>
					<div class="ump-features-title"><?php esc_html_e('Email Notifications', 'ihc');?></div>
				</div>
				<div class="ump-features-col">
					<div class="ump-features-icon"><i class="fa-ihc fa-account_page_menu-ihc"></i></div>
					<div class="ump-features-title"><?php esc_html_e('Membership Portal', 'ihc');?></div>
				</div>
				<div class="ump-features-col">
					<div class="ump-features-icon"><i class="fa-ihc fa-import_users-ihc"></i></div>
					<div class="ump-features-title"><?php esc_html_e('Members Management', 'ihc');?></div>
				</div>
				<div class="ump-features-col">
					<div class="ump-features-icon"><i class="fa-ihc fa-prorate_subscription_settings-ihc"></i></div>
					<div class="ump-features-title"><?php esc_html_e('Recurring Payments with Subscriptions', 'ihc');?></div>
				</div>
			</div>
		</div>
		<div class="ump-dashboard-rating-section">
			<div class="ump-dashboard-rating-wrapp">
				<div class="ump-dashboard-rating-icons">
					<i class="fa-ihc fa-star"></i><i class="fa-ihc fa-star"></i><i class="fa-ihc fa-star"></i><i class="fa-ihc fa-star"></i><i class="fa-ihc fa-star"></i>
				</div>
				<div class="ump-dashboard-rating-details">
					<h2><?php esc_html_e('Rate your experience with', 'ihc');?> <strong>Ultimate Membership Pro</strong></h2>
					<p><?php esc_html_e('Assist fellow users in making informed decisions and improve their websites with a premium Membership program.', 'ihc');?></p>
					<a class="iump-first-button ump-big-button ihc-text-center"  href="https://ultimatemembershippro.com/rating/" target="_blank"><?php esc_html_e('Rate the Plugin', 'ihc');?>
					<img src="<?php echo esc_url(IHC_URL . 'admin/assets/images/right-arrow-icon.png');?>" alt="Ultimate Membership Pro"/></a>
				</div>
			</div>
		</div>

<div class="ihc-dashboard-wrapper">

	<div class="ihc-dashboard-row-title"><?php esc_html_e('Last 30 days', 'ihc');?></div>
	<div class="row-fluid">

		<div class="span4">
			<div class="ihc-dashboard-box-wrapper">
				<div class="ihc-dashboard-box-top-section">
					<i class="fa-ihc fa-ihc-dashboard fa-users-ihc"></i>
					<div class="ihc-dashboard-stats">
						<?php
								$percentage = false;
								$start = time() - 30*24*60*60;// 30days
								$end = time();
								$lastThirty = \Indeed\Ihc\Db\Users::countInInterval( $start, $end );

								$start = time() - 60*24*60*60;// 60days
								$end = time() - 30*24*60*60;// 30days
								$beforeLastThirty = \Indeed\Ihc\Db\Users::countInInterval( $start, $end );

								if ( $beforeLastThirty > 0 ){
										$percentage = $beforeLastThirty / 100;
										$percentage = $lastThirty / $percentage;
										$percentage = round( $percentage, 1 );
										$percentage = $percentage - 100;
								}
						?>
						<div class="ihc-dashboard-stats-title"><?php esc_html_e('Members', 'ihc');?></div>
						<span class="ihc-dashboard-stats-count"><?php echo esc_html($lastThirty);?></span>
							<?php if ( $percentage !== false ):?>
								<?php $extraClass = $percentage > -0.01 ? 'ihc-dashboard-stats-trendup' : 'ihc-dashboard-stats-trenddown';?>
								<span class="ihc-dashboard-stats-trend <?php echo esc_attr($extraClass);?>">
									<i class="fa-ihc fa-arrow-ihc"></i>
									<?php echo esc_html($percentage);?>
									<span>%</span>
								</span>
							<?php endif;?>
					</div>
				</div>
				<div class="ihc-dashboard-box-bottom-section">
					<a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=users' );?>"><?php esc_html_e('View all', 'ihc');?></a>
				</div>
			</div>
		</div>

		<div class="span4">
			<div class="ihc-dashboard-box-wrapper">
				<div class="ihc-dashboard-box-top-section">
					<i class="fa-ihc fa-ihc-dashboard fa-levels-ihc"></i>
					<div class="ihc-dashboard-stats">
						<?php
								$percentage = false;
								$start = time() - 30*24*60*60;// 30days
								$end = time();
								$lastThirty = \Indeed\Ihc\UserSubscriptions::countInInterval( $start, $end );

								$start = time() - 60*24*60*60;// 60days
								$end = time() - 30*24*60*60;// 30days
								$beforeLastThirty = \Indeed\Ihc\UserSubscriptions::countInInterval( $start, $end );

								if ( $beforeLastThirty > 0 ){
										$percentage = $beforeLastThirty / 100;
										$percentage = $lastThirty / $percentage;
										$percentage = round( $percentage, 1 );
										$percentage = $percentage - 100;
								}
						?>
						<div class="ihc-dashboard-stats-title"><?php esc_html_e('Memberships', 'ihc');?></div>
						<span class="ihc-dashboard-stats-count"><?php echo esc_html($lastThirty);?></span>

						<?php if ( $percentage !== false ):?>
							<?php $extraClass = $percentage > -0.01 ? 'ihc-dashboard-stats-trendup' : 'ihc-dashboard-stats-trenddown';?>
							<span class="ihc-dashboard-stats-trend <?php echo esc_attr($extraClass);?>">
								<i class="fa-ihc fa-arrow-ihc"></i>
								<?php echo esc_html($percentage);?>
								<span>%</span>
							</span>
						<?php endif;?>

					</div>
				</div>
				<div class="ihc-dashboard-box-bottom-section">
					<a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=users' );?>"><?php esc_html_e('View all', 'ihc');?></a>
				</div>
			</div>
		</div>

		<div class="span4">
			<div class="ihc-dashboard-box-wrapper">
				<div class="ihc-dashboard-box-top-section">
					<i class="fa-ihc fa-ihc-dashboard fa-payment_settings-ihc"></i>
					<div class="ihc-dashboard-stats">
						<?php
								$percentage = false;
								$start = time() - 30*24*60*60;// 30days
								$end = time();
								$lastThirty = $ordersObject->getTotalAmountInInterval( $start, $end );

								$start = time() - 60*24*60*60;// 60days
								$end = time() - 30*24*60*60;// 30days
								$beforeLastThirty = $ordersObject->getTotalAmountInInterval( $start, $end );
								if ( $beforeLastThirty > 0 ){
										$percentage = $beforeLastThirty / 100;
										$percentage = $lastThirty / $percentage;
										$percentage = round( $percentage, 1 );
										$percentage = $percentage - 100;
								}
						?>
						<div class="ihc-dashboard-stats-title"><?php esc_html_e('Earnings', 'ihc');?></div>
						<span class="ihc-dashboard-stats-count ihc-dashboard-small-stats"><?php echo ihc_format_price_and_currency( $currency, $lastThirty );?></span>
						<?php if ( $percentage !== false ):?>
							<?php $extraClass = $percentage > -0.01 ? 'ihc-dashboard-stats-trendup' : 'ihc-dashboard-stats-trenddown';?>
							<span class="ihc-dashboard-stats-trend <?php echo esc_attr($extraClass);?>">
								<i class="fa-ihc fa-arrow-ihc"></i>
								<?php echo esc_html($percentage);?>
								<span>%</span>
							</span>
						<?php endif;?>
					</div>
				</div>
				<div class="ihc-dashboard-box-bottom-section">
					<a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=orders' );?>"><?php esc_html_e('View all', 'ihc');?></a>
				</div>
			</div>
		</div>

	</div>

	<div class="ihc-dashboard-row-title"><?php esc_html_e('All-time', 'ihc');?></div>
	<div class="row-fluid">

		<div class="span3">
			<div class="ihc-dashboard-box-wrapper">
				<div class="ihc-dashboard-box-top-section">
					<i class="fa-ihc fa-ihc-dashboard fa-users-ihc"></i>
					<div class="ihc-dashboard-stats">
						<div class="ihc-dashboard-stats-title"><?php esc_html_e('Members', 'ihc');?></div>
						<span class="ihc-dashboard-stats-count"><?php echo \Indeed\Ihc\Db\Users::countAll();?></span>
					</div>
				</div>
				<div class="ihc-dashboard-box-bottom-section">
					<a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=users' );?>"><?php esc_html_e('View all', 'ihc');?></a>
				</div>
			</div>
		</div>

		<div class="span3">
			<div class="ihc-dashboard-box-wrapper">
				<div class="ihc-dashboard-box-top-section">
					<i class="fa-ihc fa-ihc-dashboard fa-levels-ihc"></i>
					<div class="ihc-dashboard-stats">
						<div class="ihc-dashboard-stats-title"><?php esc_html_e('Memberships', 'ihc');?></div>
						<span class="ihc-dashboard-stats-count"><?php echo \Indeed\Ihc\UserSubscriptions::getCount();?></span>
					</div>
				</div>
				<div class="ihc-dashboard-box-bottom-section">
					<a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=users' );?>"><?php esc_html_e('View all', 'ihc');?></a>
				</div>
			</div>
		</div>

		<div class="span3">
			<div class="ihc-dashboard-box-wrapper">
				<div class="ihc-dashboard-box-top-section">
					<i class="fa-ihc fa-ihc-dashboard fa-payment_settings-ihc"></i>
					<div class="ihc-dashboard-stats">
						<div class="ihc-dashboard-stats-title"><?php esc_html_e('Earnings', 'ihc');?></div>
						<span class="ihc-dashboard-stats-count  ihc-dashboard-small-stats"><?php
									echo ihc_format_price_and_currency( $currency, $ordersObject->getTotalAmount() );
							?></span>
					</div>
				</div>
				<div class="ihc-dashboard-box-bottom-section">
					<a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=orders' );?>"><?php esc_html_e('View all', 'ihc');?></a>
				</div>
			</div>
		</div>

		<div class="span3">
			<div class="ihc-dashboard-box-wrapper">
				<div class="ihc-dashboard-box-top-section">
					<i class="fa-ihc fa-ihc-dashboard fa-payments-ihc"></i>
					<div class="ihc-dashboard-stats">
						<div class="ihc-dashboard-stats-title"><?php esc_html_e('Orders', 'ihc');?></div>
						<span class="ihc-dashboard-stats-count"><?php	echo esc_html($ordersObject->getCountAll());?></span>
					</div>
				</div>
				<div class="ihc-dashboard-box-bottom-section">
					<a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=orders' );?>"><?php esc_html_e('View all', 'ihc');?></a>
				</div>
			</div>
		</div>

	</div>

	<div class="ihc-dashboard-row-title"><?php esc_html_e('Overall Earnings', 'ihc');?></div>
	<div class="row-fluid">
		<div class="span12">
			<div class="ihc-dashboard-earnings-graph-wrapper">
					<?php
							$timePassed = $ordersObject->getFirstOrderDaysPassed();

							$startTime = time() - $timePassed * 24 * 60 * 60;

							if ( $timePassed < 31 ){
									// days
									$earnings_arr = $ordersObject->getTotalAmountInLastTime( $startTime, 'days' );
							} else if ( $timePassed > 30 && $timePassed < 181 ){
									// weeks
									$earnings_arr = $ordersObject->getTotalAmountInLastTime( $startTime, 'weeks' );
							} else if ( $timePassed > 180 && $timePassed < 721 ){
									// months
									$earnings_arr = $ordersObject->getTotalAmountInLastTime( $startTime, 'months' );
							} else if ( $timePassed > 720 ){
									// years
									$earnings_arr = $ordersObject->getTotalAmountInLastTime( $startTime, 'years' );
							}
							if ( count( $earnings_arr ) > 18 ){
									$extraClass = 'flot-tick-label-rotate';
							} else {
									$extraClass =  '';
							}
					?>
					<div id="ihc-chart-earnings" class='ihc-flot <?php echo esc_attr($extraClass);?>'></div>

					<?php if ($earnings_arr):	?>
							<?php foreach ( $earnings_arr as $k => $v ):?>
									<?php
											$date = $v->the_time;
											$sum = $v->sum_value;
									?>
									<span class="ihc-js-dashboard-earnings-data" data-date="<?php echo esc_attr($date);?>" data-sum="<?php echo esc_attr($sum);?>"></span>
							<?php endforeach;?>
					<?php endif;?>

			</div>
		</div>
	</div>

</div>


</div>
<?php

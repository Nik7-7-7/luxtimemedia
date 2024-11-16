<div class="uap-wrapper uap-admin-reports-wrapper">
<div class="uap-page-title"><?php esc_html_e('Affiliate Program Reports', 'uap');?></div>

		<?php /*if (!empty($data['subtitle'])):?>
			<h4><?php echo esc_uap_content($data['subtitle']);?></h4>
		<?php endif;*/?>

<div class="uap-reports-filter-wrapp">
<form  method="post">

<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-12">
				<input type="text" value="<?php echo (isset($_POST['affiliate_name'])) ? $_POST['affiliate_name'] : ''?>" name="affiliate_name" placeholder="Search for Affiliate" class="uap-search-phrase-reports">
			<select name="search" class="uap-js-reports-search-period-select"><?php foreach ($data['select_values'] as $k=>$v):?>
				<?php $selected = ($data['selected']==$k) ? 'selected' : '';?>
				<option <?php echo esc_attr($selected);?> value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
			<?php endforeach;?>
		  </select>

			<?php $showCustomDate = $data['selected'] === 'custom' ? '' : 'uap-display-none';?>
			<input type="text" name="udf" value="<?php echo isset( $data['custom_start_date'] ) ? $data['custom_start_date'] : '';?>" class="uap-no-margin-right <?php echo $showCustomDate;?>" id="uap_admin_reports_search_start_date" placeholder="From - yyyy-mm-dd"/>
		  <span class="uap-date-line">-</span>
			<input type="text" name="udu" value="<?php echo isset( $data['custom_end_date'] ) ? $data['custom_end_date'] : '';?>" class="<?php echo $showCustomDate;?>" placeholder="To - yyyy-mm-dd" id="uap_admin_reports_search_end_date"/>

			<input type="submit" value="<?php esc_html_e('Filter', 'uap');?>" name="submit" class="button button-primary button-large uap-reports-filter-button" />

		</div>
		</div>
	</div>
		</form>
</div>
<div class="uap-stuffbox">
	<div class="inside">

		<?php if ( empty( $_REQUEST['affiliate_id'] ) && empty($_REQUEST['affiliate_name'])):?>
		<div class="row">
				<div class="col-xs-12">
					<h3><?php esc_html_e('Affiliates Stats', 'uap');?></h3>
				</div>
		</div>
		<div class="row">
		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-affiliates-uap"></i>
				<div class="stats">
					<h4><?php echo esc_html($data['reports']['affiliates']);?></h4>
					<?php esc_html_e('New Registered Affiliates', 'uap');?>
				</div>
			</div>
		</div>
		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-dashboard-visits-uap"></i>
				<div class="stats">
					<h4><?php echo esc_html($data['reports']['total_affiliates']);?></h4>
					<?php esc_html_e('Total Affiliates', 'uap');?>
				</div>
			</div>
		</div>
	</div>
	<?php endif;?>

		<div class="row">
				<div class="col-xs-12">
					<h3><?php esc_html_e('Referrals Stats', 'uap');?></h3>
				</div>
		</div>

		<div class="row">
		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-dashboard-referrals-uap"></i>
				<div class="stats">
					<h4><?php echo esc_html($data['reports']['referrals']);?></h4>
					<?php esc_html_e('Total Referrals', 'uap');?>
				</div>
			</div>
		</div>

		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-payments-uap"></i>
				<div class="stats">
					<h4><?php echo uap_format_price_and_currency($data['currency'], round($data['reports']['total_amount_referrals'], 2));?></h4>
					<?php esc_html_e('Total Amount Referrals', 'uap');?>
				</div>
			</div>
		</div>

		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-success-rate-uap"></i>
				<div class="stats">
					<h4><?php echo uap_format_price_and_currency($data['currency'], round($data['reports']['average_amount_referrals'], 2));?></h4>
					<?php esc_html_e('Average Referral Amount', 'uap');?>
				</div>
			</div>
		</div>


		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-success-rate-uap"></i>
				<div class="stats">
					<h4><?php echo $data['reports']['count_paid_referrals']."/".$data['reports']['count_unpaid_referrals'];?></h4>
					<?php esc_html_e('Paid/Unpaid Referrals', 'uap');?>
				</div>
			</div>
		</div>

		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-dashboard-payments-unpaid-uap"></i>
				<div class="stats">
					<h4><?php echo uap_format_price_and_currency($data['currency'], round($data['reports']['total_paid_referrals'], 2));?></h4>
					<?php esc_html_e('Paid Referrals Amount', 'uap');?>
				</div>
			</div>
		</div>

		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-dashboard-payments-unpaid-uap"></i>
				<div class="stats">
					<h4><?php echo uap_format_price_and_currency($data['currency'], round($data['reports']['total_unpaid_referrals'], 2));?></h4>
					<?php esc_html_e('Unpaid Referrals Amount', 'uap');?>
				</div>
			</div>
		</div>

	</div>

	<div class="row">
			<div class="col-xs-12">
				<h3><?php esc_html_e('Clicks Stats', 'uap');?></h3>
			</div>
	</div>
	<div class="row">
		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-visits-reports-uap"></i>
				<div class="stats">
					<h4><?php echo esc_html($data['reports']['visits']);?></h4>
					<?php esc_html_e('Total Clicks', 'uap');?>
				</div>
			</div>
		</div>

		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-success-number-uap"></i>
				<div class="stats">
					<h4><?php echo esc_html($data['reports']['conversions']);?></h4>
					<?php esc_html_e('Successful Conversions', 'uap');?>
				</div>
			</div>
		</div>

		<div class="col-xs-3">
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-success-rate-uap"></i>
				<div class="stats">
					<h4><?php echo esc_html($data['reports']['success_rate'] . '%');?></h4>
					<?php esc_html_e('Succesfully Rate', 'uap');?>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
			<div class="col-xs-12">
				<h3><?php esc_html_e('Payout Stats', 'uap');?></h3>
			</div>
	</div>
	<div class="row">
			<div class="col-xs-3">
				<div class="uap-dashboard-top-box">
					<i class="fa-uap fa-payments-uap"></i>
					<div class="stats">
						<h4><?php echo uap_format_price_and_currency($data['currency'], round($data['reports']['total_paid'], 2));?></h4>
						<?php esc_html_e('Total Paid Earnings', 'uap');?>
					</div>
				</div>
			</div>
			<div class="col-xs-3">
				<div class="uap-dashboard-top-box">
					<i class="fa-uap fa-dashboard-payments-unpaid-uap"></i>
					<div class="stats">
						<h4><?php echo esc_html($data['reports']['total_transactions']);?></h4>
						<?php esc_html_e('Total Transactions', 'uap');?>
					</div>
				</div>
			</div>
			<div class="col-xs-3">
				<div class="uap-dashboard-top-box">
					<i class="fa-uap fa-ranks-uap"></i>
					<div class="stats">
						<h4><?php echo esc_html($data['reports']['total_completed_transactions']);?></h4>
						<?php esc_html_e('Completed Transactions', 'uap');?>
					</div>
				</div>
			</div>
			<div class="col-xs-3">
				<div class="uap-dashboard-top-box">
					<i class="fa-uap fa-success-rate-uap"></i>
					<div class="stats">
						<h4><?php echo uap_format_price_and_currency($data['currency'], round($data['reports']['average_paid'], 2));?></h4>
						<?php esc_html_e('Average Transactions', 'uap');?>
					</div>
				</div>
			</div>
	</div>

	</div>
</div>

<?php

if (isset($data['visit_graph']) && count($data['visit_graph'])>0){

	/// VISIT GRAPH
	?>
		<div class="uap-stuffbox">
			<div class="inside">
				<div id="uap-plot-1" class="uap-plot"></div>
			</div>
		</div>
	<?php
	reset($data['visit_graph']);
	$first_key = key($data['visit_graph']);
	if (isset($data['visit_graph'][$first_key])){
		$start_time = strtotime($first_key);
	}

	end($data['visit_graph']);
	$last_key = key($data['visit_graph']);
	if (isset($data['visit_graph'][$last_key])){
		$end_time = strtotime($last_key);
	}
	reset($data['visit_graph']);
	?>
	<?php
		if (!empty($data['visit_graph']) && is_array($data['visit_graph'])):
			foreach ($data['visit_graph'] as $date=>$value):?>
				<span class="uap-js-visit-graph-data" data-date="<?php echo strtotime($date) . '000';?>" data-value="<?php echo esc_attr($value);?>" ></span>
			<?php endforeach;
		endif;

			if (!empty($data['visit_graph_success']) && is_array($data['visit_graph_success'])):
				if (count($data['visit_graph_success'])<2){
					if (empty($data['visit_graph_success'][$first_key])){
						$data['visit_graph_success'][$first_key] = 0;
					} else if (empty($data['visit_graph_success'][$last_key])){
						$data['visit_graph_success'][$last_key] = 0;
					}
				}
				foreach ($data['visit_graph_success'] as $date=>$value):?>
					<span class="uap-js-visit-graph-success-data" data-date="<?php echo strtotime($date) . '000';?>" data-value="<?php echo esc_attr($value);?>" ></span>
				<?php endforeach;
			endif;
	?>

	<span class="uap-js-visits-tick-size" data-value="<?php echo esc_attr($data['tick_size']);?>"></span>
	<span class="uap-js-visits-tick-type" data-value="<?php echo esc_attr($data['tick_type']);?>"></span>
	<span class="uap-js-visits-min-time" data-value="<?php echo esc_attr($start_time . '000'); ?>"></span>
	<span class="uap-js-visits-max-time" data-value="<?php echo esc_attr($end_time . '000');?>"></span>

	<?php
}


if (isset($data['referrals_graph']) && count($data['referrals_graph'])>0){
	//dd($data['referrals_graph']);
	/// REFERRALS GRAPH
	?>
		<div class="uap-stuffbox">
			<div class="inside">
				<div id="uap-plot-2" class="uap-plot"></div>
			</div>
		</div>
	<?php
	reset($data['referrals_graph']);
	$first_key = key($data['referrals_graph']);
	if (isset($data['referrals_graph'][$first_key])){
		$start_time = strtotime($first_key);
	}

	end($data['referrals_graph']);
	$last_key = key($data['referrals_graph']);
	if (isset($data['referrals_graph'][$last_key])){
		$end_time = strtotime($last_key);
	}
	reset($data['referrals_graph']);
	?>
	<?php
		if (!empty($data['referrals_graph']) && !empty($data['referrals_graph'])):
			foreach ($data['referrals_graph'] as $date=>$value):?>
				<span class="uap-js-referral-graph-data" data-date="<?php echo strtotime($date) . '000';?>" data-value="<?php echo esc_attr($value);?>" ></span>
			<?php
			endforeach;
		endif;
	?>
	<?php
	if (!empty($data['referrals_graph-refuse']) && is_array($data['referrals_graph-refuse'])):
		if (count($data['referrals_graph-refuse'])<2 ){
			if (empty($data['referrals_graph-refuse'][$first_key])){
				$data['referrals_graph-refuse'][$first_key] = 0;
			} else if (empty($data['referrals_graph-refuse'][$last_key])){
				$data['referrals_graph-refuse'][$last_key] = 0;
			}
		}
		foreach ($data['referrals_graph-refuse'] as $date=>$value):
			?>
				<span class="uap-js-referral-graph-refuse-data" data-date="<?php echo strtotime($date) . '000';?>" data-value="<?php echo esc_attr($value);?>" ></span>
			<?php
		endforeach;
	endif;
	?>
	<?php
		if (!empty($data['referrals_graph-unverified']) && is_array($data['referrals_graph-unverified'])):
			if (count($data['referrals_graph-unverified'])<2 ){
				if (empty($data['referrals_graph-unverified'][$first_key])){
					$data['referrals_graph-unverified'][$first_key] = 0;
				} else if (empty($data['referrals_graph-unverified'][$last_key])){
					$data['referrals_graph-unverified'][$last_key] = 0;
				}
			}
			foreach ($data['referrals_graph-unverified'] as $date=>$value):?>
				<span class="uap-js-referral-graph-unverified-data" data-date="<?php echo strtotime($date) . '000';?>" data-value="<?php echo esc_attr($value);?>" ></span>
			<?php
			endforeach;
		endif;
	?>
	<?php
	if (!empty($data['referrals_graph-verified']) && is_array($data['referrals_graph-verified'])):
		if ( count($data['referrals_graph-verified'])<2 ){
			if (empty($data['referrals_graph-verified'][$first_key])){
				$data['referrals_graph-verified'][$first_key] = 0;
			} else if (empty($data['referrals_graph-verified'][$last_key])){
				$data['referrals_graph-verified'][$last_key] = 0;
			}
		}
		foreach ($data['referrals_graph-verified'] as $date=>$value):?>
			<span class="uap-js-referral-graph-verified-data" data-date="<?php echo strtotime($date) . '000';?>" data-value="<?php echo esc_attr($value);?>" ></span>
		<?php
		endforeach;
	endif;
?>

	<span class="uap-js-referrals-tick-size" data-value="<?php echo esc_attr($data['tick_size']);?>"></span>
	<span class="uap-js-referrals-tick-type" data-value="<?php echo esc_attr($data['tick_type']);?>"></span>
	<span class="uap-js-referrals-min-time" data-value="<?php echo esc_attr($start_time . '000'); ?>"></span>
	<span class="uap-js-referrals-max-time" data-value="<?php echo esc_attr($end_time . '000');?>"></span>
<?php
}
?>
<span class="uap-js-reports-labels"
		data-all_clicks="<?php esc_html_e('All Clicks', 'uap');?>"
		data-converted_clicks="<?php esc_html_e('Converted Clicks', 'uap');?>"
		data-all_referrals="<?php esc_html_e('All Referrals', 'uap');?>"
		data-refuse_referrals="<?php esc_html_e('Rejected Referrals', 'uap');?>"
		data-unverified_referrals="<?php esc_html_e('Pending Referrals', 'uap');?>"
		data-verified_referrals="<?php esc_html_e('Approved Referrals', 'uap');?>"
></span>
</div>

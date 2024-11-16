<div class="uap-wrapper">
<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('REST API', 'uap');?></h3>
		<div class="inside">

			<div class="uap-form-line">
				<h2><?php esc_html_e('Activate/Hold Customize Menu', 'uap');?></h2>
				<label class="uap_label_shiwtch uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_rest_api_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_rest_api_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" name="uap_rest_api_enabled" value="<?php echo esc_attr($data['metas']['uap_rest_api_enabled']);?>" id="uap_rest_api_enabled" />
			</div>


			<p><?php esc_html_e( 'In order to use this REST API, you must do a basic authentication with your username and password.', 'uap' );?></p>
			<p>
					ex:
					<code>
						$wp_request_headers = array(
						  'Authorization' => 'Basic ' . base64_encode( 'username:password' )
						);
						$wp_request_url = '{endpoint}';
						$response = wp_remote_request(
						  $wp_request_url,
						  array(
						      'method'    => 'POST',
						      'headers'   => $wp_request_headers
						  )
						);
					</code>
			</p>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>

		</div>
	</div>


</form>

<?php
$endpoints = [
		'getAffiliates'						=> [
						'title' 	=> esc_html__('List Affiliates', 'uap'),
						'link'		=> 'affiliates',
						'method'	=> 'GET',
						'args'		=> 'page, limit',
						'extra'		=> '',
		],
		'approveAffiliate'				=> [
						'title' 	=> esc_html__('Approve affiliate', 'uap'),
						'link'		=> 'approve-affiliate/{affiliateId}',
						'method'	=> 'POST',
						'args'		=> 'affiliateId',
						'extra'		=> '',
		],
		'updateAffiliateRank'			=> [
						'title' 	=> esc_html__('Update affiliate rank', 'uap'),
						'link'		=> 'update-affiliate-rank/{affiliateId}/{newRankId}',
						'method'	=> 'POST',
						'args'		=> 'affiliateId, rankId',
						'extra'		=> '',
		],
		'getAllUserData'					=> [
						'title' 	=> esc_html__('Get user data', 'uap'),
						'link'		=> 'get-user-data/{affiliateId}',
						'method'	=> 'GET',
						'args'		=> 'affiliateId',
						'extra'		=> '',
		],
		'getUserFieldValue'				=> [
						'title' 	=> esc_html__('Get user field value', 'uap'),
						'link'		=> 'get-user-field-value/{affiliateId}/{fieldName}',
						'method'	=> 'GET',
						'args'		=> 'affiliateId, fieldName',
						'extra'		=> '',
		],
		'getAffiliateRank'				=> [
						'title' 	=> esc_html__('Get affiliate rank', 'uap'),
						'link'		=> 'get-affiliate-rank/{affiliateId}',
						'method'	=> 'GET',
						'args'		=> 'affiliateId',
						'extra'		=> '',
		],
		'getAffiliateRankDetails' => [
						'title' 	=> esc_html__('Get affiliate rank details', 'uap'),
						'link'		=> 'get-affiliate-rank-details/{affiliateId}',
						'method'	=> 'GET',
						'args'		=> 'affiliateId',
						'extra'		=> '',
		],
		'searchAffiliate'					=> [
						'title' 	=> esc_html__('Search Affiliate', 'uap'),
						'link'		=> 'search-affiliate/{search}',
						'method'	=> 'GET',
						'args'		=> 'search',
						'extra'		=> '',
		],
		'listRanks'								=> [
						'title' 	=> esc_html__('List Ranks', 'uap'),
						'link'		=> 'list-ranks',
						'method'	=> 'GET',
						'args'		=> '',
						'extra'		=> '',
		],
		'getAffiliatesByRank'			=> [
						'title' 	=> esc_html__('Get affiliates by rank', 'uap'),
						'link'		=> 'list-affiliates-by-rank/{rankId}',
						'method'	=> 'GET',
						'args'		=> 'rankId',
						'extra'		=> '',
		],
		'makeUserAffiliate'				=> [
						'title' 	=> esc_html__('Make user affiliate', 'uap'),
						'link'		=> 'make-user-affiliate/{userId}',
						'method'	=> 'PUT',
						'args'		=> 'userId',
						'extra'		=> '',
		],
		'listReferrals'						=> [
						'title' 	=> esc_html__('List Referrals', 'uap'),
						'link'		=> 'list-referrals',
						'method'	=> 'GET',
						'args'		=> '',
						'extra'		=> '',
		],
		'createReferral'							=> [
						'title' 	=> esc_html__('Add Referral', 'uap'),
						'link'		=> 'add-referral',
						'method'	=> 'PUT',
						'args'		=> 'json with all referral details',
						'extra'		=> 'Example: {
														"refferal_wp_uid": 2,
														"campaign": "",
														"affiliate_id": "",
														"visit_id": "",
														"description": "test",
														"source": "restapi",
														"reference": "q",
														"reference_details": "test",
														"parent_referral_id": "test",
														"child_referral_id": "",
														"amount": 10,
														"currency": "usd",
														"date": "12-02-2018",
														"status": 1,
														"payment": 0
					}'
		],

];
?>
			<?php foreach ($endpoints as $array):?>
					<div class="uap-stuffbox">
						<h3 class="uap-h3"><?php echo esc_uap_content($array['title']);?></h3>
						<div class="inside">
							<div class="uap-form-line">
								<p class="uap-api-link"><?php echo esc_url($data['base_url'] . '/wp-json/ultimate-affiliates-pro/v1/' . $array['link']);?></p>
								<p><?php echo esc_uap_content('<strong>' . esc_html__('Method: ', 'uap') . '</strong>' . $array['method']);?></p>
								<p><?php echo esc_uap_content('<strong>' . esc_html__('Arguments: ', 'uap') . '</strong>' . $array['args']);?></p>
								<p><?php echo esc_uap_content($array['extra']);?></p>
							</div>
						</div>
					</div>
			<?php endforeach;?>

</div>

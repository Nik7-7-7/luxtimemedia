<?php
	$ranks = $indeed_db->get_rank_list();

	$available_systems = $indeed_db->getPossibleSources();
	$source_arr = [];
	if ( $available_systems ){
			foreach($available_systems as $k=>$v){
					$label = uap_service_type_code_to_title($v['source']);
					if ( $label === '' ){
							continue;
					}
					$source_arr[$v['source']] = $label;
			}
	}

if ( isset( $responseFromImportCSV ) && count( $responseFromImportCSV ) > 0 && isset( $responseFromImportCSV['status'] ) && isset( $responseFromImportCSV['message'] ) ):?>
		<?php
			switch ( $responseFromImportCSV['status'] ){
					case 0:
						$messageClass = 'uap-danger-box';
						break;
					case 1:
						$messageClass = 'uap-success-box';
						break;
					case -1:
						$messageClass = 'uap-warning-box';
						break;
			}
		?>
		<div class="<?php echo $messageClass;?>"><?php echo $responseFromImportCSV['message'];?></div>
<?php endif;?>

<div class="uap-wrapper uap-export-import-section-wrap">
	<form  method="post">
		<div class="uap-stuffbox">
			<h3 class="uap-h3"><?php esc_html_e('Export CSV Files', 'uap');?></h3>
			<div class="inside">
				<div class="uap-form-line">
					<h2><?php esc_html_e('Export Affiliates', 'uap');?></h2>
					<p><?php esc_html_e('Export your affiliates list to a CSV file to access detailed information about your affiliates in a convenient format.', 'uap');?></p>
				</div>
				<div class="uap-form-line uap-export-csv-filters-wrap">

					<div class="uap-datatable-filters-wrapper"  id="uap_js_import_export_wrapp_for_affiliates">

							<input type="text" value="" placeholder="Search" class="uap-js-search-phrase">
							<div class="uap-datatable-multiselect-wrapp">
									<select name="ranks[]" class="uap-js-import-export-affiliates-filter-ranks" multiple data-placeholder="<?php esc_html_e("Ranks", 'uap');?>">
											<?php if ( $ranks ):?>
													<?php foreach ( $ranks as $rankId => $rankLabel ):?>
															<option value="<?php echo $rankId;?>"><?php echo $rankLabel;?></option>
													<?php endforeach;?>
											<?php endif;?>

									</select>
							</div>
							<div class="uap-datatable-filter-bttn js-uap-export-csv uap-js-affiliates-export-run-bttn" data-filters="" data-export_type="affiliates"><?php esc_html_e( 'Export CSV', 'uap' );?>

					</div>

				</div>
			</div>
					<div class="uap-form-line">
						<h2><?php esc_html_e('Export Referrals', 'uap');?></h2>
						<p><?php esc_html_e('Export all referrals data to a CSV file for detailed analysis and reporting.', 'uap');?></p>
					</div>
					<div class="uap-form-line uap-export-csv-filters-wrap">

						<div class="uap-datatable-filters-wrapper" id="uap_js_import_export_wrapp_for_referrals" >

										<input type="text" value="" placeholder="Search" class="uap-js-search-phrase-referrals">

										<!--label class="uap-label"><?php esc_html_e('Start:', 'uap');?></label-->
										<input type="text" name="udf" value="" class="uap-no-margin-right uap-js-referrals-start-date" placeholder="From - yyyy-mm-dd"/>
										<!--label class="uap-label"><?php esc_html_e('Until:', 'uap');?></label--><span class="uap-date-line">-</span>
										<input type="text" name="udu" value="" class="uap-js-referrals-end-date" placeholder="To - yyyy-mm-dd"/>

										<div class="uap-datatable-multiselect-wrapp uap-filter-status-select">
											<select name="status_in[]" class="uap-js-import-export-referrals-filter-status" multiple data-placeholder="<?php esc_html_e("Status", 'uap');?>">
												 	<option value="2"><?php esc_html_e( 'Approved', 'uap' );?></option>
													<option value="1"><?php esc_html_e( 'Pending', 'uap' );?></option>
													<option value="0"><?php esc_html_e( 'Rejected', 'uap' );?></option>
											</select>
										</div>

										<?php if ( $source_arr ):?>
										<div class="uap-datatable-multiselect-wrapp">
											<select name="source_in[]" class="uap-js-import-export-referrals-filter-source" multiple data-placeholder="<?php esc_html_e("Source", 'uap');?>">
														<?php foreach ( $source_arr as $sourceSlug => $sourceLabel ):?>
															<option value="<?php echo $sourceSlug;?>" ><?php echo $sourceLabel;?></option>
														<?php endforeach;?>
											</select>
										</div>
										<?php endif;?>

										<div class="uap-datatable-filter-bttn js-uap-export-csv uap-js-referrals-export-run-bttn" data-filters="" data-export_type="referrals"><?php esc_html_e( 'Export CSV', 'uap' );?>
										</div>
						</div>

					</div>

					<div class="uap-form-line">
						<h2><?php esc_html_e('Export Clicks', 'uap');?></h2>
						<p><?php esc_html_e('Export all click data to a CSV file for comprehensive analysis and reporting.', 'uap');?></p>
					</div>
					<div class="uap-form-line uap-export-csv-filters-wrap">
						<div class="uap-datatable-filters-wrapper" id="uap_js_import_export_wrapp_for_visits" >

										<input type="text" value="" placeholder="Search" class="uap-js-search-phrase-visits">

										<!--label class="uap-label"><?php esc_html_e('Start:', 'uap');?></label-->
										<input type="text" name="udf" value="" class="uap-no-margin-right uap-js-visits-start-date" placeholder="From - yyyy-mm-dd"/>
										<!--label class="uap-label"><?php esc_html_e('Until:', 'uap');?></label--><span class="uap-date-line">-</span>
										<input type="text" name="udu" value="" class="uap-js-visits-end-date" placeholder="To -yyyy-mm-dd"/>

										<div class="uap-datatable-multiselect-wrapp">
											<select name="status_in[]" class="uap-js-import-export-visits-filter-status" multiple data-placeholder="<?php esc_html_e("Status", 'uap');?>">
													<option value="0"><?php esc_html_e( 'Just Visit', 'uap' );?></option>
													<option value="1"><?php esc_html_e( 'Converted', 'uap' );?></option>
											</select>
										</div>
										<div class="uap-datatable-filter-bttn js-uap-export-csv uap-js-visits-export-run-bttn" data-filters=""  data-export_type="visits"><?php esc_html_e( 'Export CSV', 'uap' );?>
									</div>

						</div>
					</div>

					<div class="uap-form-line">
						<h2><?php esc_html_e('Export Payout Payments', 'uap');?></h2>
						<p><?php esc_html_e('Export all Payout Payments to a CSV file in a convenient format for furhter management.', 'uap');?></p>
					</div>
					<div class="uap-form-line uap-export-csv-filters-wrap">
						<div class="uap-datatable-filters-wrapper" id="uap_js_import_export_wrapp_for_payouts" >

										<input type="text" value="" placeholder="Search" class="uap-js-search-phrase">

										<!--label class="uap-label"><?php esc_html_e('Start:', 'uap');?></label-->
										<input type="text" name="udf" value="" class="uap-no-margin-right uap-js-payouts-start-date" placeholder="From - yyyy-mm-dd"/>
										<!--label class="uap-label"><?php esc_html_e('Until:', 'uap');?></label--><span class="uap-date-line">-</span>
										<input type="text" name="udu" value="" class="uap-js-payouts-end-date" placeholder="To -yyyy-mm-dd"/>

										<div class="uap-datatable-multiselect-wrapp">
											<select name="status_in[]" class="uap-js-import-export-payouts-filter-status" multiple data-placeholder="<?php esc_html_e("Status", 'uap');?>">
													<option value="0"><?php esc_html_e( 'Failed', 'uap' );?></option>
													<option value="1"><?php esc_html_e( 'Pending', 'uap' );?></option>
													<option value="2"><?php esc_html_e( 'Completed', 'uap' );?></option>
											</select>
										</div>
										<div class="uap-datatable-filter-bttn js-uap-export-csv uap-js-payouts-export-run-bttn" data-filters=""  data-export_type="payouts"><?php esc_html_e( 'Export CSV', 'uap' );?>
									</div>

						</div>
					</div>
			</div>
		</div>
	</form>
<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Export Settings', 'uap');?></h3>
		<div class="inside">

				<!--div class="uap-form-line">
					<h4><?php esc_html_e('Export Affiliates', 'uap');?></h4>
					<p><?php esc_html_e('Export the Affiliates for this site as a .xml file. This allows you to easily import them into another site.', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<input type="checkbox" class="iump-switch" onClick="uapCheckAndH(this, '#import_users');" />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="import_users" value=0 id="import_users"/>
				</div -->
				<div class="uap-form-line">
					<h4><?php esc_html_e('Export Settings', 'uap');?></h4>
					<p><?php esc_html_e('Export the Ultimate Affiliate Pro settings for this site as a .xml file. This allows you to easily import the configuration into another site.', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#import_settings');" />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="import_settings" value=0 id="import_settings"/>
				</div>

			<div class="uap-form-line">
				<div class="uap-hidden-download-results">
						<div class="uap-hidden-download-link uap-display-none">
							<h4><?php esc_html_e('Export File', 'uap');?></h4>
							<?php esc_html_e('Your Export file is ready to download through this link:', 'uap');?> <a href="" target="_blank" download>export.xml</a>
						</div>
						<div class="uap-display-inline" id="uap_loading_gif" ><span class="spinner"></span></div>
				</div>
			</div>

			<div id="uap_save_changes" class="uap-wrapp-submit-bttn uap-submit-form">
				<div class="button button-primary button-large uap-display-inline" onClick="uapMakeExportFile();"><?php esc_html_e('Generate XML file', 'uap');?></div>

			</div>

		</div>
	</div>
</form>

<form  method="post" enctype="multipart/form-data">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Import Settings', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<h4><?php esc_html_e('Import XML File', 'uap');?></h4>
				<p><?php esc_html_e('Import the .xml file from another WordPress website that was created using the Ultimate Affiliate Pro plugin.', 'uap');?></p>

				<input type="file" name="import_file" />
			</div>

			<input type="hidden" name="uap_admin_import_nonce" value="<?php echo wp_create_nonce( 'uap_admin_import_nonce' );?>" />

			<div id="uap_save_changes" class="uap-wrapp-submit-bttn uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Import XML File', 'uap');?>" name="import" class="button button-primary button-large">
			</div>
		</div>
	</div>

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Import Affiliates', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<h4><?php esc_html_e('Import CSV File', 'uap');?></h4>
				<p><?php esc_html_e('Import the .csv file from another WordPress website that was created using the Ultimate Affiliate Pro plugin.', 'uap');?></p>

				<input type="file" name="import_file_affiliates" />
			</div>

			<input type="hidden" name="uap_admin_import_nonce" value="<?php echo wp_create_nonce( 'uap_admin_import_nonce' );?>" />

			<div id="uap_save_changes" class="uap-wrapp-submit-bttn uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Import CSV File', 'uap');?>" name="import_csv_affiliates" class="button button-primary button-large">
			</div>
		</div>
	</div>

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Import Referrals', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<h4><?php esc_html_e('Import CSV File', 'uap');?></h4>
				<p><?php esc_html_e('Import the .csv file from another WordPress website that was created using the Ultimate Affiliate Pro plugin.', 'uap');?></p>

				<input type="file" name="import_file_referrals" />
			</div>

			<input type="hidden" name="uap_admin_import_nonce" value="<?php echo wp_create_nonce( 'uap_admin_import_nonce' );?>" />

			<div id="uap_save_changes" class="uap-wrapp-submit-bttn uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Import CSV File', 'uap');?>" name="import_csv_referrals" class="button button-primary button-large">
			</div>
		</div>
	</div>

</form>

</div>

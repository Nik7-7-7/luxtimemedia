<?php
// $atts are defined in class-shortcodes.php
// don't try to load this file directly, instead call ssa()->shortcodes->ssa_upcoming_appointments()

$upcoming_appointments = ssa()->appointment_model->query( $atts );

$settings    = ssa()->settings->get();
$date_format = SSA_Utils::localize_default_date_strings($settings['global']['date_format']);
$time_format = SSA_Utils::localize_default_date_strings($settings['global']['time_format']);
?>
<div class="ssa-upcoming-appointments-container" role="region" aria-labelledby="appointments-heading">
	<div class="ssa-upcoming-appointments">
		<?php if ( ! is_user_logged_in() ) : ?>
			<?php echo $atts['logged_out_message']; ?>
		<?php elseif ( empty( $upcoming_appointments ) && !empty( $atts['block_settings']['no_results_message'] ) ) : ?>
			<?php echo $atts['block_settings']['no_results_message']; ?>
		<?php elseif ( empty( $upcoming_appointments ) ) : ?>
			<?php echo $atts['no_results_message']; ?>
		<?php else : ?>
			<?php foreach ( $upcoming_appointments as $upcoming_appointment ) : ?>
				<?php $staff_details = ssa()->staff_appointment_model->get_staff_details_for_appointment_id( $upcoming_appointment['id'] );
				$resources = ssa()->resource_appointment_model->get_resource_details( $upcoming_appointment['id'] );
				$show_staff_image = false;
				$show_staff_name = false;
				$show_appointment_type = false;
				$hide_team_member = false;
				$hide_resources = false;
				$show_all_resources = false;
				if (!empty($atts['block_settings']['memberInformation']) && is_array($atts['block_settings']['memberInformation'])) {
					foreach ($atts['block_settings']['memberInformation'] as $setting) {
						if ($setting['value'] === 'Display Team Members' && $setting['checked'] == "true") {
							$hide_team_member = true;
						}
						if ($setting['value'] === 'Display Member Images' && $setting['checked'] == "true") {
							$show_staff_image = true;
						}
						if ($setting['value'] === 'Display Member Names' && $setting['checked'] == "true") {
							$show_staff_name = true;
						}
					}
				}
				if (!empty($atts['block_settings']['memberInfo']) && is_array($atts['block_settings']['memberInfo'])) {
					if (in_array('Display Team Members', $atts['block_settings']['memberInfo'])) {
						$hide_team_member = true;
					}
					if (in_array('Display Member Images', $atts['block_settings']['memberInfo'])) {
						$show_staff_image = true;
					}
					if (in_array('Display Member Names', $atts['block_settings']['memberInfo'])) {
						$show_staff_name = true;
					}
				}
				if (!empty($atts['block_settings']['appointmentDisplay'])) {
					if (is_array($atts['block_settings']['appointmentDisplay'])) {
						foreach ($atts['block_settings']['appointmentDisplay'] as $options) {
							if ($options['value'] === 'Display Appointment Types' && $options['checked'] == "true") {
								$show_appointment_type = true;
							}
						}
					} else {
						$show_appointment_type = true;
					}
				}
				if (!empty($atts['block_settings']['resourceDisplay'])) {
					if (is_array($atts['block_settings']['resourceDisplay'])) {
						foreach ($atts['block_settings']['resourceDisplay'] as $options) {
							if ($options['value'] === 'Display Resources' && $options['checked'] == "true") {
								$hide_resources = true;
							}
						}
					} elseif ($atts['block_settings']['resourceDisplay'] === 'Display Resources'){
						$hide_resources = true;
					}
				}
				if (!empty($atts['block_settings']['allResourcesTypeOption'])) {
					if (is_array($atts['block_settings']['allResourcesTypeOption'])) {
						foreach ($atts['block_settings']['allResourcesTypeOption'] as $options) {
							if ($options['value'] === 'All' && $options['checked'] == "true") {
								$show_all_resources = true;
							}
						}
					} elseif ($atts['block_settings']['allResourcesTypeOption'] === 'All') {
						$show_all_resources = true;
					}
				}
				?>
						<div class="appointment-card" role="article" aria-labelledby="appointment-<?php echo $upcoming_appointment['id']; ?>">
							<div class="appointment-card-header">
									<?php
										if ($staff_details && is_array($staff_details) && count($staff_details) > 1) {
											if (!$hide_team_member && $show_staff_image) {
												$count = 0;
												echo "<div class='staff-images-container'>";
												foreach ($staff_details as $staff_member) {
													$position_class = ($count === 0) ? 'secondary-image' : 'primary-image';
													echo '<img class="staff-avatar ' . esc_attr($position_class) . '" src="' . esc_url(get_avatar_url($staff_member['email'])) . '" />';
													$count++;
													if ($count >= 2) {
														break;
													}
												}
												echo "</div>";
											}
										} elseif (is_array($staff_details) && count($staff_details) === 1) {
											if ($staff_details) {
												if (!$hide_team_member && $show_staff_image) {
													echo '<img src="' . esc_url(get_avatar_url($staff_details[0]['email'])) . '" />';
												}
											}
										}
									?>
								<div class="appointment-information">
									<div class="appointment-header">
										<?php
											$upcoming_appointment_datetime = ssa_datetime( $upcoming_appointment['start_date'] );

											if ( ! empty( $upcoming_appointment['customer_timezone'] ) ) {
												$customer_timezone_string = $upcoming_appointment['customer_timezone'];
											} else {
												$customer_timezone_string = 'UTC';
											}
											$customer_timezone = new DateTimezone( $customer_timezone_string );
											$localized_date = $upcoming_appointment_datetime->setTimezone($customer_timezone)->format($date_format);
											$localized_time = $upcoming_appointment_datetime->setTimezone($customer_timezone)->format($time_format. ' (T)');

											$localized_date = SSA_Utils::translate_formatted_date($localized_date);

											echo '<p><span class="appointment-date">' . esc_html__($localized_date, 'simply-schedule-appointments') . 
											'</span> <span class="appointment-time">' . esc_html__('at', 'simply-schedule-appointments') . ' ' . esc_html__($localized_time, 'simply-schedule-appointments') . '</span></p>';

											$upcoming_appointment_type = new SSA_Appointment_Type_Object( $upcoming_appointment['appointment_type_id'] );
											$upcoming_appointment_title = $upcoming_appointment_type->get_title();

											if ($staff_details && $show_appointment_type && !$hide_team_member && $show_staff_name) {
												echo '<p>' . esc_html__($upcoming_appointment_title, 'simply-schedule-appointments') . ' ' . esc_html__('with', 'simply-schedule-appointments') . ' ';
												$staff_names = array_map('esc_html', array_column($staff_details, 'name'));
												echo '<span class="appointment-staff">' . implode(', ', $staff_names) . '</span>';
												echo '</p>';
											} elseif ($staff_details && !$hide_team_member && $show_staff_name) {
												echo '<p>' . esc_html__('With', 'simply-schedule-appointments') . ' ';
												$staff_names = array_map('esc_html', array_column($staff_details, 'name'));
												echo '<span class="appointment-staff">' . implode(', ', $staff_names) . '</span>';
												echo '</p>';
											} elseif ($show_appointment_type) {
												echo '<p>' . esc_html__($upcoming_appointment_title, 'simply-schedule-appointments') . '</p>';
											} elseif ( filter_var( $atts['appointment_type_displayed'], FILTER_VALIDATE_BOOLEAN ) ) {
												echo '<p>' . esc_html__($upcoming_appointment_title, 'simply-schedule-appointments') . '</p>';
											}
											
										?>
									</div>
									<div class="appointment-details">
										<ul>
											<?php 
												if (!empty($resources) && !$hide_resources){
													if( !$show_all_resources){
														if( !empty( $atts['block_settings']['resourceTypes'] )){
															$appointmentResources = $atts['block_settings']['resourceTypes'];
															foreach ($resources as $resource) {
																if (
																	is_array($appointmentResources) &&
																	in_array($resource['resource_group_id'], $appointmentResources)
																) {
																	echo '<li>' . esc_html__($resource['group_title'] . ': ' . $resource['resource_title'], 'simply-schedule-appointments') . '</li>';
																}
															}
														}
													} else {
														foreach ($resources as $resource) {
															echo '<li>' . esc_html__($resource['group_title'] . ': ' . $resource['resource_title'], 'simply-schedule-appointments') . '</li>';
														}
													}
												}
											?>
											<?php 	if ( ! empty( $upcoming_appointment['web_meeting_url'] ) && filter_var( $atts['web_meeting_url'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
												<li>
													<?php echo esc_html__( 'On', 'simply-schedule-appointments'); ?>
													<a target="_blank" href="<?php echo esc_url( $upcoming_appointment['web_meeting_url'] ); ?>"><?php esc_html_e( 'Web Meeting', 'simply-schedule-appointments' ); ?></a>
												</li>
											<?php endif; ?>
										</ul>
									</div>
								</div>
							</div>
							<div class="action-bar">
								<?php
								if ( ! empty( $atts['details_link_displayed'] ) ) {
									echo '<button id="details_button" onclick="window.open(\'' . ssa()->appointment_model->get_public_edit_url($upcoming_appointment['id']) . '\', \'_blank\')">' . $atts['details_link_label'] . '</button>';
								}
								?>
							</div>
						</div>
			<?php endforeach; ?>
		<?php endif ?>
	</div>
</div>

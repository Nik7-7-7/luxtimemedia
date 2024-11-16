<?php
$field_id  = esc_attr( $field['id'] );
$map_field_phone = $field['map_field_phone'] ? $field['map_field_phone'] : '';
$map_field_name = $field['map_field_name'] ? $field['map_field_name'] : '';
$map_field_email = $field['map_field_email'] ? $field['map_field_email'] : '';
$map_field_sms_optin = $field['map_field_sms_optin'] ? $field['map_field_sms_optin'] : '';

?>
<p><?php echo __( sprintf( 'This field displays your <a href="%s">Simply Schedule Appointments appointment types</a>', ssa()->wp_admin->url( '/ssa/appointment-types/all' ) ), 'simply-schedule-appointments' ); ?></p>

<!-- Appointment Type Select Input -->
<p class="frm6 frm_form_field">
	<div class="ssa_appointment_type_setting field_setting">
		<label for="appointment_type_id_<?php echo $field_id ?>" class="section_label frm_help" title="" data-original-title="Appointment Type - Note: any payment options or custom fields defined in SSA settings will be skipped since the appointment type will be embedded in this form.">
			<?php esc_html_e('Appointment Type', 'simply-schedule-appointments'); ?>
		</label>
		<select name="field_options[appointment_type_id_<?php echo $field_id ?>]" id="appointment_type_id_<?php echo $field_id ?>" onchange="">
			<option value="" 
			<?php selected( '', esc_attr( $field['appointment_type_id'] ), true ); ?>
			>
			<?php
				esc_html_e('All', 'simply-schedule-appointments');
				?>
			</option>
			<?php
			$appointment_types = ssa()->appointment_type_model->query(  array(
				'status' => 'publish',
			) );
			foreach ($appointment_types as $key => $appointment_type): ?>
				<option value="<?php echo $appointment_type['id']; ?>" 
					<?php selected( $appointment_type['id'], esc_attr( $field['appointment_type_id'] ), true ); ?>
				>
					<?php echo $appointment_type['title']; ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
</p>
<!-- Name Form Field Select Input -->
<p class="frm6 frm_form_field">
	<div class="ssa_map_name_setting field_setting">
		<label for="map_field_name_<?php echo $field_id ?>" class="section_label frm_help" title="" data-original-title="Select the name form field that SSA should use for the customer's name.">
			<?php esc_html_e('Name Form Field', 'simply-schedule-appointments'); ?>
		</label>
		<select data-ssa-target="name" onchange="ssaUpdateMappedFieldsState(this)" name="field_options[map_field_name_<?php echo $field_id ?>]" id="map_field_name_<?php echo $field_id ?>" onchange="">
			<option value="">Select...</option>
		</select>
	</div>
</p>
<!-- Email Form Field Select Input -->
<p class="frm6 frm_form_field">
	<div class="ssa_map_email_setting field_setting">
		<label for="map_field_email_<?php echo $field_id ?>" class="section_label frm_help" title="" data-original-title="Select the email form field that SSA should use for the customer's email.">
			<?php esc_html_e('Email Form Field', 'simply-schedule-appointments'); ?>
		</label>
		<select data-ssa-target="email" onchange="ssaUpdateMappedFieldsState(this)" name="field_options[map_field_email_<?php echo $field_id ?>]" id="map_field_email_<?php echo $field_id ?>" onchange="">
			<option value="">Select...</option>
		</select>
	</div>
</p>
<!-- Phone Form Field Select Input -->
<p class="frm6 frm_form_field">
	<div class="ssa_map_phone_setting field_setting">
		<label for="map_field_phone_<?php echo $field_id ?>" class="section_label frm_help" title="" data-original-title="Select the phone form field that SSA should use for the customer's phone.">
			<?php esc_html_e('Phone Form Field', 'simply-schedule-appointments'); ?>
		</label>
		<select data-ssa-target="phone" onchange="ssaUpdateMappedFieldsState(this)" name="field_options[map_field_phone_<?php echo $field_id ?>]" id="map_field_phone_<?php echo $field_id ?>" onchange="">
			<option value="">Select...</option>
		</select>
	</div>
</p>
<!-- SMS opt-in Form Field Select Input -->
<p class="frm6 frm_form_field">
	<div class="ssa_map_sms_optin_setting field_setting">
		<label for="map_field_sms_optin_<?php echo $field_id ?>" class="section_label frm_help" title="" data-original-title="Select the toggle form field that SSA should use for the customer's SMS opt-in.">
			<?php esc_html_e('SMS opt-in Form Field', 'simply-schedule-appointments'); ?>
		</label>
		<select data-ssa-target="toggle" onchange="ssaUpdateMappedFieldsState(this)" name="field_options[map_field_sms_optin_<?php echo $field_id ?>]" id="map_field_sms_optin_<?php echo $field_id ?>" onchange="">
			<option value="">Select...</option>
		</select>
	</div>
</p>
<!-- JavaScript -->
<script defer>
	if(undefined === window.ssaMappedFieldsState){
		window.ssaMappedFieldsState = {}
	}
	window.addEventListener('DOMContentLoaded',  () => {
			let field_id = "<?php echo $field_id?>"
				window.ssaMappedFieldsState[field_id]= {
					'phone': "<?php echo $map_field_phone ?>",
					'name': "<?php echo $map_field_name ?>",
					'email': "<?php echo $map_field_email ?>",
					'toggle': "<?php echo $map_field_sms_optin ?>",
				}
			},
		false
	);
	function ssaUpdateMappedFieldsState (element){
		let field_id = element.id?.split('_').pop()
		let ssaTarget = element.dataset.ssaTarget
		window.ssaMappedFieldsState[field_id][ssaTarget] = element.value;
	}
	// used to attach logic to the click event on the label of SSA field preview
	document.querySelectorAll( '#frm-show-fields > li, .frm_field_id_<?php echo $field_id?>' ).forEach( ( el, _key ) => {
		let field_id = "<?php echo $field_id?>"
		el.addEventListener( 'click', function(e) {
			let validIds ={
				'phone':[],
				'name':[],
				'email':[],
				'toggle':[],
			};
			
			document.querySelectorAll('li[id^=frm_field_id_]').forEach((element)=>{
				if ( 0 === element?.id.match(/frm_field_id_[0-9]+$/)?.length ) {
					return;
				}
				let fieldType = element?.dataset?.type
				let readableId = '(ID '+ element?.dataset?.fid + ')'
				let label = jQuery(element).find('label')[0].innerText.replaceAll('*','').trim()
				if('text' === fieldType){
					if('name' === label.toLowerCase()){
						fieldType = 'name'
					} else if ('email' === label.toLowerCase()){
						fieldType = 'email'
					} else if ('phone' === label.toLowerCase()){
						fieldType = 'phone'
					}
				}
				let fieldRef = {
							innerText: readableId + ' ' + label,
							id: element.id.match(/frm_field_id_([0-9]+)$/)?.[1],
							elementId: element?.id
						}
				if(undefined != validIds?.[fieldType]){
					validIds?.[fieldType].push(fieldRef)
				}
			})
			let $map_fields = {
				'phone':jQuery("#map_field_phone_<?php echo $field_id ?>"),
				'name':jQuery("#map_field_name_<?php echo $field_id ?>"),
				'email':jQuery("#map_field_email_<?php echo $field_id ?>"),
				'toggle':jQuery("#map_field_sms_optin_<?php echo $field_id ?>"),
			}
			for (const [key, validFieldsIDsArray] of Object.entries(validIds)) {
				$map_fields[key].empty();
				$map_fields[key].append('<option value="">Select Formidable Field</option>');
				if(validFieldsIDsArray.length){
					validFieldsIDsArray.forEach((fieldRef)=>{
						$map_fields[key].append(`<option ${fieldRef.id === ssaMappedFieldsState[field_id]?.[key] ? 'selected' : '' } value="${fieldRef.id}">${fieldRef.innerText}</option>`);
					})
				}
			}
		});
	});
</script>

<script defer>
setTimeout(()=>{
	let value = jQuery('.frm-type-ssa-appointment>h3')[0].innerHTML;
	let linkText = <?php echo "'" . __( 'Learn how this works', 'simply-schedule-appointments' ) . "'"; ?>;
	setTimeout(() => {
		jQuery('.frm-type-ssa-appointment>h3')[0].innerHTML= value + " <p style='display:inline;font-size:13px;'><a target='_blank' href='https://simplyscheduleappointments.com/guides/formidable-forms-appointment-field/?utm_source=ff-field&utm_medium=guides&utm_campaign=support-help&utm_content=ff-learn-more'>" + linkText + "</a></p>";
	}, 0)
})
</script>
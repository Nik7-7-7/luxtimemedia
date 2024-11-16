var el = wp.element.createElement,
	registerBlockType = wp.blocks.registerBlockType,
	ServerSideRender = wp.ServerSideRender,
	SelectControl = wp.components.SelectControl,
	CheckboxControl = wp.components.CheckboxControl,
	RangeControl = wp.components.RangeControl,
	InspectorControls = wp.blockEditor.InspectorControls,
	PanelColorSettings = wp.blockEditor.PanelColorSettings,
	PanelBody = wp.components.PanelBody,
	NumberControl = wp.components.__experimentalNumberControl,
	Button = wp.components.Button,
	TabPanel = wp.components.TabPanel;

if ( typeof ssaAppointmentTypesViewOptions === 'undefined' ) {
	ssaAppointmentTypesViewOptions = null;
}

if ( typeof ssaBookingFlowOptions === 'undefined' ) {
	ssaBookingFlowOptions = null
}


registerBlockType("ssa/booking", {
	title: "Appointment Booking Form",
	description:
		"Displays an Appointment Booking Form. You can customize the appointment type and styles.",
	icon: "calendar-alt",
	category: "widgets",

	edit: function (props) {

		// For exsiting/old shortcodes before introducing the checkboxes; run Conversion
		// Needed only for the selected type to be checked in UI
		if(props.attributes.type){
			let type = props.attributes.type;
			props.setAttributes({ types : [type] });
			props.setAttributes({ type : '' } );
		}

		var apptTypeOptions = 
			{
				All: 'All',
			};
		Object.keys(ssaAppointmentTypes).forEach(function (key) {
			apptTypeOptions[key] = ssaAppointmentTypes[key]
		});

		function onCheckChange(isChecked) {
			let element = this.valueOf()
			if(isChecked){
				if(!props.attributes.types.includes(element)){
					if(element==='All'){
						props.setAttributes({ types: Object.keys(apptTypeOptions) });
					} else {
						let selectedApptType = [...props.attributes.types]
						selectedApptType.push(element)
						props.setAttributes({ types: selectedApptType });
					}
				}
			} else {
				if(props.attributes.types.includes(element)){
					if(element==='All'){
						let selectedAppTypes = Object.keys(apptTypeOptions)
						index = selectedAppTypes.indexOf('All');
						selectedAppTypes.splice(index, 1);
						props.setAttributes({ types: selectedAppTypes });
					} else {
						let selectedApptType = [...props.attributes.types]
						var index = selectedApptType.indexOf(element);
						selectedApptType.splice(index, 1);
						props.setAttributes({ types: selectedApptType });
					}
				}
			}
		}

		var apptTypeCheckboxes = Object.keys(apptTypeOptions).filter((option) => {
			if(props.attributes.types.includes('All')){
				return option === 'All'
			}
			return true
		}).map(function(key) {
			// Render the checkboxes elements inside a parent container
			// Only render the uncheck all button besides the All checkbox
			return el(
							"div",
							{ className: "ssa-checkboxes-input-container" },
							el(CheckboxControl, {
								onChange: onCheckChange.bind(key),
								label: apptTypeOptions[key],
								checked: props.attributes.types.includes(key)
							}),
							(key === 'All' && !props.attributes.types.includes('All') && props.attributes.types.length ) ?
							el(Button, {
								isSecondary: true,
								className: "ssa-block-booking-uncheck-all",
								onClick: function () {
										props.setAttributes({
												types: [],
										});
								}
							}, 'Uncheck All') : 
							null,
						)
			
		})

		var LabelsOptions = [
			{
				value: "All",
				label: "All",
			},
		];
		Object.keys(ssaAppointmentTypeLabels).forEach(function (key) {
			LabelsOptions.push({
				value: key,
				label: ssaAppointmentTypeLabels[key],
			});
		});

		/* BOOKING FLOW */
		let bookingFlowTabs = [
			{
				name: 'date',
				title: 'Date View',
				className: 'tab-date-view',
			},
			{
				name: 'time',
				title: 'Time View',
				className: 'tab-time-view',
			},
		];

		const displaybookingFlowTabs = () => {
			if (props.attributes.flow === 'express') {
				return bookingFlowTabs.filter(tab => tab.name !== 'date')
			} else if (props.attributes.flow === 'first_available' && props.attributes.fallback_flow === 'express') {
				return bookingFlowTabs.filter(tab => tab.name !== 'date')
			}
			return bookingFlowTabs;
		}
		/* END BOOKING FLOW */

		return [
			el(
				"div",
				{ className: "ssa-block-container" },
				el(ServerSideRender, {
					block: "ssa/booking",
					attributes: props.attributes,
				}),
				el("div", {
					className: "ssa-block-handler",
				})
			),
			el(
				InspectorControls,
				{},
				el(
					PanelBody,
					{ title: "Select Appointment types", initialOpen: false },
					el(SelectControl, {
						label: 'Filter by',
						value: props.attributes.filter,
						options: [
							{
								value: "types",
								label: "Appointment types",
							},
							{
								value: "label",
								label: "Label",
							},
						],
						onChange: function (value) {
							props.setAttributes({ filter: value });
						},
					}),
					props.attributes.filter === 'label' ?
					el(SelectControl, {
						label: "Labels",
						value: props.attributes.label,
						options: LabelsOptions,
						onChange: function (value) {
							props.setAttributes({ label: value });
						},
					}) :
					el('div', null, apptTypeCheckboxes)
				),

				/* Appointment types view */
				ssaAppointmentTypesViewOptions && el(
					PanelBody,
					{ title: "Appointment types view", initialOpen: false },
					el(SelectControl, {
						label: "Appointment types view",
						value: props.attributes.appointment_types_view,
						options: ssaAppointmentTypesViewOptions,
						onChange: function (value) {
							props.setAttributes({ appointment_types_view: value });
						},
					}),
				),
				/* End Appointment types view */

				/* Booking flow */
				ssaBookingFlowOptions && el(
					PanelBody,
					{ title: "Booking Flow", initialOpen: false },
					el(SelectControl, {
						label: "Main Booking layout",
						value: props.attributes.flow,
						options: ssaBookingFlowOptions.main_booking_flow,
						onChange: function (value) {
							props.setAttributes({ flow: value });
						},
					}),
					props.attributes.flow === 'first_available' && 	el(
						'PanelBody',
						{ title: "first_available", initialOpen: false },
						el('div', {}, 'First available within:'),
						el(
							'div',
							{ className: "ssa_first_available_control_wrapper" },
							el(NumberControl, {
								label:"Duration",
								value: props.attributes.suggest_first_available_duration,
								min:1,
								onChange: function (value) {
									props.setAttributes({ suggest_first_available_duration: value });
								},
							}),
							el(SelectControl, {
								label: "Duration Unit",
								className: 'ssa_duration_unit',
								value: props.attributes.suggest_first_available_duration_unit,
								options: ssaBookingFlowOptions.suggest_first_available.duration_unit,
								onChange: function (value) {
									props.setAttributes({ suggest_first_available_duration_unit: value });
								},
							}),
						),
					),
					props.attributes.flow === 'first_available' && el(SelectControl, {
						label: "Fallback Flow",
						value: props.attributes.fallback_flow,
						options: ssaBookingFlowOptions.fallback_flow,
						onChange: function (value) {
							props.setAttributes({ fallback_flow: value });
						},
					}),
					props.attributes.flow !== 'appt_type_settings' && el(TabPanel, {
						className: 'ssa-bookingflow-tab-panel',
						activeClass: 'active-tab',
						tabs: displaybookingFlowTabs(),
						orientation: 'horizontal',
						// initialTabName: 'team_members', // the name of the tab that is selected by default
						children: function(tab) {
								switch (tab.name) {
										case 'date':
												return el(SelectControl, {
													label: 'Date view',
													value: props.attributes.date_view,
													options: ssaBookingFlowOptions.date_view,
													onChange: function (value) {
														props.setAttributes({ date_view: value });
													},
												});
										case 'time':
											return el(SelectControl, {
												label: 'Time view',
												value: props.attributes.time_view,
												options: ssaBookingFlowOptions.time_view,
												onChange: function (value) {
													props.setAttributes({ time_view: value });
												},
											});
								}
						},
					}),
				),
				/* End Booking flow */

				el(PanelColorSettings, {
					title: "Colors",
					colorSettings: [
						{
							value: props.attributes.accent_color,
							label: "Accent Color",
							onChange: function (value) {
								props.setAttributes({
									accent_color: value,
								});
							},
						},
						{
							value: props.attributes.background,
							label: "Background Color",
							onChange: function (value) {
								props.setAttributes({
									background: value,
								});
							},
						},
					],
				}),
				el(
					PanelBody,
					{ title: "Padding", initialOpen: true },
					el(RangeControl, {
						initialPosition: 0,
						value: props.attributes.padding,
						onChange: function (value) {
							props.setAttributes({
								padding: value,
							});
						},
						min: 0,
						max: 100,
					}),
					el(SelectControl, {
						label: "Padding Unit",
						value: props.attributes.padding_unit,
						options: [
							{
								value: "px",
								label: "px",
							},
							{
								value: "em",
								label: "em",
							},
							{
								value: "rem",
								label: "rem",
							},
							{
								value: "vw",
								label: "vw",
							},
							{
								value: "percent",
								label: "%",
							},
						],
						onChange: function (value) {
							props.setAttributes({ padding_unit: value });
						},
					})
				)
			),
		];
	},

	save: function () {
		return null;
	},
});

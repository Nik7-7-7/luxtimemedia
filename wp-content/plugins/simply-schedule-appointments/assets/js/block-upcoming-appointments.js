var el = wp.element.createElement,
	registerBlockType = wp.blocks.registerBlockType,
	ServerSideRender = wp.serverSideRender,
	TextControl = wp.components.TextControl,
	InspectorControls = wp.blockEditor.InspectorControls;
	Button = wp.components.Button,
	CheckboxControl = wp.components.CheckboxControl,
	toggleControl = wp.components.ToggleControl,
	__ = wp.i18n.__;

registerBlockType("ssa/upcoming-appointments", {
	title: "Upcoming Appointments",
	description:
		__('Displays Upcoming Appointments. You can select what to show in the appointment card.', 'simply-schedule-appointments'),
	icon: "calendar-alt",
	category: "widgets",
	example: {},

	edit: function (props) {
		var options = [
			{
				value: "No upcoming appointments",
				label:
					"Message to display if customer has no upcoming appointments",
			},
		];

		var toggleElements = props.attributes.memberInfo.map((toggle, index) => {
			if (toggle.value === 'Display Team Members') {
				return el(
					"div",
					{ className: "ssa-toggle-input-container" },
					el(CheckboxControl, {
						onChange: function (isChecked) {
							const updatedmembersInformation = props.attributes.memberInfo.map((item, idx) => {
								if (idx === index) {
									return { ...item, checked: isChecked ? true : false };
								}
								return item;
							});
		
							props.setAttributes({ memberInfo: updatedmembersInformation });
						},
						label: toggle.label,
						checked: toggle.checked,
					})
				);
			}
		
			if (props.attributes.memberInfo.find(item => item.value === 'Display Team Members').checked) {
				return null;
			}
		
			return el(
				"div",
				{ className: "ssa-toggle-input-container" },
				el(CheckboxControl, {
					onChange: function (isChecked) {
						const updatedmembersInformation = props.attributes.memberInfo.map((item, idx) => {
							if (idx === index) {
								return { ...item, checked: isChecked ? true : false };
							}
							return item;
						});
		
						props.setAttributes({ memberInfo: updatedmembersInformation });
					},
					label: toggle.label,
					checked: toggle.checked,
				})
			);
		});

		var toggledisplayOptions = props.attributes.appointmentDisplay
		.filter(toggle => toggle.label === 'Appointment Types')
		.map((toggle, index) => {
			return el(
				"div",
				{ className: "ssa-checkboxes-input-container" },
				el(CheckboxControl, {
					onChange: function (isChecked) {
						const updatedDisplayOptions = props.attributes.appointmentDisplay.map((item, idx) => {
							if (idx === index) {
								return { ...item, checked: isChecked ? true : false };
							}
							return item;
						});
			
						props.setAttributes({ appointmentDisplay: updatedDisplayOptions });
					},
					label: toggle.label,
					checked: toggle.checked,
				})
			);
		});
		var toggleDisplayOptionsTwo = props.attributes.resourceOptions
		.filter(toggle => toggle.label === 'Disable')
		.map((toggle, index) => {
			return el(
				"div",
				{ className: "ssa-checkboxes-input-container" },
				el(CheckboxControl, {
					onChange: function (isChecked) {
						const updatedoptionsDisplays = props.attributes.resourceOptions.map((item, idx) => {
							if (idx === index) {
								return { ...item, checked: isChecked ? true : false };
							}
							return item;
						});
			
						props.setAttributes({ resourceOptions: updatedoptionsDisplays });
					},
					label: toggle.label,
					checked: toggle.checked,
				})
			);
		});

		if(props.attributes.type){
			let type = props.attributes.type;
			props.setAttributes({ selectedResourceTypes : [type] });
			props.setAttributes({ type : '' } );
		}

		var rssTypeOptions = {};
		Object.keys(ssaResources.types).forEach(function (key) {
			rssTypeOptions[key] = ssaResources.types[key]
		});
		var disableResourceOption = props.attributes.resourceOptions.some(option => option.checked);

		if (!disableResourceOption) {
			var toggleDisplayAllResourceType = props.attributes.allResourcesTypeOption
			.filter(toggle => toggle.label === 'All')
			.map((toggle, index) => {
				return el(
					"div",
					{ className: "ssa-checkboxes-input-container" },
					el(CheckboxControl, {
						onChange: function (isChecked) {
							const updatedOptionsDisplays = props.attributes.allResourcesTypeOption.map((item, idx) => {
								if (idx === index) {
									return { ...item, checked: isChecked ? true : false };
								}
								return item;
							});
							props.setAttributes({ allResourcesTypeOption: updatedOptionsDisplays });
						},
						label: toggle.label,
						checked: toggle.checked,
					}),
					(props.attributes.selectedResourceTypes.length) ?
							el(Button, {
								isSecondary: true,
								className: "ssa-block-booking-uncheck-all",
								onClick: function () {
									props.setAttributes({
										selectedResourceTypes: [],
									});
								}
							}, 'Uncheck All') :
							null,
				);
			});
		}

		
		function onCheckChange(id, isChecked) {
			const currentTypes = props.attributes.selectedResourceTypes;
		  
			if (isChecked && !currentTypes.includes(id)) {
			  const updatedTypes = [...currentTypes, id];
			  props.setAttributes({ selectedResourceTypes: updatedTypes });
			} else if (!isChecked && currentTypes.includes(id)) {
			  const updatedTypes = currentTypes.filter(type => type !== id);
			  props.setAttributes({ selectedResourceTypes: updatedTypes });
			}
		  }

		var allResourceOption = props.attributes.allResourcesTypeOption.some(option => option.checked);

		var apptTypeCheckboxes;

		if (!disableResourceOption) {
			if (!allResourceOption) {
				apptTypeCheckboxes = Object.keys(rssTypeOptions).map(function (key) {
					return el(
						"div",
						{ className: "ssa-toggle-input-container" },
						el(CheckboxControl, {
							onChange: onCheckChange.bind(null, key),
							label: rssTypeOptions[key],
							checked: props.attributes.selectedResourceTypes.includes(key),
							id: `checkbox_${key}`
						}),
					)
				});
			}
		}

		let showTeamMemberBlock = null;
		if (true == staff.enabled){
			showTeamMemberBlock = el(
				PanelBody,
				{ 
					title: __('Display Team Member Information', 'simply-schedule-appointments'), 
					initialOpen: false,
				},
				el('div', { className: 'panel-content' }, toggleElements),
			);
		}

		let showResourcesBlock = null;
		if (ssaResources.enabled && Object.keys(ssaResources.types).length > 0){
			showResourcesBlock = el(
				PanelBody,
				{ 
					title: __('Display Resources', 'simply-schedule-appointments'),
					initialOpen: false,
				},
				el('div', { className: 'panel-content' }, toggleDisplayOptionsTwo,toggleDisplayAllResourceType, apptTypeCheckboxes),
            );
		}

		return [
			el(ServerSideRender, {
				block: "ssa/upcoming-appointments",
				attributes: props.attributes,
			},
			el("div", {
				className: "ssa-block-handler",
			})
			),
			el(
				InspectorControls,
				{},
				el(TextControl, {
					label:
						__('Message to display if customer has no upcoming appointments', 'simply-schedule-appointments'),
					value: props.attributes.no_results_message,
					onChange: (value) => {
						props.setAttributes({ no_results_message: value });
					},
					onBlur: () => {
						if (!props.attributes.no_results_message) {
							props.setAttributes({ no_results_message: 'No upcoming appointments' });
						}
					},
					className: "message-box" 
				}),
				el(
                    PanelBody,
                    { title: __('Display Information', 'simply-schedule-appointments'), initialOpen: false },
                    el('div', null, toggledisplayOptions)
                ),
				showBlock && showResourcesBlock,
				showBlock && showTeamMemberBlock,
			),
		];
	},

	save: function () {
		return null;
	},
});

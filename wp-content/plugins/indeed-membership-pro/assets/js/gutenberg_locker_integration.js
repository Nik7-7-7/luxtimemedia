/*
* Ultimate Membership Pro - Gutenberg Locker
*/
"use strict";
(function(wp){
    var registerBlockType = wp.blocks.registerBlockType;
    var InspectorControls = wp.editor.InspectorControls;
    var SelectControl     = wp.components.SelectControl;
    var el                = wp.element.createElement;
    var withState         = wp.compose.withState;
    var __                = wp.i18n.__;
    var lockerOptions     = window.ihc_locker_options;

    function lockerControl(props) {
        if ( typeof window.ihc_locker_options != 'object' ){
            lockerOptions = JSON.parse(window.ihc_locker_options);
        }
        var attributes = props.attributes
        var setAttributes = props.setAttributes
        var inspectorControl = el(InspectorControls, {},
            el(SelectControl, {
                label     : 'Type:',
                value     : attributes.lockerType,
                options   : lockerOptions.lockerType,
                onChange  : function(value) {
                              setAttributes({lockerType: value});
                }
            }),
            el(SelectControl, {
                label     : 'Target:',
                multiple  : true,
                value     : attributes.lockerTarget,
                options   : lockerOptions.lockerTarget,
                onChange  : function(value) {
                              setAttributes({lockerTarget: value});
                }
            }),
            el(SelectControl, {
                label     : 'Template:',
                value     : attributes.template,
                options   : lockerOptions.templates,
                onChange  : function(value) {
                              setAttributes({template: value});
                }
            })
        );
        var output = '[ihc-hide-content ihc_mb_type="' + props.attributes.lockerType + '" ihc_mb_who="' + props.attributes.lockerTarget + '" ihc_mb_template="' + props.attributes.template + '" ][/ihc-hide-content]'

        return el('div', {
            className: '',
            style: {}
          },
          el('p', {className: ''}, output ),
          inspectorControl
        );
    }

    registerBlockType('indeed-membership-pro/locker', {
        title       : 'UMP - Locker',
        category    : 'ihc-locker',
        icon        : 'universal-access-alt',
        attributes  : {
                        template        : {type: 'string', default: ''},
                        lockerTarget    : {type: 'string', default: ''},
                        lockerType      : {type: 'string', default: 'block'},
        },
        edit        : withState({
                        urlStatus: null,
                        startDateError: null,
                        orgTags: null,
                        orgTagsMsg: null,
                        orgFacilities: null,
                        orgFacilitiesMsg: null
        })(lockerControl),
        save        : function( props ) {
            return '[ihc-hide-content ihc_mb_type="' + props.attributes.lockerType + '" ihc_mb_who="' + props.attributes.lockerTarget + '" ihc_mb_template="' + props.attributes.template + '" ][/ihc-hide-content]';
        }
    });

})(window.wp)

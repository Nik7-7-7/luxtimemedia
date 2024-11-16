import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { __, sprintf } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { find, get } from 'lodash';
import {
    G,
    Path,
    SVG,
    SelectControl,
    ToggleControl,
    TextareaControl,
    TextControl,
    Dashicon,
    PanelBody
} from '@wordpress/components';

const getFormName = (id) => get(find(quformBlockL10n.forms, ['value', id]), 'label', __('Unknown', 'quform'));

registerBlockType('quform/form', {
    title: __('Form', 'quform'),

    icon: <SVG
              version="1.0"
              xmlns="http://www.w3.org/2000/svg"
              width="397.000000pt"
              height="354.000000pt"
              viewBox="0 0 397.000000 354.000000"
              preserveAspectRatio="xMidYMid meet"
          >
                  <G
                      transform="translate(0.000000,354.000000) scale(0.100000,-0.100000)"
                      fill="currentColor"
                      stroke="none"
                  >
                      <Path d="M1660 3530 c-548 -67 -1036 -347 -1337 -768 -146 -204 -244 -433 -295 -687 -32 -160 -32 -451 0 -614 157 -784 810 -1360 1644 -1450 136 -15 2208 -15 2241 0 53 24 57 47 57 304 0 257 -4 280 -57 304 -16 7 -128 11 -319 11 l-295 0 67 83 c226 277 344 569 376 929 19 224 -6 432 -82 659 -206 622 -766 1089 -1450 1210 -131 24 -428 33 -550 19z m400 -635 c135 -21 230 -49 346 -104 139 -67 244 -140 344 -240 451 -454 449 -1114 -5 -1566 -467 -465 -1243 -473 -1726 -18 -148 140 -275 352 -326 548 -22 87 -26 120 -26 255 0 136 4 168 27 255 46 174 144 355 268 490 272 297 692 443 1098 380z" />
                      <Path d="M1255 2341 c-11 -5 -31 -21 -45 -36 -22 -23 -25 -36 -25 -96 0 -64 2 -71 33 -101 l32 -33 660 0 660 0 32 33 c31 30 33 37 33 102 0 65 -2 72 -33 102 l-32 33 -648 2 c-356 1 -656 -2 -667 -6z" />
                      <Path d="M1255 1901 c-11 -5 -31 -21 -45 -36 -22 -23 -25 -36 -25 -96 0 -64 2 -71 33 -101 l32 -33 405 0 405 0 32 33 c31 30 33 37 33 102 0 65 -2 72 -33 102 l-32 33 -393 2 c-215 1 -401 -2 -412 -6z" />
                      <Path d="M1255 1461 c-11 -5 -31 -21 -45 -36 -22 -23 -25 -36 -25 -96 0 -64 2 -71 33 -101 l32 -33 165 0 165 0 32 33 c31 30 33 37 33 102 0 65 -2 72 -33 102 l-32 33 -153 2 c-83 1 -161 -1 -172 -6z" />
                  </G>
          </SVG>,

    keywords: [__('quform', 'quform')],

    attributes: {
        id: {
            type: 'string',
            default: ''
        },
        name: {
            type: 'string',
            default: ''
        },
        show_title: {
            type: 'boolean',
            default: true
        },
        show_description: {
            type: 'boolean',
            default: true
        },
        popup: {
            type: 'boolean',
            default: false
        },
        content: {
            type: 'string',
            default: ''
        },
        width: {
            type: 'string',
            default: ''
        },
        values: {
            type: 'string',
            default: ''
        }
    },

    supports: {
        html: false
    },

    category: 'widgets',

    edit: (props) => {
        return <Fragment>
            <InspectorControls>
                <PanelBody title={__('Form Settings', 'quform')}>
                    <SelectControl
                        label={__('Select a form', 'quform')}
                        value={props.attributes.id}
                        options={quformBlockL10n.forms}
                        onChange={(value) => { props.setAttributes({ id: value, name: getFormName(value) }) }}
                    />
                    <ToggleControl
                        label={__('Show form title', 'quform')}
                        checked={props.attributes.show_title}
                        onChange={(value) => { props.setAttributes({ show_title: value }) }}
                    />
                    <ToggleControl
                        label={__('Show form description', 'quform')}
                        checked={props.attributes.show_description}
                        onChange={(value) => { props.setAttributes({ show_description: value }) }}
                    />
                    <ToggleControl
                        label={__('Popup form', 'quform')}
                        checked={props.attributes.popup}
                        onChange={(value) => { props.setAttributes({ popup: value }) }}
                    />
                    {props.attributes.popup &&
                        <TextareaControl
                            label={__('Content', 'quform')}
                            value={props.attributes.content}
                            help={__('The text or HTML to trigger the popup, shortcodes can also be used.', 'quform')}
                            onChange={(value) => { props.setAttributes({ content: value }) }}
                        />
                    }
                    {props.attributes.popup &&
                        <TextControl
                            label={__('Width (optional)', 'quform')}
                            value={props.attributes.width}
                            help={__('The width of the popup, any CSS width or number is accepted.', 'quform')}
                            onChange={(value) => { props.setAttributes({ width: value }) }}
                        />
                    }
                    <TextareaControl
                        label={[
                            __('Default values (optional)', 'quform'),
                            <a
                                href="https://support.themecatcher.net/quform-wordpress-v2/guides/customization/dynamic-default-value"
                                target="_blank"
                                title={__('View help and examples', 'quform')}
                                style={{ position: 'relative', top: '5px' }}
                            >
                                <Dashicon icon="editor-help" />
                            </a>
                        ]}
                        value={props.attributes.values}
                        help={__('Sets the default values of fields.', 'quform')}
                        onChange={(value) => { props.setAttributes({ values: value }) }}
                    />
                </PanelBody>
            </InspectorControls>
            <p>
                {props.attributes.id ? (
                    props.attributes.popup ? (
                        /* translators: %s: form name */
                        sprintf(__('Popup Form: %s', 'quform'), props.attributes.name)
                    ) : (
                        /* translators: %s: form name */
                        sprintf(__('Form: %s', 'quform'), props.attributes.name)
                    )
                ) : (
                    __('No form selected, choose a form in the block settings.', 'quform')
                )}
            </p>
          </Fragment>;
    },

    save: () => null
});

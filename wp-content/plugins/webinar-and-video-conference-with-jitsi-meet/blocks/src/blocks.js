const { InspectorControls } = wp.editor;
const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { PanelBody, TextControl, RangeControl, CheckboxControl } = wp.components;

const blockIcon = () => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      height="40"
      viewBox="0 0 49 28"
      fill="none"
    >
      <path
        fill-rule="evenodd"
        clip-rule="evenodd"
        d="M34.4757 22.0614V17.2941L43.0323 23.4061C43.5361 23.7659 44.1987 23.814 44.7491 23.5307C45.2996 23.2474 45.6455 22.6803 45.6455 22.0612V5.53492C45.6455 4.91587 45.2996 4.34873 44.7491 4.06545C44.1987 3.78219 43.5361 3.8303 43.0323 4.19012L34.4757 10.3021V5.53504C34.4757 2.61741 31.8784 0.577148 29.0998 0.577148H8.62239C5.84387 0.577148 3.24658 2.61741 3.24658 5.53504V22.0614C3.24658 24.979 5.84387 27.0193 8.62239 27.0193H29.0998C31.8784 27.0193 34.4757 24.979 34.4757 22.0614ZM20.3316 18.1759C17.8232 16.8906 15.7668 14.8431 14.4904 12.3347L16.4404 10.3847C16.6886 10.1365 16.7596 9.79081 16.6621 9.48059C16.3341 8.48784 16.1568 7.42421 16.1568 6.31627C16.1568 5.82876 15.758 5.4299 15.2704 5.4299H12.1681C11.6807 5.4299 11.2818 5.82876 11.2818 6.31627C11.2818 14.6393 18.027 21.3845 26.35 21.3845C26.8375 21.3845 27.2364 20.9856 27.2364 20.4981V17.4047C27.2364 16.9172 26.8375 16.5183 26.35 16.5183C25.2509 16.5183 24.1784 16.341 23.1857 16.0131C22.8755 15.9068 22.5209 15.9865 22.2816 16.2258L20.3316 18.1759ZM25.8625 5.42103L26.4918 6.04149L20.8989 11.6345H24.5773V12.5209H19.2591V7.20264H20.1455V11.0051L25.8625 5.42103Z"
        fill="#407BFF"
      />
    </svg>
  );
};

import './style.scss';
import './editor.scss';

class EditBlock extends Component {
  constructor(props) {
    super(props);
  }

  componentDidMount() {
    const {
      setAttributes,
      attributes: { name, domain, fromGlobal },
    } = this.props;
    const _newName = Math.random().toString(36).substring(2, 15);
    if (!name) {
      setAttributes({ name: _newName });
    }

    if( !domain ){
      setAttributes({ domain: jitsi.domain });
    }

  }

  render() {
    const { attributes, setAttributes } = this.props;

    const {
      name,
      domain,
      fromGlobal,
      width,
      height,
      audioMuted,
      videoMuted,
      screenSharing,
      invite,
    } = attributes;

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title={__('Settings')} initialOpen={true}>
            <TextControl
              label={__('Name')}
              value={name}
              onChange={(val) => setAttributes({ name: val })}
            />

            <TextControl
              label={__('Domain')}
              value={domain}
              onChange={(val) => setAttributes({ domain: val })}
              disabled={true}
            />

            <CheckboxControl
              label={__('Config from global')}
              checked={fromGlobal}
              onChange={(val) => {
                setAttributes({ fromGlobal: val });
                if (!fromGlobal) {
                  setAttributes({
                    width: parseInt(jitsi.meeting_width),
                    height: parseInt(jitsi.meeting_height),
                    audioMuted: parseInt(jitsi.startwithaudiomuted)
                      ? true
                      : false,
                    videoMuted: parseInt(jitsi.startwithvideomuted)
                      ? true
                      : false,
                    screenSharing: parseInt(jitsi.startscreensharing)
                      ? true
                      : false,
                    invite: parseInt(jitsi.invite) ? true : false,
                  });
                }
              }}
            />
            {!fromGlobal && (
              <div>
                <RangeControl
                  label={__('Width')}
                  value={width}
                  onChange={(val) => setAttributes({ width: val })}
                  min={100}
                  max={2000}
                  step={10}
                />
                <RangeControl
                  label={__('Height')}
                  value={height}
                  onChange={(val) => setAttributes({ height: val })}
                  min={100}
                  max={2000}
                  step={10}
                />
                <CheckboxControl
                  label={__('Start with audio muted')}
                  checked={audioMuted}
                  onChange={(val) => setAttributes({ audioMuted: val })}
                />
                <CheckboxControl
                  label={__('Start with video muted')}
                  checked={videoMuted}
                  onChange={(val) => setAttributes({ videoMuted: val })}
                />
                <CheckboxControl
                  label={__('Start with screen sharing')}
                  checked={screenSharing}
                  onChange={(val) => setAttributes({ screenSharing: val })}
                />
                <CheckboxControl
                  label={__('Enable Inviting')}
                  checked={invite}
                  onChange={(val) => setAttributes({ invite: val })}
                />
              </div>
            )}
          </PanelBody>
        </InspectorControls>
        <div
          className="jitsi-wrapper"
          data-name={name}
          data-domain={domain}
          data-width={width}
          data-height={height}
          data-mute={audioMuted}
          data-videomute={videoMuted}
          data-screen={screenSharing}
          data-invite={invite}
        >{`https://${domain}/${name}`}</div>
      </Fragment>
    );
  }
}

registerBlockType('jitsi-meet-wp/jitsi-meet', {
  title: __('Jitsi Meet', 'jitsi-meet-wp'),
  icon: blockIcon,
  category: 'embed',
  keywords: [
    __('jitsi', 'jitsi-meet-wp'),
    __('meeting', 'jitsi-meet-wp'),
    __('video', 'jitsi-meet-wp'),
    __('conference', 'jitsi-meet-wp'),
    __('zoom', 'jitsi-meet-wp'),
  ],
  attributes: {
    name: {
      type: 'string',
      default: '',
    },
    domain: {
      type: 'string',
      default: '',
    },
    width: {
      type: 'number',
      default: 1080,
    },
    height: {
      type: 'number',
      default: 720,
    },
    fromGlobal: {
      type: 'boolean',
      default: false,
    },
    audioMuted: {
      type: 'boolean',
      default: false,
    },
    videoMuted: {
      type: 'boolean',
      default: true,
    },
    screenSharing: {
      type: 'boolean',
      default: false,
    },
    invite: {
      type: 'boolean',
      default: true,
    },
  },
  edit: EditBlock,
  save: function (props) {
    const {
      name,
      domain,
      width,
      height,
      audioMuted,
      videoMuted,
      screenSharing,
      invite,
    } = props.attributes;

    return (
      <div
        className="jitsi-wrapper"
        data-name={name}
        data-domain={domain}
        data-width={width}
        data-height={height}
        data-mute={audioMuted}
        data-videomute={videoMuted}
        data-screen={screenSharing}
        data-invite={invite}
        style={{
          width: `${width}px`,
        }}
      ></div>
    );
  },
});

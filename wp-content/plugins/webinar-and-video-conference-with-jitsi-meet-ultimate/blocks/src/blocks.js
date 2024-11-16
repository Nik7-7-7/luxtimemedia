const { InspectorControls } = wp.editor;
const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { PanelBody, TextControl, RangeControl, CheckboxControl, ToggleControl, SelectControl } = wp.components;
const { withSelect } = wp.data;

const blockIcon = () => {
	return (
		<svg xmlns="http://www.w3.org/2000/svg" height="40" viewBox="0 0 49 28" fill="none">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M34.4757 22.0614V17.2941L43.0323 23.4061C43.5361 23.7659 44.1987 23.814 44.7491 23.5307C45.2996 23.2474 45.6455 22.6803 45.6455 22.0612V5.53492C45.6455 4.91587 45.2996 4.34873 44.7491 4.06545C44.1987 3.78219 43.5361 3.8303 43.0323 4.19012L34.4757 10.3021V5.53504C34.4757 2.61741 31.8784 0.577148 29.0998 0.577148H8.62239C5.84387 0.577148 3.24658 2.61741 3.24658 5.53504V22.0614C3.24658 24.979 5.84387 27.0193 8.62239 27.0193H29.0998C31.8784 27.0193 34.4757 24.979 34.4757 22.0614ZM20.3316 18.1759C17.8232 16.8906 15.7668 14.8431 14.4904 12.3347L16.4404 10.3847C16.6886 10.1365 16.7596 9.79081 16.6621 9.48059C16.3341 8.48784 16.1568 7.42421 16.1568 6.31627C16.1568 5.82876 15.758 5.4299 15.2704 5.4299H12.1681C11.6807 5.4299 11.2818 5.82876 11.2818 6.31627C11.2818 14.6393 18.027 21.3845 26.35 21.3845C26.8375 21.3845 27.2364 20.9856 27.2364 20.4981V17.4047C27.2364 16.9172 26.8375 16.5183 26.35 16.5183C25.2509 16.5183 24.1784 16.341 23.1857 16.0131C22.8755 15.9068 22.5209 15.9865 22.2816 16.2258L20.3316 18.1759ZM25.8625 5.42103L26.4918 6.04149L20.8989 11.6345H24.5773V12.5209H19.2591V7.20264H20.1455V11.0051L25.8625 5.42103Z" fill="#407BFF"/>
        </svg>
	)
}

import './style.scss';
import './editor.scss';

class EditBlock extends Component{
	constructor(props) {
		super( props );
        this.state = {
            postArr: []
        }
    }
    
    componentDidMount() {
        const { setAttributes, attributes: { name, fromGlobal } } = this.props;
        const _newName = Math.random().toString(36).substring(2, 15);
        if ( !name ) {
            setAttributes({ name: _newName });
        }          
    }

    toggleFromPost(){
        if (this.props.posts && this.state.postArr.length < 1) {
            let options = [];
            this.props.posts.forEach((post) => {
                options.push(
                    {
                        value: post.id,
                        label: post.title.rendered
                    });
            });
            this.setState({postArr: options})
        } 
        const { setAttributes, attributes: { formPosts, postId } } = this.props;
        setAttributes({ formPosts: !formPosts });
        if(!postId && this.props.posts.length > 0){
            setAttributes({ postId: this.props.posts[0].id, postTitle: this.props.posts[0].title.rendered });
        }
    }

    previewMock(){
        return(
            <div className="jitsi-preview-people-mock">
                <div><div style={{backgroundImage: `url(${jitsi_pro.plugin_url}assets/img/01.png)`}}></div></div>  
                <div><div style={{backgroundImage: `url(${jitsi_pro.plugin_url}assets/img/02.png)`}}></div></div>  
                <div><div style={{backgroundImage: `url(${jitsi_pro.plugin_url}assets/img/03.png)`}}></div></div>  
                <div><div style={{backgroundImage: `url(${jitsi_pro.plugin_url}assets/img/04.png)`}}></div></div>  
                <div><div style={{backgroundImage: `url(${jitsi_pro.plugin_url}assets/img/05.png)`}}></div></div>  
                <div><div style={{backgroundImage: `url(${jitsi_pro.plugin_url}assets/img/06.png)`}}></div></div>  
                <div><div style={{backgroundImage: `url(${jitsi_pro.plugin_url}assets/img/07.png)`}}></div></div>  
                <div><div style={{backgroundImage: `url(${jitsi_pro.plugin_url}assets/img/08.png)`}}></div></div>  
            </div>
        )
    }

	render(){
		const {
			attributes,
			setAttributes,
            posts
        } = this.props;
        
        const {
            formPosts,
            postId,
            name,
            width,
            height,
            fromGlobal,
            enablewelcomepage,
            startaudioonly,
            startaudiomuted,
            startwithaudiomuted,
            startsilent,
            resolution,
            maxfullresolutionparticipant,
            disablesimulcast,
            startvideomuted,
            startwithvideomuted,
            startscreensharing,
            filerecordingsenabled,
            transcribingenabled,
            livestreamingenabled,
            invite
        } = attributes;

		return(
			<Fragment>
                <InspectorControls>
                    <PanelBody title={__('Settings')} initialOpen={true}>
                        <ToggleControl
                            label={__("From Post?")}
                            checked={formPosts}
                            onChange={() => this.toggleFromPost()}
                        />
                        {formPosts &&
                            <SelectControl
                                label={__("Select Post")}
                                value={ postId }
                                options={this.state.postArr}
                                onChange={ ( val ) => setAttributes({postId: val, postTitle: posts.find(obj => obj.id == val).title.rendered}) }
                            />
                        }
                        {!formPosts &&
                            <TextControl
                                label={__('Name')}
                                value={ name }
                                onChange={ ( val ) => setAttributes({name: val}) }
                            />
                        }  
                        <CheckboxControl
                            label={__('Config from global')}
                            checked={ fromGlobal }
                            onChange={ val => {
                                setAttributes({fromGlobal: val});
                                if(!fromGlobal){
                                    setAttributes({
                                        width: parseInt(jitsi_pro.meeting_width), 
                                        height: parseInt(jitsi_pro.meeting_height),
                                        enablewelcomepage: parseInt(jitsi_pro.enablewelcomepage) ? true : false,
                                        startaudioonly: parseInt(jitsi_pro.startaudioonly) ? true : false,
                                        startaudiomuted: parseInt(jitsi_pro.startaudiomuted) ? parseInt(jitsi_pro.startaudiomuted) : 10,
                                        startwithaudiomuted: parseInt(jitsi_pro.startwithaudiomuted) ? true : false,
                                        startsilent: parseInt(jitsi_pro.startsilent) ? true : false,
                                        resolution: parseInt(jitsi_pro.resolution) ? parseInt(jitsi_pro.resolution) : 720,
                                        maxfullresolutionparticipant: parseInt(jitsi_pro.maxfullresolutionparticipant) ? parseInt(jitsi_pro.maxfullresolutionparticipant) : 2,
                                        disablesimulcast: parseInt(jitsi_pro.disablesimulcast) ? true : false,
                                        startvideomuted: parseInt(jitsi_pro.startvideomuted) ? true : false,
                                        startwithvideomuted: parseInt(jitsi_pro.startwithvideomuted) ? parseInt(jitsi_pro.startwithvideomuted) : 10,
                                        startscreensharing: parseInt(jitsi_pro.startscreensharing) ? true : false,
                                        filerecordingsenabled: parseInt(jitsi_pro.filerecordingsenabled) ? true : false,
                                        transcribingenabled: parseInt(jitsi_pro.transcribingenabled) ? true : false,
                                        livestreamingenabled: parseInt(jitsi_pro.livestreamingenabled) ? true : false,
                                        invite: parseInt(jitsi_pro.invite) ? true : false,
                                    });
                                } 
                            }}
                        />      
                        {!fromGlobal && (
                            <div>
                                <RangeControl
                                    label={__('Width')}
                                    value={ width }
                                    onChange={ ( val ) => setAttributes({width: val}) }
                                    min={ 100 }
                                    max={ 2000 }
                                    step={ 10 }
                                />
                                <RangeControl
                                    label={__('Height')}
                                    value={ height }
                                    onChange={ ( val ) => setAttributes({height: val}) }
                                    min={ 100 }
                                    max={ 2000 }
                                    step={ 10 }
                                />
                                <CheckboxControl
                                    label={__('Welcome Page')}
                                    checked={ enablewelcomepage }
                                    onChange={ val => setAttributes({enablewelcomepage: val}) }
                                />
                                <CheckboxControl
                                    label={__('Start Audio Only')}
                                    checked={ startaudioonly }
                                    onChange={ val => setAttributes({startaudioonly: val}) }
                                />
                                <RangeControl
                                    label={__('Audio Muted After')}
                                    value={ startaudiomuted }
                                    onChange={ ( val ) => setAttributes({startaudiomuted: val}) }
                                    min={ 0 }
                                    max={ 20 }
                                    step={ 1 }
                                />
                                <CheckboxControl
                                    label={__('Yourself Muted')}
                                    checked={ startwithaudiomuted }
                                    onChange={ val => setAttributes({startwithaudiomuted: val}) }
                                />
                                <CheckboxControl
                                    label={__('Start Silent')}
                                    checked={ startsilent }
                                    onChange={ val => setAttributes({startsilent: val}) }
                                />
                                <SelectControl
                                    label={__("Resolution")}
                                    value={ resolution }
                                    options={[
                                        { label: __('480p'), value: 480 },
                                        { label: __('720p'), value: 720 },
                                        { label: __('1080p'), value: 1080 },
                                        { label: __('1440p'), value: 1440 },
                                        { label: __('2160p'), value: 2160 },
                                        { label: __('4320p'), value: 4320 }
                                    ]}
                                    onChange={ ( val ) => setAttributes({ resolution: val }) }
                                />
                                <RangeControl
                                    label={__('Max Full Resolution')}
                                    value={ maxfullresolutionparticipant }
                                    onChange={ ( val ) => setAttributes({maxfullresolutionparticipant: val}) }
                                    min={ 0 }
                                    max={ 20 }
                                    step={ 1 }
                                />
                                <CheckboxControl
                                    label={__('Start Video Muted')}
                                    checked={ startvideomuted }
                                    onChange={ val => setAttributes({ startvideomuted: val }) }
                                />
                                <RangeControl
                                    label={__('Video Muted After')}
                                    value={ startwithvideomuted }
                                    onChange={ ( val ) => setAttributes({startwithvideomuted: val}) }
                                    min={ 0 }
                                    max={ 50 }
                                    step={ 1 }
                                />
                                <CheckboxControl
                                    label={__('Start Screen Sharing')}
                                    checked={ startscreensharing }
                                    onChange={ val => setAttributes({ startscreensharing: val }) }
                                />
                                <CheckboxControl
                                    label={__('Enable Recording')}
                                    checked={ filerecordingsenabled }
                                    onChange={ val => setAttributes({ filerecordingsenabled: val }) }
                                />
                                <CheckboxControl
                                    label={__('Enable Transcription')}
                                    checked={ transcribingenabled }
                                    onChange={ val => setAttributes({ transcribingenabled: val }) }
                                />
                                <CheckboxControl
                                    label={__('Enable Livestream')}
                                    checked={ livestreamingenabled }
                                    onChange={ val => setAttributes({ livestreamingenabled: val }) }
                                />
                                <CheckboxControl
                                    label={__('Simulcast')}
                                    checked={ disablesimulcast }
                                    onChange={ val => setAttributes({ disablesimulcast: val }) }
                                />
                                <CheckboxControl
                                    label={__('Enable Inviting')}
                                    checked={ invite }
                                    onChange={ val => setAttributes({ invite: val }) }
                                />
                            </div>
                        )} 
                    </PanelBody>                    
                </InspectorControls>
                <div id="meeting-ui-preview" className="preview-success preview-block">
                    {this.previewMock()}
                </div>
            </Fragment>
		);
	}
}

registerBlockType('jitsi-pro/jitsi-pro', {
  title: __('Jitsi Pro', 'jitsi-pro'),
  icon: blockIcon,
  category: 'embed',
  keywords: [
    __( 'jitsi', 'jitsi-pro' ),
    __( 'meeting', 'jitsi-pro' ),
    __( 'video', 'jitsi-pro' ),
    __( 'conference', 'jitsi-pro' ),
    __( 'zoom', 'jitsi-pro' )
  ],
  attributes: {
    formPosts: {
        type: 'boolean',
        default: false
    },
    postId: {
        type: 'number',
        default: ''
    },
    postTitle: {
        type: 'string',
        default: ''
    },
    name: {
        type: 'string',
        default: ''
    },
    width: {
        type: 'number',
        default: 1080
    },
    height: {
        type: 'number',
        default: 720
    },
    fromGlobal: {
        type: 'boolean',
        default: false
    },
    enablewelcomepage: {
        type: 'boolean',
        default: true
    },
    startaudioonly: {
        type: 'boolean',
        default: false
    },
    startaudiomuted: {
        type: 'number',
        default: 10
    },
    startwithaudiomuted: {
        type: 'boolean',
        default: false
    },
    startsilent: {
        type: 'boolean',
        default: false
    },
    resolution: {
        type: 'number',
        default: 720
    },
    maxfullresolutionparticipant: {
        type: 'number',
        default: 2
    },
    startvideomuted: {
        type: 'boolean',
        default: true
    },
    startwithvideomuted: {
        type: 'number',
        default: 10
    },
    startscreensharing: {
        type: 'boolean',
        default: false
    },
    filerecordingsenabled: {
        type: 'boolean',
        default: false
    },
    transcribingenabled: {
        type: 'boolean',
        default: false
    },
    livestreamingenabled: {
        type: 'boolean',
        default: false
    },
    disablesimulcast: {
        type: 'boolean',
        default: false
    },
    invite: {
        type: 'boolean',
        default: true
    }
  },
  edit: withSelect((select) => {
    return {
        posts: select('core').getEntityRecords('postType', 'meeting', {per_page: -1}),
    };
  })(EditBlock),
  save: function( props ) {
		const {
            formPosts,
            postId,
            postTitle,
            name,
            width,
            height,
            enablewelcomepage,
            startaudioonly,
            startaudiomuted,
            startwithaudiomuted,
            startsilent,
            resolution,
            maxfullresolutionparticipant,
            disablesimulcast,
            startvideomuted,
            startwithvideomuted,
            startscreensharing,
            filerecordingsenabled,
            transcribingenabled,
            livestreamingenabled,
            invite
		} = props.attributes;

		return (
            <div>
                <div 
                    className="jitsi-wrapper" 
                    data-name={formPosts ? postTitle : name} 
                    data-width={width} 
                    data-height={height}
                    data-startaudioonly={startaudioonly}
                    data-startaudiomuted={startaudiomuted}
                    data-startwithaudiomuted={startwithaudiomuted}
                    data-startsilent={startsilent}
                    data-resolution={resolution}
                    data-maxfullresolutionparticipant={maxfullresolutionparticipant}
                    data-disablesimulcast={disablesimulcast}
                    data-startvideomuted={startvideomuted}
                    data-startwithvideomuted={startwithvideomuted}
                    data-startscreensharing={startscreensharing}
                    data-filerecordingsenabled={filerecordingsenabled}
                    data-transcribingenabled={transcribingenabled}
                    data-livestreamingenabled={livestreamingenabled}
                    data-enablewelcomepage={enablewelcomepage}
                    data-invite={invite}
                    style={{
                        width: `${width}px`
                    }}
                ></div>
            </div>
		);
	}
});

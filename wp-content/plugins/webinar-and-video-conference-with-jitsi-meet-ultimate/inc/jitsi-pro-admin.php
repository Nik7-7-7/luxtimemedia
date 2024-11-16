<?php
if (! defined('ABSPATH')) {
    exit;
}

class Jitsi_Settings
{
    public $settings = array();
    public $sections = array();
    public $fields = array();

    public function __construct()
    {
        add_action('admin_menu', array( $this, 'jitsi_pro_admin_menu_page' ));
        add_action('admin_init', array( $this, 'registerCustomFields' ));
    }

    public function jitsi_pro_admin_menu_page()
    {
        add_submenu_page(
            'jitsi-meet',
            __('All Meetings', 'jitsi-pro'),
            __('Meetings', 'jitsi-pro'),
            'manage_options',
            'edit.php?post_type=meeting',
            null
        );

		$meet_instance = new Jitsi_Meet_WP();
        $is_ultimate = $meet_instance->is_ultimate_active();
		if($is_ultimate){
			add_menu_page(
				__('Jitsi Meet Ultimate', 'jitsi-pro'),
				__('Jitsi Meet Ultimate', 'jitsi-pro'),
				'manage_options',
				'jitsi-meet',
				null,
				'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="512" viewBox="0 0 49 28" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M34.4757 22.0614V17.2941L43.0323 23.4061C43.5361 23.7659 44.1987 23.814 44.7491 23.5307C45.2996 23.2474 45.6455 22.6803 45.6455 22.0612V5.53492C45.6455 4.91587 45.2996 4.34873 44.7491 4.06545C44.1987 3.78219 43.5361 3.8303 43.0323 4.19012L34.4757 10.3021V5.53504C34.4757 2.61741 31.8784 0.577148 29.0998 0.577148H8.62239C5.84387 0.577148 3.24658 2.61741 3.24658 5.53504V22.0614C3.24658 24.979 5.84387 27.0193 8.62239 27.0193H29.0998C31.8784 27.0193 34.4757 24.979 34.4757 22.0614ZM20.3316 18.1759C17.8232 16.8906 15.7668 14.8431 14.4904 12.3347L16.4404 10.3847C16.6886 10.1365 16.7596 9.79081 16.6621 9.48059C16.3341 8.48784 16.1568 7.42421 16.1568 6.31627C16.1568 5.82876 15.758 5.4299 15.2704 5.4299H12.1681C11.6807 5.4299 11.2818 5.82876 11.2818 6.31627C11.2818 14.6393 18.027 21.3845 26.35 21.3845C26.8375 21.3845 27.2364 20.9856 27.2364 20.4981V17.4047C27.2364 16.9172 26.8375 16.5183 26.35 16.5183C25.2509 16.5183 24.1784 16.341 23.1857 16.0131C22.8755 15.9068 22.5209 15.9865 22.2816 16.2258L20.3316 18.1759ZM25.8625 5.42103L26.4918 6.04149L20.8989 11.6345H24.5773V12.5209H19.2591V7.20264H20.1455V11.0051L25.8625 5.42103Z" fill="#407BFF"/></svg>'),
				40
			);
		} else {
			add_menu_page(
				__('Jitsi Meet Pro', 'jitsi-pro'),
				__('Jitsi Meet Pro', 'jitsi-pro'),
				'manage_options',
				'jitsi-meet',
				null,
				'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="512" viewBox="0 0 49 28" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M34.4757 22.0614V17.2941L43.0323 23.4061C43.5361 23.7659 44.1987 23.814 44.7491 23.5307C45.2996 23.2474 45.6455 22.6803 45.6455 22.0612V5.53492C45.6455 4.91587 45.2996 4.34873 44.7491 4.06545C44.1987 3.78219 43.5361 3.8303 43.0323 4.19012L34.4757 10.3021V5.53504C34.4757 2.61741 31.8784 0.577148 29.0998 0.577148H8.62239C5.84387 0.577148 3.24658 2.61741 3.24658 5.53504V22.0614C3.24658 24.979 5.84387 27.0193 8.62239 27.0193H29.0998C31.8784 27.0193 34.4757 24.979 34.4757 22.0614ZM20.3316 18.1759C17.8232 16.8906 15.7668 14.8431 14.4904 12.3347L16.4404 10.3847C16.6886 10.1365 16.7596 9.79081 16.6621 9.48059C16.3341 8.48784 16.1568 7.42421 16.1568 6.31627C16.1568 5.82876 15.758 5.4299 15.2704 5.4299H12.1681C11.6807 5.4299 11.2818 5.82876 11.2818 6.31627C11.2818 14.6393 18.027 21.3845 26.35 21.3845C26.8375 21.3845 27.2364 20.9856 27.2364 20.4981V17.4047C27.2364 16.9172 26.8375 16.5183 26.35 16.5183C25.2509 16.5183 24.1784 16.341 23.1857 16.0131C22.8755 15.9068 22.5209 15.9865 22.2816 16.2258L20.3316 18.1759ZM25.8625 5.42103L26.4918 6.04149L20.8989 11.6345H24.5773V12.5209H19.2591V7.20264H20.1455V11.0051L25.8625 5.42103Z" fill="#407BFF"/></svg>'),
				40
			);
		}
        

        add_submenu_page(
            'jitsi-meet',
            __('APP Settings', 'jitsi-pro'),
            __('API Settings', 'jitsi-pro'),
            'manage_options',
            'jitsi-pro-apis',
            [ $this, 'jitsi_pro_manu_page_apis_output' ]
        );

		add_submenu_page(
			null,
			__('Jitsi Meet Welcome', 'jitsi-pro'),
			null,
			'manage_options',
			'jitsi-meet-welcome',
			[ $this, 'jitsi_pro_manu_page_welcome_output' ]
		);
    
        add_submenu_page(
            'jitsi-meet',
            __('Admin Settings', 'jitsi-pro'),
            __('Admin Settings', 'jitsi-pro'),
            'manage_options',
            'jitsi-pro-admin',
            [ $this, 'jitsi_pro_manu_page_admin_output' ]
        );

        add_submenu_page(
            'jitsi-meet',
            __('Configurations', 'jitsi-pro'),
            __('Configurations', 'jitsi-pro'),
            'manage_options',
            'jitsi-pro-config',
            [ $this, 'jitsi_pro_manu_page_config_output' ]
        );

        add_submenu_page(
            'jitsi-meet',
            __('Audio Settings', 'jitsi-pro'),
            __('Audio Settings', 'jitsi-pro'),
            'manage_options',
            'jitsi-pro-audio',
            [ $this, 'jitsi_pro_manu_page_audio_output' ]
        );

        add_submenu_page(
            'jitsi-meet',
            __('Video Settings', 'jitsi-pro'),
            __('Video Settings', 'jitsi-pro'),
            'manage_options',
            'jitsi-pro-video',
            [ $this, 'jitsi_pro_manu_page_video_output' ]
        );
    }

    public function jitsi_pro_menu_page_tab_link($link = 'apis')
    {
        $arroftab = array(
            'apis' 			=> sprintf('<svg size="24" viewBox="0 0 24 24" class="sc-htoDjs brCwHT"><g><path fill-rule="evenodd" d="M9 12c-2.7614 0-5-2.2386-5-5s2.2386-5 5-5 5 2.2386 5 5c0 1.019-.3049 1.967-.8284 2.7574L20 16.5858c.3111.3111.3744.7763.1897 1.15a.9954.9954 0 0 1-.24.3855l-2.8284 2.8284c-.3905.3906-1.0237.3906-1.4142 0-.3905-.3905-.3905-1.0236 0-1.4142l2.2071-2.2071-1.5858-1.5858-1.0858 1.0858c-.3905.3906-1.0236.3906-1.4142 0-.3905-.3905-.3905-1.0237 0-1.4142l1.0858-1.0858-3.1568-3.1568C10.967 11.6951 10.0191 12 9 12zm0-2c1.6569 0 3-1.3431 3-3s-1.3431-3-3-3-3 1.3431-3 3 1.3431 3 3 3z" clip-rule="evenodd"></path></g></svg><label><span class="jitsi-tab-title">%1$s</span><span class="jitsi-tab-desc">%2$s</span></label>', __('API Settings', 'jitsi-pro'), __('Authenticate with JAAS', 'jitsi-pro')),
            'admin' 		=> sprintf('<svg size="24" viewBox="0 0 24 24" class="sc-htoDjs brCwHT"><g><path fill-rule="evenodd" d="M20.5 17h-3.6762c-.1155-.7365-.2893-1.4027-.5254-2h3.6952A4.7212 4.7212 0 0 0 20 14.75c0-3.6983-.8752-4.75-4-4.75-.8127 0-1.4732.0711-2.0058.2415A4.9715 4.9715 0 0 0 14 10c0-1.3984-.574-2.6626-1.4993-3.57C12.538 4.5293 14.0904 3 16 3c1.933 0 3.5 1.567 3.5 3.5a3.4832 3.4832 0 0 1-.5913 1.9473C21.1785 9.3075 22 11.4084 22 14.75c0 1.5-.5 2.25-1.5 2.25zm-3-10.5c0 .8284-.6716 1.5-1.5 1.5s-1.5-.6716-1.5-1.5S15.1716 5 16 5s1.5.6716 1.5 1.5zM3.75 22C2.5833 22 2 21.1667 2 19.5c0-3.8103 1.0093-6.1687 3.8183-7.0754A3.9824 3.9824 0 0 1 5 10c0-2.2091 1.7909-4 4-4 2.2091 0 4 1.7909 4 4a3.982 3.982 0 0 1-.8184 2.4246C14.9907 13.3313 16 15.6897 16 19.5c0 1.6667-.5833 2.5-1.75 2.5H3.75zM11 10c0 1.1046-.8954 2-2 2s-2-.8954-2-2 .8954-2 2-2 2 .8954 2 2zm3 9.5c0 .2139-.0122.3804-.0272.5H4.0272C4.0122 19.8804 4 19.7139 4 19.5 4 15.2936 5.1303 14 9 14s5 1.2936 5 5.5z" clip-rule="evenodd"></path></g></svg><label><span class="jitsi-tab-title">%1$s</span><span class="jitsi-tab-desc">%2$s</span></label>', __('Admin Settings', 'jitsi-pro'), __('Username, email, avatar etc.', 'jitsi-pro')),
            'config' 		=> sprintf('<svg size="24" viewBox="0 0 512 512"><path d="M272.066 512h-32.133c-25.989 0-47.134-21.144-47.134-47.133v-10.871a206.698 206.698 0 0 1-32.097-13.323l-7.704 7.704c-18.659 18.682-48.548 18.134-66.665-.007l-22.711-22.71c-18.149-18.129-18.671-48.008.006-66.665l7.698-7.698A206.714 206.714 0 0 1 58.003 319.2h-10.87C21.145 319.2 0 298.056 0 272.067v-32.134C0 213.944 21.145 192.8 47.134 192.8h10.87a206.755 206.755 0 0 1 13.323-32.097L63.623 153c-18.666-18.646-18.151-48.528.006-66.665l22.713-22.712c18.159-18.184 48.041-18.638 66.664.006l7.697 7.697A206.893 206.893 0 0 1 192.8 58.003v-10.87C192.8 21.144 213.944 0 239.934 0h32.133C298.056 0 319.2 21.144 319.2 47.133v10.871a206.698 206.698 0 0 1 32.097 13.323l7.704-7.704c18.659-18.682 48.548-18.134 66.665.007l22.711 22.71c18.149 18.129 18.671 48.008-.006 66.665l-7.698 7.698a206.714 206.714 0 0 1 13.323 32.097h10.87c25.989 0 47.134 21.144 47.134 47.133v32.134c0 25.989-21.145 47.133-47.134 47.133h-10.87a206.755 206.755 0 0 1-13.323 32.097l7.704 7.704c18.666 18.646 18.151 48.528-.006 66.665l-22.713 22.712c-18.159 18.184-48.041 18.638-66.664-.006l-7.697-7.697a206.893 206.893 0 0 1-32.097 13.323v10.871c0 25.987-21.144 47.131-47.134 47.131zM165.717 409.17a176.812 176.812 0 0 0 45.831 19.025 14.999 14.999 0 0 1 11.252 14.524v22.148c0 9.447 7.687 17.133 17.134 17.133h32.133c9.447 0 17.134-7.686 17.134-17.133v-22.148a14.999 14.999 0 0 1 11.252-14.524 176.812 176.812 0 0 0 45.831-19.025 15 15 0 0 1 18.243 2.305l15.688 15.689c6.764 6.772 17.626 6.615 24.224.007l22.727-22.726c6.582-6.574 6.802-17.438.006-24.225l-15.695-15.695a15 15 0 0 1-2.305-18.242 176.78 176.78 0 0 0 19.024-45.831 15 15 0 0 1 14.524-11.251h22.147c9.447 0 17.134-7.686 17.134-17.133v-32.134c0-9.447-7.687-17.133-17.134-17.133H442.72a15 15 0 0 1-14.524-11.251 176.815 176.815 0 0 0-19.024-45.831 15 15 0 0 1 2.305-18.242l15.689-15.689c6.782-6.774 6.605-17.634.006-24.225l-22.725-22.725c-6.587-6.596-17.451-6.789-24.225-.006l-15.694 15.695a15 15 0 0 1-18.243 2.305 176.812 176.812 0 0 0-45.831-19.025 14.999 14.999 0 0 1-11.252-14.524v-22.15c0-9.447-7.687-17.133-17.134-17.133h-32.133c-9.447 0-17.134 7.686-17.134 17.133v22.148a14.999 14.999 0 0 1-11.252 14.524 176.812 176.812 0 0 0-45.831 19.025 15.002 15.002 0 0 1-18.243-2.305l-15.688-15.689c-6.764-6.772-17.627-6.615-24.224-.007l-22.727 22.726c-6.582 6.574-6.802 17.437-.006 24.225l15.695 15.695a15 15 0 0 1 2.305 18.242 176.78 176.78 0 0 0-19.024 45.831 15 15 0 0 1-14.524 11.251H47.134C37.687 222.8 30 230.486 30 239.933v32.134c0 9.447 7.687 17.133 17.134 17.133h22.147a15 15 0 0 1 14.524 11.251 176.815 176.815 0 0 0 19.024 45.831 15 15 0 0 1-2.305 18.242l-15.689 15.689c-6.782 6.774-6.605 17.634-.006 24.225l22.725 22.725c6.587 6.596 17.451 6.789 24.225.006l15.694-15.695c3.568-3.567 10.991-6.594 18.244-2.304z"/><path d="M256 367.4c-61.427 0-111.4-49.974-111.4-111.4S194.573 144.6 256 144.6 367.4 194.574 367.4 256 317.427 367.4 256 367.4zm0-192.8c-44.885 0-81.4 36.516-81.4 81.4s36.516 81.4 81.4 81.4 81.4-36.516 81.4-81.4-36.515-81.4-81.4-81.4z"/></svg><label><span class="jitsi-tab-title">%1$s</span><span class="jitsi-tab-desc">%2$s</span></label>', __('Configurations', 'jitsi-pro'), __('Width, height, recording etc...', 'jitsi-pro')),
            'audio' 		=> sprintf('<svg size="24" viewBox="0 0 493.3 493.3"><path d="M384.1 372.9c-8.3 0-15 6.7-15 15v66c0 7.3-7.9 11.8-14.2 8.1 -1.6-1-125.1-74.8-180.9-108.2 0-9.5 0-203.4 0-214.2 55.9-33.4 179.3-107.2 180.9-108.2 6.2-3.7 14.2 0.7 14.2 8.1v73.3c0 8.3 6.7 15 15 15 8.3 0 15-6.7 15-15v-73.3c0-30.6-33.3-49.5-59.6-33.8l-184.7 110.4H52.9c-24.4 0-44.3 19.9-44.3 44.3v172.6c0 24.4 19.9 44.3 44.3 44.3h102l184.7 110.4c26.3 15.7 59.6-3.2 59.6-33.8v-66C399.1 379.6 392.4 372.9 384.1 372.9zM144 347.3h-91.1c-7.9 0-14.3-6.4-14.3-14.3v-172.6c0-7.9 6.4-14.3 14.3-14.3h91.1V347.3z"/><path d="M469.7 231.7h-17.5c-1.8-8.1-5-15.7-9.3-22.5 0 0 0 0 0 0l12.4-12.4c5.9-5.9 5.9-15.4 0-21.2 -5.9-5.9-15.4-5.9-21.2 0l-12.4 12.4c0 0 0 0 0 0 -6.8-4.3-14.3-7.5-22.4-9.3 0 0 0 0 0 0v-17.5c0-8.3-6.7-15-15-15s-15 6.7-15 15v17.5c-8.1 1.8-15.7 5-22.5 9.3l-12.4-12.4c-5.9-5.9-15.4-5.9-21.2 0 -5.9 5.9-5.9 15.4 0 21.2l12.4 12.4c-4.3 6.8-7.5 14.4-9.3 22.5h-17.5c-8.3 0-15 6.7-15 15s6.7 15 15 15h17.5c1.8 8.1 5 15.7 9.3 22.5l-12.4 12.4c-5.9 5.9-5.9 15.4 0 21.2 5.9 5.9 15.4 5.9 21.2 0l12.4-12.4c6.8 4.3 14.3 7.6 22.5 9.3v17.5c0 8.3 6.7 15 15 15s15-6.7 15-15v-17.5c0 0 0 0 0 0 8.1-1.8 15.7-5 22.5-9.3 0 0 0 0 0 0l12.4 12.4c5.9 5.9 15.4 5.9 21.2 0 5.9-5.9 5.9-15.4 0-21.2l-12.4-12.4c0 0 0 0 0 0 4.3-6.8 7.6-14.4 9.3-22.5h17.5c8.3 0 15-6.7 15-15S477.9 231.7 469.7 231.7zM423.8 246.7c0 21.8-17.8 39.5-39.6 39.6 0 0 0 0 0 0 0 0 0 0 0 0 -21.8 0-39.6-17.8-39.6-39.6s17.7-39.6 39.6-39.6c0 0 0 0 0 0 0 0 0 0 0 0 21.8 0 39.6 17.8 39.6 39.6 0 0 0 0 0 0.1S423.8 246.7 423.8 246.7z"/><circle cx="383.7" cy="246.7" r="18.6"/></svg><label><span class="jitsi-tab-title">%1$s</span><span class="jitsi-tab-desc">%2$s</span></label>', __('Audio Settings', 'jitsi-pro'), __('Initial audio configurations...', 'jitsi-pro')),
            'video' 		=> sprintf('<svg size="24" viewBox="0 0 512 512"><path d="m467 18.3h-422c-24.8 0-45 20.2-45 45v318.9c0 24.8 20.2 45 45 45h196v36.6h-51.8c-8.3 0-15 6.7-15 15s6.7 15 15 15h133.6c8.3 0 15-6.7 15-15s-6.7-15-15-15h-51.8v-36.6h196c24.8 0 45-20.2 45-45v-318.9c0-24.8-20.2-45-45-45zm0 378.9h-422c-8.3 0-15-6.7-15-15v-58.6h452v58.6c0 8.3-6.7 15-15 15zm-422-348.9h422c8.3 0 15 6.7 15 15v230.3h-452v-230.3c0-8.3 6.7-15 15-15z"/><path d="m161.1 205.2c1.5 4.6 3.4 9.1 5.6 13.4-3.3 7.5-1.9 16.7 4.3 22.8l17.4 17.4c6.2 6.2 15.3 7.6 22.8 4.3 4.3 2.2 8.8 4 13.4 5.6 3 7.7 10.4 13.1 19.2 13.1h24.6c8.7 0 16.2-5.4 19.2-13.1 4.6-1.5 9.1-3.4 13.4-5.6 7.5 3.3 16.7 1.9 22.8-4.3l17.4-17.4c6.2-6.2 7.6-15.3 4.3-22.8 2.2-4.3 4-8.8 5.6-13.4 7.7-3 13.1-10.4 13.1-19.2v-24.6c0-8.7-5.4-16.2-13.1-19.2-1.5-4.6-3.4-9.1-5.6-13.4 3.3-7.5 1.9-16.7-4.3-22.8l-17.4-17.4c-6.2-6.2-15.3-7.6-22.8-4.3-4.3-2.2-8.8-4-13.4-5.6-3-7.7-10.4-13.1-19.2-13.1h-24.6c-8.7 0-16.2 5.4-19.2 13.1-4.6 1.5-9.1 3.4-13.4 5.6-7.5-3.3-16.7-1.9-22.8 4.3l-17.4 17.4c-6.2 6.2-7.6 15.3-4.3 22.8-2.2 4.3-4 8.8-5.6 13.4-7.7 3-13.1 10.4-13.1 19.2v24.6c0 8.7 5.4 16.2 13.1 19.2zm16.9-36.6c5-2.6 8.9-7.2 10.4-12.9 1.6-5.9 3.9-11.6 7-16.9 3-5.1 3.5-11.1 1.8-16.5l7.3-7.3c5.4 1.7 11.4 1.2 16.5-1.8 5.3-3.1 11-5.4 16.9-7 5.7-1.5 10.3-5.4 12.9-10.4h10.3c2.6 5 7.2 8.9 12.9 10.4 5.9 1.6 11.6 3.9 16.9 7 5.1 3 11.1 3.5 16.5 1.8l7.3 7.3c-1.7 5.4-1.2 11.4 1.8 16.5 3.1 5.3 5.4 11 7 16.9 1.5 5.7 5.4 10.3 10.4 12.9v10.3c-5 2.6-8.9 7.2-10.4 12.9-1.6 5.9-3.9 11.6-7 16.9-3 5.1-3.5 11.1-1.8 16.5l-7.3 7.3c-5.4-1.7-11.4-1.2-16.5 1.8-5.3 3.1-11 5.4-16.9 7-5.7 1.5-10.3 5.4-12.9 10.4h-10.3c-2.6-5-7.2-8.9-12.9-10.4-5.9-1.6-11.6-3.9-16.9-7-5.1-3-11.1-3.5-16.5-1.8l-7.3-7.3c1.7-5.4 1.2-11.4-1.8-16.5-3.1-5.3-5.4-11-7-16.9-1.5-5.7-5.4-10.3-10.4-12.9z"/><path d="m256 224.6c28 0 50.8-22.8 50.8-50.8s-22.8-50.8-50.8-50.8-50.8 22.8-50.8 50.8 22.8 50.8 50.8 50.8zm0-71.6c11.5 0 20.8 9.3 20.8 20.8s-9.3 20.8-20.8 20.8-20.8-9.3-20.8-20.8 9.3-20.8 20.8-20.8z"/><path d="m278.4 352.6h-44.8c-8.3 0-15 6.7-15 15s6.7 15 15 15h44.8c8.3 0 15-6.7 15-15s-6.7-15-15-15z"/></svg><label><span class="jitsi-tab-title">%1$s</span><span class="jitsi-tab-desc">%2$s</span></label>', __('Video Settings', 'jitsi-pro'), __('Initial video configurations...', 'jitsi-pro')),
            //'integration' 	=> sprintf('<svg xmlns="http://www.w3.org/2000/svg" id="Capa_1" enable-background="new 0 0 512 512" height="512" viewBox="0 0 512 512" width="512"><g><path d="m452 121v-61h-61c0-33.084-26.916-60-60-60s-60 26.916-60 60h-45c-124.38 0-226 101.632-226 226 0 124.38 101.632 226 226 226 124.38 0 226-101.632 226-226v-45c33.084 0 60-26.916 60-60s-26.916-60-60-60zm-241-30.431v69.304c18.086-6.039 21.725-8.873 30-8.873 16.542 0 30 13.458 30 30s-13.458 30-30 30c-8.287 0-11.967-2.851-30-8.873v68.873h-30c0-33.084-26.916-60-60-60s-60 26.916-60 60h-30.431c7.31-96.142 84.289-173.121 180.431-180.431zm-180.431 210.431h69.306c-6.047-18.103-8.875-21.725-8.875-30 0-16.542 13.458-30 30-30s30 13.458 30 30c0 8.275-2.832 11.907-8.875 30h68.875v30c-33.084 0-60 26.916-60 60s26.916 60 60 60v30.431c-96.142-7.31-173.121-84.289-180.431-180.431zm210.431 180.431v-69.304c-18.086 6.039-21.725 8.873-30 8.873-16.542 0-30-13.458-30-30s13.458-30 30-30c8.287 0 11.967 2.851 30 8.873v-68.873h30c0 33.084 26.916 60 60 60s60-26.926 60-60.01l30.431.01c-7.31 96.142-84.289 173.121-180.431 180.431zm211-270.431c-6.802 0-11.956-3.094-30-9.285v69.285h-69.875c6.046 18.103 8.875 21.725 8.875 30 0 16.542-13.458 30-30 30s-30-13.458-30-30c0-8.275 2.832-11.907 8.875-30h-68.875v-30c33.084 0 60-26.916 60-60s-26.916-60-60-60v-31h69.285c-6.121-17.842-9.285-23.202-9.285-30 0-16.542 13.458-30 30-30s30 13.458 30 30c0 6.804-3.098 11.966-9.285 30h70.285v70.285c17.842-6.122 23.202-9.285 30-9.285 16.542 0 30 13.458 30 30s-13.458 30-30 30z"/></g></svg><label><span class="jitsi-tab-title">%1$s</span><span class="jitsi-tab-desc">%2$s</span></label>', __('Integrations', 'jitsi-pro'), __('Integrate with popular tools...', 'jitsi-pro'))
        )
        ?>
		<div class="jitsi-setting-tab-links">
			<?php
            foreach ($arroftab as $key => $value) {
                $act_class = $key == $link ? 'active' : '';
                printf('<a class="jitsi-tab-link %1$s" href="%2$s">%3$s</a>', esc_attr($act_class), admin_url() . 'admin.php?page=jitsi-pro-' . $key, $value);
            } ?> 
		</div>
		<?php
    }

    public function jitsi_pro_manu_page_welcome_output()
    {
		// if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true'){
		// 	wp_redirect(admin_url( 'admin.php?page=jitsi-pro-apis' ));
		// 	die();
		// }
        ?>
		<div class="jitsi-setting-tabs-wrapper">
			<div class="jitsi-meet-welcome" id="jitsi-meet-welcome">
				<div class="jitsi-meet-welcome-content">
				<svg xmlns="http://www.w3.org/2000/svg" width="312" height="56" viewBox="0 0 156 28" fill="none">
					<g clip-path="url(#clip0_1262_13)">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M34.4757 22.2613V17.4941L43.0323 23.606C43.5361 23.9658 44.1987 24.0139 44.7491 23.7306C45.2996 23.4474 45.6455 22.8802 45.6455 22.2612V5.73487C45.6455 5.11582 45.2996 4.54868 44.7491 4.26541C44.1987 3.98214 43.5361 4.03025 43.0323 4.39007L34.4757 10.502V5.73499C34.4757 2.81736 31.8784 0.7771 29.0998 0.7771H8.62239C5.84387 0.7771 3.24658 2.81736 3.24658 5.73499V22.2613C3.24658 25.1789 5.84387 27.2192 8.62239 27.2192H29.0998C31.8784 27.2192 34.4757 25.1789 34.4757 22.2613ZM20.3316 18.3758C17.8232 17.0905 15.7668 15.0431 14.4904 12.5347L16.4404 10.5846C16.6886 10.3364 16.7596 9.99076 16.6621 9.68054C16.3341 8.68779 16.1568 7.62416 16.1568 6.51622C16.1568 6.02871 15.758 5.62985 15.2704 5.62985H12.1681C11.6807 5.62985 11.2818 6.02871 11.2818 6.51622C11.2818 14.8392 18.027 21.5845 26.35 21.5845C26.8375 21.5845 27.2364 21.1856 27.2364 20.6981V17.6047C27.2364 17.1172 26.8375 16.7183 26.35 16.7183C25.2509 16.7183 24.1784 16.541 23.1857 16.2131C22.8755 16.1067 22.5209 16.1865 22.2816 16.4258L20.3316 18.3758ZM25.8625 5.62099L26.4918 6.24144L20.8989 11.8344H24.5773V12.7208H19.2591V7.40259H20.1455V11.2051L25.8625 5.62099Z" fill="#407BFF"/>
					<path d="M63.8221 8.05792V17.2932C63.8221 17.8697 63.7238 18.3909 63.5277 18.8569C63.3438 19.323 63.0742 19.7216 62.7183 20.0527C62.3751 20.3839 61.9519 20.6414 61.4489 20.8254C60.9459 21.0094 60.3818 21.1014 59.7563 21.1014C58.5666 21.1014 57.6407 20.7948 56.9784 20.1815C56.3161 19.556 55.893 18.7282 55.709 17.6979L57.9534 17.238C58.0638 17.7899 58.26 18.2192 58.5421 18.5258C58.8365 18.8201 59.2351 18.9673 59.7379 18.9673C60.2162 18.9673 60.6087 18.8201 60.9154 18.5258C61.2345 18.2192 61.3937 17.7531 61.3937 17.1276V10.0448H57.3463V8.05792H63.8221ZM66.5178 20.899V18.9489H68.2104V10.008H66.5178V8.05792H72.3497V10.008H70.6388V18.9489H72.3497V20.899H66.5178ZM80.0689 10.2104V20.899H77.6405V10.2104H73.9979V8.05792H83.7115V10.2104H80.0689ZM89.7641 21.1198C88.6603 21.1198 87.7221 20.9236 86.9494 20.5311C86.1888 20.1386 85.5328 19.6235 84.9809 18.9857L86.6182 17.4036C87.0598 17.9187 87.5502 18.3112 88.09 18.581C88.6419 18.8508 89.249 18.9857 89.9113 18.9857C90.6592 18.9857 91.2238 18.8263 91.6038 18.5074C91.9838 18.1763 92.1741 17.7347 92.1741 17.1828C92.1741 16.7535 92.0517 16.404 91.8062 16.1342C91.5607 15.8644 91.1008 15.6681 90.4264 15.5455L89.2122 15.3615C86.6487 14.9568 85.3672 13.7119 85.3672 11.6269C85.3672 11.0505 85.4713 10.5293 85.68 10.0632C85.9008 9.59712 86.2135 9.19853 86.6182 8.86739C87.023 8.53624 87.5076 8.28484 88.0716 8.11311C88.6482 7.92914 89.2979 7.83716 90.0217 7.83716C90.9904 7.83716 91.8367 7.99658 92.5605 8.31548C93.2843 8.63438 93.9034 9.10655 94.4186 9.73205L92.7628 11.2958C92.4438 10.9033 92.0574 10.5845 91.6038 10.3391C91.1502 10.0938 90.5799 9.97121 89.8929 9.97121C89.1938 9.97121 88.6666 10.1061 88.3108 10.3759C87.9675 10.6335 87.7956 11.0014 87.7956 11.4798C87.7956 11.9703 87.9365 12.3322 88.2188 12.5652C88.5011 12.7982 88.9547 12.9699 89.5802 13.0803L90.776 13.3011C92.0758 13.5341 93.0325 13.9511 93.6459 14.552C94.2714 15.1408 94.5841 15.9686 94.5841 17.0356C94.5841 17.6489 94.4737 18.2069 94.253 18.7098C94.0443 19.2003 93.7315 19.6296 93.3147 19.9976C92.91 20.3532 92.407 20.6292 91.8062 20.8254C91.2175 21.0216 90.5368 21.1198 89.7641 21.1198ZM96.7722 20.899V18.9489H98.4647V10.008H96.7722V8.05792H102.604V10.008H100.893V18.9489H102.604V20.899H96.7722ZM115.068 11.6821H114.994L114.001 13.7242L111.37 18.581L108.739 13.7242L107.746 11.6821H107.672V20.899H105.373V8.05792H108.114L111.407 14.3313H111.481L114.737 8.05792H117.368V20.899H115.068V11.6821ZM120.68 20.899V8.05792H129.142V10.2104H123.108V13.3195H128.443V15.4719H123.108V18.7466H129.142V20.899H120.68ZM131.998 20.899V8.05792H140.46V10.2104H134.426V13.3195H139.761V15.4719H134.426V18.7466H140.46V20.899H131.998ZM148.357 10.2104V20.899H145.929V10.2104H142.286V8.05792H152V10.2104H148.357Z" fill="#407BFF"/>
					</g>
					<defs>
					<clipPath id="clip0_1262_13">
					<rect width="156" height="27.6" fill="white" transform="translate(0 0.199951)"/>
					</clipPath>
					</defs>
				</svg>
			<form method="post" action="options.php" class="jitsi-pro-option-form jitsi-welcome-option-form" id="jaasOptionFormApi">
				<?php 
				printf(
					'<p class="wc-title">%1$s</p><p class="wc-text">%2$s</p>',
					__('Welcome to Jitsi Meet WP Ultimate', 'jitsi-pro'),
					__('Select for which kind of api you want to use with our plugin and give credential accordingly.', 'jitsi-pro')
				);
				?>
				<div class="jitsi-setting-tab active">
					<?php
						settings_fields('jitsi-pro-api');
						do_settings_sections('jitsi-pro-api');
						echo '<p class="submit">';
						echo '<a class="button button-secondary" href="'.admin_url( 'admin.php?page=jitsi-pro-apis' ).'">'.__('Skip', 'jitsi-pro').'</a>';
						submit_button('Continue', 'primary', 'submit', false); 
						echo '</p>';
					?>
				</div>
				<div class="jitsi-welcome-video">
					<p class="jitsi-welcome-video-title"><?php _e('How to select Jitsi Meet server?', 'jitsi-pro'); ?></p>
					<iframe style="max-width: 100%" width="778" height="438" src="https://www.youtube.com/embed/FGu8GR_0_Ks" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
				</div>
				<input type="hidden" name="_wp_http_referer" value="<?php echo admin_url( 'admin.php?page=jitsi-pro-apis' ); ?>" />
			</form>
			</div></div>
		</div>
		<?php
    }

    public function jitsi_pro_manu_page_apis_output()
    {
        ?>
		<div class="wrapv jitsi-admin-wrap jitsi-wrap">
			<?php settings_errors(); ?>
			<h3 class="title">
				<svg xmlns="http://www.w3.org/2000/svg" height="40" viewBox="0 0 49 28" fill="none">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M34.4757 22.0614V17.2941L43.0323 23.4061C43.5361 23.7659 44.1987 23.814 44.7491 23.5307C45.2996 23.2474 45.6455 22.6803 45.6455 22.0612V5.53492C45.6455 4.91587 45.2996 4.34873 44.7491 4.06545C44.1987 3.78219 43.5361 3.8303 43.0323 4.19012L34.4757 10.3021V5.53504C34.4757 2.61741 31.8784 0.577148 29.0998 0.577148H8.62239C5.84387 0.577148 3.24658 2.61741 3.24658 5.53504V22.0614C3.24658 24.979 5.84387 27.0193 8.62239 27.0193H29.0998C31.8784 27.0193 34.4757 24.979 34.4757 22.0614ZM20.3316 18.1759C17.8232 16.8906 15.7668 14.8431 14.4904 12.3347L16.4404 10.3847C16.6886 10.1365 16.7596 9.79081 16.6621 9.48059C16.3341 8.48784 16.1568 7.42421 16.1568 6.31627C16.1568 5.82876 15.758 5.4299 15.2704 5.4299H12.1681C11.6807 5.4299 11.2818 5.82876 11.2818 6.31627C11.2818 14.6393 18.027 21.3845 26.35 21.3845C26.8375 21.3845 27.2364 20.9856 27.2364 20.4981V17.4047C27.2364 16.9172 26.8375 16.5183 26.35 16.5183C25.2509 16.5183 24.1784 16.341 23.1857 16.0131C22.8755 15.9068 22.5209 15.9865 22.2816 16.2258L20.3316 18.1759ZM25.8625 5.42103L26.4918 6.04149L20.8989 11.6345H24.5773V12.5209H19.2591V7.20264H20.1455V11.0051L25.8625 5.42103Z" fill="#407BFF"/>
				</svg>
				<span><?php echo esc_html__('Webinar and Video Conference with Jitsi Meet Premium', 'jitsi-pro') ?></span>
			</h3>
			<div class="option-form-field-wrap">
				<?php $this->jitsi_pro_menu_page_tab_link(); ?>
				<div class="jitsi-setting-tabs">
					<div class="jitsi-setting-tabs-wrapper">
						<form method="post" action="options.php" class="jitsi-pro-option-form" id="jaasOptionFormApi">
							<div class="jitsi-setting-tab active">
								<?php
                                    settings_fields('jitsi-pro-api');
									do_settings_sections('jitsi-pro-api');
									submit_button('Save Changes'); 
								?>
							</div>
						</form>
						<?php $this->jitsi_pro_preview_gen(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
    }

    public function jitsi_pro_manu_page_admin_output()
    {
        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
        } else {
            wp_enqueue_style('thickbox');
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
        } ?>
		<div class="wrapv jitsi-admin-wrap jitsi-wrap">
			<?php settings_errors(); ?>
			<h3 class="title">
				<svg xmlns="http://www.w3.org/2000/svg" height="40" viewBox="0 0 49 28" fill="none">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M34.4757 22.0614V17.2941L43.0323 23.4061C43.5361 23.7659 44.1987 23.814 44.7491 23.5307C45.2996 23.2474 45.6455 22.6803 45.6455 22.0612V5.53492C45.6455 4.91587 45.2996 4.34873 44.7491 4.06545C44.1987 3.78219 43.5361 3.8303 43.0323 4.19012L34.4757 10.3021V5.53504C34.4757 2.61741 31.8784 0.577148 29.0998 0.577148H8.62239C5.84387 0.577148 3.24658 2.61741 3.24658 5.53504V22.0614C3.24658 24.979 5.84387 27.0193 8.62239 27.0193H29.0998C31.8784 27.0193 34.4757 24.979 34.4757 22.0614ZM20.3316 18.1759C17.8232 16.8906 15.7668 14.8431 14.4904 12.3347L16.4404 10.3847C16.6886 10.1365 16.7596 9.79081 16.6621 9.48059C16.3341 8.48784 16.1568 7.42421 16.1568 6.31627C16.1568 5.82876 15.758 5.4299 15.2704 5.4299H12.1681C11.6807 5.4299 11.2818 5.82876 11.2818 6.31627C11.2818 14.6393 18.027 21.3845 26.35 21.3845C26.8375 21.3845 27.2364 20.9856 27.2364 20.4981V17.4047C27.2364 16.9172 26.8375 16.5183 26.35 16.5183C25.2509 16.5183 24.1784 16.341 23.1857 16.0131C22.8755 15.9068 22.5209 15.9865 22.2816 16.2258L20.3316 18.1759ZM25.8625 5.42103L26.4918 6.04149L20.8989 11.6345H24.5773V12.5209H19.2591V7.20264H20.1455V11.0051L25.8625 5.42103Z" fill="#407BFF"/>
				</svg>
				<span><?php echo esc_html__('Webinar and Video Conference with Jitsi Meet Premium', 'jitsi-pro') ?></span>
			</h3>
			<div class="option-form-field-wrap">
				<?php $this->jitsi_pro_menu_page_tab_link('admin'); ?>
				<div class="jitsi-setting-tabs">
					<div class="jitsi-setting-tabs-wrapper">
						<form method="post" action="options.php" class="jitsi-pro-option-form" id="jaasOptionFormAdmin">
							<div class="jitsi-setting-tab active">
								<?php
                                    settings_fields('jitsi-pro-admin');
									do_settings_sections('jitsi-pro-admin');
									submit_button('Save Changes'); 
								?>
							</div>
						</form>
						<?php $this->jitsi_pro_preview_gen(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
    }

    public function jitsi_pro_manu_page_config_output()
    {
        ?>
		<div class="wrapv jitsi-admin-wrap jitsi-wrap">
			<?php settings_errors(); ?>
			<h3 class="title">
				<svg xmlns="http://www.w3.org/2000/svg" height="40" viewBox="0 0 49 28" fill="none">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M34.4757 22.0614V17.2941L43.0323 23.4061C43.5361 23.7659 44.1987 23.814 44.7491 23.5307C45.2996 23.2474 45.6455 22.6803 45.6455 22.0612V5.53492C45.6455 4.91587 45.2996 4.34873 44.7491 4.06545C44.1987 3.78219 43.5361 3.8303 43.0323 4.19012L34.4757 10.3021V5.53504C34.4757 2.61741 31.8784 0.577148 29.0998 0.577148H8.62239C5.84387 0.577148 3.24658 2.61741 3.24658 5.53504V22.0614C3.24658 24.979 5.84387 27.0193 8.62239 27.0193H29.0998C31.8784 27.0193 34.4757 24.979 34.4757 22.0614ZM20.3316 18.1759C17.8232 16.8906 15.7668 14.8431 14.4904 12.3347L16.4404 10.3847C16.6886 10.1365 16.7596 9.79081 16.6621 9.48059C16.3341 8.48784 16.1568 7.42421 16.1568 6.31627C16.1568 5.82876 15.758 5.4299 15.2704 5.4299H12.1681C11.6807 5.4299 11.2818 5.82876 11.2818 6.31627C11.2818 14.6393 18.027 21.3845 26.35 21.3845C26.8375 21.3845 27.2364 20.9856 27.2364 20.4981V17.4047C27.2364 16.9172 26.8375 16.5183 26.35 16.5183C25.2509 16.5183 24.1784 16.341 23.1857 16.0131C22.8755 15.9068 22.5209 15.9865 22.2816 16.2258L20.3316 18.1759ZM25.8625 5.42103L26.4918 6.04149L20.8989 11.6345H24.5773V12.5209H19.2591V7.20264H20.1455V11.0051L25.8625 5.42103Z" fill="#407BFF"/>
				</svg>
				<span><?php echo esc_html__('Webinar and Video Conference with Jitsi Meet Premium', 'jitsi-pro') ?></span>
			</h3>
			<div class="option-form-field-wrap">
				<?php $this->jitsi_pro_menu_page_tab_link('config'); ?>
				<div class="jitsi-setting-tabs">
					<div class="jitsi-setting-tabs-wrapper">
						<form method="post" action="options.php" class="jitsi-pro-option-form" id="jaasOptionFormConfig">
							<div class="jitsi-setting-tab active">
								<?php
                                    settings_fields('jitsi-pro-config');
									do_settings_sections('jitsi-pro-config');
									submit_button('Save Changes'); 
								?>
							</div>
						</form>
						<?php $this->jitsi_pro_preview_gen(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
    }

    public function jitsi_pro_manu_page_audio_output()
    {
        ?>
		<div class="wrapv jitsi-admin-wrap jitsi-wrap">
			<?php settings_errors(); ?>
			<h3 class="title">
				<svg xmlns="http://www.w3.org/2000/svg" height="40" viewBox="0 0 49 28" fill="none">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M34.4757 22.0614V17.2941L43.0323 23.4061C43.5361 23.7659 44.1987 23.814 44.7491 23.5307C45.2996 23.2474 45.6455 22.6803 45.6455 22.0612V5.53492C45.6455 4.91587 45.2996 4.34873 44.7491 4.06545C44.1987 3.78219 43.5361 3.8303 43.0323 4.19012L34.4757 10.3021V5.53504C34.4757 2.61741 31.8784 0.577148 29.0998 0.577148H8.62239C5.84387 0.577148 3.24658 2.61741 3.24658 5.53504V22.0614C3.24658 24.979 5.84387 27.0193 8.62239 27.0193H29.0998C31.8784 27.0193 34.4757 24.979 34.4757 22.0614ZM20.3316 18.1759C17.8232 16.8906 15.7668 14.8431 14.4904 12.3347L16.4404 10.3847C16.6886 10.1365 16.7596 9.79081 16.6621 9.48059C16.3341 8.48784 16.1568 7.42421 16.1568 6.31627C16.1568 5.82876 15.758 5.4299 15.2704 5.4299H12.1681C11.6807 5.4299 11.2818 5.82876 11.2818 6.31627C11.2818 14.6393 18.027 21.3845 26.35 21.3845C26.8375 21.3845 27.2364 20.9856 27.2364 20.4981V17.4047C27.2364 16.9172 26.8375 16.5183 26.35 16.5183C25.2509 16.5183 24.1784 16.341 23.1857 16.0131C22.8755 15.9068 22.5209 15.9865 22.2816 16.2258L20.3316 18.1759ZM25.8625 5.42103L26.4918 6.04149L20.8989 11.6345H24.5773V12.5209H19.2591V7.20264H20.1455V11.0051L25.8625 5.42103Z" fill="#407BFF"/>
				</svg>
				<span><?php echo esc_html__('Webinar and Video Conference with Jitsi Meet Premium', 'jitsi-pro') ?></span>
			</h3>
			<div class="option-form-field-wrap">
				<?php $this->jitsi_pro_menu_page_tab_link('audio'); ?>
				<div class="jitsi-setting-tabs">
					<div class="jitsi-setting-tabs-wrapper">
						<form method="post" action="options.php" class="jitsi-pro-option-form" id="jaasOptionFormAudio">
							<div class="jitsi-setting-tab active">
								<?php
                                    settings_fields('jitsi-pro-audio');
									do_settings_sections('jitsi-pro-audio');
									submit_button('Save Changes'); 
								?>
							</div>
						</form>
						<?php $this->jitsi_pro_preview_gen(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
    }

    public function jitsi_pro_manu_page_video_output()
    {
        ?>
		<div class="wrapv jitsi-admin-wrap">
			<?php settings_errors(); ?>
			<h3 class="title">
				<svg xmlns="http://www.w3.org/2000/svg" height="40" viewBox="0 0 49 28" fill="none">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M34.4757 22.0614V17.2941L43.0323 23.4061C43.5361 23.7659 44.1987 23.814 44.7491 23.5307C45.2996 23.2474 45.6455 22.6803 45.6455 22.0612V5.53492C45.6455 4.91587 45.2996 4.34873 44.7491 4.06545C44.1987 3.78219 43.5361 3.8303 43.0323 4.19012L34.4757 10.3021V5.53504C34.4757 2.61741 31.8784 0.577148 29.0998 0.577148H8.62239C5.84387 0.577148 3.24658 2.61741 3.24658 5.53504V22.0614C3.24658 24.979 5.84387 27.0193 8.62239 27.0193H29.0998C31.8784 27.0193 34.4757 24.979 34.4757 22.0614ZM20.3316 18.1759C17.8232 16.8906 15.7668 14.8431 14.4904 12.3347L16.4404 10.3847C16.6886 10.1365 16.7596 9.79081 16.6621 9.48059C16.3341 8.48784 16.1568 7.42421 16.1568 6.31627C16.1568 5.82876 15.758 5.4299 15.2704 5.4299H12.1681C11.6807 5.4299 11.2818 5.82876 11.2818 6.31627C11.2818 14.6393 18.027 21.3845 26.35 21.3845C26.8375 21.3845 27.2364 20.9856 27.2364 20.4981V17.4047C27.2364 16.9172 26.8375 16.5183 26.35 16.5183C25.2509 16.5183 24.1784 16.341 23.1857 16.0131C22.8755 15.9068 22.5209 15.9865 22.2816 16.2258L20.3316 18.1759ZM25.8625 5.42103L26.4918 6.04149L20.8989 11.6345H24.5773V12.5209H19.2591V7.20264H20.1455V11.0051L25.8625 5.42103Z" fill="#407BFF"/>
				</svg>
				<span><?php echo esc_html__('Webinar and Video Conference with Jitsi Meet Premium', 'jitsi-pro') ?></span>
			</h3>
			<div class="option-form-field-wrap">
				<?php $this->jitsi_pro_menu_page_tab_link('video'); ?>
				<div class="jitsi-setting-tabs">
					<div class="jitsi-setting-tabs-wrapper">
						<form method="post" action="options.php" class="jitsi-pro-option-form" id="jaasOptionFormVideo">
							<div class="jitsi-setting-tab active">
								<?php
                                    settings_fields('jitsi-pro-video');
									do_settings_sections('jitsi-pro-video');
									submit_button('Save Changes'); 
								?>
							</div>
						</form>
						<?php $this->jitsi_pro_preview_gen(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
    }

	public function jitsi_pro_manu_page_integration_output()
    {
        ?>
		<div class="wrapv jitsi-admin-wrap">
			<?php settings_errors(); ?>
			<h3 class="title">
				<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 512 512" height="50" viewBox="0 0 512 512"
					width="50">
					<g>
						<path fill="black"
							d="m296 256c0-22.056-17.944-40-40-40s-40 17.944-40 40 17.944 40 40 40 40-17.944 40-40zm-40 20c-11.028 0-20-8.972-20-20s8.972-20 20-20 20 8.972 20 20-8.972 20-20 20z" />
						<path fill="black"
							d="m70 476h141c5.522 0 10-4.477 10-10s-4.478-10-10-10h-141c-24.146 0-44.35-17.206-48.995-40.01h469.99c-4.645 22.804-24.849 40.01-48.995 40.01h-141c-5.522 0-10 4.477-10 10s4.478 10 10 10h141c38.598 0 70-31.402 70-70 0-5.523-4.478-10-10-10h-35v-260c0-5.523-4.478-10-10-10h-71v-80c0-3.688-2.03-7.077-5.281-8.817-3.252-1.74-7.199-1.549-10.266.497l-74.453 49.635v-41.315c0-5.523-4.478-10-10-10h-150c-5.522 0-10 4.477-10 10v80h-71c-5.522 0-10 4.477-10 10v260h-35c-5.522 0-10 4.477-10 10 0 38.598 31.402 70 70 70zm236-80h-100v-30c0-27.57 22.43-50 50-50s50 22.43 50 50zm60-331.315v82.63l-61.973-41.315zm-220-8.685h130v100h-130zm-81 90h61v20c0 5.523 4.478 10 10 10h150c5.522 0 10-4.477 10-10v-41.315l74.453 49.635c3.073 2.049 7.019 2.234 10.266.497 3.251-1.74 5.281-5.129 5.281-8.817v-20h61v250h-121v-30c0-38.598-31.402-70-70-70s-70 31.402-70 70v30h-121z" />
						<circle cx="256" cy="466" r="10" />
					</g>
				</svg>
				<span><?php echo esc_html__('Webinar and Video Conference with Jitsi Meet Premium', 'jitsi-pro') ?></span>
			</h3>
			<div class="option-form-field-wrap">
				<?php $this->jitsi_pro_menu_page_tab_link('integration'); ?>
				<div class="jitsi-setting-tabs">
					<div class="jitsi-setting-tabs-wrapper">
						<form method="post" action="options.php" class="jitsi-pro-option-form" id="jaasOptionFormIntegration">
							<div class="jitsi-setting-tab active">
								<?php
                                    settings_fields('jitsi-pro-integration');
									do_settings_sections('jitsi-pro-integration');
									submit_button('Save Changes'); ?>
							</div>
						</form>
						<?php $this->jitsi_pro_preview_gen(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
    }

    public function jitsi_pro_preview_gen()
    {
		$api_select = get_option('jitsi_opt_select_api', 'free');
		$self_url = get_option('jitsi_opt_custom_domain', '');
        $app_id = get_option('jitsi_opt_app_id', '');
        $api_key = get_option('jitsi_opt_api_key', '');
        $private_key = get_option('jitsi_opt_private_key', '');
        $avatar = get_option('jitsi_opt_user_avatar', JITSI_ULTIMATE_URL.'/assets/img/avatar.png');

        $prev_class = 'meeting-ui-preview';
        $prev_msg = '';

        if($api_select == 'free'){
			$prev_class = 'meeting-ui-preview preview-success';
		} elseif($api_select == 'self'){
			if(!empty($self_url)){
				$prev_class = 'meeting-ui-preview preview-success';
			} else {
				$prev_class = 'meeting-ui-preview preview-error';
				$prev_msg = __('Self hosted domain missing', 'jitsi-pro');
			}
		} elseif (!$app_id) {
            $prev_class = 'meeting-ui-preview preview-error';
            $prev_msg = __('App id missing', 'jitsi-pro');
        } elseif (!$api_key) {
            $prev_class = 'meeting-ui-preview preview-error';
            $prev_msg = __('Api key is missing', 'jitsi-pro');
        } elseif (!$private_key) {
            $prev_class = 'meeting-ui-preview preview-error';
            $prev_msg = __('Private key is missing', 'jitsi-pro');
        } else {
            $prev_class = 'meeting-ui-preview preview-success';
        } ?>
		<div id="meeting-ui-preview" class="<?php echo esc_attr($prev_class); ?>">
			<div class="jitsi-preview-loader">
				<svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" height="40px" width="40px" class="jitsi-preview-loading"><circle r="8.333333333333334" cx="10" cy="10" fill="none" stroke="#F0F3F7" stroke-width="1.6666666666666667"></circle><circle r="8.333333333333334" cx="10" cy="10" fill="none" stroke="#2087e1" stroke-width="1.6666666666666667" stroke-linecap="round" stroke-dasharray="26.179938779914945"></circle></svg>
			</div>
			<div class="jitsi-preview-message"><?php echo $prev_msg; ?></div>
			<div class="jitsi-preview-people-mock">
				<?php
                for ($i=1;$i<9;$i++) {
                    if ($i == 4) {
                        echo '<div><div class="jitsi-pro-admin-avatar">';
                        echo '<div class="jitsi-icon icon-gsm-bars"><svg height="4" width="4" fill="#ffffff" viewBox="0 0 32 32"><path d="M2 24h4a2 2 0 012 2v4a2 2 0 01-2 2H2a2 2 0 01-2-2v-4a2 2 0 012-2zm12-12h4a2 2 0 012 2v16a2 2 0 01-2 2h-4a2 2 0 01-2-2V14a2 2 0 012-2zM26 0h4a2 2 0 012 2v28a2 2 0 01-2 2h-4a2 2 0 01-2-2V2a2 2 0 012-2z"></path></svg></div>';
                        echo '<div class="jitsi-pro-admin-avatar-thumb" style="background-image: url('.get_option('jitsi_opt_user_avatar', JITSI_ULTIMATE_URL.'/assets/img/avatar.png').')"></div>';
                        echo '</div></div>';
                    } else {
                        echo '<div><div style="background-image: url('.JITSI_ULTIMATE_URL.'/assets/img/0'.$i.'.png)"></div></div>';
                    }
                } ?>
			</div>
		</div>
		<script>
		document.addEventListener("DOMContentLoaded", function() {			
			var pageurl = new URL(window.location.href);
			var pageParam = pageurl.searchParams.get("page");

			if(!(jQuery('#jitsi-meet-welcome').length > 0)){
				jQuery('.jitsi-admin-tooltip').each(function(){
					var offsetParent = jQuery('.jitsi-setting-tabs');
					var thisBottom = jQuery(this).offset().top + jQuery(this).height();
					var parentBottom = offsetParent.offset().top + offsetParent.height();
					if(thisBottom > parentBottom){
						jQuery(this).css('top', 0 - (thisBottom - parentBottom));
						jQuery(this).find('.tooltip-arrow').css('top', 10 + (thisBottom - parentBottom))
					}
				});
			}

			function regenJitsiPreview(){
				if(document.querySelector('input[name="jitsi_opt_select_api"]') !== null && document.querySelector('input[name="jitsi_opt_select_api"]:checked').value == 'free'){
					document.getElementById('meeting-ui-preview').className = 'meeting-ui-preview preview-success';
				} else if(document.querySelector('input[name="jitsi_opt_select_api"]') !== null && document.querySelector('input[name="jitsi_opt_select_api"]:checked').value == 'self'){
					if(document.getElementById("jitsi_opt_custom_domain") && document.getElementById("jitsi_opt_custom_domain").value){
						document.getElementById('meeting-ui-preview').className = 'meeting-ui-preview preview-success';
					} else {
						document.getElementById('meeting-ui-preview').className = 'meeting-ui-preview preview-error';
						document.querySelector('.jitsi-preview-message').innerHTML  = 'Self hosted domain missing';
					}
				} else if(document.getElementById("jitsi_opt_app_id") && !document.getElementById("jitsi_opt_app_id").value){
					document.getElementById('meeting-ui-preview').className = 'meeting-ui-preview preview-error';
					document.querySelector('.jitsi-preview-message').innerHTML  = 'App id missing';
				} else if(document.getElementById("jitsi_opt_api_key") && !document.getElementById("jitsi_opt_api_key").value){
					document.getElementById('meeting-ui-preview').className = 'meeting-ui-preview preview-error';
					document.querySelector('.jitsi-preview-message').innerHTML  = 'Api key is missing';
				} else if(document.getElementById("jitsi_opt_private_key") && !document.getElementById("jitsi_opt_private_key").value){
					document.getElementById('meeting-ui-preview').className = 'meeting-ui-preview preview-error';
					document.querySelector('.jitsi-preview-message').innerHTML  = 'Private key is missing';
				} else {
					document.getElementById('meeting-ui-preview').className = 'meeting-ui-preview preview-success';
				}
			}

			document.querySelectorAll('.jitsi-admin-field').forEach(function(e){
				e.addEventListener('change', function(){
					if(document.getElementById("meeting-ui-preview") && document.getElementById("jitsi_opt_app_id")) {
						regenJitsiPreview();
					}
				});
			});
		});
		</script>
		<?php
    }

    public function setSettings(array $settings)
    {
        $this->settings = $settings;
        return $this;
    }

    public function setSections(array $sections)
    {
        $this->sections = $sections;
        return $this;
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    public function registerCustomFields()
    {
        // Register Settings
        foreach ($this->settings as $setting) {
            register_setting($setting['option_group'], $setting['option_name'], (isset($setting['callback']) ? $setting['callback'] : ''));
        }

        //Register Setting Section
        foreach ($this->sections as $section) {
            add_settings_section($section['id'], $section['title'], (isset($section['callback']) ? $section['callback'] : ''), $section['page']);
        }

        // Settings Field
        foreach ($this->fields as $field) {
            add_settings_field($field['id'], $field['title'], (isset($field['callback']) ? $field['callback'] : ''), $field['page'], $field["section"], (isset($field["args"]) ? $field["args"] : ''));
        }
    }
}

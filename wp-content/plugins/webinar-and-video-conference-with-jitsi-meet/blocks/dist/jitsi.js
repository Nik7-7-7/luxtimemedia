jQuery(window).on('elementor/frontend/init', function () {
  'use strict';
  elementorFrontend.hooks.addAction(
    'frontend/element_ready/jitsi_elementor.default',
    function () {
      initJitsi('.jitsi-wrapper-elementor');
    }
  );
});

function initJitsi(elem) {
  if (window.location.protocol == 'http:') {
    jQuery(elem).each(function (index, element) {
      jQuery(element).html(
        '<div class="device-status device-status-error" role="alert" tabindex="-1"><div class="jitsi-icon jitsi-icon-default device-icon device-icon--warning"><svg fill="none" height="16" width="16" viewBox="0 0 18 16"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.233 14.325L9.708.911a.817.817 0 00-1.417 0L.768 14.325a.78.78 0 00-.1.382.8.8 0 00.808.793h15.05a.82.82 0 00.39-.098.785.785 0 00.318-1.077zm-14.39-.41L9 2.937l6.158 10.978H2.842zm5.349-2.378c0-.438.355-.793.792-.793h.032a.793.793 0 110 1.586h-.032a.793.793 0 01-.792-.793zM9 6.781a.808.808 0 00-.808.809v1.554a.808.808 0 001.617 0V7.59A.808.808 0 009 6.782z" fill="#040404"></path></svg></div><span role="heading">For Jitsi Meet to work properly. You need SSL on your site.</span></div>'
      );
    });
    return false;
  }

  const api = [];

  jQuery(elem).each(function (index, element) {
    var roomName = 'meeting/' + jQuery(element).data('name'),
      width = jQuery(element).data('width'),
      height = jQuery(element).data('height'),
      muted = jQuery(element).data('mute'),
      videoMuted = jQuery(element).data('videomute'),
      screenSharing = jQuery(element).data('screen'),
      datadomain = jQuery(element).data('domain'),
      domain     = datadomain ? datadomain : 'video.devloprog.org';

    let toolbarButtons = [
      'camera',
      'chat',
      'closedcaptions',
      'download',
      'embedmeeting',
      'etherpad',
      'feedback',
      'filmstrip',
      'fullscreen',
      'hangup',
      'help',
      'livestreaming',
      'microphone',
      'mute-everyone',
      'mute-video-everyone',
      'participants-pane',
      'profile',
      'raisehand',
      'recording',
      'security',
      'select-background',
      'settings',
      'shareaudio',
      'sharedvideo',
      'shortcuts',
      'stats',
      'tileview',
      'toggle-camera',
      'videoquality',
      '__end',
    ];

    Boolean(jQuery(element).data('invite')) && toolbarButtons.push('invite');
    Boolean(jQuery(element).data('screen')) && toolbarButtons.push('desktop');

    const options = {
      roomName,
      width,
      height,
      parentNode: element,
      configOverwrite: {
        startWithAudioMuted: muted,
        startWithVideoMuted: videoMuted,
       // startScreenSharing: screenSharing,
        enableWelcomePage: false,
        enableCLosePage: false,
        toolbarButtons: toolbarButtons,
      },
      interfaceConfigOverwrite: {
        SHOW_CHROME_EXTENSION_BANNER: false,
        SHOW_PROMOTIONAL_CLOSE_PAGE: false,
      },
    };

    var userInfo = jQuery(element).data('userinfo');
    if (userInfo && userInfo != '') {
      userInfo = userInfo.split(',');
      options.userInfo = {
        displayName: userInfo[0],
        email: userInfo[1],
      };
    }

    api[index] = new JitsiMeetExternalAPI(domain, options);
  });
}

jQuery(document).ready(function () {
  'use strict';
  initJitsi('.jitsi-wrapper');
});

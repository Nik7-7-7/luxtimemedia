jQuery(document).ready(function () {
  if (jQuery('body').hasClass('elementor-editor-active')) {
    elementorFrontend.hooks.addAction(
      'frontend/element_ready/jitsi_elementor.default',
      function () {
        if (jitsi_pro.api_select == 'jaas') {
          if (jitsi_pro.jwt) {
            initJitsi();
          } else {
            initFreeJitsi();
          }
        } else if (jitsi_pro.api_select == 'self') {
          initJitsiSelf();
        } else {
          initFreeJitsi();
        }
      }
    );
  }
});

function initJitsi() {
  //Jitsi Call
  const domain = '8x8.vc';
  const api = [];

  if (window.location.protocol == 'http:') {
    jQuery('.jitsi-wrapper').each(function (index, element) {
      jQuery(element).html(
        '<div class="device-status device-status-error" role="alert" tabindex="-1"><div class="jitsi-icon jitsi-icon-default device-icon device-icon--warning"><svg fill="none" height="16" width="16" viewBox="0 0 18 16"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.233 14.325L9.708.911a.817.817 0 00-1.417 0L.768 14.325a.78.78 0 00-.1.382.8.8 0 00.808.793h15.05a.82.82 0 00.39-.098.785.785 0 00.318-1.077zm-14.39-.41L9 2.937l6.158 10.978H2.842zm5.349-2.378c0-.438.355-.793.792-.793h.032a.793.793 0 110 1.586h-.032a.793.793 0 01-.792-.793zM9 6.781a.808.808 0 00-.808.809v1.554a.808.808 0 001.617 0V7.59A.808.808 0 009 6.782z" fill="#040404"></path></svg></div><span role="heading">For Jitsi Meet to work properly. You need SSL on your site.</span></div>'
      );
    });
    return false;
  }

  jQuery('.jitsi-wrapper').each(function (index, element) {
    if (!jQuery(element).hasClass('.jitsi-wrapper-rendered')) {
      if (window.location.protocol == 'http:') {
        jQuery(element).html(
          '<div class="device-status device-status-error" role="alert" tabindex="-1"><div class="jitsi-icon jitsi-icon-default device-icon device-icon--warning"><svg fill="none" height="16" width="16" viewBox="0 0 18 16"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.233 14.325L9.708.911a.817.817 0 00-1.417 0L.768 14.325a.78.78 0 00-.1.382.8.8 0 00.808.793h15.05a.82.82 0 00.39-.098.785.785 0 00.318-1.077zm-14.39-.41L9 2.937l6.158 10.978H2.842zm5.349-2.378c0-.438.355-.793.792-.793h.032a.793.793 0 110 1.586h-.032a.793.793 0 01-.792-.793zM9 6.781a.808.808 0 00-.808.809v1.554a.808.808 0 001.617 0V7.59A.808.808 0 009 6.782z" fill="#040404"></path></svg></div><span role="heading">For Jitsi Meet to work properly. You need SSL on your site.</span></div>'
        );
        return false;
      }

      let toolbarButtons = [
        'camera',
        'chat',
        'closedcaptions',
        'desktop',
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

      var roomName = jitsi_pro.appid + '/' + jQuery(element).data('name'),
        width = jQuery(element).data('width'),
        height = jQuery(element).data('height'),
        configOverwrite = {
          startAudioOnly: jQuery(element).data('startaudioonly')
            ? jQuery(element).data('startaudioonly')
            : 0,
          startAudioMuted: jQuery(element).data('startaudiomuted')
            ? jQuery(element).data('startaudiomuted')
            : 10,
          startWithAudioMuted: jQuery(element).data('startwithaudiomuted')
            ? jQuery(element).data('startwithaudiomuted')
            : 0,
          startSilent: jQuery(element).data('startsilent')
            ? jQuery(element).data('startsilent')
            : 0,
          resolution: jQuery(element).data('resolution')
            ? jQuery(element).data('resolution')
            : 720,
          maxfullresolutionparticipant: jQuery(element).data(
            'maxfullresolutionparticipant'
          )
            ? jQuery(element).data('maxfullresolutionparticipant')
            : 2,
          disableSimulcast: jQuery(element).data('disablesimulcast')
            ? jQuery(element).data('disablesimulcast')
            : 0,
          startVideoMuted: jQuery(element).data('startvideomuted')
            ? jQuery(element).data('startvideomuted')
            : 10,
          startWithVideoMuted: jQuery(element).data('startwithvideomuted')
            ? jQuery(element).data('startwithvideomuted')
            : 0,
          startScreenSharing: jQuery(element).data('startscreensharing')
            ? jQuery(element).data('startscreensharing')
            : 0,
          fileRecordingsEnabled: jQuery(element).data('filerecordingsenabled')
            ? jQuery(element).data('filerecordingsenabled')
            : 0,
          transcribingEnabled: jQuery(element).data('transcribingenabled')
            ? jQuery(element).data('transcribingenabled')
            : 0,
          liveStreamingEnabled: jQuery(element).data('livestreamingenabled')
            ? jQuery(element).data('livestreamingenabled')
            : 0,
          prejoinConfig: {
            enabled: jQuery(element).data('enablewelcomepage')
              ? jQuery(element).data('enablewelcomepage')
              : 0,
          },
          toolbarButtons: toolbarButtons,
          brandingRoomAlias: 'meeting/' + jQuery(element).data('name'),
        };

      const options = {
        roomName,
        width,
        height,
        parentNode: element,
        jwt: jitsi_pro.jwt,
        configOverwrite: configOverwrite,
        interfaceConfigOverwrite: {
          SHOW_CHROME_EXTENSION_BANNER: false,
          SHOW_PROMOTIONAL_CLOSE_PAGE: false,
          SHOW_POWERED_BY: false,
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
      jQuery(element).addClass('jitsi-wrapper-rendered');
    }
  });
}

function initFreeJitsi() {
  const domain = 'meet.jit.si';
  const api = [];

  jQuery('.jitsi-wrapper').each(function (index, element) {
    if (!jQuery(element).hasClass('jitsi-wrapper-rendered')) {
      if (window.location.protocol == 'http:') {
        jQuery(element).html(
          '<div class="device-status device-status-error" role="alert" tabindex="-1"><div class="jitsi-icon jitsi-icon-default device-icon device-icon--warning"><svg fill="none" height="16" width="16" viewBox="0 0 18 16"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.233 14.325L9.708.911a.817.817 0 00-1.417 0L.768 14.325a.78.78 0 00-.1.382.8.8 0 00.808.793h15.05a.82.82 0 00.39-.098.785.785 0 00.318-1.077zm-14.39-.41L9 2.937l6.158 10.978H2.842zm5.349-2.378c0-.438.355-.793.792-.793h.032a.793.793 0 110 1.586h-.032a.793.793 0 01-.792-.793zM9 6.781a.808.808 0 00-.808.809v1.554a.808.808 0 001.617 0V7.59A.808.808 0 009 6.782z" fill="#040404"></path></svg></div><span role="heading">For Jitsi Meet to work properly. You need SSL on your site.</span></div>'
        );
        return false;
      }

      let toolbarButtons = [
        'camera',
        'chat',
        'closedcaptions',
        'desktop',
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

      var roomName = 'meeting/' + jQuery(element).data('name'),
        width = jQuery(element).data('width'),
        height = jQuery(element).data('height'),
        configOverwrite = {
          startAudioOnly: jQuery(element).data('startaudioonly')
            ? jQuery(element).data('startaudioonly')
            : 0,
          startAudioMuted: jQuery(element).data('startaudiomuted')
            ? jQuery(element).data('startaudiomuted')
            : 10,
          startWithAudioMuted: jQuery(element).data('startwithaudiomuted')
            ? jQuery(element).data('startwithaudiomuted')
            : 0,
          startSilent: jQuery(element).data('startsilent')
            ? jQuery(element).data('startsilent')
            : 0,
          resolution: jQuery(element).data('resolution')
            ? jQuery(element).data('resolution')
            : 720,
          maxfullresolutionparticipant: jQuery(element).data(
            'maxfullresolutionparticipant'
          )
            ? jQuery(element).data('maxfullresolutionparticipant')
            : 2,
          disableSimulcast: jQuery(element).data('disablesimulcast')
            ? jQuery(element).data('disablesimulcast')
            : 0,
          startVideoMuted: jQuery(element).data('startvideomuted')
            ? jQuery(element).data('startvideomuted')
            : 10,
          startWithVideoMuted: jQuery(element).data('startwithvideomuted')
            ? jQuery(element).data('startwithvideomuted')
            : 0,
          startScreenSharing: jQuery(element).data('startscreensharing')
            ? jQuery(element).data('startscreensharing')
            : 0,
          fileRecordingsEnabled: jQuery(element).data('filerecordingsenabled')
            ? jQuery(element).data('filerecordingsenabled')
            : 0,
          transcribingEnabled: jQuery(element).data('transcribingenabled')
            ? jQuery(element).data('transcribingenabled')
            : 0,
          liveStreamingEnabled: jQuery(element).data('livestreamingenabled')
            ? jQuery(element).data('livestreamingenabled')
            : 0,
          prejoinConfig: {
            enabled: jQuery(element).data('enablewelcomepage')
              ? jQuery(element).data('enablewelcomepage')
              : 0,
          },
          toolbarButtons: toolbarButtons,
        };

      const options = {
        roomName,
        width,
        height,
        parentNode: element,
        configOverwrite: configOverwrite,
        interfaceConfigOverwrite: {
          SHOW_CHROME_EXTENSION_BANNER: false,
          SHOW_PROMOTIONAL_CLOSE_PAGE: false,
          SHOW_POWERED_BY: false,
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
      jQuery(element).addClass('jitsi-wrapper-rendered');
    }
  });
}

function initJitsiSelf() {
  //Jitsi Call
  const domain = jitsi_pro.custom_domain;
  const api = [];

  jQuery('.jitsi-wrapper').each(function (index, element) {
    if (!jQuery(element).hasClass('jitsi-wrapper-rendered')) {
      if (window.location.protocol == 'http:') {
        jQuery(element).html(
          '<div class="device-status device-status-error" role="alert" tabindex="-1"><div class="jitsi-icon jitsi-icon-default device-icon device-icon--warning"><svg fill="none" height="16" width="16" viewBox="0 0 18 16"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.233 14.325L9.708.911a.817.817 0 00-1.417 0L.768 14.325a.78.78 0 00-.1.382.8.8 0 00.808.793h15.05a.82.82 0 00.39-.098.785.785 0 00.318-1.077zm-14.39-.41L9 2.937l6.158 10.978H2.842zm5.349-2.378c0-.438.355-.793.792-.793h.032a.793.793 0 110 1.586h-.032a.793.793 0 01-.792-.793zM9 6.781a.808.808 0 00-.808.809v1.554a.808.808 0 001.617 0V7.59A.808.808 0 009 6.782z" fill="#040404"></path></svg></div><span role="heading">For Jitsi Meet to work properly. You need SSL on your site.</span></div>'
        );
        return false;
      }

      let toolbarButtons = [
        'camera',
        'chat',
        'closedcaptions',
        'desktop',
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

      var roomName = jQuery(element).data('name'),
        width = jQuery(element).data('width'),
        height = jQuery(element).data('height'),
        configOverwrite = {
          startAudioOnly: jQuery(element).data('startaudioonly')
            ? jQuery(element).data('startaudioonly')
            : 0,
          startAudioMuted: jQuery(element).data('startaudiomuted')
            ? jQuery(element).data('startaudiomuted')
            : 10,
          startWithAudioMuted: jQuery(element).data('startwithaudiomuted')
            ? jQuery(element).data('startwithaudiomuted')
            : 0,
          startSilent: jQuery(element).data('startsilent')
            ? jQuery(element).data('startsilent')
            : 0,
          resolution: jQuery(element).data('resolution')
            ? jQuery(element).data('resolution')
            : 720,
          maxfullresolutionparticipant: jQuery(element).data(
            'maxfullresolutionparticipant'
          )
            ? jQuery(element).data('maxfullresolutionparticipant')
            : 2,
          disableSimulcast: jQuery(element).data('disablesimulcast')
            ? jQuery(element).data('disablesimulcast')
            : 0,
          startVideoMuted: jQuery(element).data('startvideomuted')
            ? jQuery(element).data('startvideomuted')
            : 10,
          startWithVideoMuted: jQuery(element).data('startwithvideomuted')
            ? jQuery(element).data('startwithvideomuted')
            : 0,
          startScreenSharing: jQuery(element).data('startscreensharing')
            ? jQuery(element).data('startscreensharing')
            : 0,
          fileRecordingsEnabled: jQuery(element).data('filerecordingsenabled')
            ? jQuery(element).data('filerecordingsenabled')
            : 0,
          transcribingEnabled: jQuery(element).data('transcribingenabled')
            ? jQuery(element).data('transcribingenabled')
            : 0,
          liveStreamingEnabled: jQuery(element).data('livestreamingenabled')
            ? jQuery(element).data('livestreamingenabled')
            : 0,
          prejoinConfig: {
            enabled: jQuery(element).data('enablewelcomepage')
              ? jQuery(element).data('enablewelcomepage')
              : 0,
          },
          toolbarButtons: toolbarButtons,
        };

      const options = {
        roomName,
        width,
        height,
        parentNode: element,
        configOverwrite: configOverwrite,
        interfaceConfigOverwrite: {
          SHOW_CHROME_EXTENSION_BANNER: false,
          SHOW_PROMOTIONAL_CLOSE_PAGE: false,
          SHOW_POWERED_BY: false,
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
      jQuery(element).addClass('jitsi-wrapper-rendered');
    }
  });
}

(function ($) {
  $(document).ready(function () {
    'use strict';

    if (jitsi_pro.api_select == 'jaas') {
      if (jitsi_pro.jwt) {
        initJitsi();
      } else {
        initFreeJitsi();
      }
    } else if (jitsi_pro.api_select == 'self') {
      initJitsiSelf();
    } else {
      initFreeJitsi();
    }

    $('.jitsi-usertime').each(function () {
      var date = new Date($(this).data('time'));
      $(this).html(date.toString());
    });

    $('.jitsi-countdown').each(function () {
      var date = new Date($(this).data('time'));
      if ($(this).data('time')) {
        $(this).countdown(date, function (event) {
          if (event.type == 'finish') {
            $(this).html(
              '<span class="jitsi-countedown-block"><span class="jitsi-countdown-value">Meeting is running</span><span class="jitsi-countdown-label">The meeting is started and running</span></span>'
            );
          } else {
            $(this).html(
              event.strftime(
                '<span class="jitsi-countedown-block"><span class="jitsi-countdown-value">%D</span><span class="jitsi-countdown-label">Days</span></span>' +
                  '<span class="jitsi-countedown-block"><span class="jitsi-countdown-value">%H</span><span class="jitsi-countdown-label">Hours</span></span>' +
                  '<span class="jitsi-countedown-block"><span class="jitsi-countdown-value">%M</span><span class="jitsi-countdown-label">Minutes</span></span>' +
                  '<span class="jitsi-countedown-block"><span class="jitsi-countdown-value">%S</span><span class="jitsi-countdown-label">Seconds</span></span>'
              )
            );
          }
        });
      }
    });

    $('#attendee_registration_form').on('submit', function (e) {
      e.preventDefault();
      var name = $.trim($(this).find('#meeting_rname').val());
      var email = $.trim($(this).find('#meeting_remail').val());
      var data = $(this).serialize();
      if (!name) {
        $(this)
          .children('.form-message')
          .addClass('form-error')
          .removeClass('form-success')
          .text('Please enter your name before submit');
        return false;
      } else if (!email) {
        $(this)
          .children('.form-message')
          .addClass('form-error')
          .removeClass('form-success')
          .text('Please enter valid email before submit');
        return false;
      } else if (!isEmail(email)) {
        $(this)
          .children('.form-message')
          .addClass('form-error')
          .removeClass('form-success')
          .text('Please enter valid email before submit');
        return false;
      } else {
        $(this)
          .children('.form-message')
          .removeClass('form-success form-error')
          .text('');
        let that = this;
        $.ajax({
          type: 'post',
          dataType: 'json',
          url: jitsi_pro.ajaxurl,
          data: data,
          beforeSend: function () {
            $(that)
              .children('.form-message')
              .removeClass('form-success form-error')
              .text('Submitting registration. Please wait...');
          },
          success: function (response) {
            if (response.type == 'success') {
              $(that)
                .children('.form-message')
                .addClass('form-success')
                .removeClass('form-error')
                .text(response.statusText);
            } else {
              $(that)
                .children('.form-message')
                .addClass('form-error')
                .removeClass('form-success')
                .text(response.statusText);
            }
          },
          error: function (errorThrown) {
            $(that)
              .children('.form-message')
              .addClass('form-error')
              .removeClass('form-success')
              .text(errorThrown.statusText);
          },
        });
      }
    });
  });
})(jQuery);

function isEmail(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);
}

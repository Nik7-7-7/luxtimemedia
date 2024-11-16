/*
* Ultimate Learning Pro - Youtube Functions
*/
"use strict";
var ulpYoutube = {
    playerId        : 'ulp_youtube_player',
    lessonId        : 0,
    autoplay        : 0,
    autocomplete    : 0,
    target          : '',
    width           : 450,
    height          : 390,

    init: function( args ){
        if ( typeof args == 'object' ){
            args = args;
        } else {
            args = JSON.parse( args );
        }

        var obj = this;
        obj.setAttributes(obj, args);

        obj.initPlayer( obj );
    },

    initPlayer: function( obj ){
        window.onYouTubeIframeAPIReady = function () {
            var player = new YT.Player( obj.playerId, {
                height      : obj.height,
                width       : obj.width,
                videoId     : obj.target,
                playerVars  : {
                              'autoplay'                : obj.autoplay,
                              'controls'                : 0,
                              'cc_load_policy'          : 0,
                              'rel'                     : 0,
                },
                events      : {
                    'onReady'         : obj.onPlayerReady,
                    'onStateChange'   : obj.onPlayerStateChange
                }
            });
        }
    },

    onPlayerReady: function(obj){

    },

    onPlayerStateChange: function( evt ){
        if ( evt.data == 0 ){
          if ( ulpYoutube.autocomplete == 0 ){
              return;
          }
          jQuery.ajax({
              type: 'post',
              url: decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
              data: {
                      action       : "ulp_complete_lesson",
                      lesson_id    : ulpYoutube.lessonId,
              },
              success: function( response ){

              }
          });

        }
    },

    setAttributes: function(obj, args){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

}

ulpYoutube.init( ulp_youtube_settings );

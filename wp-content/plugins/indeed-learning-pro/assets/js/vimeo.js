/*
* Ultimate Learning Pro - Vimeo Functions
*/
"use strict";
var ulpVimeo = {
  playerId        : 'ulp_vimeo_player',
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

    var options = {
        url       : obj.target,
        loop      : false,
        autoplay  : obj.autoplay,
        width     : obj.width,
        height    : obj.height,
    }

    var player = new Vimeo.Player( 'ulp_vimeo_player', options );

    player.on( 'play', function() {
        obj.onVideoPlay( obj );
    })

    player.on( 'ended', function() {
        obj.onFinishVideo( obj );
    });

  },

  onFinishVideo: function( obj ){
      if ( obj.autocomplete == 0 ){
          return;
      }
      jQuery.ajax({
    			type: 'post',
    			url: decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
    			data: {
    	                   action: "ulp_complete_lesson",
    	                   lesson_id: obj.lessonId,
    			},
    			success: function( response ){

    			}
  		})
  },

  onVideoPlay: function( obj ){

  },

  setAttributes: function(obj, args){
      for (var key in args) {
        obj[key] = args[key];
      }
  },

}

ulpVimeo.init( ulp_vimeo_settings );

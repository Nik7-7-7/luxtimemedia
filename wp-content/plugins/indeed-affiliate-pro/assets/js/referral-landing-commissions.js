/*
 *Ultimate Affiliate Pro - Landing Commission module
 */
"use strict";
window.addEventListener( 'DOMContentLoaded', function(){
  jQuery( '.uap-js-referral-landing-commisions-data' ).each( function( e, html ){
      var expire = jQuery( html ).attr('data-expire');
      var shortcodeId = jQuery( html ).attr('data-shortcode_id');
      if (expire) {
            var date = new Date();
            date.setTime(date.getTime()+(expire * 60 * 60 * 1000));
            var e = date.toGMTString();
        } else {
          var date = new Date();
          var e = date.toGMTString();
        }
        document.cookie = 'uaplandingcommission_' + shortcodeId + '=true; expires=' + e + '; path=/';
  });
});

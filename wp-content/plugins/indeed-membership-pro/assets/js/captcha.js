/*
* Ultimate Membership Pro - ReCaptcha
*/
"use strict";
jQuery( window ).on( 'load', function(){
  //"ready" method name established by Google, NOT jQuery Event and no jQuery On Method is applicable
    grecaptcha.ready(function() {
        var key = jQuery( '.ihc-js-recaptcha-v3-key' ).attr( 'data-value' );
        grecaptcha.execute( key, { action: 'homepage' } ).then(function(token) {
            jQuery('.js-ump-recaptcha-v3-item').html('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
        });
    });
});

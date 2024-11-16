/*
 *Ultimate Affiliate Pro - ReCaptcha
 */
"use strict";
//"ready" method name established by Google, NOT jQuery Event and no JjQuery On Method is applicable
jQuery( window ).on( 'load', function(){
    grecaptcha.ready(function() {
        var uapCaptchaKey = jQuery( '.uap-js-register-captcha-key' ).attr('data-value');
        grecaptcha.execute( uapCaptchaKey, { action: 'homepage' } ).then(function(token) {
            // console.log( uapCaptchaKey );
            // console.log( token );
            jQuery('.js-uap-recaptcha-v3-item').html('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
        });
    });
});

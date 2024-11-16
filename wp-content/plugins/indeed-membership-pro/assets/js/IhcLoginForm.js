/**
 * Ultimate Membership Pro - Login Form
 */
"use strict";
var IhcLoginForm = {

    init									: function( args ){
        var obj = this;

        // login modal
        if ( jQuery( '.ihc-js-login-popup-data' ).length ){
            jQuery( '.ihc-js-login-popup-data' ).each( function(){

                // login modal
                if ( jQuery( this ).attr('data-is_register_page') == '1' ){
                    jQuery('.ihc-modal-trigger-login').on( 'click', function() {
                        jQuery('html, body').animate({
                            scrollTop: jQuery( '.ihc-login-form-wrap' ).offset().top
                        }, 1000);
                    });
                } else if ( jQuery( this ).attr('data-is_logged') == '1' ){
                    jQuery('.ihc-modal-trigger-login').on( 'click', function() {
                        return false;
                    });
                } else {
                    if ( typeof IhcLoginModal !== 'undefined' ){
                        var triggerSelector = jQuery( this ).attr('data-trigger_selector');
                        var preventDefault = jQuery( this ).attr('data-trigger_default');
                        var autostart = jQuery( this ).attr('data-autoStart');
                        IhcLoginModal.init({
                                  triggerModalSelector  : triggerSelector,
                                  preventDefault        : preventDefault,
                                  autoStart             : autostart
                        });
                    }
                }
            });
        }

        //  show / hide password
        obj.showHidePassword();

    },

    checkFields                 : function( t, e ){
        var n = jQuery('#notice_' + t);
        n.remove();
        var target = jQuery('#ihc_login_form [name='+t+']').parent();
        var v = jQuery('#ihc_login_form [name='+t+']').val();
        if (v==''){
            jQuery(target).append('<div class="ihc-login-notice" id="notice_' + t + '">' + e + '</div>');
        }
    },

    showHidePassword: function(){
      // show - hide password
      if ( jQuery( '.ihc-hide-login-pw' ).length > 0 ){
        jQuery('.ihc-hide-login-pw').each(function(index, button) {
          jQuery(button).on( 'click', function () {
            var pass = jQuery(button).prev();
            if ( 'password' === pass.attr( 'type' ) ) {
              pass.attr( 'type', 'text' );
              jQuery( this ).children().removeClass( 'dashicons-visibility' ).addClass('dashicons-hidden');
            } else {
              pass.attr( 'type', 'password' );
              jQuery( this ).children().removeClass( 'dashicons-hidden' ).addClass('dashicons-visibility');
            }
          });
        });
      }
    },
};

window.addEventListener( 'load', function(){

    IhcLoginForm.init();
    // check fields
    if ( jQuery( '.ihc-js-login-data' ).length ){
        var user_field = jQuery( '.ihc-js-login-data' ).attr('data-user_field');
        var password_field = jQuery( '.ihc-js-login-data' ).attr('data-password_field');
        var error_message = jQuery( '.ihc-js-login-data' ).attr('data-error_message');
        jQuery( user_field ).on('blur', function(){
            IhcLoginForm.checkFields('log', error_message );
        });
        jQuery( password_field ).on('blur', function(){
            IhcLoginForm.checkFields('pwd', error_message );
        });
        jQuery('#ihc_login_form').on('submit', function(e){
            e.preventDefault();
            var u = jQuery('#ihc_login_form [name=log]').val();
            var p = jQuery('#ihc_login_form [name=pwd]').val();
            if (u!='' && p!=''){
              document.getElementById( 'ihc_login_form' ).submit();
            } else {
              IhcLoginForm.checkFields('log', error_message );
              IhcLoginForm.checkFields('pwd', error_message );
              return false;
            }
        });
    }


});

/*
* Ultimate Membership Pro - Register Box Modal
*/
"use strict";
var IhcRegisterModal = {
	modalSelector						     : '#ihc_register_modal',
  triggerModalSelector         : '.ihc-register-modal-trigger',
	preventDefault							 : 0,

	init: function(args){
		var obj = this;
		obj.setAttributes(obj, args);
    obj.initModal(obj);
    jQuery(obj.triggerModalSelector).on('click', function(evt){
        obj.handleOpenModal(obj, evt);
    });

		// show - hide password
		if ( jQuery( '.ihc-hide-pw' ).length > 0 ){
			jQuery('.ihc-hide-pw').each(function(index, button) {
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

  setAttributes: function(obj, args){
		for (var key in args) {
			obj[key] = args[key];
		}
	},

  initModal: function(obj){
      jQuery(obj.modalSelector).iziModal({
  				title: jQuery(obj.modalSelector).attr('data-title'),
  				headerColor: '#88A0B9',
  				background: null,
  				theme: 'light',  // light
  				width: 600,
  				top: null,
  				bottom: null,
  				borderBottom: true,
  				padding: 20,
  				radius: 3,
  				zindex: 9999,
  				focusInput: true,
  				autoOpen: 0, // Boolean, Number
  				bodyOverflow: false,
  				closeOnEscape: true,
  				closeButton: true,
  				appendTo: 'body', // or false
  				appendToOverlay: 'body', // or false
  				overlay: true,
  				overlayClose: true,
  				overlayColor: 'rgba(0, 0, 0, 0.4)',
  				transitionIn: 'comingIn',
  				transitionOut: 'comingOut',
  				transitionInOverlay: 'fadeIn',
  				transitionOutOverlay: 'fadeOut',
					onOpening: function(){},
  				onClosing: function(){},
  				onClosed: function(){},
  				afterRender: function(){}
  		})
  },

  handleOpenModal: function( obj, evt ){
			if (obj.preventDefault){
					evt.preventDefault();
			}
      jQuery(obj.modalSelector).iziModal('open');
  },

}

window.addEventListener( 'load', function(){
		// register modal
		if ( jQuery( '.ihc-js-register-popup-data' ).length ){
				if ( jQuery( '.ihc-js-register-popup-data' ).attr('data-is_register_page') == '1' ){
						jQuery('.ihc-modal-trigger-register' ).on( 'click', function() {
								jQuery('html, body').animate({
										scrollTop: jQuery( '.ihc-form-create-edit' ).offset().top
								}, 1000);
						});
				} else if ( jQuery( '.ihc-js-register-popup-data' ).attr('data-is_registered') == '1' ){
						jQuery('.ihc-modal-trigger-register').on( 'click', function() {
								return false;
						});
				} else {
						if ( typeof IhcRegisterModal !== 'undefined' ){
								var triggerSelector = jQuery( '.ihc-js-register-popup-data' ).attr('data-trigger_selector');
								var preventDefault = jQuery( '.ihc-js-register-popup-data' ).attr('data-trigger_default');
								IhcRegisterModal.init({
													triggerModalSelector  : triggerSelector,
													preventDefault        : preventDefault
								});
						}
				}
		}
});

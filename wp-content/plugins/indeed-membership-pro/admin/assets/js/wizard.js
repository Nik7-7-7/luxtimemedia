/*
* Ultimate Membership Pro - Wizard
*/
"use strict";
var iumpWizard = {
    status    : '',

    init      : function(args){
        var obj = this;
        obj.setAttributes(obj, args);

        // next page button
        jQuery( '.iump-js-wizard-go-next' ).on( 'click', function(){
            obj.Loading();
            obj.NextPage( obj, false );
        } );
        // end of next page button

        // previous page button
        jQuery( '.iump-js-wizard-go-back' ).on( 'click', function(){
            obj.Loading();
            obj.PrevPage( obj );
        } );
        // end of previous page button

        // skip page 1 - button
        jQuery( '.iump-js-wizard-skip-step-1' ).on('click', function(){
            obj.Loading();
            obj.NextPage( obj, true );
        });
        // end of skip page 1 - button

        // license
        jQuery( '[name=ihc_save_licensing_code]' ).on( 'click', function(){
                jQuery.ajax({
                    type : "post",
                    url : decodeURI( window.ihc_site_url ) + '/wp-admin/admin-ajax.php',
                    data : {
                               action						: "ihc_wizard_el_check_get_url_ajax",
                               s 								: jQuery( '[name=pv2]' ).val(),
                               nonce						: jQuery( '.ihc-js-help-page-data' ).attr( 'data-nonce' ),
                               n                : jQuery( '[name=cn]' ).val(),
                               v                : jQuery( '[name=ls]' ).val(),
                           },
                    success: function ( response ) {
                        if ( response ){
                            window.location.href = response;
                        } else {
    												jQuery( '[name=pv2]' ).parent().append( "<span class='iump-wizard-field-notice'>" + jQuery( '.ihc-js-help-page-data' ).attr( 'data-help' ) + "</span>" );
                        }
                    }
                });
                return false;
        });
        // end of license

        // skip button
        jQuery( '.iump-js-skip-wizard' ).on('click', function(){
            jQuery.ajax({
                  type : "post",
                  url : decodeURI( window.ihc_site_url ) + '/wp-admin/admin-ajax.php',
                  data : {
                             action						: "ihc_ajax_wizard_do_skip"
                         },
                  success: function ( responseAsJSON ) {
                      var response = jQuery.parseJSON( responseAsJSON );
                      if ( typeof( response.status ) !== 'undefined' && response.status == 1 ){
                          window.location.href = jQuery('.iump-js-skip-wizard').attr('data-redirect');
                      }
                  }
            });
        });
        // end of skip button

        // bank transfer
        jQuery( '.iump-js-wizard-activate-bt' ).on( 'change', function(){
            if ( parseInt( jQuery( '#ihc_bank_transfer_status' ).val() ) === 1 ){
                // show bt settings
                jQuery( '.iump-js-bt-wrapp' ).attr( 'class', 'iump-wizard-payment-settings-wrapper iump-js-bt-wrapp ihc-display-block' );
            } else {
                // hide bt settings
                jQuery( '.iump-js-bt-wrapp' ).attr( 'class', 'iump-wizard-payment-settings-wrapper iump-js-bt-wrapp ihc-display-none' );
            }
            obj.UpdateDefaultPayment(obj);
        });
        // end of bank transfer

        // stripe connect
        jQuery( '.iump-js-wizard-activate-stripe-connect' ).on( 'change', function(){
            if ( parseInt( jQuery( '#ihc_stripe_connect_status' ).val() ) === 1 ){
                // show stripe connect settings
                jQuery( '.iump-js-stripe-connect-wrapp' ).attr( 'class', 'iump-wizard-payment-settings-wrapper iump-js-stripe-connect-wrapp ihc-display-block' );
            } else {
                // hide stripe connect settings
                jQuery( '.iump-js-stripe-connect-wrapp' ).attr( 'class', 'iump-wizard-payment-settings-wrapper iump-js-stripe-connect-wrapp ihc-display-none' );
            }
            obj.UpdateDefaultPayment(obj);
        });
        // end of stripe connect

        // paypal
        jQuery( '.iump-js-wizard-activate-paypal' ).on( 'change', function(){
            if ( parseInt( jQuery( '#ihc_paypal_status' ).val() ) === 1 ){
                // show paypal settings
                jQuery( '.iump-js-paypal-wrapp' ).attr( 'class', 'iump-wizard-payment-settings-wrapper iump-js-paypal-wrapp ihc-display-block' );
            } else {
                // hide paypal settings
                jQuery( '.iump-js-paypal-wrapp' ).attr( 'class', 'iump-wizard-payment-settings-wrapper iump-js-paypal-wrapp ihc-display-none' );
            }
            obj.UpdateDefaultPayment(obj);
        });
        // end of paypal

        // membership levels - add/edit datepicker
        jQuery('#access_interval_start').datepicker({
            dateFormat : 'dd-mm-yy',
            onSelect: function(selectedDate) {
                if ( typeof selectedDate !== 'undefined' && selectedDate !== '' ){
                    obj.removeError( 'access_interval_start' );
                }
            }
        });
        jQuery('#access_interval_end').datepicker({
            dateFormat : 'dd-mm-yy',
            onSelect: function(selectedDate) {
                if ( typeof selectedDate !== 'undefined' && selectedDate !== '' ){
                    obj.removeError( 'access_interval_end' );
                }
            }
        });
        // end of membership levels - add/edit datepicker

        // required fields - remove notice class and message
        jQuery( '.iump-js-wizard-required-field' ).on( 'blur', function(){
            if ( this.value !== '' ){
                // remove notice class if its case
                if ( jQuery( this ).hasClass( 'ihc-input-notice' ) ){
                    jQuery( this ).removeClass( 'ihc-input-notice' );
                }
                var fieldType = jQuery( this ).attr( 'name' );
                obj.removeError( fieldType );
            }
        });

        // type of membership - custom buttons
        jQuery( '.iump-wizard-access-type-item' ).on( 'click', function(){
            jQuery( '.iump-wizard-js-access-type-value' ).val( jQuery(this).attr('data-value') );
            ihcAccessPaymentType(jQuery(this).attr('data-value'));
            jQuery( '.iump-wizard-access-type-item' ).removeClass('iump-wizard-ati-selected');
            jQuery( this ).addClass( 'iump-wizard-ati-selected' );
        });

        // currency
        jQuery( '.iump-wizard-default-currency' ).on( 'change', function(){
            jQuery( '.iump-wizard-currency-of-price' ).html( this.value );
        });

        //paypal sandbox
        jQuery('[name=radio_for_ihc_paypal_sandbox]').on( 'click', function(){
            if ( this.value == 0 ){
                jQuery( '.iump-wizard-paypal-sandbox-sign' ).css( 'display', 'none' );
            } else {
                jQuery( '.iump-wizard-paypal-sandbox-sign' ).css( 'display', 'inline-block' );
            }
        });

        // bank transfer message
        let editor = tinymce.get("ihc_bank_transfer_message");
        editor.contentDocument.addEventListener('keyup', function (e) {
              jQuery('#ihc_bank_transfer_message').html( editor.getContent() );
              if ( editor.getContent() !== '' ){
                  obj.removeError('ihc_bank_transfer_message');
              }
        });
    },

    setAttributes: function(obj, args){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

    NextPage    : function( obj, force_next ){

        var currentPage = parseInt(jQuery( '.iump-js-wizard-content-wrapp' ).attr( 'data-current_page' ));
        var nextPage = currentPage + 1;

        var formValues = {};
        var formElements = jQuery( '.iump-js-wizard-step-' + currentPage).find("select, textarea, input").each(function( i, v ) {
           formValues[ jQuery( this ).attr('name') ] = jQuery( this ).val();
        });

        // send via ajax
        jQuery.ajax({
              type : "post",
              url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
              data : {
                         action					: 'ihc_ajax_wizard_save_page',
                         page           : currentPage,
                         form_values    : JSON.stringify( formValues ),
              },
              success: function ( responseAsJSON ) {
                  obj.RemoveLoading();
                  var response = jQuery.parseJSON( responseAsJSON );
                  if ( ( typeof( response.status ) !== 'undefined' && response.status == 1 ) || force_next ){
                      jQuery( '.iump-js-wizard-step-' + currentPage ).attr( 'class', 'iump-js-wizard-step-' + currentPage + ' ihc-display-none' );
                      jQuery( '.iump-js-wizard-step-' + nextPage ).attr( 'class', 'iump-js-wizard-step-' + nextPage + ' ihc-display-block' );
                      jQuery( '.iump-js-wizard-content-wrapp' ).attr( 'data-current_page', nextPage );

                      obj.UpdatePagination( obj );

                      // its page 2, update lid
                      if ( typeof(response.level_id) !== 'undefined' && response.level_id ){
                          jQuery( '.iump-js-wizard-lid-field' ).val( response.level_id );
                      }
                  } else if ( typeof( response.status ) !== 'undefined' && response.status == 0 ) {
                      /// an error have occured ...
                      if ( typeof( response.field_message ) !== 'undefined' && typeof( response.target_field ) !== 'undefined' ){
                          obj.setError( response.target_field, response.field_message );
                      } else if ( typeof ( response.general_message ) !== 'undefined' ){
                          jQuery( '.iump-wizard-before-message' ).after( "<div class='iump-wizard-message iump-wizard-field-notice'>" + response.general_message + "</div>" );
                      }

                  }
              }
         });
    },

    PrevPage    : function( obj ){
        var currentPage = parseInt(jQuery( '.iump-js-wizard-content-wrapp' ).attr( 'data-current_page' ));
        var prevPage = currentPage - 1;
        jQuery( '.iump-js-wizard-step-' + currentPage ).attr( 'class', 'iump-js-wizard-step-' + currentPage + ' ihc-display-none' );
        jQuery( '.iump-js-wizard-step-' + prevPage ).attr( 'class', 'iump-js-wizard-step-' + prevPage + ' ihc-display-block' );
        jQuery( '.iump-js-wizard-content-wrapp' ).attr( 'data-current_page', prevPage );
        obj.UpdatePagination( obj );
        obj.RemoveLoading();
    },

    UpdatePagination: function( obj ){
        var currentPage = parseInt( jQuery( '.iump-js-wizard-content-wrapp' ).attr( 'data-current_page' ) );
        if ( currentPage > 1 ){
            // page 2,3,4,5
            jQuery( '#iump_wizard_go_back_bttn').removeClass( 'ihc-display-none' ).addClass( 'ihc-display-inline' );
            jQuery( '#iump_wizard_skip_step_1' ).removeClass( 'ihc-display-inline' ).addClass( 'ihc-display-none' );
        } else {
            jQuery( '#iump_wizard_go_back_bttn').removeClass( 'ihc-display-inline' ).addClass( 'ihc-display-none' );
            jQuery( '#iump_wizard_skip_step_1' ).removeClass( 'ihc-display-none' ).addClass( 'ihc-display-inline' );
        }
        if ( currentPage < 5 ){
            jQuery( '#iump_wizard_go_next_bttn span' ).html( jQuery( '#iump_wizard_go_next_bttn' ).attr('data-next') );
        } else {
            // switch label
            jQuery( '#iump_wizard_go_next_bttn span' ).html( jQuery( '#iump_wizard_go_next_bttn' ).attr('data-complete_label') );
        }
        if ( currentPage === 6 ){
            // complete setup page
            jQuery( '#iump_wizard_skip_the_setup_bttn' ).removeClass( 'ihc-display-block' ).addClass( 'ihc-display-none' );
            jQuery( '#iump_wizard_go_back_bttn').removeClass( 'ihc-visibility-visible' ).addClass( 'ihc-visibility-hidden' );
            jQuery( '#iump_wizard_go_next_bttn').removeClass( 'ihc-visibility-visible' ).addClass( 'ihc-visibility-hidden' );
        }
        //
        jQuery( '.iump-wizard-progress-bar-wrapp' ).children().each( function(){
            jQuery( this ).removeClass( 'iump-wizard-progress-bar-item-selected' );
        });

        var i = 1;
        var currentPage = parseInt( jQuery( '.iump-js-wizard-content-wrapp' ).attr( 'data-current_page' ) );

        // top
        for ( i =1; i<7; i++ ){
            if ( i < currentPage ){
                jQuery( '.iump-wizard-pbi-' + i ).addClass( 'iump-wizard-progress-bar-item-completed' );
                jQuery( '.iump-wizard-pbi-' + i + ' .iump-wizard-progress-bar-item-icon-wrapper').html('<span><svg viewBox="64 64 896 896" focusable="false" data-icon="check" width="1.4em" height="1.4em" fill="currentColor" aria-hidden="true"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg></span>');
            } else if( i === currentPage ){
              jQuery( '.iump-wizard-pbi-' + currentPage ).removeClass( 'iump-wizard-progress-bar-item-completed' );
              jQuery( '.iump-wizard-pbi-' + currentPage + ' .iump-wizard-progress-bar-item-icon-wrapper').html('<span><span class="iump-wizard-progress-bar-step">' + currentPage + '</span></span>');
              jQuery( '.iump-wizard-pbi-' + currentPage ).addClass( 'iump-wizard-progress-bar-item-selected' );
            } else {
              jQuery( '.iump-wizard-pbi-' + i ).removeClass( 'iump-wizard-progress-bar-item-completed' );
            }
        }

    },

    Loading : function(){
        // put the entire content on loading
        jQuery( '#iump_wizard_content' ).addClass( 'iump-wizard-content-loading-wrapp' );
        jQuery( '.iump-wizard-field-notice' ).each(function(){
            var currentSelectorNotice = jQuery( this ).attr( 'id' );
            currentSelectorNotice = '#' + currentSelectorNotice;
            if ( jQuery( currentSelectorNotice ).hasClass( 'ihc-display-block' ) ){
            	  jQuery( currentSelectorNotice ).html( '' );
                jQuery( currentSelectorNotice ).removeClass( 'ihc-display-block' ).addClass( 'ihc-display-none' );
            }
        });
    },

    RemoveLoading : function(){
        jQuery( '#iump_wizard_content' ).removeClass( 'iump-wizard-content-loading-wrapp' );
        // remove messages if its case
        jQuery( '.iump-wizard-message' ).remove();
    },

    UpdateDefaultPayment : function( obj ){
        if ( parseInt( jQuery( '#ihc_stripe_connect_status' ).val() ) === 1 ){
            // stripe connect
            jQuery( '.iump-js-wizard-default-payment' ).val( 'stripe_connect' );
            return;
        } else if ( parseInt( jQuery( '#ihc_paypal_status' ).val() ) === 1 ){
            // paypal
            jQuery( '.iump-js-wizard-default-payment' ).val( 'paypal' );
            return;
        } else if ( parseInt( jQuery( '#ihc_bank_transfer_status' ).val() ) === 1 ){
            // bt
            jQuery( '.iump-js-wizard-default-payment' ).val( 'bank_transfer' );
            return;
        }
    },

    setError : function( field, message ){
        var selector = '#iump_js_error_message_for_' + field;
        if ( jQuery( selector ).hasClass( 'ihc-display-none' ) ){
            jQuery( selector ).html( message );
            jQuery( selector ).removeClass( 'ihc-display-none' ).addClass( 'ihc-display-block' );
            jQuery( '[name="' + field + '"]' ).addClass( 'ihc-input-notice' );
        }
    },

    removeError : function( field ){
        var selector = '#iump_js_error_message_for_' + field;
        if ( jQuery( selector ).hasClass( 'ihc-display-block' ) ){
            jQuery( selector ).html( '' );
            jQuery( selector ).removeClass( 'ihc-display-block' ).addClass( 'ihc-display-none' );
            jQuery( '[name="' + field + '"]' ).removeClass( 'ihc-input-notice' );
        }
    },

}

jQuery(window).on('load', function(){
    iumpWizard.init([]);
});

function iumpUpdateStripeConnectAuthUrlWizard()
{
		jQuery( '.ihc-js-stripe-connect-live' ).removeClass( 'ihc-display-block' );
		jQuery( '.ihc-js-stripe-connect-live' ).removeClass( 'ihc-display-none' );
		jQuery( '.ihc-js-stripe-connect-sandbox' ).removeClass( 'ihc-display-block' );
		jQuery( '.ihc-js-stripe-connect-sandbox' ).removeClass( 'ihc-display-none' );
		if ( jQuery('[name=ihc_stripe_connect_live_mode]').val() == 1 ){
				// live mode
				jQuery( '.ihc-js-stripe-connect-live' ).addClass( 'ihc-display-block' );
				jQuery( '.ihc-js-stripe-connect-sandbox' ).addClass( 'ihc-display-none' );
		} else {
				// sandbox mode
				jQuery( '.ihc-js-stripe-connect-live' ).addClass( 'ihc-display-none' );
				jQuery( '.ihc-js-stripe-connect-sandbox' ).addClass( 'ihc-display-block' );
		}
}

function iumpPutValueIntoHidden( nameTarget, value )
{
    jQuery("[name='"+nameTarget+"']").val( value );
}

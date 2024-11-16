/*
* Ultimate Affiliate Pro - Wizard
*/
"use strict";
var uapWizard = {
    status    : '',

    init      : function(args){
        var obj = this;
        obj.setAttributes(obj, args);

        // next page button
        jQuery( '.uap-js-wizard-go-next' ).on( 'click', function(){
            obj.Loading();
            obj.NextPage( obj, false );
        } );
        // end of next page button

        // previous page button
        jQuery( '.uap-js-wizard-go-back' ).on( 'click', function(){
            obj.Loading();
            obj.PrevPage( obj );
        } );
        // end of previous page button

        // skip page 1 - button
        jQuery( '.uap-js-wizard-skip-step-1' ).on('click', function(){
            obj.Loading();
            obj.NextPage( obj, true );
        });
        // end of skip page 1 - button

        // lc
        jQuery( '.uap-js-wizard-submit-c-to-e' ).on( 'click', function(){
                jQuery.ajax({
                    type : "post",
                    url : decodeURI( window.uap_url ) + '/wp-admin/admin-ajax.php',
                    data : {
                               action						: "uap_wizard_el_check_get_url_ajax",
                               s 								: jQuery( '[name=h]' ).val(),
                               nonce						: jQuery( '.uap-js-help-page-data' ).attr( 'data-nonce' ),
    													 ls								: jQuery( '.uap-js-admin-help-section-ls' ).val(),
    													 ame 							: jQuery( '.uap-js-admin-help-section-name').val()
                    },
                    success: function ( response ) {
                        if ( response ){
                            window.location.href = response;
                        } else {
                            jQuery( '[name=h]' ).parent().append( "<span class='uap-wizard-field-notice'>" + jQuery( '.uap-js-help-page-data' ).attr( 'data-help' ) + "</span>" );
                        }
                    }
                });
                return false;
        });
        // end of lc

        // skip button
        jQuery( '.uap-js-skip-wizard' ).on('click', function(){
            jQuery.ajax({
                  type : "post",
                  url : decodeURI( window.uap_url ) + '/wp-admin/admin-ajax.php',
                  data : {
                             action						: "uap_ajax_wizard_do_skip"
                         },
                  success: function ( responseAsJSON ) {
                      var response = jQuery.parseJSON( responseAsJSON );
                      if ( typeof( response.status ) !== 'undefined' && response.status == 1 ){
                          window.location.href = jQuery('.uap-js-skip-wizard').attr('data-redirect');
                      }
                  }
            });
        });
        // end of skip button

        // required fields - remove notice class and message
        jQuery( '.uap-js-wizard-required-field' ).on( 'blur', function(){
            if ( this.value !== '' ){
                // remove notice class if its case
                if ( jQuery( this ).hasClass( 'uap-input-notice' ) ){
                    jQuery( this ).removeClass( 'uap-input-notice' );
                }
                var fieldType = jQuery( this ).attr( 'name' );
                obj.removeError( fieldType );
            }
        });
    },

    setAttributes: function(obj, args){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

    NextPage    : function( obj, force_next ){

        var currentPage = parseInt(jQuery( '.uap-js-wizard-content-wrapp' ).attr( 'data-current_page' ));
        var nextPage = currentPage + 1;

        var formValues = {};
        var formElements = jQuery( '.uap-js-wizard-step-' + currentPage).find("select, textarea, input").each(function( i, v ) {
           formValues[ jQuery( this ).attr('name') ] = jQuery( this ).val();
        });

        // send via ajax
        jQuery.ajax({
              type : "post",
              url : decodeURI( window.uap_url ) + '/wp-admin/admin-ajax.php',
              data : {
                         action					: 'uap_ajax_wizard_save_page',
                         page           : currentPage,
                         form_values    : JSON.stringify( formValues ),
              },
              success: function ( responseAsJSON ) {
                  obj.RemoveLoading();
                  var response = jQuery.parseJSON( responseAsJSON );
                  if ( ( typeof( response.status ) !== 'undefined' && response.status == 1 ) || force_next ){
                      jQuery( '.uap-js-wizard-step-' + currentPage ).attr( 'class', 'uap-js-wizard-step-' + currentPage + ' uap-display-none' );
                      jQuery( '.uap-js-wizard-step-' + nextPage ).attr( 'class', 'uap-js-wizard-step-' + nextPage + ' uap-display-block' );
                      jQuery( '.uap-js-wizard-content-wrapp' ).attr( 'data-current_page', nextPage );

                      obj.UpdatePagination( obj );

                      // its page 2, update lid
                      if ( typeof(response.level_id) !== 'undefined' && response.level_id ){
                          jQuery( '.uap-js-wizard-lid-field' ).val( response.level_id );
                      }
                  } else if ( typeof( response.status ) !== 'undefined' && response.status == 0 ) {
                      /// an error have occured ...
                      if ( typeof( response.field_message ) !== 'undefined' && typeof( response.target_field ) !== 'undefined' ){
                          obj.setError( response.target_field, response.field_message );
                      } else if ( typeof ( response.general_message ) !== 'undefined' ){
                          jQuery( '.uap-wizard-before-message' ).after( "<div class='uap-wizard-message uap-wizard-field-notice'>" + response.general_message + "</div>" );
                      }

                  }
              }
         });
    },

    PrevPage    : function( obj ){
        var currentPage = parseInt(jQuery( '.uap-js-wizard-content-wrapp' ).attr( 'data-current_page' ));
        var prevPage = currentPage - 1;
        jQuery( '.uap-js-wizard-step-' + currentPage ).attr( 'class', 'uap-js-wizard-step-' + currentPage + ' uap-display-none' );
        jQuery( '.uap-js-wizard-step-' + prevPage ).attr( 'class', 'uap-js-wizard-step-' + prevPage + ' uap-display-block' );
        jQuery( '.uap-js-wizard-content-wrapp' ).attr( 'data-current_page', prevPage );
        obj.UpdatePagination( obj );
        obj.RemoveLoading();
    },

    UpdatePagination: function( obj ){
        var currentPage = parseInt( jQuery( '.uap-js-wizard-content-wrapp' ).attr( 'data-current_page' ) );
        if ( currentPage > 1 ){
            // page 2,3,4,5
            jQuery( '#uap_wizard_go_back_bttn').removeClass( 'uap-display-none' ).addClass( 'uap-display-inline' );
            jQuery( '#uap_wizard_skip_step_1' ).removeClass( 'uap-display-inline' ).addClass( 'uap-display-none' );
        } else {
            jQuery( '#uap_wizard_go_back_bttn').removeClass( 'uap-display-inline' ).addClass( 'uap-display-none' );
            jQuery( '#uap_wizard_skip_step_1' ).removeClass( 'uap-display-none' ).addClass( 'uap-display-inline' );
        }
        if ( currentPage < 5 ){
            jQuery( '#uap_wizard_go_next_bttn span' ).html( jQuery( '#uap_wizard_go_next_bttn' ).attr('data-next') );
        } else {
            // switch label
            jQuery( '#uap_wizard_go_next_bttn span' ).html( jQuery( '#uap_wizard_go_next_bttn' ).attr('data-complete_label') );
        }
        if ( currentPage === 6 ){
            // complete setup page
            jQuery( '#uap_wizard_skip_the_setup_bttn' ).removeClass( 'uap-display-block' ).addClass( 'uap-display-none' );
            jQuery( '#uap_wizard_go_back_bttn').removeClass( 'uap-visibility-visible' ).addClass( 'uap-visibility-hidden' );
            jQuery( '#uap_wizard_go_next_bttn').removeClass( 'uap-visibility-visible' ).addClass( 'uap-visibility-hidden' );
        }
        //
        jQuery( '.uap-wizard-progress-bar-wrapp' ).children().each( function(){
            jQuery( this ).removeClass( 'uap-wizard-progress-bar-item-selected' );
        });

        var i = 1;
        var currentPage = parseInt( jQuery( '.uap-js-wizard-content-wrapp' ).attr( 'data-current_page' ) );

        // top
        for ( i =1; i<7; i++ ){
            if ( i < currentPage ){
                jQuery( '.uap-wizard-pbi-' + i ).addClass( 'uap-wizard-progress-bar-item-completed' );
                jQuery( '.uap-wizard-pbi-' + i + ' .uap-wizard-progress-bar-item-icon-wrapper').html('<span><svg viewBox="64 64 896 896" focusable="false" data-icon="check" width="1.4em" height="1.4em" fill="currentColor" aria-hidden="true"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg></span>');
            } else if( i === currentPage ){
              jQuery( '.uap-wizard-pbi-' + currentPage ).removeClass( 'uap-wizard-progress-bar-item-completed' );
              jQuery( '.uap-wizard-pbi-' + currentPage + ' .uap-wizard-progress-bar-item-icon-wrapper').html('<span><span class="uap-wizard-progress-bar-step">' + currentPage + '</span></span>');
              jQuery( '.uap-wizard-pbi-' + currentPage ).addClass( 'uap-wizard-progress-bar-item-selected' );
            } else {
              jQuery( '.uap-wizard-pbi-' + i ).removeClass( 'uap-wizard-progress-bar-item-completed' );
            }
        }

    },

    Loading : function(){
        // put the entire content on loading
        jQuery( '#uap_wizard_content' ).addClass( 'uap-wizard-content-loading-wrapp' );
        jQuery( '.uap-wizard-field-notice' ).each(function(){
            var currentSelectorNotice = jQuery( this ).attr( 'id' );
            currentSelectorNotice = '#' + currentSelectorNotice;
            if ( jQuery( currentSelectorNotice ).hasClass( 'uap-display-block' ) ){
            	  jQuery( currentSelectorNotice ).html( '' );
                jQuery( currentSelectorNotice ).removeClass( 'uap-display-block' ).addClass( 'uap-display-none' );
            }
        });
    },

    RemoveLoading : function(){
        jQuery( '#uap_wizard_content' ).removeClass( 'uap-wizard-content-loading-wrapp' );
        // remove messages if its case
        jQuery( '.uap-wizard-message' ).remove();
    },

    UpdateDefaultPayment : function( obj ){
        if ( parseInt( jQuery( '#uap_stripe_connect_status' ).val() ) === 1 ){
            // stripe connect
            jQuery( '.uap-js-wizard-default-payment' ).val( 'stripe_connect' );
            return;
        } else if ( parseInt( jQuery( '#uap_paypal_status' ).val() ) === 1 ){
            // paypal
            jQuery( '.uap-js-wizard-default-payment' ).val( 'paypal' );
            return;
        } else if ( parseInt( jQuery( '#uap_bank_transfer_status' ).val() ) === 1 ){
            // bt
            jQuery( '.uap-js-wizard-default-payment' ).val( 'bank_transfer' );
            return;
        }
    },

    setError : function( field, message ){
        var selector = '#uap_js_error_message_for_' + field;
        if ( jQuery( selector ).hasClass( 'uap-display-none' ) ){
            jQuery( selector ).html( message );
            jQuery( selector ).removeClass( 'uap-display-none' ).addClass( 'uap-display-block' );
            jQuery( '[name="' + field + '"]' ).addClass( 'uap-input-notice' );
        }
    },

    removeError : function( field ){
        var selector = '#uap_js_error_message_for_' + field;
        if ( jQuery( selector ).hasClass( 'uap-display-block' ) ){
            jQuery( selector ).html( '' );
            jQuery( selector ).removeClass( 'uap-display-block' ).addClass( 'uap-display-none' );
            jQuery( '[name="' + field + '"]' ).removeClass( 'uap-input-notice' );
        }
    },


}

jQuery( window ).on('load', function(){
    uapWizard.init([]);
});

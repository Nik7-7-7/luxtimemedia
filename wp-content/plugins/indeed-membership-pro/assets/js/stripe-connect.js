/*
* Ultimate Membership Pro - Stripe Connect utilities
*/
"use strict";
var IhcStripeConnect = {
    formId                      : 'createuser',
    stripeObject                : null,
    card                        : null,
    elements                    : null,
    canDoSubmit                 : false,
    stripeSelected              : false,

    init                        : function( args ){
        var object = this;
        if ( window.ihcCheckoutIsRegister === '0' ){
            object.formId = 'checkout';
        }

        // saved cards
        if ( jQuery('[name=ihc_stripe_connect_payment_methods]').length > 0  ){
            jQuery( '[name=ihc_stripe_connect_payment_methods]' ).on( 'click', function(){
                jQuery( '.ihc-stripe-connect-saved-card-wrapper' ).removeClass( 'ihc-stripe-connect-saved-card-wrapper-selected' );
                if ( this.value === 'new' ){
                    // show stripe new card field
                    jQuery( '.ihc-js-stripe-connect-wrapp' ).removeClass( 'ihc-display-none' );
                } else {
                    // hide stripe new card field
                    jQuery( '.ihc-js-stripe-connect-wrapp' ).addClass( 'ihc-display-none' );
                }
                jQuery( this ).parent().addClass( 'ihc-stripe-connect-saved-card-wrapper-selected' );
            });
        }

        if ( jQuery('.ihc-js-connect-do-setup-intent').length == 0 && jQuery('.ihc-js-connect-do-payment-intent').length == 0 ){
            object.removePreventSubmit();
            return;
        }

        ihcAddAction( 'checkeout-payment-type-radio-change', function(){
        		var type = window.IhcCheckout.paymentType;
            if ( type !== 'stripe_connect' ){
                if ( jQuery( '#ihc_submit_bttn' ).length > 0 ){
                    // remove disabled attr from submit button
                    jQuery( '#ihc_submit_bttn' ).removeAttr( 'disabled' );
                    jQuery( '#ihc_submit_bttn' ).attr( 'value', jQuery( '#ihc_submit_bttn' ).attr('data-standard-label') );
                }
                object.removePreventSubmit();
            }
        }, 0 );

        ihcAddAction( 'checkeout-payment-type-select-change', function(){
            var type = window.IhcCheckout.paymentType;
            if ( type !== 'stripe_connect' ){
                if ( jQuery( '#ihc_submit_bttn' ).length > 0 ){
                    // remove disabled attr from submit button
                    jQuery( '#ihc_submit_bttn' ).removeAttr( 'disabled' );
                    jQuery( '#ihc_submit_bttn' ).attr( 'value', jQuery( '#ihc_submit_bttn' ).attr('data-standard-label') );
                }
                object.removePreventSubmit();
            }
        }, 0 );

        ihcAddAction( 'checkeout-payment-type-logos-change', function(){
            var type = window.IhcCheckout.paymentType;
            if ( type !== 'stripe_connect' ){
                if ( jQuery( '#ihc_submit_bttn' ).length > 0 ){
                    // remove disabled attr from submit button
                    jQuery( '#ihc_submit_bttn' ).removeAttr( 'disabled' );
                    jQuery( '#ihc_submit_bttn' ).attr( 'value', jQuery( '#ihc_submit_bttn' ).attr('data-standard-label') );
                }
                object.removePreventSubmit();
            }
        }, 0 );

        if ( jQuery( '[name=payment_selected]' ).val() === 'stripe_connect' ){
            self.IhcStripeConnect.initStripeObject();
            object.activatePreventSubmit();
        }

        // hook into indeed checkout object
        /*
        ihcAddAction( 'checkout-loaded', function(){
        		self.IhcStripeConnect.initStripeObject();
        }, 0 );
        */
        self.IhcStripeConnect.initStripeObject();

    },

    activatePreventSubmit           : function(){
        /// prevent form submit when stripe connect is selected, this will perform an extra ajax check to see if the payment fields are completed
        var theTarget = document.getElementById( self.IhcStripeConnect.formId );
        if ( typeof theTarget === 'undefined' || theTarget === null ){
            return;
        }

        if ( self.IhcStripeConnect.indeedDetectBrowser() === 'Firefox' ){
            // FIREFOX
            self.stripeSelected = true;
            jQuery( '#' + self.IhcStripeConnect.formId ).on( 'submit', self.IhcStripeConnect.preventFormSubmit );
        } else {
            theTarget.addEventListener( 'submit', self.IhcStripeConnect.preventFormSubmit, true );
        }

    },

    removePreventSubmit              : function(){
        var theTarget = document.getElementById( self.IhcStripeConnect.formId );
        if ( typeof theTarget === 'undefined' || theTarget === null ){
            return;
        }
        if ( self.IhcStripeConnect.indeedDetectBrowser() === 'Firefox' ){
            // FIREFOX
            self.stripeSelected = false;
            theTarget.removeEventListener( 'submit', self.IhcStripeConnect.preventFormSubmit );
        } else {
            theTarget.removeEventListener( 'submit', self.IhcStripeConnect.preventFormSubmit, true );
        }

    },

    initStripeObject                  : function(){
      // initiate stripe
      self.IhcStripeConnect.stripeObject = Stripe( window.ihcStripeConnectPublicKey, { stripeAccount: window.ihcStripeConnectAcctNumber, locale: window.ihcStripeConnectLang } );
      //var clientSecret = jQuery('#ihc-js-stripe-connect-card-element').attr('data-client');

      self.IhcStripeConnect.elements = self.IhcStripeConnect.stripeObject.elements( );

      self.IhcStripeConnect.card = self.IhcStripeConnect.elements.create("card", {
        style: {
        base: {
          lineHeight: '50px',
          color: '#444444',
          fontWeight: '500',
          fontFamily: 'Montserrat, Arial, Helvetica',
          fontSize: '15px',
          fontSmoothing: 'antialiased',
          ':-webkit-autofill': {
            backgroundColor: '#fce883',
          },
          '::placeholder': {
            color: '#aaaaaa',
          },
        },
        invalid: {
          iconColor: '#dd3559',
          color: '#dd3559',
        },
      },
        hidePostalCode: true
      });
      self.IhcStripeConnect.card.mount( "#ihc-js-stripe-connect-card-element" );

    },

    preventFormSubmit             : function( evt ){

      if ( self.IhcStripeConnect.indeedDetectBrowser() === 'Firefox' ){
          // special conditions for firefox
          if ( self.stripeSelected == false ){
              return true;
          } else {
              evt.preventDefault();
              evt.stopPropagation();
              evt.stopImmediatePropagation();
              self.IhcStripeConnect.check();
              return false;
          }
      } else {
          evt.preventDefault();
          evt.stopPropagation();
          evt.stopImmediatePropagation();
          self.IhcStripeConnect.check();
          return false;
      }
    },

    check                         : function(){
      if ( self.IhcStripeConnect.formId === 'createuser' ){
          // is register, so we must verify if the form is properly completed
          if ( typeof window.indeedRegisterErrors !== 'undefined' && window.indeedRegisterErrors.length > 0 ){
              return;
          }
          if ( typeof window.ihc_register_fields !== 'undefined'
              && window.ihc_register_fields
              && self.IhcStripeConnect.canDoSubmit === false
              && ( typeof window.ihcRegisterCheckFieldsAjaxFired === 'undefined'
              || window.ihcRegisterCheckFieldsAjaxFired == 0 )
          ){
                self.IhcStripeConnect.checkAllFieldsBeforeSubmit();
                return;
          }
      }

      if ( jQuery('[name=ihc_stripe_connect_payment_methods]').length > 0
            && jQuery('input[name=ihc_stripe_connect_payment_methods]:checked').val() !== 'new'
            && jQuery('[name=ihc_stripe_connect_payment_methods]').val() !== '' ){
         // payment with old card
         var theTarget = document.getElementById( self.IhcStripeConnect.formId );
         self.IhcStripeConnect.removePreventSubmit();
         self.IhcStripeConnect.activateSpinner();
         theTarget.submit();
         return false; /// very important to stop the process here
      } else if ( jQuery('.ihc-js-connect-do-payment-intent').length > 0 ){
          // new card - payment intent

          // set billing details that will be passed to stripe
          var billingDetails = self.IhcStripeConnect.setBillingDetails();

          self.IhcStripeConnect.activateSpinner();

          self.IhcStripeConnect.stripeObject.createPaymentMethod({
            type              : 'card',
            card              : self.IhcStripeConnect.card,
            billing_details   : billingDetails,
          }).then(function(result) {

              if ( jQuery( '#ihc_js_stripe_connect_card_error_message').length > 0 ){
                  jQuery( '#ihc_js_stripe_connect_card_error_message' ).remove();
              }
              if ( typeof result.error !== 'undefined' ){
                  jQuery( '#ihc_stripe_connect_payment_fields' ).append( '<div class="ump-stripe-connect-error-message" id="ihc_js_stripe_connect_card_error_message">' + result.error.message + '</div>' );
                  self.IhcStripeConnect.deactivateSpinner();
                  return false;
              }

              if ( typeof result.paymentMethod.id !== 'undefined' ){
                  // send ajax to get the payment intent or setup intent
                  jQuery.ajax({
                       type 		: "post",
                       url 		: decodeURI(window.ihc_site_url) + '/wp-admin/admin-ajax.php',
                       data 		: {
                                  action							: "ihc_ajax_stripe_connect_generate_payment_intent",
                                  session             : jQuery( '.ihc-js-checkout-session' ).attr( 'data-value'),
                                  payment_method      : result.paymentMethod.id,

                       },
                       success	: function( responseJson ) {
                          var response = JSON.parse( responseJson );
                          if ( response.status === 0 ){
                              self.IhcStripeConnect.deactivateSpinner();
                              return false;
                          }

                          jQuery( '[name=stripe_payment_intent]' ).val( response.payment_intent_id );
                          self.IhcStripeConnect.stripeObject.confirmCardPayment( response.client_secret, {
                                payment_method: {
                                    card: self.IhcStripeConnect.card,
                                    billing_details: billingDetails
                                }
                          }).then(function(result) {
                              if ( typeof result.error !== 'undefined' ){
                                  // write the errors
                                  jQuery( '#ihc_stripe_connect_payment_fields' ).append( '<div class="ump-stripe-connect-error-message" id="ihc_js_stripe_connect_card_error_message">' + result.error.message + '</div>' );
                                  self.IhcStripeConnect.deactivateSpinner();
                                  return false;
                              } else {
                                  //self.IhcStripeConnect.activateSpinner();
                                  var theTarget = document.getElementById( self.IhcStripeConnect.formId );
                                  self.IhcStripeConnect.removePreventSubmit();
                                  theTarget.submit();
                              }
                          });
                       }
                  });
              }
          });
          //return false;
      } else if ( jQuery('.ihc-js-connect-do-setup-intent').length > 0 ){
          // new card - setup intent
          var fullName = jQuery( '[name=ihc_stripe_connect_full_name]' ).val();
          self.IhcStripeConnect.activateSpinner();
          // set billing details that will be passed to stripe
          var billingDetails = self.IhcStripeConnect.setBillingDetails();

          self.IhcStripeConnect.stripeObject.createPaymentMethod({
            type              : 'card',
            card              : self.IhcStripeConnect.card,
            billing_details   : billingDetails,
          }).then(function(result) {
              if ( jQuery( '#ihc_js_stripe_connect_card_error_message').length > 0 ){
                  jQuery( '#ihc_js_stripe_connect_card_error_message' ).remove();
              }
              if ( typeof result.error !== 'undefined' ){
                  jQuery( '#ihc_stripe_connect_payment_fields' ).append( '<div class="ump-stripe-connect-error-message" id="ihc_js_stripe_connect_card_error_message">' + result.error.message + '</div>' );
                  self.IhcStripeConnect.deactivateSpinner();
                  return;
              }

              if ( typeof result.paymentMethod.id !== 'undefined' ){
                  // send ajax to get the payment intent or setup intent
                  jQuery.ajax({
                       type 		: "post",
                       url 		: decodeURI(window.ihc_site_url) + '/wp-admin/admin-ajax.php',
                       data 		: {
                                  action							: "ihc_ajax_stripe_connect_generate_setup_intent",
                                  session             : jQuery( '.ihc-js-checkout-session' ).attr( 'data-value'),
                                  payment_method      : result.paymentMethod.id,

                       },
                       success	: function( responseJson ) {
                          var response = JSON.parse( responseJson );
                          if ( response.status === 0 ){
                              self.IhcStripeConnect.deactivateSpinner();
                              return false;
                          }
                          var fullName = jQuery( '[name=ihc_stripe_connect_full_name]' ).val();
                          jQuery( '[name=stripe_setup_intent]' ).val( response.setup_intent_id );
                          self.IhcStripeConnect.stripeObject.confirmCardSetup( response.client_secret, {
                                payment_method: {
                                    card: self.IhcStripeConnect.card,
                                    billing_details: billingDetails
                                }
                          }).then(function(result) {
                              if ( typeof result.error !== 'undefined' ){
                                  jQuery( '#ihc_stripe_connect_payment_fields' ).append( '<div class="ump-stripe-connect-error-message" id="ihc_js_stripe_connect_card_error_message">' + result.error.message + '</div>' );
                                  self.IhcStripeConnect.deactivateSpinner();
                                  return false;
                              } else {
                                  //self.IhcStripeConnect.activateSpinner();
                                  var theTarget = document.getElementById( self.IhcStripeConnect.formId );
                                  self.IhcStripeConnect.removePreventSubmit();
                                  theTarget.submit();
                              }

                          });
                       }
                  });
              }
          });
          return false;
      }

    },

    activateSpinner: function(){
        if ( jQuery( '.ihc-loading-purchase-button' ).length > 0 ){
          jQuery( '.ihc-complete-purchase-button' ).addClass( 'ihc-display-none-important' );
          jQuery('.ihc-loading-purchase-button').removeClass('ihc-display-none-important').addClass('ihc-display-block');
        } else {
            if ( jQuery( '#ihc_submit_bttn' ).length > 0 ){
                // make submit button disabled
                jQuery( '#ihc_submit_bttn' ).attr( 'disabled', 'disabled' );
                jQuery( '#ihc_submit_bttn' ).attr( 'value', jQuery( '#ihc_submit_bttn' ).attr('data-loading-label') );
            }
        }
    },

    deactivateSpinner: function(){
        if ( jQuery( '.ihc-loading-purchase-button' ).length > 0 ){
          jQuery('.ihc-loading-purchase-button').removeClass('ihc-display-block').addClass('ihc-display-none-important');
          jQuery( '.ihc-complete-purchase-button' ).removeClass('ihc-display-none-important').addClass( 'ihc-display-block' );
        } else {
            if ( jQuery( '#ihc_submit_bttn' ).length > 0 ){
                // remove disabled attr from submit button
                jQuery( '#ihc_submit_bttn' ).removeAttr( 'disabled' );
                jQuery( '#ihc_submit_bttn' ).attr( 'value', jQuery( '#ihc_submit_bttn' ).attr('data-standard-label') );
            }
        }
    },

    checkAllFieldsBeforeSubmit: function(){
      if ( typeof window.ihcRegisterCheckFieldsAjaxFired === 'undefined' || window.ihcRegisterCheckFieldsAjaxFired === 0 ){
          window.ihcRegisterCheckFieldsAjaxFired = 1;
      } else {
          return;
      }
      self.IhcStripeConnect.activateSpinner();
      jQuery('.ihc-register-notice').remove();
      var fields_to_send = [];

      // set the fields
      var fields = JSON.parse( window.ihc_register_fields );
      // set the required fields
      if ( typeof window.ihc_register_required_fields !== 'undefined' ){
          var required_fields = JSON.parse( window.ihc_register_required_fields );
      } else {
          var required_fields = [];
      }

      for (var i=0; i<fields.length; i++){

        // current field is not available so we skip it
        if ( self.IhcStripeConnect.fieldHasException( fields[i] ) ){
            continue;
        }

        // remove old notices
        jQuery('.ihc-form-create-edit [name='+fields[i]+']').removeClass('ihc-input-notice');

        // getting form type
        var field_type = self.IhcStripeConnect.getFieldTypeByName( fields[i] );

        // initiate the variables
        var val1 = '';
        var val2 = '';
        var is_unique_field = false;

        if (field_type=='checkbox' || field_type=='radio'){
          val1 = self.IhcStripeConnect.getCheckboxRadioValue(field_type, fields[i]);
        } else if ( field_type=='multiselect' ){
          val1 = jQuery('.ihc-form-create-edit [name=\'' + fields[i] + '[]\']').val();
          if (typeof val1=='object' && val1!=null){
            val1 = val1.join(',');
          }
        } else {
          val1 = jQuery('.ihc-form-create-edit [name='+fields[i]+']').val();
          if (jQuery('.ihc-form-create-edit [name='+fields[i]+']').attr('data-search-unique')){
             is_unique_field = true;
          }
        }

        if (fields[i]=='pass2'){
          val2 = jQuery('.ihc-form-create-edit [name=pass1]').val();
        } else if (fields[i]=='confirm_email'){
          val2 = jQuery('.ihc-form-create-edit [name=user_email]').val();
        } else if (fields[i]=='tos') {
          if (jQuery('.ihc-form-create-edit [name=tos]').is(':checked')){
            val1 = 1;
          } else {
            val1 = 0;
          }
        } else if ( fields[i] == 'recaptcha' ){
            if ( jQuery( '[name=stripe_connect_form_data]').length > 0 ){
                continue;
            }
            val1 = jQuery( '.ihc-form-create-edit [name=g-recaptcha-response]' ).val();
        }

        if ( typeof val1 === 'undefined' ){
            val1 = '';
        }
        var params_to_send = {name: fields[i], value: val1, second_value: val2};
        if (is_unique_field){
          params_to_send.is_unique_field = true;
        }
        if ( self.IhcStripeConnect.isFieldRequired( required_fields, fields[i] ) ){
            params_to_send.is_required = true;
        }
        fields_to_send.push(params_to_send);
      }

        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
            data : {
                       action               : 'ihc_ajax_register_form_check_all_fields',
                       fields_obj           : fields_to_send,
                       payment_gateway      : 'stripe_connect',
                   },
            success: function (data) {
              var obj = JSON.parse(data);
              var must_submit = 1;

              for (var j=0; j<obj.length; j++){
                  var field_type = jQuery('.ihc-form-create-edit [name=' + obj[j].name + ']').attr('type');
                  if (typeof field_type=='undefined'){
                    var field_type = jQuery('.ihc-form-create-edit [name=\'' + obj[j].name + '[]\']').attr('type');
                  }
                  if (typeof field_type=='undefined'){
                    var field_type = jQuery('.ihc-form-create-edit [name=\'' + obj[j].name + '\']').prop('nodeName');
                  }
                  if (typeof field_type=='undefined'){
                    var field_type = jQuery('.ihc-form-create-edit [name=\'' + obj[j].name + '[]\']').prop('nodeName');
                    if (field_type=='SELECT'){
                      field_type = 'multiselect';
                    } else if ( obj[j].name === 'recaptcha' ){
                        field_type = 'recaptcha';
                        /// extra for recaptcha
                        if ( typeof obj[j].extra_field !== 'undefined' ){
                            jQuery( '.ihc-form-create-edit' ).append( obj[j].extra_field );
                        }
                    }
                  }

                  if (field_type=='radio'){
                    var target_id = jQuery('.ihc-form-create-edit [name='+obj[j].name+']').parent().parent().attr('id');
                  } else if (field_type=='checkbox' && obj[j].name!='tos'){
                    var target_id = jQuery('.ihc-form-create-edit [name=\''+obj[j].name+'[]\']').parent().parent().attr('id');
                  } else if ( field_type=='multiselect'){
                    var target_id = jQuery('.ihc-form-create-edit [name=\''+obj[j].name+'[]\']').parent().attr('id');
                  } else if ( field_type == "recaptcha" ){
                    var target_id = jQuery( '.g-recaptcha-wrapper' ).parent().attr('id');
                  }  else {
                    var target_id = jQuery('.ihc-form-create-edit [name='+obj[j].name+']').parent().attr('id');
                  }

                  if (obj[j].value==1){
                    // it's all good
                  } else {
                    //errors
                      if (typeof target_id=='undefined'){
                        //no target id...insert msg after input
                        jQuery('.ihc-form-create-edit [name='+obj[j].name+']').after('<div class="ihc-register-notice">'+obj[j].message+'</div>');
                        must_submit = 0;
                      } else {
                        jQuery('#'+target_id).append('<div class="ihc-register-notice">'+obj[j].message+'</div>');
                        jQuery('.ihc-form-create-edit [name=' + obj[j].name + ']').addClass('ihc-input-notice');
                        must_submit = 0;
                      }
                  }
              }

              self.IhcStripeConnect.deactivateSpinner();
              window.ihcRegisterCheckFieldsAjaxFired = 0;
              if (must_submit==1){
                 // do submit
                 self.IhcStripeConnect.canDoSubmit = true;
                 self.IhcStripeConnect.check();
                 window.IhcRegisterForm.must_submit = 1;
                 window.must_submit = 1;
              } else {
                 self.IhcStripeConnect.canDoSubmit = false;
                 window.IhcRegisterForm.must_submit = 0;
                 window.must_submit = 0;
              }
            }
        });

    },

    isFieldRequired: function ( required_fields, name ){
        if ( required_fields.indexOf( name ) < 0 ){
            return false;
        }
        var exceptions = jQuery("[name=ihc_exceptionsfields]").val();
        if ( exceptions ){
            // exceptions are the conditional logic fields, that are required in some case.
            var exceptions_arr = exceptions.split(',');
        }
        if ( exceptions_arr && exceptions_arr.indexOf( name ) > -1 ){
            //CHECK IF FIELD is in exceptions
            return false;
        }
        return true;
    },

    // getting the type of field based on name of field. Used in register form.
    getFieldTypeByName        : function( name ){
        var fieldType = jQuery('.ihc-form-create-edit [name=' + name + ']').attr('type');
        if ( fieldType === 'text' && jQuery( '.ihc-form-create-edit [name=' + name + ']' ).hasClass('iump-form-datepicker') ){
            return 'date';
        }
        if ( typeof fieldType === 'undefined' ){
           fieldType = jQuery('.ihc-form-create-edit [name=\'' + name + '[]\']').attr('type');
        }
        if ( typeof fieldType === 'undefined' ){
           fieldType = jQuery('.ihc-form-create-edit [name=\'' + name + '\']').prop('nodeName');
           if ( typeof fieldType !== 'undefined' && fieldType !== '' ){
              fieldType = fieldType.toLowerCase();
           }
        }
        if ( typeof fieldType === 'undefined' ){
            fieldType = jQuery('.ihc-form-create-edit [name=\'' + name + '[]\']').prop('nodeName');
            if ( typeof fieldType !== 'undefined' && fieldType !== '' ){
               fieldType = fieldType.toLowerCase();
            }
            if ( fieldType == 'select' ){
                fieldType = 'multiselect';
            }
        }
        return fieldType;
    },

    fieldHasException: function ( name ){
        var exceptions = jQuery("[name=ihc_exceptionsfields]").val();
        if ( exceptions ){
            // exceptions are the conditional logic fields, that are required in some case.
            var exceptions_arr = exceptions.split(',');
        } else {
            return false;
        }
        if ( typeof exceptions_arr !== 'undefined' && exceptions_arr.indexOf( name ) > -1 ){
            //CHECK IF FIELD is in exceptions
            return true;
        }
        return false;
    },

    setBillingDetails       : function(){
        var clientEmail       = jQuery( '[name=user_email]' ).val();
        var clientCountry     = jQuery( '[name=ihc_country' ).val();
        var clientState       = jQuery( '[name=ihc_state' ).val();
        var clientCity        = jQuery( '[name=city' ).val();
        var clientAddr        = jQuery( '[name=addr1' ).val();
        var clientPostalCode  = jQuery( '[name=zip' ).val();

        // set billing details that will be passed to stripe
        var billingDetails = {};
        billingDetails.name = jQuery( '[name=ihc_stripe_connect_full_name]' ).val();

        if ( typeof clientEmail !== 'undefined' && clientEmail !== '' ){
            billingDetails.email = clientEmail;
        }
        if ( typeof clientCountry !== 'undefined' && clientCountry !== '' ){
            if ( typeof billingDetails.address === 'undefined' ){
                billingDetails.address = {};
            }
            billingDetails.address.country = clientCountry;
        }
        if ( typeof clientCity !== 'undefined' && clientCity !== '' ){
            if ( typeof billingDetails.address === 'undefined' ){
                billingDetails.address = {};
            }
            billingDetails.address.city = clientCity;
        }
        if ( typeof clientState !== 'undefined' && clientState !== '' ){
            if ( typeof billingDetails.address === 'undefined' ){
                billingDetails.address = {};
            }
            billingDetails.address.state = clientState;
        }
        if ( typeof clientAddr !== 'undefined' && clientAddr !== '' ){
            if ( typeof billingDetails.address === 'undefined' ){
                billingDetails.address = {};
            }
            billingDetails.address.line1 = clientAddr;
        }
        if ( typeof postal_code !== 'undefined' && postal_code !== '' ){
            if ( typeof billingDetails.address === 'undefined' ){
                billingDetails.address = {};
            }
            billingDetails.address.postal_code = clientPostalCode;
        }
        return billingDetails;
    },

    getCheckboxRadioValue : function(type, selector){
      if (type=='radio'){
        var r = jQuery('[name='+selector+']:checked').val();
        if (typeof r!='undefined'){
          return r;
        }
      } else {
        var arr = [];
        jQuery('[name=\''+selector+'[]\']:checked').each(function(){
          arr.push(this.value);
        });
        if (arr.length>0){
          return arr.join(',');
        }
      }
      if ( jQuery('[name="' + selector + '"]').is(':checked') ){
          return 1;
      }
      return '';
    },

    indeedDetectBrowser     : function()
    {
        if((navigator.userAgent.indexOf("Opera") || navigator.userAgent.indexOf('OPR')) != -1 ) {
            return 'Opera';
        } else if(navigator.userAgent.indexOf("Chrome") != -1 ) {
            return 'Chrome';
        } else if(navigator.userAgent.indexOf("Safari") != -1) {
            return 'Safari';
        } else if(navigator.userAgent.indexOf("Firefox") != -1 ){
            return 'Firefox';
        } else if((navigator.userAgent.indexOf("MSIE") != -1 ) || (!!document.documentMode == true )) {
            return 'IE';
        } else {
            return 'Unknown';
        }
    }

}

jQuery( window ).on( 'load', function(){
		window.ihcStripeObject = IhcStripeConnect.init( [] );

    ihcAddAction( 'checkout-loaded', function(){
          window.ihcStripeObject = null;
          window.ihcStripeObject = IhcStripeConnect.init( [] );
    }, 0 );

});

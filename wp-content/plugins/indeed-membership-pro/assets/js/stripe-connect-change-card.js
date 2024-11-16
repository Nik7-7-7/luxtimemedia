/*
* Ultimate Membership Pro - Stripe Connect Change Cards
*/
"use strict";
var IhcStripeConnectChangeCards = {
  stripeObject                : null,
  card                        : null,
  elements                    : null,
  subscriptionId              : null,

  init                        : function( args ){
      var object = this;

      jQuery( '.ihc-js-stripe-connect-change-card' ).on( 'click', function(){
        jQuery('.ihc-stripe-connect-change-card').remove();
        self.IhcStripeConnectChangeCards.subscriptionId = jQuery( this ).attr('data-subscription_id');
          jQuery.ajax({
              type : "post",
              url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
              data : {
                         action: "ihc_ajax_get_stripe_connect_change_card_fields"
              },
              success: function ( response ) {
                  if ( jQuery('.ihc-stripe-connect-change-card').length > 0 ){
                      return;
                  }
                  jQuery( '.ihc-account-subscr-list' ).after( response );
                  self.IhcStripeConnectChangeCards.initStripeObject();
                  jQuery( '.ihc-js-stripe-connect-change-card-submit' ).on( 'click', self.IhcStripeConnectChangeCards.check );
              }
         });
      });
  },


  initStripeObject                  : function(){
      // initiate stripe
      self.IhcStripeConnectChangeCards.stripeObject = Stripe( window.ihcStripeConnectPublicKey, { stripeAccount: window.ihcStripeConnectAcctNumber, locale: window.ihcStripeConnectLang } );
      var clientSecret = jQuery('#ihc-js-stripe-connect-card-element').attr('data-client');

      self.IhcStripeConnectChangeCards.elements = self.IhcStripeConnectChangeCards.stripeObject.elements( );

      self.IhcStripeConnectChangeCards.card = self.IhcStripeConnectChangeCards.elements.create("card", {
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
      self.IhcStripeConnectChangeCards.card.mount( "#ihc-js-stripe-connect-card-element" );
  },

  check                         : function(){
    var clientSecret = jQuery( '[name=stripe_client_secret]' ).val();
    var fullName = jQuery( '[name=ihc_stripe_connect_full_name]' ).val();
    self.IhcStripeConnectChangeCards.stripeObject.createPaymentMethod({
      type              : 'card',
      card              : self.IhcStripeConnectChangeCards.card,
      billing_details   : {
                            name      : fullName,
      },
    }).then(function(result) {
        if ( typeof result.paymentMethod.id !== 'undefined' ){
          // send ajax to get the payment intent or setup intent
          jQuery.ajax({
               type 		: "post",
               url 		: decodeURI(window.ihc_site_url) + '/wp-admin/admin-ajax.php',
               data 		: {
                          action							: "ihc_ajax_stripe_connect_generate_setup_intent_no_payment",
                          payment_method      : result.paymentMethod.id,

               },
               success	: function( responseJson ) {
                  var response = JSON.parse( responseJson );
                  if ( response.status === 0 ){
                      return false;
                  }
                  var fullName = jQuery( '[name=ihc_stripe_connect_full_name]' ).val();

                  self.IhcStripeConnectChangeCards.stripeObject.confirmCardSetup( response.client_secret, {
                        payment_method: {
                          card: self.IhcStripeConnectChangeCards.card,
                          billing_details: {
                            name: fullName
                          }
                        }
                  }).then(function(result) {
                      // do something with result
                      if ( typeof result.setupIntent.payment_method === 'undefined' ){
                          // error
                          return false;
                      }

                      jQuery.ajax({
                              type : "post",
                              url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
                              data : {
                                         action                   : "ihc_ajax_do_stripe_connect_change_card",
                                         payment_method_id        : result.setupIntent.payment_method,
                                         ump_subscription_id      : self.IhcStripeConnectChangeCards.subscriptionId,
                                         uid                      : jQuery( '.ihc-js-stripe-connect-change-card' ).attr( 'data-uid'),
                              },
                              success: function ( response ) {
                                  if ( typeof response != 'object' ){
                                      var responseObject = JSON.parse( response );
                                  }
                                  if ( responseObject.status === 1 ){
                                      jQuery( '.ihc-js-stripe-connect-wrapp' ).html('<div class="ihc-succes-message">' + responseObject.message + '</div>');
                                  } else {
                                      jQuery( '.ihc-js-stripe-connect-wrapp' ).append('<div class="ihc-wrapp-the-errors">' + responseObject.message + '</div>');
                                  }
                              }
                       });

                  });
               }
          });
        }
        return false;
    });
    /*
    self.IhcStripeConnectChangeCards.stripeObject.confirmCardSetup( clientSecret, {
          payment_method: {
            card: self.IhcStripeConnectChangeCards.card,
            billing_details: {
              name: fullName
            }
          }
    }).then(function(result) {
        // do something with result
        if ( typeof result.setupIntent.payment_method === 'undefined' ){
            // error
            return false;
        }

        jQuery.ajax({
                type : "post",
                url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
                data : {
                           action                   : "ihc_ajax_do_stripe_connect_change_card",
                           payment_method_id        : result.setupIntent.payment_method,
                           ump_subscription_id      : self.IhcStripeConnectChangeCards.subscriptionId,
                           uid                      : jQuery( '.ihc-js-stripe-connect-change-card' ).attr( 'data-uid'),
                },
                success: function ( response ) {
                    if ( typeof response != 'object' ){
                        var responseObject = JSON.parse( response );
                    }
                    if ( responseObject.status === 1 ){
                        jQuery( '.ihc-js-stripe-connect-wrapp' ).html('<div class="ihc-succes-message">' + responseObject.message + '</div>');
                    } else {
                        jQuery( '.ihc-js-stripe-connect-wrapp' ).append('<div class="ihc-wrapp-the-errors">' + responseObject.message + '</div>');
                    }
                }
         });

    });
    */

  },

};


jQuery( window ).on( 'load', function(){
		IhcStripeConnectChangeCards.init( [] );
});

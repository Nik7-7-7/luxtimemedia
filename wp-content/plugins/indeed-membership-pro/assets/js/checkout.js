/*
* Ultimate Membership Pro - Checkout Page
*/
"use strict";
var IhcCheckout = {
	lid 									: -1,
	paymentType    				: '',

	init									: function(args){
			var obj = this;
			obj.lid = window.ihcCurrentLid;
			obj.paymentType = window.ihcPaymentType;

					obj.paymentUpdate( obj );

					//Apply Discount ( Coupon )
					jQuery( '#ihc-apply-discount' ).on( 'click', function( e, html ){
							e.preventDefault();
							obj.UpdateHtml( obj, 'coupon' );
							return false;
					});

					//Apply Dynamic Price
					jQuery( '#ihc-apply-dynamic-price' ).on( 'click', function( e, html ){
							e.preventDefault();
							obj.UpdateHtml( obj, 'dynamic_price' );
							return false;
					});

					// country change
					if ( jQuery( '#ihc_country_field' ).length > 0 ){
							jQuery( '#ihc_country_field' ).on( 'change', function( evt, html ){
									evt.preventDefault();
									obj.UpdateHtml( obj, 'country' );
							});
					}

					// state change
					obj.InitEventsOnStateChange( obj );

					if ( jQuery( 'form#checkout' ).length > 0 ){
							jQuery( 'form#checkout' ).on( 'submit', function(){
			          	jQuery( '.ihc-complete-purchase-button' ).addClass( 'ihc-display-none-important' );
									jQuery('.ihc-loading-purchase-button').removeClass('ihc-display-none-important').addClass('ihc-display-block');
							});
					}

	},

  paymentSelect					: function( type ){
      jQuery('.ihc-payment-icon').removeClass('ihc-payment-select-img-selected');
      jQuery('#ihc_payment_icon_' + type ).addClass('ihc-payment-select-img-selected');
  },

  paymentUpdate					: function( obj ){
		// select payment - logos
		jQuery( '.ihc-js-select-payment' ).on( 'click', function( e, html ){
				var type = jQuery(e.target).attr( 'data-type' );
				obj.ShowSpinner( type );
				jQuery('[name=ihc_payment_gateway]').val( type );
				var accessType = jQuery( '[name="subscription_access_type"]' ).val();
				obj.paymentSelect( type );
				obj.paymentType = type;
				obj.UpdateHtml( obj, 'payment_type' );
				// do actions
				ihcDoAction( 'checkeout-payment-type-logos-change' );
		});

		// select payment - select
		jQuery( '.ihc-js-select-payment-service-select' ).on( 'change', function( e, html ){
				var type = jQuery(e.target).val();
				obj.ShowSpinner( type );
				jQuery('[name=ihc_payment_gateway]').val( type );
				var accessType = jQuery( '[name="subscription_access_type"]' ).val();
				obj.paymentSelect( type );
				obj.paymentType = type;
				obj.UpdateHtml( obj, 'payment_type' );
				// do actions
				ihcDoAction( 'checkeout-payment-type-select-change' );
		});

		// select payment - radio
		jQuery( '.ihc-js-select-payment-service-radio' ).on( 'click', function( e, html ){
				var type = jQuery(e.target).val();
				obj.ShowSpinner( type );
				jQuery('[name=ihc_payment_gateway]').val( type );
				var accessType = jQuery( '[name="subscription_access_type"]' ).val();
				obj.paymentSelect( type );
				obj.paymentType = type;
				obj.UpdateHtml( obj, 'payment_type' );
				// do actions
				ihcDoAction( 'checkeout-payment-type-radio-change' );
		});
  },

	ShowSpinner						: function( currentType ){
			var paymentWithFields = ['braintree', 'authorize', 'stripe_connect'];
			if ( paymentWithFields.includes(currentType) || paymentWithFields.includes(self.IhcCheckout.paymentType) ){
				jQuery('.ihc-loading-inline-payment-fields').removeClass('ihc-display-none-important').addClass('ihc-display-block');
			}
	},

	CleanInputData				: function( target ){
      jQuery( target ).val('');
  },

	ShowAlert							: function( alertDiv, alertMess, alertClass ){
		jQuery( alertDiv ).html(alertMess);
		jQuery( alertDiv ).attr( 'class', '');
		jQuery( alertDiv ).addClass('ihc-checkout-alert');
		jQuery( alertDiv ).addClass(alertClass);
		jQuery( alertDiv ).css( 'display', 'block' );
  },

	AlertDiscount					: function(obj, status){
			var alertDiv = "#ihc-discount-error-wrap";
			var succMess = jQuery( '.ihc-js-discount-success-message' ).attr('data-value');
			var errorMess = jQuery( '.ihc-js-discount-error-message' ).attr('data-value');

			if ( status === true ){
				obj.CleanInputData('#ihc-discount');
				obj.ShowAlert( alertDiv , succMess , 'ihc-checkout-alert-success');
				jQuery( alertDiv ).fadeOut( 2000, "linear");
			} else {
				obj.ShowAlert( alertDiv, errorMess , 'ihc-checkout-alert-error');
			}
	},

	AlertDynamicPrice			: function( obj, status ){
			var alertDiv = "#ihc-dynamic-price-error-wrap";
			//var	errorMess = "Price is invalid.";
			//var	succMess = "Price successfully applied.";
			var succMess = jQuery( '.ihc-js-dynamic-price-success-message' ).attr('data-value');
			var	errorMess = jQuery( '.ihc-js-dynamic-price-error-message' ).attr('data-value');

			if ( status === true ){
			    obj.CleanInputData('#ihc-dynamic-price');
					obj.ShowAlert( alertDiv , succMess , 'ihc-checkout-alert-success');
					//jQuery( alertDiv ).fadeOut( 2000, "linear");
			} else {
				  obj.ShowAlert( alertDiv, errorMess , 'ihc-checkout-alert-error');
			}
	},

	UpdateHtml				: function( object, typeOfRequest ){

			var dynamicPrice 			= '';
			var coupon 						= '';
			var country 					= '';
			var state 						= '';
			if ( jQuery('#ihc-dynamic-price').length > 0 ){
					dynamicPrice 			= jQuery('#ihc-dynamic-price').val();
			}
			if ( jQuery('#ihc-discount').length > 0 ){
					coupon 						= jQuery('#ihc-discount').val();
			}
			if ( dynamicPrice === '' ){
					dynamicPrice = jQuery( '[name=dynamic_price_set]' ).val();
			}
			if ( coupon === '' ){
					coupon = jQuery( '[name=coupon_used]' ).val();
			}
			if ( jQuery( '#ihc_country_field' ).length > 0 ){
					country = jQuery( '#ihc_country_field' ).val();
			}
			if ( jQuery( '[name=ihc_state]' ).length > 0 ){
					state = jQuery( '[name=ihc_state]' ).val();
			}

			jQuery.ajax({
					 type 		: "post",
					 url 		: decodeURI(window.ihc_site_url) + '/wp-admin/admin-ajax.php',
					 data 		: {
											action							: "ihc_checkout_subscription_details",
											lid									: object.lid,
											country							: country,
											state								: state,
											dynamicPrice 				: dynamicPrice,
											coupon 							: coupon,
											paymentType       	: object.paymentType,
											typeOfRequest       : typeOfRequest,
					 },
					 success	: function( response ) {
						 	var responseObject = JSON.parse( response );
							// Update the HTML

							if ( jQuery( '#ihc-checout-page-taxes-section' ).length > 0 && typeof responseObject.taxes !=='undefined' ){
									responseObject.taxes = object.stripslashes( responseObject.taxes );
									jQuery( '#ihc-checout-page-taxes-section' ).html('');
									jQuery( '#ihc-checout-page-taxes-section' ).html( responseObject.taxes  );
							}

							if ( jQuery( '#ihc-checout-page-purchase-subscription-details-section' ).length > 0 && typeof responseObject.subscription_details !== 'undefined' ){
									responseObject.subscription_details = object.stripslashes( responseObject.subscription_details );
									jQuery( '#ihc-checout-page-purchase-subscription-details-section' ).html('');
									jQuery( '#ihc-checout-page-purchase-subscription-details-section' ).html( responseObject.subscription_details  );
							}

							if ( jQuery( '#ihc-checout-page-subtotal-section' ).length > 0 && typeof responseObject.subtotal !== 'undefined' ){
									responseObject.subtotal = object.stripslashes( responseObject.subtotal );
									jQuery( '#ihc-checout-page-subtotal-section' ).html('');
									jQuery( '#ihc-checout-page-subtotal-section' ).html( responseObject.subtotal  );
							}

							if ( jQuery( '#ihc-checout-page-purchase-button-section' ).length > 0 && typeof responseObject.bttn !== 'undefined' ){
									responseObject.bttn = object.stripslashes( responseObject.bttn );
									jQuery( '#ihc-checout-page-purchase-button-section' ).html( '' );
									jQuery( '#ihc-checout-page-purchase-button-section' ).html( responseObject.bttn );
							}

							if ( jQuery( '.ihc-discount-wrapper .ihc-checkout-page-used' ).length > 0 && typeof responseObject.coupon_success !== 'undefined' ){
									responseObject.coupon_success = object.stripslashes( responseObject.coupon_success );
									jQuery( '.ihc-discount-wrapper .ihc-checkout-page-used' ).html( responseObject.coupon_success );
							}

							if ( jQuery( '.ihc-dynamic-price-wrapper .ihc-checkout-page-used' ).length > 0 && typeof responseObject.dynamic_price_success !== 'undefined' ){
									responseObject.dynamic_price_success = object.stripslashes( responseObject.dynamic_price_success );
									jQuery( '.ihc-dynamic-price-wrapper .ihc-checkout-page-used' ).html(responseObject.dynamic_price_success);
							}

							// payment method
							if ( jQuery( '#ihc-checout-page-purchase-payment-method-section' ).length > 0 && typeof responseObject.payment_method_section !== 'undefined' ){
									responseObject.payment_method_section = object.stripslashes( responseObject.payment_method_section );
									jQuery( '#ihc-checout-page-purchase-payment-method-section' ).html( responseObject.payment_method_section );
							}

							object.setRemoveDiscount( object );
							object.setRemoveDynamicPrice( object );
							object.paymentUpdate( object );

							if ( typeOfRequest === 'country' ){
								object.UpdateStateByCountry( object );
							}

							// do actions
							ihcDoAction( 'checkout-loaded' );

							if ( responseObject.status == 1 ){
									// message - success
									if ( typeOfRequest === 'coupon' ){
											object.CleanInputData('#ihc-discount');
											jQuery( '#ihc-discount-error-wrap' ).html('');
											jQuery( '#ihc-discount-error-wrap' ).attr( 'class', '' );
									} else if ( typeOfRequest === 'dynamic_price' ){
											object.CleanInputData('#ihc-dynamic-price');
											jQuery( '#ihc-dynamic-price-error-wrap' ).html('');
											jQuery( '#ihc-dynamic-price-error-wrap' ).attr( 'class', '' );
									}
							} else {
									// message - error
									if ( typeOfRequest === 'coupon' ){
											object.AlertDiscount( object, false );
									} else if ( typeOfRequest === 'dynamic_price' ){
											object.AlertDynamicPrice( object, false );
									}
							}
					 }
			});
	},

	stripslashes				: function(str) {
		if ( str === '' ){
				return str;
		}
	 	return str.replace(/\\'/g,'\'').replace(/\"/g,'"').replace(/\\\\/g,'\\').replace(/\\0/g,'\0');
	},

	setRemoveDynamicPrice: function( object ){
			jQuery( '.ihc-js-checkout-page-do-remove-dynamic-price' ).on( 'click', function(){
					jQuery( '[name=dynamic_price_set]' ).val('');
					object.UpdateHtml( object, 'refresh' );
			});
	},

	setRemoveDiscount: function( object ){
		jQuery( '.ihc-js-checkout-page-do-remove-coupon' ).on( 'click', function(){
				jQuery( '[name=coupon_used]' ).val('');
				object.UpdateHtml( object, 'refresh' );
		});
	},

	InitEventsOnStateChange: function( obj ){
		if ( jQuery( '[name=ihc_state]' ).length > 0 ){
				jQuery( '[name=ihc_state]' ).on( 'blur', function( evt, html ){
						obj.UpdateHtml( obj, 'refresh' );
				});
				obj.UpdateHtml( obj, 'refresh' );
		}
	},

	UpdateStateByCountry: function( object ){
		if ( jQuery('.ihc-form-create-edit [name=ihc_state]').length > 0 ){
			var e = jQuery('.ihc-form-create-edit [name=ihc_country]');
			jQuery.ajax({
				type : "post",
					url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
					data : {
									 action: "ihc_get_ihc_state_field",
									 country: e.val(),
					},
					success: function (r) {
						var f = jQuery('.ihc-form-create-edit [name=ihc_state]');
						var p = f.parent();
						f.remove();
						p.append(r);
						object.InitEventsOnStateChange( object );
					}
			});
		}
	},

};

jQuery( window ).on( 'load', function(){
		IhcCheckout.init();
});


////
function ihcDoAction( action_name ) {
	if ( typeof window.ihcactions === 'undefined' || typeof window.ihcactions[action_name] == 'undefined' ) {
			return;
	}

	var args = Array.prototype.slice.call(window.ihcactions);
	var object = this;
	var actionLength = window.ihcactions[action_name].length;

	for ( var i = 0; i<=actionLength; i++ ) {
		if ( window.ihcactions[action_name][i] ) {
			var argsLength = window.ihcactions[action_name][i].length;
			for ( var j = 0; j<argsLength; j++ ) {
				window.ihcactions[action_name][i][j].apply( object, args );
			}
		}
	}

}
function ihcAddAction( action_name, callback, priority ) {
	if ( typeof window.ihcactions === 'undefined' ){
			window.ihcactions = [];
	}
	if ( !priority )  {
			priority = 10;
	}

	if ( priority > 100 ) {
			priority = 100;
	}

	if ( priority < 0 ) {
			priority = 0;
	}

	if ( typeof window.ihcactions[action_name] == 'undefined' ) {
			window.ihcactions[action_name] = [];
	}

	if ( typeof window.ihcactions[action_name][priority] == 'undefined' ) {
			window.ihcactions[action_name][priority] = [];
	}

	window.ihcactions[action_name][priority].push( callback );
}

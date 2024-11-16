/*
* Ultimate Membership Pro - Braintree utilities
*/
"use strict";
var IhcBraintree = {
    formId                      : 'createuser',
    init                        : function( args ){
        var object = this;

        if ( window.ihcCheckoutIsRegister === '0' ){
            object.formId = 'checkout';
        }

        // card name
        jQuery( '[name=ihc_braintree_cardholderName]' ).on( 'blur', function( evt, html ){
            object.checkField( evt, html, object );
        });

        // expire month
        jQuery( '[name=ihc_braintree_card_expire_month]' ).on( 'blur', function( evt, html ){
            object.checkField( evt, html, object );
        });

        // expire year
        jQuery( '[name=ihc_braintree_card_expire_year]' ).on( 'blur', function( evt, html ){
            object.checkField( evt, html, object );
        });

        // cvc
        jQuery( '[name=ihc_braintree_cvv]' ).on( 'blur', function( evt, html ){
            object.checkField( evt, html, object );
        });

        // card number
        jQuery( '[name=ihc_braintree_card_number]' ).on( 'blur', function( evt, html ){
            object.checkField( evt, html, object );
        });


        // if braintree is the payment gateway
        // select payment - logos

        ihcAddAction( 'checkeout-payment-type-radio-change', function(){
            var type = window.IhcCheckout.paymentType;
            if ( type === 'braintree' ){
                self.IhcBraintree.activatePreventSubmit();
            } else {
                self.IhcBraintree.removePreventSubmit();
            }
        }, 0 );

        ihcAddAction( 'checkeout-payment-type-select-change', function(){
            var type = window.IhcCheckout.paymentType;
            if ( type === 'braintree' ){
                self.IhcBraintree.activatePreventSubmit();
            } else {
                self.IhcBraintree.removePreventSubmit();
            }
        }, 0 );

        ihcAddAction( 'checkeout-payment-type-logos-change', function(){
            var type = window.IhcCheckout.paymentType;
            if ( type === 'braintree' ){
                self.IhcBraintree.activatePreventSubmit();
            } else {
                self.IhcBraintree.removePreventSubmit();
            }
        }, 0 );

        if ( jQuery( '[name=payment_selected]' ).val() === 'braintree' ){
            object.activatePreventSubmit();
        }

    },

    checkField                    : function( evt, html, object ){
        if ( evt.target.value === '' ){
            jQuery( evt.target ).addClass( 'ihc-input-notice' );
        } else {
            jQuery( evt.target ).removeClass( 'ihc-input-notice' );
        }
    },

    preventFormSubmit             : function( evt ){
        if ( self.IhcBraintree.formId === 'createuser' ){
            // register
            evt.preventDefault();
            evt.stopPropagation();
            var promise = new Promise(function(resolve, reject) {
                window.IhcRegisterForm.checkAllFieldsBeforeSubmit( window.IhcRegisterForm );
                resolve();
            });

            promise.then(function() {
                if ( window.IhcRegisterForm.must_submit ){
                    self.IhcBraintree.checkFieldsViaAjax();
                }
            });
            return false;
        } else {
            // buy membership for registered users
            evt.preventDefault();
            evt.stopPropagation();
            self.IhcBraintree.checkFieldsViaAjax();
            return false;
        }

        /*
        evt.preventDefault();
        evt.stopPropagation();
        self.IhcBraintree.checkFieldsViaAjax();
        return false;
        */
    },

    checkFieldsViaAjax            : function(){
        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
            data : {
                       action                            : 'ihc_ajax_check_braintree_form_fields',
                       ihc_braintree_cardholderName      : jQuery( '[name=ihc_braintree_cardholderName]' ).val(),
                       ihc_braintree_cvv                 : jQuery( '[name=ihc_braintree_cvv]' ).val(),
                       ihc_braintree_card_number         : jQuery( '[name=ihc_braintree_card_number]' ).val(),
                       ihc_braintree_card_expire_month   : jQuery( '[name=ihc_braintree_card_expire_month]' ).val(),
                       ihc_braintree_card_expire_year    : jQuery( '[name=ihc_braintree_card_expire_year]' ).val(),
            },
            success: function ( response ) {
   						 	var responseObject = JSON.parse( response );
                if ( responseObject.status === 1 ){
                    var theTarget = document.getElementById( self.IhcBraintree.formId );
                    theTarget.removeEventListener( 'submit', self.IhcBraintree.preventFormSubmit, true );
                    theTarget.submit();
                } else {
                    if ( typeof responseObject.errors.ihc_braintree_cardholderName !== 'undefined' ){
                        jQuery( '[name=ihc_braintree_cardholderName]' ).addClass( 'ihc-input-notice' );
                    }
                    if ( typeof responseObject.errors.ihc_braintree_cvv !== 'undefined' ){
                        jQuery( '[name=ihc_braintree_cvv]' ).addClass( 'ihc-input-notice' );
                    }
                    if ( typeof responseObject.errors.ihc_braintree_card_number !== 'undefined' ){
                        jQuery( '[name=ihc_braintree_card_number]' ).addClass( 'ihc-input-notice' );
                    }
                    if ( typeof responseObject.errors.ihc_braintree_card_expire_month !== 'undefined' ){
                        jQuery( '[name=ihc_braintree_card_expire_month]' ).addClass( 'ihc-input-notice' );
                    }
                    if ( typeof responseObject.errors.ihc_braintree_card_expire_year !== 'undefined' ){
                        jQuery( '[name=ihc_braintree_card_expire_year]' ).addClass( 'ihc-input-notice' );
                    }
                    return false;
                }
            }
        });
        return false;
    },

    activatePreventSubmit           : function(){
        /// prevent form submit when braintree is selected, this will perform an extra ajax check to see if the payment fields are completed
        var theTarget = document.getElementById( self.IhcBraintree.formId );
        if ( typeof theTarget === 'undefined' || theTarget === null ){
            return;
        }
        theTarget.addEventListener( 'submit', self.IhcBraintree.preventFormSubmit, true );
    },

    removePreventSubmit              : function(){
      var theTarget = document.getElementById( self.IhcBraintree.formId );
      if ( typeof theTarget === 'undefined' || theTarget === null ){
          return;
      }
      theTarget.removeEventListener( 'submit', self.IhcBraintree.preventFormSubmit, true );
    },

}

jQuery( window ).on( 'load', function(){
		IhcBraintree.init( [] );
});

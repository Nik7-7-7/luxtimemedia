/*
* Ultimate Membership Pro - Manage Member Memberships
*/
"use strict";
var IhcPublicUserMembershipManagement = {

    init: function(){
        var object = this;
        jQuery( window ).on( 'load', function(){

            // pause
            if ( jQuery( '.ihc-js-pause-subscription-bttn' ).length ){
                jQuery( '.ihc-js-pause-subscription-bttn' ).on( 'click', function( e, html ){
                    object.pauseSubscription( e );
                });
            }

            // resume
            if ( jQuery( '.ihc-js-resume-subscription-bttn' ).length ){
                jQuery( '.ihc-js-resume-subscription-bttn' ).on( 'click', function( e, html ){
                    object.reactivateSubscription( e );
                });
            }

            // finish payment
            if ( jQuery( '.ihc-js-finish-payment-bttn' ).length ){
                jQuery( '.ihc-js-finish-payment-bttn' ).on( 'click', function( e, html ){
                    object.finishPayment( e );
                });
            }

            // renew payment
            if ( jQuery( '.ihc-js-renew-level-bttn' ).length ){
                jQuery( '.ihc-js-renew-level-bttn' ).on( 'click', function( e, html ){
                    object.renewSubscription( e, object );
                });
            }

        });

    },

    pauseSubscription: function( e ){
        var level = jQuery( e.target ).attr( 'data-lid' );
        var subscriptionId = jQuery( e.target ).attr( 'data-subscription_id' );
        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url) + '/wp-admin/admin-ajax.php',
            data : {
                       action                 : 'ihc_user_put_subscrition_on_pause',
                       subscriptionId         : jQuery( e.target ).attr( 'data-subscription_id' ),
                       lid                    : level
            },
            success: function ( response ) {
            console.log(response);
                if ( response ){
                    window.location.reload();
                }
            }
       });
    },

    reactivateSubscription: function( e ){
        var level = jQuery( e.target ).attr( 'data-lid' );
        var subscriptionId = jQuery( e.target ).attr( 'data-subscription_id' );
        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url) + '/wp-admin/admin-ajax.php',
            data : {
                       action                 : 'ihc_user_put_subscrition_resume',
                       subscriptionId         : jQuery( e.target ).attr( 'data-subscription_id' ),
                       lid                    : level
            },
            success: function ( response ) {
                if ( response ){
                    window.location.reload();
                }
            }
       });
    },

    finishPayment: function( e ){
        var levelName = jQuery( e.target ).attr( 'data-level_name' );
        var levelAmount = jQuery( e.target ).attr( 'data-level_amount' );
        var lid = jQuery( e.target ).attr( 'data-lid' );
        var formId = '#ihc_form_ap_subscription_page';
        var inputHiddenId = '#ihc_finish_payment_level';
        var paymntType = jQuery('[name=ihc_payment_gateway]').val();
        var orderId = jQuery( e.target ).attr( 'data-oid' );

        if (jQuery("#ihc_coupon").val()){
              jQuery(formId).append("<input type=hidden value=" + jQuery("#ihc_coupon").val() + " name=ihc_coupon />");
        }
        jQuery(inputHiddenId).val(lid);
        jQuery(formId).append( "<input type=hidden value=" + orderId + " name=order_id />" );
        // submit the form
        jQuery(formId).submit();
    },

    renewSubscription: function( e, object ){
          var levelName = jQuery( e.target ).attr( 'data-level_name' );
          var levelAmount = jQuery( e.target ).attr( 'data-level_amount' );
          var lid = jQuery( e.target ).attr( 'data-lid' );
          var formId = '#ihc_form_ap_subscription_page';
          var inputHiddenId = '#ihc_renew_level';
          object.doRenewFunction( inputHiddenId, formId, lid, levelName, levelAmount );
    },

    doRenewFunction : function(i_id, f_id, l_id, l_name, l_amount){
        if (confirm){
          if ( typeof window.ihc_translated_labels == 'object' ){
              var ihc_labels = window.ihc_translated_labels;
          } else {
              var ihc_labels = JSON.parse(window.ihc_translated_labels);
          }

          var c = window.confirm( ihc_labels.delete_level );
          if (!c){
            return;
          }
        }
        if (jQuery("#ihc_coupon").val()){
          jQuery(f_id).append("<input type=hidden value=" + jQuery("#ihc_coupon").val() + " name=ihc_coupon />");
        }
        jQuery(i_id).val(l_id);
        jQuery(f_id).submit();
    }

};

IhcPublicUserMembershipManagement.init();

var IhcRemoveCancelLevels = {
	modalSelector						    : '#ihc_reasons_modal',
	formSelector						    : '#ihc_form_ap_subscription_page',
	cancelTrigger						    : '.iump-cancel-subscription-button',
	deleteTrigger						    : '.iump-delete-subscription-button',
	modalOn								      : 0,
	predefinedReasonSelector    : '#ihc_reason_predefined_type',
	reasonSelector						  : '#ihc_the_reason_textarea',
	typeOfActionSelector				: '#ihc_reason_type',
	sendReasonAjax						  : 'ihc_save_reason_for_cancel_delete_level',
	submitModal							    : '#ihc_submit_subscription_form',
	cancelLidSelector					  : '#ihc_cancel_level',
	deleteLidSelector					  : '#ihc_delete_level',
  modalCloseBttn              : '#ihc_close_modal_bttn',

	init: function(args){
		var obj = this;
		obj.setAttributes(obj, args);
		obj.modalOn = jQuery(obj.formSelector).attr('data-modal');
		if ( typeof window.ihc_translated_labels == 'object' ){
				window.ihc_labels = window.ihc_translated_labels;
		} else {
				window.ihc_labels = JSON.parse( window.ihc_translated_labels );
		}

    if (obj.modalOn>0){
        obj.initModal(obj);
        jQuery(obj.cancelTrigger).on('click', function(evt){
            obj.handleCancelWithModal(obj, evt);
        });
        jQuery(obj.deleteTrigger).on('click', function(evt){
            obj.handleDeleteWithModal(obj, evt);
        });
        jQuery(obj.submitModal).on('click', function(evt){
            obj.sendReason(obj, evt);
        });
        jQuery(obj.modalCloseBttn).on('click', function(evt){
            jQuery(obj.modalSelector).iziModal('close');
        });
    } else {
        if ( jQuery(obj.cancelTrigger).length ){
            jQuery(obj.cancelTrigger).on('click', function(evt){
                obj.handleSimpleCancel(obj, evt);
            });
        }
        if ( jQuery(obj.deleteTrigger).length ){
            jQuery(obj.deleteTrigger).on('click', function(evt){
                obj.handleSimpleDelete(obj, evt);
            });
        }
    }
	},

  setAttributes: function(obj, args){
		for (var key in args) {
			obj[key] = args[key];
		}
	},

  initModal: function(obj){
      jQuery(obj.modalSelector).iziModal({
  				title: '',
  				headerColor: '#88A0B9',
  				background: null,
  				theme: 'light',  // light
  				width: 600,
  				top: null,
  				bottom: null,
  				borderBottom: true,
  				padding: 20,
  				radius: 3,
  				zindex: 999,
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
  				onClosing: function(){
              jQuery(obj.reasonSelector).val('');
          },
  				onClosed: function(){},
  				afterRender: function(){}
  		})
  },

	handleSimpleCancel: function(obj, evt){
		var lid = jQuery(evt.target).attr('data-lid');
		jQuery(obj.cancelLidSelector).val(lid);
		jQuery(obj.formSelector).submit();
	},

	handleSimpleDelete: function(obj, evt){
		var confirmAlert = confirm(window.ihc_labels.delete_level);
		if (!confirmAlert){
			return false;
		}
		var lid = jQuery(evt.target).attr('data-lid');
		jQuery(obj.deleteLidSelector).val(lid);
		jQuery(obj.formSelector).submit();
	},

	handleDeleteWithModal: function(obj, evt){
    var lid = jQuery(evt.target).attr('data-lid');
    jQuery(obj.deleteLidSelector).val(lid);
    jQuery(obj.typeOfActionSelector).val('delete');
    jQuery(obj.modalSelector).iziModal('setTitle', ihc_labels.delete_level );
    jQuery(obj.modalSelector).iziModal('open');
	},

	handleCancelWithModal: function(obj, evt){
    var lid = jQuery(evt.target).attr('data-lid');
    jQuery(obj.cancelLidSelector).val(lid);
    jQuery(obj.typeOfActionSelector).val('cancel');
    jQuery(obj.modalSelector).iziModal('setTitle', ihc_labels.cancel_level );
    jQuery(obj.modalSelector).iziModal('open');
	},

	sendReason: function(obj, evt){
		if (jQuery(obj.typeOfActionSelector).val()=='delete'){
			var levelId = jQuery(obj.deleteLidSelector).val();
		} else {
			var levelId = jQuery(obj.cancelLidSelector).val();
		}
		var theReason = jQuery(obj.predefinedReasonSelector).val();
		if ( theReason==0 ){
				theReason = jQuery(obj.reasonSelector).val();
		}
		jQuery.ajax({
			  type : "post",
			  url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
			  data : {
						 action			 : obj.sendReasonAjax,
						 reason			 : theReason,
						 lid			   : levelId,
						 action_type : jQuery(obj.typeOfActionSelector).val(),
					 },
			  success: function (response) {
          jQuery(obj.modalSelector).iziModal('close');
				  if (response==1){
					  jQuery(obj.formSelector).submit();
				  }
			  }
		});
	},

}

jQuery( window ).on( 'load', function(){
    IhcRemoveCancelLevels.init();
});

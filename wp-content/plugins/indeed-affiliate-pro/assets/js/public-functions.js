/*
 *Ultimate Affiliate Pro - Public Main Functions
 */
"use strict";
function iaGenerateLink(aff_id){
	jQuery('.uap-ap-generate-links-result').css('visibility', 'hidden');
	jQuery('.uap-ap-generate-social-result').css('visibility', 'hidden');
	var the_url = jQuery('#ia_generate_aff_custom_url').val();
	if (jQuery('#campaigns_select').length){
		var c = jQuery('#campaigns_select').val();
		if (c=='...') c = '';
	} else {
		var c = '';
	}
	var refType = 0;
	var friendlyLinks = 0;
	if (jQuery('#ref_type').html() && jQuery('#ref_type').val()==1){
		var refType = 1;
	}
	if (jQuery('#friendly_links').html() && jQuery('#friendly_links').val()==1){
		var friendlyLinks = 1;
	}
   	jQuery.ajax({
        type : "post",
        url : decodeURI(ajax_url),
        data : {
                   action: "uap_ia_ajax_return_url_for_aff",
                   aff_id: aff_id,
                   url: the_url,
                   campaign: c,
                   slug: refType,
                   friendly_links: friendlyLinks,
               },
        success: function (r) {
        	if (r){
        		var obj = JSON.parse(r);
        		var u = obj.url;
        		var s = obj.social;
        		var qr = obj.qr_code;
        		if (u!=0){
                	jQuery('.uap-ap-generate-links-result').html(u);
                	jQuery('.uap-ap-generate-links-result').css('visibility', 'visible');
        		}
        		if (s){
        			jQuery('.uap-ap-generate-social-result').html(s);
                	jQuery('.uap-ap-generate-social-result').css('visibility', 'visible');
        		}
        		if (qr){
        			jQuery('.uap-ap-generate-qr-code').html(qr);
                	jQuery('.uap-ap-generate-qr-code').css('visibility', 'visible');
        		}
						uapReloadListAffiliateLinksTable();
        	}
        }
   });
}


function uapRegisterCheckViaAjax(the_type){
	var target_id = '#' + jQuery('.uap-form-create-edit [name='+the_type+']').parent().attr('id');
	var val1 = jQuery('.uap-form-create-edit [name='+the_type+']').val();
	var val2 = '';

	if (the_type=='pass2'){
		val2 = jQuery('.uap-form-create-edit [name=pass1]').val();
	} else if (the_type=='confirm_email'){
		val2 = jQuery('.uap-form-create-edit [name=user_email]').val();
	}

   	jQuery.ajax({
        type : "post",
        url : decodeURI(ajax_url),
        data : {
                   action: "uap_check_reg_field_ajax",
                   type: the_type,
                   value: val1,
                   second_value: val2
               },
        success: function (data) {
        	//remove prev notice, if its case
        	jQuery(target_id + ' .uap-register-notice').remove();
        	jQuery('.uap-form-create-edit [name='+the_type+']').removeClass('uap-input-notice');
        	if (data==1){
        		// it's all good
        	} else {
        		jQuery(target_id).append('<div class="uap-register-notice">'+data+'</div>');
        		jQuery('.uap-form-create-edit [name='+the_type+']').addClass('uap-input-notice');
        	}
        }
   	});
}

//////////////logic condition

function uapAjaxCheckFieldConditionOnblurOnclick(check_name, field_id, field_name, show){
	var check_value = jQuery(".uap-form-create-edit [name="+check_name+"]").val();
	uapAjaxCheckFieldCondition(check_value, field_id, field_name, show);
}

function uapAjaxCheckOnClickFieldCondition(check_name, field_id, field_name, type, show){
	if (type=='checkbox'){
		var vals = [];
		jQuery(".uap-form-create-edit [name='"+check_name+"[]']:checked").each(function() {
			vals.push(jQuery(this).val());
	    });
		var check_value = vals.join(',');
	} else {
		var check_value = jQuery(".uap-form-create-edit [name="+check_name+"]:checked").val();
	}

	uapAjaxCheckFieldCondition(check_value, field_id, field_name, show);
}

function uapAjaxCheckCnChangeMultiselectFieldCondition(check_name, field_id, field_name, show){
	var obj = jQuery(".uap-form-create-edit [name='"+check_name+"[]']").val();
	if (obj!=null){
		var check_value = obj.join(',');
		uapAjaxCheckFieldCondition(check_value, field_id, field_name, show);
	}
}

function uapAjaxCheckFieldCondition(check_value, field_id, field_name, show){
   	jQuery.ajax({
        type : "post",
        url : decodeURI(ajax_url),
        data : {
                   action: "uap_check_logic_condition_value",
                   val: check_value,
                   field: field_name
               },
        success: function (data){
        	var str = jQuery("#uap_exceptionsfields").val();
        	if (str){
            	var arr = str.split(',');
            	var index = arr.indexOf(field_name);
        	} else {
        		var arr = [];
        	}

        	if (data=='1'){
                if (show==1){
                	jQuery(field_id).fadeIn(200);
                	if (arr.indexOf(field_name)!=-1){
                        arr.splice(index, 1);
                	}
                } else {
                	jQuery(field_id).fadeOut(200);
                	if (arr.indexOf(field_name)==-1){
                		arr.push(field_name);
                	}

                }
        	} else {
                    if (show==1){
                    	jQuery(field_id).fadeOut(200);
                    	if (arr.indexOf(field_name)==-1){
                    		arr.push(field_name);
                    	}
                    } else {
                    	jQuery(field_id).fadeIn(200);
                    	if (arr.indexOf(field_name)!=-1){
                            arr.splice(index, 1);
                    	}
                    }
        	}
        	if (arr){
            	var str = arr.join(',');
            	jQuery("#uap_exceptionsfields").val(str);
        	}
        }
   	});
}

function uapGetCheckboxRadioValue(type, selector){
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
}

function uapRegisterCheckViaAjaxRec(types_arr){
	jQuery('.uap-register-notice').remove();
	var fields_to_send = [];

	//EXCEPTIONS
	var exceptions = jQuery("#uap_exceptionsfields").val();
	if (exceptions){
		var exceptions_arr = exceptions.split(',');
	}

	for (var i=0; i<types_arr.length; i++){
		//CHECK IF FIELD is in exceptions
		if (exceptions_arr && exceptions_arr.indexOf(types_arr[i])>-1){
			continue;
		}

		jQuery('.uap-form-create-edit [name='+types_arr[i]+']').removeClass('uap-input-notice');

		var field_type = jQuery('.uap-form-create-edit [name=' + types_arr[i] + ']').attr('type');
		if (typeof field_type=='undefined'){
			var field_type = jQuery('.uap-form-create-edit [name=\'' + types_arr[i] + '[]\']').attr('type');
		}
		if (typeof field_type=='undefined'){
			var field_type = jQuery('.uap-form-create-edit [name=\'' + types_arr[i] + '\']').prop('nodeName');
		}
		if (typeof field_type=='undefined'){
			var field_type = jQuery('.uap-form-create-edit [name=\'' + types_arr[i] + '[]\']').prop('nodeName');
			if (field_type=='SELECT'){
				field_type = 'multiselect';
			}
		}
		if (field_type=='checkbox' || field_type=='radio'){
			var val1 = uapGetCheckboxRadioValue(field_type, types_arr[i]);
		} else if ( field_type=='multiselect' ){
			val1 = jQuery('.uap-form-create-edit [name=\'' + types_arr[i] + '[]\']').val();
			if (typeof val1=='object' && val1!=null){
				val1 = val1.join(',');
			}
		} else {
			var val1 = jQuery('.uap-form-create-edit [name='+types_arr[i]+']').val();
		}

		var val2 = '';
		if (types_arr[i]=='pass2'){
			val2 = jQuery('.uap-form-create-edit [name=pass1]').val();
		} else if (types_arr[i]=='confirm_email'){
			val2 = jQuery('.uap-form-create-edit [name=user_email]').val();
		} else if (types_arr[i]=='tos') {
			if (jQuery('.uap-form-create-edit [name=tos]').is(':checked')){
				val1 = 1;
			} else {
				val1 = 0;
			}
		}
		fields_to_send.push({type: types_arr[i], value: val1, second_value: val2});
	}

   	jQuery.ajax({
        type : "post",
        url : decodeURI(ajax_url),
        data : {
                   action: "uap_check_reg_field_ajax",
                   fields_obj: fields_to_send
               },
        success: function (data) {
        	var obj = JSON.parse(data);
        	var must_submit = 1;
        	for (var j=0; j<obj.length; j++){
        		var field_type = jQuery('.uap-form-create-edit [name=' + obj[j].type + ']').attr('type');
        		if (typeof field_type=='undefined'){
        			var field_type = jQuery('.uap-form-create-edit [name=\'' + obj[j].type + '[]\']').attr('type');
        		}
        		if (typeof field_type=='undefined'){
        			var field_type = jQuery('.uap-form-create-edit [name=\'' + obj[j].type + '\']').prop('nodeName');
        		}
        		if (typeof field_type=='undefined'){
        			var field_type = jQuery('.uap-form-create-edit [name=\'' + obj[j].type + '[]\']').prop('nodeName');
        			if (field_type=='SELECT'){
        				field_type = 'multiselect';
        			}
        		}

            	if (field_type=='radio'){
            		var target_id = jQuery('.uap-form-create-edit [name='+obj[j].type+']').parent().parent().attr('id');
            	} else if (field_type=='checkbox' && obj[j].type!='tos'){
            		var target_id = jQuery('.uap-form-create-edit [name=\''+obj[j].type+'[]\']').parent().parent().attr('id');
            	} else if ( field_type=='multiselect'){
            		var target_id = jQuery('.uap-form-create-edit [name=\''+obj[j].type+'[]\']').parent().attr('id');
            	} else {
            		var target_id = jQuery('.uap-form-create-edit [name='+obj[j].type+']').parent().attr('id');
            	}

            	if (obj[j].value==1){
            		// it's all good
            	} else {
            		//errors
                	if (typeof target_id=='undefined'){
                		//no target id...insert msg after input
                		jQuery('.uap-form-create-edit [name='+obj[j].type+']').after('<div class="uap-register-notice">'+obj[j].value+'</div>');
                		must_submit = 0;
                	} else {
                		jQuery('#'+target_id).append('<div class="uap-register-notice">'+obj[j].value+'</div>');
                		jQuery('.uap-form-create-edit [name=' + obj[j].type + ']').addClass('uap-input-notice');
                		must_submit = 0;
                	}
            	}
        	}

        	if (must_submit==1){
    			window.must_submit=1;
    			jQuery(".uap-form-create-edit").submit();
        	} else {
    			return false;
        	}
        }
   	});

}

function uapShowSubtabs(t){
	if (jQuery('#uap_public_ap_' + t).css('display')=='block'){
		jQuery('#uap_fa_sign-' + t).removeClass('fa-account-down-uap');
		jQuery('#uap_fa_sign-' + t).addClass('fa-account-right-uap');
		jQuery('.uap-public-ap-menu-subtabs').css('display', 'none');
	} else {
		jQuery('.uap-ap-menu-sign').removeClass('fa-account-down-uap');
		jQuery('.uap-ap-menu-sign').addClass('fa-account-right-uap');
		jQuery('.uap-public-ap-menu-subtabs').css('display', 'none');
		jQuery('#uap_public_ap_' + t).css('display', 'block');
		jQuery('#uap_fa_sign-' + t).removeClass('fa-account-right-uap');
		jQuery('#uap_fa_sign-' + t).addClass('fa-account-down-uap');
	}
}

function uapPaymentType(){
	jQuery.each(['paypal', 'bt', 'stripe', 'stripe_v2', 'stripe_v3'], function(k, v){
		jQuery('#uap_payment_with_' + v).css('display', 'none');
	});
	var t = jQuery('[name=uap_affiliate_payment_type]').val();
	jQuery('#uap_payment_with_' + t).fadeIn(200);
}

function uapBecomeAffiliatePublic(){
   	jQuery.ajax({
        type: 'post',
        url : decodeURI(ajax_url),
        data: {
               action: 'uap_make_wp_user_affiliate_from_public',
        },
        success: function (r) {
					if (r){
						window.location.href = r;
					}
        }
   	});
}

function uapAddToWallet(divCheck, showValue, hidden_input_id){
    var str = jQuery(hidden_input_id).val();
    if (str!=''){
    	var show_arr = str.split(',');
    	for ( var a in show_arr ){
        	show_arr[a] = parseInt(show_arr[a]);
			}
    } else {
    	var show_arr = [];
    }

    if (jQuery(divCheck).is(':checked')){
    	if (show_arr.indexOf(showValue)==-1){
        	show_arr.push(showValue);
    	}
    } else {
        for ( var a in show_arr ){
	        	if (parseInt(show_arr[a])==showValue){
	        		show_arr.splice(a, 1);
	        	}
				}
    }
    var str = show_arr.join(',');
    jQuery(hidden_input_id).val(str);

    jQuery('#uap_total_amount').html('');
   	jQuery.ajax({
        type: 'post',
        url : decodeURI(ajax_url),
        data: {
               action: 'uap_get_amount_for_referral_list',
               r: str
        },
        success: function (r) {
						if (r){
							jQuery('#uap_total_amount').html(r);
						} else {
							jQuery('#uap_total_amount').html(0);
						}
        }
   	});
}

function uapRemoveWalletItem(t, c){
		var uapCurrentUrl = window.location.href;
   	jQuery.ajax({
        type: 'post',
        url : decodeURI(ajax_url),
        data: {
               action: 'uap_delete_wallet_item_via_ajax',
               type: t,
               code: c,
        },
        success: function (r) {
						if (r){
								window.location.href = uapCurrentUrl;
						}
        }
   	});
}

function uapDeleteFileViaAjax(id, u_id, parent, name, hidden_id){
	var r = confirm("Are you sure you want to delete?");
	if (r) {
			var s = jQuery(parent).attr('data-h');
	   	jQuery.ajax({
	        type : "post",
	        url : decodeURI(ajax_url),
	        data : {
	                   action: "uap_delete_attachment_ajax_action",
	                   attachemnt_id: id,
	                   user_id: u_id,
	                   field_name: name,
										 h: s
	               },
	        success: function (data) {
	        	jQuery(hidden_id).val('');
	        	jQuery(parent + ' .ajax-file-upload-filename').remove();
	        	jQuery(parent + ' .uap-delete-attachment-bttn').remove();
	        	if (jQuery(parent + ' .uap-member-photo').length){
	        		jQuery(parent + ' .uap-member-photo').remove();
	        		if (name=='uap_avatar'){
	        			jQuery(parent).prepend("<div class='uap-no-avatar uap-member-photo'></div>");
	        			jQuery(parent + " .uap-file-upload").css("display", 'block');
	        		}
	        	}

	        	if (jQuery(parent + " .uap-file-name-uploaded").length){
	        		jQuery(parent + " .uap-file-name-uploaded").remove();
	        	}

	        	if (jQuery(parent + ' .ajax-file-upload-progress').length){
	        		jQuery(parent + ' .ajax-file-upload-progress').remove();
	        	}
	        	if (jQuery(parent + ' .uap-icon-file-type').length){
	        		jQuery(parent + ' .uap-icon-file-type').remove();
	        	}
	        }
	   });
	}
}

function uapMakeInputhString(divCheck, showValue, hidden_input_id){
    var str = jQuery(hidden_input_id).val();
    if (str==-1){
			str = '';
		}
    if (str!=''){
			var show_arr = str.split(',');
		} else {
			var show_arr = new Array();
		}
    if (jQuery(divCheck).is(':checked')){
        show_arr.push(showValue);
    } else {
        var index = show_arr.indexOf(showValue);
        show_arr.splice(index, 1);
    }
    str = show_arr.join(',');
    if (str=='') {
			str = -1;
		}
		jQuery(hidden_input_id).val(str);
}

function uapAffiliateUsernameTest(v){
	jQuery('.uap-username-not-exists').remove();
   	jQuery.ajax({
        type: 'post',
        url : decodeURI(ajax_url),
        data: {
               action: 'uap_check_if_username_is_affiliate',
               username: v,
        },
        success: function (r) {
					if (r==1){
						jQuery('#uap_affiliate_username_text').after('<div class="uap-username-not-exists">Username that You write is not affiliate!</div>');
					}
        }
   	});
}

function uapCheckLoginField(t, e){
	var n = jQuery('#notice_' + t);
	n.fadeOut(500, function(){
		n.remove();
	});
	var target = jQuery('#uap_login_form [name='+t+']').parent();
	var v = jQuery('#uap_login_form [name='+t+']').val();
	if (v==''){
		jQuery(target).append('<div class="uap-login-notice" id="notice_' + t + '">' + e + '</div>');
	}
}

function uapStripeV2UpdateFields(){
	var country = jQuery('.stripe_v2_meta_data_country').val();
	var user_type = jQuery('.stripe_v2_meta_data_user_type').val();
	if (country!='us'){
		country = 'non_us';
	}
	jQuery('.uap-stripe-v2-field').each(function(){
		var temp_country = jQuery(this).attr('data-country');
		var temp_type = jQuery(this).attr('data-type');
		if (temp_country=='all' && temp_type=='all'){
			jQuery(this).css('display', 'block');
		} else if (country==temp_country){
			if (user_type==temp_type || temp_type=='all'){
				jQuery(this).css('display', 'block');
			}
		} else if (user_type==temp_type){
			if (country==temp_country || temp_country=='all'){
				jQuery(this).css('display', 'block');
			}
		} else {
			jQuery(this).css('display', 'none');
		}
	})

	var country = jQuery('.stripe_v2_meta_data_country').val();
	if ( country=='ca' ){
			jQuery( '.uap-js-routing-number' ).css( 'display', 'none' );
			jQuery( '.uap-js-transit-number' ).css( 'display', 'block' );
			jQuery( '.uap-js-institution-number' ).css( 'display', 'block' );
	}
}

function uapDoHideInfoAffiliateBar( selector )
{
		jQuery.ajax({
					type: 'post',
					url : decodeURI(ajax_url),
					data: {
								 action: 'uap_info_affiliate_bar_do_hide'
					},
					success: function ( response ) {
							jQuery( '.uap-menu-section' ).fadeOut( 300 );
							jQuery( '#uap_info_affiliate_bar' ).fadeOut( 300 );
							jQuery( '#uap_iab_style' ).remove();
					}
		});
}


function uapInfoAffiliateBarUpdateLink()
{
	  jQuery.ajax({
	      type 	: "post",
	      url 	: decodeURI(ajax_url),
	      data 	: {
	                   action							: "uap_ia_ajax_return_url_for_aff",
	                   aff_id							: jQuery( '#uap_info_affiliate_bar_extra_info' ).attr( 'data-affiliate_id' ),
	                   url								: window.location.href,
	                   campaign						: '',
	                   slug								: jQuery( '#ap_affiliate_bar_ref_type' ).val(),
	                   friendly_links			: jQuery( '#uap_affiliate_bar_friendly_links' ).val(),
	      },
	      success		: function ( response ) {
	      		if ( response ){
	        		var obj = JSON.parse( response );
	        		var theUrl = obj.url;
	        		if ( theUrl != 0 ){
	                	jQuery('.uap-js-iab-affiliate-link').val( theUrl );
	        		}
	        	}
	      }
	  });
}

function uapInfoAffiliateBarChangeBannerSize( size )
{
		jQuery.ajax({
				type 	: 'post',
				url 	: decodeURI(ajax_url),
				data 	: {
										 action							  : 'uap_ajax_get_banner_for_permalink',
										 affiliate_id				  : jQuery( '#uap_info_affiliate_bar_banner_extra_info' ).attr( 'data-affiliate_id' ),
										 uid									: jQuery( '#uap_info_affiliate_bar_banner_extra_info' ).attr( 'data-uid' ),
										 url								  : window.location.href,
										 size						      : size
				},
				success		: function ( response ) {
						if ( response ){
								jQuery('#uap_info_bar_banner_the_value').val( response );
								jQuery('#uap_info_bar_banner_wrapp').html( response );
						}
				}
		});
}

jQuery(document).ajaxSend(function (event, jqXHR, ajaxOptions) {
    if ( typeof ajaxOptions.data !== 'string' || ajaxOptions.data.includes( 'action=uap' ) === false ){
        return;
    }
    if ( typeof ajaxOptions.url === 'string' && ajaxOptions.url.includes('/admin-ajax.php')) {
       var token = jQuery('meta[name="uap-token"]').attr("content");
       jqXHR.setRequestHeader('X-CSRF-UAP-TOKEN', token );
    }
});

window.addEventListener( 'DOMContentLoaded', function(){
		jQuery('.uap-js-submit-campaign').on( 'click', function(e){
				e.preventDefault();
				jQuery.ajax({
						type : "post",
						url : decodeURI(ajax_url),
						data : {
											 action						: "uap_ajax_save_campaign",
											 campaignName			: jQuery('[name=campaign_name]').val(),
									 },
						success: function (response) {
								location.reload();
						}
			 });
		});

		/*
		// Login Form
		jQuery('#uap_login_username').on('blur', function(){
			var errorMessage = jQuery( '.uap-js-login-form-error-messages' ).attr( 'data-log' );
			uapCheckLoginField('log', errorMessage );
		});
		jQuery('#uap_login_password').on('blur', function(){
			var errorMessage = jQuery( '.uap-js-login-form-error-messages' ).attr( 'data-pwd' );
			uapCheckLoginField('pwd', errorMessage );
		});

		jQuery('#uap_login_form').on('submit', function(e){
			e.preventDefault();
			var u = jQuery('#uap_login_form [name=log]').val();
			var p = jQuery('#uap_login_form [name=pwd]').val();
			if (u!='' && p!=''){
				jQuery('#uap_login_form').unbind('submit').submit();
			} else {
				var errorMessageLog = jQuery( '.uap-js-login-form-error-messages' ).attr( 'data-log' );
				var errorMessagePwd = jQuery( '.uap-js-login-form-error-messages' ).attr( 'data-pwd' );
				uapCheckLoginField('log', errorMessageLog );
				uapCheckLoginField('pwd', errorMessagePwd );
				return FALSE;
			}
		});
		*/
		// end of Login Form

		// payment settings
		if ( jQuery( '.uap-js-payment-settings-nonce' ).val() != '' ){
				uapPaymentType();
				uapStripeV2UpdateFields();
				// stripe auth
				jQuery( '.uap-js-stripe-v3-auth-bttn' ).on( 'click', function(){
						jQuery( '#uap_stripe_auth_message' ).html( '' );
						jQuery( '#uap_stripe_auth_message' ).attr( 'class', 'uap-display-none' );
						jQuery( '.uap-js-stripe-v3-auth-bttn' ).html( jQuery( '.uap-js-stripe-v3-auth-bttn' ).attr('data-loading') );
						jQuery.ajax({
									type : "post",
									url : decodeURI(ajax_url),
									data : {
														 action						: "uap_ajax_stripe_generate_onboarding_url",
												 },
									success: function ( responseAsJson ) {
											var response = jQuery.parseJSON( responseAsJson );
											if ( response.status == 1 && response.url != '' ){
													window.location.href =  response.url;
											} else {
													// something went wrong
													jQuery( '#uap_stripe_auth_message' ).html( response.message );
													jQuery( '#uap_stripe_auth_message' ).attr( 'class', 'uap-warning-box-margin-top' );
													jQuery( '.uap-js-stripe-v3-auth-bttn' ).html( jQuery( '.uap-js-stripe-v3-auth-bttn' ).attr('data-submit_label') );
											}
									}
						 });
				})
		}

		// simple links
		jQuery('.uap-js-submit-simple-link').on( 'click', function(e){
				e.preventDefault();
				jQuery.ajax({
						type : "post",
						url : decodeURI(ajax_url),
						data : {
											 action						: "uap_ajax_save_simple_link",
											 url							: jQuery('[name=url]').val(),
											 currentUrl 			: jQuery( '.uap-js-simple-links-section' ).attr('data-current_url'),
									 },
						success: function (response) {
								if (response){
									document.location.href= response;
									return;
								}
								location.reload();
						}
			 });
		})

		// date picker
		if ( jQuery( '.uap-general-date-filter' ).length ){
			jQuery('.uap-general-date-filter').each(function(){
				jQuery(this).datepicker({
		            dateFormat : 'yy-mm-dd',
		            onSelect: function(datetext){
		                jQuery(this).val(datetext);
		            }
		        });
		    });
		}

		// selector 2
		if ( jQuery( '.uap-js-select2-data' ).length ){
				jQuery('.uap-js-select2-data').each(function( e, html ){
						var theSelector = jQuery( html ).attr( 'data-selector' );
						var theLabel = jQuery( html ).attr( 'data-label' );
						jQuery( theSelector ).select2({
							placeholder: theLabel,
							allowClear: true
						});
			  });
		}

		// date picker
		if ( jQuery( '.uap-js-date-picker-data' ).length ){
				jQuery('.uap-js-date-picker-data').each(function( e, html ){
						var theSelector = jQuery( html ).attr( 'data-selector' );
						jQuery( theSelector ).datepicker({
								dateFormat : "dd-mm-yy"
						});
				});
		}

		// file upload
		if ( jQuery( '.uap-js-upload-file-data' ).length ){
				jQuery('.uap-js-upload-file-data').each(function( e, html ){

						var rand = jQuery(html).attr( "data-rand" );
						var theUrl = jQuery(html).attr( "data-url" );
						var max_size = jQuery(html).attr( "data-max_size" );
						var alowed_types = jQuery(html).attr( "data-alowed_types" );
						var name = jQuery(html).attr( "data-name" );
						var alertText = jQuery(html).attr( "data-alert_text" );

						jQuery("#uap_fileuploader_wrapp_" + rand + " .uap-file-upload").uploadFile({
							onSelect: function (files) {
								jQuery("#uap_fileuploader_wrapp_" + rand + " .ajax-file-upload-container").css("display", "block");
								var check_value = jQuery("#uap_upload_hidden_" + rand ).val();
								if (check_value!="" ){
									alert( alertText );
									return false;
								}
								return true;
							},
							url: theUrl,
							fileName: "uap_file",
							dragDrop: false,
							showFileCounter: false,
							showProgress: true,
							showFileSize: false,
							maxFileSize: max_size,
							allowedTypes: alowed_types,
							onSuccess: function(a, response, b, c){
								if (response){
									var obj = jQuery.parseJSON(response);
									if (typeof obj.secret!="undefined"){
											jQuery("#uap_fileuploader_wrapp_" + rand ).attr("data-h", obj.secret);
									}
									var bttn = "<div onClick=\"uapDeleteFileViaAjax("+obj.id+", -1, '#uap_fileuploader_wrapp_" + rand + "', '" + name + "', '#uap_upload_hidden_" + rand + "');\" class='uap-delete-attachment-bttn'>Remove</div>";
									jQuery("#uap_fileuploader_wrapp_" + rand + " .uap-file-upload").prepend(bttn);
									switch (obj.type){
										case "image":
											jQuery("#uap_fileuploader_wrapp_" + rand + " .uap-file-upload").prepend("<img src="+obj.url+" class=\'uap-member-photo\' /><div class=\'uap-clear\'></div>");
										break;
										case "other":
											jQuery("#uap_fileuploader_wrapp_" + rand + " .uap-file-upload").prepend("<div class=uap-icon-file-type></div><div class=uap-file-name-uploaded>"+obj.name+"</div>");
										break;
									}
									jQuery("#uap_upload_hidden_" + rand).val(obj.id);
									setTimeout(function(){
										jQuery("#uap_fileuploader_wrapp_" + rand + " .ajax-file-upload-container").css("display", "none");
									}, 3000);
								}
							}
						});
				});
		}

		// Login
		if ( jQuery( '.uap-js-login-form-details' ).length ){
				jQuery('.uap-js-login-form-details').each(function( e, html ){
					  var usernameSelector = jQuery( html ).attr('data-username_selector');
						var passwordSelector = jQuery( html ).attr('data-password_selector');
						var errorMessage = jQuery( html ).attr('data-error_message');

						jQuery( usernameSelector ).on('blur', function(){
							uapCheckLoginField('log', errorMessage);
						});
						jQuery(passwordSelector).on('blur', function(){
							uapCheckLoginField('pwd', errorMessage);
						});
						jQuery('#uap_login_form').on('submit', function(e){
							e.preventDefault();
							var u = jQuery('#uap_login_form [name=log]').val();
							var p = jQuery('#uap_login_form [name=pwd]').val();
							if (u === '' && p === ''){
									// no u and p
									uapCheckLoginField('log', errorMessage);
									uapCheckLoginField('pwd', errorMessage );
									return false;
							} else if ( u === '' ){
									// no u
									uapCheckLoginField('log', errorMessage);
									return false;
							} else if ( p === '' ){
									// no p
									uapCheckLoginField('pwd', errorMessage);
									return false;
							} else {
									jQuery('#uap_login_form').unbind('submit').submit();
						 	}
						});
				});
		}
		// end of Login

		if ( jQuery( '.uap-hide-pw' ).length > 0 ){
			jQuery('.uap-hide-pw').each(function(index, button) {
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

		// Listing Top Affiliates
		if ( jQuery( '.uap-js-owl-settings-data' ).length ){
				jQuery('.uap-js-owl-settings-data').each(function( e, html ){
						uapInitiateOwl(html);
				});
		}

		// delete campaigns
		if ( jQuery( '.uap-js-account-page-campaigns-delete-item' ).length ){
				jQuery( '.uap-js-account-page-campaigns-delete-item' ).on( 'click', function( e, html ){
						var id = jQuery( this ).attr('data-id');
						jQuery('#uap_delete_campaign').val( id );
						jQuery('#uap_campaign_form').submit();
				});
		}

		// stripe tos
		if ( jQuery( '.uap-js-payment-settings-stripe-tos' ).length ){
				jQuery( '.uap-js-payment-settings-stripe-tos' ).on( 'click', function( e, html ){
					jQuery('.stripe_v2_tos').removeAttr('disabled');
					window.open('https://stripe.com/us/connect-account/legal', '_blank');
				});
		}

		if ( jQuery( '.uap-js-list-affiliate-links-remove' ).length > 0 ){
				jQuery( '.uap-js-list-affiliate-links-remove' ).on( 'click', function(){
					jQuery.ajax({
							type : "post",
							url : decodeURI(ajax_url),
							data : {
												 action						: "uap_ajax_do_remove_affiliate_link",
												 id							  : jQuery( this ).attr( 'data-id' ),
										 },
							success: function (response) {
									location.reload();
							}
				 });
				});
		}

		if ( jQuery( '.uap-js-list-affiliate-links-copy' ).length > 0 ){
				jQuery( '.uap-js-list-affiliate-links-copy' ).on( 'click', function(){
						const el = document.createElement('textarea');
						el.value = jQuery( this ).attr( 'data-link' );
						document.body.appendChild(el);
						el.select();
						document.execCommand('copy');
						document.body.removeChild(el);
						if ( typeof uapSwal !== 'undefined' ){
							uapSwal({
								title: jQuery( this ).attr( 'data-confirm' ),
								text: "",
								type: "success",
								showConfirmButton: false,
								confirmButtonClass: "btn-success",
								confirmButtonText: "OK",
								timer: 1000
							});
						}
				});
		}

		if ( jQuery('.uap-js-add-to-wallet-submit-bttn').length > 0 ){
				jQuery('.uap-js-add-to-wallet-submit-bttn').on('click', function( evt ){
						jQuery( '.uap-js-add-to-wallet-submit-bttn' ).attr('disabled', 'disabled');
						jQuery('.uap-js-public-add-to-wallet-form').submit();
						return true;
				});
		}

});

function uapInitiateOwl(selector)
{
		var selector = jQuery( selector ).attr( 'data-selector' );
		var autoHeight = jQuery( selector ).attr( 'data-autoHeight' );
		var animateOut = jQuery( selector ).attr( 'data-animateOut' );
		var animateIn = jQuery( selector ).attr( 'data-animateIn' );
		var lazyLoad = jQuery( selector ).attr( 'data-lazyLoad' );
		var loop = jQuery( selector ).attr( 'data-loop' );
		var autoplay = jQuery( selector ).attr( 'data-autoplay' );
		var autoplayTimeout = jQuery( selector ).attr( 'data-autoplayTimeout' );
		var autoplayHoverPause = jQuery( selector ).attr( 'data-autoplayHoverPause' );
		var autoplaySpeed = jQuery( selector ).attr( 'data-autoplaySpeed' );
		var nav = jQuery( selector ).attr( 'data-nav' );
		var navSpeed = jQuery( selector ).attr( 'data-navSpeed' );
		var dots = jQuery( selector ).attr( 'data-dots' );
		var dotsSpeed = jQuery( selector ).attr( 'data-dotsSpeed' );
		var responsiveClass = jQuery( selector ).attr( 'data-responsiveClass' );
		var navigation = jQuery( selector ).attr( 'data-navigation' );
		var owl = jQuery( selector );
		owl.owluapCarousel({
				items : 1,
				mouseDrag: true,
				touchDrag: true,

				autoHeight: autoHeight,

				animateOut: animateOut,
				animateIn: animateIn,

				lazyLoad : lazyLoad,
				loop: loop,

				autoplay : autoplay,
				autoplayTimeout: autoplayTimeout,
				autoplayHoverPause: autoplayHoverPause,
				autoplaySpeed: autoplaySpeed,

				nav : nav,
				navSpeed : navSpeed,
				navText: [ '', '' ],

				dots: dots,
				dotsSpeed : dotsSpeed,

				responsiveClass: responsiveClass,
				responsive:{
					0:{
						nav:false
					},
					450:{
						nav : navigation
					}
				}
		});
}

function uapReloadListAffiliateLinksTable()
{
		jQuery.ajax({
				type : "post",
				url : decodeURI(ajax_url),
				data : {
									 action						: "uap_ajax_load_list_affiliate_links_table"
							 },
				success: function (response) {
						jQuery( '.uap-js-list-affiliate-links-wrapp' ).remove();
						jQuery( '.uap-user-page-content' ).append( response );
				}
	 });
}

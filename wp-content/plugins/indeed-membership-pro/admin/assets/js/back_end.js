/*
* Ultimate Membership Pro - Backend Functions
*/
"use strict";
function setAddVal(id, target){
	var value;
	switch(jQuery(id).val()){
	 case '1':
	 	 value = 'ihc-login-template-1';
	      break;
	case '2':
	 	 value = 'ihc-login-template-7';
	      break;
	case '3':
	 	 value = 'ihc-login-template-7';
	      break;
	case '4':
	 	 value = 'ihc-login-template-5';
	      break;
	case '5':
	 	 value = 'ihc-login-template-3';
	      break;
	case '6':
	 	 value = 'ihc-login-template-6';
	      break;
	case '7':
	 	 value = 'ihc-login-template-2';
	      break;
	case '8':
	 	 value = 'ihc-login-template-8';
	      break;
	case '9':
	 	 value = 'ihc-login-template-9';
	      break;
	case '10':
	 	 value = 'ihc-login-template-10';
	      break;
	case '11':
	 	 value = 'ihc-login-template-11';
	      break;
	case '12':
	 	 value = 'ihc-login-template-12';
	      break;
	case '13':
	 	 value = 'ihc-login-template-13';
	      break;
	default:
		  value = 'ihc-login-template-1';
	}
	jQuery(target).val(value);
}


function ihcMakeInputhString(divCheck, showValue, hiddenInputId){
    var str = jQuery(hiddenInputId).val();
    if (str==-1){
				str = '';
		}
    if (str!=''){
				var showArr = str.split(',');
		} else {
			  var showArr = new Array();
		}
    if (jQuery(divCheck).is(':checked')){
        show_arr.push(showValue);
    } else {
        var index = show_arr.indexOf(showValue);
        show_arr.splice(index, 1);
    }
    str = show_arr.join(',');
    if (str==''){
				str = -1;
		}
    jQuery(hiddenInputId).val(str);
}

function ihcClosePopup(){
	jQuery('#popup_box').fadeOut(300, function(){
		jQuery(this).remove();
	});
}

function ihcLoginPreview(){
   	jQuery.ajax({
        type : "post",
        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
        data : {
                   action: "ihc_login_form_preview",
                   remember: jQuery('#ihc_login_remember_me').val(),
                   register: jQuery('#ihc_login_register').val(),
                   pass_lost: jQuery('#ihc_login_pass_lost').val(),
                   css: jQuery('#ihc_login_custom_css').val(),
                   template: jQuery('#ihc_login_template').val(),
                   ihc_login_show_sm: jQuery("#ihc_login_show_sm").val(),
                   ihc_login_show_recaptcha: jQuery('#ihc_login_show_recaptcha').val(),
               },
        success: function (data) {
        	jQuery('#ihc-preview-login').fadeOut(200, function(){
        		jQuery(this).html(data);
        		jQuery(this).fadeIn(400);
        	});
        }
   });
}

function ihcWriteTagValue(id, hiddenId, viewDivId, prevDivPrefix){
    if ( id.value==-1 ){
				return;
		}
    var hidden_i = jQuery(hiddenId).val();

    if (hidden_i!=''){
				var show_arr = hidden_i.split(',');
		} else {
				var show_arr = new Array();
		}

    if ( show_arr.indexOf( id.value ) == -1 ){
        show_arr.push(id.value);
		    var str = show_arr.join(',');
		    jQuery(hiddenId).val(str);
				var label = jQuery(id).find("option:selected").text();
				jQuery(viewDivId).append('<div id="'+prevDivPrefix+id.value+'" class="ihc-tag-item">'+label+'<div class="ihc-remove-tag" onclick="ihcremoveTag(\''+id.value+'\', \'#'+prevDivPrefix+'\', \''+hiddenId+'\');" title="Removing tag">x</div></div>');
    }

    jQuery(id).val(-1);
}

function ihcRemoveNoticeAfterWriteTag(){
	jQuery( '#ihc-change-target-user-set-regex' ).removeClass( 'iump-form-field-notice' );
	jQuery( '#ihc-change-target-user-set' ).removeClass( 'iump-form-field-notice' );
	jQuery( '#iump_admin_car_target_message' ).html('');
}

function ihcWriteTagValueForEditPost(id, hiddenId, viewDivId, prevDivPrefix){
    if ( id.value == -1 ){
			return;
		}
    var hidden_i = jQuery(hiddenId).val();

    if (hidden_i!=''){
				var show_arr = hidden_i.split(',');
		} else {
				var show_arr = new Array();
		}

    if ( show_arr.indexOf( id.value ) == -1 ){
        show_arr.push(id.value);

		    var str = show_arr.join(',');
		    jQuery(hiddenId).val(str);

				var label = jQuery(id).find("option:selected").text();
				jQuery(viewDivId).append('<div id="'+prevDivPrefix+id.value+'" class="ihc-tag-item">'+label+'<div class="ihc-remove-tag" onclick="ihcremoveTagForEditPost(\''+id.value+'\', \'#'+prevDivPrefix+'\', \''+hiddenId+'\');" title="Removing tag">x</div></div>');

				//drip
				jQuery('#ihc_drip_content_list_targets').append('<div id="ihc_drip_target-'+id.value+'">'+label+'</div>');
				if (jQuery('#ihc_mb_type').val()=='show'){
					jQuery('#ihc_drip_content_empty_meta_box').css('display', 'none');
					jQuery('#ihc_drip_content_meta_box').css('display', 'block');
				}
    }
    jQuery(id).val(-1);
}

function ihcWriteLevelTagValue(id, hiddenId, viewDivId, prevDivPrefix){
    if (id.value==-2){
			 	return;
		}
    var hidden_i = jQuery(hiddenId).val();

    if ( hidden_i != '' ){
				var show_arr = hidden_i.split(',');
		} else {
				var show_arr = new Array();
		}

    if (show_arr.indexOf(id.value)==-1){
        show_arr.push(id.value);

		    var str = show_arr.join(',');
		    jQuery(hiddenId).val(str);

				var label = jQuery(id).find("option:selected").text();
				jQuery(viewDivId).append('<div id="'+prevDivPrefix+id.value+'" class="ihc-tag-item">'+label+'<div class="ihc-remove-tag" onclick="ihcremoveTagForEditPost(\''+id.value+'\', \'#'+prevDivPrefix+'\', \''+hiddenId+'\');" title="Removing tag">x</div></div>');

				//drip
				jQuery('#ihc_drip_content_list_targets').append('<div id="ihc_drip_target-'+id.value+'">'+label+'</div>');
				if (jQuery('#ihc_mb_type').val()=='show'){
						jQuery('#ihc_drip_content_empty_meta_box').css('display', 'none');
						jQuery('#ihc_drip_content_meta_box').css('display', 'block');
				}
    }
    jQuery(id).val(-2);
}

function ihcremoveTagForEditPost(removeVal, prevDivPrefix, hiddenId){
		jQuery(prevDivPrefix+removeVal).fadeOut(200, function(){
			jQuery(this).remove();
		});

    var hidden_i = jQuery(hiddenId).val();
    var show_arr = hidden_i.split(',');

    var show_arr = removeArrayElement(removeVal, show_arr);
    var str = show_arr.join(',');
		jQuery(hiddenId).val(str);

		//drip
		jQuery('#ihc_drip_target-'+removeVal).remove();
		if (str==''){
			jQuery('#ihc_drip_content_meta_box').fadeOut(300, function(){
				jQuery('#ihc_drip_content_empty_meta_box').css('display', 'block');
			});
		}
}

function ihcWriteTagValueCfl(id, hiddenId, viewDivId, prevDivPrefix){
    if ( id.value==-2 ){
				return;
		}
    var hidden_i = jQuery(hiddenId).val();

    if ( hidden_i != '' ){
				var show_arr = hidden_i.split(',');
		} else {
				var show_arr = new Array();
		}

    if ( show_arr.indexOf(id.value) == -1 ){
        show_arr.push(id.value);

		    var str = show_arr.join(',');
		    jQuery(hiddenId).val(str);

				var label = jQuery(id).find("option:selected").text();
				jQuery(viewDivId).append('<div id="'+prevDivPrefix+id.value+'" class="ihc-tag-item">'+label+'<div class="ihc-remove-tag" onclick="ihcremoveTag(\''+id.value+'\', \'#'+prevDivPrefix+'\', \''+hiddenId+'\');" title="Removing tag">x</div></div>');
    }

    jQuery(id).val(-2);
}

function ihcWriteTagValueListUsers(id, hiddenId, viewDivId, prevDivPrefix){
    if (id.value==-1){
			 	return;
		}
    var hidden_i = jQuery(hiddenId).val();

    if ( hidden_i != '' ){
				var show_arr = hidden_i.split(',');
		} else {
				var show_arr = new Array();
		}

    if ( show_arr.indexOf(id.value) == -1 ){
        show_arr.push(id.value);

		    var str = show_arr.join(',');
		    jQuery(hiddenId).val(str);

				var label = jQuery(id).find("option:selected").text();
				jQuery(viewDivId).append('<div id="'+prevDivPrefix+id.value+'" class="ihc-tag-item">'+label+'<div class="ihc-remove-tag" onclick="ihcremoveTag(\''+id.value+'\', \'#'+prevDivPrefix+'\', \''+hiddenId+'\');ihcPreviewUList();" title="Removing tag">x</div></div>');
    }

    jQuery(id).val(-1);
}

function ihcShowHideDrip(){
		if (jQuery('#ihc_mb_type').val()=='show'){
				jQuery('#ihc_drip_content_empty_meta_box').css('display', 'none');
				jQuery('#ihc_drip_content_meta_box').css('display', 'block');
		} else {
				jQuery('#ihc_drip_content_empty_meta_box').css('display', 'block');
				jQuery('#ihc_drip_content_meta_box').css('display', 'none');
		}
}

function ihcremoveTag(removeVal, prevDivPrefix, hiddenId){
		jQuery(prevDivPrefix+removeVal).fadeOut(200, function(){
				jQuery(this).remove();
		});

    var hidden_i = jQuery(hiddenId).val();
    var show_arr = hidden_i.split(',');

    show_arr = removeArrayElement(removeVal, show_arr);
    var str = show_arr.join(',');
		jQuery(hiddenId).val(str);
}

function removeArrayElement(elem, arr){
	for ( var i=0; i<arr.length; i++ ) {
	    if ( arr[i] == elem ){
	    		arr.splice(i, 1);
	    }
	}
	return arr;
}

function ihcRedirectReplaceDd(v){
	var replace_id = '#ihc-meta-box-replace';
	var redirect_id = '#ihc-meta-box-redirect';
	var hidden_replace_content = '#ihc_replace_content';
	if ( v == 'redirect' ){
		jQuery(replace_id).attr('class', 'ihc-display-none');
		jQuery(redirect_id).attr('class', 'ihc-display-block');
		//hide the replace content editor
		jQuery(hidden_replace_content).fadeOut(300);
	} else {
		jQuery(redirect_id).attr('class', 'ihc-display-none');
		jQuery(replace_id).attr('class', 'ihc-display-block');
		//hide the replace content editor
		jQuery(hidden_replace_content).fadeIn(300);
	}
}

jQuery('#ihc_locker_custom_content').on('blur', function(){
		ihcLockerPreview();
});

function ihcUpdateTextarea(){
    var content = jQuery( "#ihc_locker_custom_content_ifr" ).contents().find( '#tinymce' ).html();
    jQuery('#ihc_locker_custom_content').val(content);
    ihcLockerPreview();
}

function ihcLockerPreview(){
	  //preview locker based of current selections
   	jQuery.ajax({
        type : "post",
        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
        data : {
                   action: "ihc_locker_preview_ajax",
                   ihc_locker_template: jQuery('#ihc_locker_template').val(),
				   		 		 ihc_locker_login_template: jQuery('#ihc_locker_login_template').val(),
                   ihc_locker_login_form: jQuery('#ihc_locker_login_form').val(),
                   ihc_locker_additional_links: jQuery('#ihc_locker_additional_links').val(),
                   ihc_locker_custom_content: jQuery('#ihc_locker_custom_content').val(),
                   ihc_locker_custom_css: jQuery('#ihc_locker_custom_css').val(),
                   ihc_locker_display_sm: jQuery('#ihc_locker_display_sm').val(),
        },
        success: function (response) {
	        	jQuery('#locker-preview').fadeOut(200, function(){
		        		jQuery(this).html(response);
		        		jQuery(this).fadeIn(400);
	        	});
        }
   });
}

function ihcLockerPreviewWi(id, popupDisplay){
	if ( id == -1 ){
		return;
	}
	//preview locker based on id
   	jQuery.ajax({
        type : "post",
        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
        data : {
                   action					: 'ihc_locker_preview_ajax',
                   locker_id			: id,
                   popup_display	: popupDisplay,
									 nonce					: jQuery('meta[name="ump-admin-token"]').attr("content")
               },
        success: function (response) {
        	if (popupDisplay){
        		jQuery('#locker-preview').append(response);
        	} else {
            	jQuery('#locker-preview').fadeOut(200, function(){
            		jQuery(this).html(response);
            		jQuery(this).fadeIn(400);
            	});
        	}

        }
   });
}

function ihcReq(id, n){
	if ( !jQuery(id).is(':checked') ){
			if ( jQuery('#ihc-field-display-admin'+n ).val() == 0
					 && jQuery('#ihc-field-display-public-reg'+n).val() == 0
					 && jQuery( '#ihc-field-display-public-ap'+n).val() == 0 ){
						 jQuery('#req-check-'+n).removeAttr('checked');
 						 jQuery('#ihc-require-'+n).val(0);
			}
	}
}

function ihcSortableOnOff(i, selector){
		if (window.ihc_sortable){
			//disable
			jQuery( selector ).sortable( "disable" );
			jQuery( i ).attr('class', 'ihc-sortable-off');
			jQuery(selector).css('cursor', '');
			jQuery(selector).css('opacity', '1');
			jQuery('#ihc-reorder-msg').fadeOut(200);
			window.ihc_sortable = 0;
		} else {
			//enable
			jQuery( selector ).sortable( "enable" );
			jQuery( i ).attr('class', 'ihc-sortable-on');
			jQuery(selector).css('cursor', 'move');
			jQuery(selector).css('opacity', '0.7');
			jQuery('#ihc-reorder-msg').fadeIn(200);
			window.ihc_sortable = 1;
		}
}

function ihcSelectAllCheckboxes(check, selector){
	if (jQuery(check).is(':checked')){
		jQuery(selector).each(function(){
			jQuery(this).attr('checked', 'checked');
		});
	} else {
		jQuery(selector).each(function(){
			jQuery(this).removeAttr('checked');
		});
	}
}

function ihcDhSelector(id, display){
	if (display){
		jQuery(id).css('visibility', 'visible');
	} else {
		jQuery(id).css('visibility', 'hidden');
	}
}

function ihcDeleteUserPrompot(i){
		ihcSwal({
			title: jQuery('.ihc-js-admin-messages').attr('data-delete_user'),
			text: "",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: jQuery('.ihc-js-admin-messages').attr('data-confirm_label'),
			closeOnConfirm: true
		},
		function(){
			jQuery.ajax({
					type : 'post',
					url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
					data : {
										 action: 'ihc_delete_user_via_ajax',
										 id: i,
								 },
					success: function () {
						jQuery('#ihc_user_id_'+i).fadeOut(200);
					}
		 });
	 }
 	);
}

function ihcFirstConfirm(m){
		ihcSwal({
			title: m,
			text: "",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "OK",
			closeOnConfirm: true
		},
		function(){
			return true;
	 });
}

function ihcFirstConfirmBeforeSubmitForm(m, okLabel){
		ihcSwal({
				title: m,
				text: "",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: okLabel,
				closeOnConfirm: true
			},
			function(){
				jQuery("form[name='ihc-users']").submit();
	 	});
}

function indeedConfirmAndRedirect(url){
		ihcSwal({
			title: "Are you sure?",
			text: "",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "OK",
			closeOnConfirm: true
		},
		function(){
			 window.location.href = url;
	 });
}

function ihcRegisterLockerPreview(){
	  // preview locker based of current selections
   	jQuery.ajax({
        type : 'post',
        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
        data : {

                   action				: 'ihc_register_preview_ajax',
                   template			: jQuery('#ihc_register_template').val(),
                   custom_css		: jQuery('#ihc_register_custom_css').val(),
               },
        success: function (response) {
        	jQuery('#register_preview').fadeOut(200, function(){
	        		jQuery(this).html(response);
	        		jQuery(this).fadeIn(400);
        	});
        }
   });
}

function ihcSelectShDiv(s, target, value){
	if (jQuery(s).val()==value){
			jQuery(target).fadeIn(300, function(){
					jQuery(this).css('display', 'block');
			});
	} else {
		jQuery(target).fadeOut(300, function(){
			jQuery(this).css('display', 'none');
		});
	}
}

function ihcApproveUser(id){
   	jQuery.ajax({
        type : 'post',
        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
        data : {
                   action: 'ihc_approve_new_user',
                   uid: id,
               },
        success: function (response) {
        	jQuery('#user-'+id+'-status').fadeOut(200, function(){
        		var the_span_styl = 'ihc-approve-user-span';
        		jQuery(this).html('<span class="'+the_span_styl+'">'+response+'</span>');
        		jQuery(this).fadeIn(200);
        		jQuery('#approveUserLNK'+id).fadeOut(200, function(){
        			jQuery(this).html('');
        		});
        	});
        }
   });
}


function ihcPreviewSelectLevels(){
   	jQuery.ajax({
        type : 'post',
        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
        data : {
                   action: 'ihc_preview_select_level',
                   template: jQuery('#ihc_level_template').val(),
                   custom_css: jQuery('#ihc_select_level_custom_css').val()
               },
        success: function (response) {
	        	jQuery('#ihc_preview_levels').fadeOut(200, function(){
		        		jQuery(this).html(response);
		        		jQuery(this).fadeIn(200);
	        	});
        }
   });
}

//OPT IN
function ihcConnectAweber(t){
    jQuery.ajax({
        type : "post",
        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
        data : {
                action: "ihc_update_aweber",
                auth_code: jQuery( t ).val()
            },
        success: function (data) {
            alert('Connected');
        }
	});
}

function ihcGetCcList( ihc_cc_user,ihc_cc_pass ){
  jQuery("#ihc_cc_list").find('option').remove();
	jQuery.ajax({
            type : "post",
			dataType: 'JSON',
            url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
            data : {
                    action: "ihc_get_cc_list",
                    ihc_cc_user: jQuery( ihc_cc_user ).val(),
                    ihc_cc_pass: jQuery( ihc_cc_pass ).val()
            },
            success: function (data) {
								jQuery.each(data, function(i, option){
										jQuery("<option/>").val(i).text(option.name).appendTo("#ihc_cc_list");
								});
						}
    });
}

function ihcAccessPaymentType(v){
	var arr = ['#limited_access_metas','#date_interval_access_metas','#regular_period_access_metas', '#set_expired_level'];
	for ( var i=0; i<arr.length; i++ ) {
	    jQuery(arr[i]).css('display', 'none');
	}
	if(v !== 'unlimited'){
		jQuery('#billind_rec_label').css('display', 'inline-block');
		jQuery('#set_expired_level').css('display', 'table');
		jQuery('#set_grace_period').css('display', 'table');
	}

	switch (v){
		case 'limited':
			jQuery(arr[0]).css('display', 'block');
			jQuery('#billing_type_1').css('display', 'inline-block');
			jQuery('#billing_type_2').css('display', 'none');
			jQuery('#regular_period_billing').css('display', 'none');
			jQuery('#set_cancel_level').css('display', 'none');
			break;
		case 'date_interval':
			jQuery(arr[1]).css('display', 'block');
			jQuery('#billing_type_1').css('display', 'inline-block');
			jQuery('#billing_type_2').css('display', 'none');
			jQuery('#regular_period_billing').css('display', 'none');
			jQuery('#set_cancel_level').css('display', 'none');
			break;
		case 'regular_period':
			jQuery(arr[2]).css('display', 'block');
			jQuery('#billing_type_2').val('bl_ongoing');
			jQuery('#billing_type_1').css('display', 'none');
			jQuery('#billing_type_2').css('display', 'inline-block');
			jQuery('#trial_period_billing').css('display', 'inline-block');
			jQuery('#set_cancel_level').css('display', 'table');
			break;
		case 'unlimited':
			jQuery('#billing_type_1').css('display', 'none');
			jQuery('#billing_type_2').css('display', 'none');
			jQuery('#regular_period_billing').css('display', 'none');
			jQuery('#trial_period_billing').css('display', 'none');
			jQuery('#billind_rec_label').css('display', 'none');
			jQuery('#set_cancel_level').css('display', 'table');
			jQuery('#set_cancel_level').css('display', 'none');
			jQuery('#set_grace_period').css('display', 'none');
			break;
	}
}

function ihcAfterCancelAction(v){
	if (v=='2'){
		jQuery('#aftercancel_level').css('display', 'block');
	} else {
		jQuery('#aftercancel_level').css('display', 'none');
	}
}

function ihcCheckBillingType(v){
	if (v=='bl_limited'){
		jQuery('#regular_period_billing').css('display', 'block');
	} else {
		jQuery('#regular_period_billing').css('display', 'none');
	}
}

function ihcAddZero(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}
function iumpCheckAndH(from, target){
		if ( jQuery(from).is(":checked") ){
				jQuery(target).val(1);
		} else {
			jQuery(target).val(0);
		}
}
function ihcShowHide(div){
	if ( jQuery(div).hasClass('ihc-display-none') ){
			jQuery(div).removeClass('ihc-display-none');
			jQuery(div).addClass('ihc-display-block');
	} else if ( jQuery(div).hasClass('ihc-display-block') ){
			jQuery(div).removeClass('ihc-display-block');
			jQuery(div).addClass('ihc-display-none');
	}
}

function checkAndH(id, target){
	if(jQuery(id).is(':checked')){
		jQuery(target).val(1);
	}else{
		jQuery(target).val(0);
	}
}


function ihcChangeTrialType(v){
	if (v==1){
		jQuery('#trial_couple_cycles').fadeOut(200, function(){
			jQuery('#trial_certain_period').css('display', 'block');
		});
	} else {
		jQuery('#trial_certain_period').fadeOut(200, function(){
			jQuery('#trial_couple_cycles').css('display', 'block');
		});
	}
}

function ihcRegisterFields(v){
	jQuery('#ihc-register-field-values').fadeOut(200);
	jQuery('#ihc-register-field-plain-text').fadeOut(200);
	jQuery('#ihc-register-field-conditional-text').fadeOut(200);
	if (v=='select' || v=='checkbox' || v=='radio' || v=='multi_select'){
		jQuery('#ihc-register-field-values').fadeIn(200);
	} else if (v=='plain_text'){
		jQuery('#ihc-register-field-plain-text').fadeIn(200);
	} else if (v=='conditional_text'){
		jQuery('#ihc-register-field-conditional-text').fadeIn(200);
	}
}

function ihcAddNewRegisterFieldValue(){
	var s = '<div class="ihc-custom-field-item-wrapp ihc-custom-field-item-wrapp-st" >';
	s += '<input type="text" name="values[]" value=""/> ';
	s += '<i class="fa-ihc ihc-icon-remove-e" onclick="jQuery(this).parent().remove();"></i>';
	s += '<i class="fa-ihc fa-arrows-ihc"></i>';
	s += '</div>';
	jQuery('.ihc-register-the-values').append(s);
}

function ihcMakeInputhString(divCheck, showValue, hidden_input_id){
    var str = jQuery(hidden_input_id).val();
    if (str!=''){
    	var show_arr = str.split(',');
    } else{
    	var show_arr = new Array();
    }
    if ( jQuery( divCheck ).is(':checked') ){
        show_arr.push(showValue);
    } else {
        var index = show_arr.indexOf(showValue);
        show_arr.splice(index, 1);
    }
    str = show_arr.join(',');
    jQuery(hidden_input_id).val(str);
}

function ihcGenerateCode(target, length){
		var str = "";
	  var characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

	  for ( var i=0; i<length; i++ ){
	    	str += characters.charAt(Math.floor(Math.random() * characters.length));
		}
		jQuery(target).val(str);
}

function ihcDiscountType(v){
		if (v=='percentage'){
				jQuery("#discount_currency").fadeOut(300, function(){
						jQuery("#discount_percentage").css("display", "inline");
				});
		} else {
				jQuery("#discount_percentage").fadeOut(300, function(){
						jQuery("#discount_currency").css("display", "inline");
				});
		}
}

function ihcDeleteCoupon(i, d){
	var c = confirm("Are you sure you want to delete this coupon?");
	if (c){
		  //delete here
	   	jQuery.ajax({
		        type : 'post',
		        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
		        data : {
		                   action: 'ihc_delete_coupon_ajax',
		                   id: i
		               },
		        success: function (r) {
		        	if (r){
		        		jQuery(d).fadeOut(300);
		        	}
		        }
		   });
    }
}

function ihcChangeNotificationTemplate(){
   	jQuery.ajax({
	        type : 'post',
	        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
	        data : {
	                   action: 'ihc_notification_templates_ajax',
	                   type: jQuery('#notification_type').val()
	               },
	        success: function (r) {
	        	var o = jQuery.parseJSON(r);
	        	jQuery('#notification_subject').val(o.subject);
	        	jQuery('#ihc_message').val(o.content);
						jQuery('#ihc_notification_explanation').html(o.explanation);
	        	jQuery( "#ihc_message_ifr" ).contents().find( '#tinymce' ).html(o.content);
	        }
   });
}

function ihcRemoveCurrency(c){
   	jQuery.ajax({
        type : 'post',
        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
        data : {
                   action: 'ihc_delete_currency_code_ajax',
                   code: c
               },
        success: function (r) {
	        	if ( r ){
	        			jQuery("#ihc_div_" + c ).fadeOut(300);
	        	}
        }
   });
}

function ihcCheckboxDivRelation(c, t){
	/*
	 * c = checkbox id to check
	 * t = target div
	 */
	var o = 0.5;
	if (jQuery(c).is(":checked")){
		o = 1;
	}
	jQuery(t).css("opacity", o);
}

function ihcChangeColorScheme(id, value, where ){
    jQuery('#colors_ul li').each(function(){
        jQuery(this).attr('class', 'color-scheme-item');
    });
    jQuery(id).attr('class', 'color-scheme-item-selected');
    jQuery(where).val(value);
}
function ihcChageColor(id, value, where ){
	jQuery('#colors_ul li').each(function(){
			jQuery(this).removeClass('color-scheme-item-selected');
	});
	jQuery(id).addClass('color-scheme-item-selected');
	jQuery(where).val(value);
}

function ihcChangeColorSchemeWd(id, value, where ){
		var non_selected = 'color-scheme-item';
		var selected = 'color-scheme-item-selected';
		var c = jQuery(id).attr('class');
	  jQuery('#colors_ul li').each(function(){
	      jQuery(this).attr( 'class', non_selected );
	  });
	  jQuery(where).val('');
	  if (c==non_selected){
		    jQuery(id).attr('class', selected);
		    jQuery(where).val(value);
	  }
}

function ihcPreviewUList(){
	jQuery('#preview').html('');
	jQuery("#preview").html('<div class="ihc-loading-img-wrapper"><img src="'+window.ihc_plugin_url+'admin/assets/images/loading.gif"/></div>');
	var meta = [];
	meta.num_of_entries = jQuery('#num_of_entries').val();
	meta.entries_per_page = jQuery('#entries_per_page').val();
	meta.order_by = jQuery('#order_by').val();
	meta.order_type = jQuery('#order_type').val();
	if (jQuery('#filter_by_level').is(":checked")){
			meta.filter_by_level = 1;
			meta.levels_in = jQuery('#levels_in').val();
	}
	meta.user_fields = jQuery('#user_fields').val();

	if (jQuery('#include_fields_label').is(':checked')){
		meta.include_fields_label = 1;
	}
	meta.theme = jQuery('#theme').val();
	meta.color_scheme = jQuery('#color_scheme').val();
	meta.columns = jQuery('#columns').val();
	if (jQuery('#align_center').is(":checked")){
		meta.align_center = 1;
	}
	if (jQuery('#inside_page').is(":checked")){
		meta.inside_page = 1;
	}
	if (jQuery('#slider_set').is(":checked")){
		meta.slider_set = 1;
		meta.items_per_slide = jQuery('#items_per_slide').val();
		meta.speed = jQuery("#speed").val();
		meta.pagination_speed = jQuery('#pagination_speed').val();
		meta.pagination_theme = jQuery('#pagination_theme').val();
		meta.animation_in = jQuery('#animation_in').val();
		meta.animation_out = jQuery('#animation_out').val();
		var slider_special_metas = ['bullets', 'nav_button', 'autoplay', 'stop_hover', 'responsive', 'autoheight', 'lazy_load', 'loop'];
		for (var i=0; i<slider_special_metas.length; i++){
				if ( jQuery('#'+slider_special_metas[i]).is(":checked") ){
						meta[slider_special_metas[i]] = 1;
				}
		}
	}

	if (jQuery('#show_search').is(':checked')){
		meta.show_search = 1;
		meta.search_by = jQuery('#search_by').val();
	}
	if (jQuery('#show_search_filter').is(':checked') && jQuery('#search_filter').val()!=''){
		meta.show_search_filter = 1;
		meta.search_filter_items = jQuery('#search_filter').val();
	}

	meta.general_pagination_theme = jQuery('#general_pagination_theme').val();
	meta.pagination_pos = jQuery('#pagination_pos').val();

	if (jQuery('#exclude_pending').is(':checked')){
			meta.exclude_pending = 1;
	}

	///SHORTCODE
	var str = "[ihc-list-users ";
	for ( var key in meta ) {
		str += key + " ='" + meta[key] +"' ";
	}
	str += ']';
    jQuery('.the-shortcode').html(str);
    jQuery(".php-code").html('&lt;?php echo do_shortcode("'+str+'");?&gt;');

    //AJAX CALL
   	jQuery.ajax({
        type : 'post',
        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
        data : {
                   action			: 'ihc_preview_user_listing',
                   shortcode	:	str
               },
        success: function (r) {
        		jQuery('#preview').html(r);
						ihcInitiateOwl( jQuery( '.ihc-js-owl-settings-data' ) );
        }
   	});
}

function ihcAdminDeleteUserLevelRelationship(l_id, u_id){
   	jQuery.ajax({
        type : 'post',
        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
        data : {
                   action			: 'ihc_delete_user_level_relationship',
                   uid				: u_id,
                   lid				: l_id
               },
        success: function (r){
        	jQuery('#tr_level_user_' + l_id + '_' + u_id).remove();
        }
   	});
}

function ihcFirstConfirmThenRedirect(u){
	ihcSwal({
			title: "Are you sure you want to delete this Membership?",
			text: "",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Delete",
			closeOnConfirm: true
	},
	function(){
		 window.location.href = u;
	});

}

function ihcChangeUapAffiliate(i){
	var c = jQuery('#uap_checkbox_' + i);
	if (c.is(':checked')){
		/// make affiliate
		var the_action = 1;
		jQuery.ajax({
				type : 'post',
				url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
				data : {
									 action		: 'ihc_make_user_affiliate',
									 uid			: i,
									 act			: 1
				},
				success: function (r){

				}
		});
	} else {
		///remove from affiliate
		ihcSwal({
			title: 'Are you sure you want to remove this User From Affiliate list?',
			text: "",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Delete",
			closeOnConfirm: true
		},
		function(response){
			if ( !response ){
				//c.attr('checked', 'checked');
					console.log(c);
				c.checked = true;
				return false;
			} else {
				// go to ajax
				jQuery.ajax({
						type : 'post',
						url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
						data : {
											 action		: 'ihc_make_user_affiliate',
											 uid			: i,
											 act			: 0
						},
						success: function (r){

						}
				});
			}
		});
	}
}

function ihcNotificationLevelOnlyFor(){
	var v = jQuery('#notification_type').val();
	switch (v){
		case 'admin_user_register':
		case 'register':
		case 'review_request':
		case 'before_expire':
		case 'expire':
		case 'payment':
		case 'bank_transfer':
		case 'admin_user_expire_level':
		case 'admin_before_user_expire_level':
		case 'admin_user_payment':
			/// show only for level id
			jQuery('[name=level_id]').removeAttr('disabled');
			break;
		case 'email_check':
		case 'email_check_success':
		case 'reset_password_process':
		case 'reset_password':
		case 'change_password':
		case 'approve_account':
		case 'delete_account':
			/// hide
			jQuery('[name=level_id]').val(-1);
			jQuery('[name=level_id]').attr('disabled', 'disabled');
			break;
	}
}

function ihcCheckEmailServer(){
	jQuery.ajax({
			type : 'post',
	        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
	        data : {
	                   action: 'ihc_check_mail_server',
	        },
	        success: function (r){
	        		alert(window.ihc_messages.email_server_check);
	        }
	});
}

function ihcUpdateLevelSlugSpan(){
	var s = jQuery('#level_slug_id').val();
	jQuery('.plan-slug-name').html(s);
}


function ihcApMakeVisible(t, m){
	jQuery('.ihc-ap-tabs-list-item').removeClass('ihc-ap-tabs-selected-item');
	jQuery(m).addClass('ihc-ap-tabs-selected-item');
	jQuery('.ihc-ap-tabs-settings-item').fadeOut(200, function(){
		jQuery('#ihc_tab_item_' + t).css('display', 'block');
	});
}

function openMediaUp(target, img_target){
    //If the uploader object has already been created, reopen the dialog
  var custom_uploader;
  if (custom_uploader) {
      custom_uploader.open();
      return;
  }
  //Extend the wp.media object
	custom_uploader = wp.media.frames.file_frame = wp.media({
      title: 'Choose Image',
      button: {
          text: 'Choose Image'
      },
      multiple: false
  });
  //When a file is selected, grab the URL and set it as the text field's value
  custom_uploader.on('select', function() {
		  var attachment = custom_uploader.state().get('selection').first().toJSON();
		  jQuery(target).val(attachment.url);
		  if (img_target!=''){
		    	jQuery(img_target).attr('src', attachment.url);
		    	jQuery(img_target).css('display', 'block');
		  }
  });
  //Open the uploader dialog
  custom_uploader.open();
}

function ihcDoBuiltInvidualPages(){
	jQuery('#ihc_loading').css('visibility', 'visible');
	jQuery.ajax({
		type : 'post',
	    url : decodeURI(window.ihc_site_url) + '/wp-admin/admin-ajax.php',
	    data : {
	            action: 'ihc_do_generate_individual_pages',
	    },
	    success: function (r){
	    	jQuery('#ihc_loading').css('visibility', 'hidden');
	    }
	});
}

function ihcAddToHiddenWhenUncheck(c, v, t){
    var i = jQuery(t).val();
    if (i!=''){
    	var a = i.split(',');
    } else {
    	var a = new Array();
    }
	if (jQuery(c).is(':checked')){
		var k = a.indexOf(v);
		if (k!=-1){
			a.splice(k, 1);
		}
	} else {
        a.push(v);
	}
	if (a.length){
		var s = a.join(',');
	} else {
		var s = '';
	}
	jQuery(t).val(s);
}

function iumpAdminPreviewInvoice(){
	var metas = jQuery('#invoice_form').serializeArray();
	jQuery.ajax({
		type : 'post',
	    url : decodeURI(window.ihc_site_url) + '/wp-admin/admin-ajax.php',
	    data : {
	            action			: 'ihc_preview_invoice_via_ajax',
	            m						: metas
	    },
	    success: function (r){
	    	jQuery('#preview_container').html(r);
	    }
	});
}

function ihcInsidePageChangeContentType(){
		if (jQuery('#iump-switch_left').is(':checked')){
				jQuery('#ihc_listing_users_content_basic').css('display', 'block');
				jQuery('#ihc_listing_users_content_extra_custom').css('display', 'none');
		} else {
				jQuery('#ihc_listing_users_content_basic').css('display', 'none');
				jQuery('#ihc_listing_users_content_extra_custom').css('display', 'block');
		}
}

function ihcDoCleanUpLogs(u){
	var o = jQuery('#older_then_select').val();
	window.location.href = u + '&do_cleanup_logs=1&older_then=' + o;
}

function ihcCheckFieldLimit(limit, d){
	var val = jQuery(d).val().length;
	if (val>limit){
		jQuery(d).val('');
		alert(limit + ' is the maximum number of characters for this field!');
	}
}


function ihcDoUpdateHashField(){
	var string = jQuery('#ihc_api_hash').val();
	jQuery('.ihc-base-api-link-hash').html(string);
}


///// SHINY SELECT

function indeedShinySelect(params){
	/*
	 * params selector, item_selector, option_name_code, option_name_icon, default_icon, default_code
	 */
	this.selector = params.selector; ///got # in front of it
	this.popup_id = 'indeed_select_' + params.option_name_code;
	this.popup_visible = false;
	this.option_name_code = params.option_name_code;
	this.option_name_icon = params.option_name_icon;
	this.item_selector = params.item_selector; /// got . in front of it
	this.init_default = params.init_default;
	this.second_selector = params.second_selector;
	var current_object = this;

	jQuery(current_object.selector).after('<input type="hidden" name="' + current_object.option_name_code + '" value="' + params.default_code + '" />');
	jQuery(current_object.selector).after('<input type="hidden" name="' + current_object.option_name_icon + '" value="' + params.default_icon + '" />');
	jQuery(current_object.selector).after('<div class="indeed_select_popup ihc-display-none" id="' + current_object.popup_id + '"></div>');

	///run init
	if (this.init_default){
		jQuery(current_object.selector).html('<i class="fa-ihc-preview fa-ihc ' + params.default_icon + '"></i>');
	}


	function loadDataViaAjax(){
			var img = "<img src='" + decodeURI(window.ihc_site_url)+'/wp-content/plugins/indeed-membership-pro/admin/assets/images/loading.gif' + "' class='ihc-loading-img'/>";
			jQuery('#'+current_object.popup_id).html(img);
			jQuery('#'+current_object.popup_id).css('display', 'block');
			jQuery.ajax({
			    type : 'post',
			    dataType: "text",
			    url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
			    data : {
			             action: 'ihc_get_font_awesome_popup'
			    },
			    success: function (r){
			       	jQuery('#'+current_object.popup_id).html(r);
			       	jQuery(current_object.item_selector).on('click', function(){
									var code = jQuery(this).attr('data-code');
									var i_class = jQuery(this).attr('data-class');
									var the_html = jQuery(this).html();

									jQuery('[name=' + current_object.option_name_code + ']').val(code);
									jQuery('[name=' + current_object.option_name_icon + ']').val(i_class);
									jQuery(current_object.selector).html(the_html);
									removePopup();
			       	});
					}
			});
	}

	jQuery(current_object.selector).on('click', function(){
			if (!current_object.popup_visible){
					current_object.popup_visible = true;
					loadDataViaAjax();
			} else {
					removePopup();
			}
	});

	jQuery(current_object.second_selector).on('click', function(){
			if (!current_object.popup_visible){
					current_object.popup_visible = true;
					loadDataViaAjax();
			} else {
					removePopup();
			}
	});

	function removePopup(){
			jQuery('#'+current_object.popup_id).empty();
			jQuery('#'+current_object.popup_id).css('display', 'none');
			current_object.popup_visible = false;
	}

}

function ihcMakeExportFile(){
	var u = jQuery('#import_users').val();
	var s = jQuery('#import_settings').val();
	var pm = jQuery('#import_postmeta').val();
	jQuery('#ihc_loading_gif .spinner').css('visibility', 'visible');
	   	jQuery.ajax({
	        type : 'post',
	        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
	        data : {
	                   action: 'ihc_make_export_file',
	                   import_users: u,
	                   import_settings: s,
	                   import_postmeta: pm
	               },
	        success: function (response) {
		        	if (response!=0){
			        		jQuery('.ihc-hidden-download-link a').attr('href', response);
			        		jQuery('.ihc-hidden-download-link').fadeIn(200);
									jQuery('#ihc_loading_gif .spinner').css('visibility', 'hidden');
		        	}
	        }
	   });
}

function ihcAutocompleteWriteTag(value_id, hiddenId, viewDivId, prevDivPrefix, label){
	/*
	 * viewDivId - parent
	 * prevDivPrefix - prefix of tag
	 * hiddenId - where values are
	 */
	var id = prevDivPrefix + value_id;
	jQuery(viewDivId).append('<div id="'+id+'" class="ihc-tag-item">'+label+'<div class="ihc-remove-tag" onclick="ihcRemoveTag(\''+value_id+'\', \'#'+id+'\', \''+hiddenId+'\');" title="Removing tag">x</div></div>');
}

function ihcRemoveTag(removeVal, removeDiv, hiddenId){
		jQuery(removeDiv).fadeOut(200, function(){
			jQuery(this).remove();
		});

    var hidden_i = jQuery(hiddenId).val();
    var show_arr = hidden_i.split(',');

    show_arr = removeArrayElement(removeVal, show_arr);
    var str = show_arr.join(',');
	  jQuery(hiddenId).val(str);
}

function removeArrayElement(elem, arr){
		for ( var i=0; i<arr.length; i++ ) {
		    if ( arr[i] == elem ){
		    		arr.splice(i, 1);
		    }
		}
		return arr;
}

function ihcDoDeleteWooIhcRelation(i, u){
	jQuery.ajax({
			type 		: 'post',
	    url 		: decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
	    data 		: {
			            action		: 'ihc_do_delete_woo_ihc_relation',
			            id				: i,
	    },
	    success	: function (response) {
	    	window.location.href = u;
	    }
	});
}

function ihcChangeSearchWooType(){
	var v = jQuery('#search_woo_type').val();
	switch (v){
		case '-1':
			ihcResetAutocomplete('#product_search_input', '#ihc_reference_search_tags');
			jQuery('#woo_the_search_box').css('display', 'none');
			jQuery('#ihc_woo_all_products').css('display', 'none');
			break;
		case 'all':
			/// al prod
			if (jQuery('#product_search_input').val(-1)!=-1){
				jQuery('#product_search_input').val(-1);
				jQuery('#ihc_woo_all_products').css('display', 'block');
				jQuery('#woo_the_search_box').css('display', 'none');
			}
			break;
		case 'category':
			jQuery('#ihc_woo_all_products').css('display', 'none');
			ihcResetAutocomplete('#product_search_input', '#ihc_reference_search_tags');
			jQuery('#woo_the_search_box').css('display', 'block');
			jQuery('#the_search_label').html(window.search_cats_label);
			break;
		case 'product':
			jQuery('#ihc_woo_all_products').css('display', 'none');
			ihcResetAutocomplete('#product_search_input', '#ihc_reference_search_tags');
			jQuery('#woo_the_search_box').css('display', 'block');
			jQuery('#the_search_label').html(window.search_prod_label);
			break;
	}
}

function ihcResetAutocomplete(hidden, wrapper){
	jQuery(hidden).val('');
	jQuery(wrapper).html('');
}

function ihcRunAjaxProcess(t){
	jQuery('#ihc_ajax_run_process_spinner').css('visibility', 'visible');
	jQuery.ajax({
				type 		: 'post',
	    	url 		: decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
	    	data 		: {
	            action		: 'ihc_run_custom_process',
	            type			: t
	    	},
	    	success : function (response) {
						jQuery('#ihc_ajax_run_process_spinner').css('visibility', 'hidden');
		    }
	});
}

function ihc_split(v){
	if (v.indexOf(',')!=-1){
	    return v.split( /,\s*/ );
	} else if (v!=''){
		return [v];
	}
	return [];
}
function ihc_extract(t) {
    return ihc_split(t).pop();
}

function ihcContains(a, obj) {
    return a.some(function(element){return element == obj;})
}

function ihcInitiateOwl(selector)
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
		owl.owlihcCarousel({
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



jQuery(window).on('load', function(){

	if ( jQuery( '.iump-js-tracking-modal' ).length ){
			setTimeout( function(){
					jQuery( '.iump-js-tracking-modal' ).removeClass( 'ihc-display-none' );
			}, 5000 );
	}

	// tracking modal
	if ( jQuery('#iump-admin-bttn-modal-tracking').length ){
			jQuery('#iump-admin-bttn-modal-tracking').on('click', function(){
					if ( jQuery( '.iump-js-confirm-tracking-checkbox' ).is(':checked') ){
							// fire the ajax call to confirm the tracking
							jQuery.ajax({
											type 			: 'post',
											url 			: decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
											data 			: {
																 action		: 'ihc_admin_confirm_tracking',
											},
											success		: function (response) {
													ihcClosePopup();
											}
							});
					} else {
							// fire the ajax call to refuse the tracking
							jQuery.ajax({
											type 			: 'post',
											url 			: decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
											data 			: {
																 action		: 'ihc_admin_refuse_tracking',
											},
											success		: function (response) {
													ihcClosePopup();
											}
							});
					}
			});
			jQuery(document).mouseup(function(e){
					var container = jQuery( ".ihc-the-popup" );
					if (!container.is(e.target) && container.has(e.target).length === 0){
								/// close modal
								ihcClosePopup();
					}
			});
	}

	// affiliates
	if ( jQuery( '.ihc-js-admin-affiliates-limit' ).length ){
	    jQuery( '.ihc-js-admin-affiliates-limit' ).on( 'change', function(){
					var url = jQuery( this ).attr( 'data-url' );
	       	window.location = url + this.value;
	    });
	}

	if ( jQuery( '.ihc-js-admin-restrict-wp-menu-links' ).length ){
			jQuery( '.ihc-js-admin-restrict-wp-menu-links' ).on( 'change', function(){
					var url = jQuery( this ).attr( 'data-url' );
					window.location = url + this.value;
			});
	}

	// users
	if ( jQuery( '.js-ihc-search-users-limit' ).length ){
			jQuery( '.js-ihc-search-users-limit' ).on( 'change', function(){
					var url = jQuery( this ).attr( 'data-url' );
					window.location = url + this.value;
			});
	}


	// woo product custom prices
	if ( jQuery( '.ihc-js-woo-product-custom-prices-select' ).length ){
	    jQuery( '.ihc-js-woo-product-custom-prices-select' ).on( 'change', function(){
					var url = jQuery( this ).attr( 'data-url' );
					jQuery('#product_search').autocomplete( 'option', { source: url + this.value } );
					ihcChangeSearchWooType();
			});
	}

	// remove top image
	jQuery( '.ihc-js-admin-top-bacgrkound-image-delete' ).on( 'click', function(){
			jQuery('#ihc_ap_top_background_image').val('');
	});

	// remove badge image
	jQuery( '.ihc-js-admin-badge-image-remove' ).on( 'click', function(){
		  var id = jQuery( this ).attr( 'data-id' );
			jQuery( '#badge_image_url' + id ).val('');
			jQuery( '#img_level' + id ).css('display', 'none');
	});

	jQuery( '.ihc-js-admin-block-url-delete-block-url' ).on( 'click', function(){
			var key = jQuery( this ).attr( 'data-key' );
			jQuery( '#delete_block_url' ).val( key );
			jQuery('#block_url_form').submit();
	});

	jQuery( '.ihc-js-admin-block-regex' ).on( 'click', function(){
			var key = jQuery( this ).attr( 'data-key' );
			jQuery( '#delete_block_regex' ).val( key );
			jQuery( '#block_url_form' ).submit();
	});

	jQuery( '.ihc-js-admin-delete-block-url-block' ).on( 'click', function(){
			var key = jQuery( this ).attr( 'data-key' );
			jQuery('#delete_block').val( key );
			jQuery('#delete_block_form').submit();
	});

	jQuery( '.ihc-js-admin-delete-block-url-redirect' ).on( 'click', function(){
			var key = jQuery( this ).attr( 'data-key' );
			jQuery('#delete_block').val( key );
			jQuery('#delete_block_form').submit();
	});

	jQuery( '.ihc-js-delete-block-url-redirect' ).on( 'click', function(){
			var key = jQuery( this ).attr( 'data-key' );
			jQuery('#delete_block').val( key );
			jQuery('#delete_block_form').submit();
	});

	jQuery( '.ihc-js-admin-delete-logo-image' ).on( 'click', function(){
			jQuery('#ihc_wp_login_logo_image').val('');
	});

	jQuery( '.ihc-js-admin-invitation-code-delete-code' ).on( 'click', function(){
			var id = jQuery( this ).attr( 'data-id' );
			jQuery('#delete_code').val( id );
			jQuery('#delete_code_form').submit();
	});

	jQuery( '.ihc-js-admin-invoices-delete' ).on( 'click', function(){
			jQuery('[name=ihc_invoices_logo]').val('');
	});

	jQuery( '.ihc-js-admin-badge-image-do-delete' ).on( 'click', function(){
			jQuery('#badge_image_url').val('');
	});

	jQuery( '.ihc-js-admin-redirect-links-do-delete' ).on( 'click', function(){
			var key = jQuery( this ).attr( 'data-key' );
			jQuery('#delete_redirect_link').val( key );
			jQuery('#redirect_links_form').submit();
	});

	jQuery( '.ihc-js-admin-register-delete-parent' ).on( 'click', function(){
			jQuery(this).parent().remove();
	});

	jQuery( '.ihc-js-admin-levels-delete-product-assign' ).on( 'click', function(){
			jQuery('#iump_change_level_product_relation').css('display', 'block');
			jQuery('#iump_current_product_level').css('display', 'none');
	});

	jQuery( '.ihc-js-listing-users-slide-up' ).on( 'click', function(){
			jQuery('#the_ihc_user_list_settings').slideToggle();
	});
	jQuery( '.ihc-js-listing-users-slide-up-preview' ).on( 'click', function(){
			jQuery('#preview').slideToggle();
	});

	jQuery( '.ihc-js-admin-listing-users-delete-banner-image' ).on( 'click', function(){
			jQuery('#ihc_listing_users_inside_page_banner_href').val('');
	});

	jQuery( '.ihc-js-admin-notifications-delete-notification' ).on( 'click', function(){
			var id = jQuery( this ).attr( 'data-id' );
			jQuery('#delete_notification_by_id').val( id );
			jQuery('#delete_notification').submit();
	});

	// user sites
	if ( jQuery( '.ihc-js-user-sites-table-data' ).length ){
		var ihc_current_url = jQuery( '.ihc-js-user-sites-table-data' ).attr( 'data-current_url' );
		var ihc_current_question  = jQuery( '.ihc-js-user-sites-table-data' ).attr( 'data-current_question' );
	}

	// Listing Members
	if ( jQuery( '.ihc-js-owl-settings-data' ).length ){
			jQuery( '.ihc-js-owl-settings-data' ).each(function( e, html ){
					ihcInitiateOwl( this );
			});
	}

	// member form
	if ( jQuery( '.ihc-js-countries-list-data' ).length ){
			jQuery( jQuery( '.ihc-js-countries-list-data' ).attr( 'data-selector' ) ).select2({
					placeholder					: jQuery( '.ihc-js-countries-list-data' ).attr( 'data-placeholder' ),
					allowClear					: true,
					selectionCssClass		: "ihc-select2-dropdown"
			});
	}

	// member form
	if ( jQuery( '.ihc-js-member-form-datepicker-data' ).length ){
			var currentYear = new Date().getFullYear() + 10;
			jQuery( '.ihc-js-member-form-datepicker-data' ).each( function( e, html ){
					jQuery( jQuery( this ).attr( 'data-selector' ) ).datepicker({
							dateFormat   : "dd-mm-yy",
							changeMonth  : true,
							changeYear   : true,
							yearRange    : "1900:"+currentYear,
							onClose      : function(r) {
										jQuery( this ).attr( 'data-callback' )
							}
					});
			});
	}

	// custom banner
	if ( jQuery( '.ihc-js-custom-banner-data' ).length ){
			jQuery( '.ihc-js-custom-banner-data' ).each( function( e, html ){
					IhcImageUpload.init({
							triggerId					           : jQuery( this ).attr('data-trigger_id'),
							saveImageTarget		           : jQuery( this ).attr('data-url'),
							cropImageTarget              : jQuery( this ).attr('data-url'),
							imageSelectorWrapper         : '.ihc-upload-top-banner-wrapper',
							hiddenInputSelector          : '[name=' + jQuery( this ).attr('data-field_name') + ']',
							imageClass                   : 'ihc-image-photo',
							removeImageSelector          : jQuery( this ).attr('data-remove_image_bttn'),
							buttonId 					           : 'ihc_top_custom_banner_js_bttn',
							buttonLabel 			           : jQuery( this ).attr('data-bttn_label'),
					});
			});
	}

	// upload image
	if ( jQuery( '.ihc-js-admin-upload-image-data' ).length ){
			jQuery( '.ihc-js-admin-upload-image-data' ).each( function(e,html){
					var rand = jQuery( this ).attr( 'data-rand' );
					var url = jQuery( this ).attr( 'data-url' );
					var name = jQuery( this ).attr( 'data-name' );
					var bttn = jQuery( this ).attr( 'data-bttn_label' );
					IhcAvatarCroppic.init({
							triggerId					           : 'js_ihc_trigger_avatar' + rand,
							saveImageTarget		           : url,
							cropImageTarget              : url,
							imageSelectorWrapper         : '.ihc-js-upload-image-wrapp',
							hiddenInputSelector          : '[name='+name+']',
							imageClass                   : 'ihc-member-photo',
							removeImageSelector          : '#ihc_upload_image_remove_bttn_' + rand,
							buttonId 					           : 'ihc-avatar-button',
							buttonLabel 			           : bttn,
					});
			});
	}

	// file upload
	if ( jQuery( '.ihc-js-upload-file-data' ).length ){
			jQuery( '.ihc-js-upload-file-data' ).each( function( e, html ){
					var rand = jQuery( this ).attr( 'data-rand' );
					var alert_text = jQuery( this ).attr( 'data-alert_text' );
					var theUrl = jQuery( this ).attr( 'data-url' );
					var maxSize = jQuery( this ).attr( 'data-max_size' );
					var allowed_types = jQuery( this ).attr( 'data-allowed_types' );
					var fieldName = jQuery( this ).attr( 'data-field_name' );
					jQuery("#ihc_fileuploader_wrapp_" + rand + " .ihc-file-upload").uploadFile({
						onSelect: function (files) {
								jQuery("#ihc_fileuploader_wrapp_" + rand + " .ajax-file-upload-container").css("display", "block");
								var check_value = jQuery("#ihc_upload_hidden_" + rand ).val();
								if (check_value!="" ){
									alert(alert_text);
									return false;
								}
								return true;
						},
						url: theUrl,
						fileName: "ihc_file",
						dragDrop: false,
						showFileCounter: false,
						showProgress: true,
						showFileSize: false,
						maxFileSize: maxSize,
						allowedTypes: allowed_types,
						onSuccess: function(a, response, b, c){
							if (response){
								var obj = jQuery.parseJSON(response);
								if (typeof obj.secret!="undefined"){
										jQuery("#ihc_fileuploader_wrapp_" + rand ).attr("data-h", obj.secret);
								}
								jQuery("#ihc_fileuploader_wrapp_" + rand + " .ihc-file-upload").prepend("<div onClick=\"ihcDeleteFileViaAjax("+obj.id+", -1, '#ihc_fileuploader_wrapp_" + rand + "', '"+fieldName+"', '#ihc_upload_hidden_" + rand + "');\" class=\'ihc-delete-attachment-bttn\'>Remove</div>");
								switch (obj.type){
									case "image":
										jQuery("#ihc_fileuploader_wrapp_" + rand + " .ihc-file-upload").prepend("<img src="+obj.url+" class=\'ihc-member-photo\' /><div class=\'ihc-clear\'></div>");
									break;
									case "other":
										jQuery("#ihc_fileuploader_wrapp_" + rand + " .ihc-file-upload").prepend("<div class=ihc-icon-file-type></div><div class=ihc-file-name-uploaded>"+obj.name+"</div>");
									break;
								}
								jQuery("#ihc_upload_hidden_" + rand ).val(obj.id);
								setTimeout(function(){
									jQuery("#ihc_fileuploader_wrapp_" + rand + " .ajax-file-upload-container").css("display", "none");
								}, 3000);
							}
						}
					});
			});
	}

	jQuery('#direct_link_generate_link').on('click', function(event){
			event.preventDefault();
			jQuery.ajax({
				type : 'post',
					url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
					data : {
									action: 'ihc_generate_direct_link',
									username: jQuery('#direct_login_usernmae').val(),
									expire_time: jQuery('#direct_login_timeout').val()
					},
					success: function (response) {
							jQuery('#direct_link_value').html(response);
					}
			});
	});

	jQuery('.ihc-admin-direct-login-generator').on('click', function(event){
			jQuery.ajax({
				type : 'post',
					url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
					data : {
									action: 'ihc_generate_direct_link_by_uid',
									uid: jQuery(event.target).attr('data-uid'),
					},
					success: function (response) {
							ihcSwal({
								title: "Success",
								text: response,
								type: "success",
								showCancelButton: false,
								confirmButtonText: "OK",
								closeOnConfirm: true
							});
					}
			});
	});

	jQuery('.ihc-direct-login-remove-item').on('click', function(event){
			ihcSwal({
				title: "Are you sure you want to delete this link?",
				text: "",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Delete",
				closeOnConfirm: true
			},
			function(){
				jQuery.ajax({
						type : 'post',
						url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
						data : {
											 action: 'ihc_direct_login_delete_item',
											 uid: jQuery(event.target).attr('data-uid'),
									 },
						success: function (response) {
								location.reload();
						}
			 });
		 });
	});

	jQuery( '.deactivate' ).on( 'click', function(evt){
			if ( jQuery( evt.target ).attr('href').indexOf( 'indeed-membership-pro' ) > -1 ){
					if ( window.ihcKeepData == 1 ){
							var theMessage = 'Plugin data will be kept in database after you delete the plugin.';
					} else {
							var theMessage = 'Plugin data will be lost after you delete the plugin.';
					}
					var target = jQuery( evt.target ).attr('href');
					ihcSwal({
						title: theMessage,
						text: "Go to WP Admin dashboard, select Membership Pro Ultimate WP, click on General Options followed by Admin Workflow in the submenu. Toggle the Keep data after delete plugin: to 'On'.",
						type: "warning",
						showCancelButton: true,
						confirmButtonClass: "btn-danger",
						confirmButtonText: "OK",
						closeOnConfirm: true
					},	function(){
							window.location.href = target;
					});
					return false;
			}
	});

	jQuery( '.ihc-js-close-admin-dashboard-notice' ).on( 'click', function(){
			var parent = jQuery(this).parent();
			var typeOfAction = jQuery( this ).attr( 'data-type' );
			parent.css( 'display', 'none');
			jQuery.ajax({
					type : 'post',
					url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
					data : {
										 action				: 'ihc_close_admin_notice',
										 actionType		: typeOfAction,
								 },
					success: function (response) {
							parent.remove();
					}
		 });
	});

	jQuery( '.ihc-js-close-admin-dashboard-registration-notice' ).on( 'click', function(){
			var parent = jQuery(this).parent();
			parent.fadeOut( 1000 );
			jQuery.ajax({
					type : 'post',
					url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
					data : {
										 action: 'ihc_close_admin_registration_notice'
								 },
					success: function (response) {
							parent.remove();
					}
		 });
	});

	jQuery( '.ihc-js-close-admin-dashboard-mk-notice' ).on( 'click', function(){
			var parent = jQuery(this).parent();
			parent.fadeOut( 1000 );
			jQuery.ajax({
					type : 'post',
					url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
					data : {
										 action: 'ihc_ajax_close_admin_mk_notice',
										 target: jQuery( this ).attr( 'data-name' ),
								 },
					success: function (response) {
							parent.remove();
					}
		 });
	});

	jQuery('.ihc-admin-mobile-bttn').on('click', function(){
		jQuery('.ihc-dashboard-menu-items').toggle();
	});

	jQuery('.ihc-js-membership-card-select-size').on('change', function( e ){
		var size = jQuery( e.target ).val();
		var imageHref = jQuery( '.ihc-membership-card-size-image-example-wrapper' ).attr( 'data-path_to_image' );
		switch ( size ){
				case 'ihc-membership-card-small':
					imageHref += 'membership-card-size-small.png';
					break;
				case 'ihc-membership-card-medium':
					imageHref += 'membership-card-size-medium.png';
					break;
				case 'ihc-membership-card-large':
					imageHref += 'membership-card-size-large.png';
					break;
		}
		jQuery( '.ihc-membership-card-size-image-example-wrapper img').attr( 'src', imageHref );
	});

	jQuery( '.ihc-js-image-type-selector' ).on( 'click', function( e ){
			if ( jQuery( e.target ).val() == 1 ){
					// show
					jQuery( '.ihc-membership-card-settings-image' ).removeClass( 'ihc-display-none' );
			} else {
					// hide
					jQuery( '.ihc-membership-card-settings-image' ).addClass( 'ihc-display-none' );
			}
	});

	jQuery('#ihc-register-fields-table tbody').sortable({
		 update: function(e, ui) {
						jQuery('#ihc-register-fields-table tbody tr').each(function (i, row) {
							var id = jQuery(this).attr('id');
							var newindex = jQuery("#ihc-register-fields-table tbody tr").index(jQuery('#'+id));
							jQuery('#'+id+' .ihc-order').val(newindex);
						});
				}
	});


	jQuery('#ihc-levels-table tbody').sortable({
		 disabled: true,
		 update: function(e, ui) {
				var arr = new Array();
				var i = 0;
				jQuery('#ihc-levels-table tbody tr').each(function (i, row) {
					var id = jQuery(this).attr('id');
					if ( id ){
								var level_id = jQuery('#'+id+' .ihc-hidden-level-id').val();
								arr.push(level_id);
							}
							i++;
					});
					var j = false;
					var j = JSON.stringify(arr);
					if (j){
								jQuery.ajax({
										type : 'post',
										url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
										data : {
															 action			: 'ihc_reorder_levels',
															 json_data	: j,
													 },
										success: function (response) {
												console.log(response);
										}
							 });
					 }
				}
	});

	jQuery('#ihc_reorder_menu_items tbody').sortable({
		 update: function(e, ui) {
						jQuery('#ihc_reorder_menu_items tbody tr').each(function (i, row) {
							var id = jQuery(this).attr('id');
							jQuery('#'+id+' .ihc_account_page_menu_order').val(i);
						});
		 }
	});

	var indeed_shiny_object = new indeedShinySelect({
				selector					: '#indeed_shiny_select',
				item_selector			: '.ihc-font-awesome-popup-item',
				option_name_code	: 'ihc_account_page_menu_add_new-the_icon_code',
				option_name_icon	: 'ihc_account_page_menu_add_new-the_icon_class',
				default_icon			: '',
				default_code			: '',
				init_default			: false,
				second_selector		: '.ihc-icon-arrow'
	});

	var ihcCurrentUrl = window.location.href;
	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=add_edit_taxes' ) !== -1 ){
					// Taxes - add edit
					jQuery("#country_field").select2({
							placeholder		: "Select Your Country",
							allowClear		: true
					});
					return;
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=add_new_order' ) !== -1 ){
			// orders - add new
			jQuery('#created_date_ihc').datepicker({
          dateFormat  : 'yy-mm-dd',
          onSelect		: function(datetext){
              var d = new Date();
              datetext = datetext+" "+d.getHours()+":"+ihcAddZero(d.getMinutes())+":"+ihcAddZero(d.getSeconds());
              jQuery(this).val(datetext);
          }
      });
	}
	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=coupons&subtab=multiple_coupons' ) !== -1 ||
				ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=coupons&subtab=add_edit' ) !== -1
	){
			jQuery('#ihc_start_time').datepicker({
					dateFormat : 'dd-mm-yy'
			});
			jQuery('#ihc_end_time').datepicker({
					dateFormat : 'dd-mm-yy'
			});
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=general&subtab=public_workflow' ) !== -1 ){
			// general settings - public workflow
			jQuery("[name=ihc_default_country]").select2({
				placeholder: "Select Your Country",
				allowClear: true
			});
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=block_url' ) !== -1 ){

			if ( jQuery( '[name=ihc_save]' ).length > 0 ){
					// post_types, cats, files
					jQuery('[name=ihc_save]').on( 'click', function(){
							if ( jQuery( '#ihc-change-target-user-set-regex' ).length > 0 ){
									if ( jQuery( '[name=target_users]').val() === '' || jQuery( '[name=target_users]').val() == -1 ){
											jQuery( '#ihc-change-target-user-set-regex' ).addClass( 'iump-form-field-notice' );
											jQuery( '#iump_admin_car_target_message' ).html( '<span class="ihc-danger-box">' + jQuery( '#iump_admin_car_target_message').attr( 'data-notice' ) + '</span>' );
											return false;
									} else {
											jQuery( '#ihc-change-target-user-set-regex' ).removeClass( 'iump-form-field-notice' );
											jQuery( '#iump_admin_car_target_message' ).html('');
											return true;
									}
							}
					});
			} else if ( jQuery( '[name=ihc_save_block_url]' ).length > 0 ){
					jQuery('[name=ihc_save_block_url]').on( 'click', function(){
							if ( jQuery( '[name=ihc_block_url_entire-target_users]').length > 0 ){
										// entire_url
										if ( jQuery( '[name=ihc_block_url_entire-target_users]').val() === '' || jQuery( '[name=ihc_block_url_entire-target_users]').val() == -1 ){
												jQuery( '#ihc-change-target-user-set' ).addClass( 'iump-form-field-notice' );
												jQuery( '#iump_admin_car_target_message' ).html( '<span class="ihc-danger-box">' + jQuery( '#iump_admin_car_target_message').attr( 'data-notice' ) + '</span>' );
												return false;
										} else {
												jQuery( '#ihc-change-target-user-set' ).removeClass( 'iump-form-field-notice' );
												jQuery( '#iump_admin_car_target_message' ).html('');
												return true;
										}
							} else if ( jQuery( '[name=ihc_block_url_word-target_users]').length > 0 ){
									// keyword
									if ( jQuery( '[name=ihc_block_url_word-target_users]').val() === '' || jQuery( '[name=ihc_block_url_word-target_users]').val() == -1 ){
												jQuery( '#ihc-change-target-user-set-regex' ).addClass( 'iump-form-field-notice' );
												jQuery( '#iump_admin_car_target_message' ).html( '<span class="ihc-danger-box">' + jQuery( '#iump_admin_car_target_message').attr( 'data-notice' ) + '</span>' );
												return false;
									} else {
												jQuery( '#ihc-change-target-user-set-regex' ).removeClass( 'iump-form-field-notice' );
												jQuery( '#iump_admin_car_target_message' ).html('');
												return true;
									}
							}
					});
			}
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=general&subtab=captcha' ) !== -1 ){
			// general settings
			jQuery( '.js-ihc-change-recaptcha-version' ).on( 'change', function( evt ){
					if ( this.value == 'v2' ){
							jQuery( '.js-ihc-recaptcha-v2-wrapp' ).css( 'display', 'block' );
							jQuery( '.js-ihc-recaptcha-v3-wrapp' ).css( 'display', 'none' );
					} else {
							jQuery( '.js-ihc-recaptcha-v2-wrapp' ).css( 'display', 'none' );
							jQuery( '.js-ihc-recaptcha-v3-wrapp' ).css( 'display', 'block' );
					}
			});
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=levels' ) !== -1 ){
			// levels
			ihcUpdateLevelSlugSpan();
			// delete levels
			jQuery( '.ihc-js-delete-level' ).on( 'click', function(){
					var levelId = jQuery( this ).attr( 'data-id' );
					ihcSwal({
						title: jQuery('.ihc-js-admin-messages').attr('data-delete_level'),
						text: "",
						type: "warning",
						showCancelButton: true,
						confirmButtonClass: "btn-danger",
						confirmButtonText: "OK",
						closeOnConfirm: true
					},
					function(){
							jQuery.ajax({
									type 			: 'post',
									url 			: decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
									data 			: {
														 action		: 'ihc_admin_delete_level',
														 lid			: levelId,
									},
									success		: function (response) {
										  location.reload();
									}
						 });
				 });
			});
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=account_page_menu' ) !== -1 ){
			jQuery( '.ihc-js-account-page-menu-item-delete-action' ).on( 'click', function(){
					var targetSlug = jQuery( this ).attr('data-slug');
					jQuery.ajax({
								type : 'post',
								url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
								data : {
													 action: 'ihc_admin_delete_account_page_menu_item',
													 slug:			targetSlug,
											 },
								success: function (response) {
										location.reload();
								}
					 });
			});
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=levels&edit_level' ) !== -1 ||
 				ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=levels&new_level=true' ) !== -1
	){
			// levels - add/edit
			jQuery('#access_interval_start').datepicker({
					dateFormat : 'dd-mm-yy'
			});
			jQuery('#access_interval_end').datepicker({
					dateFormat : 'dd-mm-yy'
			});

			/// REFERENCE SEARCH
			jQuery( "#reference_search" ).on( "keydown", function(event){
				if ( event.keyCode === jQuery.ui.keyCode.TAB &&
						jQuery(this).autocomplete( "instance" ).menu.active){
						event.preventDefault();
				}
			}).autocomplete({
				focus				: function( event, ui ){},
				minLength		: 0,
				source			: decodeURI( window.ihc_site_url ) + '/wp-admin/admin-ajax.php?action=ihc_ajax_custom_admin_ajax_gate&ihcAdminAjaxNonce=' + window.ihcAdminAjaxNonce,
				select			: function( event, ui ) {
						jQuery('[name=new_woo_product]').val(ui.item.id);
						jQuery('#reference_search').val(ui.item.label);
						return false;
				}
			});

	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=order-edit' ) !== -1 ){
			jQuery('#created_date_ihc').datepicker({
					dateFormat 		: 'yy-mm-dd',
					onSelect			: function(datetext){
							var d = new Date();
							datetext = datetext+" "+d.getHours()+":"+ihcAddZero(d.getMinutes())+":"+ihcAddZero(d.getSeconds());
							jQuery(this).val(datetext);
					}
			});
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=orders' ) !== -1 ){


		jQuery( '.ihc-js-delete-payment-transaction' ).on( 'click', function(){
				var transactionId = jQuery( this ).attr( 'data-id' );
				ihcSwal({
						title								: jQuery( '.ihc-js-admin-messages' ).attr( 'data-delete_transaction' ),
						text								: "",
						type								: "warning",
						showCancelButton		: true,
						confirmButtonClass	: "btn-danger",
						confirmButtonText		: "OK",
						closeOnConfirm			: true
				},
				function(){
						jQuery.ajax({
								type : 'post',
								url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
								data : {
													 action: 'ihc_admin_delete_payment_transaction',
													 id:			transactionId,
											 },
								success: function (response) {
										location.reload();
								}
					 });
			 });
		});

		jQuery( '.ihc-js-delete-order' ).on( 'click', function(){
				var orderId = jQuery( this ).attr( 'data-id' );
				ihcSwal({
						title									: jQuery( '.ihc-js-admin-messages' ).attr( 'data-delete_order' ),
						text									: "",
						type									: "warning",
						showCancelButton			: true,
						confirmButtonClass		: "btn-danger",
						confirmButtonText			: "OK",
						closeOnConfirm				: true
				},
				function(){
						jQuery.ajax({
								type 				: 'post',
								url 				: decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
								data 				: {
													 action: 'ihc_admin_delete_order',
													 id:			orderId,
								},
								success			: function (response) {
										location.reload();
								}
					 });
			 });
		});

		jQuery( '.ihc-js-make-order-completed' ).on( 'click', function(){
				var orderId = jQuery( this ).attr( 'data-id' );
				jQuery.ajax({
						type : 'post',
						url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
						data : {
											 action: 'ihc_admin_make_order_completed',
											 id:			orderId,
									 },
						success: function (response) {
								location.reload();
						}
			 });
		});

		jQuery('#ihc_orders_filter_start_time').datepicker({
				dateFormat : 'dd-mm-yy'
		});
		jQuery('#ihc_orders_filter_end_time').datepicker({
				dateFormat : 'dd-mm-yy'
		});

	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=listing_users' ) !== -1 ){
			// Members Directory
			ihcPreviewUList();
	}

	if ( ihcCurrentUrl.indexOf( 'page=ihc_manage&tab=locker&subtab=add_new' ) !== -1 ){
			// Locker - add/edit
			ihcLockerPreview();
			jQuery(document).on('click', '#ihc_locker_custom_content-html', function() {
					jQuery('#ihc-update-bttn-show-edit').css('display', 'none');
			});
			jQuery(document).on('click', '#ihc_locker_custom_content-tmce', function() {
					jQuery('#ihc-update-bttn-show-edit').css('display', 'block');
			});

			var display = jQuery('#ihc_locker_custom_content').css('display');
			if ( display == 'none' ){
					jQuery('#ihc-update-bttn-show-edit').css('display', 'block');
			}
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=locker' ) !== -1 ){
			// Locker - list all
			jQuery( '.ihc-js-admin-delete-locker' ).on( 'click', function(){
					var lockerId = jQuery( this ).attr( 'data-id' );
					ihcSwal({
						title: jQuery( '.ihc-js-admin-messages' ).attr( 'data-delete_item' ),
						text: "",
						type: "warning",
						showCancelButton: true,
						confirmButtonClass: "btn-danger",
						confirmButtonText: "OK",
						closeOnConfirm: true
					},
					function(){
							jQuery.ajax({
									type : 'post',
									url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
									data : {
														 action: 'ihc_admin_delete_locker',
														 id:			lockerId,
												 },
									success: function (response) {
											location.reload();
									}
						 });
				 });
			});
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=login' ) !== -1 ){
			// Login
			ihcLoginPreview();
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=notifications&edit_notification=' ) !== -1 ||
				ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=notifications&add_notification=true' ) !== -1
 	){
		// notifications - add/edit
		jQuery( '.ump-js-change-notification-type' ).on( 'change', function( e ){
				ihcChangeNotificationTemplate();
				ihcNotificationLevelOnlyFor();
				jQuery( '.ump-js-list-constants' ).html('');
				jQuery.ajax({
						type 				: "post",
						url 				: decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
						data 				: {
											 action							: "ihc_update_list_notification_constants",
											 notificationType		: jQuery(e.target).val(),
						},
						success			: function (data) {
								jQuery( '.ump-js-list-constants' ).html( data );
								if ( jQuery( '.iump-tag-code' ).length ){
										jQuery( '.iump-tag-code' ).on('click', function(){
												var text  =jQuery(this).html();
												if ( jQuery(this).attr('data-target_selector') ){
														var textarea = document.getElementById( jQuery(this).attr('data-target_selector') );
												} else {
														var textarea = document.getElementById('ihc_message');
												}

												var position = textarea.selectionStart;
												var before = textarea.value.substring(0, position);
												var after = textarea.value.substring(position, textarea.value.length);
												textarea.value = before + text + after;
												textarea.selectionStart = textarea.selectionEnd = position + text.length;
										});
								}
						}
			 });
		});


	}

	// constants for wp editor
	if ( jQuery( '.iump-tag-code' ).length ){
			jQuery( '.iump-tag-code' ).on('click', function(){
					var text  =jQuery(this).html();
					if ( jQuery(this).attr('data-target_selector') ){
							var textarea = document.getElementById( jQuery(this).attr('data-target_selector') );
					} else {
							var textarea = document.getElementById('ihc_message');
					}

					var position = textarea.selectionStart;
					var before = textarea.value.substring(0, position);
					var after = textarea.value.substring(position, textarea.value.length);
					textarea.value = before + text + after;
					textarea.selectionStart = textarea.selectionEnd = position + text.length;
			});
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=notifications&add_notification=true' ) !== -1 ){
			// notifications - add
			ihcChangeNotificationTemplate();
	}
	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=notifications' ) !== -1 ){
			// notifications - list
				ihcNotificationLevelOnlyFor();

				if ( jQuery( '.ihc-js-notifications-fire-notification-test' ).length > 0 ){
						jQuery( '.ihc-js-notifications-fire-notification-test' ).on( 'click', function(){
								var notificationId = jQuery( this ).attr( 'data-notification_id' );
								jQuery.ajax({
										type 			: 'post',
										url 			: decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
										data 			: {
															 action : 'ihc_ajax_notification_send_test_email',
															 id			: notificationId
										},
										success		: function (data) {
												jQuery(data).hide().appendTo('body').fadeIn('normal');
										}
							 });
						});
				}
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=register' ) !== -1 ){
			// register
			jQuery('.ihc-register-the-values').sortable({
				cursor: 'move'
			});
			ihcRegisterLockerPreview();

			jQuery( '.ihc-js-delete-register-field' ).on( 'click', function(){
					var fieldId = jQuery( this ).attr( 'data-id' );
					ihcSwal({
						title									: jQuery( '.ihc-js-admin-messages' ).attr( 'data-delete_item' ),
						text									: "",
						type									: "warning",
						showCancelButton			: true,
						confirmButtonClass		: "btn-danger",
						confirmButtonText			: "OK",
						closeOnConfirm				: true
					},
					function(){
							jQuery.ajax({
									type 			: 'post',
									url 			: decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
									data 			: {
														 action: 'ihc_admin_delete_register_field',
														 id:			fieldId,
									},
									success		: function (response) {
											location.reload();
									}
						 });
				 });
			});
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=subscription_plan' ) !== -1 ){
			// Subscription Plan
			ihcPreviewSelectLevels();
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=user-details' ) !== -1 ){
			// User Details

			var ihcPrintThisOptions = {
						importCSS: true,
			      importStyle: true,
			      loadCSS: window.ihc_plugin_url + 'admin/assets/css/style.css',
			      debug: false,
			      printContainer: true,
			      pageTitle: "",
			      removeInline: false,
			      printDelay: 333,
			      header: null,
			      formValues: false,
			      base: true
			};

			// print page
			jQuery(".ihc-js-print-page").on("click", function(e){
					jQuery( "#ihc_js_print_this" ).printThis( ihcPrintThisOptions );
			});

			// show more details about member
			jQuery( '.ihc-js-show-more' ).on( 'click', function( e ){
				var show = jQuery( '.ihc-js-user-extra-fields' ).css( 'display' );
				if ( show == 'block' ){
						jQuery( '.ihc-js-user-extra-fields' ).css( 'display', 'none' );
						jQuery( '.ihc-js-show-more' ).html( jQuery('.ihc-js-admin-messages').attr('data-show_more') );
				} else {
						jQuery( '.ihc-js-user-extra-fields' ).css( 'display', 'block' );
						jQuery( '.ihc-js-show-more' ).html( jQuery('.ihc-js-admin-messages').attr('data-show_less') );
				}
			});
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=users&ihc-edit-user' ) !== -1 ){
			jQuery('.start_input_text').datepicker({
					dateFormat : 'yy-mm-dd',
					onSelect: function(datetext){
							var d = new Date();
							datetext = datetext+" "+d.getHours()+":"+ihcAddZero(d.getMinutes())+":"+ihcAddZero(d.getSeconds());
							jQuery(this).val(datetext);
					}
			});
			jQuery('.expire_input_text').datepicker({
					dateFormat : 'yy-mm-dd',
					onSelect: function(datetext){
							if ( datetext == '' || datetext == null ){
								jQuery( '#' + jQuery(this).parent().parent().attr('id') + ' .ihc-level-status' ).html( jQuery( '.ihc-js-admin-messages' ).attr( 'data-hold' ) ).attr( 'class', '' ).attr( 'class', 'ihc-level-status ihc-level-status-Expired');
							}
							var d = new Date();
							datetext = datetext+" "+d.getHours()+":"+ihcAddZero(d.getMinutes())+":"+ihcAddZero(d.getSeconds());
							jQuery(this).val(datetext);
							var currentTimestamp = ( new Date().getTime()/1000 );
							var selectedTimestamp = (new Date(datetext).getTime() / 1000 );
							if ( currentTimestamp > selectedTimestamp ){
									jQuery( '#' + jQuery(this).parent().parent().attr('id') + ' .ihc-level-status' ).html( jQuery( '.ihc-js-admin-messages' ).attr( 'data-expired' ) ).attr( 'class', '' ).attr( 'class', 'ihc-level-status ihc-level-status-Expired');
							} else {
									jQuery( '#' + jQuery(this).parent().parent().attr('id') + ' .ihc-level-status' ).html( jQuery( '.ihc-js-admin-messages' ).attr( 'data-active' ) ).attr( 'class', '' ).attr( 'class', 'ihc-level-status ihc-level-status-Active');
							}

					}
			});

	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=users' ) !== -1 ){
			if ( jQuery( '.ihc-js-users-list-users-spent-values' ).length ){
					jQuery.ajax({
							type : "post",
							url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
							data : {
												 action			: "ihc_admin_list_users_total_spent_values",
												 users   		: jQuery( '.ihc-js-users-list-users-spent-values' ).attr( 'data-value' ),
										 },
							success: function (data) {
									if ( data == '' ){
											return false;
									}
									var jsonObject = JSON.parse( data );
									Object.keys( jsonObject ).forEach( function (key){
											document.getElementById('ihc_js_total_spent_for_'+key).innerHTML = jsonObject[key];
									});
							}
				 });
			}

	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=woo_product_custom_prices&subtab=add_edit' ) !== -1 ){
			var search_cats_label = jQuery( '.ihc-js-admin-messages' ).attr( 'data-search_cats' );
			var search_prod_label = jQuery( '.ihc-js-admin-messages' ).attr( 'data-search_products' );
			var ihc_woo_search_type = jQuery('#search_woo_type').val();

			var ihc_from = jQuery('#start_date_input').datepicker({
					dateFormat : 'yy-mm-dd'
			})
			.on( "change", function() {
					ihc_to.datepicker( "option", "minDate", this.value );
			});
			var ihc_to = jQuery('#end_date_input').datepicker({
					dateFormat : 'yy-mm-dd'
			})
			.on( "change", function() {
					ihc_from.datepicker( "option", "maxDate", this.value );
			});

			/// REFERENCE SEARCH
		jQuery( "#product_search" ).on( "keydown", function(event){
			if ( event.keyCode === jQuery.ui.keyCode.TAB &&
				jQuery(this).autocomplete( "instance" ).menu.active){
				event.preventDefault();
			}
		}).autocomplete({
			focus: function( event, ui ){},
			minLength: 0,
			source: decodeURI(window.ihc_site_url) + '/wp-admin/admin-ajax.php?action=ihc_ajax_custom_admin_ajax_gate&ihcAdminAjaxNonce=' + window.ihcAdminAjaxNonce + '&woo_type=' + ihc_woo_search_type,
			select: function( event, ui ) {
				var input_id = '#product_search_input';
				var terms = ihc_split(jQuery(input_id).val());//get items from input hidden
				var v = ui.item.id;
				var l = ui.item.label;
				if (!ihcContains(terms, v)){
					terms.push(v);
					ihcAutocompleteWriteTag(v, input_id, '#ihc_reference_search_tags', 'ihc_reference_tag_', l);// print the new shiny box
				}
				var str_value = terms.join( "," );
				jQuery(input_id).val(str_value);//send to input hidden
				this.value = '';//reset search input
				return false;
			}
		});
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=account_page' ) !== -1 ){
			// account page
				ihcApMakeVisible('overview', '#ihc_tab-overview');
				var i = 0;
				var ihc_shiny_object = [];
				jQuery( '.ihc-js-data-for-indeed-shinny-select' ).each( function( e, html ){

						var type = jQuery( this ).attr( 'data-type' );
						var value = jQuery( this ).attr( 'data-value' );

						ihc_shiny_object[i] = new indeedShinySelect({
										selector: '#indeed_shiny_select_' + type,
										item_selector: '.ihc-font-awesome-popup-item',
										option_name_code: 'ihc_ap_' + type + '_icon_code',
										option_name_icon: 'ihc_ap_' + type + '_icon_class',
										default_icon: 'fa-ihc fa-' + type + '-account-ihc',
										default_code: value,
										init_default: true,
										second_selector: '#ihc_icon_arrow_' + type
							});
						i++;
				});
	}

	if ( ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=dashboard' ) !== -1 )
			|| ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage' ) !== -1 && ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab' ) === -1 )
	){
			// dashboard
			if ( jQuery( '.ihc-js-dashboard-earnings-data' ).length ){
					var i = 0;
					var ihc_ticks = [];
					var ihc_chart_stats = [];
					jQuery( '.ihc-js-dashboard-earnings-data' ).each( function( e, hmtl ){
							var date = jQuery( this ).attr('data-date');
							var sum = jQuery( this ).attr('data-sum');
							ihc_ticks[i] = [ i, date];
							ihc_chart_stats[i] = { 0: i, 1: sum };
							i++;
					});

					var options = {
							lines: { show: true, fill: '#7ebffc', fillColor: "rgba(32, 211, 129, 0.1)", lineWidth:2},
							points: {radius: 2,show: true, fillColor: "rgb(32, 211, 129)"},
							grid: { hoverable: false, backgroundColor: "#fff", minBorderMargin: 0,  borderWidth: {top: 0, right: 0, bottom: 1, left: 1}, borderColor: "#ddd", color: "#ddd" },
							xaxis: {
												show: true,
												ticks: ihc_ticks,
												tickLength: 1,
												color:"#9fa6b2",
												position: "bottom",
												reserveSpace : true
							},
							yaxis: { tickDecimals: 0, tickColor: "#f4f5f7", color:"#9fa6b2"},
							legend: {
									show: true,
									position: "ne",
									}
						};
						jQuery.plot( jQuery( "#ihc-chart-earnings" ), [ {
								color: "rgb(32, 211, 129)",
								data: ihc_chart_stats,
								shadowSize: 0
							} ], options
						);
			}
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=help' ) !== -1 ){
			// help
			jQuery( '[name=ihc_save_licensing_code]' ).on( 'click', function(){
					jQuery.ajax({
							type : "post",
							url : decodeURI( window.ihc_site_url ) + '/wp-admin/admin-ajax.php',
							data : {
												 action						: "ihc_el_check_get_url_ajax",
												 s 								: jQuery( '[name=pv2]' ).val(),
												 nonce						: jQuery( '.ihc-js-help-page-data' ).attr( 'data-nonce' ),
												 n                : jQuery( '[name=cn]' ).val(),
												 v                : jQuery( '[name=ls]' ).val(),
										 },
							success: function (data) {
									if ( data ){
											window.location.href = data;
									} else {
											alert( jQuery( '.ihc-js-help-page-data' ).attr( 'data-help' ) );
									}
							}
				  });
					return false;
			});

			jQuery( '.ihc-js-revoke-license' ).on( 'click', function(){
					jQuery.ajax({
								type : "post",
								url : decodeURI( window.ihc_site_url ) + '/wp-admin/admin-ajax.php',
								data : {
												 action						: "ihc_revoke_license",
												 nonce						: jQuery( '.ihc-js-help-page-data' ).attr( 'data-nonce' ),
								},
								success: function (data) {
										window.location.href = jQuery( '.ihc-js-help-page-data' ).attr( 'data-revoke_url' );
								}
					});
			});
	}

	if ( ihcCurrentUrl.indexOf( 'admin.php?page=ihc_manage&tab=invoices' ) !== -1 ){
			iumpAdminPreviewInvoice();
	}

	if ( jQuery('.ihc-js-deauth-from-stripe-checkout-bttn').length > 0 ){
			//
			jQuery( '.ihc-js-deauth-from-stripe-checkout-bttn' ).on( 'click', function(){
					// make the request
					jQuery.ajax({
								type : "post",
								url : decodeURI( window.ihc_site_url ) + '/wp-admin/admin-ajax.php',
								data : {
												 action						: "ihc_ajax_deauth_from_stripe_connect",
												 url						  : jQuery( this ).attr('data-url'),
								},
								success: function (response) {
										var responseObject = jQuery.parseJSON(response);
										if ( responseObject.code === 200 ){
												window.location.href = jQuery( '.ihc-js-deauth-from-stripe-checkout-bttn' ).attr('data-refresh_url');
										} else {
												window.location.href = jQuery( '.ihc-js-deauth-from-stripe-checkout-bttn' ).attr('data-refresh_url') + '&error_deauth=1';
										}
								}
					});

			});
	}

	if ( ihcCurrentUrl.indexOf( 'page=ihc_manage&tab=opt_in' ) !== -1 ){
			jQuery( '.iump-js-admin-optin-cc-do-auth' ).on( 'click', function(){
					jQuery('#iump_admin_cc_message_box').attr( 'class', '').html('');
					jQuery.ajax({
								type : "post",
								url : decodeURI( window.ihc_site_url ) + '/wp-admin/admin-ajax.php',
								data : {
												 action						: "ihc_admin_get_cc_auth_uri",
												 client_id        : jQuery('#ihc_cc_email_service_client_id').val(),
												 client_secret    : jQuery('#ihc_cc_email_service_client_secret').val(),
								},
								success: function (response) {
										var responseObject = jQuery.parseJSON(response);
										if ( responseObject.status === 1 ){
												// everything ok
												window.location.href = responseObject.redirect_uri;
										} else {
												// something went wrong
												jQuery('#iump_admin_cc_message_box').attr('class', 'ihc-error-global-dashboard-message').html( responseObject.message );
										}
								}
					});
			});
			jQuery( '.iump-js-admin-optin-cc-do-reset' ).on( 'click', function(){
					jQuery.ajax({
								type : "post",
								url : decodeURI( window.ihc_site_url ) + '/wp-admin/admin-ajax.php',
								data : {
												 action						: "ihc_admin_reset_cc",
								},
								success: function (response) {
										var responseObject = jQuery.parseJSON(response);
										if ( responseObject.status === 1 ){
												// everything ok
												location.reload();
										} else {
												// something went wrong
										}
								}
					});
			});
	}


	if ( ihcCurrentUrl.indexOf( 'page=ihc_manage&tab=prorate_add_edit' ) !== -1 ){
			jQuery( '.ihc-js-prorate-add-new-level-to-group-bttn' ).on( 'click', function(){
					var number = jQuery( '.ihc-prorate-memberships-groups' ).children().length;
					number = parseInt( number ) + 1;
					jQuery( '.ihc-js-memberships-hidden .ihc-prorate-memberhip-item-no' ).html( number + '.' );
					jQuery( '.ihc-js-memberships-hidden .ihc-prorate-group-item' ).attr( 'id', 'ihc_prorate_membership_no_' + number );
					jQuery( '.ihc-prorate-memberships-groups' ).append( jQuery( '.ihc-js-memberships-hidden' ).html() );

					jQuery( '.ihc-js-prorate-remove-entry' ).on( 'click', function(){
							jQuery( this ).parent().remove();
							var i = 1;
							jQuery( '.ihc-prorate-memberships-groups .ihc-prorate-group-item' ).each(function( index ){
									var selector = jQuery( this ).attr('id');
									jQuery( '#' + selector + ' .ihc-prorate-memberhip-item-no').html( i + '.' );
									i++;
							});
					});

			});

			jQuery( '.ihc-js-prorate-remove-entry' ).on( 'click', function(){
					jQuery( this ).parent().remove();
					var i = 1;
					jQuery( '.ihc-prorate-memberships-groups .ihc-prorate-group-item' ).each(function( index ){
							var selector = jQuery( this ).attr('id');
							jQuery( '#' + selector + ' .ihc-prorate-memberhip-item-no').html( i + '.' );
							i++;
					});
			});

			jQuery('.ihc-prorate-memberships-groups').sortable({
				 update: function(e, ui) {
								 var i = 1;
			 					jQuery( '.ihc-prorate-memberships-groups .ihc-prorate-group-item' ).each(function( index ){
			 							var selector = jQuery( this ).attr('id');
			 							jQuery( '#' + selector + ' .ihc-prorate-memberhip-item-no').html( i + '.' );
			 							i++;
			 					});
						}
			});
	}

	if ( ihcCurrentUrl.indexOf( 'page=ihc_manage&tab=prorate_subscription' ) !== -1 ){
			jQuery( '.ihc-js-prorate-delete-group' ).on( 'click', function(){
					jQuery.ajax({
								type : "post",
								url : decodeURI( window.ihc_site_url ) + '/wp-admin/admin-ajax.php',
								data : {
												 action						: "ihc_ajax_prorate_delete_group",
												 group_id					: jQuery(this).attr('data-id')
								},
								success: function (data) {
										location.reload();
								}
					});
			});
	}

	if( jQuery('#ihc_locker_custom_css').length ) {
				jQuery.extend(wp.codeEditor.defaultSettings, {
						"codemirror": {
								"theme"             : "cobalt",
								"direction"         : "ltr",
								"mode"              : "text/css",
								"indentUnit"        : 4,
								"styleActiveLine"   : true,
								"indentWithTabs"    : true,
								"lineWrapping"      : true,
								"lineNumbers"       : true,
								"continueComments"  : true,
								"matchBrackets"     : true,
								"autoCloseBrackets" : true,
								"lint"              : false,
								"inputStyle"        : "contenteditable",
								"gutters"           : ["CodeMirror-lint-markers"],
								"extraKeys"         : {
											"Alt-F"   : "findPersistent",
											"Cmd-F"   : "findPersistent",
											"Ctrl-F"  : "findPersistent",
								},
						},
				});
			 var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_locker_custom_css') );
	 }

	 if( jQuery('#ihc_membership_card_custom_css').length ) {
				 jQuery.extend(wp.codeEditor.defaultSettings, {
						 "codemirror": {
								 "theme"             : "cobalt",
								 "direction"         : "ltr",
								 "mode"              : "text/css",
								 "indentUnit"        : 4,
								 "styleActiveLine"   : true,
								 "indentWithTabs"    : true,
								 "lineWrapping"      : true,
								 "lineNumbers"       : true,
								 "continueComments"  : true,
								 "matchBrackets"     : true,
								 "autoCloseBrackets" : true,
								 "lint"              : false,
								 "inputStyle"        : "contenteditable",
								 "gutters"           : ["CodeMirror-lint-markers"],
								 "extraKeys"         : {
											 "Alt-F"   : "findPersistent",
											 "Cmd-F"   : "findPersistent",
											 "Ctrl-F"  : "findPersistent",
								 },
						 },
				 });
				var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_membership_card_custom_css') );
		}

		if( jQuery('#ihc_register_lite_custom_css').length ) {
					jQuery.extend(wp.codeEditor.defaultSettings, {
							"codemirror": {
									"theme"             : "cobalt",
									"direction"         : "ltr",
									"mode"              : "text/css",
									"indentUnit"        : 4,
									"styleActiveLine"   : true,
									"indentWithTabs"    : true,
									"lineWrapping"      : true,
									"lineNumbers"       : true,
									"continueComments"  : true,
									"matchBrackets"     : true,
									"autoCloseBrackets" : true,
									"lint"              : false,
									"inputStyle"        : "contenteditable",
									"gutters"           : ["CodeMirror-lint-markers"],
									"extraKeys"         : {
												"Alt-F"   : "findPersistent",
												"Cmd-F"   : "findPersistent",
												"Ctrl-F"  : "findPersistent",
									},
							},
					});
				 var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_register_lite_custom_css') );
		 }

		 if( jQuery('#ihc_account_page_custom_css').length ) {
					 jQuery.extend(wp.codeEditor.defaultSettings, {
							 "codemirror": {
									 "theme"             : "cobalt",
									 "direction"         : "ltr",
									 "mode"              : "text/css",
									 "indentUnit"        : 4,
									 "styleActiveLine"   : true,
									 "indentWithTabs"    : true,
									 "lineWrapping"      : true,
									 "lineNumbers"       : true,
									 "continueComments"  : true,
									 "matchBrackets"     : true,
									 "autoCloseBrackets" : true,
									 "lint"              : false,
									 "inputStyle"        : "contenteditable",
									 "gutters"           : ["CodeMirror-lint-markers"],
									 "extraKeys"         : {
												 "Alt-F"   : "findPersistent",
												 "Cmd-F"   : "findPersistent",
												 "Ctrl-F"  : "findPersistent",
									 },
							 },
					 });
					var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_account_page_custom_css') );
			}

			if( jQuery('#ihc_listing_users_custom_css').length ) {
						jQuery.extend(wp.codeEditor.defaultSettings, {
								"codemirror": {
										"theme"             : "cobalt",
										"direction"         : "ltr",
										"mode"              : "text/css",
										"indentUnit"        : 4,
										"styleActiveLine"   : true,
										"indentWithTabs"    : true,
										"lineWrapping"      : true,
										"lineNumbers"       : true,
										"continueComments"  : true,
										"matchBrackets"     : true,
										"autoCloseBrackets" : true,
										"lint"              : false,
										"inputStyle"        : "contenteditable",
										"gutters"           : ["CodeMirror-lint-markers"],
										"extraKeys"         : {
													"Alt-F"   : "findPersistent",
													"Cmd-F"   : "findPersistent",
													"Ctrl-F"  : "findPersistent",
										},
								},
						});
					 var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_listing_users_custom_css') );
			 }

			 if( jQuery('#ihc_login_custom_css').length ) {
 						jQuery.extend(wp.codeEditor.defaultSettings, {
 								"codemirror": {
 										"theme"             : "cobalt",
 										"direction"         : "ltr",
 										"mode"              : "text/css",
 										"indentUnit"        : 4,
 										"styleActiveLine"   : true,
 										"indentWithTabs"    : true,
 										"lineWrapping"      : true,
 										"lineNumbers"       : true,
 										"continueComments"  : true,
 										"matchBrackets"     : true,
 										"autoCloseBrackets" : true,
 										"lint"              : false,
 										"inputStyle"        : "contenteditable",
 										"gutters"           : ["CodeMirror-lint-markers"],
 										"extraKeys"         : {
 													"Alt-F"   : "findPersistent",
 													"Cmd-F"   : "findPersistent",
 													"Ctrl-F"  : "findPersistent",
 										},
 								},
 						});
 					 var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_login_custom_css') );
 			 }

			 if( jQuery('#ihc_thank_you_custom_css').length ) {
 						jQuery.extend(wp.codeEditor.defaultSettings, {
 								"codemirror": {
 										"theme"             : "cobalt",
 										"direction"         : "ltr",
 										"mode"              : "text/css",
 										"indentUnit"        : 4,
 										"styleActiveLine"   : true,
 										"indentWithTabs"    : true,
 										"lineWrapping"      : true,
 										"lineNumbers"       : true,
 										"continueComments"  : true,
 										"matchBrackets"     : true,
 										"autoCloseBrackets" : true,
 										"lint"              : false,
 										"inputStyle"        : "contenteditable",
 										"gutters"           : ["CodeMirror-lint-markers"],
 										"extraKeys"         : {
 													"Alt-F"   : "findPersistent",
 													"Cmd-F"   : "findPersistent",
 													"Ctrl-F"  : "findPersistent",
 										},
 								},
 						});
 					 var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_thank_you_custom_css') );
 			 }

			 if( jQuery('#ihc_sm_custom_css').length ) {
 						jQuery.extend(wp.codeEditor.defaultSettings, {
 								"codemirror": {
 										"theme"             : "cobalt",
 										"direction"         : "ltr",
 										"mode"              : "text/css",
 										"indentUnit"        : 4,
 										"styleActiveLine"   : true,
 										"indentWithTabs"    : true,
 										"lineWrapping"      : true,
 										"lineNumbers"       : true,
 										"continueComments"  : true,
 										"matchBrackets"     : true,
 										"autoCloseBrackets" : true,
 										"lint"              : false,
 										"inputStyle"        : "contenteditable",
 										"gutters"           : ["CodeMirror-lint-markers"],
 										"extraKeys"         : {
 													"Alt-F"   : "findPersistent",
 													"Cmd-F"   : "findPersistent",
 													"Ctrl-F"  : "findPersistent",
 										},
 								},
 						});
 					 var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_sm_custom_css') );
 			 }

			 if( jQuery('#ihc_register_custom_css').length ) {
				 jQuery.extend(wp.codeEditor.defaultSettings, {
						 "codemirror": {
								 "theme"             : "cobalt",
								 "direction"         : "ltr",
								 "mode"              : "text/css",
								 "indentUnit"        : 4,
								 "styleActiveLine"   : true,
								 "indentWithTabs"    : true,
								 "lineWrapping"      : true,
								 "lineNumbers"       : true,
								 "continueComments"  : true,
								 "matchBrackets"     : true,
								 "autoCloseBrackets" : true,
								 "lint"              : false,
								 "inputStyle"        : "contenteditable",
								 "gutters"           : ["CodeMirror-lint-markers"],
								 "extraKeys"         : {
											 "Alt-F"   : "findPersistent",
											 "Cmd-F"   : "findPersistent",
											 "Ctrl-F"  : "findPersistent",
								 },
						 },
				 });
						 var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_register_custom_css') );
				 }


				 if( jQuery('#ihc_select_level_custom_css').length ) {
								jQuery.extend(wp.codeEditor.defaultSettings, {
										"codemirror": {
												"theme"             : "cobalt",
												"direction"         : "ltr",
												"mode"              : "text/css",
												"indentUnit"        : 4,
												"styleActiveLine"   : true,
												"indentWithTabs"    : true,
												"lineWrapping"      : true,
												"lineNumbers"       : true,
												"continueComments"  : true,
												"matchBrackets"     : true,
												"autoCloseBrackets" : true,
												"lint"              : false,
												"inputStyle"        : "contenteditable",
												"gutters"           : ["CodeMirror-lint-markers"],
												"extraKeys"         : {
															"Alt-F"   : "findPersistent",
															"Cmd-F"   : "findPersistent",
															"Ctrl-F"  : "findPersistent",
												},
										},
								});
							 var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_select_level_custom_css') );
					 }


					 if( jQuery('#ihc_checkout_custom_css').length ) {
									jQuery.extend(wp.codeEditor.defaultSettings, {
											"codemirror": {
													"theme"             : "cobalt",
													"direction"         : "ltr",
													"mode"              : "text/css",
													"indentUnit"        : 4,
													"styleActiveLine"   : true,
													"indentWithTabs"    : true,
													"lineWrapping"      : true,
													"lineNumbers"       : true,
													"continueComments"  : true,
													"matchBrackets"     : true,
													"autoCloseBrackets" : true,
													"lint"              : false,
													"inputStyle"        : "contenteditable",
													"gutters"           : ["CodeMirror-lint-markers"],
													"extraKeys"         : {
																"Alt-F"   : "findPersistent",
																"Cmd-F"   : "findPersistent",
																"Ctrl-F"  : "findPersistent",
													},
											},
									});
								 var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_checkout_custom_css') );
						 }

						 if( jQuery('#ihc_list_access_posts_custom_css').length ) {
										jQuery.extend(wp.codeEditor.defaultSettings, {
												"codemirror": {
														"theme"             : "cobalt",
														"direction"         : "ltr",
														"mode"              : "text/css",
														"indentUnit"        : 4,
														"styleActiveLine"   : true,
														"indentWithTabs"    : true,
														"lineWrapping"      : true,
														"lineNumbers"       : true,
														"continueComments"  : true,
														"matchBrackets"     : true,
														"autoCloseBrackets" : true,
														"lint"              : false,
														"inputStyle"        : "contenteditable",
														"gutters"           : ["CodeMirror-lint-markers"],
														"extraKeys"         : {
																	"Alt-F"   : "findPersistent",
																	"Cmd-F"   : "findPersistent",
																	"Ctrl-F"  : "findPersistent",
														},
												},
										});
									 var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_list_access_posts_custom_css') );
							 }

								 if( jQuery('#ihc_badge_custom_css').length ) {
												jQuery.extend(wp.codeEditor.defaultSettings, {
														"codemirror": {
																"theme"             : "cobalt",
																"direction"         : "ltr",
																"mode"              : "text/css",
																"indentUnit"        : 4,
																"styleActiveLine"   : true,
																"indentWithTabs"    : true,
																"lineWrapping"      : true,
																"lineNumbers"       : true,
																"continueComments"  : true,
																"matchBrackets"     : true,
																"autoCloseBrackets" : true,
																"lint"              : false,
																"inputStyle"        : "contenteditable",
																"gutters"           : ["CodeMirror-lint-markers"],
																"extraKeys"         : {
																			"Alt-F"   : "findPersistent",
																			"Cmd-F"   : "findPersistent",
																			"Ctrl-F"  : "findPersistent",
																},
														},
												});
											 var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_badge_custom_css') );
									 }

									 if( jQuery('#ihc_invoices_custom_css').length ) {
													jQuery.extend(wp.codeEditor.defaultSettings, {
															"codemirror": {
																	"theme"             : "cobalt",
																	"direction"         : "ltr",
																	"mode"              : "text/css",
																	"indentUnit"        : 4,
																	"styleActiveLine"   : true,
																	"indentWithTabs"    : true,
																	"lineWrapping"      : true,
																	"lineNumbers"       : true,
																	"continueComments"  : true,
																	"matchBrackets"     : true,
																	"autoCloseBrackets" : true,
																	"lint"              : false,
																	"inputStyle"        : "contenteditable",
																	"gutters"           : ["CodeMirror-lint-markers"],
																	"extraKeys"         : {
																				"Alt-F"   : "findPersistent",
																				"Cmd-F"   : "findPersistent",
																				"Ctrl-F"  : "findPersistent",
																	},
															},
													});
												 var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_invoices_custom_css') );
									}
									if( jQuery('#ihc_listing_users_inside_page_custom_css').length ) {
 												jQuery.extend(wp.codeEditor.defaultSettings, {
 														"codemirror": {
 																"theme"             : "cobalt",
 																"direction"         : "ltr",
 																"mode"              : "text/css",
 																"indentUnit"        : 4,
 																"styleActiveLine"   : true,
 																"indentWithTabs"    : true,
 																"lineWrapping"      : true,
 																"lineNumbers"       : true,
 																"continueComments"  : true,
 																"matchBrackets"     : true,
 																"autoCloseBrackets" : true,
 																"lint"              : false,
 																"inputStyle"        : "contenteditable",
 																"gutters"           : ["CodeMirror-lint-markers"],
 																"extraKeys"         : {
 																			"Alt-F"   : "findPersistent",
 																			"Cmd-F"   : "findPersistent",
 																			"Ctrl-F"  : "findPersistent",
 																},
 														},
 												});
 											 var iumpCSSEditor = wp.codeEditor.initialize( jQuery('#ihc_listing_users_inside_page_custom_css') );
 								}

			if ( jQuery( '.ump-admin-js-pause-notification' ).length > 0 ){
					jQuery( '.ump-admin-js-pause-notification' ).on( 'click', function(){
						  jQuery.ajax({
									type 			: 'post',
									url 			: decodeURI( window.ihc_site_url )+'/wp-admin/admin-ajax.php',
									data 			: {
														 action : 'ihc_ajax_admin_announcement_do_close',
									},
									success		: function (data) {
											jQuery( '.ihc-dashboard-wrap' ).removeClass('ump-dashboard-show-announcement');
											jQuery( '.ump-dashboard-announcement-wrapper' ).removeClass('ihc-display-block').addClass('ihc-display-none');
									}
						  });
					});
			}



}); // end of document load

function ihcSendNotificationTest()
{
		jQuery.ajax({
					type : "post",
					url : decodeURI( window.ihc_site_url ) + '/wp-admin/admin-ajax.php',
					data : {
									 action						: "ihc_ajax_do_send_notification_test",
									 id								: jQuery('.ihc-js-notification-test-id').val(),
									 email						:jQuery( '.ihc-js-notification-test-email' ).val()
					},
					success: function (data) {

							ihcClosePopup();
					}
		});
}

function ihcDeleteFileViaAjax(id, u_id, parent, name, hidden_id){
    var r = confirm("Are you sure you want to delete?");
	if (r) {
    var s = jQuery(parent).attr('data-h');
    jQuery.ajax({
        type : "post",
        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
        data : {
                   action: "ihc_delete_attachment_ajax_action",
                   attachemnt_id: id,
                   user_id: u_id,
                   field_name: name,
                   h: s
               },
        success: function (data) {
			    jQuery(hidden_id).val('');
        	jQuery(parent + ' .ajax-file-upload-filename').remove();
        	jQuery(parent + ' .ihc-delete-attachment-bttn').remove();
        	if (jQuery(parent + ' .ihc-member-photo').length){
        		jQuery(parent + ' .ihc-member-photo').remove();
        		if (name=='ihc_avatar'){
        			jQuery(parent).prepend("<div class='ihc-no-avatar ihc-member-photo'></div>");
        			jQuery(parent + " .ihc-file-upload").css("display", 'block');
        		}
        	}

        	if (jQuery(parent + " .ihc-file-name-uploaded").length){
        		jQuery(parent + " .ihc-file-name-uploaded").remove();
        	}

        	if (jQuery(parent + ' .ajax-file-upload-progress').length){
        		jQuery(parent + ' .ajax-file-upload-progress').remove();
        	}
        	if (jQuery(parent + ' .ihc-icon-file-type').length){
        		jQuery(parent + ' .ihc-icon-file-type').remove();
        	}
        }
   });
	}
}

function ihcUpdateStripeConnectAuthUrl()
{
		jQuery( '.ihc-js-stripe-connect-live' ).removeClass( 'ihc-display-block' );
		jQuery( '.ihc-js-stripe-connect-live' ).removeClass( 'ihc-display-none' );
		jQuery( '.ihc-js-stripe-connect-sandbox' ).removeClass( 'ihc-display-block' );
		jQuery( '.ihc-js-stripe-connect-sandbox' ).removeClass( 'ihc-display-none' );
		if ( jQuery('#ihc_stripe_connect_live_mode').val() == 1 ){
				// live mode
				jQuery( '.ihc-js-stripe-connect-live' ).addClass( 'ihc-display-block' );
				jQuery( '.ihc-js-stripe-connect-sandbox' ).addClass( 'ihc-display-none' );
		} else {
				// sandbox mode
				jQuery( '.ihc-js-stripe-connect-live' ).addClass( 'ihc-display-none' );
				jQuery( '.ihc-js-stripe-connect-sandbox' ).addClass( 'ihc-display-block' );
		}
}

jQuery( function() {
    jQuery('.ihc-addons-link-wrapp a').attr('target','_blank');
		jQuery('.ihc-uap-link-wrapp a').attr('target','_blank');
		jQuery('.ihc-admin-menu-documentation-item a').attr('target','_blank');
});

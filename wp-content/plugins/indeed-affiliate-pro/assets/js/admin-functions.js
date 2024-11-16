/*
 *Ultimate Affiliate Pro - Main Backend JS Functions
 */
"use strict";
function uapDeleteFromTable(i, t, h, f){
	var m = window.uap_messages['general_delete'] + t + "?";
 	var c = confirm(m);
	if (c){
		jQuery(h).val(i);
		jQuery(f).submit();
	}
}

function uapSelectAllCheckboxes(c, t){
	if (jQuery(c).is(':checked')){
		jQuery(t).attr('checked', 'checked');
	} else {
		jQuery(t).removeAttr('checked');
	}
}

function uapDhSelector(t, v){
	if (v){
		var d = 'visible';
	} else {
		var d = 'hidden';
	}
	jQuery(t).css('visibility', d);
}

function uapDeleteBannerConfirm(i){
	var c = confirm("Delete This Banner?");
	if (c){
		jQuery('#delete_banner_id').val(i);
		jQuery('#form_banners').submit();
	}
}

function uapDeleteNotificationConfirm(i){
	var c = confirm("Delete This Notification?");
	if (c){
		jQuery('#delete_notification_id').val(i);
		jQuery('#form_notification').submit();
	}
}

function openMediaUp(target){
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
  });
  //Open the uploader dialog
  custom_uploader.open();
}

function uapRegisterFields(v){
	jQuery('#uap-register-field-values').fadeOut(200);
	jQuery('#uap-register-field-plain-text').fadeOut(200);
	jQuery('#uap-register-field-conditional-text').fadeOut(200);
	if (v=='select' || v=='checkbox' || v=='radio' || v=='multi_select'){
		jQuery('#uap-register-field-values').fadeIn(200);
	} else if (v=='plain_text'){
		jQuery('#uap-register-field-plain-text').fadeIn(200);
	} else if (v=='conditional_text'){
		jQuery('#uap-register-field-conditional-text').fadeIn(200);
	}
}

function uapAddNewRegisterFieldValue(){
	var s = '<div class="uap-custom-field-item-wrapp uap-custom-field-item-wrapp-st">';
	s += '<input type="text" name="values[]" value=""/> ';
	s += '<i class="fa-uap fa-remove-uap uap-js-register-fields-add-edit-remove"></i>';
	s += '<i class="fa-uap fa-arrows-uap"></i>';
	s += '</div>';
	jQuery('.uap-register-the-values').append(s);
}

function uapCheckAndH(from, target){
	if ( jQuery(from).is(":checked") ){
			jQuery(target).val(1);
	}	else {
		jQuery(target).val(0);
	}
}

function checkAndH(id, target){
	if(jQuery(id).is(':checked')){
		jQuery(target).val(1);
	}else{
		jQuery(target).val(0);
	}
}

function uapRegisterPreview(){
   	jQuery.ajax({
        type : 'post',
        url : window.uap_url + '/wp-admin/admin-ajax.php',
        data : {
                   action: 'uap_register_preview_ajax',
                   template: jQuery('#uap_register_template').val(),
                   custom_css: jQuery('#uap_register_custom_css').val(),
               },
        success: function (response) {
        	jQuery('#register_preview').fadeOut(200, function(){
        		jQuery(this).html(response);
        		jQuery(this).fadeIn(400);
        	});
        }
   });
}

function uapHandleDataFromResponseOnMenu( data )
{
		var response = JSON.parse( data );
		if ( typeof response.items !== 'undefined' && response.items !== '' ){
				if ( jQuery('.uap-subtab-menu').length > 0 ){
						jQuery('.uap-subtab-menu').after( response.items );
				} else {
						jQuery('.uap-admin-header').after( response.items );
				}

		}
}

function uapLoginPreview(){
   	jQuery.ajax({
        type : "post",
        url : decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
        data : {
                   action: "uap_login_form_preview",
                   remember: jQuery('#uap_login_remember_me').val(),
                   register_link: jQuery('#uap_login_register').val(),
                   pass_lost: jQuery('#uap_login_pass_lost').val(),
                   css: jQuery('#uap_login_custom_css').val(),
                   template: jQuery('#uap_login_template').val(),
                   uap_login_show_recaptcha: jQuery('#uap_login_show_recaptcha').val(),
               },
        success: function (d) {
        	jQuery('#uap-preview-login').fadeOut(200, function(){
        		jQuery(this).html(d);
        		jQuery(this).fadeIn(400);
        	});
        }
   });
}

function uapAddNewAchieveRule(){

	var t = jQuery('#achieve_type').val();
	var v = jQuery('#achieve_value').val();
	var print = '';

	if (t==-1 || t == null || t == ''){return;}

	if (jQuery('#achieve_relation_div').css('display')=='none'){
		jQuery('#achieve_relation_div').css('display', 'block');
	}

	var str = jQuery('#achieve_type_value').val();
	if (str==''){
		var n = 1;
		var obj = {i: n, type_1: t, value_1: v};
	} else {
		var obj = JSON.parse(str);
		obj.i++;
		var n = obj.i;
		obj["type_"+obj.i] = jQuery('#achieve_type').val();
		obj["value_"+obj.i] = jQuery('#achieve_value').val();
		obj["relation_"+obj.i] = jQuery('#achieve_relation').val();
		print += '<div class="achieve-item-relation">' + obj["relation_"+obj.i] + '</div>';
	}
	var str = JSON.stringify(obj);
	jQuery('#achieve_type_value').val(str);

	var achieve_type = jQuery("#achieve_type option[value='"+t+"']").text();
	print += '<div class="achieve-item" id="achieve_item_' + n + '"><div class="uap-achieve-type">'+'' + achieve_type + '</div><div>' + 'From: ' + v + '</div></div>';
	jQuery("#achieve_type option[value='"+t+"']").remove();

	var c = 0;
	jQuery("#achieve_type option").each(function(){
		c++;
	});
	if (c==1){
		jQuery('#achieve_type').attr('disabled', 'disabled');
		jQuery('#add_new_achieve').css('display', 'none');
	}

	var initial = jQuery('#achieve_rules_view').html();
	jQuery('#achieve_rules_view').html(initial + print);

	jQuery('#achieve_type').val('');
	jQuery('#achieve_value').val('');

	if (jQuery('#achieve_reset').css('display')=='none'){
		jQuery('#achieve_reset').css('display', 'inline-block');
	}

}

function uapAchieveReset(){
	jQuery('#achieve_type').removeAttr('disabled');
	jQuery('#add_new_achieve').css('display', 'inline-block');
	jQuery("#achieve_type option").each(function(){
		jQuery(this).remove();
	});
	jQuery.each(window.achieve_arr, function(k, v){
		if (typeof v!='undefined'){
			jQuery('#achieve_type').append('<option value="'+v.value+'">'+v.label+'</option>');
		}
	});

	jQuery('#achieve_rules_view').html('');
	jQuery('#achieve_reset').css('display', 'none');
	jQuery('#achieve_type_value').val('');
	jQuery('#achieve_relation_div').css('display', 'none');
}

function uapChageColor(id, value, where ){
    jQuery('#uap_colors_ul li').each(function(){
				jQuery(this).removeClass('uap-color-scheme-item-selected');
    });
    jQuery(id).addClass('uap-color-scheme-item-selected');
    jQuery(where).val(value);
}


function uapAutocompleteWriteTag(value_id, hiddenId, viewDivId, prevDivPrefix, label){
	/*
	 * viewDivId - parent
	 * prevDivPrefix - prefix of tag
	 * hiddenId - where values are
	 */
	var id = prevDivPrefix + value_id;
	jQuery(viewDivId).append('<div id="'+id+'" class="uap-tag-item">'+label+'<div class="uap-remove-tag" onclick="uapRemoveTag(\''+value_id+'\', \'#'+id+'\', \''+hiddenId+'\');" title="Removing tag">x</div></div>');
}

function uapAutocompleteWriteAndReplaceTag(value_id, hiddenId, viewDivId, prevDivPrefix, label){
	/*
	 * viewDivId - parent
	 * prevDivPrefix - prefix of tag
	 * hiddenId - where values are
	 */
	var id = prevDivPrefix + value_id;
	jQuery(viewDivId).html('<div id="'+id+'" class="uap-tag-item">'+label+'<div class="uap-remove-tag" onclick="uapRemoveTag(\''+value_id+'\', \'#'+id+'\', \''+hiddenId+'\');" title="Removing tag">x</div></div>');
}

function uapRemoveTag(removeVal, removeDiv, hiddenId){
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
	var i;
	for (i=0;i<arr.length;i++) {
	    if(arr[i]==elem){
	    	arr.splice(i, 1);
	    }
	}
	return arr;
}

function uapAddZero(i){
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}

function uapRankChangeOrderPreview(r, v){
	jQuery('.uap-rank-graphic').css('visibility', 'none');
   	jQuery.ajax({
        type : "post",
        url : decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
        data : {
                   action: "uap_make_ranks_reorder",
                   new_order: v,
                   rank_id: r,
                   current_label: jQuery('#rank_label').val(),
               },
        success: function (d) {
        	jQuery('.uap-rank-graphic').html(d);
        	jQuery('.uap-rank-graphic').css('visibility', 'visible');
        }
   });
}

function uapMakeInputhString(divCheck, showValue, hidden_input_id){
    var str = jQuery(hidden_input_id).val();
    if(str==-1) str = '';
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
    if(str=='') str = -1;
    jQuery(hidden_input_id).val(str);
}

//OPT IN
function uapConnectAweber(t){
    jQuery.ajax({
        type : "post",
        url : decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
        data : {
                action: "uap_update_aweber",
                auth_code: jQuery(t).val()
            },
        success: function (data) {
            alert('Connected');
        }
	});
}


function uapGetCcList(uap_cc_user, uap_cc_pass){
    jQuery("#uap_cc_list").find('option').remove();
	jQuery.ajax({
            type : "post",
						dataType: 'JSON',
            url : decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
            data : {
                    action: "uap_get_cc_list",
                    uap_cc_user: jQuery( uap_cc_user ).val(),
                    uap_cc_pass: jQuery( uap_cc_pass ).val()
            },
            success: function (data) {
								jQuery.each(data, function(i, option){
										jQuery("<option/>").val(i).text(option.name).appendTo("#uap_cc_list");
								});
						}
    });
}

function uapReturnNotification(){
    jQuery.ajax({
        type : "post",
        url : decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
        data : {
                action: "uap_get_notification_default_by_type",
                type: jQuery('#notf_type').val(),
            },
        success: function (r) {
					if ( typeof r === 'undefined' || r === '' ){
							jQuery('.uap-js-notification-description').html( '' );
							jQuery('#notf_message').val( '' );
		        	jQuery('#notf_subject').val( '' );
							return;
					}
        	var o = jQuery.parseJSON(r);
        	jQuery('#notf_subject').val(o.subject);
        	jQuery('#notf_message').val(o.content);
					if ( typeof o.description !== 'undefined' && o.description !== '' ){
        			jQuery('.uap-js-notification-description').html(o.description);
					} else {
							jQuery('.uap-js-notification-description').html('');
					}
        	jQuery("#notf_message_ifr" ).contents().find( '#tinymce' ).html(o.content);
        }
	});
}

function uapMatrixTypeCondition(v){
	if (v=='unilevel'){
		jQuery('#children_limit_div').css('display', 'none');
		jQuery('#uap_mlm_child_limit').removeAttr('max');
	}
	else {
		jQuery('#children_limit_div').css('display', 'table');
		if (v=='binary'){
			jQuery('#uap_mlm_child_limit').attr('max', 2);
			jQuery('#uap_mlm_child_limit').val(2);
		} else {
			jQuery('#uap_mlm_child_limit').removeAttr('max');
		}
	}
}

function uapMlmUpdateTbl(v){
	var last = parseInt(jQuery('#mlm-amount-for-each-level tr').last().attr('data-tr'));
	if (v<last){
		for (var i=last; i>v; i--){
			jQuery('#uap_mlm_level_' + i).remove();
		}
	} else {
		var str_model = jQuery('#uap_mlm_model tbody').html();
		var default_type = jQuery('#uap_mlm_default_amount_type').val();
		var default_value = jQuery('#uap_mlm_default_amount_value').val();
		for (var i=last+1; i<=v; i++){
			var str = str_model;
			str = str.replace(/{{i}}/g, i);
			jQuery('#mlm-amount-for-each-level tbody').append(str);
			jQuery('#uap_mlm_level_' + i + ' td select').val(default_type);
			jQuery('#uap_mlm_level_' + i + ' td input').val(default_value);
		}
	}
}

function uapClosePopup(){
	jQuery('#popup_box').fadeOut(300, function(){
		jQuery(this).remove();
	});
}

function uapApproveAffiliate(i){
   	jQuery.ajax({
        type: 'post',
        url: decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
        data: {
               action: 'uap_approve_affiliate',
               uid: i,
        },
        success: function () {
        	location.reload();
        }
   	});
}

function uapPaymentFormPaymentStatus(v){
	if (v=='bank_transfer'){
		jQuery('#payment_status_div').css('display', 'block');
	} else {
		jQuery('#payment_status_div').css('display', 'none');
	}
}

function uapDoDelete(t, f){
	var m = window.uap_messages[t];
	var c = confirm(m);
	if (c){
		jQuery(f).submit();
	} else {
		return false;
	}
}

function uapApMakeVisible(t, m){
	jQuery('.uap-ap-tabs-list-item').removeClass('uap-ap-tabs-selected-item');
	jQuery(m).addClass('uap-ap-tabs-selected-item');
	jQuery('.uap-ap-tabs-settings-item').fadeOut(200, function(){
		jQuery('#uap_tab_item_' + t).css('display', 'block');
	});
}

function checkSubmitAffiliateAction(){
	if (jQuery('[name=do_action]').val()=='delete'){
		var m = window.uap_messages['affiliates'] +  "?";
		var c = confirm(m);
		if (c){
			jQuery('#form_affiliates').submit();
		}
	} else {
		jQuery('#form_affiliates').submit();
	}
}

function uapMakeUserAffiliate(i){
   	jQuery.ajax({
        type: 'post',
        url: decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
        data: {
               action: 'uap_make_wp_user_affiliate',
               uid: i,
        },
        success: function (data){
        	if (data==2){
        		alert('Admin cannot become Affiliate!');
        	} else {
   	        	location.reload();
        	}
        }
   	});
}

function uapMakeAffiliateSimpleUser(i){
	jQuery.ajax({
        type: 'post',
        url: decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
        data: {
               action: 'uap_affiliate_simple_user',
               uid: i,
        },
        success: function () {
        	location.reload();
        }
   	});
}

function uapRemoveCurrency(c){
   	jQuery.ajax({
        type : 'post',
        url : decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
        data : {
                   action: 'uap_delete_currency_code_ajax',
                   code: c
        },
        success: function (r) {
        	if (r){
        		jQuery("#uap_div_"+c).fadeOut(300);
        	}
        }
   });
}

function uapRemoveSlug(i){
	jQuery.ajax({
        type : 'post',
        url : decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
        data : {
                   action: 'uap_remove_slug_from_aff',
                   uid: i
        },
        success: function (r) {
        	window.location = window.custom_aff_base_url;
        }
   });
}

function uapChangeColorScheme(id, value, where ){
    jQuery('#colors_ul li').each(function(){
        jQuery(this).attr('class', 'color-scheme-item');
    });
    jQuery(id).attr('class', 'color-scheme-item-selected');
    jQuery(where).val(value);
}
function uapChangeColorTop(id, value, where ){
	jQuery('#colors_ul li').each(function(){
			jQuery(this).removeClass('color-scheme-item-selected');
	});
	jQuery(id).addClass('color-scheme-item-selected');
	jQuery(where).val(value);
}

function uapPreviewUList(){
	jQuery('#preview').html('');
	jQuery("#preview").html('<div class="uap-loading-img-wrapper"><img src="'+window.uapPluginUrl+'/assets/images/loading.gif" /></div>');
	var meta = [];
	meta.num_of_entries = jQuery('#num_of_entries').val();
	meta.entries_per_page = jQuery('#entries_per_page').val();
	meta.order_by = jQuery('#order_by').val();
	meta.order_type = jQuery('#order_type').val();
	if (jQuery('#filter_by_rank').is(":checked")){
		meta.filter_by_rank = 1;
		meta.ranks_in = jQuery('#ranks_in').val();
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
			if (jQuery('#'+slider_special_metas[i]).is(":checked")){
				meta[slider_special_metas[i]] = 1;
			}
		}
	}

	///SHORTCODE
	var str = "[uap-listing-affiliates ";
	for (var key in meta) {
		str += key + " ='" + meta[key] +"' ";
	}
	str += ']';
    jQuery('.the-shortcode').html(str);
    jQuery(".php-code").html('&lt;?php echo do_shortcode("'+str+'");?&gt;');

    //AJAX CALL
   	jQuery.ajax({
        type : 'post',
        url : decodeURI(window.uap_url) + '/wp-admin/admin-ajax.php',
        data : {
                   action: 'uap_preview_user_listing',
                   shortcode: str
               },
        success: function (r) {
        	jQuery('#preview').html(r);
					if ( jQuery( '.uap-js-owl-settings-data' ).length ){
							jQuery('.uap-js-owl-settings-data').each(function( e, html ){
									uapInitiateOwl(html);
							});
					}
        }
   	});
}

function uapCheckboxDivRelation(c, t){
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

function uapWriteTagValueListUsers(id, hiddenId, viewDivId, prevDivPrefix){
    if( id.value == -1 || id.value == '' ) return;
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
			jQuery(viewDivId).append('<div id="'+prevDivPrefix+id.value+'" class="uap-tag-item">'+label+'<div class="uap-remove-tag" onclick="uapremoveTag(\''+id.value+'\', \'#'+prevDivPrefix+'\', \''+hiddenId+'\');uapPreviewUList();" title="Removing tag">x</div></div>');
    }

    jQuery(id).val(-1);
}

function uapShowHideDrip(){
	if (jQuery('#ihc_mb_type').val()=='show'){
		jQuery('#ihc_drip_content_empty_meta_box').css('display', 'none');
		jQuery('#ihc_drip_content_meta_box').css('display', 'block');
	} else {
		jQuery('#ihc_drip_content_empty_meta_box').css('display', 'block');
		jQuery('#ihc_drip_content_meta_box').css('display', 'none');
	}
}

function uapremoveTag(removeVal, prevDivPrefix, hiddenId){
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

function uapDeleteFileViaAjax(id, u_id, parent, name, hidden_id){
   	jQuery.ajax({
        type : "post",
        url : decodeURI(ajax_url),
        data : {
                   action: "uap_delete_attachment_ajax_action",
                   attachemnt_id: id,
                   user_id: u_id,
                   field_name: name,
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


function uapApproveEmail(id, new_label){
   	jQuery.ajax({
        type : 'post',
        url : decodeURI(ajax_url),
        data : {
                   action: 'uap_approve_user_email',
                   uid: id,
               },
        success: function (response) {
        	jQuery('#user_email_'+id+'_status').fadeOut(200, function(){
        		var the_span_styl = 'uap-user-email-st';
        		jQuery(this).html('<span class="'+the_span_styl+'">'+new_label+'</span>');
        		jQuery(this).fadeIn(200);

        		jQuery('#approve_email_'+id).fadeOut(200, function(){
        			jQuery(this).html('');
        		});
        	});
        }
   });
}

function uapCheckEmailServer(){
	jQuery.ajax({
			type : 'post',
	        url : decodeURI(ajax_url),
	        data : {
	                   action: 'uap_check_mail_server',
	               },
	        success: function (r){
	        	alert(window.uap_messages.email_server_check);
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

function uapCheckFieldLimit(limit, d){
	var val = jQuery(d).val().length;
	if (val>limit){
		jQuery(d).val('');
		alert(limit + ' is the maximum number of characters for this field!');
	}
}

function uapGeneratePaymentsCsv(){
   	jQuery.ajax({
        type : 'post',
        url : decodeURI(ajax_url),
        data : {
                   action: 'uap_do_generate_payments_csv',
                   min_date: jQuery('#csv_min_date').val(),
                   max_date: jQuery('#csv_max_date').val(),
                   payment_type: jQuery('#csv_payment_type').val(),
                   switch_status: jQuery('#csv_switch_status').val(),
        },
        success: function (response) {
        	if (response){
        		jQuery('.uap-hidden-download-link a').attr('href', response);
        		jQuery('.uap-hidden-download-link').fadeIn(200);
        		window.open(response, '_blank');
        	}
        }
   });

}


function uapDoRedirect(base_url, param, value_input){
	var the_url = base_url + '&' + param + '=' + jQuery(value_input).val();
	window.location = the_url;
}



///// SHINY SELECT

function uapShinySelect(params){
	/*
	 * params selector, item_selector, option_name_code, option_name_icon, default_icon, default_code
	 */
	var current_object = {};
	current_object.selector = params.selector; ///got # in front of it
	current_object.popup_id = 'indeed_select_' + params.option_name_code;
	current_object.popup_visible = false;
	current_object.option_name_code = params.option_name_code;
	current_object.option_name_icon = params.option_name_icon;
	current_object.item_selector = params.item_selector; /// got . in front of it
	current_object.init_default = params.init_default;
	current_object.second_selector = params.second_selector;
	current_object.default_code = params.default_code;

	jQuery(current_object.selector).after('<input type="hidden" name="' + current_object.option_name_code + '" value="' + params.default_code + '" />');
	jQuery(current_object.selector).after('<input type="hidden" name="' + current_object.option_name_icon + '" value="' + params.default_icon + '" />');
	jQuery(current_object.selector).after('<div class="indeed_select_popup uap-display-none" id="' + current_object.popup_id + '"></div>');

	///run init
	if (current_object.init_default){
		jQuery(current_object.selector).html('<i class="fa-uap-preview fa-uap ' + params.default_icon + '"></i>');
	}

	function getDataAndClose(){
		var code = jQuery(this).attr('data-code');
		var i_class = jQuery(this).attr('data-class');
		var the_html = jQuery(this).html();
		jQuery('[name=' + current_object.option_name_code + ']').val(code);
		jQuery('[name=' + current_object.option_name_icon + ']').val(i_class);
		jQuery(current_object.selector).html(the_html);
		removePopup();
	}

	function loadDataViaAjax(){
		var img = "<img src='" + decodeURI(window.uap_gif_loading) + "' class='uap-loading-img'/>";
		jQuery('#'+current_object.popup_id).html(img);
		jQuery('#'+current_object.popup_id).css('display', 'block');
		jQuery.ajax({
		    type : 'post',
		    dataType: "text",
		    url : decodeURI(window.uap_url) + '/wp-admin/admin-ajax.php',
		    data : {
		             action: 'uap_get_font_awesome_popup'
		    },
		    success: function (r){
		       	jQuery('#'+current_object.popup_id).html(r);
		       	jQuery(current_object.item_selector).on('click', getDataAndClose);
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
		//// arrow
		if (!current_object.popup_visible){
			current_object.popup_visible = true;
			loadDataViaAjax();
		} else {
			removePopup();
		}
	});

	function removePopup(){
		jQuery('#'+current_object.popup_id).html('');
		jQuery('#'+current_object.popup_id).css('display', 'none');
		current_object.popup_visible = false;
	}

}

function uapMakeExportFile(){
	var u = jQuery('#import_users').val();
	var s = jQuery('#import_settings').val();
	var pm = jQuery('#import_postmeta').val();
	jQuery('#uap_loading_gif .spinner').css('visibility', 'visible');
	jQuery.ajax({
		type : 'post',
	    url : decodeURI(window.uap_url) + '/wp-admin/admin-ajax.php',
	    data : {
	            action: 'uap_make_export_file',
	            import_users: u,
	            import_settings: s,
	            import_postmeta: pm
	           },
	    success: function (response) {
	        if (response!=0){
	        	jQuery('.uap-hidden-download-link a').attr('href', response);
	        	jQuery('.uap-hidden-download-link').fadeIn(200);
						jQuery('#uap_loading_gif .spinner').css('visibility', 'hidden');
	        }
	    }
	});
}

function uapCheckBaseReferralLink(v, u){
		if (v.indexOf(u)==-1){
				alert(jQuery('#base_referral_link_alert').html());
		}
}

function uapHideDivIfValue( checkValue, desiredValue, targetToHide )
{
		if ( checkValue==desiredValue ){
				jQuery(targetToHide).css('display', 'none');
		} else {
				jQuery(targetToHide).css('display', 'block');
		}
}

jQuery(document).ajaxSend(function (event, jqXHR, ajaxOptions) {
    if ( typeof ajaxOptions.data !== 'string' || ajaxOptions.data.includes( 'action=uap' ) === false ){
        return;
    }
    if ( typeof ajaxOptions.url === 'string' && ajaxOptions.url.includes('/admin-ajax.php')) {
       var token = jQuery('meta[name="uap-admin-token"]').attr("content");
       jqXHR.setRequestHeader('X-CSRF-UAP-ADMIN-TOKEN', token );
    }
});



function uap_split(v){
	if (v.indexOf(',')!=-1){
	    return v.split( /,\s*/ );
	} else if (v!=''){
		return [v];
	}
	return [];
}

function uapContains(a, obj) {
    return a.some(function(element){return element == obj;})
}

function uap_reset_autocomplete_fields()
{
		jQuery('#uap_pay_to_become_affiliate_target_products').val('')
		jQuery('#uap_reference_search_tags').html('')
}

function uap_make_disable_if_checked( checkSelector, target )
{
		if (jQuery(checkSelector).is(':checked')){
				jQuery(target).attr('disabled', 'disabled')
		} else {
				jQuery(target).removeAttr('disabled')
		}
}


function uap_extract(t) {
    return uap_split(t).pop();
}

window.addEventListener( 'DOMContentLoaded', function(){

	if( jQuery('#uap_login_custom_css').length ) {
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
			var uapCSSEditor = wp.codeEditor.initialize( jQuery('#uap_login_custom_css') );
	}

	if( jQuery('#uap_register_custom_css').length ) {
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
			var uapCSSEditor = wp.codeEditor.initialize( jQuery('#uap_register_custom_css') );
	}

	if( jQuery('#uap_listing_users_custom_css').length ) {
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
			var uapCSSEditor = wp.codeEditor.initialize( jQuery('#uap_listing_users_custom_css') );
	}

	if( jQuery('#uap_account_page_custom_css').length ) {
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
			var uapCSSEditor = wp.codeEditor.initialize( jQuery('#uap_account_page_custom_css') );
	}

		if ( jQuery( '.uap-js-close-admin-dashboard-'+'regist'+'ration'+'-notice' ).length ){
				jQuery( '.uap-js-close-admin-dashboard-'+'regist'+'ration'+'-notice' ).on( 'click', function(){
						var parent = jQuery(this).parent();
						parent.fadeOut( 1000 );
						jQuery.ajax({
								type : 'post',
								url : decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php',
								data : {
													 action: 'uap_'+'close_'+'admin_'+'registr'+'ation_'+'notice'
											 },
								success: function (response) {
										parent.remove();
								}
					 });
				});
		}


		if ( jQuery( '.uap-js-tracking-modal' ).length ){
				setTimeout( function(){
						jQuery( '.uap-js-tracking-modal' ).removeClass( 'uap-display-none' );
				}, 5000 );
		}

		// tracking modal
		if ( jQuery('#uap-admin-bttn-modal-tracking').length ){
				jQuery('#uap-admin-bttn-modal-tracking').on('click', function(){
						if ( jQuery( '.uap-js-confirm-tracking-checkbox' ).is(':checked') ){
								// fire the ajax call to confirm the tracking
								jQuery.ajax({
												type 			: 'post',
												url 			: decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
												data 			: {
																	 action		: 'uap_admin_confirm_tracking',
												},
												success		: function (response) {
														uapClosePopup();
												}
								});
						} else {
								// fire the ajax call to refuse the tracking
								jQuery.ajax({
												type 			: 'post',
												url 			: decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
												data 			: {
																	 action		: 'uap_admin_refuse_tracking',
												},
												success		: function (response) {
														uapClosePopup();
												}
								});
						}
				});
				jQuery(document).mouseup(function(e){
						var container = jQuery( ".uap-the-popup" );
						if (!container.is(e.target) && container.has(e.target).length === 0){
									/// close modal
									uapClosePopup();
						}
				});
		}
		// end of tracking modal

		var i, j;
		window.uap_messages = {
							referrals: jQuery( '.uap-js-general-messages' ).attr( 'data-referrals' ),
							general_delete: jQuery( '.uap-js-general-messages' ).attr( 'data-general_delete' ),
							affiliates: jQuery( '.uap-js-general-messages' ).attr( 'data-affiliates' ),
							email_server_check: jQuery( '.uap-js-general-messages' ).attr( 'data-email_server_check' ),
		};

		var uapCurrentUrl = window.location.href;

		uapShinySelect({
					selector: '#indeed_shiny_select_uap',
					item_selector: '.uap-font-awesome-popup-item',
					option_name_code: 'icon_code',
					option_name_icon: 'icon_class',
					default_icon: '',
					default_code: '',
					init_default: false,
					second_selector: '.uap-icon-arrow'
		});

		uapApMakeVisible('overview', '#uap_tab-overview');

		if ( jQuery('#referrals_date').length ){
				jQuery('#referrals_date').datepicker({
							dateFormat : 'yy-mm-dd ',
							onSelect: function(datetext){
									var d = new Date();
									datetext = datetext+d.getHours()+":"+uapAddZero(d.getMinutes())+":"+uapAddZero(d.getSeconds());
									jQuery(this).val(datetext);
							}
				});
		}

		jQuery( '.deactivate' ).on( 'click', function(evt){
				if ( jQuery( evt.target ).attr('href').indexOf( 'indeed-affiliate-pro' ) > -1 ){
						if ( window.uapKeepData == 1 ){
								var theMessage = 'Plugin data will be kept in database after you delete the plugin.';
						} else {
								var theMessage = 'Plugin data will be lost after you delete the plugin.';
						}
						var target = jQuery( evt.target ).attr('href');
						uapSwal({
							title: theMessage,
							text: "In order to change that, go to General Settings -> Admin Workflow.",
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

		jQuery( '.uap-js-close-admin-dashboard-notice' ).on( 'click', function(){
				var parent = jQuery(this).parent();
				parent.fadeOut( 1000 );
				jQuery.ajax({
						type : 'post',
						url : decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
						data : {
											 action: 'uap_close_admin_notice'
									 },
						success: function (response) {
								parent.remove();
						}
			 });
		});

		jQuery( '.uap-js-paypal-sandbox-on-off' ).on( 'click', function(){
				if ( jQuery('.uap-js-paypal-sandbox-on-off').is(':checked') ){
						/// sandbox
						jQuery('.uap-js-paypal-sandbox-credentials').css( 'display', 'block' );
						jQuery('.uap-js-paypal-live-credentials').css( 'display', 'none' );
				} else {
						/// live
						jQuery('.uap-js-paypal-sandbox-credentials').css( 'display', 'none' );
						jQuery('.uap-js-paypal-live-credentials').css( 'display', 'block' );
				}
		});

		jQuery( '.js-uap-change-recaptcha-version' ).on( 'change', function( evt ){
				if ( this.value == 'v2' ){
						jQuery( '.js-uap-recaptcha-v2-wrapp' ).css( 'display', 'block' );
						jQuery( '.js-uap-recaptcha-v3-wrapp' ).css( 'display', 'none' );
				} else {
						jQuery( '.js-uap-recaptcha-v2-wrapp' ).css( 'display', 'none' );
						jQuery( '.js-uap-recaptcha-v3-wrapp' ).css( 'display', 'block' );
				}
		});

		jQuery('#uap-register-fields-table tbody').sortable({
			 update: function(e, ui) {
			        jQuery('#uap-register-fields-table tbody tr').each(function (i, row) {
			        	var id = jQuery(this).attr('id');
			        	var newindex = jQuery("#uap-register-fields-table tbody tr").index(jQuery('#'+id));
			        	jQuery('#'+id+' .uap-order').val(newindex);
			        });
			    }
		});

		jQuery('#uap_reorder_menu_items tbody').sortable({
			 update: function(e, ui) {
			        jQuery('#uap_reorder_menu_items tbody tr').each(function (i, row) {
			        	var id = jQuery(this).attr('id');
			        	jQuery('#'+id+' .uap_account_page_menu_order').val(i);
			        });
			 }
		});

		jQuery('.uap-admin-mobile-bttn').on('click', function(){
			jQuery('.uap-dashboard-menu-items').toggle();
		});
		/// events
		jQuery( '#uap_js_add_edit_banners_trash' ).on( 'click', function( e, html ){
				jQuery('#uap_the_image').val('');
		});
		jQuery( '#uap_js_affiliate_list_add_filter' ).on( 'click', function( e, html ){
				jQuery('.uap-filters-wrapper').toggle();
		});
		jQuery( '#uap_js_edit_background_image_trash' ).on( 'click', function( e, html ){
				jQuery('#uap_ap_edit_background_image').val('');
		});
		jQuery( '#uap_js_flashbar_trash_img' ).on( 'click', function( e, html ){
				jQuery('[name=uap_info_affiliate_bar_logo]').val('');
		});
		jQuery( '#uap_js_iab_trash_banner' ).on( 'click', function( e, html ){
				jQuery('[name=uap_info_affiliate_bar_banner_default_value]').val('');
		});
		jQuery( '#uap_js_top_affiliates_the_toggle' ).on( 'click', function( e, html ){
				jQuery('#the_uap_user_list_settings').slideToggle();
		});
		jQuery( '#uap_js_toggle_preview' ).on( 'click', function( e, html ){
				jQuery('#preview').slideToggle();
		});
		jQuery( '.uap-js-location-reload' ).on( 'click', function( e, html ){
				var url = jQuery( this ).attr('data-url');
				window.location.href = url;
		});
		jQuery( '.uap-js-pay-to-become-aff-select-target' ).on( 'click', function( e, html ){
				var url = jQuery( this ).attr( 'data-url' );
				var value = jQuery( this ).val();
				uap_reset_autocomplete_fields();
				jQuery('#reference_search').autocomplete( 'option', { source: url + value } );
		});

		///
		if ( uapCurrentUrl.indexOf('wizard' ) === -1 ){
				if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro' ) !== -1 ){
					jQuery.ajax({
									type : 'post',
									url : decodeURI( window.uap_url ) + '/wp-admin/admin-ajax.php',
									data : {
														 action: 'uap_ajax_admin_top_menu_dynamic_data'
												 },
									success: function (response) {
											//console.log( response );
											uapHandleDataFromResponseOnMenu( response );
									}
					});
				} else {
						jQuery.ajax({
										type : 'post',
										url : decodeURI( window.uap_url ) + '/wp-admin/admin-ajax.php',
										data : {
															 action: 'uap_ajax_admin_top_menu_dynamic_data',
															 type: 'affiliate',

													 },
										success: function (response) {
												//console.log( response );
												uapHandleDataFromResponseOnMenu( response );
										}
						});
				}
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=dashboard' ) !== -1 ){
				// dashboard
				if (jQuery("#uap_chart_1").length > 0) {
						var uap_ticks = [];
						var uap_chart_stats = [];
						var i = 0;
						var j;
						jQuery( '.uap-js-dashboard-rank-data' ).each( function( e, html ){
								var label = jQuery( html ).attr('data-label');
								var value = jQuery( html ).attr('data-value');
								uap_ticks[ i ]=[ i, label ];
								uap_chart_stats[ i ]={ 0: i, 1:value};
								i++;
						});
						if ( i < 10 ) {
							for ( j=i; j<11; j++){
								uap_ticks[ i ]=[ i, '' ];
								uap_chart_stats[ i ]={ 0: j, 1:0 };
							}
						}

						var options = {
						    bars: { show: true, barWidth: 0.75, fillColor: '#7ebffc', lineWidth: 0 },
								grid: { hoverable: false, backgroundColor: "#fff", minBorderMargin: 0,  borderWidth: {top: 0, right: 0, bottom: 1, left: 1}, borderColor: "#aaa" },
								xaxis: { ticks: uap_ticks, tickLength:0 },
								yaxis: { tickDecimals: 0, tickColor: '#eee'},
								legend: {show: true, position: "ne"}
						};

						jQuery.plot(jQuery("#uap_chart_1"), [ {
												color: "#669ccf",
												data: uap_chart_stats,
											} ], options
						);
				}
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=login' ) !== -1 ){
				// login
				uapLoginPreview();
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=banners&subtab=add_edit' ) !== -1 ){
				// creatives
				if ( jQuery( '.uap-js-admin-creatives-select-type').length > 0 ){
						jQuery( '.uap-js-admin-creatives-select-type').on('change', function(){
								if ( this.value === 'text' ){
										jQuery( '#uap_js_admin_creatives_image_url' ).addClass( 'uap-display-none' );
										jQuery( '#uap_js_admin_creatives_text_type' ).removeClass( 'uap-display-none' );
								} else {
									jQuery( '#uap_js_admin_creatives_image_url' ).removeClass( 'uap-display-none' );
									jQuery( '#uap_js_admin_creatives_text_type' ).addClass( 'uap-display-none' );
								}
						});
				}
		}


		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=register&subtab=custom_fields-add_edit' ) !== -1 ){
						// register fields - add edit
						jQuery( document ).on( 'click', '.uap-js-register-fields-add-edit-remove', function( e, html ){
								jQuery(this).parent().remove();
						});
						return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=register&subtab=custom_fields' ) !== -1 ){
				// custom fields
				jQuery('.uap-register-the-values').sortable({
					cursor: 'move'
				});
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=affiliates&subtab=add_edit&id=' ) !== -1 ){
				// affiliates add/edit
				jQuery( '#usernames_search' ).on( 'blur', function(){
						var value = jQuery( '#usernames_search' ).val();
						if ( value === '' ){
								jQuery( '#uap_affiliate_mlm_parent' ).val( 0 );
						}
				});
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=affiliates' ) !== -1 ){
				// affiliates list
				jQuery( '.uap-js-affiliate-list-limit' ).on( 'change', function(){
						var baseUrl = jQuery( this ).attr('data-url');
						var value = jQuery( this ).val();
						window.location = baseUrl + value;
				});
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=register' ) !== -1 ){
				// register
				uapRegisterPreview();
				return;
		}

		if ( uapCurrentUrl.indexOf('page=ultimate_affiliates_pro&tab=magic_features&subtab=mlm_view_affiliate_children') !== -1 ){
				// Mlm - View Affiliate Childern
				google.charts.load('current', {packages:["orgchart"]});
				google.charts.setOnLoadCallback(uapDrawChart);

				function uapDrawChart() {
							var rows = [];

							// parent
							var theParentID = jQuery( '.uap-js-mlm-view-affiliate-children-parent-data' ).attr('data-parent_id');
							var parent = jQuery( '.uap-js-mlm-view-affiliate-children-parent-data' ).attr('data-parent');
							var parentAvatar = jQuery( '.uap-js-mlm-view-affiliate-children-parent-data' ).attr('data-parent_avatar');
							var parentFullName = jQuery( '.uap-js-mlm-view-affiliate-children-parent-data' ).attr('data-parent_full_name');
							if ( parent ){
									var htmlData = '<div class="uap-mlm-tree-avatar-child uap-mlm-tree-avatar-parent"><img src="' + parentAvatar + '" /></div><div class="uap-mlm-tree-name-child">'+ parentFullName +'</div>';
									rows.push( [ {v: theParentID, f: htmlData }, '', '' ] );
							}

							// affiliate
							var affiliateId = jQuery( '.uap-js-mlm-view-affiliate-data' ).attr('data-affiliate_id');
							var affiliateAvatar = jQuery( '.uap-js-mlm-view-affiliate-data' ).attr('data-affiliate_avatar');
							var affiliateFullName = jQuery( '.uap-js-mlm-view-affiliate-data' ).attr('data-parent_full_name');
							var htmlData = '<div class="uap-mlm-tree-avatar-child uap-mlm-tree-avatar-main"><img src="' + affiliateAvatar + '" /></div><div class="uap-mlm-tree-name-child">'+ affiliateFullName +'</div>';
							rows.push( [ {v: affiliateId, f: htmlData }, theParentID, 'Main Affiliate' ] );

							// children
							jQuery( '.uap-js-mlm-view-affiliate-children-data' ).each(function( e, html ){
										var affiliateId = jQuery( html ).attr('data-id');
										var avatar = jQuery( html ).attr('data-avatar');
										var name = jQuery( html ).attr('data-full_name');
										var parentId = jQuery( html ).attr('data-parent_id');
										var amount = jQuery( html ).attr('data-amount');
										htmlData = '<div class="uap-mlm-tree-avatar-child"><img src="' + avatar + '" /></div><div class="uap-mlm-tree-name-child">'+ name +'</div>';
										rows.push( [ {v: affiliateId, f: htmlData }, parentId, amount ] );
							})

							var data = new google.visualization.DataTable();
							data.addColumn('string', 'Name');
							data.addColumn('string', 'Manager');
							data.addColumn('string', 'ToolTip');

							data.addRows( rows );
							var chart = new google.visualization.OrgChart(document.getElementById('uap_mlm_chart'));
							if ( parent ){
								data.setRowProperty(0, 'style', 'background-color: #2a81ae; color: #fff;');
								data.setRowProperty(1, 'style', 'background-color: #f25a68; color: #fff;');
							}
							chart.draw(data, {allowHtml:true, size:"medium", allowCollapse:true});
				}
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=magic_features&subtab=migrate_affiliate_wp' ) !== -1 ){
				// Migrate - affiliate wp
				UapMigrate.init({
						trigger: '.uap-trigger-event-migrate',
						rankSelector: '.uap-migrate-assign-rank',
						progressBarWrapp: '.uap-progress-bar-wrapp',
						completeDiv: '.uap-progress-bar-warning',
						completeMessage: window.uapMigrationCompleteMessage,
						progressBarDiv: '.progress-bar',
				});
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=magic_features&subtab=migrate_affiliates_pro' ) !== -1 ){
				// Migrate - affiliates pro
				UapMigrate.init({
				    trigger: '.uap-trigger-event-migrate',
				    rankSelector: '.uap-migrate-assign-rank',
				    progressBarWrapp: '.uap-progress-bar-wrapp',
						completeDiv: '.uap-progress-bar-warning',
						completeMessage: window.uapMigrationCompleteMessage,
						progressBarDiv: '.progress-bar',
				});
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=magic_features&subtab=migrate_wp_affiliates' ) !== -1 ){
				// Migrate - wp affiliates
				UapMigrate.init({
						trigger: '.uap-trigger-event-migrate',
						rankSelector: '.uap-migrate-assign-rank',
						progressBarWrapp: '.uap-progress-bar-wrapp',
						completeDiv: '.uap-progress-bar-warning',
						completeMessage: window.uapMigrationCompleteMessage,
						progressBarDiv: '.progress-bar',
				});
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=magic_features&subtab=mlm' ) !== -1 ){
				/// mlm - username search
				jQuery( "#affiliate_name" ).on( "keydown", function( event ) {
					if ( event.keyCode === jQuery.ui.keyCode.TAB &&
						jQuery( this ).autocomplete( "instance" ).menu.active ) {
						event.preventDefault();
					}
				}).autocomplete({
					minLength: 0,
					source: decodeURI( window.uap_url ) + '/wp-admin/admin-ajax.php?action=uap_ajax_coupons_autocomplete&uapAdminAjaxNonce=' + window.uapAdminAjaxNonce + '&users=true',
					focus: function() {},
					select: function( event, ui ) {
						var l = ui.item.label;
						jQuery('#affiliate_name').val(l);
						return false;
					}
				});
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=magic_features&subtab=checkout_select_referral' ) !== -1 ){
				/// checkout select referral
				jQuery(function() {
					/// USERNAME SEARCH
					jQuery( "#usernames_search" ).on( "keydown", function( event ) {
						if ( event.keyCode === jQuery.ui.keyCode.TAB &&
							jQuery( this ).autocomplete( "instance" ).menu.active ) {
						 	event.preventDefault();
						}
					}).autocomplete({
						minLength: 0,
						source: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php?action=uap_ajax_offers_autocomplete&users=true&uapAdminAjaxNonce=' + window.uapAdminAjaxNonce,
						focus: function() {},
						select: function( event, ui ) {
							var input_id = '#usernames_search_hidden';
						 	var terms = uap_split(jQuery(input_id).val());//get items from input hidden
							var v = ui.item.id;
							var l = ui.item.label;
						 	if (!uapContains(terms, v)){
								terms.push(v);
							 	// print the new shiny box
							 	uapAutocompleteWriteTag(v, input_id, '#uap_username_search_tags', 'uap_username_tag_', l);
							 }
						 	var str_value = terms.join( "," );
						 	jQuery(input_id).val(str_value);//send to input hidden
							this.value = '';//reset search input
						 	return false;
						}
					});

				});
				return;
		}


		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=magic_features&subtab=coupons&add_edit' ) !== -1 ){
				/// coupons - add/edit
				var uap_source = jQuery('#the_source').val();

				jQuery('#the_source').on('change', function(){
						uap_source = jQuery(this).val();
						jQuery('#coupon_code').val('');
				});

				jQuery(function() {
					jQuery( "#coupon_code" ).on( "keydown", function( event ) {
						if ( event.keyCode === jQuery.ui.keyCode.TAB &&
							jQuery( this ).autocomplete( "instance" ).menu.active ) {
						 	event.preventDefault();
						}
					}).autocomplete({
						focus: function( event, ui ){},
						minLength: 0,
						source: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php?action=uap_ajax_coupons_autocomplete&uapAdminAjaxNonce='  + window.uapAdminAjaxNonce + '&source='+uap_source,
						select: function( event, ui ) {
							var v = ui.item.label;
							jQuery('#coupon_code').val(v);
						 	return false;

						}
					});

					/// USERNAME SEARCH
					jQuery( "#affiliate_name" ).on( "keydown", function( event ) {
						if ( event.keyCode === jQuery.ui.keyCode.TAB &&
							jQuery( this ).autocomplete( "instance" ).menu.active ) {
						 	event.preventDefault();
						}
					}).autocomplete({
						minLength: 0,
						source: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php?action=uap_ajax_coupons_autocomplete&uapAdminAjaxNonce='  + window.uapAdminAjaxNonce + '&users=true',
						focus: function() {},
						select: function( event, ui ) {
							var v = ui.item.id;
							var l = ui.item.label;
							jQuery('#affiliate_name').val(l);
						 	jQuery('#affiliate_id_hidden').val(v);//send to input hidden
						 	return false;
						}
					});

					jQuery( '.uap-js-coupons-add-edit-autocomplete' ).on( 'change', function( target ){
							var url = jQuery( '.uap-js-coupons-add-edit-autocomplete' ).attr( 'data-url_target' );
							var value =  jQuery( '.uap-js-coupons-add-edit-autocomplete' ).val();
							jQuery('#coupon_code').autocomplete( 'option', { source: url + value } );
					});

				});
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=magic_features&subtab=custom_affiliate_slug' ) !== -1 ){
				// custom affiliate slug

				jQuery(function() {
					/// USERNAME SEARCH
					jQuery( "#affiliate_name" ).on( "keydown", function( event ) {
						if ( event.keyCode === jQuery.ui.keyCode.TAB &&
							jQuery( this ).autocomplete( "instance" ).menu.active ) {
						 	event.preventDefault();
						}
					}).autocomplete({
						minLength: 0,
						source: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php?action=uap_ajax_coupons_autocomplete&uapAdminAjaxNonce='  + window.uapAdminAjaxNonce + '&users=true',
						focus: function() {},
						select: function( event, ui ) {
							var v = ui.item.id;
							var l = ui.item.label;
							jQuery('#affiliate_name').val(l);
						 	jQuery('#affiliate_id_hidden').val(v);//send to input hidden
						 	return false;
						}
					});

					window.custom_aff_base_url = jQuery( '.uap-js-custom-aff-base-url' ).attr( 'data-value' );
				});
				return;
		}

		if ( ( uapCurrentUrl.indexOf( 'wp-admin/post.php') !== -1 && jQuery.fn.autocomplete ) ||
				( uapCurrentUrl.indexOf( 'wp-admin/post-new.php' ) !== -1 && jQuery.fn.autocomplete )
		){
				// posts and pages
				// set the landing page
				jQuery( "#usernames_search" ).on( "keydown", function( event ) {
					if ( event.keyCode === jQuery.ui.keyCode.TAB &&
						jQuery( this ).autocomplete( "instance" ).menu.active ) {

						event.preventDefault();
					}
				}).autocomplete({
					minLength: 0,
					source: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php?action=uap_ajax_offers_autocomplete&users=true&without_all=true&uapAdminAjaxNonce=' + window.uapAdminAjaxNonce,
					focus: function() {},
					select: function( event, ui ) {
						var input_id = '#uap_landing_page_affiliate_id';
						var v = ui.item.id;
						var l = ui.item.label;
						uapAutocompleteWriteAndReplaceTag(v, input_id, '#uap_username_search_tags', 'uap_username_tag_', l);
						jQuery(input_id).val(v);//send to input hidden
						this.value = '';//reset search input
						return false;
					}
				});
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=magic_features&subtab=pay_to_become_affiliate' ) !== -1 ){
				// pay to become affiliate
					var uap_offer_source = jQuery('#the_source').val();
					jQuery('#the_source').on('change', function(){
						uap_offer_source = jQuery(this).val();
						jQuery('#uap_reference_search_tags').empty();
						jQuery('#reference_search_hidden').val('');
					});

					/// REFERENCE SEARCH
					jQuery( "#reference_search" ).on( "keydown", function( event ) {
						if ( event.keyCode === jQuery.ui.keyCode.TAB &&
							jQuery( this ).autocomplete( "instance" ).menu.active ) {
						 	event.preventDefault();
						}
					}).autocomplete({
					focus: function( event, ui ){},
					minLength: 0,
					source: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php?action=uap_ajax_offers_autocomplete&uapAdminAjaxNonce=' + window.uapAdminAjaxNonce + '&source=' + uap_offer_source,
					select: function( event, ui ) {
						var input_id = '#uap_pay_to_become_affiliate_target_products';
					 	var terms = uap_split(jQuery(input_id).val());//get items from input hidden
						var v = ui.item.id;
						var l = ui.item.label;
					 	if (!uapContains(terms, v)){
							terms.push(v);
						 	uapAutocompleteWriteTag(v, input_id, '#uap_reference_search_tags', 'uap_reference_tag_', l);// print the new shiny box
						 }
						var str_value = terms.join( "," );
					 	jQuery(input_id).val(str_value);//send to input hidden
						this.value = '';//reset search input
					 	return false;
					}
				});
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=notifications&subtab=add_edit' ) !== -1 ){
				// constants for wp editor
				if ( jQuery( '.uap-tag-code' ).length ){
						jQuery( '.uap-tag-code' ).on('click', function(){
								var text  =jQuery(this).html();
								var textarea = document.getElementById('notf_message');
								var position = textarea.selectionStart;
								var before = textarea.value.substring(0, position);
								var after = textarea.value.substring(position, textarea.value.length);
								textarea.value = before + text + after;
								textarea.selectionStart = textarea.selectionEnd = position + text.length;
						});
				}

				// Notifications - Add/Edit
				var notificationId = jQuery( '.uap-js-add-edit-notification-id' ).val();
				if ( parseInt( notificationId ) > 0 ){
						return;
				} else {
						uapReturnNotification();
						return;
				}
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=offers&subtab=add_edit' ) !== -1 ){
				// Offers - Add/Edit

				var uap_offer_source = jQuery('#the_source').val();

				jQuery('#the_source').on('change', function(){
						uap_offer_source = jQuery(this).val();
						if ( uap_offer_source == 'woo' ){
								jQuery( '.js-uap-product-label').html( jQuery( '.uap-js-offers-add-edit-labels' ).attr('data-products_and_cats') );
						} else {
								jQuery( '.js-uap-product-label').html( jQuery( '.uap-js-offers-add-edit-labels' ).attr('data-products') );
						}
						jQuery('#uap_reference_search_tags').empty();
						jQuery('#reference_search_hidden').val('');
				});


				/// REFERENCE SEARCH
				jQuery( "#reference_search" ).on( "keydown", function( event ) {
					if ( event.keyCode === jQuery.ui.keyCode.TAB &&
						jQuery( this ).autocomplete( "instance" ).menu.active ) {
					 	event.preventDefault();
					}
				}).autocomplete({
					focus: function( event, ui ){},
					minLength: 0,
					source: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php?action=uap_ajax_offers_autocomplete&source='+uap_offer_source+'&uapAdminAjaxNonce=' + window.uapAdminAjaxNonce,
					select: function( event, ui ) {
						var input_id = '#reference_search_hidden';
					 	var terms = uap_split(jQuery(input_id).val());//get items from input hidden
						var v = ui.item.id;
						var l = ui.item.label;
						if ( typeof ui.item.is_category != 'undefined' ){

								for ( var i = 0; i < ui.item.children.length; i++ ){
										if (!uapContains(terms, ui.item.children[i].id )){
											terms.push(ui.item.children[i].id);
											uapAutocompleteWriteTag(ui.item.children[i].id, input_id, '#uap_reference_search_tags', 'uap_reference_tag_', ui.item.children[i].label);// print the new shiny box
										 }
										var str_value = terms.join( "," );
										jQuery(input_id).val(str_value);//send to input hidden
								}
								this.value = '';//reset search input
								return false;
						}
					 	if (!uapContains(terms, v)){
							terms.push(v);
						 	uapAutocompleteWriteTag(v, input_id, '#uap_reference_search_tags', 'uap_reference_tag_', l);// print the new shiny box
						 }
						var str_value = terms.join( "," );
					 	jQuery(input_id).val(str_value);//send to input hidden
						this.value = '';//reset search input
					 	return false;

					}
				});

			/// USERNAME SEARCH
			jQuery( "#usernames_search" ).on( "keydown", function( event ) {
				if ( event.keyCode === jQuery.ui.keyCode.TAB &&
					jQuery( this ).autocomplete( "instance" ).menu.active ) {
					event.preventDefault();
				}
			}).autocomplete({
				minLength: 0,
				source: decodeURI( window.uap_url ) + '/wp-admin/admin-ajax.php?action=uap_ajax_offers_autocomplete&users=true&uapAdminAjaxNonce=' + window.uapAdminAjaxNonce,
				focus: function() {},
				select: function( event, ui ) {
					var input_id = '#usernames_search_hidden';
					var terms = uap_split(jQuery(input_id).val());//get items from input hidden
					var v = ui.item.id;
					var l = ui.item.label;
					if (!uapContains(terms, v)){
						terms.push(v);
						// print the new shiny box
						uapAutocompleteWriteTag(v, input_id, '#uap_username_search_tags', 'uap_username_tag_', l);
					 }
					var str_value = terms.join( "," );
					jQuery(input_id).val(str_value);//send to input hidden
					this.value = '';//reset search input
					return false;
				}
			});

			jQuery('.uap-datepick').each(function(){
				jQuery(this).datepicker({
								dateFormat : 'yy-mm-dd ',
								onSelect: function(datetext){
										var d = new Date();
										datetext = datetext+d.getHours()+":"+uapAddZero(d.getMinutes())+":"+uapAddZero(d.getSeconds());
										jQuery(this).val(datetext);
								}
						});
				});

				jQuery( '.uap-js-offers-add-edit-select-source' ).on( 'change', function(){
						var targetUrl = jQuery( '.uap-js-offers-add-edit-select-source' ).attr( 'data-target_url' );
						var value = jQuery( '.uap-js-offers-add-edit-select-source' ).val();
						jQuery('#reference_search').autocomplete( 'option', { source: targetUrl + value } );
				});

				return;
		}


		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=payments&subtab=list_all_unpaid' ) !== -1 ){
				// Payments - list all unpaid
				jQuery(".do-the-payment").on('click', function(e){
						e.preventDefault();
			    	jQuery('.uap-referral').each(function(i){
						if (jQuery(this).is(':checked')){
							jQuery("#form_payments").submit();
						}
					});
				});
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=ranks' ) !== -1 ){
				// Ranks - Listing
				jQuery( '.uap-js-delete-ranks' ).on( 'click', function(){
						var rankId = jQuery( this ).attr( 'data-id' );
						uapSwal({
							title: jQuery( '.uap-js-ranks-listing-delete-item-label' ).attr( 'data-value'),
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
										url : decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php',
										data : {
															 action									: 'uap_admin_delete_ranks',
															 id											:	rankId,
															 uap_admin_forms_nonce	:	jQuery( '.uap-js-ranks-listing-delete-nonce' ).attr( 'data-value'),
													 },
										success: function (response) {
												if ( response == 'success' ){
														location.reload();
												} else {
													uapSwal({
														title: response,
														text: "",
														type: "warning",
														showCancelButton: false,
														confirmButtonClass: "btn-danger",
														confirmButtonText: "OK",
														closeOnConfirm: true
													});
												}
										}
							 });
					 });
				});
		}


		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=magic_features&subtab=simple_links' ) !== -1 ){
				/// USERNAME SEARCH
				jQuery("#affiliate_name").on("keydown", function(event){
					if (event.keyCode===jQuery.ui.keyCode.TAB &&
						jQuery(this).autocomplete("instance").menu.active) {
						event.preventDefault();
					}
				}).autocomplete({
					minLength: 0,
					source: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php?action=uap_ajax_coupons_autocomplete&uapAdminAjaxNonce=' + window.uapAdminAjaxNonce + '&users=true',
					focus: function() {},
					select: function( event, ui ) {
						jQuery('#affiliate_name').val(ui.item.label);
						jQuery('#affiliate_id').val(ui.item.id);
						return false;
					}
				});

				/// USERNAME SEARCH
				jQuery("#affiliate_name_search").on("keydown", function(event){
					if (event.keyCode===jQuery.ui.keyCode.TAB &&
						jQuery(this).autocomplete("instance").menu.active) {
						event.preventDefault();
					}
				}).autocomplete({
					minLength: 0,
					source: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php?action=uap_ajax_coupons_autocomplete&uapAdminAjaxNonce=' + window.uapAdminAjaxNonce + '&users=true',
					focus: function() {},
					select: function( event, ui ) {
						jQuery('#affiliate_name_search').val(ui.item.label);
						jQuery('#search_aff_id').val(ui.item.id);
						return false;
					}
				});
				return;
		}

		if ( uapCurrentUrl.indexOf('page=ultimate_affiliates_pro&tab=help') !== -1 ){
				// help section
				jQuery( '[name=uap_save_licensing_code]' ).on( 'click', function(){
						jQuery.ajax({
									type : "post",
									url : window.uap_url + '/wp-admin/admin-ajax.php',
									data : {
													 action						: "uap_el_check_get_url_ajax",
													 s 					  		: jQuery('.uap-js-admin-help-section-s').val(),
													 nonce						: jQuery( '.uap-js-help-section-nonce' ).attr('data-value'),
													 ls								: jQuery( '.uap-js-admin-help-section-ls' ).val(),
													 ame 							: jQuery( '.uap-js-admin-help-section-name').val()
									},
									success: function (data) {
											if ( data ){
													window.location.href = data;
											} else {
													alert( 'Error!' );
											}
									}
						});
						return false;
				});
				jQuery( '.uap-js-revoke-license' ).on( 'click', function(){
						jQuery.ajax({
									type : "post",
									url : window.uap_url + '/wp-admin/admin-ajax.php',
									data : {
													 action						: "uap_revoke_license",
													 nonce						: jQuery( '.uap-js-help-section-nonce' ).attr('data-value'),
									},
									success: function (data) {
											window.location.href = jQuery( '.uap-js-help-section-revoke-url' ).attr('data-value');
									}
						});
				});
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=top_affiliates') !== -1 ){
				// top affiliates
				uapPreviewUList();
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=account_page' ) !== -1 ){
				// account page
				var i = 0;
				var uap_shiny_object = [];
				jQuery( '.uap-js-account-page-icon-details' ).each( function( e, html ){
					var iconCode = jQuery( this ).attr('data-icon_code');
					var slug = jQuery( this ).attr('data-slug');

					uap_shiny_object[i] = new uapShinySelect({
								selector: '#indeed_shiny_select_' + slug,
								item_selector: '.uap-font-awesome-popup-item',
								option_name_code: 'uap_tab_' + slug + '_icon_code',
								option_name_icon: 'uap_tab_' + slug + '_icon_class',
								default_icon: 'fa-uap fa-' + slug + '-account-uap',
								default_code: iconCode,
								init_default: true,
								second_selector: '#uap_icon_arrow_' + slug
					});
					i++;
				});
				return;
		}

		if ( uapCurrentUrl.indexOf('page=ultimate_affiliates_pro&tab=ranks&subtab=add_edit') !== -1 ){
				// Ranks - Add/Edit
				var i = 1;
				window.achieve_arr = [];
				jQuery( '.uap-js-achieve-types-values' ).each( function( e, html ){
						var theLabel = jQuery( html ).attr('data-label');
						var theValue = jQuery( html ).attr('data-value');
						window.achieve_arr[i] = {label: theLabel, value: theValue};
						i++;
				});
				return;
		}


		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=user_profile&affiliate_id' ) !== -1 ){
				// User Profile
				    jQuery('.uap-js-delete-referrals-link').on( 'click', function(){
				        jQuery.ajax({
				            type : 'post',
				            url : window.uap_url + '/wp-admin/admin-ajax.php',
				            data : {
				                       action: 'uap_delete_referrer_link_for_affiliate',
				                       id: jQuery(this).attr('data-id'),
				                   },
				            success: function (response) {
				                location.reload();
				            }
				       });
				    });
				    jQuery('.uap-js-delete-landing-page').on( 'click', function(){
				        jQuery.ajax({
				            type : 'post',
				            url : window.uap_url + '/wp-admin/admin-ajax.php',
				            data : {
				                       action: 'uap_delete_landing_page_for_affiliate',
				                       id: jQuery(this).attr('data-id'),
				                   },
				            success: function (response) {
				                location.reload();
				            }
				       });
				    });
				    jQuery('.uap-js-delete-coupons-link').on( 'click', function(){
				        jQuery.ajax({
				            type : 'post',
				            url : window.uap_url + '/wp-admin/admin-ajax.php',
				            data : {
				                       action: 'uap_delete_coupons_for_affiliate',
				                       id: jQuery(this).attr('data-id'),
				                   },
				            success: function (response) {
				                location.reload();
				            }
				       });
				    });

				    jQuery('.js-uap-copy').on( 'click', function(){
				        var url = jQuery( '.uap-userprofile-links a' ).attr( 'href' );
				        const el = document.createElement('textarea');
				        el.value = url;
				        document.body.appendChild(el);
				        el.select();
				        document.execCommand('copy');
				        document.body.removeChild(el);
				        uapSwal({
				          title: jQuery( '.uap-js-user-profile-copy-message' ).attr( 'data-value' ),
				          text: "",
				          type: "success",
				          showConfirmButton: false,
				          confirmButtonClass: "btn-success",
				          confirmButtonText: "OK",
				          timer: 1000
				        });
				    });
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=reports' ) !== -1 ){
				// Reports

				/// select time to search into
				if( jQuery( '.uap-js-reports-search-period-select' ).length ){
						jQuery( '.uap-js-reports-search-period-select' ).on('change', function(){
								if ( this.value === 'custom' ){
										jQuery( '#uap_admin_reports_search_start_date' ).removeClass( 'uap-display-none' );
										jQuery( '#uap_admin_reports_search_end_date' ).removeClass( 'uap-display-none' );
								} else {
										if ( !jQuery('#uap_admin_reports_search_start_date').hasClass( 'uap-display-none' ) ){
												jQuery( '#uap_admin_reports_search_start_date' ).addClass( 'uap-display-none' );
										}
										if ( !jQuery('#uap_admin_reports_search_end_date').hasClass( 'uap-display-none' ) ){
												jQuery( '#uap_admin_reports_search_end_date' ).addClass( 'uap-display-none' );
										}
								}
						});
				}

				/// datepicker
				if ( jQuery('#uap_admin_reports_search_start_date').length ){
						jQuery('#uap_admin_reports_search_start_date').datepicker({
									dateFormat : 'yy-mm-dd ',
									onSelect: function(datetext){
									}
						});
				}
				if ( jQuery('#uap_admin_reports_search_end_date').length ){
						jQuery('#uap_admin_reports_search_end_date').datepicker({
									dateFormat : 'yy-mm-dd ',
									onSelect: function(datetext){
									}
						});
				}

				var tick_Type = jQuery( '.uap-js-visits-tick-type' ).attr('data-value');
				var tick_Size = jQuery( '.uap-js-visits-tick-size' ).attr('data-value');
				var minTime = jQuery( '.uap-js-visits-min-time' ).attr('data-value');
				var maxTime = jQuery( '.uap-js-visits-max-time' ).attr('data-value');

				// visits
				var uap_visits = [];
				jQuery('.uap-js-visit-graph-data').each(function(e,html){
						var value = jQuery( html ).attr('data-value');
						var date = jQuery( html ).attr('data-date');
						var temporary = [ date, value ];
						uap_visits.push( temporary );
				});

				// visits success
				var uap_visits_success = [];
				jQuery('.uap-js-visit-graph-success-data').each(function(e,html){
						var value = jQuery( html ).attr('data-value');
						var date = jQuery( html ).attr('data-date');
						var temporary = [ date, value ];
						uap_visits_success.push( temporary );
				});

				if ( uap_visits.length > 0 ){
					jQuery.plot(
							jQuery("#uap-plot-1"), [{
								label : jQuery('.uap-js-reports-labels').attr('data-all_clicks'),
								data : uap_visits,
								color : "#625afa",
								shadowSize: 0,
								hoverable: true
							},{
								label : jQuery('.uap-js-reports-labels').attr('data-converted_clicks'),
								data : uap_visits_success,
								color : "#a3acba",
								shadowSize: 0,
								hoverable: true
							}
							], {
								grid: {
									hoverable: true,
									backgroundColor: "#fff",
									minBorderMargin: 0,
									borderWidth: {top: 0, right: 0, bottom: 1, left: 0},
									borderColor: "#ebeef1",
									autoHighlight: true
								},
								xaxis : {
									min : minTime,
									max : maxTime,
									mode : "time",
									color: "#ebeef1",
									//tickColor:'#c0c8d2',
									tickColor:'#ebeef1',
									tickSize: [tick_Size, tick_Type],
								},
								yaxis : {
									min:0,
									tickLength:0,
									tickDecimals:0,
									//color: "#a3acba",
									color: "#ebeef1",
								},
								series: {
									lines: { show: true, steps: 0, lineWidth: 2, zero: true, fill: false, fillColor: "rgba(255, 255, 255, 0.8)" }
								},
								legend: {
									labelBoxBorderColor: "#fff"
								}
								}
					);
				}


				var tick_Type = jQuery( '.uap-js-referrals-tick-type' ).attr('data-value');
				var tick_Size = jQuery( '.uap-js-referrals-tick-size' ).attr('data-value');
				var minTime = jQuery( '.uap-js-referrals-min-time' ).attr('data-value');
				var maxTime = jQuery( '.uap-js-referrals-max-time' ).attr('data-value');

				var uap_all_referrals = [];
				jQuery('.uap-js-referral-graph-data').each(function(e,html){
						var value = jQuery( html ).attr('data-value');
						var date = jQuery( html ).attr('data-date');
						var temporary = [ date, value ];
						uap_all_referrals.push( temporary );
				});

				var uap_all_referrals_refuse = [];
				jQuery('.uap-js-referral-graph-refuse-data').each(function(e,html){
						var value = jQuery( html ).attr('data-value');
						var date = jQuery( html ).attr('data-date');
						var temporary = [ date, value ];
						uap_all_referrals_refuse.push( temporary );
				});

				var uap_all_referrals_unverified = [];
				jQuery('.uap-js-referral-graph-unverified-data').each(function(e,html){
						var value = jQuery( html ).attr('data-value');
						var date = jQuery( html ).attr('data-date');
						var temporary = [ date, value ];
						uap_all_referrals_unverified.push( temporary );
				});

				var uap_all_referrals_verified = [];
				jQuery('.uap-js-referral-graph-verified-data').each(function(e,html){
						var value = jQuery( html ).attr('data-value');
						var date = jQuery( html ).attr('data-date');
						var temporary = [ date, value ];
						uap_all_referrals_verified.push( temporary );
				});

				if ( uap_all_referrals.length > 0 ){
						jQuery.plot(
								jQuery("#uap-plot-2"), [{
									label : jQuery('.uap-js-reports-labels').attr('data-all_referrals'),
									data : uap_all_referrals,
									color : "#625afa",
									shadowSize: 0,
									hoverable: true
								}

								], {
									grid: {
										hoverable: true,
										backgroundColor: "#fff",
										minBorderMargin: 0,
										borderWidth: {top: 0, right: 0, bottom: 1, left: 0},
										borderColor: "#ebeef1",
										autoHighlight: true
									},
									xaxis : {
										min : minTime,
										max : maxTime,
										mode : "time",
										color: "#ebeef1",
										//tickColor:'#c0c8d2',
										tickColor:'#ebeef1',
										tickSize: [tick_Size, tick_Type],

									},
									yaxis : {
										tickLength:0,
										tickDecimals:0,
										//color: "#a3acba",
										color: "#ebeef1",
									},
									series: {
        						lines: { show: true, steps: 0, lineWidth: 2, zero: true, fill: false, fillColor: "rgba(255, 255, 255, 0.8)" }
									},
									legend: {
										labelBoxBorderColor: "#fff"
									}
									}
						);
				}

				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=referrals' ) !== -1 ){
				// referrals - list
				jQuery( '.uap-js-referral-list-change-status' ).on( 'click', function( e, html ){
						var value = jQuery(this).attr('data-value');
						jQuery('#change_status').val( value );
						jQuery('#form_referrals').submit();
				});

				jQuery( '.uap-js-referral-list-limit-number' ).on( 'click', function( e, html ){
						var url = jQuery( this ).attr('data-url');
						var value = jQuery( this ).val();
						window.location = url + value;
				});
				return;
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=visits' ) !== -1 ){
			jQuery( '.uap-js-visits-list-limit-number' ).on( 'click', function( e, html ){
					var url = jQuery( this ).attr('data-url');
					var value = jQuery( this ).val();
					window.location = url + value;
			});
		}

		if ( uapCurrentUrl.indexOf( 'page=ultimate_affiliates_pro&tab=payments' ) !== -1 ){
				// transactions
				jQuery( '.uap-js-transactions-change-status' ).on( 'click', function( e, html ){
						var status = jQuery(this).attr('data-status');
						var id = jQuery(this).attr('data-id');
						jQuery('#transaction_id').val( id );
						jQuery('#new_status').val(status);
						jQuery('#form_payments').submit();
				});

				jQuery( '.uap-js-transactions-delete-transaction' ).on( 'click', function( e, html ){
						var id = jQuery(this).attr( 'data-id' );
						jQuery('#delete_transaction').val( id );
						jQuery('#form_payments').submit();
				});
		}

		if ( uapCurrentUrl.indexOf( 'admin.php?page=ultimate_affiliates_pro&tab=notifications' ) !== -1 ){
					if ( jQuery( '.uap-js-notifications-fire-notification-test' ).length > 0 ){
							jQuery( '.uap-js-notifications-fire-notification-test' ).on( 'click', function(){
									var notificationId = jQuery( this ).attr( 'data-notification_id' );
									jQuery.ajax({
											type 			: 'post',
											url 			: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php',
											data 			: {
																 action : 'uap_ajax_notification_send_test_email',
																 id			: notificationId
											},
											success		: function (data) {
													jQuery(data).hide().appendTo('body').fadeIn('normal');
											}
								 });
							});
					}
		}

		if ( uapCurrentUrl.indexOf( 'admin.php?page=ultimate_affiliates_pro&tab=banners&subtab=add_edit' ) !== -1 ){
				if ( jQuery( '.uap-js-banners-add-edit-multiple-cats').length > 0){
						jQuery( '.uap-js-banners-add-edit-multiple-cats').multiselect({
                selectAll: true,
                placeholder: jQuery('.uap-js-banners-add-edit-multiple-cats').attr( 'data-placeholder' ),
            });
				}
		}

		if ( uapCurrentUrl.indexOf( 'admin.php?page=ultimate_affiliates_pro&tab=integrations' ) !== -1 ){
				jQuery('.uap-js-do-switch-value').on('change', function(){
						var type = jQuery(this).attr('data-type');
						var status = jQuery(this).is(':checked') ? 1 : 0;
						var inputHidden = jQuery(this).attr('data-target');
						jQuery.ajax({
									type 			: 'post',
									url 			: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php',
									data 			: {
														 action : 'uap_ajax_update_integrations_status',
														 type 	: type,
														 value	: status,
									},
									success		: function (data) {
											var statusAsLabel = (status === 1) ? jQuery( '.uap-js-integration-status-for-' + type ).attr('data-enabled') : jQuery( '.uap-js-integration-status-for-' + type ).attr('data-disabled');
											jQuery( '#' + inputHidden ).val( status );
											jQuery( '.uap-js-integration-status-for-' + type ).html( statusAsLabel );
									}
						});

				});
		}

		if ( jQuery( '.uap-admin-js-pause-notification' ).length > 0 ){
				jQuery( '.uap-admin-js-pause-notification' ).on( 'click', function(){
					  jQuery.ajax({
								type 			: 'post',
								url 			: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php',
								data 			: {
													 action : 'uap_ajax_admin_announcement_do_close',
								},
								success		: function (data) {
										jQuery( '.uap-dashboard-wrap' ).removeClass('uap-dashboard-show-announcement');
										jQuery( '.uap-dashboard-announcement-wrapper' ).removeClass('uap-display-block').addClass('uap-display-none');
								}
					  });
				});
		}

});

window.addEventListener( 'DOMContentLoaded', function(){
		// events for all wp admin pages
		jQuery( '.uap-js-close-admin-dashboard-mk-notice' ).on( 'click', function(){
				var parent = jQuery(this).parent();
				parent.fadeOut( 1000 );
				jQuery.ajax({
								type : 'post',
								url : decodeURI( window.uap_url ) + '/wp-admin/admin-ajax.php',
								data : {
													 action: 'uap_ajax_close_admin_mk_notice',
													 target: jQuery( this ).attr( 'data-name' ),
											 },
								success: function (response) {
										parent.remove();
								}
				});
		});
});

function uapSendNotificationTest()
{
		jQuery.ajax({
					type : "post",
					url : decodeURI( window.uap_url ) + '/wp-admin/admin-ajax.php',
					data : {
									 action						: "uap_ajax_do_send_notification_test",
									 id								: jQuery('.uap-js-notification-test-id').val(),
									 email						:jQuery( '.uap-js-notification-test-email' ).val()
					},
					success: function (data) {
							uapClosePopup();
					}
		});
}
jQuery( function() {
    jQuery('.uap-addons-link-wrapp a').attr('target','_blank');
		jQuery('.uap-ihc-link-wrapp a').attr('target','_blank');
		jQuery('.uap-admin-menu-documentation-item a').attr('target','_blank');
});

function uapDoCleanUpLogs()
{
		var olderThen = jQuery( '#uap_older_then_select' ).val();
		jQuery.ajax({
					type : "post",
					url : decodeURI( window.uap_url ) + '/wp-admin/admin-ajax.php',
					data : {
									 action						: "uap_ajax_clean_notifications_logs",
									 older_then								: olderThen
					},
					success: function (data) {
							location.reload();
					}
		});
}

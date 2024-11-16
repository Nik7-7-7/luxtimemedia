/*
* Ultimate Learning Pro - Select Functions
*/
"use strict";

function ulpShinySelect(params){

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
	jQuery(current_object.selector).after('<div class="indeed_select_popup ulp-display-none" id="' + current_object.popup_id + '"></div>');
	///run init
	if (this.init_default){
		jQuery(current_object.selector).html('<i class="fa-ulp-preview fa-ulp ' + params.default_icon + '"></i>');
	}

	this.loadDataViaAjax = function(current_object){
		var img = "<img src='" + decodeURI(window.ulp_plugin_url)+'/assets/images/loading.gif' + "' class='ulp-loading-img'/>";
		jQuery('#'+current_object.popup_id).html(img);
		jQuery('#'+current_object.popup_id).css('display', 'block');
		jQuery.ajax({
		    type : 'post',
		    dataType: "text",
		    url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
		    data : {
		             action: 'ulp_get_font_awesome_popup'
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
							current_object.removePopup(current_object);
		       	});
				}
		});
	}

	this.removePopup = function(current_object){
		jQuery('#'+current_object.popup_id).empty();
		jQuery('#'+current_object.popup_id).css('display', 'none');
		current_object.popup_visible = false;
	}

	jQuery(current_object.selector).on('click', function(){
		if (!current_object.popup_visible){
			current_object.popup_visible = true;
			current_object.loadDataViaAjax(current_object);
		} else {
			current_object.removePopup(current_object);
		}
	});
	jQuery(current_object.second_selector).on('click', function(){
		if (!current_object.popup_visible){
			current_object.popup_visible = true;
			current_object.loadDataViaAjax(current_object);
		} else {
		current_object.removePopup(current_object);
		}
	});
}

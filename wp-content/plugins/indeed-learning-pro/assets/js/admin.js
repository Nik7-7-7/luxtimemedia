/*
* Ultimate Learning Pro - Backend Functions
*/
"use strict";
var UltimateLearningPro = {
	current_page: null,
	ulp_admin_general_messages: null,
	init: function(){
		if ( typeof ulp_admin_messages == 'object' ){
				this.ulp_admin_general_messages = ulp_admin_messages;
		} else {
				this.ulp_admin_general_messages = JSON.parse(ulp_admin_messages);
		}


		jQuery('#ulp_course_featured-checkbox').on( 'click', function(){
			UltimateLearningPro.checkAndH('#ulp_course_featured-checkbox', '[name=ulp_course_featured]');
		});
		///not working...
		jQuery('.key-up-action').keyup(function(){});
		jQuery('.ulp-delete-parent').on( 'click', function(){
			ulpRemoveElementFromLeft(this);
		});
		jQuery('.js-ulp-instructor-become-normal-user').on('click', function(){
				var uid = jQuery(this).attr('data-uid');
				if (uid){
						jQuery.ajax({
								type : 'post',
								url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
								data : {
								        action:  'ulp_instructor_become_normal_user',
								        user_id: uid,
								},
								success: function (r) {
									jQuery('#row_'+uid).remove();
								}
						});
				}
		});
	},
	checkAndH: function(s, t){
		if (jQuery(s).is(':checked')){
			jQuery(t).val(1);
		} else {
			jQuery(t).val(0);
		}
	}
};
/// execute script
jQuery(function(){
	UltimateLearningPro.init();
});
function ulpDoModuleRemove(i){
	jQuery('#moduleid_'+i).fadeOut(500, function(){
			this.remove();
	});
}
function ulpDoModuleToggle(i){
		var display = jQuery('#ulp_module_content_inside_'+i).css('display');
		if (display=='none'){
				jQuery('#ulp_module_content_inside_'+i).slideDown( 500 );
				var str = '<i class="fa-ulp fa-toggle_up-ulp" title="' + UltimateLearningPro.ulp_admin_general_messages.toggle_section + '" ></i>';
		} else {
				jQuery('#ulp_module_content_inside_'+i).slideUp( 500 );
				var str = '<i class="fa-ulp fa-toggle_down-ulp" title="' + UltimateLearningPro.ulp_admin_general_messages.toggle_section + '" ></i>';
		}
		jQuery('#ulp_toggle_modukle_' + i ).html(str);
}

function ulpLoading(selector)
{
		jQuery(selector).html("<div class='ulp-loading-wrapp'><img src='" + decodeURI(window.ulp_url) + "/wp-content/plugins/indeed-learning-pro/assets/images/loading.gif' /></div>");
}

function removeUlpLoading(selector)
{
		jQuery(selector).empty();
}

function ulpCheckAndH(s, t){
	if (jQuery(s).is(':checked')){
		jQuery(t).val(1);
	} else {
		jQuery(t).val(0);
	}
}

function ulpUpdateNumOfEachDiv(selector){
		jQuery(selector).each(function(e, m){
			e++;
			jQuery(m).html(e);
		});
}

function ulpMakeInputhString(divCheck, hidden_input_id){
    var str = jQuery(hidden_input_id).val();
    if ( str == -1 ) {
				str = '';
		}
    if ( str != '' ){
			var show_arr = str.split(',');
		} else {
			var show_arr = new Array();
		}
		var showValue = jQuery(divCheck).val();
    if ( jQuery( divCheck ).is( ':checked' ) ){
        show_arr.push(showValue);
    } else {
        var index = show_arr.indexOf(showValue);
        show_arr.splice(index, 1);
    }
    str = show_arr.join(',');
    if ( str == '' ){
				str = -1;
		}
    jQuery(hidden_input_id).val(str);
}
function ulpSecondMakeInputhString(divCheck, showValue, hidden_input_id){
    var str = jQuery(hidden_input_id).val();
    if ( str==-1 ){
			 str = '';
		}
    if ( str != '' ){
			var show_arr = str.split(',');
		} else {
			var show_arr = new Array();
		}
    if ( jQuery(divCheck).is(':checked') ){
        show_arr.push(showValue);
    } else {
        var index = show_arr.indexOf(showValue);
        show_arr.splice(index, 1);
    }
    str = show_arr.join(',');
    if(str=='') str = -1;
    jQuery(hidden_input_id).val(str);
}
function ulpMakeInputhStringWithValue(divCheck, hidden_input_id, the_value){
    var str = jQuery(hidden_input_id).val();
    if (str==-1){
			 str = '';
		}
    if (str!=''){
			var show_arr = str.split(',');
		} else {
			var show_arr = new Array();
		}
    if ( jQuery(divCheck).is(':checked') ){
        show_arr.push(the_value);
    } else {
        var index = show_arr.indexOf(the_value);
        show_arr.splice(index, 1);
    }
    str = show_arr.join(',');
    if(str=='') str = -1;
    jQuery(hidden_input_id).val(str);
}
function ulpRemoveElementFromLeft(element){
	jQuery(element).parent().remove();
}
function ulpDhSelector(t, v){
	if (v){
		var d = 'visible';
	} else {
		var d = 'hidden';
	}
	jQuery(t).css('visibility', d);
}
function ulpDeleteFromTable(i, t, h, f){
	swal({
		title: "",
		text: UltimateLearningPro.ulp_admin_general_messages.are_you_sure,
		type: "warning",
		showCancelButton: true,
		confirmButtonClass: "btn-danger",
		confirmButtonText: UltimateLearningPro.ulp_admin_general_messages.delete_it,
		closeOnConfirm: false
	},
	function(){
		jQuery(h).val(i);
		jQuery(f).submit();
	});
}
function ulpCheckEmailServer(){
	jQuery.ajax({
			type : 'post',
	        url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
	        data : {
	                   action: 'ulp_check_mail_server',
	               },
	        success: function (r){
							swal(UltimateLearningPro.ulp_admin_general_messages.email_works, "", "success");
	        }
	});
}
function ulpReturnNotification(){

    jQuery.ajax({
        type : "post",
        url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
        data : {
                action: "ulp_get_notification_default_by_type",
                type: jQuery('#notf_type').val(),
            },
        success: function (r) {
        	var o = jQuery.parseJSON(r);
        	jQuery('#notf_subject').val(o.subject);
        	jQuery('#notf_message').val(o.content);
        	jQuery("#notf_message_ifr" ).contents().find( '#tinymce' ).html(o.content);
        }
	});
}
function ulpDuplicatePost(pid){
	console.log(decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php');
	jQuery.ajax({
			type : "post",
			url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
			data : {
							action: "ulp_duplicate_post",
							post_id: pid
					},
			success: function (r) {
					location.reload(); /// refresh
			}
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
function ulpBadgesDoDelete(badge_id){
	jQuery.ajax({
			type : "post",
			url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
			data : {
							action: "ulp_delete_badge",
							id: badge_id
					},
			success: function (r) {
					jQuery('#table_tr_'+badge_id).fadeOut(300);
			}
		});
}
function ulpMakeInstructorUser(uid){
	jQuery.ajax({
			type : 'post',
			url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
			data : {
							action:  'ulp_instructor_become_normal_user',
							user_id: uid,
			},
			success: function (r) {
				location.reload();
			}
	});
}
function ulpMakeUserInstructor(uid){
	jQuery.ajax({
			type : 'post',
			url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
			data : {
							action:  'ulp_user_become_instructor',
							user_id: uid,
			},
			success: function (r) {
				location.reload();
			}
	});
}
function ulpRemoveCourse(uid, cid, course_name){
	swal({
	  title: "",
	  text: UltimateLearningPro.ulp_admin_general_messages.remove_user_from_course + course_name + "?",
	  type: "warning",
	  showCancelButton: true,
	  confirmButtonClass: "btn-danger",
	  confirmButtonText: UltimateLearningPro.ulp_admin_general_messages.delete_it,
	  closeOnConfirm: false
	},
	function(){
			jQuery.ajax({
					type : 'post',
					url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
					data : {
									action:  'ulp_user_remove_course',
									user_id: uid,
									course_id: cid,
					},
					success: function (r) {
						location.reload();
					}
			});
	});
}
function ulpRemoveInstructorFromCourse(uid, cid, course_name, additional_instructor){
	swal({
	  title:"",
	  text: UltimateLearningPro.ulp_admin_general_messages.remove_instructor_from_course + course_name + "?",
	  type: "warning",
	  showCancelButton: true,
	  confirmButtonClass: "btn-danger",
	  confirmButtonText: UltimateLearningPro.ulp_admin_general_messages.delete_it,
	  closeOnConfirm: false
	},
	function(){
			jQuery.ajax({
					type : 'post',
					url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
					data : {
									action:  'ulp_remove_instructor_from_course',
									user_id: uid,
									course_id: cid,
									is_additional_instructor: additional_instructor
					},
					success: function (r) {
						location.reload();
					}
			});
	});
}
function ulpAdminPreviewInvoice(){
	var m = jQuery('#invoice_form').serializeArray();
	jQuery('#preview_container').html('');
	jQuery.ajax({
		  type : 'post',
		  url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
			data : {
								 action: "ulp_admin_invoice_preview",
								 metas: m
			},
			success: function (r) {
					jQuery('#preview_container').html(r);
			}
	});
}
function ulpOpenInvoiceAdmin(i, user_id){
		jQuery.ajax({
				type : "post",
				url : decodeURI(window.ulp_url)+'/wp-admin/admin-ajax.php',
				data : {
									 action: "ulp_get_invoice_popup",
									 order_id: i,
									 uid: user_id,
				},
				success: function (r) {
						jQuery('body').append(r);
				}
		});
}
function ulpClosePopup(){
		jQuery('#ulp_popup').fadeOut(500, function(){
				jQuery(this).remove();
		});
}
function ulpApMakeVisible(t, m){
		jQuery('.ulp-ap-tabs-list-item').removeClass('ulp-ap-tabs-selected-item');
		jQuery(m).addClass('ulp-ap-tabs-selected-item');
		jQuery('.ulp-ap-tabs-settings-item').fadeOut(200, function(){
				jQuery('#ulp_tab_item_' + t).css('display', 'block');
		});
}
function ulpOpenCertificateFromAdmin(certificateId){
	jQuery.ajax({
			type : "post",
			url : decodeURI(window.ulp_url)+'/wp-admin/admin-ajax.php',
			data : {
								 action: "ulp_get_certificate_popup_for_admin",
								 certificate_id: certificateId
			},
			success: function (r) {
					jQuery('body').append(r);
			}
	});
}
function ulpShowSelectorIf(target, check, value_to_compare){
		if (check==value_to_compare){
				jQuery(target).css('display', 'block');
				return;
		}
		jQuery(target).css('display', 'none');
}
function ulpResetPoints(uid){
	jQuery.ajax({
			type : "post",
			url : decodeURI(window.ulp_url)+'/wp-admin/admin-ajax.php',
			data : {
								 action: "ulp_reset_points",
								 user_id: uid
			},
			success: function (r) {
					location.reload();
			}
	});
}
function ulpRemoveCurrency(c){
	jQuery.ajax({
			type : 'post',
			url : decodeURI(window.ulp_url)+'/wp-admin/admin-ajax.php',
			data : {
								 action: 'ulp_delete_custom_currency',
								 code: c
						 },
			success: function (r) {
				if (r){
					jQuery("#ulp_div_"+c).fadeOut(300);
				}
			}
	});
}
function ulpRemoveDifficultyType(the_slug){
	jQuery.ajax({
			type : 'post',
			url : decodeURI(window.ulp_url)+'/wp-admin/admin-ajax.php',
			data : {
								 action: 'ulp_delete_course_difficulty',
								 slug: the_slug
						 },
			success: function (r) {
				if (r){
					jQuery("#ulp_div_"+the_slug).fadeOut(300);
				}
			}
	});
}
function ulpCheckFieldLimit(limit, d){
	var val = jQuery(d).val().length;
	if (val>limit){
		jQuery(d).val('');
		swal(UltimateLearningPro.ulp_admin_general_messages.error, limit + ' ' + UltimateLearningPro.ulp_admin_general_messages.limit_char, "error");
	}
}
function ulpChangePostStatus(postId, postStatus, redirectLink){
		jQuery.ajax({
				type : 'post',
				url : decodeURI(window.ulp_url)+'/wp-admin/admin-ajax.php',
				data : {
									 action: 'ulp_change_post_status',
									 post_id: postId,
									 post_status: postStatus
							 },
				success: function (r) {
						if (redirectLink==''){
							location.reload();
						} else {
							window.location.href = redirectLink;
						}
				}
		});
}
function ulpMakeExportFile(){
		var instructors = jQuery('#import_instructors').val();
		var students = jQuery('#import_students').val();
		var s = jQuery('#import_settings').val();
		var cpt = jQuery('#import_custom_post_types').val();
		jQuery('#ulpLoading_gif .spinner').css('visibility', 'visible');

		jQuery.ajax({
		        type : 'post',
		        url : decodeURI(window.ulp_url)+'/wp-admin/admin-ajax.php',
		        data : {
		                   action: 'ulp_make_export_file',
		                   import_instructors: instructors,
		                   import_students: students,
		                   import_settings: s,
											 import_custom_post_types: cpt
		               },
		        success: function (response) {
		        	if (response!=0){
		        		jQuery('.ulp-hidden-download-link a').attr('href', response);
		        		jQuery('.ulp-hidden-download-link').fadeIn(200);
								jQuery('#ulpLoading_gif .spinner').css('visibility', 'hidden');
		        	}
		        }
		});
}
function ulpChangeColorScheme(id, value, where ){
    jQuery('#colors_ul li').each(function(){
        jQuery(this).removeClass('color-scheme-item-selected');
    });
    jQuery(id).addClass('color-scheme-item-selected');
    jQuery(where).val(value);
}
function ulpChangeColorSchemeWd(id, value, where ){
		var non_selected = 'color-scheme-item';
		var selected = 'color-scheme-item-selected';
		var c = jQuery(id).attr('class');
    jQuery('#colors_ul li').each(function(){
        jQuery(this).attr('class', non_selected);
    });
    jQuery(where).val('');
    if (c==non_selected){
	    jQuery(id).attr('class', selected);
	    jQuery(where).val(value);
    }
}
function ulpCheckboxDivRelation(c, t){
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
function ulpPreviewShortcode(){
		jQuery('#preview').html('');
		jQuery("#preview").html('<div class="ulp-preview-warpper"><img src="'+window.ulp_plugin_url+'/assets/images/loading.gif"/></div>');
		var meta = [];
		meta.num_of_entries = jQuery('#num_of_entries').val();
		meta.entries_per_page = jQuery('#entries_per_page').val();
		meta.order_by = jQuery('#order_by').val();
		meta.order_type = jQuery('#order_type').val();
		if (jQuery('#include_fields_label').is(':checked')){
			meta.include_fields_label = 1;
		}
		meta.theme = jQuery('#theme').val();
		meta.color_scheme = jQuery('#color_scheme').val();
		meta.columns = jQuery('#columns').val();
		if (jQuery('#align_center').is(":checked")){
			meta.align_center = 1;
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
		meta.general_pagination_theme = jQuery('#general_pagination_theme').val();
		meta.pagination_pos = jQuery('#pagination_pos').val();
		meta.fields = jQuery('#ulp_grid_fields').val();
		///SHORTCODE
		var str = '';
		switch (window.ulp_grid_entity_type){
				case 'students':
					var str = "[ulp-grid-students ";
					break;
				case 'courses':
					meta.filter_by_cats = jQuery('#ulp_filter_by_cats').val();
					if (meta.filter_by_cats==-1) meta.filter_by_cats = '';
					var str = "[ulp-grid-courses ";
					break;
		}
		for (var key in meta) {
			str += key + "='" + meta[key] +"' ";
		}
		str += ']';

	  jQuery('.the-shortcode').html(str);
	  jQuery(".php-code").html('&lt;?php echo do_shortcode("'+str+'");?&gt;');
					jQuery.ajax({
			        type : 'post',
			        url : decodeURI(window.ulp_url)+'/wp-admin/admin-ajax.php',
			        data : {
			                   action: 'ulp_ajax_do_shortcode',
												 shortcode: str
			               },
			        success: function (r) {
			        	jQuery('#preview').html(r);
								ulpInitOwl();
			        }
			   	});
}
function ulpSplit(v){
	if (v.indexOf(',')!=-1){
	    return v.split( /,\s*/ );
	} else if (v!=''){
		return [v];
	}
	return [];
}
function ulpExtract(t) {
    return ulpSplit(t).pop();
}
function contains(a, obj) {
    return a.some(function(element){return element == obj;})
}
function ulpLoadCourseStudents(PostId){
	jQuery('#ulp_list_course_students').slideUp( 500 );
	jQuery.ajax({
			type : 'post',
			url : decodeURI(window.ulp_url)+'/wp-admin/admin-ajax.php',
			data : {
								 action: 'ulp_ajax_edit_course_return_all_students_in_table',
								 post_id: PostId
						 },
			success: function (r) {
					jQuery('#ulp_list_course_students').html(r);
					jQuery('#ulp_list_course_students').slideDown( 500 );
			}
	});
}
function ulpAddUserToCourse(PostId){
		jQuery('#ulp_list_course_students').slideUp( 500 );
		jQuery.ajax({
				type : 'post',
				url : decodeURI(window.ulp_url)+'/wp-admin/admin-ajax.php',
				data : {
									 action: 'ulp_ajax_edit_course_add_new_student',
									 post_id: PostId,
									 username: jQuery('#ulp_new_student').val()
							 },
				success: function (r) {
						ulpLoadCourseStudents(r);
						jQuery('#ulp_new_student').val('');
				}
		});
}
function ulp_remove_course_reload_table(uid, cid, course_name){
	swal({
	  title: "",
	  text: UltimateLearningPro.ulp_admin_general_messages.remove_user_from_course + course_name + "?",
	  type: "warning",
	  showCancelButton: true,
	  confirmButtonClass: "btn-danger",
	  confirmButtonText: UltimateLearningPro.ulp_admin_general_messages.delete_it,
	  closeOnConfirm: true
	},
	function(){
			jQuery.ajax({
					type : 'post',
					url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
					data : {
									action:  'ulp_user_remove_course',
									user_id: uid,
									course_id: cid,
					},
					success: function (r) {
							ulpLoadCourseStudents(cid);
					}
			});
	});
}
function ulpRemoveUserBadge(uid, badgeId){
		swal({
		  title: "",
		  text: UltimateLearningPro.ulp_admin_general_messages.remove_badge_from_user,
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-danger",
		  confirmButtonText: UltimateLearningPro.ulp_admin_general_messages.delete_it,
		  closeOnConfirm: false
		},
		function(){
				jQuery.ajax({
						type : 'post',
						url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
						data : {
										action:  'ulp_user_remove_badge',
										user_id: uid,
										badge_id: badgeId,
						},
						success: function (r) {
							location.reload();
						}
				});
		});
}
function ulpRemoveUserCertificate(uid, certificateId){
		swal({
		  title: "",
		  text: UltimateLearningPro.ulp_admin_general_messages.remove_certificate_from_user,
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-danger",
		  confirmButtonText: UltimateLearningPro.ulp_admin_general_messages.delete_it,
		  closeOnConfirm: false
		},
		function(){
				jQuery.ajax({
						type : 'post',
						url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
						data : {
										action:  'ulp_user_remove_certificate',
										user_id: uid,
										certificate_id: certificateId,
						},
						success: function (r) {
							location.reload();
						}
				});
		});
}

//jQuery(document).on( 'ready', function(){
document.addEventListener("DOMContentLoaded", function() {

		jQuery('.js-ulp-do-delete-post').on('click', function(evt){
			swal({
			  title: "",
			  text: UltimateLearningPro.ulp_admin_general_messages.delete_post,
			  type: "warning",
			  showCancelButton: true,
			  confirmButtonClass: "btn-danger",
			  confirmButtonText: UltimateLearningPro.ulp_admin_general_messages.delete_it,
			  closeOnConfirm: false
			},
			function(){
					jQuery.ajax({
							type : 'post',
							url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
							data : {
											action: 'ulp_ajax_do_delete_post',
											postId: jQuery(evt.target).attr('data-id'),
							},
							success: function (r) {
								location.reload();
							}
					});
			});
		});

		// uninstall
		jQuery( '.deactivate' ).on( 'click', function(evt){
				if ( jQuery( evt.target ).attr('href').indexOf( 'indeed-learning-pro' ) > -1 ){
						if ( window.ulpKeepData == 1 ){
								var theMessage = 'Plugin data will be kept in database after you delete the plugin.';
						} else {
								var theMessage = 'Plugin data will be lost after you delete the plugin.';
						}
						var target = jQuery( evt.target ).attr('href');
						swal({
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

		// disable notice message
		jQuery( '.ulp-js-close-admin-dashboard-notice' ).on( 'click', function(){
				var parent = jQuery(this).parent();
				parent.fadeOut( 1000 );
				jQuery.ajax({
						type : 'post',
						url : decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
						data : {
											 action: 'ulp_close_admin_notice'
									 },
						success: function (response) {
								parent.remove();
						}
			 });
		});

		if ( jQuery( '.ulp-js-course-grid-list-settings-toggle' ).length > 0 ){
				jQuery( '.ulp-js-course-grid-list-settings-toggle' ).on( 'click', function(){
						jQuery('#the_ulp_user_list_settings').slideToggle();
				});
		}

		if ( jQuery( '.ulp-js-course-grid-list-settings-toggle-preview' ).length > 0 ){
				jQuery( '.ulp-js-course-grid-list-settings-toggle-preview' ).on( 'click', function(){
						jQuery('#preview').slideToggle();
				});
		}

		if ( jQuery( '.ulp-js-courses-grid-preview-shortcode' ).length > 0 ){
				window.ulp_grid_entity_type = jQuery( '.ulp-js-courses-grid-preview-shortcode' ).attr( 'data-grid_entity_type' );
				ulpPreviewShortcode();
		}

		if ( jQuery( '.ulp-js-magic-feat-invoices' ).length > 0 ){
				ulpAdminPreviewInvoice();
		}

		if ( jQuery( '.ulp-js-magic-feat-invoice-remove' ).length > 0 ){
				jQuery( '.ulp-js-magic-feat-invoice-remove' ).on( 'click', function(){
						jQuery('[name=ulp_invoices_logo]').val('');
				});
		}

		if ( jQuery( '.ulp-js-showcase-account-page-do-delete' ).length > 0 ){
				jQuery( '.ulp-js-showcase-account-page-do-delete' ).on( 'click', function(){
						jQuery('#ulp_ap_top_background_image').val('');
				});
		}

		if ( jQuery( '.ulp-js-showcase-account-page-shiny-select-data' ).length > 0 && typeof ulpShinySelect != 'undefined' ){
				var i = 0;
				var ulp_shiny_object = Array();
				jQuery( '.ulp-js-showcase-account-page-shiny-select-data' ).each( function(){
					  var k = jQuery( this ).attr( 'data-k' );
						var defaultCode = jQuery( this ).attr( 'data-default_code' );
						ulp_shiny_object[i] = new ulpShinySelect({
          													selector: '#indeed_shiny_select_' + k,
          													item_selector: '.ulp-font-awesome-popup-item',
          													option_name_code: 'ulp_ap_'  + k + '_icon_code',
          													option_name_icon: 'ulp_ap_'  + k + '_icon_class',
          													default_icon: 'fa-ulp fa-'  + k + '-account-ulp',
          													default_code: defaultCode,
          													init_default: true,
          													second_selector: '#ulp_icon_arrow_' + k
          	});
						i++;
				});
		}

		if ( jQuery( '.ulp-js-student-leaderboard-toggle' ).length > 0 ){
				jQuery( '.ulp-js-student-leaderboard-toggle' ).on( 'click', function(){
						jQuery('#the_ulp_user_list_settings').slideToggle();
				});
		}

		if ( jQuery( '.ulp-js-student-leaderboard-preview-toggle' ).length > 0 ){
				jQuery( '.ulp-js-student-leaderboard-preview-toggle' ).on( 'click', function(){
						jQuery('#preview').slideToggle();
				});
		}

		if ( jQuery( '.ulp-js-student-leaderboard-preview-shortcode' ).length > 0 ){
				window.ulp_grid_entity_type = 'students';
				ulpPreviewShortcode();
		}

		if ( jQuery( '.ulp-js-showcase-account-page-make-ap-visible' ).length > 0 ){
				ulpApMakeVisible( 'overview', '#ulp_tab-overview' );
		}

		// magic feat - certificates - autocomplete
		if ( jQuery( '.ulp-js-certificates-magic-feat-autocomplete' ).length > 0 ){
			jQuery( "#username" ).on( "keydown", function( event ) {
				if ( event.keyCode === jQuery.ui.keyCode.TAB &&
					jQuery( this ).autocomplete( "instance" ).menu.active ) {
					event.preventDefault();
				}
			}).autocomplete({
					minLength			: 0,
					source				: decodeURI(window.ulp_url)+'/wp-admin/admin-ajax.php?action=ulp_autocomplete_users&n=' + jQuery('meta[name="ulp-admin-token"]').attr("content") + '&term=',
					focus					: function() {},
					select				: function( event, ui ) {}
			});
		}

		// student badges - add/edit
		if ( jQuery( '.ulp-js-student-badge-add-edit-ulp-change-rules' ).length > 0 ){
				ulp_change_rules();
		}

		// notification - add/edit -
		if ( jQuery( '.ulp-js-notification-add-edit-load-notification-sample' ).length > 0 ){
				ulpReturnNotification();
		}

		// course reviews
		if ( jQuery('.ulp-js-course-review').length > 0 ){
				window.course_review_url = jQuery('.ulp-js-course-review').attr( 'data-url' );
		}

		// courses tags
		if ( jQuery( '.ulp-js-course-tags' ).length > 0 ){
			jQuery('.ulp-delete-course-tag').on('click', function(evt){
				swal({
					title: "",
					text: UltimateLearningPro.ulp_admin_general_messages.are_you_sure,
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-danger",
					confirmButtonText: UltimateLearningPro.ulp_admin_general_messages.delete_it,
					closeOnConfirm: true
				},
				function(){
						var term_id = jQuery(evt.currentTarget).attr('data-term_id');
						jQuery.ajax({
								type : "post",
								url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
								data : {
												action: "ulp_delete_tag",
												termId: term_id
										},
								success: function (r) {
										location.reload(); /// refresh
								}
						});
				});
			});
		}

		// dashboard
		if (jQuery("#ulp-chart-1").length > 0) {

			var ulp_ticks = [];
			var ulp_chart_stats = [];
			var i = 0;

			jQuery( '.ulp-js-dashboard-students-per-course' ).each( function(){
				var k = jQuery( this ).attr( 'data-k' );
				var v = jQuery( this ).attr( 'data-v' );
				ulp_ticks[ i ] = [ i, k ];
				ulp_chart_stats[ i ] = { 0: i, 1: v };
				i++;
			});

			if ( i < 11 ){
				for ( i; i<11; i++ ){
					ulp_ticks[ i ] = [ i, '' ];
					ulp_chart_stats[ i ] = { 0: i, 1: 0 };
				}
			}


			var options = {
				bars: { show: true, barWidth: 0.75, fillColor: '#7ebffc', lineWidth: 0 },
				grid: { hoverable: false, backgroundColor: "#fff", minBorderMargin: 0,  borderWidth: {top: 0, right: 0, bottom: 1, left: 1}, borderColor: "#aaa" },
				xaxis: { ticks: ulp_ticks, tickLength:0 },
				yaxis: { tickDecimals: 0, tickColor: "#eee"},
				legend: {
						show: true,
						position: "ne",
						}
			};

			jQuery.plot(jQuery("#ulp-chart-1"), [ {
				color: "#669ccf",
				data: ulp_chart_stats,
			} ], options
			);

		}

		// help
		if ( jQuery( '.ulp-js-help' ).length > 0 ){
			var nonce = jQuery( '.ulp-js-help' ).attr( 'data-nonce' );
			var location_reload = jQuery( '.ulp-js-help' ).attr( 'data-location_reload' );
			jQuery( '[name=ulp_save_licensing_code]' ).on( 'click', function(){
					jQuery.ajax({
								type : "post",
								url : window.ulp_url + '/wp-admin/admin-ajax.php',
								data : {
												 action						: "ulp_el_check_get_url_ajax",
												 purchase_code		: jQuery('[name=ulp_licensing_code]').val(),
												 nonce 						: nonce,
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
			jQuery( '.ulp-js-revoke-license' ).on( 'click', function(){
					jQuery.ajax({
								type : "post",
								url : window.ulp_url + '/wp-admin/admin-ajax.php',
								data : {
												 action						: "ulp_revoke_license",
												 nonce 						: nonce,
								},
								success: function (data) {
										window.location.href = location_reload;
								}
					});
			});
		}

		if ( jQuery( '.ulp-js-courses-students-meta-box' ).length > 0 ){
				var postId = jQuery( '.ulp-js-courses-students-meta-box' ).attr( 'data-post_id' );
				jQuery( "#ulp_new_student" ).on( "keydown", function( event ) {
					if ( event.keyCode === jQuery.ui.keyCode.TAB &&
						jQuery( this ).autocomplete( "instance" ).menu.active ) {
						event.preventDefault();
					}
				}).autocomplete({
						minLength: 0,
						source: decodeURI(window.ulp_url)+'/wp-admin/admin-ajax.php?action=ulp_autocomplete_users&n=' + jQuery('meta[name="ulp-admin-token"]').attr("content") + '&term=',
						focus: function() {},
						select: function( event, ui ) {
						}
				});
				ulpLoadCourseStudents( postId );

		}

		if ( jQuery( '.ulp-js-lesson-drip-content' ).length ){
			var startTimeType = jQuery( '.ulp-js-lesson-drip-content' ).attr( 'data-ulp_drip_start_type' );
			jQuery('#ulp_drip_start_certain_date').datepicker({
			dateFormat : 'dd-mm-yy',
			onClose: function( selectedDate ){
				jQuery( "#ihc_drip_end_certain_date" ).datepicker( "option", "minDate", selectedDate );
				}
			});
			ulpShowSelectorIf( '#after_x_time', startTimeType, 1 );
			ulpShowSelectorIf( '#specific_date', startTimeType, 2 );
		}

		if ( jQuery( '.ulp-js-questions-answer-meta-box' ).length > 0 ){
			jQuery('#ulpsrotingtype_wrapp').sortable({
				 update: function(e, ui) {
						jQuery('#ulpsrotingtype_wrapp div').each(function (i, row) {});
				 }
			});
		}

		if ( jQuery( '.ulp-js-quiz-questions-select-meta-box' ).length > 0 ){
				jQuery("#ulp_select_questions").multiselect({sortable: true, searchable: true, dividerLocation: 0.5,});
		}

		//datepicker
		if ( jQuery('.ulp-datetime').length > 0 ){
				jQuery('.ulp-datetime').each(function(){
					jQuery(this).datepicker({
									dateFormat : 'yy-mm-dd ',
									onSelect: function(datetext){
											var d = new Date();
											datetext = datetext+d.getHours()+":"+ulpAddZero(d.getMinutes())+":"+ulpAddZero(d.getSeconds());
											jQuery(this).val(datetext);
									}
					});
				});
		}

		if ( jQuery( '.ulp-js-post-panel-ulp-course' ).length > 0 ){
				var assessments = jQuery( '.ulp-js-post-panel-ulp-course' ).attr('data-assessmets');
				var payment = jQuery( '.ulp-js-post-panel-ulp-course' ).attr('data-payment');
				ulpShowSelectorIf('#ulp_zuiq_average', assessments, 'quizes');
				ulpShowSelectorIf('#ulp_course_price_num', payment, 1);
		}

		if ( jQuery( '.ulp-js-post-panel-ulp-quiz' ).length > 0 ){
				var ulp_quiz_grade_type = jQuery( '.ulp-js-post-panel-ulp-quiz' ).attr( 'data-ulp_quiz_grade_type' );
				ulpShowSelectorIf('#ulp_point_description', ulp_quiz_grade_type, 'point');
		}

		if ( jQuery('.ulp-js-student-account-custom-tabs').length > 0 ){
			jQuery('#ulp_reorder_menu_items tbody').sortable({
				 update: function(e, ui) {
								jQuery('#ulp_reorder_menu_items tbody tr').each(function (i, row) {
									var id = jQuery(this).attr('id');
									jQuery('#'+id+' .ulp_account_page_menu_order').val(i);
								});
				 }
			});

			var ulpStundentCustomMenuItem = new ulpShinySelect({
						selector: '#indeed_shiny_select',
						item_selector: '.ulp-font-awesome-popup-item',
						option_name_code: 'ulp_account_page_menu_add_new-the_icon_code',
						option_name_icon: 'ulp_account_page_menu_add_new-the_icon_class',
						default_icon: 'no-icon',
						default_code: 'no-icon',
						init_default: false,
						second_selector: '.ulp-icon-arrow'
			});
		}

		// print this init
		if ( jQuery( '.ulp-js-init-print-this' ).length > 0 ){
			var cssForPrinthis = jQuery( '.ulp-js-init-print-this' ).attr( 'data-load_css' );
			window.printhisopt = {
						importCSS: true,
						importStyle: true,
						loadCSS: cssForPrinthis,
						debug: false,
						printContainer: true,
						pageTitle: "",
						removeInline: false,
						printDelay: 333,
						header: null,
						formValues: false,
			};
		}

});

jQuery(document).ajaxSend(function (event, jqXHR, ajaxOptions) {
	if ( typeof ajaxOptions.data !== 'string' ||  ajaxOptions.data.includes( 'action=ulp' ) === false ){
			return;
	}

	if ( typeof ajaxOptions.url === 'string' && ajaxOptions.url.includes('/admin-ajax.php')) {
		 var token = jQuery('meta[name="ulp-admin-token"]').attr("content");
		 jqXHR.setRequestHeader('X-CSRF-ULP-ADMIN-TOKEN', token );
	}
});

function ulpAddZero(i)
{
		if (i < 10) {
				i = "0" + i;
		}
		return i;
}

function ulp_change_rules(){
		var t = jQuery('[name=badge_type]').val();
		if (t=='static'){
				jQuery('#rule_types_tier').css('display', 'none');
				jQuery('#rule_types_static').css('display', 'block');
		} else {
				jQuery('#rule_types_tier').css('display', 'block');
				jQuery('#rule_types_static').css('display', 'none');
		}
}

function ulpInitOwl()
{
	if ( jQuery( '.ulp-js-slider-option-data' ).length ){
			jQuery( '.ulp-js-slider-option-data' ).each( function(){
				var target = jQuery( this ).attr( 'data-target' );
				var autoHeight = jQuery( this ).attr( 'data-autoHeight' );
				var animateOut = jQuery( this ).attr( 'data-animateOut' );
				var animateIn = jQuery( this ).attr( 'data-animateIn' );
				var lazyLoad = jQuery( this ).attr( 'data-lazyLoad' );
				var loop = jQuery( this ).attr( 'data-loop' );
				var autoplay = jQuery( this ).attr( 'data-autoplay' );
				var autoplayTimeout = jQuery( this ).attr( 'data-autoplayTimeout' );
				autoplayTimeout = parseInt( autoplayTimeout );
				var autoplayHoverPause = jQuery( this ).attr( 'data-autoplayHoverPause' );
				var autoplaySpeed = jQuery( this ).attr( 'data-autoplaySpeed' );
				autoplaySpeed = parseInt( autoplaySpeed );
				var nav = jQuery( this ).attr( 'data-nav' );
				var navSpeed = jQuery( this ).attr( 'data-navSpeed' );
				navSpeed = parseInt( navSpeed );
				var dots = jQuery( this ).attr( 'data-dots' );
				var dotsSpeed = jQuery( this ).attr( 'data-dotsSpeed' );
				dotsSpeed = parseInt( dotsSpeed );
				var responsiveClass = jQuery( this ).attr( 'data-responsiveClass' );

				var owl = jQuery( target );
				var options = {
					items : 1,
					mouseDrag: true,
					touchDrag: true,

					autoHeight: autoHeight,

					animateOut: false,
					animateIn: false,

					lazyLoad : false,
					loop: true,

					autoplay : true,
					autoplayTimeout: autoplayTimeout,
					autoplayHoverPause: false,
					autoplaySpeed: autoplaySpeed,

					nav : true,
					navSpeed : navSpeed,
					navText: [ '', '' ],

					dots: true,
					dotsSpeed : dotsSpeed,

					responsiveClass: false,
					responsive:{
						0:{
							nav: false
						},
						450:{
							nav : true
						}
					}
				};
				owl.owlUlpCarousel( options );
			});
	}
}

function ulpInitPrinthis( selector )
{
		if ( typeof window.printhisopt != 'undefined' ){
				jQuery( selector ).printThis( window.printhisopt );
		}
}

function ulpInitCourseMultiselect()
{
		if ( jQuery( '.ulp-js-couse-add-new-module' ).length > 0 ){
			jQuery( '.ulp-js-couse-add-new-module' ).each( function(){
					var theId = jQuery( this ).attr( 'data-id' );
					jQuery( '#ulp_remove_modukle_' + theId ).on( 'click', function(){
						ulpDoModuleRemove( theId );
					});
					jQuery('#ulp_toggle_modukle_' + theId ).on( 'click', function(){
						ulpDoModuleToggle( theId );
					});
					jQuery("#module_item_" + theId ).multiselect({sortable: true, searchable: true, dividerLocation: 0.5});
			});
		}
}

/*
* Ultimate Learning Pro - Public Functions
*/
"use strict";
var IndeedQuiz = {
	expire_time: 0,
	submited_value: '',
	do_decode: '',
	quiz_id: 0,
	question_id: 0,
	submit_quiz_button: '',
	next_bttn: '',
	course_id: 0,

	start: function(){
	   	jQuery.ajax({
	        type : "post",
	        url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
	        data : {
	                   action: "ulp_start_quiz",
	                   qid: this.quiz_id,
										 course_id: this.course_id,
	               },
	        success: function (r) {
	        	window.location.href = r;
	        }
	   	});
	},
	shift: function(d){
	   	jQuery.ajax({
	        type : "post",
	        url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
	        data : {
	                   action: "ulp_quiz_shift_question",
	                   question_id: this.question_id,
	                   qid: this.quiz_id,
	                   direction: d,
										 course_id: this.course_id,
	        },
	        success: function (r) {
				  		window.location.href = r;
	        }
	   	});
	},
	saveAnswer: function(do_submit){
		var s = "[name=" + this.question_id + "]";
		var t = jQuery(s).attr('type');
		var do_decode = 0;
		if (typeof t=='undefined'){
				var is_sorting = jQuery("[name='" + this.question_id + "[]']").attr('data-field_type');
				if (is_sorting=='sorting'){
						t = 'sorting';
				} else if (is_sorting=='fill_in'){
						t = 'fill_in';
				} else if (is_sorting=='matching'){
						t = 'matching';
				} else if (is_sorting=='textarea'){
						t = 'textarea';
				} else {
						//Essay
						is_sorting = jQuery("[name='" + this.question_id + "']").attr('data-field_type');
						if (is_sorting=='textarea'){
							t = 'textarea';
						}else{
							t = 'checkbox';
						}
				}
		}

		var ajaxData = {
							 action: "ulp_save_question_answer",
							 question_id: this.question_id,
							 qid: this.quiz_id,
							 course_id: this.course_id,
		};
		switch (t){
			case 'radio':
				var v = jQuery(s+":checked").val();
				break;
			case 'text':
			case 'number':
			case 'hidden':
			case 'textarea':
				var v = jQuery(s).val();
				break;
			case 'sorting':
					var c = new Array();
					jQuery("[name='" + this.question_id + "[]']").each(function(){
							c.push(jQuery(this).val());
					});
					v = JSON.stringify(c);
					do_decode = 1;
				break;
			case 'checkbox':
				var c = new Array();
				jQuery("[name='" + this.question_id + "[]']").each(function(){
					if (jQuery(this).is(":checked")){
						c.push(jQuery(this).val());
					}
				});
				v = JSON.stringify(c);
				do_decode = 1;
				break;
			case 'fill_in':
				var c = new Array();
				jQuery("[name='" + this.question_id + "[]']").each(function(){
						c.push(jQuery(this).val());
				});
				v = JSON.stringify(c);
				do_decode = 1;
				break;
			case 'matching':
				var c = new Array();
				jQuery("[name='" + this.question_id + "[]']").each(function(){
						c.push(jQuery(this).val());
				});
				v = JSON.stringify(c);
				var questions = new Array();
				jQuery(".js-ulp-micro-question").each(function(){
						questions.push(jQuery(this).html());
				});
				ajaxData.the_questions = questions;
				do_decode = 1;
				break;
		}

		ajaxData.the_value = v;
		ajaxData.decode = do_decode;

	   	jQuery.ajax({
	        type : "post",
	        url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
	        data : ajaxData,
	        success: function (r) {
	        	/// some extra jobs
	        	if (do_submit==true){
		        	IndeedQuiz.submit();
	        	}
	        }
	   	});
	},
	makeDisabled: function(){
			var t = jQuery("[name=" + this.question_id + "]").attr('disabled', 'disabled');
	},
	returnCorrectOrWrong: function(){
		var c = '<div class="ulp-correct-answer-msg">' + this.correct_answer_msg + '</div>';
		var w = '<div class="ulp-wrong-answer-msg">' + this.wrong_answer_msg + '</div>';
		jQuery.ajax({
				type : "post",
				url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
				data : {
									 action: "ulp_question_return_correct_or_wrong",
									 question_id: this.question_id,
									 qid: this.quiz_id,
									 course_id: this.course_id,
							 },
				success: function (r) {
						if (r==1){
								jQuery('.ulp-question-responses').append(c);
						} else {
								jQuery('.ulp-question-responses').append(w);
						}
						if (IndeedQuiz.is_last()==1){
								//IndeedQuiz.insert_submit();
						} else {
								IndeedQuiz.insert_next();
						}
				}
		});
	},
	submit: function(){
	   	jQuery.ajax({
	        type : "post",
	        url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
	        data : {
	                   action: "ulp_submit_quiz",
	                   qid: this.quiz_id,
										 course_id: this.course_id,
	        },
	        success: function (r) {
	        	window.location.href = r;
	        }
	   	});
	},
	is_last: function(){
			return this.is_last_question;
	},
	insert_submit: function(){
			jQuery('.ulp-question-buttons-wrapper').append('<div class="ulp-quiz-submit-via-ajax" id="ulp_quiz_submit_via_ajax">' + this.submit_quiz_button + '</div>' );
	},
	insert_next: function(){
			jQuery('.ulp-question-buttons-wrapper').append( '<div class="ulp-quiz-next-button" id="ulp_quiz_next_question">' + this.next_bttn + '</div>');
	},
};

var IndeedCourse = {
	enroll: function(CourseId){
	   	jQuery.ajax({
	        type : "post",
	        url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
	        data : {
	                   action: "ulp_do_enroll_course",
	                   course_id: CourseId,
	        },
	        success: function (r){
						jQuery('#enroll_course_'+CourseId).remove();
						jQuery('#ulp_enroll_box_course_'+CourseId).append(r);
						setTimeout(function(){
								location.reload();
						}, 2000);
	        }
	   	});
	},
	finish: function(){
		var CourseId = jQuery('#ulp_finish_course_bttn').attr('data-course_id');
	   	jQuery.ajax({
	        type : "post",
	        url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
	        data : {
	                   action: "ulp_finish_course",
	                   course_id: CourseId,
	        },
	        success: function (r) {
							location.reload();
	        }
	   	});
	},

	retake: function(CourseId){
	   	jQuery.ajax({
	        type : "post",
	        url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
	        data : {
	                   action: "ulp_do_retake_course",
	                   course_id: CourseId,
	        },
	        success: function (r){
						jQuery('#ulp_retake_box_message_'+CourseId).append(r);
						setTimeout(function(){
								location.reload();
						}, 6000);
	        }
	   	});
	},


}
var IndeedLesson = {
	doComplete: function(){
		var LessonId = jQuery('#ulp_lesson_complete_bttn').attr('data-lesson_id');
		jQuery.ajax({
			type: 'post',
			url: decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
			data: {
	                   action: "ulp_complete_lesson",
	                   lesson_id: LessonId,
			},
			success: function(r){
				if (r==1){
					location.reload(); /// refresh
				}
			}
		});
	}
};
var IndeedQuestion = {
	showHint: function(){
		var d = jQuery('#ulp_the_hint').attr('class');
		if (d=='ulp-hint-hide'){
			jQuery('#ulp_the_hint').attr('class', 'ulp-hint-show');
		} else {
			jQuery('#ulp_the_hint').attr('class', 'ulp-hint-hide');
		}
	}
};
var UltimateLearningPro = {
	current_page: null,
	init: function(){
		if ( typeof ulp_messages == 'object' ){
				var ulp_general_messages = ulp_messages;
		} else {
				var ulp_general_messages = JSON.parse(ulp_messages);
		}

		//============= QUIZES
		/// START QUIZ
		jQuery('#ulp_start_quiz_bttn').on( 'click', function(){
			IndeedQuiz.start();
		});
		/// RETAKE QUIZ
		jQuery('#ulp_retake_quiz_bttn').on( 'click', function(){
			IndeedQuiz.start();
		});
		/// NEXT QUESTION
		jQuery(document).on('click', '#ulp_quiz_next_question', function(){
				IndeedQuiz.saveAnswer(false);
				IndeedQuiz.shift('forward');
		});
		/// PREV QUESTION
		jQuery(document).on('click', '#ulp_quiz_prev_question', function(){
				IndeedQuiz.saveAnswer(false);
				IndeedQuiz.shift('back');
		});
		/// SUBMIT QUIZ
		jQuery(document).on('click', '#ulp_submit_quiz_bttn', function(){
				IndeedQuiz.saveAnswer(true);
		});
		jQuery('#ulp_quiz_submit_via_ajax').on( 'click', function(){
				IndeedQuiz.saveAnswer(false);
				IndeedQuiz.makeDisabled();
				jQuery('#ulp_quiz_submit_via_ajax').remove();
				jQuery('#ulp_hint_link').remove();
				setTimeout(function(){
					IndeedQuiz.returnCorrectOrWrong();
				}, 1000);
		});
		/// show hint
		jQuery('#ulp_hint_link').on( 'click', function(){
			IndeedQuestion.showHint();
		});
		jQuery('#ulp_become_instructor').on( 'click', function(){
			jQuery.ajax({
				type: 'post',
				url: decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
				data: {
		    		action: "ulp_become_instructor_ajax",
				},
				success: function(r){
					jQuery('#ulp_become_instructor').remove();
					jQuery('.ulp-become-instructor-wrapp').append(window.become_instructor_message);
					location.reload(); /// refresh
				}
			});
		});
		//=============== COURSES
		/// FINISH COURSE
		jQuery('#ulp_finish_course_bttn').on( 'click', function(){
			IndeedCourse.finish();
		});
		jQuery('.ulp-enroll-course-the-button').on( 'click', function(){
			var course_id = jQuery(this).attr('data-cid');
			IndeedCourse.enroll(course_id);
		});

		jQuery('#ulp_retake_course_bttn').on( 'click', function(){
			var course_id = jQuery(this).attr('data-course_id');
			IndeedCourse.retake(course_id);
		});
		//============ LESSONS
		jQuery('#ulp_lesson_complete_bttn').on( 'click', function(){
			IndeedLesson.doComplete();
		});
		/// course rating
		jQuery('.ulp-star-item .fa-ulp').on('hover', function(e){
				var parent = jQuery(this).parent().parent().attr('class');
				ulpUnsetStars(parent);
				var num = jQuery(this).parent().attr('data-star_num');
				ulpSetStar(parent, num);
		});
		jQuery('.ulp-star-item .fa-ulp').on('mouseout', function(e){
				var parent = jQuery(this).parent().parent().attr('class');
				ulpUnsetStars(parent);
				if (jQuery('.js-ulp-course-rating').val()){
						ulpSetStar(parent, jQuery('.js-ulp-course-rating').val() );
				}
		});
		jQuery('.ulp-star-item .fa-ulp').on('click', function(e){
				var parent = jQuery(this).parent().parent().attr('class');
				var num = jQuery(this).parent().attr('data-star_num');
				ulpSetStar(parent, num);
				jQuery('.js-ulp-course-rating').val(num);
				return false;
		});
		/// notes
		jQuery('#note_course_id').on('click', ulpNoteOpenPopup);
		/// buy course
		jQuery('.js-ulp-pay-course-bttn').on('click', function(){
				var courseId = jQuery(this).attr('data-course_id');
				var paymentType = jQuery(this).attr('data-payment_type');
				jQuery.ajax({
						type: 'post',
						url: decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
						data: {
								action: 'ulp_buy_course_via_standard_bttn',
								course_id: courseId,
								payment_type: paymentType,
						},
						success: function(response){
								if (response==0 || response=='0'){
										swal({
													title: ulp_general_messages.error,
													text: ulp_general_messages.general_error,
													type: "error",
													showCancelButton: false,
													confirmButtonClass: "ulp-btn-danger",
													confirmButtonText: "Ok",
													closeOnConfirm: true
										});
								}	else {
										window.location.href = response;
								}
						}
				});
		});
		jQuery('#ulp_checkout').on('submit', function(){
				var got_type = jQuery('[name=payment_type]:checked').val();
				if (typeof got_type=='undefined'){
						swal({
								  title: ulp_general_messages.error,
								  text: ulp_general_messages.payment_type_error,
								  type: "error",
								  showCancelButton: false,
								  confirmButtonClass: "ulp-btn-danger",
								  confirmButtonText: "Ok",
								  closeOnConfirm: true
						});
						return false;
				}
				return true;
		});
	}, ///end of init
};
function ulpSetStar(parent, num){
	for (var i=1; i<=num; i++){
			jQuery('.' + parent + ' .i_'+i+ ' .fa-ulp').removeClass('fa-star-o-ulp').addClass('fa-star-ulp');
	}
}
function ulpUnsetStars(parent){
	for ( var i=1; i<6; i++){
			jQuery('.' + parent + ' .i_'+i+ ' .fa-ulp').removeClass('fa-star-ulp').addClass('fa-star-o-ulp');
	}
}
/// execute script
jQuery(function(){
	UltimateLearningPro.init();
});
///////////// NOTES
function ulpNoteOpenPopup(){
		jQuery.ajax({
				type : "post",
				url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
				data : {
									 action: "ulp_get_note_popup"
				},
				success: function (r) {
						jQuery('body').append(r);
				}
		});
}
function ulpOpenInvoice(i){
		jQuery.ajax({
				type : "post",
				url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
				data : {
									 action: "ulp_get_invoice_popup",
									 order_id: i
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
function ulpNoteSave(){
	jQuery.ajax({
			type : "post",
			url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
			data : {
								 action: "ulp_save_note",
								 course_id: jQuery('#note_course_id').attr('data-course_id'),
								 title: jQuery('#note_title').val(),
								 content: jQuery('#note_content').val(),
			},
			success: function (r) {
					ulpClosePopup();
			}
	});
}
function ulpLoveCourse(course, elem){
		var the_action = jQuery('#' + elem).attr('data-action');
		if (the_action=='add'){
				var t = 'ulp_add_to_watch_list';
		} else {
			  var t = 'ulp_remove_from_watch_list';
		}
		jQuery.ajax({
				type : "post",
				url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
				data : {
									 action: t,
									 course_id: course,
				},
				success: function (r) {
						if (the_action=='add'){
								jQuery('#'+elem).attr('class', '');
								jQuery('#'+elem).attr('class', 'ulp-watch-list-button ulp-watch-list-active');
								jQuery('#wishlist-icon').attr('class', '');
								jQuery('#wishlist-icon').attr('class', 'fa-ulp fa-watch_list-ulp');
								jQuery('#'+elem).attr('data-action', 'remove');
						} else {
							jQuery('#'+elem).attr('class', '');
							jQuery('#'+elem).attr('class', 'ulp-watch-list-button ulp-watch-list-noactive');
							jQuery('#wishlist-icon').attr('class', '');
							jQuery('#wishlist-icon').attr('class', 'fa-ulp fa-watch_list_not_assign-ulp');
							jQuery('#'+elem).attr('data-action', 'add');
						}
				}
		});
}
function ulpRemoveFromWatchList(e){
		jQuery.ajax({
				type : "post",
				url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
				data : {
									 action: 'ulp_remove_from_watch_list',
									 course_id: jQuery(e.target).attr('data-course_id'),
				},
				success: function(){
						course_id = jQuery(e.target).attr('data-course_id');
						jQuery('.ulp-remove-'+course_id).remove();
				}
		});
}
function ulpRemoveNote(e){
		jQuery.ajax({
				type : "post",
				url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
				data : {
									 action: 'ulp_delete_note',
									 id: jQuery(e.target).attr('data-id'),
				},
				success: function(r){
						jQuery(e.target).parent().remove();
				}
		});
}
//jQuery(document).on( 'ready', function(){
document.addEventListener("DOMContentLoaded", function() {
		jQuery('.js-remove-watch-list').on('click', ulpRemoveFromWatchList, this.element);
		jQuery('.js-ulp-remove-note').on('click', ulpRemoveNote, this.element);
		//jQuery('#ulp_checkout_payment_select input').on('click', ulpPaymentSelect, this.element);
});
function ulpOpenCertificate(certificateId){
	jQuery.ajax({
			type : "post",
			url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
			data : {
								 action: "ulp_get_certificate_popup",
								 user_certificate_id: certificateId
			},
			success: function (r) {
					jQuery('body').append(r);
			}
	});
}
function ulpPaymentSelect(e){
	return ;
		/*
		jQuery('#js_ulp_checkout_loading_gif').css('visibility', 'visible');
		if (jQuery(e.target).val()=='stripe'){
				jQuery.ajax({
						type : "post",
						url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
						data : {
											 action: 'ulp_get_stripe_payment_form',
						},
						success: function(r){
								if ( jQuery('#ulp_stripe_payment_form_fields').length==0 ){
										jQuery(e.target).parent().append(r);
										jQuery('#ulp_stripe_payment_form_fields').slideDown(500, function (){
												jQuery('#js_ulp_checkout_loading_gif').css('visibility', 'hidden');
										});
								} else {
										jQuery('#js_ulp_checkout_loading_gif').css('visibility', 'hidden');
								}
						}
				});
		} else {
				if ( jQuery('#ulp_stripe_payment_form_fields').length ){
						jQuery('#ulp_stripe_payment_form_fields').slideUp(500, function(){
								jQuery('#ulp_stripe_payment_form_fields').remove();
								jQuery('#js_ulp_checkout_loading_gif').css('visibility', 'hidden');
						});
				} else {
						jQuery('#js_ulp_checkout_loading_gif').css('visibility', 'hidden');
				}
		}
		*/
}
function ulpDeleteFileViaAjax(id, u_id, parent, name, hidden_id){
	var r = confirm("Are you sure you want to delete?");
	if (r) {
			var s = jQuery(parent).attr('data-h');
	   	jQuery.ajax({
	        type : "post",
	        url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
	        data : {
	                   action: "ulp_delete_attachment_ajax_action",
	                   attachemnt_id: id,
	                   user_id: u_id,
	                   field_name: name,
										 h: s
	        },
	        success: function (data) {
	        	jQuery(hidden_id).val('');
	        	jQuery(parent + ' .ajax-file-upload-filename').remove();
	        	jQuery(parent + ' .ulp-delete-attachment-bttn').remove();
	        	if (jQuery(parent + ' .ulp-member-photo').length){
	        		jQuery(parent + ' .ulp-member-photo').remove();
	        		if (name=='ulp_avatar'){
	        			jQuery(parent).prepend("<div class='ulp-no-avatar ulp-member-photo'></div>");
	        			jQuery(parent + " .ulp-file-upload").css("display", 'block');
	        		}
	        	}
	        	if (jQuery(parent + " .ulp-file-name-uploaded").length){
	        		jQuery(parent + " .ulp-file-name-uploaded").remove();
	        	}
	        	if (jQuery(parent + ' .ajax-file-upload-progress').length){
	        		jQuery(parent + ' .ajax-file-upload-progress').remove();
	        	}
	        	if (jQuery(parent + ' .ulp-icon-file-type').length){
	        		jQuery(parent + ' .ulp-icon-file-type').remove();
	        	}
	        }
	   });
	}
}

function ulpCheckAndH(s, t){
	if (jQuery(s).is(':checked')){
		jQuery(t).val(1);
	} else {
		jQuery(t).val(0);
	}
}

function ulpShowSelectorIf(target, check, value_to_compare){
		if (check==value_to_compare){
				jQuery(target).css('display', 'block');
				return;
		}
		jQuery(target).css('display', 'none');
}

function ulpDoModuleToggle(i){
	if ( typeof ulp_messages == 'object' ){
				var ulp_general_messages = ulp_messages;
	} else {
			var ulp_general_messages = JSON.parse(ulp_messages);
	}

		var display = jQuery('#ulp_module_content_inside_'+i).css('display');
		if (display=='none'){
				jQuery('#ulp_module_content_inside_'+i).slideDown( 500 );
				var str = '<i class="fa-ulp fa-toggle_up-ulp" title="' + ulp_general_messages.toggle_section + '" ></i>';
		} else {
				jQuery('#ulp_module_content_inside_'+i).slideUp( 500 );
				var str = '<i class="fa-ulp fa-toggle_down-ulp" title="' + ulp_general_messages.toggle_section + '" ></i>';
		}
		jQuery('#ulp_toggle_modukle_' + i ).html(str);
}

function ulpAddZero(i)
{
		if (i < 10) {
				i = "0" + i;
		}
		return i;
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
      attachment = custom_uploader.state().get('selection').first().toJSON();
      jQuery(target).val(attachment.url);
      if (img_target!=''){
      	jQuery(img_target).attr('src', attachment.url);
      	jQuery(img_target).css('display', 'block');
      }
  });
  //Open the uploader dialog
  custom_uploader.open();
}

function ulpInitPrinthis( selector )
{
		if ( typeof window.printhisopt != 'undefined' ){
				jQuery( selector ).printThis( window.printhisopt );
		}
}

//jQuery(document).on( 'ready', function() {
document.addEventListener("DOMContentLoaded", function() {

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

		// instructors - quizes special settings
		if ( jQuery( '.ulp-js-quizes-special-settings' ).length > 0 ){
				var quizGradeType = jQuery( this ).attr( 'data-quiz_grade_type' );
				ulpShowSelectorIf('#ulp_point_description', quizGradeType, 'point');
		}

		// instructors - include ckeditor
		if ( jQuery( '.ulp-js-add-edit-include-ckeditor' ).length > 0 ){
				var CKEDITOR_BASEPATH = jQuery( '.ulp-js-add-edit-include-ckeditor' ).attr( 'data-base_path' );
				CKEDITOR.replace( 'post_content' );
		}

		// instructor - comming soon message
		if ( jQuery( '.ulp-js-include-ckeditor-comming-soon-message' ).length > 0 ){
				var CKEDITOR_BASEPATH = jQuery( '.ulp-js-include-ckeditor-comming-soon-message' ).attr( 'data-base_path' );
				CKEDITOR.replace( 'ulp_course_coming_soon_message' );
		}

		// become instructor - message
		if ( jQuery( '.ulp-js-become-instructor-message' ).length > 0 ){
				var become_instructor_label = jQuery( '.ulp-js-become-instructor-message' ).attr('data-value');
				window.become_instructor_message = '<div class="ulp-instructor-pending-request">' + become_instructor_label + '</div>';
		}

		// form field - matching
		if ( jQuery( '.ulp-js-form-field-micro-answer' ).length && typeof UlpFormFieldMatching != 'undefined' ){
				var formFieldMatchingTitle = jQuery( '.ulp-js-form-field-micro-answer' ).attr( 'data-title' );
				var formFieldMatchingMessage = jQuery( '.ulp-js-form-field-micro-answer' ).attr( 'data-message' );
		    UlpFormFieldMatching.init({
		        droppableClass: '.ulp-micro-answer',
		        draggableClass: '.ulp-item',
		        possibleAnswersWrapp: '.ulp-micro-answers-possible',
		        standardTitleMessage: formFieldMatchingTitle,
		        extraTitleMessage: formFieldMatchingMessage,
		    });
		}

		// form fields - image
		if ( jQuery( '.ulp-js-form-field-image-single-choice' ).length && typeof UlpFormFieldSingleChoice != 'undefined' ){
				var name = jQuery( '.ulp-js-form-field-image-single-choice' ).attr('data-name');
				UlpFormFieldSingleChoice.init({
						'wrapper' 			: '.ulp-images-single-choice-wrapp .ulp-images-single-choice-one-item',
						'name' 					: name,
						'selectedClass' : 'selected'
				});
		}

		// instructor - datepicker
		if ( jQuery( '.ulp-datetime' ).length ){
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

		// instructors - course special settings
		if ( jQuery( '.ulp-js-instructor-courses-special-settings' ).length > 0 ){
				var ulpCourseAssesments = jQuery( '.ulp-js-instructor-courses-special-settings' ).attr( 'data-ulp_course_assessments' );
				var ulp_course_payment = jQuery( '.ulp-js-instructor-courses-special-settings' ).attr( 'data-ulp_course_payment' );
				ulpShowSelectorIf('#ulp_zuiq_average', ulpCourseAssesments, 'quizes');
				ulpShowSelectorIf('#ulp_course_price_num', ulp_course_payment, 1);
		}

		// instructor - drip content
		if ( jQuery( '#ulp_drip_start_certain_date' ).length ){
				jQuery('#ulp_drip_start_certain_date').datepicker({
						dateFormat : 'dd-mm-yy',
						onClose: function( selectedDate ){
								jQuery( "#ihc_drip_end_certain_date" ).datepicker( "option", "minDate", selectedDate );
						}
				});
			  ulpShowSelectorIf('#after_x_time', jQuery('.ulp-js-instructor-lesson-drip-content').attr('data-start_type'), 1);
			  ulpShowSelectorIf('#specific_date', jQuery('.ulp-js-instructor-lesson-drip-content').attr('data-start_type'), 2);
		}

		// instructor - settings
		if ( jQuery( '.ulp-js-file-upload-settings' ).length ){
				var rand = jQuery( '.ulp-js-file-upload-settings' ).attr( 'data-rand' );
				var url = jQuery( '.ulp-js-file-upload-settings' ).attr( 'data-url' );
				jQuery("#ulp_fileuploader_wrapp_" + rand + " .ulp-file-upload").uploadFile({
					onSelect: function (files) {
						jQuery("#ulp_fileuploader_wrapp_" + rand + " .ajax-file-upload-container").css("display", "block");
						var check_value = jQuery("#ulp_upload_hidden_" + rand ).val();
						if (check_value!="" ){
							alert("To add a new image please remove the previous one!");
							return false;
						}
						return true;
					},
					url: url,
					allowedTypes: "jpg,png,jpeg,gif",
					fileName: "avatar",
					dragDrop: false,
					showFileCounter: false,
					showProgress: true,
					onSuccess: function(a, response, b, c){
						if (response){
							var obj = jQuery.parseJSON(response);
							if (typeof obj.secret!="undefined"){
									jQuery("#ulp_fileuploader_wrapp_" + rand).attr("data-h", obj.secret);
							}
							jQuery("#ulp_upload_hidden_" + rand ).val(obj.id);
							var htmlData = "<div onClick=\"ulpDeleteFileViaAjax("+obj.id+", -1, \'#ulp_fileuploader_wrapp_" + rand + "\', \'' . 'ulp_avatar' . '\', \'#ulp_upload_hidden_" + rand + "\');\" class=\'ulp-delete-attachment-bttn\'>Remove</div>";
							jQuery("#ulp_fileuploader_wrapp_" + rand + " .ulp-file-upload").prepend( htmlData );
							jQuery("#ulp_fileuploader_wrapp_" + rand + " .ulp-file-upload").prepend("<img src="+obj.url+" class=\'ulp-member-photo\' /><div class=\'ulp-clear\'></div>");
							jQuery(".ulp-no-avatar").remove();
							setTimeout(function(){
								jQuery("#ulp_fileuploader_wrapp_" + rand + " .ajax-file-upload-container").css("display", "none");
							}, 3000);
						}
					}
			});
		}

		// course curriculum slider
		if ( jQuery( '.ulp-js-course-curriculum-slider' ).length > 0 ){
				const container = document.querySelector('.scroll-wrapper');
				const ps = new PerfectScrollbar(container);
		}

		// course review list
		if ( jQuery( '.ulp-js-course-review-list-init' ).length > 0 && typeof UlpReviews != 'undefined' ){
				var slug = jQuery( '.ulp-js-course-review-list-init' ).attr( 'data-slug' );
				var hash = jQuery( '.ulp-js-course-review-list-init' ).attr( 'data-hash' );
				UlpReviews.init({
							offset				: 10,
							slug					: slug,
							hash					: hash,
							wrapper				: '.ulp-course-review-list'
				});
		}

		// instructor - listing comments
		if ( jQuery( '.ulp-js-instructor-listing-comments' ).length > 0 ){
				CKEDITOR.replace( 'add_new_comment' );
		}

		// instructors - questions answers
		if ( jQuery( '.ulp-js-instructor-questions-answers' ).length > 0 ){
				jQuery('#ulpsrotingtype_wrapp').sortable({
					 update: function(e, ui) {
								jQuery('#ulpsrotingtype_wrapp div').each( function (i, row) {} );
					 }
				});
		}

		// lesson - complete bttn
		if ( jQuery( '.ulp-js-lesson-complete-bttn' ).length > 0 ){
				var secondsRemain = jQuery( '.ulp-js-lesson-complete-bttn' ).attr( 'data-seconds_remain' );
				var timer = new Timer();
				secondsRemain = parseInt( secondsRemain );
				timer.start({countdown: true, startValues: { seconds: secondsRemain }});
				timer.addEventListener('secondsUpdated', function (e) {
					var units = ['minutes', 'seconds'];
					var seconds_remains = secondsRemain;

					if( seconds_remains > 3600){
							units = ['hours', 'minutes', 'seconds'];
					}
					if( seconds_remains > 86400){
							units = ['days','hours', 'minutes', 'seconds'];
					}

					jQuery('#ulp_lesson_countdown').html(timer.getTimeValues().toString(units));
				});
				timer.addEventListener('targetAchieved', function (e) {
					IndeedLesson.doComplete();
				});
		}

		// quiz - countdown
		if ( jQuery( '.ulp-js-the-countdown' ).length > 0 ){
				var secondsRemain = jQuery( '.ulp-js-the-countdown' ).attr( 'data-seconds_remain' );
				var timer = new Timer();
				secondsRemain = parseInt( secondsRemain );

				timer.start( { countdown: true, startValues: { seconds: secondsRemain } } );

				timer.addEventListener('secondsUpdated', function (e) {
					var units = ['minutes', 'seconds'];
					var seconds_remains = secondsRemain;

					if( seconds_remains > 3600){
							units = ['hours', 'minutes', 'seconds'];
					}
					if( seconds_remains > 86400){
							units = ['days','hours', 'minutes', 'seconds'];
					}
						jQuery('#ulp_quiz_countdown').html(timer.getTimeValues().toString(units));
				});

				timer.addEventListener('targetAchieved', function (e) {
					IndeedQuiz.saveAnswer(true);
				});
		}

		// quiz - retake button
		if ( jQuery( '.ulp-js-retake-bttn' ).length > 0 ){
				var quizId = jQuery( '.ulp-js-retake-bttn' ).attr( 'data-quiz_id' );
				var courseId = jQuery( '.ulp-js-retake-bttn' ).attr( 'data-course_id' );
				IndeedQuiz.quiz_id = quizId;
				IndeedQuiz.course_id = courseId;
		}

		// quiz - start button
		if ( jQuery( '.ulp-js-quiz-start-bttn' ).length > 0 ){
				var quizId = jQuery( '.ulp-js-quiz-start-bttn' ).attr( 'data-quiz_id' );
				var courseId = jQuery( '.ulp-js-quiz-start-bttn' ).attr( 'data-course_id' );
				IndeedQuiz.quiz_id = quizId;
				IndeedQuiz.course_id = courseId;
		}

		// quiz - submit button
		if ( jQuery( '.ulp-js-quiz-submit-bttn' ).length > 0 ){
				var quizId = jQuery( '.ulp-js-quiz-submit-bttn' ).attr( 'data-quiz_id' );
				var courseId = jQuery( '.ulp-js-quiz-submit-bttn' ).attr( 'data-course_id' );
				IndeedQuiz.quiz_id = quizId;
				IndeedQuiz.course_id = courseId;
		}

		// single course coming soon
		if ( jQuery( '.ulp-js-single-course-coming-soon' ).length > 0 ){
			var theData = jQuery( '.ulp-js-single-course-coming-soon' ).attr( 'data-date' );
			var untilTimestamp = jQuery( '.ulp-js-single-course-coming-soon' ).attr( 'data-until_timestamp' );
			jQuery('.ulp-countdown').downCount({
					date 						: theData,
					until_timestamp : untilTimestamp
			});
		}

		// sorting field type
		if ( jQuery( '.ulp-js-sorting-field-type' ).length > 0 ){
			jQuery('.ulp-sortable-field-ul').sortable({
				 update: function(e, ui) {
						 jQuery('.ulp-sortable-field-ul li').each(function (i, row) {});
				 }
			});
		}

		// student profile - header
		if ( jQuery( '.ulp-js-student-profile-header' ).length > 0 ){
			var urlTarget = jQuery( '.ulp-js-student-profile-header' ).attr( 'data-url' );
			UlpAccountPageBanner.init({
					triggerId					: 'js_ulp_edit_top_ap_banner',
					saveImageTarget		: urlTarget,
					cropImageTarget   : urlTarget,
					bannerClass       : 'ulp-user-page-top-background'
			});
		}

		// student profile - upload image
		if ( jQuery( '.ulp-js-student-profile-upload-image' ).length > 0 ){
				var trigger_id = jQuery( '.ulp-js-student-profile-upload-image' ).attr( 'data-trigger_id' );
				var url = jQuery( '.ulp-js-student-profile-upload-image' ).attr( 'data-url' );
				var name = jQuery( '.ulp-js-student-profile-upload-image' ).attr( 'data-name' );
				var hidden_input_selector = jQuery( '.ulp-js-student-profile-upload-image' ).attr( 'data-hidden_input_selector' );
				var remove_image_selector = jQuery( '.ulp-js-student-profile-upload-image' ).attr( 'data-remove_image_selector' );
				var bttn_label = jQuery( '.ulp-js-student-profile-upload-image' ).attr( 'data-bttn_label' );

				UlpAvatarCroppic.init({
						triggerId					           : trigger_id,
						saveImageTarget		           : url,
						cropImageTarget              : url,
						imageSelectorWrapper         : '.ulp-upload-image-wrapp',
						hiddenInputSelector          : hidden_input_selector,
						imageClass                   : 'ulp-member-photo',
						removeImageSelector          : remove_image_selector,
						buttonId 					           : 'ulp-avatar-button',
						buttonLabel 			           : bttn_label,
				});
		}

		// single question
		if ( jQuery( '.ulp-js-single-question-data' ).length > 0 ){
				var quiz_id = jQuery( '.ulp-js-single-question-data' ).attr( 'data-quiz_id' );
				var course_id = jQuery( '.ulp-js-single-question-data' ).attr( 'data-course_id' );
				var question_id = jQuery( '.ulp-js-single-question-data' ).attr( 'data-question_id' );
				var is_last_question = jQuery( '.ulp-js-single-question-data' ).attr( 'data-is_last_question' );
				var correct_answer_msg = jQuery( '.ulp-js-single-question-data' ).attr( 'data-correct_answer_msg' );
				var wrong_answer_msg = jQuery( '.ulp-js-single-question-data' ).attr( 'data-wrong_answer_msg' );
				var submit_quiz_button = jQuery( '.ulp-js-single-question-data' ).attr( 'data-submit_quiz_button' );
				var next_bttn = jQuery( '.ulp-js-single-question-data' ).attr( 'data-next_bttn' );

				IndeedQuiz.quiz_id = quiz_id;
				IndeedQuiz.course_id = course_id;
				IndeedQuiz.question_id = question_id;
				IndeedQuiz.is_last_question = is_last_question;
				IndeedQuiz.correct_answer_msg = correct_answer_msg;
				IndeedQuiz.wrong_answer_msg = wrong_answer_msg;
				IndeedQuiz.submit_quiz_button = submit_quiz_button;
				IndeedQuiz.next_bttn = next_bttn;
		}

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

});

jQuery( document ).ajaxSend(function (event, jqXHR, ajaxOptions) {
		if ( typeof ajaxOptions.data !== 'string' ||  ajaxOptions.data.includes( 'action=ulp' ) === false ){
				return;
		}

		if ( typeof ajaxOptions.url === 'string' && ajaxOptions.url.includes('/admin-ajax.php')) {
			 var token = jQuery('meta[name="ulp-token"]').attr("content");
			 jqXHR.setRequestHeader('X-CSRF-ULP-TOKEN', token );
		}
});

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

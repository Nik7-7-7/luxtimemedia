/*
* Ultimate Learning Pro - Questions and Answers Form Functions
*/
"use strict";
var QandAForm = {
  submitSelector  : '',
  formSelector    : '',

  init: function(args){
      var obj = this;
      obj.setAttributes(obj, args);

      //jQuery(document).on( 'ready', function(){
      document.addEventListener("DOMContentLoaded", function() {
          jQuery(obj.showFormSelector).on('click', function(){
              obj.showForm(obj);
          })
          jQuery(obj.hideFormSelector).on('click', function(){
              obj.hideForm(obj);
          })
          jQuery(obj.submitSelector).on('click', function(evt){
              evt.preventDefault();
              obj.handleFormSubmit(evt, obj);
          });
      });

  },

  setAttributes: function(obj, args){
      for (var key in args) {
        obj[key] = args[key];
      }
  },

  handleFormSubmit: function(evt, obj){
      jQuery.ajax({
          type    : "post",
          url     : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
          data    : {
                     action    : "ulp_save_qanda_question",
                     course    : jQuery(obj.formSelector).attr('data-course'),
                     title     : jQuery(obj.formSelector).find('[name=title]').val(),
                     content   : jQuery(obj.formSelector).find('[name=content]').val(),
                     hash      : jQuery(obj.formSelector).attr('data-hash'),
                     courseId  : jQuery(obj.formSelector).attr('data-course-id'),
          },
          success : function (response) {
              var redirect = jQuery(obj.formSelector).attr('data-redirect');
              window.location.href = redirect;
          }
      })
  },

  appendQuestionIntoList: function(obj, question){
      if (jQuery(obj.noItemsSelector).length){
          jQuery(obj.noItemsSelector).fadeOut(100);
      }
      jQuery(obj.listWrapper).append(question);
  },

  showForm: function(obj){
      jQuery(obj.formSelector).removeClass('ulp-display-none');
      jQuery(obj.hideFormSelector).removeClass('ulp-display-none');
      jQuery(obj.showFormSelector).addClass('ulp-display-none');
  },

  hideForm: function(obj){
      jQuery(obj.formSelector).addClass('ulp-display-none');
      jQuery(obj.hideFormSelector).addClass('ulp-display-none');
      jQuery(obj.showFormSelector).removeClass('ulp-display-none');
  },

}
QandAForm.init({
    submitSelector    : '#ulp_submit_qanda_question',
    formSelector      : '#ulp_qanda_form',
    listWrapper       : '.ulp-qanda-list-wrapper',
    noItemsSelector   : '.ulp-additional-message',
    showFormSelector  : '#ulp_qanda_show_form',
    hideFormSelector  : '#ulp_qanda_hide_form',
})

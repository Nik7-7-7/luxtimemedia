/*
* Ultimate Learning Pro - Questions and Answers Search Functions
*/
"use strict";
var QandAForm = {
  searchInputSelector  : '',
  listWrapper          : '',
  loadMoreButton       : '',

  init: function(args){
      var obj = this;
      obj.setAttributes(obj, args);

      //jQuery(document).on( 'ready', function(){
      document.addEventListener("DOMContentLoaded", function() {
          jQuery(document).on('keyup', obj.searchInputSelector, function (evt) {
              obj.handleSearch(evt, obj);
          });
      });

  },

  setAttributes: function(obj, args){
      for (var key in args) {
        obj[key] = args[key];
      }
  },

  handleSearch: function(evt, obj){
      jQuery.ajax({
          type    : "post",
          url     : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
          data    : {
                     action       : "ulp_search_qanda_question",
                     course       : jQuery(obj.searchInputSelector).attr('data-course'),
                     hash         : jQuery(obj.searchInputSelector).attr('data-hash'),
                     substring    : jQuery(obj.searchInputSelector).val(),
          },
          success : function (response) {
              response = JSON.parse(response);
              obj.appendQuestionsIntoList(obj, response.html);
          }
      });
  },

  appendQuestionsIntoList: function(obj, question){
      jQuery(obj.listWrapper).empty();
      if (jQuery(obj.noItemsSelector).length){
          jQuery(obj.noItemsSelector).fadeOut(100);
      }
      jQuery(obj.listWrapper).append(question);
      if (question.length){
          obj.showBttn(obj);
      }
  },

  showBttn: function(obj){
      jQuery(obj.loadMoreButton).css('display', 'block');
  },

}
QandAForm.init({
    searchInputSelector    : '#ulp_course_qanda_search_bar',
    listWrapper            : '.ulp-qanda-list-wrapper',
    loadMoreButton         : '#ulp_course_qanda_load_more',
})

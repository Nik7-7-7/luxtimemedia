/*
* Ultimate Learning Pro - Instructor Functions
*/
"use strict";
var UlpInstructorActions = {

  init: function(args){
      var obj = this;
      obj.setAttributes(obj, args);
      if ( typeof ulp_messages == 'object' ){
          obj.ulp_messages = ulp_messages;
      } else {
          obj.ulp_messages = JSON.parse(ulp_messages);
      }

          jQuery(obj.deleteSelector).on('click', function(evt){
              obj.handleDelete(evt, obj);
          });
  },

  setAttributes: function(obj, args){
      for (var key in args) {
        obj[key] = args[key];
      }
  },

  handleDelete: function(evt, obj){
      swal({
        title: "",
        text: obj.ulp_messages.delete_post,
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: obj.ulp_messages.delete_it,
        closeOnConfirm: true
      },
      function(){
          jQuery.ajax({
              type : "post",
              url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
              data : {
                         action     : "ulp_instructor_delete_post",
                         post       : jQuery(obj.deleteSelector).attr('data-post'),
                     },
              success: function (response) {
                  if (response && response!='0'){
                      location.reload();
                  } else {
                      swal({
                        title: "",
                        text: obj.ulp_messages.cannot_delete,
                        type: "warning",
                        showCancelButton: false,
                        confirmButtonClass: "btn-danger",
                        confirmButtonText: 'Ok',
                        closeOnConfirm: true
                      });
                  }
              }
          });
      });
  },
}

//jQuery(document).on( 'ready', function(){
document.addEventListener("DOMContentLoaded", function() {
    UlpInstructorActions.init({
        'deleteSelector'      : '.js-ulp-do-delete-post'
    });
});

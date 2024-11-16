/*
* Ultimate Learning Pro - Comments Functions
*/
"use strict";
var UlpCommentsBox = {
    openBoxSelector       : '',
    formSelector          : '',
    hideFormSelector      : '',
    submitFormSelector    : '',
    deleteCommentSelector : '',
    loadMoreButton        : '',
    listCommentsWrapper   : '',
    limit                 : 10,
    offset                : 0,

    init: function(args){
        var obj = this;
        obj.setAttributes(obj, args);

        //jQuery(document).on( 'ready', function(){
        document.addEventListener("DOMContentLoaded", function() {
            obj.limit = jQuery(obj.loadMoreButton).attr('data-limit');
            obj.offset = obj.limit;

            jQuery(obj.openBoxSelector).on('click', function(){
                obj.showForm(obj);
            })
            jQuery(obj.hideFormSelector).on('click', function(){
                obj.hideForm(obj);
            })
            jQuery(obj.submitFormSelector).on('click', function(){
                obj.submitForm(obj);
            })
            jQuery(obj.deleteCommentSelector).on('click', function(evt){
                obj.doDeleteComment(obj, evt);
            })
            jQuery(obj.loadMoreButton).on('click', function(evt){
                obj.handleViewMore(evt, obj);
            });
        });
    },

    setAttributes: function(obj, args){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

    showForm: function(obj){
        jQuery(obj.formSelector).removeClass('ulp-display-none');
        jQuery(obj.hideFormSelector).removeClass('ulp-display-none');
        jQuery(obj.openBoxSelector).parent().addClass('ulp-display-none');
    },

    hideForm: function(obj){
        jQuery(obj.formSelector).addClass('ulp-display-none');
        jQuery(obj.hideFormSelector).addClass('ulp-display-none');
        jQuery(obj.openBoxSelector).parent().removeClass('ulp-display-none');
    },

    submitForm: function(obj){
      jQuery.ajax({
          type    : "post",
          url     : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
          data    : {
                     action    : "ulp_save_comment",
                     content   : jQuery(obj.formSelector).find('[name=content]').val(),
                     hash      : jQuery(obj.formSelector).attr('data-hash'),
                     postId    : jQuery(obj.formSelector).attr('data-post-id'),
          },
          success : function (response) {
              location.reload();
          }
      });
    },

    doDeleteComment: function(obj, evt){
        jQuery.ajax({
              type    : "post",
              url     : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
              data    : {
                         action     : "ulp_delete_comment",
                         comment    : jQuery(evt.target).attr('data-comment'),
              },
              success : function (response) {
                  if (response==1){
                      location.reload();
                  }
              }
        });
    },

    handleViewMore: function(evt, obj){
      jQuery.ajax({
          type : "post",
          url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
          data : {
                     action    : "ulp_load_more_comments",
                     postId    : jQuery(obj.formSelector).attr('data-post-id'),
                     hash      : jQuery(obj.formSelector).attr('data-hash'),
                     offset    : obj.offset,
                     limit     : obj.limit,
          },
          success: function (r) {
              if (r && r!='0'){
                  jQuery(obj.listCommentsWrapper).append(r);
                  // update the offset
                  obj.updateOffset(evt, obj);
              } else {
                  obj.hideBttn(evt, obj);
              }
          }
      });
    },


    hideBttn: function(evt, obj){
        jQuery(obj.loadMoreButton).css('display', 'none');
    },

    updateOffset: function(evt, obj){
        obj.offset += obj.limit;
    }

}

UlpCommentsBox.init({
    openBoxSelector       : '.ulp-js-add-new-comment',
    formSelector          : '.ulp-comment-form',
    hideFormSelector      : '.ulp-js-hide-comment-form',
    submitFormSelector    : '.ulp-js-submit-comment',
    deleteCommentSelector : '.js-ulp-do-delete-comment',
    loadMoreButton        : '.js-ulp-load-more-comments',
    listCommentsWrapper   : '.ulp-list-comments',
})

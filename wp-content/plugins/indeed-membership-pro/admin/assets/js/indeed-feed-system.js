/*
* Ultimate Membership Pro - Nonce
*/
"use strict";
function indeed_close_notf_div(){
          var token = jQuery('meta[name="ump-admin-token"]').attr("content");
          jQuery.ajax({
              headers: { "X-CSRF-UMP-ADMIN-TOKEN" : token },
              type : "post",
              url : jQuery( '.ihc-js-indeed-feed-system-data').attr( 'data-site_url') + '/wp-admin/admin-ajax.php',
              data : {
                         action: "indeed_update_notify_date_show",
              },
              success: function (r){
                jQuery('#indeed_main_notify').fadeOut();
              }
         });
}

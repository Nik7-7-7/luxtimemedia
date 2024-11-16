/*
* Ultimate Membership Pro - Manage page functionality
*/
"use strict";
window.ihc_messages = {
        email_server_check: jQuery( '.ihc-js-admin-messages' ).attr( 'data-email_server_check' )
};
jQuery(document).ajaxSend(function(b,c,a){"string"===typeof a.data&&!1!==a.data.includes("action=ihc")&&"string"=== typeof a.url&&a.url.includes("/admin-ajax.php")&&(b=jQuery('meta[name="ump-admin-token"]').attr("content"),c.setRequestHeader("X-CSRF-UMP-ADMIN-TOKEN",b))});

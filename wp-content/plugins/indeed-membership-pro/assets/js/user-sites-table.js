/*
* Ultimate Membership Pro - User sites
*/
"use strict";
function ihcDoUsersiteModuleDelete(i){
  var question = jQuery( '.ihc-js-user-sites-table-data' ).attr( 'data-current_question' );
	var c = confirm( question );
	if (c){
		jQuery.ajax({
			type : "post",
		    url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
		    data : {
		             action  : "ihc_do_user_delete_blog",
					       lid     : i
		    },
		    success: function (r){
          var currentUrl = window.location.href;
          // do a refresh
		    	window.location.href = currentUrl;
		    }
		});
	}
}

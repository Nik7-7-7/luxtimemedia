/*
* Ultimate Membership Pro - Social Functions
*/
"use strict";
function ihcRemoveSocial(t){
	jQuery.ajax({
		type : "post",
	    url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
	    data : {
	             action: "ihc_remove_sm_from_user",
	             type: t,
	    },
	    success: function (r) {
	    	location.reload();
	    }
	});
}

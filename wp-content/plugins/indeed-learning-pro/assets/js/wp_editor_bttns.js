/*
* Ultimate Learning Pro - Wp Editor Buttons Functions
*/
"use strict";
function ulpClosePopup(){
	jQuery('#popup_box').fadeOut(300, function(){
		jQuery(this).remove();
	});
}
(function (){
	////////////// REGISTER, LOGIN, LOGOUT
	tinymce.PluginManager.add('ulp_button_forms', function(ed, url) {
        // Add a button that opens a window
        ed.addButton('ulp_button_forms', {
            icon: 'ulp_btn_forms',
			      title : 'Ultimate Learning Pro ShortCodes',
            type: "button",
            text : "",
            cmd : "ulp_forms_popup"
        });
        ///LOAD POPUP
        ed.addCommand('ulp_forms_popup', function() {
	         url = url.replace('assets/js', '');
	    	 jQuery.ajax({
	    	     type : "post",
	    	     url : decodeURI(window.ulp_url)+'/wp-admin/admin-ajax.php',
	    	     data : {
	    	                action: "ulp_ajax_admin_popup_shortcodes",
	    	            },
	    	     success: function (data) {
	    	        	 	jQuery(data).hide().appendTo('body').fadeIn('normal');
	    	     }
	    	 });
        });
        ed.addCommand('ulp_return_text', function(text){
        	ed.execCommand('mceInsertContent', 0, text);
        	ulpClosePopup();
        });
    });
})();

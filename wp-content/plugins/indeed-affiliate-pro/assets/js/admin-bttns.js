/*
 *Ultimate Affiliate Pro - WP Editor buttons
 */
(function (){
	"use strict";
	tinymce.PluginManager.add('uap_button_forms', function(ed, url) {
        // Add a button that opens a window
        ed.addButton('uap_button_forms', {
            icon: 'uap_btn_forms',
						title : 'Affiliates ShortCodes',
            type: "button",
            text : "",
            cmd : "uap_forms_popup"
        });

        ///LOAD POPUP
        ed.addCommand('uap_forms_popup', function() {
		         url = url.replace('assets/js', '');
			    	 jQuery.ajax({
			    	     type : "post",
			    	     url : decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
			    	     data : {
			    	                action: "uap_ajax_admin_popup_the_shortcodes",
			    	     },
			    	     success: function (data) {
			    	    	 jQuery(data).hide().appendTo('body').fadeIn('normal');
			    	     }
			    	 });
        });

        ed.addCommand('uap_return_text', function(text){
	        	ed.execCommand('mceInsertContent', 0, text);
	        	uapClosePopup();
        });

				// constants for notifications
				if ( jQuery('.uap-tag-code').length > 0 ){
						jQuery('.uap-tag-code').on('click', function(){
								ed.execCommand('mceInsertContent', 0, jQuery(this).html() );
						});
				}
				// end of constants 

    });
})();

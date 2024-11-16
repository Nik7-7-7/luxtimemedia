/*
* Ultimate Membership Pro - WP Editor button
*/
"use strict";
(function (){
	////////////////////LOCKER
	tinymce.PluginManager.add('ihc_button_locker', function(ed, url) {
        // Add a button that opens a window
        ed.addButton('ihc_button_locker', {
            icon		: 'ihc_btn_locker',
						title 	: 'Ultimate Membership Pro - Inside Locker',
            type		: "button",
            text		: "",
            cmd			: "ihc_locker_popup",
            menu		: false,
        });

        ed.addCommand('ihc_insert_shortcode', function(param){
        	returnText = '[ihc-hide-content]'+ed.selection.getContent()+'[/ihc-hide-content]';
            ed.execCommand('mceInsertContent', 0, returnText);
        });

        ///LOAD POPUP
       ed.addCommand('ihc_locker_popup', function() {
				 	var token = jQuery('meta[name="ump-admin-token"]').attr("content");
    	   	url = url.replace('assets/js', '');
    	   	jQuery.ajax({
							 headers: { "X-CSRF-UMP-ADMIN-TOKEN" : token },
    	         type : "post",
    	         url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
    	         data : {
    	                    action: "ihc_ajax_admin_popup",
    	                },
    	         success: function (data) {
    	        	 			jQuery(data).hide().appendTo('body').fadeIn('normal');
    	         }
    	    });
       });

       ///POPUP FORM SUBMIT
       ed.addCommand('ihc_insert_locker_shortcode', function(){
           var selectedText = ed.selection.getContent();
           var returnText = '[ihc-hide-content ';//START SHORTCODE TAG

           returnText += 'ihc_mb_type="'+jQuery('#ihc_mb_type-shortcode').val()+'" ';
           returnText += 'ihc_mb_who="'+jQuery('#ihc_mb_who-shortcode').val()+'" ';
		   var templ = '';
		   if( jQuery('#ihc_mb_template-shortcode').val() ){
				templ = jQuery('#ihc_mb_template-shortcode').val();
		   }
		   returnText += 'ihc_mb_template="'+templ+'" ';

           returnText += ']' + selectedText + '[/ihc-hide-content]';//END SHORTCODE TAG
           ed.execCommand('mceInsertContent', 0, returnText);
           ihcClosePopup();
       });

			 // constants for editor
			 if ( window.location.href.indexOf( 'admin.php?page=ihc_manage&tab=notifications' ) !== -1 ){
				 		// notifications
						// observer - notification tag may be changed
						jQuery( document ).on( "ajaxComplete", function( event, xhr, settings ) {
							 if ( settings.data.indexOf("action=ihc_update_list_notification_constants&notificationType=") > -1 ) {
									 //if ( jQuery('.iump-tag-code').length > 0 ){
			 								//console.log(settings.data);
			 								jQuery('.iump-tag-code').on('click', function(){
			 										var target = jQuery( this ).attr( 'data-target_selector' );
			 										if ( ed.id === target ){
			 											 ed.execCommand('mceInsertContent', 0, jQuery(this).html() );
			 										}
			 								});
			 						//}
							 }
						 } );

				}

						if ( jQuery('.iump-tag-code').length > 0 ){
								jQuery('.iump-tag-code').on('click', function(){
										var target = jQuery( this ).attr( 'data-target_selector' );
										if ( ed.id === target ){
											 ed.execCommand('mceInsertContent', 0, jQuery(this).html() );
										}
								});
						}

			 // end of editor


    });
})();


(function (){
	////////////// REGISTER, LOGIN, LOGOUT
	tinymce.PluginManager.add('ihc_button_forms', function(ed, url) {
        // Add a button that opens a window
        ed.addButton('ihc_button_forms', {
            icon: 'ihc_btn_forms',
						title : 'Membership ShortCodes',
            type: "button",
            text : "",
            cmd : "ihc_forms_popup"
        });

        ///LOAD POPUP
        ed.addCommand('ihc_forms_popup', function() {
					 var token = jQuery('meta[name="ump-admin-token"]').attr("content");
	         url = url.replace('assets/js', '');
		    	 jQuery.ajax({
						 	 headers: { "X-CSRF-UMP-ADMIN-TOKEN" : token },
		    	     type : "post",
		    	     url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
		    	     data : {
		    	                action: "ihc_ajax_admin_popup_the_forms",
		    	            },
		    	     success: function (data) {
		    	        	 	jQuery(data).hide().appendTo('body').fadeIn('normal');
		    	     }
		    	 });
        });

        ed.addCommand('ihc_return_text', function(text){
        	ed.execCommand('mceInsertContent', 0, text);
        	ihcClosePopup();
        });

    });
})();

/*
* Ultimate Learning Pro - Admin Functions ( send email )
*/
"use strict";
var UlpAdminSendEmail = {
  popupAjax		       : '',
  sendEmailAjax	     : '',
  ajaxPath           : '',
  openPopupSelector  : '',
  sendEmailSelector  : '',
  fromSelector       : '',
  toSelector         : '',
  subjectSelector    : '',
  messageSelector    : '',

  init: function(args){
    var obj = this;
    obj.setAttributes(obj, args);

    //jQuery(document).on( 'ready', function(){
    document.addEventListener("DOMContentLoaded", function() {
        jQuery(obj.openPopupSelector).on('click', function(evt){
            obj.handleOpenPopup(obj, evt);
        });
        jQuery(document).on("click", obj.sendEmailSelector,function(evt){
           obj.handleSendEmail(obj, evt);
        });
        jQuery(document).on("click", obj.closePopupBttn,function(){
           obj.handleClosePopup(obj);
        });
    });
  },

	setAttributes: function(obj, args){
		for (var key in args) {
			obj[key] = args[key];
		}
	},

  handleOpenPopup: function(obj, evt){
    jQuery.ajax({
        type    : "post",
        url     : decodeURI(obj.ajaxPath) + '/wp-admin/admin-ajax.php',
        data    : {
                   action    : obj.popupAjax,
                   uid       : jQuery(evt.target).attr('data-uid'),
        },
        success : function (response) {
            jQuery('body').append(response);
        }
    });
  },

  handleSendEmail: function(obj, evt){
    jQuery.ajax({
        type    : "post",
        url     : decodeURI(obj.ajaxPath) + '/wp-admin/admin-ajax.php',
        data    : {
                   action    : obj.sendEmailAjax,
                   to        : jQuery(obj.toSelector).val(),
                   from      : jQuery(obj.fromSelector).val(),
                   subject   : jQuery(obj.subjectSelector).val(),
                   message   : jQuery(obj.messageSelector).val(),
        },
        success : function (response) {
            if (response){
                obj.handleClosePopup(obj);
            }
        }
    });
  },

  handleClosePopup: function(obj){
      jQuery(obj.popupWrapp).remove();
  },

}

UlpAdminSendEmail.init({
    popupAjax		       : 'ulp_admin_send_email_popup',
  	sendEmailAjax	     : 'ulp_admin_do_send_email',
  	ajaxPath           : decodeURI(window.ulp_url),
    openPopupSelector  : '.ulp-admin-do-send-email-via-ulp',
    sendEmailSelector  : '#indeed_admin_send_mail_submit_bttn',
    fromSelector       : '#indeed_admin_send_mail_from',
    toSelector         : '#indeed_admin_send_mail_to',
    subjectSelector    : '#indeed_admin_send_mail_subject',
    messageSelector    : '#indeed_admin_send_mail_content',
    closePopupBttn     : '#ulp_send_email_via_admin_close_popup_bttn',
    popupWrapp         : '#ulp_admin_popup_box',
});

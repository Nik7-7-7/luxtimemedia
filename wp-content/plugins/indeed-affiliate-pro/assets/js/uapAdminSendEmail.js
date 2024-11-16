/*
 *Ultimate Affiliate Pro - Send Direct Email box
 */

"use strict";
var uapAdminSendEmail = {
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

    jQuery(obj.openPopupSelector).on('click', function(evt){
        obj.handleOpenPopup(obj, evt);
    });
    jQuery(document).on("click", obj.sendEmailSelector,function(evt){
       obj.handleSendEmail(obj, evt);
    });

    jQuery(document).on("click", obj.closePopupBttn,function(){
       obj.handleClosePopup(obj);
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
    })
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

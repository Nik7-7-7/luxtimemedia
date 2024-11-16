/*
 *Ultimate Affiliate Pro - Account Page Banner
 */
"use strict";
var UapAccountPageBanner = {
    triggerId       : '',
    saveImageTarget : '',
    cropImageTarget : '',
    bannerClass     : '',

    init: function(args){
        var obj = this;
        obj.setAttributes(obj, args);
            var options = {
              uploadUrl                 : obj.saveImageTarget,
              cropUrl                   : obj.cropImageTarget,
              modal                     : true,
              fileNameInput             : 'uap_upload_image_top_banner',
              imgEyecandyOpacity        : 0.4,
              loaderHtml                : '<div class="loader cssload-wrapper"><div id="floatingCirclesG"><div class="f_circleG" id="frotateG_01"></div><div class="f_circleG" id="frotateG_02"></div><div class="f_circleG" id="frotateG_03"></div><div class="f_circleG" id="frotateG_04"></div><div class="f_circleG" id="frotateG_05"></div><div class="f_circleG" id="frotateG_06"></div><div class="f_circleG" id="frotateG_07"></div><div class="f_circleG" id="frotateG_08"></div></div>',
              onBeforeImgUpload         : function(){},
              onAfterImgUpload          : function(){},
              onImgDrag                 : function(){},
              onImgZoom                 : function(){},
              onBeforeImgCrop           : function(){},
              onAfterImgCrop            : function(response){ obj.handleAfterImageCrop(obj, response); },
              onAfterRemoveCroppedImg   : function(){ obj.handleRemove(obj); },
              onError                   : function(e){ console.log('onError:' + e); }
            }
            var cropperHeader = new Croppic(obj.triggerId, options);

    },

    setAttributes: function(obj, args){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

    handleAfterImageCrop: function(obj, response){
        if (response.status=='success'){
            jQuery('.'+obj.bannerClass).css('background-image', response.url);
        }
    },

    handleRemove: function(obj){
        var old = jQuery('.' + obj.bannerClass).attr('data-banner');
        jQuery.ajax({
            type : "post",
            url : decodeURI(ajax_url),
            data : {
                       action: "uap_ap_reset_custom_banner",
                       oldBanner: old,
                   },
            success: function (data) {
            	jQuery('.' + obj.bannerClass).css('background-image', old);
            }
       	});
    }
}

window.addEventListener( 'DOMContentLoaded', function(){
    var uapNonce = jQuery( '.uap-js-account-page-header-details' ).attr( 'data-nonce' );
    var uapUrl = jQuery( '.uap-js-account-page-header-details' ).attr( 'data-uap_url' );
		UapAccountPageBanner.init({
				triggerId					: 'js_uap_edit_top_ap_banner',
				saveImageTarget		: decodeURI( ajax_url ) + '?action=uap_ajax_public_upload&publicn=' + uapNonce,
				cropImageTarget   : decodeURI( ajax_url ) + '?action=uap_ajax_public_upload&publicn=' + uapNonce,
				bannerClass       : 'uap-user-page-top-background'
		})
});

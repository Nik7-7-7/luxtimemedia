/*
* Ultimate Membership Pro - My Account banner
*/
"use strict";
var IhcAccountPageBanner = {
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
              fileNameInput             : 'ihc_upload_image_top_banner',
              imgEyecandyOpacity        : 0.4,
              loaderHtml                : '<div class="loader cssload-wrapper"><div id="floatingCirclesG"><div class="f_circleG" id="frotateG_01"></div><div class="f_circleG" id="frotateG_02"></div><div class="f_circleG" id="frotateG_03"></div><div class="f_circleG" id="frotateG_04"></div><div class="f_circleG" id="frotateG_05"></div><div class="f_circleG" id="frotateG_06"></div><div class="f_circleG" id="frotateG_07"></div><div class="f_circleG" id="frotateG_08"></div></div>',
              onBeforeImgUpload         : function(){},
              onAfterImgUpload          : function(){},
              onImgDrag                 : function(){},
              onImgZoom                 : function(){},
              onBeforeImgCrop           : function(){},
              onAfterImgCrop            : function(response){ obj.handleAfterImageCrop(obj, response); },
              onAfterRemoveCroppedImg   : function(){ obj.handleRemove(obj); },
              onError                   : function(e){ console.log('onError:' + e); },
              imageAppendAfter          : false,
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
            jQuery('.'+obj.bannerClass).css('background-image', 'url('+response.url+')' );
        }
    },

    handleRemove: function(obj){
        var old = jQuery('.' + obj.bannerClass).attr('data-banner');
        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
            data : {
                       action: "ihc_ap_reset_custom_banner",
                       oldBanner: old,
                   },
            success: function (data) {
            	jQuery('.' + obj.bannerClass).css('background-image', 'url('+response.url+')' );
            }
       	});
    }


}

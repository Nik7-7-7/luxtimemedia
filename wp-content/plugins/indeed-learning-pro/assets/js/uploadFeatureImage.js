/*
* Ultimate Learning Pro - Feature Image Functions
*/
"use strict";
var UlpUploadFeatureImage = {
    triggerId                 : '',
    saveImageTarget           : '',
    cropImageTarget           : '',
    bannerClass               : '',
    featureImageInputHidden   : '',

    init: function(args){
        var obj = this;
        obj.setAttributes(obj, args);
            var options = {
              uploadUrl                 : obj.saveImageTarget,
              cropUrl                   : obj.cropImageTarget,
              modal                     : true,
              fileNameInput             : 'ulp_upload_image_top_banner',
              imgEyecandyOpacity        : 0.4,
              loaderHtml                : '<div class="loader bubblingG"><span id="bubblingG_1"></span><span id="bubblingG_2"></span><span id="bubblingG_3"></span></div>',
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
            jQuery('#'+obj.featureImageInputHidden).val(response.url);
            jQuery('#'+obj.triggerId).removeClass('ulp-add-image').addClass('ulp-edit-image');
        }

    },

    handleRemove: function(obj){

    }

}

//jQuery(document).on( 'ready', function(){
document.addEventListener("DOMContentLoaded", function() {
    UlpUploadFeatureImage.init({
    	  		triggerId			      			: 'js_ulp_upload_image_trigger',
    	  		saveImageTarget		  			: window.ulp_plugin_url + "ajax_upload.php?do_upload_image=true",
    	  		cropImageTarget   				: window.ulp_plugin_url + "ajax_upload.php?do_upload_image=true",
    	  		bannerClass       				: '', /// ulp-feat-image-preview
    				featureImageInputHidden 	: 'ulp_feat_image_input',
            oldPicSelector            : 'ulp-feat-img-old-pic',
    });
});

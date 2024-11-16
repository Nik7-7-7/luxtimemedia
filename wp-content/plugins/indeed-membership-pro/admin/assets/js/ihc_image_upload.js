/*
* Ultimate Membership Pro - Upload Image
*/
"use strict";
var IhcImageUpload = {
    triggerId                   : '',
    saveImageTarget             : '',
    cropImageTarget             : '',
    hiddenInputSelector         : '',
    standardImage               : '',
	  buttonId					          : '',
	  buttonLabel					        : '',

    init: function(args){
        var obj = this;
        obj.setAttributes(obj, args);
        var cropperHeader = obj.initCroppic(obj);
        jQuery( obj.removeImageSelector ).on( 'click', function(){
      	    //cropperHeader.reset();
      	    obj.handleRemove( obj );
        });
    },

    setAttributes: function(obj, args){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

    initCroppic: function(obj){
      var options = {
        uploadUrl                 : obj.saveImageTarget,
        cropUrl                   : obj.cropImageTarget,
        modal                     : true,
        imgEyecandyOpacity        : 0.4,
        loaderHtml                : '<div class="loader cssload-wrapper"><div id="floatingCirclesG"><div class="f_circleG" id="frotateG_01"></div><div class="f_circleG" id="frotateG_02"></div><div class="f_circleG" id="frotateG_03"></div><div class="f_circleG" id="frotateG_04"></div><div class="f_circleG" id="frotateG_05"></div><div class="f_circleG" id="frotateG_06"></div><div class="f_circleG" id="frotateG_07"></div><div class="f_circleG" id="frotateG_08"></div></div>',
        onBeforeImgUpload         : function(){},
        onAfterImgUpload          : function(response){ obj.handleImgUpload( obj, response ); },//obj.handleImgUpload(obj, response) },
        onImgDrag                 : function(){},
        onImgZoom                 : function(){},
        onBeforeImgCrop           : function(){},
        onAfterImgCrop            : function(response){ obj.handleAfterImageCrop(obj, response); },
        onAfterRemoveCroppedImg   : function(){ obj.handleRemove(obj, false); },
        onReset                   : function(){ obj.handleRemove(obj, true); },
        onError                   : function(e){ console.log('onError:' + e); },
        imgW                      : 900,
        imgH                      : 300,
        objW                      : 900,
        objH                      : 300,
        imgInitH                  : 900,
        imgInitW                  : 300,
        imageAppendAfter          : false,
        customIdentificator       : 'image',
        customUploadButtonId      : obj.buttonId,
      }
      var cropperHeader = new Croppic(obj.triggerId, options);
      return cropperHeader;
    },

    handleAfterImageCrop: function(obj, response){
        if (response.status=='success'){
            jQuery( obj.imageSelectorWrapper ).html( '' );
            jQuery(obj.hiddenInputSelector).val( response.url );
            jQuery( obj.imageSelectorWrapper ).append( '<img src="' + response.url + '" class="' + obj.imageClass + '" />' );
			      jQuery( obj.removeImageSelector ).css('display', 'block');
        }
    },

    handleRemove: function(obj, isReset){
        jQuery( obj.removeImageSelector ).css( 'display', 'none' );
        jQuery( '.ihc-upload-bttn-wrapp' ).css( 'display', 'block' );
        jQuery( obj.imageSelectorWrapper ).html( '' );
        jQuery(obj.hiddenInputSelector).val( '' );
        jQuery( '.ihc-avatar-trigger' ).removeClass( 'ihc-display-none' );
    },

    handleImgUpload: function(obj, response){
        if (typeof(response)!='undefined' && response.uploadId){
            jQuery(obj.hiddenInputSelector).val( response.uploadId );
        }
		    jQuery( obj.removeImageSelector ).css('visibility', 'visible');
    },

}

/*
 *Ultimate Affiliate Pro - Crop Avatar Image
 */
"use strict";
var UapAvatarCroppic = {
    triggerId                   : '',
    saveImageTarget             : '',
    cropImageTarget             : '',
    hiddenInputSelector         : '',
    standardImage               : '',
	  buttonId					: '',
	  buttonLabel					: '',

    init: function(args){
        var obj = this;
        obj.setAttributes(obj, args);
            var cropperHeader = obj.initCroppic(obj);
            jQuery( obj.removeImageSelector ).on( 'click', function(){
      			    cropperHeader.reset();
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
        imgW                      : 450,
        imgH                      : 450,
        objW                      : 250,
        objH                      : 250,
        imgInitH                  : 120,
        imgInitW                  : 120,
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
            jQuery( obj.hiddenInputSelector ).val( response.uploadId );
            jQuery( obj.imageSelectorWrapper ).append( '<img src="' + response.url + '" class="' + obj.imageClass + '" />' );
			      jQuery( obj.removeImageSelector ).css('visibility', 'visible');
            jQuery( obj.removeImageSelector ).removeClass('uap-visibility-hidden');
        }
    },

    handleRemove: function(obj, isReset){
        var newUser = jQuery( obj.hiddenInputSelector ).attr( 'data-new_user' );
        if ( newUser ){
            /// ajax remove picture
            jQuery.ajax({
                type      : "post",
                url       : decodeURI(ajax_url),
                data      : {
                     action			 : 'uap_remove_media_post',
                     postId			 : jQuery( obj.hiddenInputSelector ).val()
                },
                success   : function (response) {

                }
            });
        }
        jQuery( obj.imageSelectorWrapper ).html( '' );
		    jQuery( obj.imageSelectorWrapper ).append( '<div class="uap-no-avatar uap-member-photo"></div>' );
		    jQuery( obj.removeImageSelector ).css('visibility', 'hidden');
        jQuery( obj.hiddenInputSelector ).val( '' );
        if (isReset)
		        jQuery( '#' + obj.triggerId ).append( '<div id="uap-avatar-button" class="uap-upload-avatar">'+obj.buttonLabel+'</div>' );

        if ( obj.standardImage ){
            jQuery( obj.imageSelectorWrapper ).append( '<img src="' + obj.standardImage + '" class="' + obj.imageClass + '" />' );
            jQuery( '.' + obj.avatarClass ).css( 'background-image', 'url(' + obj.standardImage + ')' );
        }
    },

    handleImgUpload: function(obj, response){
        if (typeof(response)!='undefined' && response.uploadId){
            jQuery(obj.hiddenInputSelector).val( response.uploadId );
        }
		    jQuery( obj.removeImageSelector ).css('visibility', 'visible');

        jQuery( obj.removeImageSelector ).removeClass('uap-visibility-hidden');
    },

}

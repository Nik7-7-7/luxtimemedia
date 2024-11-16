/*
 *Ultimate Affiliate Pro - Upload Image
 */
"use strict";
window.addEventListener( 'DOMContentLoaded', function(){
      jQuery( '.uap-js-public-upload-image-data' ).each(function(e,html){
      UapAvatarCroppic.init({
          triggerId					           : jQuery( html ).attr( 'data-trigger_id' ),
          saveImageTarget		           : jQuery( html ).attr( 'data-save_image_target' ),
          cropImageTarget              : jQuery( html ).attr( 'data-crop_image_target' ),
          imageSelectorWrapper         : '.uap-upload-image-wrapp',
          hiddenInputSelector          : '[name=' + jQuery( html ).attr( 'data-name' ) + ']',
          imageClass                   : 'uap-member-photo',
          removeImageSelector          : jQuery( html ).attr( 'data-remove_selector' ),
          buttonId 					           : 'uap-avatar-button',
          buttonLabel 			           : jQuery( html ).attr( 'data-button_label' ),
      });
    });
});

/*
* Ultimate Membership Pro - Main Functions
*/
"use strict";
function ihcBuyNewLevel(href){
  // will redirect to checkout page
	window.location.href = href;
}

function ihcClosePopup(){
  jQuery('#popup_box').remove();
}

jQuery(document).ajaxSend(function (event, jqXHR, ajaxOptions) {
    if ( typeof ajaxOptions.data !== 'string' ||  ajaxOptions.data.includes( 'action=ihc' ) === false ){
        return;
    }
    if ( typeof ajaxOptions.url === 'string' && ajaxOptions.url.includes('/admin-ajax.php')) {
       var token = jQuery('meta[name="ump-token"]').attr("content");
       jqXHR.setRequestHeader('X-CSRF-UMP-TOKEN', token );
    }
});

window.addEventListener( 'load', function(){
      // account page - mobile bttn
      jQuery('.ihc-mobile-bttn').on('click', function(){
        jQuery('.ihc-ap-menu').toggle();
      });

      // account page - banner
      if ( jQuery( ".ihc-js-account-page-account-banner-data" ).length ){
          IhcAccountPageBanner.init({
              triggerId					: 'js_ihc_edit_top_ap_banner',
              saveImageTarget		: jQuery( '.ihc-js-account-page-account-banner-data' ).attr( 'data-url_target' ),
              cropImageTarget   : jQuery( '.ihc-js-account-page-account-banner-data' ).attr( 'data-url_target' ),
              bannerClass       : 'ihc-user-page-top-ap-background'
          });
      }
});

/*
* Ultimate Learning Pro - Cookies Functions
*/
"use strict";
//jQuery( document ).on( 'ready', function(){
document.addEventListener("DOMContentLoaded", function() {
    var cookieName = jQuery( '.ulp-js-cookie-data' ).attr( 'data-cookie_name' );
    var cookieValue = jQuery( '.ulp-js-cookie-data' ).attr( 'data-cookie_value' );
    var cookieTime = jQuery( '.ulp-js-cookie-data' ).attr( 'data-cookie_time' );
    document.cookie = cookieName + '=' + cookieValue + '; expires=' + cookieTime + '; path=/';
});

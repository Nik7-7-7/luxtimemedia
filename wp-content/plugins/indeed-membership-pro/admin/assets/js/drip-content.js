/*
* Ultimate Membership Pro - Drip Content
*/
"use strict";
jQuery(window).on('load', function(){

    jQuery( '.js-ump-select-drip-content-start-time' ).on( 'change', function(){
        var currentValue = jQuery( '.js-ump-select-drip-content-start-time' ).val();
        switch ( currentValue ){
            case '1':
              jQuery( '.js-ump-select-drip-content-start-time-after-subscription' ).css( 'display', 'none' );
              jQuery( '.js-ump-select-drip-content-start-time-on-specific-date' ).css( 'display', 'none' );
              break;
            case '2':
              jQuery( '.js-ump-select-drip-content-start-time-after-subscription' ).css( 'display', 'block' );
              jQuery( '.js-ump-select-drip-content-start-time-on-specific-date' ).css( 'display', 'none' );
              break;
            case '3':
              jQuery( '.js-ump-select-drip-content-start-time-after-subscription' ).css( 'display', 'none' );
              jQuery( '.js-ump-select-drip-content-start-time-on-specific-date' ).css( 'display', 'block' );
              break;
        }
    });

    jQuery( '.js-ump-select-drip-content-end-time' ).on( 'click', function(){
        var currentValue = jQuery( '.js-ump-select-drip-content-end-time' ).val();
        switch ( currentValue ){
          case '1':
            jQuery( '.js-ump-select-drip-content-end-time-after-subscription' ).css( 'display', 'none' );
            jQuery( '.js-ump-select-drip-content-end-time-on-specific-date' ).css( 'display', 'none' );
            break;
          case '2':
            jQuery( '.js-ump-select-drip-content-end-time-after-subscription' ).css( 'display', 'block' );
            jQuery( '.js-ump-select-drip-content-end-time-on-specific-date' ).css( 'display', 'none' );
            break;
          case '3':
            jQuery( '.js-ump-select-drip-content-end-time-after-subscription' ).css( 'display', 'none' );
            jQuery( '.js-ump-select-drip-content-end-time-on-specific-date' ).css( 'display', 'block' );
            break;
        }
    });

    jQuery('#ihc_drip_start_certain_date').datepicker({
        dateFormat : 'dd-mm-yy',
        onClose: function( selectedDate ){
          jQuery( "#ihc_drip_end_certain_date" ).datepicker( "option", "minDate", selectedDate );
        }
    });

    jQuery('#ihc_drip_end_certain_date').datepicker({
        dateFormat : 'dd-mm-yy',
        onClose: function( selectedDate ) {
          jQuery( "#ihc_drip_start_certain_date" ).datepicker( "option", "maxDate", selectedDate );
        }
    });

});

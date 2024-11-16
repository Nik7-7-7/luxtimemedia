/*
 * Ultimate Affiliate Pro - Payout Setup
 */
"use strict";
var UapPayoutSetup = {

  init: function(args){
      var obj = this;

      // affiliate search
      if ( jQuery( ".uap-js-payout-setup-search-for-affiliates" ).length > 0 ){
          jQuery( ".uap-js-payout-setup-search-for-affiliates" ).on( "keydown", function( event ) {
              if ( event.keyCode === jQuery.ui.keyCode.TAB && jQuery( this ).autocomplete( "instance" ).menu.active ) {
                  event.preventDefault();
              }
          }).autocomplete({
              minLength: 0,
              source: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php?action=uap_ajax_offers_autocomplete&users=true&uapAdminAjaxNonce=' + window.uapAdminAjaxNonce,
              focus: function() {},
              select: function( event, ui ) {
                  var input_id = '#usernames_search_hidden';
                  var terms = uap_split(jQuery(input_id).val());//get items from input hidden
                  var v = ui.item.id;
                  var l = ui.item.label;
                  if (!uapContains(terms, v)){
                      terms.push(v);
                      // print the new shiny box
                      uapAutocompleteWriteTag(v, input_id, '#uap_username_search_tags', 'uap_username_tag_', l);
                   }
                  var str_value = terms.join( "," );
                  jQuery(input_id).val(str_value);//send to input hidden
                  this.value = '';//reset search input
                  return false;
              }
          });
      }

      // select target referrals - all / older than/ custom range
      if ( jQuery( '[name=select_referrals]' ).length > 0 ){
          jQuery( '[name=select_referrals]' ).on('change', function(){
              if ( jQuery( this ).val() === 'custom_range' ){
                  jQuery( '.uap-js-payout-setup-updaid-referrals-custom-range' ).removeClass('uap-display-none').addClass( 'uap-display-block' );
              } else {
                  jQuery( '.uap-js-payout-setup-updaid-referrals-custom-range' ).removeClass('uap-display-block').addClass( 'uap-display-none' );
              }
          });
      }
      // end of - select target referrals - all / older than/ custom range

      // custom range - datepicker
      if ( jQuery( '.uap-js-payout-setup-select-custom-range-value' ).length > 0 ){
          jQuery( '.uap-js-payout-setup-select-custom-range-value' ).on( 'change', function(){
              if ( jQuery( this ).val() === 'custom' ){
                  jQuery( '.uap-js-payout-setup-date-pickers' ).removeClass('uap-display-none').addClass( 'uap-display-block' );
              } else {
                  jQuery( '.uap-js-payout-setup-date-pickers' ).removeClass('uap-display-block').addClass( 'uap-display-none' );
              }
          });
      }
      // end of custom range - datepicker

      // select target affiliates - all or just some
      if ( jQuery( '[name=select_affiliates]' ).length > 0 ){
          jQuery( '[name=select_affiliates]' ).on('change', function(){
              if ( jQuery( this ).val() === 'selected_affiliates' ){
                  // specific affiliates
                  jQuery( '.uap-js-select-specific-affiliates' ).removeClass( 'uap-display-none' ).addClass( 'uap-display-block' );
              } else {
                  // all affiliates
                  jQuery( '.uap-js-select-specific-affiliates' ).removeClass( 'uap-display-block' ).addClass( 'uap-display-none' );
              }
          });
      }
      // end of select target affiliates - all or just some

      // referrals - custom range - date picker
      // start
      if ( jQuery( '.uap-js-payout-setup-referrals-custom-range-start' ).length > 0 ){
          jQuery( '.uap-js-payout-setup-referrals-custom-range-start' ).datepicker({
                dateFormat : 'yy-mm-dd ',
                onSelect: function( datetext ){
                    var d = new Date();
                    datetext = datetext+d.getHours()+":"+uapAddZero(d.getMinutes())+":"+uapAddZero(d.getSeconds());
                    jQuery(this).val(datetext);
                }
          });
      }
      if ( jQuery( '.uap-js-payout-setup-referrals-custom-range-end' ).length > 0 ){
          jQuery( '.uap-js-payout-setup-referrals-custom-range-end' ).datepicker({
                dateFormat : 'yy-mm-dd ',
                onSelect: function( datetext ){
                    var d = new Date();
                    datetext = datetext+d.getHours()+":"+uapAddZero(d.getMinutes())+":"+uapAddZero(d.getSeconds());
                    jQuery(this).val(datetext);
                }
          });
      }
      // end of referrals - custom range - date picker

      // generate CSV file on single payout
      if ( jQuery( '.uap-js-single-payout-generate-csv' ).length ){

          jQuery( '.uap-js-single-payout-generate-csv' ).on('click', function(){
              jQuery( '.uap-js-payouts-csv-file').removeClass('uap-display-block').addClass('uap-display-none');
              var payoutID = jQuery(this).attr('data-id');
              jQuery.ajax({
                      type 			: 'post',
                      url 			: decodeURI(ajax_url),
                      data 			: {
                                 action		            : 'uap_ajax_payouts_generate_csv',// custom
                                 uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                 payout_id   			    : payoutID,
                      },
                      success		: function (response) {
                         if ( response == 0 ){
                             return;
                         }
                         jQuery( '.uap-js-payouts-csv-file-for-payout-' + payoutID).removeClass('uap-display-none').addClass('uap-display-inline')
                         jQuery( '.uap-js-payouts-csv-file-for-payout-' + payoutID).attr( 'href', response );
                      }
              });
          });
      }
      // end of generate CSV file on single payout

      // change status for payment
      if ( jQuery( '.uap-js-payment-change-status').length ){
          jQuery( '.uap-js-payment-change-status').on( 'click', function(){
              var status = jQuery( '.uap-js-single-payment-new-status' ).val();
              var paymentId = jQuery( this ).attr('data-id');
              jQuery.ajax({
                      type 			: 'post',
                      url 			: decodeURI(ajax_url),
                      data 			: {
                                 action		              : 'uap_ajax_payments_change_status',// custom
                                 uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                 id			                : paymentId,
                                 status                 : status
                      },
                      success		: function (response) {
                          location.reload();
                      }
              });
          });
      }
      // end of - change status for payment

  },

}

jQuery( window ).on('load', function(){
    UapPayoutSetup.init([]);
});

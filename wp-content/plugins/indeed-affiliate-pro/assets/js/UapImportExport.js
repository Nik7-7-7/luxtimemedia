/*
 *Ultimate Affiliate Pro - CSV Export
 */

"use strict";

var UapImportExport = {

    triggerSelector             : '.js-uap-export-csv',
    loadingClass                : 'uap-export-process-border',

    init: function( args ){
        var obj = this;
        obj.setAttributes( obj, args );
        window.addEventListener( 'DOMContentLoaded', function(){
            jQuery( obj.triggerSelector ).on( 'click', function( evt ){
                obj.handleExport( obj, evt );
            });

            // --------------------- Affiliates --------------------
            // multi select for export affiliates - ranks
            if ( jQuery( '.uap-js-import-export-affiliates-filter-ranks' ).length ){
                jQuery( '.uap-js-import-export-affiliates-filter-ranks' ).multiselect({
                    selectAll: true,
                    placeholder: jQuery('.uap-js-import-export-affiliates-filter-ranks').attr( 'data-placeholder' ),
                    onOptionClick: function( element, option ){
                        var selectedRanks = jQuery('.uap-js-import-export-affiliates-filter-ranks').val();
                        var keyword = jQuery( '.uap-js-search-phrase' ).val();
                        var filters = { ranks_in: selectedRanks, search_phrase: keyword };
                        jQuery('.uap-js-affiliates-export-run-bttn').attr( 'data-filters', JSON.stringify(filters) );
                    }
                });
            }

            // search by name - affiliates
            if ( jQuery( '.uap-js-search-phrase' ).length ){
                jQuery( '.uap-js-search-phrase' ).on( 'keyup', function(){
                      var selectedRanks = jQuery('.uap-js-import-export-affiliates-filter-ranks').val();
                      var keyword = jQuery( '.uap-js-search-phrase' ).val();
                      var filters = { ranks_in: selectedRanks, search_phrase: keyword };
                      jQuery('.uap-js-affiliates-export-run-bttn').attr( 'data-filters', JSON.stringify(filters) );
                });
            }
            /// --------------------- end of Affiliates --------------------


            /// --------------------- Referrals --------------------

            // multi select for export referrals - status
            if ( jQuery( '.uap-js-import-export-referrals-filter-status' ).length ){
                jQuery( '.uap-js-import-export-referrals-filter-status' ).multiselect({
                    selectAll: true,
                    placeholder: jQuery('.uap-js-import-export-referrals-filter-status').attr( 'data-placeholder' ),
                    onOptionClick: function( evt ){
                        var status = jQuery('.uap-js-import-export-referrals-filter-status').val();
                        var keyword = jQuery( '.uap-js-search-phrase-referrals' ).val();
                        var sourceIn = jQuery( '.uap-js-import-export-referrals-filter-source' ).val();
                        var startDate = jQuery( '.uap-js-referrals-start-date' ).val();
                        var endDate = '';
                        if (jQuery( '.uap-js-referrals-end-date' ).val()){
                          endDate = jQuery( '.uap-js-referrals-end-date' ).val()+'23:59:59';
                        }

                        var filters = { status_in: status, search_phrase: keyword, sources_in: sourceIn, start_time: startDate, end_time: endDate };
                        jQuery('.uap-js-referrals-export-run-bttn').attr( 'data-filters', JSON.stringify(filters) );
                    }
                });
            }

            // multi select for export referrals - status
            if ( jQuery( '.uap-js-import-export-referrals-filter-source' ).length ){
                jQuery( '.uap-js-import-export-referrals-filter-source' ).multiselect({
                    selectAll: true,
                    placeholder: jQuery('.uap-js-import-export-referrals-filter-source').attr( 'data-placeholder' ),
                    onOptionClick: function( evt ){
                        var status = jQuery('.uap-js-import-export-referrals-filter-status').val();
                        var keyword = jQuery( '.uap-js-search-phrase-referrals' ).val();
                        var sourceIn = jQuery( '.uap-js-import-export-referrals-filter-source' ).val();
                        var startDate = jQuery( '.uap-js-referrals-start-date' ).val();
                        var endDate = '';
                        if (jQuery( '.uap-js-referrals-end-date' ).val()){
                          endDate = jQuery( '.uap-js-referrals-end-date' ).val()+'23:59:59';
                        }

                        var filters = { status_in: status, search_phrase: keyword, sources_in: sourceIn, start_time: startDate, end_time: endDate };
                        jQuery('.uap-js-referrals-export-run-bttn').attr( 'data-filters', JSON.stringify(filters) );
                    }
                });
            }

            // search by name - referrals
            if ( jQuery( '.uap-js-search-phrase-referrals' ).length ){
                jQuery( '.uap-js-search-phrase-referrals' ).on( 'keyup', function(){
                    var status = jQuery('.uap-js-import-export-referrals-filter-status').val();
                    var keyword = jQuery( '.uap-js-search-phrase-referrals' ).val();
                    var sourceIn = jQuery( '.uap-js-import-export-referrals-filter-source' ).val();
                    var startDate = jQuery( '.uap-js-referrals-start-date' ).val();
                    var endDate = '';
                    if (jQuery( '.uap-js-referrals-end-date' ).val()){
                      endDate = jQuery( '.uap-js-referrals-end-date' ).val()+'23:59:59';
                    }

                    var filters = { status_in: status, search_phrase: keyword, sources_in: sourceIn, start_time: startDate, end_time: endDate };
                    jQuery('.uap-js-referrals-export-run-bttn').attr( 'data-filters', JSON.stringify(filters) );
                });
            }

            // change start time - referrals
            if ( jQuery('.uap-js-referrals-start-date').length ){
                jQuery('.uap-js-referrals-start-date').datepicker({
                      dateFormat : 'yy-mm-dd ',
                      onSelect: function(datetext){
                        var status = jQuery('.uap-js-import-export-referrals-filter-status').val();
                        var keyword = jQuery( '.uap-js-search-phrase-referrals' ).val();
                        var sourceIn = jQuery( '.uap-js-import-export-referrals-filter-source' ).val();
                        var startDate = jQuery( '.uap-js-referrals-start-date' ).val();
                        var endDate = '';
                        if (jQuery( '.uap-js-referrals-end-date' ).val()){
                          endDate = jQuery( '.uap-js-referrals-end-date' ).val()+'23:59:59';
                        }

                        var filters = { status_in: status, search_phrase: keyword, sources_in: sourceIn, start_time: startDate, end_time: endDate };
                        jQuery('.uap-js-referrals-export-run-bttn').attr( 'data-filters', JSON.stringify(filters) );
                      }
                });
            }

            // change end time - referrals
            if ( jQuery('.uap-js-referrals-end-date').length ){
                jQuery('.uap-js-referrals-end-date').datepicker({
                      dateFormat : 'yy-mm-dd ',
                      onSelect: function(datetext){
                        var status = jQuery('.uap-js-import-export-referrals-filter-status').val();
                        var keyword = jQuery( '.uap-js-search-phrase-referrals' ).val();
                        var sourceIn = jQuery( '.uap-js-import-export-referrals-filter-source' ).val();
                        var startDate = jQuery( '.uap-js-referrals-start-date' ).val();
                        var endDate = '';
                        if (jQuery( '.uap-js-referrals-end-date' ).val()){
                          endDate = jQuery( '.uap-js-referrals-end-date' ).val()+'23:59:59';
                        }

                        var filters = { status_in: status, search_phrase: keyword, sources_in: sourceIn, start_time: startDate, end_time: endDate };
                        jQuery('.uap-js-referrals-export-run-bttn').attr( 'data-filters', JSON.stringify(filters) );
                      }
                });
            }
            /// --------------------- end of Referrals --------------------

            // ---------------------- Visits ------------------------
            // search by name - Visits
            if ( jQuery( '.uap-js-search-phrase-visits' ).length ){
                jQuery( '.uap-js-search-phrase-visits' ).on( 'keyup', function(){
                  var status = jQuery('.uap-js-import-export-visits-filter-status').val();
                  var keyword = jQuery( '.uap-js-search-phrase-visits' ).val();
                  var startDate = jQuery( '.uap-js-visits-start-date' ).val();
                  var endDate = '';
                  if (jQuery( '.uap-js-visits-end-date' ).val()){
                    endDate = jQuery( '.uap-js-visits-end-date' ).val()+'23:59:59';
                  }
                  var filters = { status_in: status, search_phrase: keyword,  start_time: startDate, end_time: endDate };
                  jQuery( '.uap-js-visits-export-run-bttn' ).attr( 'data-filters', JSON.stringify(filters) );
                });
            }

            // multi select for export Visits - status
            if ( jQuery( '.uap-js-import-export-visits-filter-status' ).length ){
                jQuery( '.uap-js-import-export-visits-filter-status' ).multiselect({
                    selectAll: true,
                    placeholder: jQuery('.uap-js-import-export-visits-filter-status').attr( 'data-placeholder' ),
                    onOptionClick: function( evt ){
                        var status = jQuery('.uap-js-import-export-visits-filter-status').val();
                        var keyword = jQuery( '.uap-js-search-phrase-visits' ).val();
                        var startDate = jQuery( '.uap-js-visits-start-date' ).val();
                        var endDate = '';
                        if (jQuery( '.uap-js-visits-end-date' ).val()){
                          endDate = jQuery( '.uap-js-visits-end-date' ).val()+'23:59:59';
                        }

                        var filters = { status_in: status, search_phrase: keyword,  start_time: startDate, end_time: endDate };
                        jQuery('.uap-js-visits-export-run-bttn').attr( 'data-filters', JSON.stringify(filters) );
                    }
                });
            }

            // change start time - Visits
            if ( jQuery('.uap-js-visits-start-date').length ){
                jQuery('.uap-js-visits-start-date').datepicker({
                      dateFormat : 'yy-mm-dd ',
                      onSelect: function(datetext){
                        var status = jQuery('.uap-js-import-export-visits-filter-status').val();
                        var keyword = jQuery( '.uap-js-search-phrase-visits' ).val();
                        var startDate = jQuery( '.uap-js-visits-start-date' ).val();
                        var endDate = '';
                        if (jQuery( '.uap-js-visits-end-date' ).val()){
                          endDate = jQuery( '.uap-js-visits-end-date' ).val()+'23:59:59';
                        }

                        var filters = { status_in: status, search_phrase: keyword,  start_time: startDate, end_time: endDate };
                        jQuery('.uap-js-visits-export-run-bttn').attr( 'data-filters', JSON.stringify(filters) );
                      }
                });
            }

            // change end time - Visits
            if ( jQuery('.uap-js-visits-end-date').length ){
                jQuery('.uap-js-visits-end-date').datepicker({
                      dateFormat : 'yy-mm-dd ',
                      onSelect: function(datetext){
                        var status = jQuery('.uap-js-import-export-visits-filter-status').val();
                        var keyword = jQuery( '.uap-js-search-phrase-visits' ).val();
                        var startDate = jQuery( '.uap-js-visits-start-date' ).val();
                        var endDate = '';
                        if (jQuery( '.uap-js-visits-end-date' ).val()){
                          endDate = jQuery( '.uap-js-visits-end-date' ).val()+'23:59:59';
                        }

                        var filters = { status_in: status, search_phrase: keyword,  start_time: startDate, end_time: endDate };
                        jQuery('.uap-js-visits-export-run-bttn').attr( 'data-filters', JSON.stringify(filters) );
                      }
                });
            }
            // ------------------------- end of Visits ------------------


            // ------------------------- Payouts ------------------------
            // multi select for export payouts - status
            if ( jQuery( '.uap-js-import-export-payouts-filter-status' ).length ){
                jQuery( '.uap-js-import-export-payouts-filter-status' ).multiselect({
                    selectAll: true,
                    placeholder: jQuery('.uap-js-import-export-payouts-filter-status').attr( 'data-placeholder' ),
                    onOptionClick: function( evt ){
                      var status = jQuery('.uap-js-import-export-payouts-filter-status').val();
                      var keyword = jQuery( '.uap-js-search-phrase-payouts' ).val();
                      var startDate = jQuery( '.uap-js-payouts-start-date' ).val();
                      var endDate = '';
                      if (jQuery( '.uap-js-payouts-end-date' ).val()){
                        endDate = jQuery( '.uap-js-payouts-end-date' ).val()+'23:59:59';
                      }
                      var filters = { status_in: status, search_phrase: keyword,  start_time: startDate, end_time: endDate };
                      jQuery( '.uap-js-payouts-export-run-bttn' ).attr( 'data-filters', JSON.stringify(filters) );
                    }
                });
            }
            // search by name - Payouts
            if ( jQuery( '.uap-js-search-phrase-payouts' ).length ){
                jQuery( '.uap-js-search-phrase-payouts' ).on( 'keyup', function(){
                  var status = jQuery('.uap-js-import-export-payouts-filter-status').val();
                  var keyword = jQuery( '.uap-js-search-phrase-payouts' ).val();
                  var startDate = jQuery( '.uap-js-payouts-start-date' ).val();
                  var endDate = '';
                  if (jQuery( '.uap-js-payouts-end-date' ).val()){
                    endDate = jQuery( '.uap-js-payouts-end-date' ).val()+'23:59:59';
                  }

                  var filters = { status_in: status, search_phrase: keyword,  start_time: startDate, end_time: endDate };
                  jQuery( '.uap-js-payouts-export-run-bttn' ).attr( 'data-filters', JSON.stringify(filters) );
                });
            }

            // change start time - Payouts
            if ( jQuery('.uap-js-payouts-start-date').length ){
                jQuery('.uap-js-payouts-start-date').datepicker({
                      dateFormat : 'yy-mm-dd ',
                      onSelect: function(datetext){
                          var status = jQuery('.uap-js-import-export-payouts-filter-status').val();
                          var keyword = jQuery( '.uap-js-search-phrase-payouts' ).val();
                          var startDate = jQuery( '.uap-js-payouts-start-date' ).val();
                          var endDate = '';
                          if (jQuery( '.uap-js-payouts-end-date' ).val()){
                            endDate = jQuery( '.uap-js-payouts-end-date' ).val()+'23:59:59';
                          }

                          var filters = { status_in: status, search_phrase: keyword,  start_time: startDate, end_time: endDate };
                          jQuery('.uap-js-payouts-export-run-bttn').attr( 'data-filters', JSON.stringify(filters) );
                      }
                });
            }

            // change end time - Payouts
            if ( jQuery('.uap-js-payouts-end-date').length ){
                jQuery('.uap-js-payouts-end-date').datepicker({
                      dateFormat : 'yy-mm-dd ',
                      onSelect: function(datetext){
                          var status = jQuery('.uap-js-import-export-payouts-filter-status').val();
                          var keyword = jQuery( '.uap-js-search-phrase-payouts' ).val();
                          var startDate = jQuery( '.uap-js-payouts-start-date' ).val();
                          var endDate = '';
                          if (jQuery( '.uap-js-payouts-end-date' ).val()){
                            endDate = jQuery( '.uap-js-payouts-end-date' ).val()+'23:59:59';
                          }

                          var filters = { status_in: status, search_phrase: keyword,  start_time: startDate, end_time: endDate };
                          jQuery('.uap-js-payouts-export-run-bttn').attr( 'data-filters', JSON.stringify(filters) );
                      }
                });
            }
            // ---------------------- end of Payouts ---------------------

        });
    },

    setAttributes: function( obj, args ){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

    handleExport: function( obj, evt ){
        var type = jQuery( evt.target ).attr( 'data-export_type' );
        jQuery( '#uap_js_import_export_wrapp_for_' + type ).addClass( obj.loadingClass );
        jQuery( '.uap-js-notice-for-export' ).remove();
        jQuery( '.uap-download-csv-link' ).remove();
        jQuery.ajax({
            type      : "post",
            url       : decodeURI(ajax_url),
            data      : {
                 action			  : 'uap_ajax_make_csv_params_as_json',
                 exportType   : jQuery( evt.target ).attr( 'data-export_type' ),
                 filters      : jQuery( evt.target ).attr( 'data-filters' ),
            },
            success   : function ( response ) {
                jQuery( '#uap_js_import_export_wrapp_for_' + type ).removeClass( obj.loadingClass );
                var responseAsObject = JSON.parse( response );
                if ( responseAsObject.status == 0 ){
                    if ( typeof responseAsObject.errorMessageAsHtml !== 'undefined' && responseAsObject.errorMessageAsHtml !== '' ){
                        console.log(response);
                        jQuery( evt.target ).parent().after( responseAsObject.errorMessageAsHtml );
                    }
                    return false;
                } else if ( responseAsObject.status === 1 && typeof responseAsObject.htmlSuccessMessage !== 'undefined' && responseAsObject.htmlSuccessMessage !== '' ) {
                    jQuery( evt.target ).parent().after( responseAsObject.htmlSuccessMessage );
                    window.open( responseAsObject.file, '_blank' );
                }

            }
        });
    }
};

UapImportExport.init();

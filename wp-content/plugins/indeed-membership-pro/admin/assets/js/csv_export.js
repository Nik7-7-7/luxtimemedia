/*
 *Ultimate Membership Pro - CSV Export
 */

"use strict";

var UmpImportExport = {

    triggerSelector             : '.js-ump-export-csv',
    loadingClass                : 'iump-export-process-border',

    init: function( args ){
        var obj = this;
        obj.setAttributes( obj, args );
        window.addEventListener( 'DOMContentLoaded', function(){
            jQuery( obj.triggerSelector ).on( 'click', function( evt ){
                obj.handleExport( obj, evt );
            });

            // --------------------- Members --------------------
            // multi select for export members - memberships
            if ( jQuery( '.iump-export-filter-users-memberships' ).length ){
                jQuery( '.iump-export-filter-users-memberships' ).multiselect({
                    selectAll: true,
                    placeholder: jQuery('.iump-export-filter-users-memberships').attr( 'data-placeholder' ),
                    onOptionClick: function( element, option ){
                        var selectedMemberships = jQuery('.iump-export-filter-users-memberships').val();
                        var keyword = jQuery( '.ump-js-search-phrase' ).val();
                        var membershipsStatus = jQuery('.iump-export-filter-users-membership-status').val();
                        var filters = { memberships: selectedMemberships, search_phrase: keyword, memberships_status: membershipsStatus };
                        jQuery('.js-ump-export-csv').attr( 'data-filters', JSON.stringify(filters) );
                    }
                });
            }

            // multi select for export members - memberships stats
            if ( jQuery( '.iump-export-filter-users-membership-status' ).length ){
                jQuery( '.iump-export-filter-users-membership-status' ).multiselect({
                    selectAll: true,
                    placeholder: jQuery('.iump-export-filter-users-membership-status').attr( 'data-placeholder' ),
                    onOptionClick: function( element, option ){
                        var selectedMemberships = jQuery('.iump-export-filter-users-memberships').val();
                        var keyword = jQuery( '.ump-js-search-phrase' ).val();
                        var membershipsStatus = jQuery('.iump-export-filter-users-membership-status').val();
                        var filters = { memberships: selectedMemberships, search_phrase: keyword, memberships_status: membershipsStatus };
                        jQuery('.js-ump-export-csv').attr( 'data-filters', JSON.stringify(filters) );
                    }
                });
            }


            // search by name - members
            if ( jQuery( '.ump-js-search-phrase' ).length ){
                jQuery( '.ump-js-search-phrase' ).on( 'keyup', function(){
                      var selectedMemberships = jQuery('.iump-export-filter-users-memberships').val();
                      var keyword = jQuery( '.ump-js-search-phrase' ).val();
                      var membershipsStatus = jQuery('.iump-export-filter-users-membership-status').val();
                      var filters = { memberships: selectedMemberships, search_phrase: keyword, memberships_status: membershipsStatus };
                      jQuery('.js-ump-export-csv').attr( 'data-filters', JSON.stringify(filters) );
                });
            }

            /// --------------------- end of Members --------------------

        });
    },

    setAttributes: function( obj, args ){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

    handleExport: function( obj, evt ){
      jQuery( '#ump_js_import_export_wrapp_for_members' ).addClass( obj.loadingClass );
      jQuery( '.iump-js-notice-for-export' ).remove();
      jQuery( '.iump-download-csv-link' ).remove();
      jQuery.ajax({
          type : 'post',
          url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
          data : {
                     action: 'ihc_return_csv_link_with_json_params',
                     filters : jQuery( obj.triggerSelector ).attr( 'data-filters' )
          },
          success: function (response) {
              jQuery( '#ump_js_import_export_wrapp_for_members' ).removeClass( obj.loadingClass );
              var responseAsObject = JSON.parse( response );
              if ( responseAsObject.status == 0 ){
                  if ( typeof responseAsObject.errorMessageAsHtml !== 'undefined' && responseAsObject.errorMessageAsHtml !== '' ){
                      //console.log(response);
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

UmpImportExport.init();

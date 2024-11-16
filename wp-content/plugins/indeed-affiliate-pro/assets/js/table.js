/*
 * Ultimate Affiliate Pro - Table functionality
 */
"use strict";
var UapDataTable = {
    tableId               : '#uap-dashboard-table',
    translatedLabels      : null,
    tableButtons          : null,
    tableObject           : null,
    columns               : null,

    // start init
    init                        : function(){
        var object = this;
        var labels = JSON.parse( window.uap_datatable_labels );
        object.columns = JSON.parse( window.uap_datatable_cols );

        // default settings for datatable
        object.translatedLabels = {
                      processing    : "<div class='uap-table-custom-loading'></div>",
                      search				: '',
                      lengthMenu		: labels.lengthMenu,
                      info					: labels.info,
                      infoEmpty			: labels.infoEmpty,
                      infoFiltered	: labels.infoFiltered,
                      loadingRecords: labels.loadingRecords,
                      zeroRecords		: labels.zeroRecords,
                      emptyTable		: labels.emptyTable,
                      paginate			: {
                                    first					: labels.paginate.first,
                                    previous			: labels.paginate.previous,
                                    next					: labels.paginate.next,
                                    last					: labels.paginate.last,
                      },
                      aria					: {
                                    sortAscending		: labels.sortAscending,
                                    sortDescending	: labels.sortDescending,
                      },
                      searchPlaceholder			: labels.searchPlaceholder,
                      search								: "",
                      scroller              : {
                                    loadingIndicator : true
                      }
        };

        // default buttons
        object.tableButtons = [
            {
                extend      : 'colvis',// show/hide columns button
                text        : labels.show_hide_cols_label,// action buttons
                className   : 'uap-show-hide-cols uap-dashboard-table-show-hide-cols',
                columnText: function ( dt, idx, title ) {
                    if ( title === '' ){
                        return labels.checkbox_label;
                    }
                    return title;
                }
            },
        ];

        // select items checkbox
        jQuery( '.uap-js-select-all-checkboxes' ).on('click', function(){
            object.selectUnselectAll( object );
        });

        // create the table
        if ( typeof window.uap_datatable_type == 'undefined' || window.uap_datatable_type === '' ){
            return;
        }

        // multiselect - status
        if ( jQuery('.uap-js-datatable-items-status-types').length ){
            jQuery('.uap-js-datatable-items-status-types').multiselect({
                selectAll: true,
                placeholder: jQuery('.uap-js-datatable-items-status-types').attr( 'data-placeholder' ),
            });
        }

        // multiselect - target ( for notifications )
        if ( jQuery('.uap-js-datatable-items-target-types').length ){
            jQuery('.uap-js-datatable-items-target-types').multiselect({
                selectAll: true,
                placeholder: jQuery('.uap-js-datatable-items-target-types').attr( 'data-placeholder' ),
            });
        }

        // multiselect - ranks ( affiliates page )
        if ( jQuery( '.uap-js-datatable-filter-ranks' ).length ){
            jQuery( '.uap-js-datatable-filter-ranks' ).multiselect({
                selectAll: true,
                placeholder: jQuery('.uap-js-datatable-filter-ranks').attr( 'data-placeholder' ),
            });
        }

        // multiselect - sources
        if ( jQuery( '.uap-js-datatable-items-source-types-referrals' ).length ){
            jQuery( '.uap-js-datatable-items-source-types-referrals' ).multiselect({
                selectAll: true,
                placeholder: jQuery('.uap-js-datatable-items-source-types-referrals').attr( 'data-placeholder' ),
            });
        }

        switch ( window.uap_datatable_type ){
            case 'ranks':
              object.loadTableForRanks( object );
              /// event on custom filter - do reload
              jQuery( '.uap-js-datatable-items-status-types' ).on('change', function () {
                   object.tableObject.destroy();
                   object.loadTableForRanks( object );
              });
              // event on custom search - do reload
              jQuery( '.uap-js-search-phrase' ).on('keyup', function () {
                   object.tableObject.destroy();
                   object.loadTableForRanks( object );
              });
              break;
            case 'offers':
              object.loadTableForOffers( object );
              /// event on custom filter - do reload
              jQuery( '.uap-js-datatable-items-status-types' ).on('change', function () {
                   object.tableObject.destroy();
                   object.loadTableForOffers( object );
              });
              // event on custom search - do reload
              jQuery( '.uap-js-search-phrase' ).on('keyup', function () {
                   object.tableObject.destroy();
                   object.loadTableForOffers( object );
              });
              break;
            case 'landing_commissions':
              object.loadTableForLandingCommissions( object );
              /// event on custom filter - do reload
              jQuery( '.uap-js-datatable-items-status-types' ).on('change', function () {
                   object.tableObject.destroy();
                   object.loadTableForLandingCommissions( object );
              });
              // event on custom search - do reload
              jQuery( '.uap-js-search-phrase' ).on('keyup', function () {
                   object.tableObject.destroy();
                   object.loadTableForLandingCommissions( object );
              });
              break;
            case 'banners':
                object.loadTableForBanners( object );
                // event on custom search - do reload
                jQuery( '.uap-js-search-phrase' ).on('keyup', function () {
                     object.tableObject.destroy();
                     object.loadTableForBanners( object );
                });
                break;
            case 'notifications':
                // filter and search bar with button
                object.loadTableForNotifications( object );
                jQuery( '.uap-datatable-filter-bttn' ).on( 'click', function(){
                    object.tableObject.destroy();
                    object.loadTableForNotifications( object );
                });
                break;
            case 'affiliates':
                // filter and search bar with button
                object.loadTableForAffiliates( object );
                jQuery( '.uap-datatable-filter-bttn' ).on( 'click', function(){
                    object.tableObject.destroy();
                    object.loadTableForAffiliates( object );
                });
                break;
            case 'visits':
                // filter and search bar with button
                object.loadTableForVisits( object );
                jQuery( '.uap-datatable-filter-bttn' ).on( 'click', function(){
                    object.tableObject.destroy();
                    object.loadTableForVisits( object );
                });
                break;
            case 'referrals':
                // filter and search bar with button
                object.loadTableForReferrals( object );
                jQuery( '.uap-datatable-filter-bttn' ).on( 'click', function(){
                    object.tableObject.destroy();
                    object.loadTableForReferrals( object );
                });
                break;
            case 'payout_setup':
              object.loadTableForPayoutSetup( object );
              break;
            case 'payouts':
                // filter and search bar with button
                object.loadTableForPayouts( object );
                jQuery( '.uap-datatable-filter-bttn' ).on( 'click', function(){
                    object.tableObject.destroy();
                    object.loadTableForPayouts( object );
                });
              break;
            case 'payments':
              // filter and search bar with button
              object.loadTableForPayments( object );
              jQuery( '.uap-datatable-filter-bttn' ).on( 'click', function(){
                  object.tableObject.destroy();
                  object.loadTableForPayments( object );
              });
              break;
            case 'referrals_for_payment':
              object.loadTableForReferralsForPayment( object );
              break;
            case 'payout_payments':
              object.loadTableForPayoutPayments( object );
              break;
        }
    },
    // end of init

    // loadTableForRanks
    loadTableForRanks: function( object ){
          object.tableObject = new DataTable( object.tableId, {
            ajax           : {
                    type      : "post",
                    url       : decodeURI(ajax_url),
                    data      : {
                               action             : "uap_ajax_get_ranks", // ajax method
                               search_phrase      : jQuery('.uap-js-search-phrase' ).val(), // extra param for search
                               status             : jQuery('.uap-js-datatable-items-status-types').val(),
                    },
                    dataSrc   : 'data'
            },
            //retrieve       : true,
            serverSide     : true,
            dom            : '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BP><"uap-datatable-wrapp"rt>ipl', // '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BPf>r<"uap-datatable-wrapp"t>ipl'
            lengthChange   : true,
            pageLength     : 25,
            drawCallback   : function(){
                  jQuery("body").removeClass("loading"); // remove loading
                  jQuery( '#uap-dashboard-table' ).removeClass( 'uap-display-none' );
                  jQuery( '.uap-js-select-all-checkboxes' ).attr( 'checked', false );
                  jQuery( '.uap-datatable-actions-wrapp' ).html( jQuery('.uap-datatable-actions-wrapp-copy').html() );

                  // select items checkbox
                  jQuery( '.uap-js-select-all-checkboxes' ).on('click', function(){
                      object.selectUnselectAll( object );
                  });

                  // remove one rank
                  jQuery( '.uap-js-remove-one-rank' ).on( 'click', function(){
                      var rankId = jQuery( this ).attr( 'data-id' );
                      uapSwal({
                        title: jQuery( '.uap-js-messages-for-datatable' ).attr( 'data-remove_one_rank' ),
                        text: "",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-danger",
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                      },
                      function(){
                          jQuery.ajax({
                              type : 'post',
                              url : decodeURI(ajax_url),
                              data : {
                                         action                 : 'uap_admin_remove_one_rank',
          															 uap_admin_forms_nonce	:	jQuery( '.uap-js-ranks-listing-delete-nonce' ).attr( 'data-value'),
                                         id                     :	rankId,
                                     },
                              success: function (response) {
                                  object.tableObject.destroy();
                                  object.loadTableForRanks( object );
                              }
                         });
                     });
                  });
                  // end of remove one rank

                  // remove many ranks
                  if ( jQuery( '.uap-js-items-apply-bttn' ).length ){
                    jQuery( '.uap-js-items-apply-bttn' ).on( 'click', function(evt){
                          evt.preventDefault();
                          var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                              return this.value;
                          }).get();
                          if ( checkedVals.length === 0 ){
                              return;
                          }

                          if ( jQuery('.uap-js-bulk-action-select').val() === 'remove' ){
                              uapSwal({
                                title: jQuery('.uap-js-messages-for-datatable').attr('data-remove_many_ranks'),
                                text: "",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonClass: "btn-danger",
                                confirmButtonText: "OK",
                                closeOnConfirm: true
                              },
                              function(){
                                  var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                                      return this.value;
                                  }).get();
                                  var valsToSend = checkedVals.join(",");
                                  jQuery.ajax({
                                          type 			: 'post',
                                          url 			: decodeURI(ajax_url),
                                          data 			: {
                                                     action		              : 'uap_admin_remove_many_rank',
                      															 uap_admin_forms_nonce	:	jQuery( '.uap-js-ranks-listing-delete-nonce' ).attr( 'data-value' ),
                                                     ids			              : valsToSend,
                                          },
                                          success		: function (response) {
                                              object.tableObject.destroy();
                                              object.loadTableForRanks( object );
                                          }
                                  });
                             });
                          }

                    });
                }
                // end of remove many ranks

            },
            buttons        : object.tableButtons,
            language       : object.translatedLabels,
            columns        : object.columns,
            processing     : true,
            orderCellsTop  : true,
            fixedHeader    : false,
            order          : [],//[ 1, "asc" ]
            scrollToTop    : true,
            createdRow: function( row, data, dataIndex ) {
                jQuery( row ).attr( 'onmouseover', "uapDhSelector('#rank_"+ data.id +"', 1);" );
                jQuery( row ).attr( 'onmouseout', "uapDhSelector('#rank_"+ data.id +"', 0);" );
            },
            stateSave      : true,
            stateSaveCallback: function( settings, data ) {
              jQuery.ajax({
                      type 			: 'post',
                      url 			: decodeURI(ajax_url),
                      data 			: {
                            action     : "uap_ajax_datatable_save_state", // ajax method
                            state      : JSON.stringify(data),
                            type       : 'uap_datatable_state_for-ranks',
                      },
                      success		: function (response) {
                          return;
                      }
              });
            },
            stateLoadCallback: function(settings) {
                if ( jQuery( '.uap-js-datatable-state' ).length ){
                    return JSON.parse( jQuery( '.uap-js-datatable-state' ).attr( 'data-value' ) );
                }
                return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
            },
        });
    },
    // end of loadTableForRanks

    // loadTableForOffers
    loadTableForOffers: function( object ){
          object.tableObject = new DataTable( object.tableId, {
            ajax           : {
                    type      : "post",
                    url       : decodeURI(ajax_url),
                    data      : {
                               action             : "uap_ajax_get_offers", // ajax method
                               search_phrase      : jQuery('.uap-js-search-phrase' ).val(), // extra param for search
                               status             : jQuery('.uap-js-datatable-items-status-types').val(),
                    },
                    dataSrc   : 'data'
            },
            //retrieve       : true,
            serverSide     : true,
            dom            : '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BP><"uap-datatable-wrapp"rt>ipl', // '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BPf>r<"uap-datatable-wrapp"t>ipl'
            lengthChange   : true,
            pageLength     : 25,
            drawCallback   : function(){
                  jQuery("body").removeClass("loading"); // remove loading
                  jQuery( '#uap-dashboard-table' ).removeClass( 'uap-display-none' );
                  jQuery( '.uap-js-select-all-checkboxes' ).attr( 'checked', false );
                  jQuery( '.uap-datatable-actions-wrapp' ).html( jQuery('.uap-datatable-actions-wrapp-copy').html() );

                  // select items checkbox
                  jQuery( '.uap-js-select-all-checkboxes' ).on('click', function(){
                      object.selectUnselectAll( object );
                  });

                  // remove one item
                  jQuery( '.uap-js-remove-one-item' ).on( 'click', function(){
                      var itemId = jQuery( this ).attr( 'data-id' );
                      uapSwal({
                        title: jQuery( '.uap-js-messages-for-datatable' ).attr( 'data-remove_one_item' ),
                        text: "",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-danger",
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                      },
                      function(){
                          jQuery.ajax({
                              type : 'post',
                              url : decodeURI(ajax_url),
                              data : {
                                         action                 : 'uap_ajax_remove_one_offer',// custom
                                         uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value'),
                                         id                     :	itemId,
                                     },
                              success: function (response) {
                                  object.tableObject.destroy();
                                  object.loadTableForOffers( object );// custom
                              }
                         });
                     });
                  });
                  // end of remove one item

                  // remove many items
                  if ( jQuery( '.uap-js-items-apply-bttn' ).length ){
                    jQuery( '.uap-js-items-apply-bttn' ).on( 'click', function(evt){
                          evt.preventDefault();
                          var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                              return this.value;
                          }).get();
                          if ( checkedVals.length === 0 ){
                              return;
                          }
                          if ( jQuery('.uap-js-bulk-action-select').val() === 'remove' ){
                              uapSwal({
                                title: jQuery('.uap-js-messages-for-datatable').attr('data-remove_many_items'),
                                text: "",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonClass: "btn-danger",
                                confirmButtonText: "OK",
                                closeOnConfirm: true
                              },
                              function(){
                                  var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                                      return this.value;
                                  }).get();
                                  var valsToSend = checkedVals.join(",");
                                  jQuery.ajax({
                                          type 			: 'post',
                                          url 			: decodeURI(ajax_url),
                                          data 			: {
                                                     action		              : 'uap_ajax_remove_many_offers',// custom
                                                     uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                                     ids			              : valsToSend,
                                          },
                                          success		: function (response) {
                                              object.tableObject.destroy();
                                              object.loadTableForOffers( object );// custom
                                          }
                                  });
                             });
                          }

                    });
                }
                // end of remove many items

            },
            buttons        : object.tableButtons,
            language       : object.translatedLabels,
            columns        : object.columns,
            processing     : true,
            orderCellsTop  : true,
            fixedHeader    : false,
            order          : [],//[ 1, "asc" ]
            scrollToTop    : true,
            createdRow: function( row, data, dataIndex ) {
                jQuery( row ).attr( 'onmouseover', "uapDhSelector('#offer_"+ data.id +"', 1);" );// custom
                jQuery( row ).attr( 'onmouseout', "uapDhSelector('#offer_"+ data.id +"', 0);" );// custom
            },
            stateSave      : true,
            stateSaveCallback: function( settings, data ) {
              jQuery.ajax({
                      type 			: 'post',
                      url 			: decodeURI(ajax_url),
                      data 			: {
                            action     : "uap_ajax_datatable_save_state", // ajax method
                            state      : JSON.stringify(data),
                            type       : 'uap_datatable_state_for-offers',
                      },
                      success		: function (response) {
                          return;
                      }
              });
            },
            stateLoadCallback: function(settings) {
                if ( jQuery( '.uap-js-datatable-state' ).length ){
                    return JSON.parse( jQuery( '.uap-js-datatable-state' ).attr( 'data-value' ) );
                }
                return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
            },
        });
    },
    // end of loadTableForOffers

    // landing commissions
    loadTableForLandingCommissions:  function( object ){
          object.tableObject = new DataTable( object.tableId, {
            ajax           : {
                    type      : "post",
                    url       : decodeURI(ajax_url),
                    data      : {
                               action             : "uap_ajax_get_landing_commissions", // ajax method
                               search_phrase      : jQuery('.uap-js-search-phrase' ).val(), // extra param for search
                               status             : jQuery('.uap-js-datatable-items-status-types').val(),
                    },
                    dataSrc   : 'data'
            },
            //retrieve       : true,
            serverSide     : true,
            dom            : '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BP><"uap-datatable-wrapp"rt>ipl', // '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BPf>r<"uap-datatable-wrapp"t>ipl'
            lengthChange   : true,
            pageLength     : 25,
            drawCallback   : function(){
                  jQuery("body").removeClass("loading"); // remove loading
                  jQuery( '#uap-dashboard-table' ).removeClass( 'uap-display-none' );
                  jQuery( '.uap-js-select-all-checkboxes' ).attr( 'checked', false );
                  jQuery( '.uap-datatable-actions-wrapp' ).html( jQuery('.uap-datatable-actions-wrapp-copy').html() );

                  // select items checkbox
                  jQuery( '.uap-js-select-all-checkboxes' ).on('click', function(){
                      object.selectUnselectAll( object );
                  });

                  // remove one item
                  jQuery( '.uap-js-remove-one-item' ).on( 'click', function(){
                      var itemId = jQuery( this ).attr( 'data-id' );
                      uapSwal({
                        title: jQuery( '.uap-js-messages-for-datatable' ).attr( 'data-remove_one_item' ),
                        text: "",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-danger",
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                      },
                      function(){
                          jQuery.ajax({
                              type : 'post',
                              url : decodeURI(ajax_url),
                              data : {
                                         action                 : 'uap_ajax_remove_one_landing_commission',// custom
                                         uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value'),
                                         id                     :	itemId,
                                     },
                              success: function (response) {
                                  object.tableObject.destroy();
                                  object.loadTableForLandingCommissions( object );// custom
                              }
                         });
                     });
                  });
                  // end of remove one item

                  // remove many items
                  if ( jQuery( '.uap-js-items-apply-bttn' ).length ){
                    jQuery( '.uap-js-items-apply-bttn' ).on( 'click', function(evt){
                          evt.preventDefault();
                          var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                              return this.value;
                          }).get();
                          if ( checkedVals.length === 0 ){
                              return;
                          }
                          if ( jQuery('.uap-js-bulk-action-select').val() === 'remove' ){
                              uapSwal({
                                title: jQuery('.uap-js-messages-for-datatable').attr('data-remove_many_items'),
                                text: "",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonClass: "btn-danger",
                                confirmButtonText: "OK",
                                closeOnConfirm: true
                              },
                              function(){
                                  var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                                      return this.value;
                                  }).get();
                                  var valsToSend = checkedVals.join(",");
                                  jQuery.ajax({
                                          type 			: 'post',
                                          url 			: decodeURI(ajax_url),
                                          data 			: {
                                                     action		              : 'uap_ajax_remove_many_landing_commissions',// custom
                                                     uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                                     ids			              : valsToSend,
                                          },
                                          success		: function (response) {
                                              object.tableObject.destroy();
                                              object.loadTableForLandingCommissions( object );// custom
                                          }
                                  });
                             });
                          }

                    });
                }
                // end of remove many items

            },
            buttons        : object.tableButtons,
            language       : object.translatedLabels,
            columns        : object.columns,
            processing     : true,
            orderCellsTop  : true,
            fixedHeader    : false,
            order          : [],//[ 1, "asc" ]
            scrollToTop    : true,
            createdRow: function( row, data, dataIndex ) {
                jQuery( row ).attr( 'onmouseover', "uapDhSelector('#landing_commission_"+ data.id +"', 1);" );// custom
                jQuery( row ).attr( 'onmouseout', "uapDhSelector('#landing_commission_"+ data.id +"', 0);" );// custom
            },
            stateSave      : true,
            stateSaveCallback: function( settings, data ) {
              jQuery.ajax({
                      type 			: 'post',
                      url 			: decodeURI(ajax_url),
                      data 			: {
                            action     : "uap_ajax_datatable_save_state", // ajax method
                            state      : JSON.stringify(data),
                            type       : 'uap_datatable_state_for-landing_commissions',
                      },
                      success		: function (response) {
                          return;
                      }
              });
            },
            stateLoadCallback: function(settings) {
                if ( jQuery( '.uap-js-datatable-state' ).length ){
                    return JSON.parse( jQuery( '.uap-js-datatable-state' ).attr( 'data-value' ) );
                }
                return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
            },
        });
    },
    // end of landing commissions

    // banners
    loadTableForBanners : function( object ){
        object.tableObject = new DataTable( object.tableId, {
          ajax           : {
                  type      : "post",
                  url       : decodeURI(ajax_url),
                  data      : {
                             action             : "uap_ajax_get_banners", // ajax method
                             search_phrase      : jQuery('.uap-js-search-phrase' ).val(), // extra param for search
                  },
                  dataSrc   : 'data'
          },
          //retrieve       : true,
          serverSide     : true,
          dom            : '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BP><"uap-datatable-wrapp"rt>ipl', // '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BPf>r<"uap-datatable-wrapp"t>ipl'
          lengthChange   : true,
          pageLength     : 25,
          drawCallback   : function(){
                jQuery("body").removeClass("loading"); // remove loading
                jQuery( '#uap-dashboard-table' ).removeClass( 'uap-display-none' );
                jQuery( '.uap-js-select-all-checkboxes' ).attr( 'checked', false );
                jQuery( '.uap-datatable-actions-wrapp' ).html( jQuery('.uap-datatable-actions-wrapp-copy').html() );

                // select items checkbox
                jQuery( '.uap-js-select-all-checkboxes' ).on('click', function(){
                    object.selectUnselectAll( object );
                });

                // remove one item
                jQuery( '.uap-js-remove-one-item' ).on( 'click', function(){
                    var itemId = jQuery( this ).attr( 'data-id' );
                    uapSwal({
                      title: jQuery( '.uap-js-messages-for-datatable' ).attr( 'data-remove_one_item' ),
                      text: "",
                      type: "warning",
                      showCancelButton: true,
                      confirmButtonClass: "btn-danger",
                      confirmButtonText: "OK",
                      closeOnConfirm: true
                    },
                    function(){
                        jQuery.ajax({
                            type : 'post',
                            url : decodeURI(ajax_url),
                            data : {
                                       action                 : 'uap_ajax_remove_one_banners',// custom
                                       uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value'),
                                       id                     :	itemId,
                                   },
                            success: function (response) {
                                object.tableObject.destroy();
                                object.loadTableForBanners( object );// custom
                            }
                       });
                   });
                });
                // end of remove one item

                // remove many items
                if ( jQuery( '.uap-js-items-apply-bttn' ).length ){
                  jQuery( '.uap-js-items-apply-bttn' ).on( 'click', function(evt){
                        evt.preventDefault();
                        var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                            return this.value;
                        }).get();
                        if ( checkedVals.length === 0 ){
                            return;
                        }
                        if ( jQuery('.uap-js-bulk-action-select').val() === 'remove' ){
                            uapSwal({
                              title: jQuery('.uap-js-messages-for-datatable').attr('data-remove_many_items'),
                              text: "",
                              type: "warning",
                              showCancelButton: true,
                              confirmButtonClass: "btn-danger",
                              confirmButtonText: "OK",
                              closeOnConfirm: true
                            },
                            function(){
                                var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                                    return this.value;
                                }).get();
                                var valsToSend = checkedVals.join(",");
                                jQuery.ajax({
                                        type 			: 'post',
                                        url 			: decodeURI(ajax_url),
                                        data 			: {
                                                   action		              : 'uap_ajax_remove_many_banners',// custom
                                                   uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                                   ids			              : valsToSend,
                                        },
                                        success		: function (response) {
                                            object.tableObject.destroy();
                                            object.loadTableForBanners( object );// custom
                                        }
                                });
                           });
                        }

                  });
              }
              // end of remove many items

          },
          buttons        : object.tableButtons,
          language       : object.translatedLabels,
          columns        : object.columns,
          processing     : true,
          orderCellsTop  : true,
          fixedHeader    : false,
          order          : [],//[ 1, "asc" ]
          scrollToTop    : true,
          createdRow: function( row, data, dataIndex ) {
              jQuery( row ).attr( 'onmouseover', "uapDhSelector('#banner_"+ data.id +"', 1);" );// custom
              jQuery( row ).attr( 'onmouseout', "uapDhSelector('#banner_"+ data.id +"', 0);" );// custom
          },
          stateSave      : true,
          stateSaveCallback: function( settings, data ) {
            jQuery.ajax({
                    type 			: 'post',
                    url 			: decodeURI(ajax_url),
                    data 			: {
                          action     : "uap_ajax_datatable_save_state", // ajax method
                          state      : JSON.stringify(data),
                          type       : 'uap_datatable_state_for-banners',
                    },
                    success		: function (response) {
                        return;
                    }
            });
          },
          stateLoadCallback: function(settings) {
              if ( jQuery( '.uap-js-datatable-state' ).length ){
                  return JSON.parse( jQuery( '.uap-js-datatable-state' ).attr( 'data-value' ) );
              }
              return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
          },
      });
    },
    // end of banners

    // notifications
    loadTableForNotifications : function( object ){
        object.tableObject = new DataTable( object.tableId, {
          ajax           : {
                  type      : "post",
                  url       : decodeURI(ajax_url),
                  data      : {
                             action             : "uap_ajax_get_notifications", // ajax method
                             search_phrase      : jQuery('.uap-js-search-phrase' ).val(), // extra param for search
                             target_types       : jQuery( '.uap-js-datatable-items-target-types' ).val(),
                  },
                  dataSrc   : 'data'
          },
          //retrieve       : true,
          serverSide     : true,
          dom            : '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BP><"uap-datatable-wrapp"rt>ipl', // '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BPf>r<"uap-datatable-wrapp"t>ipl'
          lengthChange   : true,
          pageLength     : 25,
          drawCallback   : function(){
                jQuery("body").removeClass("loading"); // remove loading
                jQuery( '#uap-dashboard-table' ).removeClass( 'uap-display-none' );
                jQuery( '.uap-js-select-all-checkboxes' ).attr( 'checked', false );
                jQuery( '.uap-datatable-actions-wrapp' ).html( jQuery('.uap-datatable-actions-wrapp-copy').html() );

                // select items checkbox
                jQuery( '.uap-js-select-all-checkboxes' ).on('click', function(){
                    object.selectUnselectAll( object );
                });

                // remove one item
                jQuery( '.uap-js-remove-one-item' ).on( 'click', function(){
                    var itemId = jQuery( this ).attr( 'data-id' );
                    uapSwal({
                      title: jQuery( '.uap-js-messages-for-datatable' ).attr( 'data-remove_one_item' ),
                      text: "",
                      type: "warning",
                      showCancelButton: true,
                      confirmButtonClass: "btn-danger",
                      confirmButtonText: "OK",
                      closeOnConfirm: true
                    },
                    function(){
                        jQuery.ajax({
                            type : 'post',
                            url : decodeURI(ajax_url),
                            data : {
                                       action                 : 'uap_ajax_remove_one_notifications',// custom
                                       uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value'),
                                       id                     :	itemId,
                                   },
                            success: function (response) {
                                object.tableObject.destroy();
                                object.loadTableForNotifications( object );// custom
                            }
                       });
                   });
                });
                // end of remove one item

                // remove many items
                if ( jQuery( '.uap-js-items-apply-bttn' ).length ){
                  jQuery( '.uap-js-items-apply-bttn' ).on( 'click', function(evt){
                        evt.preventDefault();
                        var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                            return this.value;
                        }).get();
                        if ( checkedVals.length === 0 ){
                            return;
                        }

                        if ( jQuery('.uap-js-bulk-action-select').val() === 'remove' ){
                            uapSwal({
                              title: jQuery('.uap-js-messages-for-datatable').attr('data-remove_many_items'),
                              text: "",
                              type: "warning",
                              showCancelButton: true,
                              confirmButtonClass: "btn-danger",
                              confirmButtonText: "OK",
                              closeOnConfirm: true
                            },
                            function(){
                                var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                                    return this.value;
                                }).get();
                                var valsToSend = checkedVals.join(",");
                                jQuery.ajax({
                                        type 			: 'post',
                                        url 			: decodeURI(ajax_url),
                                        data 			: {
                                                   action		              : 'uap_ajax_remove_many_notifications',// custom
                                                   uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                                   ids			              : valsToSend,
                                        },
                                        success		: function (response) {
                                            object.tableObject.destroy();
                                            object.loadTableForNotifications( object );// custom
                                        }
                                });
                           });
                        }

                  });
              }
              // end of remove many items

              // send test notification
              if ( jQuery( '.uap-js-notifications-fire-notification-test' ).length > 0 ){
                  jQuery( '.uap-js-notifications-fire-notification-test' ).on( 'click', function(){
                      var notificationId = jQuery( this ).attr( 'data-notification_id' );
                      jQuery.ajax({
                          type 			: 'post',
                          url 			: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php',
                          data 			: {
                                     action : 'uap_ajax_notification_send_test_email',
                                     uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                     id			: notificationId
                          },
                          success		: function (data) {
                              jQuery(data).hide().appendTo('body').fadeIn('normal');
                          }
                     });
                  });
              }
              //end of send test notifications

              // modify status
              if ( jQuery( '.uap-js-admin-notification-list-on-off' ).length > 0 ){
                  jQuery( '.uap-js-admin-notification-list-on-off' ).on( 'change', function(){
                        var notificationId = jQuery( this ).attr( 'data-id' );
                        jQuery.ajax({
                            type 			: 'post',
                            url 			: decodeURI( window.uap_url )+'/wp-admin/admin-ajax.php',
                            data 			: {
                                       action : 'uap_ajax_notification_modify_status',
                                       id			: notificationId,
                                       uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                       status : jQuery( this ).is(':checked') ? 1 : 0,
                            },
                            success		: function (data) {
                                object.tableObject.destroy();
                                object.loadTableForNotifications( object );// custom
                            }
                       });

                  });
              }
              // end of modify status

          },
          buttons        : object.tableButtons,
          language       : object.translatedLabels,
          columns        : object.columns,
          processing     : true,
          orderCellsTop  : true,
          fixedHeader    : false,
          order          : [],//[ 1, "asc" ]
          scrollToTop    : true,
          createdRow: function( row, data, dataIndex ) {
              jQuery( row ).attr( 'onmouseover', "uapDhSelector('#notification_"+ data.id +"', 1);" );// custom
              jQuery( row ).attr( 'onmouseout', "uapDhSelector('#notification_"+ data.id +"', 0);" );// custom
          },
          stateSave      : true,
          stateSaveCallback: function( settings, data ) {
            jQuery.ajax({
                    type 			: 'post',
                    url 			: decodeURI(ajax_url),
                    data 			: {
                          action     : "uap_ajax_datatable_save_state", // ajax method
                          state      : JSON.stringify(data),
                          type       : 'uap_datatable_state_for-notifications',
                    },
                    success		: function (response) {
                        return;
                    }
            });
          },
          stateLoadCallback: function(settings) {
              if ( jQuery( '.uap-js-datatable-state' ).length ){
                  return JSON.parse( jQuery( '.uap-js-datatable-state' ).attr( 'data-value' ) );
              }
              return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
          },
      });
    },
    // end of notifications

    // affiliates
    loadTableForAffiliates : function( object ){
        object.tableObject = new DataTable( object.tableId, {
          ajax           : {
                  type      : "post",
                  url       : decodeURI(ajax_url),
                  data      : {
                             action             : "uap_ajax_get_affiliates", // ajax method
                             search_phrase      : jQuery('.uap-js-search-phrase' ).val(), // extra param for search
                             ranks              : jQuery( '.uap-js-datatable-filter-ranks' ).val(),
                  },
                  dataSrc   : 'data'
          },
          //retrieve       : true,
          serverSide     : true,
          dom            : '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BP><"uap-datatable-wrapp"rt>ipl', // '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BPf>r<"uap-datatable-wrapp"t>ipl'
          lengthChange   : true,
          pageLength     : 25,
          drawCallback   : function( response ){
                // ready for export
                jQuery( '.js-uap-export-csv' ).attr( 'data-filters', response.json.params );

                jQuery("body").removeClass("loading"); // remove loading
                jQuery( '#uap-dashboard-table' ).removeClass( 'uap-display-none' );
                jQuery( '.uap-js-select-all-checkboxes' ).attr( 'checked', false );
                jQuery( '.uap-datatable-actions-wrapp' ).html( jQuery('.uap-datatable-actions-wrapp-copy').html() );

                // select items checkbox
                jQuery( '.uap-js-select-all-checkboxes' ).on('click', function(){
                    object.selectUnselectAll( object );
                });

                // copy to clipboard
                if ( jQuery( '.uap-js-admin-affiliate-table-copy-clipboard' ).length > 0 ){
                    jQuery( '.uap-js-admin-affiliate-table-copy-clipboard' ).on('click', function(){
                        var cpTxt = jQuery( this ).attr('data-link');
                        navigator.clipboard.writeText(cpTxt);
                        uapSwal({
                          title: jQuery( this ).attr( 'data-message' ),
                          text: "",
                          type: "success",
                          showConfirmButton: false,
                          confirmButtonClass: "btn-success",
                          confirmButtonText: "OK",
                          timer: 1000
                        });
                    });
                }

                // remove one item
                jQuery( '.uap-js-remove-one-item' ).on( 'click', function(){
                    var itemId = jQuery( this ).attr( 'data-id' );
                    uapSwal({
                      title: jQuery( '.uap-js-messages-for-datatable' ).attr( 'data-remove_one_item' ),
                      text: "",
                      type: "warning",
                      showCancelButton: true,
                      confirmButtonClass: "btn-danger",
                      confirmButtonText: "OK",
                      closeOnConfirm: true
                    },
                    function(){
                        jQuery.ajax({
                            type : 'post',
                            url : decodeURI(ajax_url),
                            data : {
                                       action                 : 'uap_ajax_remove_one_affiliate',// custom
                                       uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value'),
                                       id                     :	itemId,
                                   },
                            success: function (response) {
                                object.tableObject.destroy();
                                object.loadTableForAffiliates( object );// custom
                            }
                       });
                   });
                });
                // end of remove one item

                // remove many items
                if ( jQuery( '.uap-js-items-apply-bttn' ).length ){
                  jQuery( '.uap-js-items-apply-bttn' ).on( 'click', function(evt){
                        evt.preventDefault();
                        var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                            return this.value;
                        }).get();
                        if ( checkedVals.length === 0 ){
                            return;
                        }

                        if ( jQuery('.uap-js-bulk-action-select').val() === 'remove' ){
                            uapSwal({
                              title: jQuery('.uap-js-messages-for-datatable').attr('data-remove_many_items'),
                              text: "",
                              type: "warning",
                              showCancelButton: true,
                              confirmButtonClass: "btn-danger",
                              confirmButtonText: "OK",
                              closeOnConfirm: true
                            },
                            function(){
                                var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                                    return this.value;
                                }).get();
                                var valsToSend = checkedVals.join(",");
                                jQuery.ajax({
                                        type 			: 'post',
                                        url 			: decodeURI(ajax_url),
                                        data 			: {
                                                   action		              : 'uap_ajax_remove_many_affiliates',// custom
                                                   uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                                   ids			              : valsToSend,
                                        },
                                        success		: function (response) {
                                            object.tableObject.destroy();
                                            object.loadTableForAffiliates( object );// custom
                                        }
                                });
                           });
                        } else if ( jQuery('.uap-js-bulk-action-select').val() === 'update_rank' ){
                            var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                                return this.value;
                            }).get();
                            var valsToSend = checkedVals.join(",");
                            jQuery.ajax({
                                    type 			: 'post',
                                    url 			: decodeURI(ajax_url),
                                    data 			: {
                                               action		              : 'uap_ajax_update_ranks_for_affiliates',// custom
                                               uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                               ids			              : valsToSend,
                                    },
                                    success		: function (response) {
                                        object.tableObject.destroy();
                                        object.loadTableForAffiliates( object );// custom
                                    }
                            });
                        }

                  });
              }
              // end of remove many items

              // approve email
              if ( jQuery('.uap-js-datatable-affiliate-approve-email').length ){
                  jQuery( '.uap-js-datatable-affiliate-approve-email' ).on( 'click', function(evt){
                        jQuery.ajax({
                              type : 'post',
                              url : decodeURI(ajax_url),
                              data : {
                                         action: 'uap_approve_user_email',
                                         uid: jQuery(this).attr('data-uid'),
                                     },
                              success: function (response) {
                                  object.tableObject.destroy();
                                  object.loadTableForAffiliates( object );// custom
                              }
                         });
                  });
              }
              // end of approve email

              // approve affiliate
              if ( jQuery('.uap-js-datatable-affiliates-approve-user').length ){
                  jQuery( '.uap-js-datatable-affiliates-approve-user' ).on( 'click', function(evt){
                      jQuery.ajax({
                          type: 'post',
                          url: decodeURI(window.uap_url)+'/wp-admin/admin-ajax.php',
                          data: {
                                 action: 'uap_approve_affiliate',
                                 uid: jQuery(this).attr('data-uid'),
                          },
                          success: function () {
                              object.tableObject.destroy();
                              object.loadTableForAffiliates( object );// custom
                          }
                      });
                  });
                }
                // end of approve affiliate

                // direct email
                uapAdminSendEmail.init({
                    popupAjax		       : 'uap_admin_send_email_popup',
                  	sendEmailAjax	     : 'uap_admin_do_send_email',
                  	ajaxPath           : decodeURI(window.ajax_url),
                    openPopupSelector  : '.uap-admin-do-send-email-via-uap',
                    sendEmailSelector  : '#indeed_admin_send_mail_submit_bttn',
                    fromSelector       : '#indeed_admin_send_mail_from',
                    toSelector         : '#indeed_admin_send_mail_to',
                    subjectSelector    : '#indeed_admin_send_mail_subject',
                    messageSelector    : '#indeed_admin_send_mail_content',
                    closePopupBttn     : '#uap_send_email_via_admin_close_popup_bttn',
                    popupWrapp         : '#uap_admin_popup_box',
                });
                // end of direct email

              // tipso
              if( jQuery('.uap-js-tipso-item').length ){
                  jQuery('.uap-js-tipso-item').tipso({
                      position: 'top',
                      background: '#555',
                      color: '#fff',
                      useTitle: false,
                  });
              }
              // tipso

          },
          buttons        : object.tableButtons,
          language       : object.translatedLabels,
          columns        : object.columns,
          processing     : true,
          orderCellsTop  : true,
          fixedHeader    : false,
          order          : [],//[ 1, "asc" ]
          scrollToTop    : true,
          stateSave      : true,
          stateSaveCallback: function( settings, data ) {
            jQuery.ajax({
                    type 			: 'post',
                    url 			: decodeURI(ajax_url),
                    data 			: {
                          action     : "uap_ajax_datatable_save_state", // ajax method
                          state      : JSON.stringify(data),
                          type       : 'uap_datatable_state_for-affiliates',
                    },
                    success		: function (response) {
                        return;
                    }
            });
          },
          stateLoadCallback: function(settings) {
              if ( jQuery( '.uap-js-datatable-state' ).length ){
                  return JSON.parse( jQuery( '.uap-js-datatable-state' ).attr( 'data-value' ) );
              }
              return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
          },

          createdRow: function( row, data, dataIndex ) {
              jQuery( row ).attr( 'onmouseover', "uapDhSelector('#affiliate_"+ data.id.value +"', 1);" );// custom
              jQuery( row ).attr( 'onmouseout', "uapDhSelector('#affiliate_"+ data.id.value +"', 0);" );// custom
          }
      });
    },
    // end of affiliates

    // visits
    loadTableForVisits : function( object ){
        object.tableObject = new DataTable( object.tableId, {
          ajax           : {
                  type      : "post",
                  url       : decodeURI(ajax_url),
                  data      : {
                             action             : "uap_ajax_get_visits", // ajax method
                             search_phrase      : jQuery('.uap-js-search-phrase' ).val(), // extra param for search
                             status_in          : jQuery( '.uap-js-datatable-items-status-types' ).val(),
                             start_time         : jQuery('[name=udf]').val(),
                             end_time           : jQuery('[name=udu]').val(),
                  },
                  dataSrc   : 'data'
          },
          //retrieve       : true,
          serverSide     : true,
          dom            : '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BP><"uap-datatable-wrapp"rt>ipl', //
          lengthChange   : true,
          pageLength     : 25,
          drawCallback   : function( response ){
                // ready for export
                jQuery( '.js-uap-export-csv' ).attr( 'data-filters', response.json.params );

                jQuery("body").removeClass("loading"); // remove loading
                jQuery( '#uap-dashboard-table' ).removeClass( 'uap-display-none' );
                jQuery( '.uap-js-select-all-checkboxes' ).attr( 'checked', false );
                jQuery( '.uap-datatable-actions-wrapp' ).html( jQuery('.uap-datatable-actions-wrapp-copy').html() );

                // select items checkbox
                jQuery( '.uap-js-select-all-checkboxes' ).on('click', function(){
                    object.selectUnselectAll( object );
                });

                // remove one item
                jQuery( '.uap-js-remove-one-item' ).on( 'click', function(){
                    var itemId = jQuery( this ).attr( 'data-id' );
                    uapSwal({
                      title: jQuery( '.uap-js-messages-for-datatable' ).attr( 'data-remove_one_item' ),
                      text: "",
                      type: "warning",
                      showCancelButton: true,
                      confirmButtonClass: "btn-danger",
                      confirmButtonText: "OK",
                      closeOnConfirm: true
                    },
                    function(){
                        jQuery.ajax({
                            type : 'post',
                            url : decodeURI(ajax_url),
                            data : {
                                       action                 : 'uap_ajax_remove_one_visit',// custom
                                       uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value'),
                                       id                     :	itemId,
                                   },
                            success: function (response) {
                                object.tableObject.destroy();
                                object.loadTableForVisits( object );// custom
                            }
                       });
                   });
                });
                // end of remove one item

                // remove many items
                if ( jQuery( '.uap-js-items-apply-bttn' ).length ){
                  jQuery( '.uap-js-items-apply-bttn' ).on( 'click', function(evt){
                        evt.preventDefault();
                        var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                            return this.value;
                        }).get();
                        if ( checkedVals.length === 0 ){
                            return;
                        }
                        if ( jQuery('.uap-js-bulk-action-select').val() === 'remove' ){
                            uapSwal({
                              title: jQuery('.uap-js-messages-for-datatable').attr('data-remove_many_items'),
                              text: "",
                              type: "warning",
                              showCancelButton: true,
                              confirmButtonClass: "btn-danger",
                              confirmButtonText: "OK",
                              closeOnConfirm: true
                            },
                            function(){
                                var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                                    return this.value;
                                }).get();
                                var valsToSend = checkedVals.join(",");
                                jQuery.ajax({
                                        type 			: 'post',
                                        url 			: decodeURI(ajax_url),
                                        data 			: {
                                                   action		              : 'uap_ajax_remove_many_visits',// custom
                                                   uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                                   ids			              : valsToSend,
                                        },
                                        success		: function (response) {
                                            object.tableObject.destroy();
                                            object.loadTableForVisits( object );// custom
                                        }
                                });
                           });
                        }
                  });
              }
              // end of remove many items

          },
          buttons        : object.tableButtons,
          language       : object.translatedLabels,
          columns        : object.columns,
          processing     : true,
          orderCellsTop  : true,
          fixedHeader    : false,
          order          : [],
          scrollToTop    : true,
          createdRow: function( row, data, dataIndex ) {
              jQuery( row ).attr( 'onmouseover', "uapDhSelector('#visit_"+ data.id +"', 1);" );// custom
              jQuery( row ).attr( 'onmouseout', "uapDhSelector('#visit_"+ data.id +"', 0);" );// custom
          },
          stateSave      : true,
          stateSaveCallback: function( settings, data ) {
            jQuery.ajax({
                    type 			: 'post',
                    url 			: decodeURI(ajax_url),
                    data 			: {
                          action     : "uap_ajax_datatable_save_state", // ajax method
                          state      : JSON.stringify(data),
                          type       : 'uap_datatable_state_for-visits',
                    },
                    success		: function (response) {
                        return;
                    }
            });
          },
          stateLoadCallback: function(settings) {
              if ( jQuery( '.uap-js-datatable-state' ).length ){
                  return JSON.parse( jQuery( '.uap-js-datatable-state' ).attr( 'data-value' ) );
              }
              return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
          },
      });
    },
    // end of visits

    // referrals
    loadTableForReferrals : function( object ){
        object.tableObject = new DataTable( object.tableId, {
          ajax           : {
                  type      : "post",
                  url       : decodeURI(ajax_url),
                  data      : {
                             action             : "uap_ajax_get_referrals", // ajax method
                             search_phrase      : jQuery('.uap-js-search-phrase' ).val(), // extra param for search
                             status_in          : jQuery( '.uap-js-datatable-items-status-types' ).val(),
                             source             : jQuery('.uap-js-datatable-items-source-types-referrals').val(),
                             start_time         : jQuery('[name=udf]').val(),
                             end_time           : jQuery('[name=udu]').val(),
                  },
                  dataSrc   : 'data'
          },
          //retrieve       : true,
          serverSide     : true,
          dom            : '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BP><"uap-datatable-wrapp"rt>ipl', //
          lengthChange   : true,
          pageLength     : 25,
          drawCallback   : function( response ){
                // ready for export
                jQuery( '.js-uap-export-csv' ).attr( 'data-filters', response.json.params );

                jQuery("body").removeClass("loading"); // remove loading
                jQuery( '#uap-dashboard-table' ).removeClass( 'uap-display-none' );
                jQuery( '.uap-js-select-all-checkboxes' ).attr( 'checked', false );
                jQuery( '.uap-datatable-actions-wrapp' ).html( jQuery('.uap-datatable-actions-wrapp-copy').html() );

                // select items checkbox
                jQuery( '.uap-js-select-all-checkboxes' ).on('click', function(){
                    object.selectUnselectAll( object );
                });

                // remove one item
                jQuery( '.uap-js-remove-one-item' ).on( 'click', function(){
                    var itemId = jQuery( this ).attr( 'data-id' );
                    uapSwal({
                      title: jQuery( '.uap-js-messages-for-datatable' ).attr( 'data-remove_one_item' ),
                      text: "",
                      type: "warning",
                      showCancelButton: true,
                      confirmButtonClass: "btn-danger",
                      confirmButtonText: "OK",
                      closeOnConfirm: true
                    },
                    function(){
                        jQuery.ajax({
                            type : 'post',
                            url : decodeURI(ajax_url),
                            data : {
                                       action                 : 'uap_ajax_remove_one_referral',// custom
                                       uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value'),
                                       id                     :	itemId,
                                   },
                            success: function (response) {
                                object.tableObject.destroy();
                                object.loadTableForReferrals( object );// custom
                            }
                       });
                   });
                });
                // end of remove one item

                // remove many items
                if ( jQuery( '.uap-js-items-apply-bttn' ).length ){
                  jQuery( '.uap-js-items-apply-bttn' ).on( 'click', function(evt){
                        evt.preventDefault();
                        var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                            return this.value;
                        }).get();
                        if ( checkedVals.length === 0 ){
                            return;
                        }
                        if ( jQuery('.uap-js-bulk-action-select').val() === 'remove' ){
                            uapSwal({
                              title: jQuery('.uap-js-messages-for-datatable').attr('data-remove_many_items'),
                              text: "",
                              type: "warning",
                              showCancelButton: true,
                              confirmButtonClass: "btn-danger",
                              confirmButtonText: "OK",
                              closeOnConfirm: true
                            },
                            function(){
                                var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                                    return this.value;
                                }).get();
                                var valsToSend = checkedVals.join(",");
                                jQuery.ajax({
                                        type 			: 'post',
                                        url 			: decodeURI(ajax_url),
                                        data 			: {
                                                   action		              : 'uap_ajax_remove_many_referrals',// custom
                                                   uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                                   ids			              : valsToSend,
                                        },
                                        success		: function (response) {
                                            object.tableObject.destroy();
                                            object.loadTableForReferrals( object );// custom
                                        }
                                });
                           });
                        }
                  });
              }
              // end of remove many items

              // mark as bttns
              if ( jQuery( '.uap-js-referral-list-change-status' ).length ){
                  jQuery( '.uap-js-referral-list-change-status' ).on('click', function(){
                      var newValue = jQuery(this).attr('data-value');
                      jQuery.ajax({
                              type 			: 'post',
                              url 			: decodeURI(ajax_url),
                              data 			: {
                                         action		              : 'uap_ajax_change_status_referral',// custom
                                         uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                         change_status			    : newValue,
                              },
                              success		: function (response) {
                                  object.tableObject.destroy();
                                  object.loadTableForReferrals( object );// custom
                              }
                      });
                  });
              }
          },
          buttons        : object.tableButtons,
          language       : object.translatedLabels,
          columns        : object.columns,
          processing     : true,
          orderCellsTop  : true,
          fixedHeader    : false,
          order          : [],
          scrollToTop    : true,
          createdRow: function( row, data, dataIndex ) {
              jQuery( row ).attr( 'onmouseover', "uapDhSelector('#referral_"+ data.id.value +"', 1);" );// custom
              jQuery( row ).attr( 'onmouseout', "uapDhSelector('#referral_"+ data.id.value +"', 0);" );// custom
          },
          stateSave      : true,
          stateSaveCallback: function( settings, data ) {
            jQuery.ajax({
                    type 			: 'post',
                    url 			: decodeURI(ajax_url),
                    data 			: {
                          action     : "uap_ajax_datatable_save_state", // ajax method
                          state      : JSON.stringify(data),
                          type       : 'uap_datatable_state_for-referrals',
                    },
                    success		: function (response) {
                        return;
                    }
            });
          },
          stateLoadCallback: function(settings) {
              if ( jQuery( '.uap-js-datatable-state' ).length ){
                  return JSON.parse( jQuery( '.uap-js-datatable-state' ).attr( 'data-value' ) );
              }
              return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
          },
      });
    },
    // end of referrals

    loadTableForPayoutSetup         : function( object ){
        object.tableObject = new DataTable('.uap-js-list-payments-for-payouts', {
            retrieve       : true,
            serverSide     : false,
            dom            : 'PBfrtipl',//'<"uap-datatable-top"<"uap-datatable-actions-wrapp">BP><"uap-datatable-wrapp"rt>ipl', // '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BPf>r<"uap-datatable-wrapp"t>ipl'
            lengthChange   : true,
            pageLength     : 25,
            buttons        : object.tableButtons,
            language       : object.translatedLabels,
            processing     : true,
            orderCellsTop  : true,
            fixedHeader    : false,
            order          : [],
            scrollToTop    : true,
            drawCallback   : function( response ){
                  jQuery("body").removeClass("loading"); // remove loading
                  jQuery( '#uap-dashboard-table' ).removeClass( 'uap-display-none' ).addClass( 'display uap-dashboard-table');
            }
        });
    },

    loadTableForPayouts               : function( object ){
      object.tableObject = new DataTable( object.tableId, {
           ajax           : {
                   type      : "post",
                   url       : decodeURI(ajax_url),
                   data      : {
                              action             : "uap_ajax_get_payouts", // ajax method
                              search_phrase      : jQuery('.uap-js-search-phrase' ).val(), // extra param for search
                              status_in          : jQuery( '.uap-js-datatable-items-status-types' ).val(),
                              start_time         : jQuery('[name=udf]').val(),
                              end_time           : jQuery('[name=udu]').val(),
                   },
                   dataSrc   : 'data'
           },
           //retrieve       : true,
           serverSide     : true,
           dom            : '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BP><"uap-datatable-wrapp"rt>ipl', //
           lengthChange   : true,
           pageLength     : 25,
           drawCallback   : function( response ){

                 jQuery("body").removeClass("loading"); // remove loading
                 jQuery( '#uap-dashboard-table' ).removeClass( 'uap-display-none' );
                 jQuery( '.uap-js-select-all-checkboxes' ).attr( 'checked', false );
                 jQuery( '.uap-datatable-actions-wrapp' ).html( jQuery('.uap-datatable-actions-wrapp-copy').html() );

                 // select items checkbox
                 jQuery( '.uap-js-select-all-checkboxes' ).on('click', function(){
                     object.selectUnselectAll( object );
                 });

                 // remove one item
                 jQuery( '.uap-js-remove-one-item' ).on( 'click', function(){
                     var itemId = jQuery( this ).attr( 'data-id' );
                     uapSwal({
                       title: jQuery( '.uap-js-messages-for-datatable' ).attr( 'data-remove_one_item' ),
                       text: "",
                       type: "warning",
                       showCancelButton: true,
                       confirmButtonClass: "btn-danger",
                       confirmButtonText: "OK",
                       closeOnConfirm: true
                     },
                     function(){
                         jQuery.ajax({
                             type : 'post',
                             url : decodeURI(ajax_url),
                             data : {
                                        action                 : 'uap_ajax_remove_one_payout',// custom
                                        uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value'),
                                        id                     :	itemId,
                                    },
                             success: function (response) {
                                 object.tableObject.destroy();
                                 object.loadTableForPayouts( object );// custom
                             }
                        });
                    });
                 });
                 // end of remove one item

                 // remove many items
                 if ( jQuery( '.uap-js-items-apply-bttn' ).length ){
                   jQuery( '.uap-js-items-apply-bttn' ).on( 'click', function(evt){
                         evt.preventDefault();
                         var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                             return this.value;
                         }).get();
                         if ( checkedVals.length === 0 ){
                             return;
                         }
                         if ( jQuery('.uap-js-bulk-action-select').val() === 'remove' ){
                             uapSwal({
                               title: jQuery('.uap-js-messages-for-datatable').attr('data-remove_many_items'),
                               text: "",
                               type: "warning",
                               showCancelButton: true,
                               confirmButtonClass: "btn-danger",
                               confirmButtonText: "OK",
                               closeOnConfirm: true
                             },
                             function(){
                                 var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                                     return this.value;
                                 }).get();
                                 var valsToSend = checkedVals.join(",");
                                 jQuery.ajax({
                                         type 			: 'post',
                                         url 			: decodeURI(ajax_url),
                                         data 			: {
                                                    action		              : 'uap_ajax_remove_many_payouts',// custom
                                                    uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                                    ids			              : valsToSend,
                                         },
                                         success		: function (response) {
                                             object.tableObject.destroy();
                                             object.loadTableForPayouts( object );// custom
                                         }
                                 });
                            });
                         }
                   });
               }
               // end of remove many items

               // generate CSV file
               if ( jQuery( '.uap-js-payouts-generate-csv-for-payout' ).length ){

                   jQuery( '.uap-js-payouts-generate-csv-for-payout' ).on('click', function(){
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
                                  jQuery( '.uap-js-payouts-csv-file-for-payout-' + payoutID).removeClass('uap-display-none').addClass('uap-display-block')
                                  jQuery( '.uap-js-payouts-csv-file-for-payout-' + payoutID).attr( 'href', response );
                               }
                       });
                   });
               }
               // end of generate CSV file
           },
           buttons        : object.tableButtons,
           language       : object.translatedLabels,
           columns        : object.columns,
           processing     : true,
           orderCellsTop  : true,
           fixedHeader    : false,
           order          : [],
           scrollToTop    : true,
           createdRow: function( row, data, dataIndex ) {},
           stateSave      : true,
           stateSaveCallback: function( settings, data ) {
             jQuery.ajax({
                     type 			: 'post',
                     url 			: decodeURI(ajax_url),
                     data 			: {
                           action     : "uap_ajax_datatable_save_state", // ajax method
                           state      : JSON.stringify(data),
                           type       : 'uap_datatable_state_for-payouts',
                     },
                     success		: function (response) {
                         return;
                     }
             });
           },
           stateLoadCallback: function(settings) {
               if ( jQuery( '.uap-js-datatable-state' ).length ){
                   return JSON.parse( jQuery( '.uap-js-datatable-state' ).attr( 'data-value' ) );
               }
               return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
           },
       });
    },

    loadTableForPayments         : function( object ){
      object.tableObject = new DataTable( object.tableId, {
           ajax           : {
                   type      : "post",
                   url       : decodeURI(ajax_url),
                   data      : {
                              action             : "uap_ajax_get_payments", // ajax method
                              search_phrase      : jQuery('.uap-js-search-phrase' ).val(), // extra param for search
                              status_in          : jQuery( '.uap-js-datatable-items-status-types' ).val(),
                              start_time         : jQuery('[name=udf]').val(),
                              end_time           : jQuery('[name=udu]').val(),
                              payout_id          : jQuery('.uap-js-payout-id').val()
                   },
                   dataSrc   : 'data'
           },
           //retrieve       : true,
           serverSide     : true,
           dom            : '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BP><"uap-datatable-wrapp"rt>ipl', //
           lengthChange   : true,
           pageLength     : 300,
           drawCallback   : function( response ){

                 jQuery("body").removeClass("loading"); // remove loading
                 jQuery( '#uap-dashboard-table' ).removeClass( 'uap-display-none' );
                 jQuery( '.uap-js-select-all-checkboxes' ).attr( 'checked', false );
                 jQuery( '.uap-datatable-actions-wrapp' ).html( jQuery('.uap-datatable-actions-wrapp-copy').html() );

                 // select items checkbox
                 jQuery( '.uap-js-select-all-checkboxes' ).on('click', function(){
                     object.selectUnselectAll( object );
                 });

                 // remove one item
                 jQuery( '.uap-js-remove-one-item' ).on( 'click', function(){
                     var itemId = jQuery( this ).attr( 'data-id' );
                     uapSwal({
                       title: jQuery( '.uap-js-messages-for-datatable' ).attr( 'data-remove_one_item' ),
                       text: "",
                       type: "warning",
                       showCancelButton: true,
                       confirmButtonClass: "btn-danger",
                       confirmButtonText: "OK",
                       closeOnConfirm: true
                     },
                     function(){
                         jQuery.ajax({
                             type : 'post',
                             url : decodeURI(ajax_url),
                             data : {
                                        action                 : 'uap_ajax_remove_one_payment',// custom
                                        uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value'),
                                        id                     :	itemId,
                                    },
                             success: function (response) {
                                 object.tableObject.destroy();
                                 object.loadTableForPayments( object );// custom
                             }
                        });
                    });
                 });
                 // end of remove one item

                 // remove many items
                 if ( jQuery( '.uap-js-items-apply-bttn' ).length ){
                   jQuery( '.uap-js-items-apply-bttn' ).on( 'click', function(evt){
                         evt.preventDefault();
                         var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                             return this.value;
                         }).get();
                         if ( checkedVals.length === 0 ){
                             return;
                         }
                         if ( jQuery('.uap-js-bulk-action-select').val() === 'remove' ){
                             uapSwal({
                               title: jQuery('.uap-js-messages-for-datatable').attr('data-remove_many_items'),
                               text: "",
                               type: "warning",
                               showCancelButton: true,
                               confirmButtonClass: "btn-danger",
                               confirmButtonText: "OK",
                               closeOnConfirm: true
                             },
                             function(){
                                 var checkedVals = jQuery('.uap-js-table-select-item:checkbox:checked').map(function() {
                                     return this.value;
                                 }).get();
                                 var valsToSend = checkedVals.join(",");
                                 jQuery.ajax({
                                         type 			: 'post',
                                         url 			: decodeURI(ajax_url),
                                         data 			: {
                                                    action		              : 'uap_ajax_remove_many_payments',// custom
                                                    uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                                    ids			              : valsToSend,
                                         },
                                         success		: function (response) {
                                             object.tableObject.destroy();
                                             object.loadTableForPayments( object );// custom
                                         }
                                 });
                            });
                         }
                   });
               }
               // end of remove many items

           },
           buttons        : object.tableButtons,
           language       : object.translatedLabels,
           columns        : object.columns,
           processing     : true,
           orderCellsTop  : true,
           fixedHeader    : false,
           order          : [],
           scrollToTop    : true,
           createdRow: function( row, data, dataIndex ) {},
           stateSave      : true,
           stateSaveCallback: function( settings, data ) {
             jQuery.ajax({
                     type 			: 'post',
                     url 			: decodeURI(ajax_url),
                     data 			: {
                           action     : "uap_ajax_datatable_save_state", // ajax method
                           state      : JSON.stringify(data),
                           type       : 'uap_datatable_state_for-payments',
                     },
                     success		: function (response) {
                         return;
                     }
             });
           },
           stateLoadCallback: function(settings) {
               if ( jQuery( '.uap-js-datatable-state' ).length ){
                   return JSON.parse( jQuery( '.uap-js-datatable-state' ).attr( 'data-value' ) );
               }
               return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
           },
       });
    },

    loadTableForReferralsForPayment         : function( object ){
        object.tableObject = new DataTable('.uap-js-list-payments-for-payouts', {
            retrieve       : true,
            serverSide     : false,
            dom            : 'PBfrtipl',//'<"uap-datatable-top"<"uap-datatable-actions-wrapp">BP><"uap-datatable-wrapp"rt>ipl', // '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BPf>r<"uap-datatable-wrapp"t>ipl'
            lengthChange   : true,
            pageLength     : 25,
            buttons        : object.tableButtons,
            language       : object.translatedLabels,
            processing     : true,
            orderCellsTop  : true,
            fixedHeader    : false,
            order          : [],
            scrollToTop    : true,
            drawCallback   : function( response ){
                  jQuery("body").removeClass("loading"); // remove loading
                  jQuery( '#uap-dashboard-table' ).removeClass( 'uap-display-none' ).addClass( 'display uap-dashboard-table');
            }
        });
    },

    loadTableForPayoutPayments         : function( object ){
        object.tableObject = new DataTable('.uap-js-list-payments-for-payouts', {
            retrieve       : true,
            serverSide     : false,
            dom            : 'PBfrtipl',//'<"uap-datatable-top"<"uap-datatable-actions-wrapp">BP><"uap-datatable-wrapp"rt>ipl', // '<"uap-datatable-top"<"uap-datatable-actions-wrapp">BPf>r<"uap-datatable-wrapp"t>ipl'
            lengthChange   : true,
            pageLength     : 25,
            buttons        : object.tableButtons,
            language       : object.translatedLabels,
            processing     : true,
            orderCellsTop  : true,
            fixedHeader    : false,
            order          : [],
            scrollToTop    : true,
            drawCallback   : function( response ){
                  jQuery("body").removeClass("loading"); // remove loading
                  jQuery( '#uap-dashboard-table' ).removeClass( 'uap-display-none' ).addClass( 'display uap-dashboard-table');

                  // change one status
                  if ( jQuery( '.uap-js-payout-change-status-for-payment').length ){
                      jQuery( '.uap-js-payout-change-status-for-payment').on( 'click', function(){
                          var status = jQuery( this ).attr('data-status');
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
                  // end of change one status

                  // change all statuses for payout payments
                  if ( jQuery( '.uap-js-payout-change-status-all-payments').length ){
                      jQuery( '.uap-js-payout-change-status-all-payments').on( 'click', function(){
                          var status = jQuery( this ).attr('data-status');
                          var payoutId = jQuery( this ).attr('data-id');
                          jQuery.ajax({
                                  type 			: 'post',
                                  url 			: decodeURI(ajax_url),
                                  data 			: {
                                             action		              : 'uap_ajax_payout_all_payments_change_status',// custom
                                             uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                             id			                : payoutId,
                                             status                 : status
                                  },
                                  success		: function (response) {
                                      location.reload();
                                  }
                          });
                      });
                  }
                  // end of change all statuses for payout payments

                  // delete one payment
                  if ( jQuery( '.uap-js-payout-delete-payment' ).length ){
                      jQuery( '.uap-js-payout-delete-payment' ).on( 'click', function(){
                          uapSwal({
                            title: jQuery('.uap-js-messages-for-datatable').attr('data-remove_one_item'),
                            text: "",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonClass: "btn-danger",
                            confirmButtonText: "OK",
                            closeOnConfirm: true
                          },
                          function(){
                              var paymentId = jQuery( this ).attr('data-id');
                              jQuery.ajax({
                                      type 			: 'post',
                                      url 			: decodeURI(ajax_url),
                                      data 			: {
                                                 action		              : 'uap_ajax_remove_one_payment',// custom
                                                 uap_admin_forms_nonce	:	jQuery( '.uap-js-datatable-listing-delete-nonce' ).attr( 'data-value' ),
                                                 id			                : paymentId,
                                      },
                                      success		: function (response) {
                                          location.reload();
                                      }
                              });
                         });
                      });
                  }
                  // delete one payment

            }
        });
    },

    selectUnselectAll     : function(){
        let doSelect = jQuery( '.uap-js-select-all-checkboxes' ).is(':checked');
        if ( doSelect ){
            jQuery( '.uap-js-table-select-item' ).each( function(){
                jQuery( this ).attr( 'checked', true );
            });
        } else {
            jQuery( '.uap-js-table-select-item' ).each( function(){
                jQuery( this ).attr( 'checked', false );
            });
        }
    }
};

jQuery( window ).on('load', function(){

    UapDataTable.init([]);

});

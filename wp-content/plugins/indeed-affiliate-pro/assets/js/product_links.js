/*
 *Ultimate Affiliate Pro - Front-end Product Links
 */
"use strict";
var uapProductLinks = {
    searchFieldSelector              : '',
    listProductsSelector             : '',
    offset                           : 0,
    ajaxResponse                     : '',
    affiliateLinkTrigger             : '',
    showMoreSelector                 : '',
    closePopupSelector               : '',
    popupSelector                    : '',
    popupSelectorWindow              : '',
    refTypeSelector                  : '',
    friendlyLinksSelector            : '',
    searchCategorySelector           : '',
    loadingGif                       : '',
    orderSelector                    : '',
    countSelector                    : '',
    productLinksLabelsOn             : false,

    init: function(args){
        var obj = this;
        obj.setAttributes(obj, args);

        window.addEventListener( 'DOMContentLoaded', function(){
            if ( jQuery('.uap-js-product-links-include-label').length ){
                obj.productLinksLabelsOn = jQuery('.uap-js-product-links-include-label').attr('data-enabled');
            } else {
                obj.productLinksLabelsOn = true;
            }

            obj.loadingGif = jQuery( obj.listProductsSelector ).attr( 'data-loading_more' );

            // search
            jQuery( obj.searchFieldSelector ).keyup( function(){
                obj.handleKeyUp( obj );
            })
            jQuery( obj.searchCategorySelector ).on( 'change', function(){
                obj.handleKeyUp( obj );
            })
            jQuery( obj.orderSelector ).on( 'change', function(){
                obj.handleKeyUp( obj );
            })
            // load more action
            jQuery( document ).on( 'click', obj.showMoreSelector, function( e ){
                obj.handleLoadMore( obj );
            })

            /// after page was loaded ... load the results
            obj.getProducts( obj, obj.writeSearchResult );

            /// show affiliate link
            jQuery( document ).on( 'click', obj.affiliateLinkTrigger, function( e ){
                obj.handleShowAffiliateLink( e, obj );
            })

            /// close popup
            jQuery( document ).on( 'click', obj.closePopupSelector, function( e ){
                obj.handleClosePopup( obj, e );
            })

            jQuery( document ).mouseup(function(e){
                var container = jQuery( obj.popupSelectorWindow );
                if ( !container.is(e.target) && container.has(e.target).length === 0){
                    obj.handleClosePopup( obj, e );
                }
            })

            /// update links
            jQuery( document ).on( 'change', obj.refTypeSelector, function( e ){
                obj.handleUpdateLink( obj, e );
            })
            jQuery( document ).on( 'change', obj.friendlyLinksSelector, function( e ){
                obj.handleUpdateLink( obj, e );
            })

        })

    },

    getProducts: function( obj, callback ){
        var searchValue = jQuery( obj.searchFieldSelector ).val();
        var category = jQuery( obj.searchCategorySelector ).val();
        jQuery.ajax({
            type    : "post",
            url     : decodeURI(ajax_url),
            data    : {
                       action       : "uap_search_product_for_product_links",
                       search       : searchValue,
                       offset       : obj.offset,
                       category     : category,
                       orderBy      : jQuery( obj.orderSelector ).val(),
                       labelsOn     : obj.productLinksLabelsOn
            },
            success : function (response) {
                obj.ajaxResponse = JSON.parse( response );
                obj.offset = obj.ajaxResponse.offset;
				        obj.count = obj.ajaxResponse.count;
                jQuery( obj.countSelector ).html( obj.count );
                callback( obj );
            }
        })
    },

    handleKeyUp: function( obj ){
        jQuery( obj.showMoreSelector ).remove();
        jQuery( obj.listProductsSelector ).html( '<img src="' + obj.loadingGif + '" />' );
        obj.offset = 0;
        obj.getProducts( obj, obj.writeSearchResult );
    },

  	setAttributes: function(obj, args){
    		for (var key in args) {
    			obj[key] = args[key];
    		}
  	},

    writeSearchResult:  function( obj, response ){
        jQuery( obj.listProductsSelector ).fadeOut( 200, function(){
            jQuery( obj.listProductsSelector ).html( obj.ajaxResponse.html );
            jQuery( obj.listProductsSelector ).fadeIn( 100 );
        })
        obj.handleLoadMoreBttn( obj );
    },

    handleLoadMore: function( obj ){
        obj.getProducts( obj, obj.handleAppendResults );
    },

    handleAppendResults: function( obj ){
        jQuery( obj.listProductsSelector ).append( obj.ajaxResponse.html );
        obj.handleLoadMoreBttn( obj );
    },

    handleShowAffiliateLink: function( e, obj ){
        jQuery.ajax({
            type    : "post",
            url     : decodeURI(ajax_url),
            data    : {
                       action       : "uap_product_link_popup",
                       postId       : jQuery( e.target ).attr( 'data-post_id' ),
                       labelsOn     : obj.productLinksLabelsOn
            },
            success : function( response ){
                jQuery( 'body' ).append( response );
            }
        });
    },

    handleLoadMoreBttn: function( obj ){
        if ( obj.ajaxResponse.showMore == 1 ){
            if ( jQuery( obj.showMoreSelector ).length > 0 ){
                return;
            }
            var loadMore = jQuery( obj.listProductsSelector ).attr( 'data-load_more_label' );
            var loadMoreHtml = '<div class="uap-show-more-products-bttn" id="uap_show_more_products_bttn" >' + loadMore + '</div>';
            jQuery( obj.listProductsSelector ).after( loadMoreHtml );
        } else {
            jQuery( obj.showMoreSelector ).remove();
        }
    },

    handleClosePopup: function( obj, evt ){
        jQuery( obj.popupSelector ).remove();
    },

    handleUpdateLink: function( obj, evt ){
        jQuery.ajax({
    	      type 	: "post",
    	      url 	: decodeURI(ajax_url),
    	      data 	: {
    	                   action							: "uap_ia_ajax_return_url_for_aff",
    	                   aff_id							: jQuery( '#uap_show_link_affiliate_id' ).attr( 'data-affiliate_id' ),
                         post_id            : jQuery( '#uap_show_link_affiliate_id' ).attr( 'data-post_id' ),
    	                   url								: window.location.href,
    	                   campaign						: '',
    	                   slug								: jQuery( '#uap_show_link_ref_type' ).val(),
    	                   friendly_links			: jQuery( '#uap_show_link_friendly_link' ).val(),
                         labelsOn           : obj.productLinksLabelsOn
    	      },
    	      success		: function ( response ) {
    	      		if ( response ){
      	        		var obj = JSON.parse( response );
      	        		var theUrl = obj.url;
      	        		if ( theUrl != 0 ){
      	                	jQuery('.uap-js-show-links-the-link').val( theUrl );
      	        		}
    	        	}
    	      }
    	  });
    },

}

uapProductLinks.init({
    searchFieldSelector               : '#uap_product_links_search_bar',
    listProductsSelector              : '.uap-product-links-items-wrapper',
    offset                            : 0,
    ajaxResponse                      : '',
    affiliateLinkTrigger              : '.js-uap-affiliate-product-affiliate-link',
    showMoreSelector                  : '#uap_show_more_products_bttn',
    closePopupSelector                : '.uap-js-close-product-link-popup',
    popupSelector                     : '.uap-show-link-popup-wrapp',
    popupSelectorWindow               : '.uap-the-popup',
    refTypeSelector                   : '#uap_show_link_ref_type',
    friendlyLinksSelector             : '#uap_show_link_friendly_link',
    searchCategorySelector            : '.uap-js-product-links-category',
    orderSelector                     : '.uap-js-product-links-order-type',
    countSelector                     : '.uap-js-product-links-count',
});

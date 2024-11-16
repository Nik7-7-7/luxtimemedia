/*
* Ultimate Membership Pro - Search & Filter Members
*/
"use strict";
var ihcSearchUsers = {
    itemSelectSelector          : '',
    itemOrderSelector           : '',
    baseLinkSelector            : '',
    selectAllLevelsSelector     : '',
    deselectAllLevelsSelector   : '',
    selectAllRolesSelector      : '',
    deselectAllRolesSelector    : '',
    limitSelector               : '',

    levels                      : [],
    roles                       : [],
    levelStatus                 : [],
    approvelRequest             : [],
    emailVerification           : [],
    order                       : '',

    init: function(args){
        var obj = this;
        obj.setAttributes(obj, args);


            /// select
            jQuery( obj.itemSelectSelector ).on('click', function( evt ){
                var oldValue = jQuery( evt.target ).attr( 'data-enabled' );
                if ( oldValue == 1 ){
                    jQuery( evt.target ).attr( 'data-enabled', 0 );
                    jQuery( evt.target ).removeClass( 'ihc-green-bttn' ).addClass( 'ihc-gray-bttn' );
                } else {
                    jQuery( evt.target ).attr( 'data-enabled', 1 );
                    jQuery( evt.target ).removeClass( 'ihc-gray-bttn' ).addClass( 'ihc-green-bttn' );
                }
                obj.handleSelection( obj );
            });

            /// order
            jQuery( obj.itemOrderSelector ).on('click', function( evt ){
                jQuery( obj.itemOrderSelector ).each(function(){
                    jQuery( this ).removeClass( 'ihc-green-bttn' ).addClass( 'ihc-gray-bttn' );
                });
                var oldValue = jQuery( evt.target ).attr( 'data-enabled' );
                if ( oldValue == 1 ){
                    jQuery( evt.target ).attr( 'data-enabled', 0 );
                    jQuery( evt.target ).removeClass( 'ihc-green-bttn' ).addClass( 'ihc-gray-bttn' );
                    obj.order = '';
                } else {
                    jQuery( evt.target ).attr( 'data-enabled', 1 );
                    jQuery( evt.target ).removeClass( 'ihc-gray-bttn' ).addClass( 'ihc-green-bttn' );
                    obj.order = jQuery( evt.target ).attr( 'data-value' );
                }
                obj.handleSelection( obj );
            });

            /// advanced order
            jQuery( obj.advancedOrderSelector ).on( 'click', function( evt ){
                jQuery( obj.advancedOrderSelector ).each(function(){
                    jQuery( this ).removeClass( 'ihc-green-bttn' ).addClass( 'ihc-gray-bttn' );
                });
                var baseUrl = jQuery( obj.baseLinkSelector ).attr( 'data-base_link' );
                var oldValue = jQuery( evt.target ).attr( 'data-enabled' );
                if ( oldValue == 1 ){
                    jQuery( evt.target ).attr( 'data-enabled', 0 );
                    jQuery( evt.target ).removeClass( 'ihc-green-bttn' ).addClass( 'ihc-gray-bttn' );
                } else {
                    jQuery( evt.target ).attr( 'data-enabled', 1 );
                    jQuery( evt.target ).removeClass( 'ihc-gray-bttn' ).addClass( 'ihc-green-bttn' );
                    baseUrl += '&advancedOrder=' + jQuery( evt.target ).attr( 'data-value' );
                }
                window.location.href = baseUrl;
            });

            /// select all levels
            jQuery( obj.selectAllLevelsSelector ).on( 'click', function(){
                jQuery( obj.itemSelectSelector ).each(function(){
                    if ( jQuery(this).attr( 'data-name' ) == 'levels' ){
                        jQuery( this ).removeClass( 'ihc-gray-bttn' ).addClass( 'ihc-green-bttn' );
                        jQuery( this ).attr( 'data-enabled', 1 );
                    }
                });
                obj.handleSelection( obj );
            });

            /// deselect all levels
            jQuery( obj.deselectAllLevelsSelector ).on( 'click', function(){
                jQuery( obj.itemSelectSelector ).each(function(){
                    if ( jQuery(this).attr( 'data-name' ) == 'levels' ){
                        jQuery( this ).removeClass( 'ihc-green-bttn' ).addClass( 'ihc-gray-bttn' );
                        jQuery( this ).attr( 'data-enabled', 0 );
                    }
                });
                obj.handleSelection( obj );
            });

            /// select all roles
            jQuery( obj.selectAllRolesSelector ).on( 'click', function(){
                jQuery( obj.itemSelectSelector ).each(function(){
                    if ( jQuery(this).attr( 'data-name' ) == 'roles' ){
                        jQuery( this ).removeClass( 'ihc-gray-bttn' ).addClass( 'ihc-green-bttn' );
                        jQuery( this ).attr( 'data-enabled', 1 );
                    }
                });
                obj.handleSelection( obj );
            });

            /// deselect all roles
            jQuery( obj.deselectAllRolesSelector ).on( 'click', function(){
                jQuery( obj.itemSelectSelector ).each(function(){
                    if ( jQuery(this).attr( 'data-name' ) == 'roles' ){
                        jQuery( this ).removeClass( 'ihc-green-bttn' ).addClass( 'ihc-gray-bttn' );
                        jQuery( this ).attr( 'data-enabled', 0 );
                    }
                });
                obj.handleSelection( obj );
            });

    },

  	setAttributes: function(obj, args){
    		for (var key in args) {
    			  obj[key] = args[key];
    		}
  	},

    handleSelection: function( obj ){
        var baseUrl = jQuery( obj.baseLinkSelector ).attr( 'data-base_link' );
        jQuery( obj.itemSelectSelector ).each(function(){
            var currentName = jQuery( this ).attr( 'data-name' );
            var theValue = jQuery( this ).attr( 'data-value' );
            if ( jQuery( this ).attr( 'data-enabled' ) == 1 && !obj.indexExists( obj[ currentName ], theValue) ){
                obj[ currentName ].push( theValue );
            } else if ( jQuery( this ).attr( 'data-enabled' ) == 0 && obj.indexExists( obj[ currentName ], theValue) ){
              var index = obj[ currentName ].indexOf( theValue );
              if (index > -1) {
                  obj[ currentName ].splice(index, 1);
              }
            }
        });

        if ( obj.levels ){
            var levels = obj.levels.join( ',' );
            if ( levels != '' ){
                baseUrl += '&levels=' + levels;
            }
        }

        if ( obj.roles ){
            var roles = obj.roles.join( ',' );
            if ( roles != '' ){
                baseUrl += '&roles=' + roles;
            }
        }

        if ( obj.levelStatus ){
            var levelStatus = obj.levelStatus.join( ',' );
            if ( levelStatus != '' ){
                baseUrl += '&levelStatus=' + levelStatus;
            }
        }

        if ( obj.order ){
            baseUrl += '&order=' + obj.order;
        }
        if ( obj.approvelRequest[0] == 1 ){
            baseUrl += '&approvelRequest=1';
        }
        if ( obj.emailVerification[0] == 1 ){
            baseUrl += '&emailVerification=1';
        }

        baseUrl += '&ihc_limit=' + jQuery(obj.limitSelector).val();
        //alert(1);

        /// and do redirect
        window.location.href = baseUrl;

    },

    indexExists: function( obj, searchValue ){
        var exists = false;
        Object.keys( obj ).forEach(function(key) {
          if ( obj[key] == searchValue) {
              exists = true;
          }
        });
        return exists;
    },

};

jQuery( window ).on( 'load', function(){
    ihcSearchUsers.init({
        itemSelectSelector          : '.js-ihc-search-select',
        itemOrderSelector           : '.js-ihc-search-order',
        baseLinkSelector            : '#ihc_search_user_base_field',
        advancedOrderSelector       : '.js-ihc-search-advanced-order',
        selectAllLevelsSelector     : '.js-ihc-select-all-levels',
        deselectAllLevelsSelector   : '.js-ihc-deselect-all-levels',
        selectAllRolesSelector      : '.js-ihc-select-all-roles',
        deselectAllRolesSelector    : '.js-ihc-deselect-all-roles',
        limitSelector               : '.js-ihc-search-users-limit'
    });
});

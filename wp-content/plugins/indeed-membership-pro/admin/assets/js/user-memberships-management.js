/*
* Ultimate Membership Pro - Manage Members Memberships
*/
"use strict";
var IhcUserMembershipManagement = {

    init: function(){
        var object = this;
        jQuery(window).on('load', function(){

            // date picker
            object.activateDatePicker();

            // add new level
            jQuery( '.ihc-js-add-new-membership-to-user-bttn' ).on( 'click', function(){
                object.submitNewSubscription( object );
            });

            jQuery( '.ihc-js-activate-user-level' ).on( 'click', function( e, html ){
                object.activateLevel( e );
            });

            jQuery( '.ihc-js-pause-user-level' ).on( 'click', function( e, html ){
                object.pauseSubscription( e );
            });

            jQuery( '.ihc-js-reactivate-user-level' ).on( 'click', function( e, html ){
                object.reactivateSubscription( e );
            });

            jQuery( '.ihc-js-set-at-canceled-user-level' ).on( 'click', function( e, html ){
                object.setAsCanceled( e );
            });

        });

        // remove level from user
        jQuery( 'body' ).on( 'click', '.ihc-js-delete-user-level', function( e, html ){
            object.removeLevel( e );
        });


        // update expire time
        jQuery( 'body' ).on( 'click', '.ihc-js-renew-user-level', function( e, html ){
            object.updateExpireTime( e );
        });
    },

    submitNewSubscription: function( object ){
        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url) + '/wp-admin/admin-ajax.php',
            data : {
                       action     : 'ihc_get_membership_details',
                       levelId    : jQuery( '.ihc-select-level' ).val(),
                       uid        : jQuery( '.ihc-select-level' ).attr( 'data-uid' )
            },
            success: function ( response ) {
                if ( response == 0 ){
                    jQuery( '.ihc-select-level' ).val( -1 );
                    return false;
                }
                jQuery('.ihc-js-membership-table').removeClass( 'ihc-display-none' );
                jQuery( '.ihc-js-membership-table' ).find( 'tbody' ).append( response );
                jQuery( '.ihc-select-level' ).val( -1 );
                object.activateDatePicker();
            }
       });
    },

    activateDatePicker: function(){
        jQuery('.start_input_text').datepicker({
            dateFormat : 'yy-mm-dd',
            onSelect: function(datetext){
                var d = new Date();
                datetext = datetext+" "+d.getHours()+":"+ihcAddZero(d.getMinutes())+":"+ihcAddZero(d.getSeconds());
                jQuery(this).val(datetext);
            }
        });
        jQuery('.expire_input_text').datepicker({
            dateFormat : 'yy-mm-dd',
            onSelect: function(datetext){
                if ( datetext == '' || datetext == null ){
                  jQuery( '#' + jQuery(this).parent().parent().attr('id') + ' .ihc-level-status' ).html( 'Hold').attr( 'class', '' ).attr( 'class', 'ihc-level-status ihc-level-status-Expired');
                }
                var d = new Date();
                datetext = datetext+" "+d.getHours()+":"+ihcAddZero(d.getMinutes())+":"+ihcAddZero(d.getSeconds());
                jQuery(this).val(datetext);
                var currentTimestamp = ( new Date().getTime()/1000 );
                var selectedTimestamp = (new Date(datetext).getTime() / 1000 );
                if ( currentTimestamp > selectedTimestamp ){
                    jQuery( '#' + jQuery(this).parent().parent().attr('id') + ' .ihc-level-status' ).html( 'Expired').attr( 'class', '' ).attr( 'class', 'ihc-level-status ihc-level-status-Expired');
                } else {
                    jQuery( '#' + jQuery(this).parent().parent().attr('id') + ' .ihc-level-status' ).html( 'Active' ).attr( 'class', '' ).attr( 'class', 'ihc-level-status ihc-level-status-Active');
                }

            }
        });
    },

    removeLevel: function( e ){
        var level = jQuery( e.target ).attr( 'data-lid' );
        jQuery( '.ihc-js-user-level-row-' + level ).remove();
        jQuery( '.ihc-js-user-subscriptions-wrapper' ).append( '<input type="hidden" name="ihc_delete_levels[]" value="' + level + '" />' );
    },

    updateExpireTime: function( e ){
        var level = jQuery( e.target ).attr( 'data-lid' );
        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url) + '/wp-admin/admin-ajax.php',
            data : {
                       action                 : 'ihc_user_level_get_next_expire_time',
                       levelId                : level,
                       currentExpireTime      : jQuery( '.ihc-js-user-level-row-' + level + ' .expire_input_text ' ).val()
            },
            success: function ( response ) {
                if ( response ){
                    var data = JSON.parse( response );
                    jQuery( '.ihc-js-user-level-row-' + level + ' td .expire_input_text' ).val( data.expire_time );
                    jQuery( '.ihc-js-user-level-row-' + level + ' .ihc-js-subscription-status' ).html( data.new_status );
                    jQuery( e.target ).remove();
                }
            }
       });
    },

    activateLevel: function( e ){
        var level = jQuery( e.target ).attr( 'data-lid' );
        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url) + '/wp-admin/admin-ajax.php',
            data : {
                       action                 : 'ihc_user_level_get_next_expire_time',
                       levelId                : level,
                       currentExpireTime      : jQuery( '.ihc-js-user-level-row-' + level + ' .expire_input_text ' ).val()
            },
            success: function ( response ) {
                if ( response ){
                    var data = JSON.parse( response );
                    jQuery( '.ihc-js-user-level-row-' + level + ' td .expire_input_text' ).val( data.expire_time );
                    jQuery( '.ihc-js-user-level-row-' + level + ' .ihc-js-subscription-status' ).html( data.new_status );
                    jQuery( e.target ).remove();
                }
            }
       });
    },

    pauseSubscription: function( e ){
        var level = jQuery( e.target ).attr( 'data-lid' );
        var subscriptionId = jQuery( e.target ).attr( 'data-subscription_id' );
        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url) + '/wp-admin/admin-ajax.php',
            data : {
                       action                 : 'ihc_user_level_pause',
                       levelId                : level,
                       uid                    : jQuery( '.ihc-select-level' ).attr( 'data-uid' ),
                       subscriptionId         : subscriptionId,
                       currentExpireTime      : jQuery( '.ihc-js-user-level-row-' + level + ' .expire_input_text ' ).val()
            },
            success: function ( response ) {
                if ( response ){
                    var data = JSON.parse( response );
                    jQuery( '.ihc-js-user-level-row-' + level + ' td .expire_input_text' ).val( data.expire_time );
                    jQuery( '.ihc-js-user-level-row-' + level + ' td .expire_input_text' ).after( '<input type="hidden" value="' + data.remain_time + '" name="subscription_meta[' + subscriptionId + '][remain_time]" />' );
                    jQuery( '.ihc-js-user-level-row-' + level + ' td .expire_input_text' ).after( '<input type="hidden" value="4" name="subscription_status[' + subscriptionId + ']" />' );
                    jQuery( '.ihc-js-user-level-row-' + level + ' .ihc-js-subscription-status' ).html( data.new_status );
                    jQuery( e.target ).remove();
                }
            }
       });
    },

    reactivateSubscription: function( e ){
        var level = jQuery( e.target ).attr( 'data-lid' );
        var subscriptionId = jQuery( e.target ).attr( 'data-subscription_id' );
        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url) + '/wp-admin/admin-ajax.php',
            data : {
                       action                 : 'ihc_user_level_reactivate',
                       levelId                : level,
                       uid                    : jQuery( '.ihc-select-level' ).attr( 'data-uid' ),
                       subscriptionId         : subscriptionId
            },
            success: function ( response ) {
                if ( response ){
                    var data = JSON.parse( response );
                    jQuery( '.ihc-js-user-level-row-' + level + ' td .expire_input_text' ).val( data.expire_time );
                    jQuery( '.ihc-js-user-level-row-' + level + ' td .start_input_text' ).val( data.start_time );
                    jQuery( '.ihc-js-user-level-row-' + level + ' td .expire_input_text' ).after( '<input type="hidden" value="1" name="subscription_status[' + subscriptionId + ']" />' );
                    jQuery( '.ihc-js-user-level-row-' + level + ' td .expire_input_text' ).after( '<input type="hidden" value="0" name="subscription_meta[' + subscriptionId + '][remain_time]" />' );
                    jQuery( '.ihc-js-user-level-row-' + level + ' .ihc-js-subscription-status' ).html( data.new_status );
                    jQuery( e.target ).remove();
                }
            }
       });
    },

    setAsCanceled: function( e ){
        var newLabel = jQuery( e.target ).attr( 'data-new_label' );
        var level = jQuery( e.target ).attr( 'data-lid' );
        var subscriptionId = jQuery( e.target ).attr( 'data-subscription_id' );
        jQuery( '.ihc-js-user-level-row-' + level + ' td .expire_input_text' ).after( '<input type="hidden" value="0" name="subscription_status[' + subscriptionId + ']" />' );
        jQuery( '.ihc-js-user-level-row-' + level + ' .ihc-js-subscription-status' ).html( newLabel );
        jQuery( e.target ).remove();
    }

};

IhcUserMembershipManagement.init();

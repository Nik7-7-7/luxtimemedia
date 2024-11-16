/*
* Ultimate Learning Pro - Announcements Functions 
*/
"use strict";
var UlpLoadAnnouncements = {
    offset          : 0,
    slug            : '',
    hash            : '',
    wrapper         : '',
    loadMoreButton  : '',

    init: function(args){
        var obj = this;
        obj.setAttributes(obj, args);
        obj.setInternalStuff(obj);

        jQuery(obj.loadMoreButton).on('click', function(evt){
            obj.handleViewMore(evt, obj);
        });
    },

    setAttributes: function(obj, args){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

    setInternalStuff: function(obj){
        obj.slug = jQuery(obj.wrapper).attr('data-slug');
        obj.hash = jQuery(obj.wrapper).attr('data-hash');
        obj.limit = jQuery(obj.wrapper).attr('data-limit');
    },

    handleViewMore: function(evt, obj){
        jQuery.ajax({
  	        type : "post",
  	        url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
  	        data : {
  	                   action    : "ulp_get_more_announcements",
  	                   course    : obj['slug'],
  										 hash      : obj['hash'],
                       offset    : obj['offset'],
                       limit     : obj['limit'],
  	        },
  	        success: function (r) {
                if (r && r!='0'){
                    jQuery(obj['wrapper']).append(r);
                    // update the offset
                    obj.updateOffset(evt, obj);
                } else {
                    obj.hideBttn(evt, obj);
                }
  	        }
  	   	});
    },

    hideBttn: function(evt, obj){
        jQuery(obj.loadMoreButton).css('display', 'none');
    },

    updateOffset: function(evt, obj){
      obj['offset'] += 10;
    }
}
UlpLoadAnnouncements.init({
    offset          : 10,
    slug            : '',
    hash            : '',
    limit           : 10,
    wrapper         : '.ulp-announcements-list-wrapper',
    loadMoreButton  : '#ulp_course_announcements_load_more',
});

"use strict";
var UlpLoadQanda = {
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
  	                   action    : "ulp_get_more_qanda_items",
  	                   course    : obj['slug'],
  										 hash      : obj['hash'],
                       offset    : obj['offset'],
                       limit     : obj['limit'],
                       substring : jQuery(obj['searchBarSelector']).val()
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
UlpLoadQanda.init({
    offset            : 10,
    slug              : '',
    hash              : '',
    limit             : 10,
    wrapper           : '.ulp-qanda-list-wrapper',
    loadMoreButton    : '#ulp_course_qanda_load_more',
    searchBarSelector : '#ulp_course_qanda_search_bar',
});

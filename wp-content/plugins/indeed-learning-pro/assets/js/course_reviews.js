/*
* Ultimate Learning Pro - Reviews Functions
*/
"use strict";
var UlpReviews = {
    offset: 0,
    slug: '',
    hash: '',
    wrapper: '',

    init: function(args){
        var obj = this;
        obj.setAttributes(obj, args);

        jQuery('#ulp_course_reviews_load_more').on('click', function(evt){
            obj.handleViewMore(evt, obj);
        });
    },

    setAttributes: function(obj, args){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

    handleViewMore: function(evt, obj){
        jQuery.ajax({
  	        type : "post",
  	        url : decodeURI(window.ulp_site_url)+'/wp-admin/admin-ajax.php',
  	        data : {
  	                   action: "ulp_get_more_course_reviews",
  	                   course: obj['slug'],
  										 hash: obj['hash'],
                       offset: obj['offset'],
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
        jQuery('#ulp_course_reviews_load_more').css('display', 'none');
    },

    updateOffset: function(evt, obj){
      obj['offset'] += 10;
    }
};

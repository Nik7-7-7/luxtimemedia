/*
* Ultimate Learning Pro - Course menu Functions
*/
"use strict";
var UlpSingleCourseMenu = {
    menuItemSelector: '',
    targetSelectors: {},

    init: function(args){
        var obj = this;
        obj.setAttributes(obj, args);

        obj.setTheTargetSelectors(obj);

        //jQuery(document).on( 'ready', function(){
        document.addEventListener("DOMContentLoaded", function() {
          obj.handlePageSelectionOnLoading(obj);

          jQuery(obj.menuItemSelector).on('click', function(evt){
              obj.handleChangePage(evt, obj);
          });
        });
    },

    setAttributes: function(obj, args){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

    setTheTargetSelectors: function(obj){
        jQuery(obj.menuItemSelector).each(function(index, element){
            var slug = jQuery(element).attr('data-target');
            obj.targetSelectors[slug] = '#ulp_single_course_' + slug + '_page';
        });
    },

    handleChangePage: function(evt, obj){
      var target = jQuery(evt.target).attr('data-target');
      jQuery(obj.menuItemSelector).each(function(index, element){
          jQuery(element).removeClass('ulp-menu-tab-active');
      })
      for (var key in obj.targetSelectors){
          if (target!=key){
              jQuery(obj.targetSelectors[key]).fadeOut(100);
          }
      }
      jQuery(obj.targetSelectors[target]).fadeIn(100);
      jQuery(evt.target).addClass('ulp-menu-tab-active');
    },

    handlePageSelectionOnLoading: function(obj){
        var target = window.ulpCurrentSubtab;
        if (jQuery(obj.targetSelectors[target]).length<1){
            return false;
        }
        jQuery(obj.menuItemSelector).each(function(index, element){
            if ( jQuery(element).attr('data-target')==target ){
                jQuery(element).addClass('ulp-menu-tab-active');
            } else {
                jQuery(element).removeClass('ulp-menu-tab-active');
            }
        });
        for (var key in obj.targetSelectors){
            if (target!=key){
                jQuery(obj.targetSelectors[key]).fadeOut(100);
            }
        }
        jQuery(obj.targetSelectors[target]).fadeIn(100);
    },

}

UlpSingleCourseMenu.init({
    menuItemSelector: '.js-ulp-single-course-menu-item',
})

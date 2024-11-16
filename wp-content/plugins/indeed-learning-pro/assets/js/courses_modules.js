/*
* Ultimate Learning Pro - Courses Functions
*/
"use strict";
function ulpLoading(selector)
{
		jQuery(selector).html("<div class='ulp-loading-wrapp'><img src='" + decodeURI(window.ulp_url) + "/wp-content/plugins/indeed-learning-pro/assets/images/loading.gif' /></div>");
}

function removeUlpLoading(selector)
{
		jQuery(selector).empty();
}

//jQuery(document).on( 'ready', function(){
document.addEventListener("DOMContentLoaded", function() {
  ulpLoading('#ulp_course_modules');
  /// ajax load modules
  jQuery.ajax({
    type : 'post',
      url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
      data : {
              action:  'ulp_ajax_get_all_course_modules',
              post_id: jQuery('#ulp_course_modules').attr('data-post_id'),
      },
      success: function (r) {
          removeUlpLoading('#ulp_course_modules');
          jQuery('#ulp_course_modules').append(r);
					ulpInitCourseMultiselect();
      }
  });
});

//jQuery(document).on( 'ready', function(){
document.addEventListener("DOMContentLoaded", function() {
    jQuery( "#ulp_course_modules" ).sortable({
        handle: ".ulp-move-module-box",
        stop: function(e, ui) {
            jQuery.map(jQuery(this).find('.ulp-module-item'), function(el) {
                var new_value = jQuery(el).index();
                new_value++;
                jQuery('#' + el.id + ' .ulp-module-order').val(new_value);
                jQuery('#' + el.id + ' .ulp-module-order-display').html(new_value);
            });
        }
    });

		jQuery('#ulp_add_module_button').on( 'click', function(){

				var max = 0;
				var items = 0;
				jQuery.map(jQuery('#modulesMetaBox').find('.ulp_module_id'), function(el) {
						items++;
						var t = jQuery(el).val();
						if ( parseInt(t) > max){
								max = parseInt(t);
						}
				});
		   	jQuery.ajax({
		        type : 'POST',
		        url : decodeURI(window.ulp_url) + '/wp-admin/admin-ajax.php',
		        data : {
		                   action:  'ulp_ajax_add_new_module_to_course',
		                   last_id: max,
											 last_order: items
		               },
		        success: function (r) {


								jQuery('#ulp_course_modules').append(r);
								ulpInitCourseMultiselect();
		        }
		   });
		});

});

function ulpDoModuleRemove(i){
	jQuery('#moduleid_'+i).fadeOut(500, function(){
			this.remove();
	});
}

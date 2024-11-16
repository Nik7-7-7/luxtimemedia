/*
* Ultimate Membership Pro - Listing Users
*/
"use strict";
function ihcInitiateOwl(selector)
{
		var selector = jQuery( selector ).attr( 'data-selector' );
		var autoHeight = jQuery( selector ).attr( 'data-autoHeight' );
		var animateOut = jQuery( selector ).attr( 'data-animateOut' );
		var animateIn = jQuery( selector ).attr( 'data-animateIn' );
		var lazyLoad = jQuery( selector ).attr( 'data-lazyLoad' );
		var loop = jQuery( selector ).attr( 'data-loop' );
		var autoplay = jQuery( selector ).attr( 'data-autoplay' );
		var autoplayTimeout = jQuery( selector ).attr( 'data-autoplayTimeout' );
		var autoplayHoverPause = jQuery( selector ).attr( 'data-autoplayHoverPause' );
		var autoplaySpeed = jQuery( selector ).attr( 'data-autoplaySpeed' );
		var nav = jQuery( selector ).attr( 'data-nav' );
		var navSpeed = jQuery( selector ).attr( 'data-navSpeed' );
		var dots = jQuery( selector ).attr( 'data-dots' );
		var dotsSpeed = jQuery( selector ).attr( 'data-dotsSpeed' );
		var responsiveClass = jQuery( selector ).attr( 'data-responsiveClass' );
		var navigation = jQuery( selector ).attr( 'data-navigation' );
		var owl = jQuery( selector );

		owl.owlihcCarousel({
				items : 1,
				mouseDrag: true,
				touchDrag: true,
				autoHeight: autoHeight,
				animateOut: animateOut,
				animateIn: animateIn,
				lazyLoad : lazyLoad,
				loop: loop,
				autoplay : autoplay,
				autoplayTimeout: autoplayTimeout,
				autoplayHoverPause: autoplayHoverPause,
				autoplaySpeed: autoplaySpeed,
				nav : nav,
				navSpeed : navSpeed,
				navText: [ '', '' ],
				dots: dots,
				dotsSpeed : dotsSpeed,
				responsiveClass: responsiveClass,
				responsive:{
					0:{
						nav:false
					},
					450:{
						nav : navigation
					}
				}
		});
}


function ihcDeselectAll(n, c){
	if (jQuery(c).is(':checked')){
		jQuery('[name="'+n+'[]"]').each(function(){
			if (jQuery(this).val()!=''){
				jQuery(this).attr('checked', false);
			}
		});
	}
}

window.addEventListener( 'load', function(){
    // Listing Members
    if ( jQuery( '.ihc-js-owl-settings-data' ).length > 0 ){
        jQuery( '.ihc-js-owl-settings-data' ).each(function( e, html ){
            ihcInitiateOwl( this );
        });
    }
});

jQuery( window ).on( 'load', function(){

		if ( jQuery( '#iump_reset_bttn' ).length ){
		    jQuery('#iump_reset_bttn').on("click", function(event){
		        event.preventDefault();
		        window.location.href = jQuery( '.ihc-js-listing-user-filter-form' ).attr( 'data-base_url' );
		        return false;
		    });
		}
		
		if ( jQuery( '[name=filter]' ).length ){
		    jQuery("[name=filter]").on('click', function(event){
		      event.preventDefault();
		      jQuery.each(this.form, function(index, field){
		        if (field.value==''){
		          field.name = '';
		        }
		      });
		      jQuery('[name=iump_filter]').val(1);
		      this.form.submit();
		    });
		}

    if ( jQuery( '.ihc-js-listing-users-filter-data' ).length ){
        jQuery( '.ihc-js-listing-users-filter-data' ).each( function( e, html ){
            var currentYear = new Date().getFullYear() + 20;
            jQuery( jQuery( this ).attr( 'data-start_selector' ) ).datepicker({
              dateFormat : "dd-mm-yy",
              changeMonth: true,
              changeYear: true,
              yearRange: "1900:"+currentYear,
              onClose: function(r){}
            });
            jQuery( jQuery( this ).attr( 'data-end_selector' ) ).datepicker({
              dateFormat : "dd-mm-yy",
              changeMonth: true,
              changeYear: true,
              yearRange: "1900:"+currentYear,
              onClose: function(r){}
            });
        });
    }

    if ( jQuery( '.ihc-js-listin-users-filter-number-data' ).length ){
        jQuery( '.ihc-js-listin-users-filter-number-data' ).each( function( e, html ){
            var selector = jQuery( this ).attr('data-selector');
            var min = jQuery( this ).attr('data-min');
            var max = jQuery( this ).attr('data-max');
            var current_min = jQuery( this ).attr('data-current_min');
            var current_max = jQuery( this ).attr('data-current_max');
            var min_selector = jQuery( this ).attr('data-min_selector');
            var max_selector = jQuery( this ).attr('data-max_selector');
            var view_selector = jQuery( this ).attr('data-view_selector');

            min = Number( min );
            max = Number( max );
            current_min = Number( current_min );
            current_max = Number( current_max );

            jQuery( selector ).slider({
                range: true,
                min: min,
                max: max,
                values: [ current_min, current_max ],
                slide: function( event, ui ){
                    jQuery( min_selector ).val(ui.values[0]);
                    jQuery( max_selector ).val(ui.values[1]);
                    jQuery( view_selector ).html(ui.values[0] + ' - ' + ui.values[1]);
                }
            });
        });
    }

} );

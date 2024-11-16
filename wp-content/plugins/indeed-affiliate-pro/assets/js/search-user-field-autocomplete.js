/*
 *Ultimate Affiliate Pro - Search Affiliates field
 */
"use strict";
jQuery(function() {
	/// USERNAME SEARCH
  var targetId = jQuery( '.uap-js-search-user-field-autocomplete' ).attr('data-id');
  var targetUrl = jQuery( '.uap-js-search-user-field-autocomplete' ).attr('data-url');
	jQuery( "#usernames_search" ).on( "keydown", function( event ) {
		if ( event.keyCode === jQuery.ui.keyCode.TAB &&
			jQuery( this ).autocomplete( "instance" ).menu.active ) {
		 	event.preventDefault();
		}
	}).autocomplete({
		minLength: 0,
		source: targetUrl,
		focus: function() {},
		select: function( event, ui ) {
			var input_id = '#' + targetId;
		 	var terms = uap_split(jQuery(input_id).val());//get items from input hidden
			var v = ui.item.id;
			var l = ui.item.label;
		 	jQuery(input_id).val(v);//send to input hidden
			this.value = l;//reset search input
		 	return false;
		}
	});

});

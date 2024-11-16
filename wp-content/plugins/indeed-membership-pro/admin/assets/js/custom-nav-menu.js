/*
* Ultimate Membership Pro - Custom Account Tabs Menu
*/
"use strict";
function ihcWriteTagValue(id, hiddenId, viewDivId, prevDivPrefix){
    var removeLabel = jQuery( '.ihc-js-custom-nav-menu-labels' ).attr( 'data-remove' );
    if ( id.value == -1 ){
       return;
    }
    var hidden_i = jQuery(hiddenId).val();

    if ( hidden_i != '' ){
         var show_arr = hidden_i.split(',');
    } else {
         var show_arr = new Array();
    }

    if ( show_arr.indexOf( id.value ) == -1 ){
        show_arr.push(id.value);

        var str = show_arr.join(',');
        jQuery(hiddenId).val(str);

        var label = jQuery(id).find("option:selected").text();
        var html = '<div id="'+prevDivPrefix+id.value+'" class="ihc-tag-item">'+label+'<div class="ihc-remove-tag" onclick="ihcremoveTag(\''+id.value+'\', \'#'+prevDivPrefix+'\', \''+hiddenId+'\');" title="'+removeLabel+'">x</div></div>';
        jQuery(viewDivId).append( html );
    }
    jQuery(id).val(-1);
}

function ihcremoveTag(removeVal, prevDivPrefix, hiddenId){
    jQuery(prevDivPrefix+removeVal).fadeOut(200, function(){
        jQuery(this).remove();
    });

    var hidden_i = jQuery(hiddenId).val();
    var show_arr = hidden_i.split(',');

    show_arr = removeArrayElement(removeVal, show_arr);
    var str = show_arr.join(',');
    jQuery( hiddenId ).val( str );
}

function removeArrayElement(elem, arr){
  for (i=0;i<arr.length;i++) {
      if(arr[i]==elem){
        arr.splice(i, 1);
      }
  }
  return arr;
}

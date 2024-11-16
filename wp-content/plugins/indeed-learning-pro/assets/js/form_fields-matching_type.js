/*
* Ultimate Learning Pro - Questions Functions ( matching types )
*/
"use strict";
var UlpFormFieldMatching = {
    draggableClass: '',
    droppableClass: '',
    possibleAnswersWrapp: '',
    standardTitleMessage: '',
    extraTitleMessage: '',

    init: function(args){
        var obj = this;
        obj.setAttributes(obj, args);

          jQuery(obj.draggableClass).draggable({
              cursor: 'move',
              refreshPositions: true,
          });

          jQuery(obj.droppableClass).droppable({
              accept: obj.draggableClass,
              drop: function(evt, ui){
                  obj.handleDrop(evt, ui, obj);
              },
              out: function(evt, ui){},
              deactivate: function(evt, ui){

              },
          });

    },

    setAttributes: function(obj, args){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

    handleDrop: function(evt, ui, obj){
      var theTarget = evt.srcElement;
      if ( typeof theTarget == 'undefined' ){
          theTarget = evt.toElement;
      }

      ui.draggable.draggable( 'option', 'revert', false );
      if (jQuery(evt.target).find('div').length>0){
          ui.draggable.draggable( 'option', 'revert', true );
          return false;
      }
      jQuery(evt.target).append(jQuery( theTarget ).detach());
      jQuery( theTarget ).css({
          top: '0px',
          left: '0px'
      });

      var valueToWrite = jQuery( theTarget ).attr('data-answer_key');
      var hiddenInput = jQuery(evt.target).find("input");
      var currentValue = jQuery(hiddenInput).val();
      if (currentValue!=''){
        ui.draggable.draggable( 'option', 'revert', true );
        return false;
      } else {
          ui.draggable.draggable('disable');
          jQuery( theTarget ).addClass('js-ulp-natching-move-to-parent');
          jQuery( theTarget ).attr('title', obj.extraTitleMessage);
          jQuery( theTarget ).on('click', function(clickEvent){
              obj.handleClick(obj, clickEvent);
          })
          jQuery(hiddenInput).val(valueToWrite);
      }

    },

    handleClick: function(obj, clickEvent){
        jQuery(clickEvent.target).parent().find('input').val('');
        jQuery(obj.possibleAnswersWrapp).append(jQuery(clickEvent.target).detach());
        jQuery(clickEvent.target).removeClass('js-ulp-natching-move-to-parent');
        jQuery(clickEvent.target).draggable('enable');
        jQuery(clickEvent.target).attr('title', obj.standardTitleMessage);
    },

};

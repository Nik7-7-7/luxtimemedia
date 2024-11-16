/*
* Ultimate Learning Pro - Questions Functions (single choices)
*/
"use strict";
var UlpFormFieldSingleChoice = {
    wrapper: '',
    name: '',
    selectedClass: '',

    init: function(args){
        var obj = this;
        obj.setAttributes(obj, args);

        jQuery(obj.wrapper).on('click', function(evt){
            obj.handleChange(evt, obj);
        });
    },

    setAttributes: function(obj, args){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

    handleChange: function(evt, obj){
        jQuery(obj.wrapper).each(function(){
          jQuery(this).removeClass(obj.selectedClass);
        });
        jQuery(evt.target).parent().addClass(obj.selectedClass);

        var newValue = jQuery(evt.target).attr('data-value');
        jQuery('[name=' + obj.name + ']').val(newValue);
    },

};

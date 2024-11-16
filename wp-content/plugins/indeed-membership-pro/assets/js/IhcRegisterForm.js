/**
 * Ultimate Membership Pro - Profile Form
 */
"use strict";
var IhcRegisterForm = {
    fields                      : [],
    required_fields             : [],
    conditional_logic_fields    : [],
    conditional_text_fields     : [],
    unique_fields               : [],
    must_submit                 : 0,

    init									: function( args ){
        var obj = this;

        if ( typeof window.ihc_register_fields !== 'undefined' ){
            obj.fields = JSON.parse( window.ihc_register_fields );
        }

        // required fields - from global variables to object properties
        if ( typeof window.ihc_register_required_fields !== 'undefined' ){
            obj.required_fields = JSON.parse( window.ihc_register_required_fields );
        }
        // conditional logic - from global variables to object properties
        if ( typeof window.ihc_register_conditional_logic !== 'undefined' ){
            obj.conditional_logic_fields = JSON.parse( window.ihc_register_conditional_logic );
        }
        // conditional text - from global variables to object properties
        if ( typeof window.ihc_register_conditional_text !== 'undefined' ){
            obj.conditional_text_fields = JSON.parse( window.ihc_register_conditional_text );
        }
        // unique field - from global variables to object properties
        if ( typeof window.ihc_register_unique_fields !== 'undefined' ){
            obj.unique_fields = JSON.parse( window.ihc_register_unique_fields );
        }

        // required fields
        if ( obj.required_fields.length > 0 ){

            // loop through all required fields, put the check function on the blur event.
            jQuery( obj.required_fields ).each( function( index, fieldName ){
                var currentFormFieldType = obj.getFieldTypeByName( fieldName );

                if ( obj.inArray( currentFormFieldType, [ 'text', 'textarea', 'number', 'password', 'conditional_text', 'select' ] )  ){
                    jQuery( ".ihc-form-create-edit [name='" + fieldName + "']" ).on( "blur", function(){
                        obj.checkRequiredField( fieldName );
                    });
                } else if ( currentFormFieldType === 'radio' ){
                    jQuery( ".ihc-form-create-edit input[type=radio][name='" + fieldName + "']" ).on( "change", function(){
                        obj.checkRequiredField( fieldName, currentFormFieldType );
                    });
                } else if ( currentFormFieldType === 'checkbox' ){
                    if ( jQuery( ".ihc-form-create-edit [name='" + fieldName + "[]']" ).length > 0 ){
                        jQuery( ".ihc-form-create-edit [name='" + fieldName + "[]']" ).on( "change", function(){
                            obj.checkRequiredField( fieldName, currentFormFieldType );
                        });
                    } else if ( jQuery(".ihc-form-create-edit [name="+fieldName+"]").length > 0 ) {
                        // checkbox - single value
                        jQuery( ".ihc-form-create-edit [name="+fieldName+"]" ).on( "change", function(){
                            obj.checkRequiredField( fieldName, currentFormFieldType );
                        });
                    }
                } else if ( currentFormFieldType === 'multiselect' ){
                    // multiselect
                    jQuery( ".ihc-form-create-edit [name='" + fieldName + "[]']" ).on( "blur", function(){
                        obj.checkRequiredField( fieldName, currentFormFieldType );
                    });
                } else if ( currentFormFieldType === 'date' ){
                    jQuery( ".ihc-form-create-edit [name='" + fieldName + "']" ).on( "change", function(){
                        obj.checkRequiredField( fieldName );
                    });
                }

            });
        }
        // end of required fields

        // conditional logic
        if ( obj.conditional_logic_fields.length > 0 ){

            // loop through all conditional logic
            jQuery( obj.conditional_logic_fields ).each( function( index ){
                switch ( obj.conditional_logic_fields[index].type ){
                    case 'text':
                    case 'textarea':
                    case 'number':
                    case 'password':
                    case 'date':
                    case 'conditional_text':
                    case 'unique_value_text':
                      // on blur event
                      jQuery(".ihc-form-create-edit [name=" + obj.conditional_logic_fields[index].field_to_check + "]").on("blur", function(){
                          var checkValue = jQuery(".ihc-form-create-edit [name=" + obj.conditional_logic_fields[index].field_to_check + "]").val();
                          obj.ihcAjaxCheckFieldCondition( checkValue, '#' + obj.conditional_logic_fields[index].target_parent_id, obj.conditional_logic_fields[index].target_field, obj.conditional_logic_fields[index].show );
                      });
                      break;
                    case 'select':
                      // on change event
                      jQuery(".ihc-form-create-edit [name=" + obj.conditional_logic_fields[index].field_to_check + "]").on("change", function(){
                          var checkValue = jQuery(".ihc-form-create-edit [name='" + obj.conditional_logic_fields[index].field_to_check + "']").val();
                          obj.ihcAjaxCheckFieldCondition( checkValue, '#' + obj.conditional_logic_fields[index].target_parent_id, obj.conditional_logic_fields[index].target_field, obj.conditional_logic_fields[index].show );
                      });
                      break;
                    case 'multi_select':
                      // on change event
                      jQuery(".ihc-form-create-edit [name='" + obj.conditional_logic_fields[index].field_to_check + "[]']").on("change", function(){
                          var checkValue = jQuery(".ihc-form-create-edit [name='" + obj.conditional_logic_fields[index].field_to_check + "[]']").val();
                          if ( checkValue != null ){
                              var checkValue = checkValue.join(',');
                          }
                          // do something with checkValue
                          obj.ihcAjaxCheckFieldCondition( checkValue, '#' + obj.conditional_logic_fields[index].target_parent_id, obj.conditional_logic_fields[index].target_field, obj.conditional_logic_fields[index].show );
                      });
                      break;
                      case 'checkbox':
                        jQuery(".ihc-form-create-edit [name='" + obj.conditional_logic_fields[index].field_to_check + "[]']").on( "click", function(){
                              var vals = [];
                              jQuery( ".ihc-form-create-edit [name='"+obj.conditional_logic_fields[index].field_to_check+"[]']:checked" ).each(function() {
                                  vals.push( jQuery( this ).val() );
                              });
                              var checkValue = vals.join( ',' );
                          // do something with checkValue
                          obj.ihcAjaxCheckFieldCondition( checkValue, '#' + obj.conditional_logic_fields[index].target_parent_id, obj.conditional_logic_fields[index].target_field, obj.conditional_logic_fields[index].show );
                        });
                        break;
                    case 'radio':
                      // on click event
                      jQuery(".ihc-form-create-edit [name=" + obj.conditional_logic_fields[index].field_to_check + "]").on( "click", function(){
                        if ( obj.conditional_logic_fields[index].type == 'checkbox' ){
                            var vals = [];
                            jQuery( ".ihc-form-create-edit [name='"+obj.conditional_logic_fields[index].field_to_check+"[]']:checked" ).each(function() {
                                vals.push( jQuery( this ).val() );
                            });
                            var checkValue = vals.join( ',' );
                        } else {
                            var checkValue = jQuery( ".ihc-form-create-edit [name="+obj.conditional_logic_fields[index].field_to_check+"]:checked" ).val();
                        }
                        // do something with checkValue
                        obj.ihcAjaxCheckFieldCondition( checkValue, '#' + obj.conditional_logic_fields[index].target_parent_id, obj.conditional_logic_fields[index].target_field, obj.conditional_logic_fields[index].show );
                      });
                      break;
                }
            });
        }

        // conditional text ( Verification Code )
        if ( obj.conditional_text_fields.length > 0 ){
            jQuery( obj.conditional_text_fields ).each( function( index ){
              // on blur event
              jQuery(".ihc-form-create-edit [name=" + obj.conditional_text_fields[index] + "]").on("blur", function(){
                  var checkValue = jQuery(".ihc-form-create-edit [name=" + obj.conditional_text_fields[index] + "]").val();
                  obj.ajaxCheckConditionalText( obj.conditional_text_fields[index], obj );
              });
            });
        }

        // unique fields
        if ( obj.unique_fields.length > 0 ){
            jQuery( obj.unique_fields ).each( function( index ){
              // on blur event
              jQuery(".ihc-form-create-edit [name=" + obj.unique_fields[index] + "]").on("blur", function(){
                  var checkValue = jQuery(".ihc-form-create-edit [name=" + obj.unique_fields[index] + "]").val();
                  obj.ajaxCheckUniqueField( obj.unique_fields[index] );
              });
            });
        }

        // country and state
        if ( jQuery('[name=ihc_country]').length > 0 && jQuery('[name=ihc_state]').length > 0 ){
            // on change event
            jQuery('[name=ihc_country]').on( 'change', function(){
                obj.updateStateField();
            } );
        }

        // when the form is submited it will check all the fields agaian via ajax.
        jQuery( '.ihc-form-create-edit' ).on( 'submit', function( evt ){
            if ( obj.must_submit == 1 ){
                // everything is ok
                return true;
            } else {
                // stop the form from submiting
                if ( obj.indeedDetectBrowser() === "Firefox" ){
                    evt.preventDefault();
                    evt.stopPropagation();
                    evt.stopImmediatePropagation();
                } else {
                    evt.preventDefault();
                }
                obj.checkAllFieldsBeforeSubmit( obj, evt );
                return false;
            }
        });

        obj.datePicker();
        obj.uploadFileAndAvatar();
        obj.countrySelect();

    },

    // check one required field. Trigger by blur event.
    checkRequiredField    : function( fieldName, fieldType ){
      	var target_id = '#' + jQuery('.ihc-form-create-edit [name=' + fieldName + ']').parent().attr('id');
        // getting the value from the specified field
        if ( fieldType === 'radio' ){
            // radio - one value
            var target_id = '#' + jQuery('.ihc-form-create-edit [name=' + fieldName + ']').parent().parent().attr('id');
            var val1 = jQuery( ".ihc-form-create-edit [name=" + fieldName + "]:checked" ).val();
        } else if ( fieldType === 'checkbox' ){
            // checkbox
            if ( jQuery(".ihc-form-create-edit [name='"+fieldName+"[]']").length > 0  ){
                // checkbox - multiple values
                var target_id = '#' + jQuery(".ihc-form-create-edit [name='"+fieldName+"[]']").first().parent().parent().attr('id');
                var vals = [];
                jQuery( ".ihc-form-create-edit [name='"+fieldName+"[]']:checked" ).each(function() {
                    vals.push( jQuery( this ).val() );
                });
                var val1 = vals.join( ',' );
            } else if ( jQuery(".ihc-form-create-edit [name="+fieldName+"]").length > 0 ) {
                // checkbox - single value
                var target_id = '#' + jQuery('.ihc-form-create-edit [name=' + fieldName + ']').parent().parent().attr('id');
                var val1 = jQuery( ".ihc-form-create-edit [name=" + fieldName + "]:checked" ).val();
            }
        } else if ( fieldType === 'multiselect' ) {
            var target_id = '#' + jQuery(".ihc-form-create-edit [name='"+fieldName+"[]']").parent().attr('id');
            var val1 = jQuery(".ihc-form-create-edit [name='"+fieldName+"[]']").val();
            var val2 = '';
        } else {
            var val1 = jQuery('.ihc-form-create-edit [name=' + fieldName + ']').val();
            var val2 = '';
        }

        if ( typeof val1 === 'undefined' ){
            val1 = '';
        }

        // special treatment for pass and confirm email
      	if ( fieldName == 'pass2' ){
      		val2 = jQuery('.ihc-form-create-edit [name=pass1]').val();
      	} else if ( fieldName == 'confirm_email' ){
      		val2 = jQuery('.ihc-form-create-edit [name=user_email]').val();
      	}

        jQuery.ajax({
              type : "post",
              url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
              data : {
                         action         : "ihc_ajax_register_forms_check_one_field",
                         name           : fieldName,
                         value          : val1,
                         second_value   : val2,
                         is_edit        : 0,
              },
              success: function ( response ) {
                  var data = JSON.parse( response );
                  if ( typeof window.indeedRegisterErrors === 'undefined' ){
                      window.indeedRegisterErrors = [];
                  }

                  //remove prev notice, if its case
                	jQuery(target_id + ' .ihc-register-notice').remove();
                	jQuery('.ihc-form-create-edit [name=' + fieldName + ']').removeClass('ihc-input-notice');

                  if ( data.status == 1 ){
                  		// it's all good
                      self.IhcRegisterForm.removeElementFromArray( window.indeedRegisterErrors, fieldName );
                	} else {
                      // error
                  		jQuery( target_id ).append('<div class="ihc-register-notice">' + data.message + '</div>');
                  		jQuery('.ihc-form-create-edit [name='+fieldName+']').addClass('ihc-input-notice');
                      self.IhcRegisterForm.addElementToArray( window.indeedRegisterErrors, fieldName );
                  }
              }
        });
    },

    // this function will loop through all fields before submiting the form
    checkAllFieldsBeforeSubmit          : function( obj, evt ){
        // remove old notices
        jQuery( '.ihc-register-notice' ).remove();

        // creating the array of fields that must be checked
        var fields_to_send = [];
        for ( var i=0; i<obj.fields.length; i++ ){

          // current field is not available so we skip it
          if ( obj.fieldHasException( obj.fields[i] ) ){
              continue;
          }

          // remove old notices
          jQuery('.ihc-form-create-edit [name='+obj.fields[i]+']').removeClass('ihc-input-notice');

          // get current field type
          var field_type = obj.getFieldTypeByName( obj.fields[i] );

          // initiate the variables
          var val1 = '';
          var val2 = '';
          var is_unique_field = false;

          if (field_type=='checkbox' || field_type=='radio'){
            var val1 = obj.getCheckboxRadioValue(field_type, obj.fields[i]);
          } else if ( field_type=='multiselect' ){
            val1 = jQuery('.ihc-form-create-edit [name=\'' + obj.fields[i] + '[]\']').val();
            if (typeof val1=='object' && val1!=null){
              val1 = val1.join(',');// array to string conversion
            }
          } else {
            var val1 = jQuery('.ihc-form-create-edit [name='+obj.fields[i]+']').val();
            if (jQuery('.ihc-form-create-edit [name='+obj.fields[i]+']').attr('data-search-unique')){
              var is_unique_field = true;
            }
          }

          if (obj.fields[i]=='pass2'){
            val2 = jQuery('.ihc-form-create-edit [name=pass1]').val();
          } else if (obj.fields[i]=='confirm_email'){
            val2 = jQuery('.ihc-form-create-edit [name=user_email]').val();
          } else if (obj.fields[i] == 'tos') {
      			if (jQuery('.ihc-form-create-edit [name=tos]').is(':checked')){
      				val1 = 1;
      			} else {
      				val1 = 0;
      			}
      		} else if ( obj.fields[i] == 'recaptcha' ){
              val1 = jQuery( '.ihc-form-create-edit [name=g-recaptcha-response]' ).val();
          }

          if ( typeof val1 === 'undefined' ){
              val1 = '';
          }
          var params_to_send = {name: obj.fields[i], value: val1, second_value: val2};
          if (is_unique_field){
            params_to_send.is_unique_field = true;
          }
          if ( obj.isFieldRequired( obj, obj.fields[i] ) ){
              params_to_send.is_required = true;
          }
          fields_to_send.push(params_to_send);
        }

        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
            data : {
                       action             : "ihc_ajax_register_form_check_all_fields",
                       fields_obj         : fields_to_send
            },
            success: function ( response ) {

              var responseObject = JSON.parse( response );
              var must_submit = 1;

            	for ( var j=0; j<responseObject.length; j++ ){
                  var field_type = obj.getFieldTypeByName( responseObject[j].name );

                	if (field_type=='radio'){
                		var target_id = jQuery('.ihc-form-create-edit [name='+responseObject[j].name+']').parent().parent().attr('id');
                	} else if (field_type=='checkbox' && responseObject[j].name!='tos'){
                		var target_id = jQuery('.ihc-form-create-edit [name=\''+responseObject[j].name+'[]\']').parent().parent().attr('id');
                	} else if ( field_type=='multiselect'){
                		var target_id = jQuery('.ihc-form-create-edit [name=\''+responseObject[j].name+'[]\']').parent().attr('id');
                	} else {
                		var target_id = jQuery('.ihc-form-create-edit [name='+responseObject[j].name+']').parent().attr('id');
                	}

                	if (responseObject[j].value==1){
                		// it's all good
                	} else {
                		//errors
                    	if (typeof target_id=='undefined'){
                    		//no target id...insert msg after input
                    		jQuery('.ihc-form-create-edit [name='+responseObject[j].name+']').after('<div class="ihc-register-notice">'+responseObject[j].message+'</div>');
                    		must_submit = 0;
                    	} else {
                    		jQuery('#'+target_id).append('<div class="ihc-register-notice">'+responseObject[j].message+'</div>');
                    		jQuery('.ihc-form-create-edit [name=' + responseObject[j].name + ']').addClass('ihc-input-notice');
                    		must_submit = 0;
                    	}
                	}
            	}

              window.ihcRegisterCheckFieldsAjaxFired = 0;
            	if (must_submit==1){
                 obj.must_submit = 1;
                 jQuery(".ihc-form-create-edit").submit();
            	} else {
                 obj.must_submit = 0;
        			   return false;
            	}
            }
        });
    },

    // check if an array contain element
    inArray           : function( needle, haystack ) {
        for ( var i = 0; i < haystack.length; i++ ) {
            if ( haystack[i] == needle ){
               return true;
            }
        }
        return false;
    },

    // getting the type of field based on name of field.
    getFieldTypeByName        : function( name ){
        var fieldType = jQuery('.ihc-form-create-edit [name=' + name + ']').attr('type');
        if ( fieldType === 'text' && jQuery( '.ihc-form-create-edit [name=' + name + ']' ).hasClass('iump-form-datepicker') ){
            return 'date';
        }
        if ( typeof fieldType === 'undefined' ){
           fieldType = jQuery('.ihc-form-create-edit [name=\'' + name + '[]\']').attr('type');
        }
        if ( typeof fieldType === 'undefined' ){
           fieldType = jQuery('.ihc-form-create-edit [name=\'' + name + '\']').prop('nodeName');
           if ( typeof fieldType !== 'undefined' && fieldType !== '' ){
              fieldType = fieldType.toLowerCase();
           }
        }
        if ( typeof fieldType === 'undefined' ){
            fieldType = jQuery('.ihc-form-create-edit [name=\'' + name + '[]\']').prop('nodeName');
            if ( typeof fieldType !== 'undefined' && fieldType !== '' ){
               fieldType = fieldType.toLowerCase();
            }
            if ( fieldType == 'select' ){
                fieldType = 'multiselect';
            }
        }
        return fieldType;
    },

    // conditional logic
    ihcAjaxCheckFieldCondition          : function(check_value, field_id, field_name, show){
       	jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
            data : {
                       action     : "ihc_ajax_register_form_check_one_conditional_logic",
                       value      : check_value,
                       field      : field_name
            },
            success: function ( response ){
            	var str = jQuery("[name=ihc_exceptionsfields]").val();
            	if (str){
                	var arr = str.split(',');
                	var index = arr.indexOf(field_name);
            	} else {
            		var arr = [];
            	}

            	if ( response == '1' ){
                    if (show==1){
                    	jQuery(field_id).css( 'display', 'block' );
                    	if (arr.indexOf(field_name)!=-1){
                          arr.splice(index, 1);
                    	}
                    } else {
                      	jQuery(field_id).css( 'display', 'none' );
                      	if (arr.indexOf(field_name)==-1){
                      		  arr.push(field_name);
                      	}
                    }
            	} else {
                  if (show==1){
                      jQuery(field_id).css( 'display', 'none' );
                      if (arr.indexOf(field_name)==-1){
                        	arr.push(field_name);
                      }
                  } else {
                      jQuery(field_id).css( 'display', 'block' );
                      if (arr.indexOf(field_name)!=-1){
                          arr.splice(index, 1);
                      }
                  }
            	}
            	if (arr){
                	var str = arr.join(',');
                	jQuery("[name=ihc_exceptionsfields]").val( str );
            	}
            }
       	});
    },

    // unique value field
    ajaxCheckUniqueField          : function( fieldName ){
        var targetId = '#' + jQuery('.ihc-form-create-edit [name='+fieldName+']').parent().attr('id');
      	var value = jQuery('.ihc-form-create-edit [name='+fieldName+']').val();
      	if ( value == '' ){
            return;
      	}
        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
            data : {
                       action        : "ihc_ajax_register_form_check_unique_field",
                       meta_key      : fieldName,
                       meta_value    : value
            },
            success: function ( response ) {
              //remove prev notice, if its case
              var responseObject = JSON.parse( response );

              jQuery(targetId + ' .ihc-register-notice').remove();
              jQuery('.ihc-form-create-edit [name='+fieldName+']').removeClass('ihc-input-notice');
              if ( responseObject.status == 1){
                // it's all good

              } else {
                jQuery(targetId).append('<div class="ihc-register-notice">' + responseObject.message  + '</div>');
                jQuery('.ihc-form-create-edit [name=' + fieldName + ']').addClass('ihc-input-notice');
                self.IhcRegisterForm.must_submit = 0;
              }
            }
        });
    },

    // conditional text
    ajaxCheckConditionalText            : function( fieldName, obj ){
        var targetId = '#' + jQuery('.ihc-form-create-edit [name='+fieldName+']').parent().attr('id');
      	var value = jQuery('.ihc-form-create-edit [name='+fieldName+']').val();
        if ( value == '' ){
            return;
        }
        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
            data : {
                       action        : "ihc_ajax_register_form_check_conditional_text_field",
                       meta_key      : fieldName,
                       meta_value    : value
            },
            success: function ( response ) {
              //remove prev notice, if its case
              var responseObject = JSON.parse( response );

              jQuery(targetId + ' .ihc-register-notice').remove();
              jQuery('.ihc-form-create-edit [name='+fieldName+']').removeClass('ihc-input-notice');
              if ( responseObject.status == 1){
                // it's all good

              } else {
                jQuery(targetId).append('<div class="ihc-register-notice">' + responseObject.message  + '</div>');
                jQuery('.ihc-form-create-edit [name=' + fieldName + ']').addClass('ihc-input-notice');
                obj.must_submit = 0;
              }
            }
        });
    },

    // update state field
    updateStateField                : function(){
        var countryField = jQuery('.ihc-form-create-edit [name=ihc_country]');
        jQuery.ajax({
            type : "post",
            url : decodeURI( window.ihc_site_url ) + '/wp-admin/admin-ajax.php',
            data : {
                     action     : "ihc_ajax_get_state_field_as_html",
                     country    : countryField.val(),
                     is_edit    : 1,
            },
            success: function( response ){
                var field = jQuery('.ihc-form-create-edit [name=ihc_state]');
                var parent = field.parent();
                field.remove();
                parent.append( response );
            }
        });
    },

    indeedDetectBrowser     : function()
    {
        if((navigator.userAgent.indexOf("Opera") || navigator.userAgent.indexOf('OPR')) != -1 ) {
            return 'Opera';
        } else if(navigator.userAgent.indexOf("Chrome") != -1 ) {
            return 'Chrome';
        } else if(navigator.userAgent.indexOf("Safari") != -1) {
            return 'Safari';
        } else if(navigator.userAgent.indexOf("Firefox") != -1 ){
            return 'Firefox';
        } else if((navigator.userAgent.indexOf("MSIE") != -1 ) || (!!document.documentMode == true )) {
            return 'IE';
        } else {
            return 'Unknown';
        }
    },

    isFieldRequired: function ( obj, name ){
        if ( obj.required_fields.length === 0 || obj.required_fields.indexOf( name ) < 0 ){
            return false;
        }
        var exceptions = jQuery("[name=ihc_exceptionsfields]").val();
        if ( exceptions ){
            // exceptions are the conditional logic fields, that are required in some case.
            var exceptions_arr = exceptions.split(',');
        }
        if ( exceptions_arr && exceptions_arr.indexOf( name ) > -1 ){
            //CHECK IF FIELD is in exceptions
            return false;
        }
        return true;
    },

    fieldHasException: function ( name ){
        var exceptions = jQuery("[name=ihc_exceptionsfields]").val();
        if ( exceptions ){
            // exceptions are the conditional logic fields, that are required in some case.
            var exceptions_arr = exceptions.split(',');
        } else {
            return false;
        }
        if ( typeof exceptions_arr !== 'undefined' && exceptions_arr.indexOf( name ) > -1 ){
            //CHECK IF FIELD is in exceptions
            return true;
        }
        return false;
    },

    removeElementFromArray : function( array, value ){
        var index = array.indexOf( value );
        if (index > -1) {
            array.splice( index, 1 );
        }
    },

    addElementToArray : function( array, value ){
        var index = array.indexOf( value );
        if (index > -1) {
            return;
        }
        array.push( value );
    },

    getCheckboxRadioValue : function(type, selector){
    	if (type=='radio'){
    		var r = jQuery('[name='+selector+']:checked').val();
    		if (typeof r!='undefined'){
    			return r;
    		}
    	} else {
    		var arr = [];
    		jQuery('[name=\''+selector+'[]\']:checked').each(function(){
    			arr.push(this.value);
    		});
    		if (arr.length>0){
    			return arr.join(',');
    		}
    	}
      if ( jQuery('[name="' + selector + '"]').is(':checked') ){
          return 1;
      }
    	return '';
    },

    datePicker: function(){
        // datepicker
        if ( jQuery( '.ihc-js-datepicker-data' ).length ){
            jQuery( '.ihc-js-datepicker-data' ).each( function(e,html){
                var currentYear = new Date().getFullYear() + 10;
                jQuery( jQuery(this).attr('data-selector') ).datepicker({
                    dateFormat        : "dd-mm-yy",
                    changeMonth       : true,
                    changeYear        : true,
                    yearRange         : "1900:"+currentYear,
                    onClose           : function(r) {
                          var callback = jQuery(this).attr('data-callback');
                          if ( typeof callback == 'function' ){
                              callback();
                          }
                    }
                });
            });
        }
    },

    uploadFileAndAvatar: function(){
      // upload file
      if ( jQuery( '.ihc-js-upload-file-public-data' ).length ){
          jQuery( '.ihc-js-upload-file-public-data' ).each( function( e, html ){
            var rand = jQuery( this ).attr( 'data-rand' );
            var url = jQuery( this ).attr( 'data-url' );
            var max_size = jQuery( this ).attr( 'data-max_size' );
            var allowed_types = jQuery( this ).attr( 'data-allowed_types' );
            var name = jQuery( this ).attr( 'data-name' );
            var remove_label = jQuery( this ).attr( 'data-remove_label' );
            var alert_text = jQuery( this ).attr('data-alert_text');
            jQuery("#ihc_fileuploader_wrapp_"+ rand +" .ihc-file-upload").uploadFile({
              onSelect: function (files) {
                  jQuery("#ihc_fileuploader_wrapp_"+ rand +" .ajax-file-upload-container").css("display", "block");
                  var check_value = jQuery("#ihc_upload_hidden_" + rand ).val();
                  if (check_value!="" ){
                    alert(alert_text);
                    return false;
                  }
                  return true;
              },
              url: url,
              fileName: "ihc_file",
              dragDrop: false,
              showFileCounter: false,
              showProgress: true,
              showFileSize: false,
              maxFileSize: max_size,
              allowedTypes: allowed_types,
              onSuccess: function(a, response, b, c){
                if (response){
                  var obj = jQuery.parseJSON(response);
                  if (typeof obj.secret!="undefined"){
                      jQuery("#ihc_fileuploader_wrapp_" + rand ).attr("data-h", obj.secret);
                  }
                  var theHtml = "<div onClick=\"ihcDeleteFileViaAjax("+obj.id+", -1, '#ihc_fileuploader_wrapp_" + rand + "' , '" + name + "', '#ihc_upload_hidden_" + rand + "' );\" class='ihc-delete-attachment-bttn'>"+remove_label+"</div>";
                  jQuery("#ihc_fileuploader_wrapp_" + rand + " .ihc-file-upload").prepend( theHtml );
                  switch (obj.type){
                    case "image":
                      jQuery("#ihc_fileuploader_wrapp_" + rand + " .ihc-file-upload").prepend("<img src="+obj.url+" class=\'ihc-member-photo\' /><div class=\'ihc-clear\'></div>");
                    break;
                    case "other":
                      jQuery("#ihc_fileuploader_wrapp_"+ rand +" .ihc-file-upload").prepend("<div class=ihc-icon-file-type></div><div class=ihc-file-name-uploaded>"+obj.name+"</div>");
                    break;
                  }
                  jQuery("#ihc_upload_hidden_"+ rand ).val(obj.id);
                  setTimeout(function(){
                    jQuery("#ihc_fileuploader_wrapp_"+ rand +" .ajax-file-upload-container").css("display", "none");
                  }, 3000);
                }
              }
            });
          });
      }
      // upload image ( avatar )
      if ( jQuery( '.ihc-js-upload-image-data' ).length ){
          jQuery( '.ihc-js-upload-image-data' ).each( function(e,html){
              var rand = jQuery( this ).attr( 'data-rand' );
              var url = jQuery( this ).attr( 'data-url' );
              var name = jQuery( this ).attr( 'data-name' );
              var bttn = jQuery( this ).attr( 'data-bttn_label' );
              IhcAvatarCroppic.init({
                  triggerId					           : 'js_ihc_trigger_avatar' + rand,
                  saveImageTarget		           : url,
                  cropImageTarget              : url,
                  imageSelectorWrapper         : '.ihc-js-upload-image-wrapp',
                  hiddenInputSelector          : '[name='+name+']',
                  imageClass                   : 'ihc-member-photo',
                  removeImageSelector          : '#ihc_upload_image_remove_bttn_' + rand,
                  buttonId 					           : 'ihc-avatar-button',
                  buttonLabel 			           : bttn
              });
          });
      }
    },

    countrySelect: function(){
      // country select
      if ( jQuery( '.ihc-js-countries-list-data' ).length ){
          jQuery( jQuery( '.ihc-js-countries-list-data' ).attr( 'data-selector' ) ).select2({
              placeholder: jQuery( '.ihc-js-countries-list-data' ).attr( 'data-placeholder' ),
              allowClear: true,
              selectionCssClass: "ihc-select2-dropdown"
          });
      }
    },

};

function ihcDeleteFileViaAjax(id, u_id, parent, name, hidden_id){
    var r = confirm("Are you sure you want to delete?");
	if (r) {
    var s = jQuery(parent).attr('data-h');
    jQuery.ajax({
        type : "post",
        url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
        data : {
                   action: "ihc_delete_attachment_ajax_action",
                   attachemnt_id: id,
                   user_id: u_id,
                   field_name: name,
                   h: s
               },
        success: function (data) {
			    jQuery(hidden_id).val('');
        	jQuery(parent + ' .ajax-file-upload-filename').remove();
        	jQuery(parent + ' .ihc-delete-attachment-bttn').remove();
        	if (jQuery(parent + ' .ihc-member-photo').length){
        		jQuery(parent + ' .ihc-member-photo').remove();
        		if (name=='ihc_avatar'){
        			jQuery(parent).prepend("<div class='ihc-no-avatar ihc-member-photo'></div>");
        			jQuery(parent + " .ihc-file-upload").css("display", 'block');
        		}
        	}

        	if (jQuery(parent + " .ihc-file-name-uploaded").length){
        		jQuery(parent + " .ihc-file-name-uploaded").remove();
        	}

        	if (jQuery(parent + ' .ajax-file-upload-progress').length){
        		jQuery(parent + ' .ajax-file-upload-progress').remove();
        	}
        	if (jQuery(parent + ' .ihc-icon-file-type').length){
        		jQuery(parent + ' .ihc-icon-file-type').remove();
        	}
        }
   });
	}
}
window.addEventListener( 'load', function(){
		IhcRegisterForm.init();
});

// social
function ihcRunSocialReg(s){
	var form = jQuery("form#createuser");
	jQuery("form#createuser input, form#createuser textarea").each(function(){
    jQuery("#ihc_social_login_form").append("<input type=hidden value="+this.value+" name="+this.name+" />");
	});
  jQuery("#ihc_social_login_form").append("<input type=hidden value='"+s+"' name='sm_type' />");
	jQuery("#ihc_social_login_form").submit();
}

// password strenght
var IhcPasswordStrength = {
  colors: ['#F00', '#F90', '#FF0', '#9F0', '#0F0'],
  labels: [],

  init: function(args){
    var obj = this;
    obj.setAttributes(obj, args);
    if ( typeof window.ihcPasswordStrengthLabels === 'string' ){
        obj.labels = JSON.parse(window.ihcPasswordStrengthLabels);
    } else {
        obj.labels = window.ihcPasswordStrengthLabels;
    }

        jQuery(document).on('keyup', jQuery('[name=pass1]'), function (evt) {
            obj.handleTypePassword( obj, evt );
        });
        jQuery(document).on('keyup', jQuery('[name=pass2]'), function (evt) {
            obj.handleTypePassword( obj, evt );
        });
        // show - hide password - register/change password
        if ( jQuery( '.ihc-hide-pw' ).length > 0 ){
          jQuery('.ihc-hide-pw').each(function(index, button) {
            jQuery(button).on( 'click', function (e) {
              var pass = jQuery(button).prev();
              if ( 'password' === pass.attr( 'type' ) ) {
                pass.attr( 'type', 'text' );
                jQuery( this ).children().removeClass( 'dashicons-visibility' ).addClass('dashicons-hidden');
              } else {
                pass.attr( 'type', 'password' );
                jQuery( this ).children().removeClass( 'dashicons-hidden' ).addClass('dashicons-visibility');
              }
            });
          });
        }

  },

  setAttributes: function(obj, args){
      for (var key in args) {
        obj[key] = args[key];
      }
  },

  handleTypePassword: function(obj, evt){
      var rules = jQuery(evt.target).attr('data-rules');
      if ( !rules ){
         return;
      }

      rules = rules.split(',');
      var strength = obj.mesureStrength(evt.target.value, rules);
      var color = obj.getColor(strength);
      var ul = jQuery(evt.target).parent().find('ul');
      ul.children('li').css({ "background": "#DDD" }).slice(0, color.idx).css({ "background": color.col });

      var newLabel;
      newLabel = obj.labels[0];

      if (strength>10 && strength<21){
          newLabel = obj.labels[1];
      } else if (strength>20 && strength<31){
          newLabel = obj.labels[2];
      } else if (strength>30){
          newLabel = obj.labels[3];
      }
      jQuery(evt.target).parent().find('.ihc-strength-label').html(newLabel);

  },

  mesureStrength: function (p, rules) {

      var _force = 0;
      var _regex = /[$-/:-?{-~!^_`\[\]]/g;

      var _letters = /[a-zA-Z]+/.test(p);
      var _lowerLetters = /[a-z]+/.test(p);
      var _upperLetters = /[A-Z]+/.test(p);
      var _numbers = /[0-9]+/.test(p);
      var _symbols = _regex.test(p);

      if (p.length<rules[0]){
          return 0;
      }
      if (rules[1]==2 && (!_numbers || !_letters )){
          return 0;
      } else if (rules[1]==3 && (!_numbers || !_letters || !_upperLetters)){
          return 0;
      }

      var _flags = [_lowerLetters, _upperLetters, _numbers, _symbols];
      var _passedMatches = jQuery.grep(_flags, function (el) { return el === true; }).length;

      _force += 2 * p.length + ((p.length >= 10) ? 1 : 0);
      _force += _passedMatches * 10;

      // penality (short password)
      _force = (p.length <= 6) ? Math.min(_force, 10) : _force;

      // penality (poor variety of characters)
      _force = (_passedMatches == 1) ? Math.min(_force, 10) : _force;
      _force = (_passedMatches == 2) ? Math.min(_force, 20) : _force;
      _force = (_passedMatches == 3) ? Math.min(_force, 40) : _force;
      return _force;
  },

  getColor: function (s) {
      var idx = 0;
      if (s <= 10) { idx = 0; }
      else if (s <= 20) { idx = 1; }
      else if (s <= 30) { idx = 2; }
      else if (s <= 40) { idx = 3; }
      else { idx = 4; }
      return { idx: idx + 1, col: this.colors[idx] };
  }

}

window.addEventListener( 'load', function(){
    IhcPasswordStrength.init({});
});

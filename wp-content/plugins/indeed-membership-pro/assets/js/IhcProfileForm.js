/**
 * Ultimate Membership Pro - Profile Form
 */
"use strict";
var IhcProfileForm = {
    required_fields             : [],
    conditional_logic_fields    : [],
    conditional_text_fields     : [],
    unique_fields               : [],
    must_submit                 : 0,

    init									: function( args ){
        var obj = this;

        // required fields
        if ( typeof window.ihc_edit_required_fields !== 'undefined' ){
            obj.required_fields = JSON.parse( window.ihc_edit_required_fields );
        }
        // conditional logic
        if ( typeof window.ihc_edit_conditional_logic !== 'undefined' ){
            obj.conditional_logic_fields = JSON.parse( window.ihc_edit_conditional_logic );
        }
        // conditional text
        if ( typeof window.ihc_edit_conditional_text !== 'undefined' ){
            obj.conditional_text_fields = JSON.parse( window.ihc_edit_conditional_text );
        }
        // unique field
        if ( typeof window.ihc_edit_unique_fields !== 'undefined' ){
            obj.unique_fields = JSON.parse( window.ihc_edit_unique_fields );
        }

        // required fields
        if ( obj.required_fields.length > 0 ){

            // check on each field
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

            // on submit form
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
        }
        // end of required fields


        // conditional logic
        if ( obj.conditional_logic_fields.length > 0 ){

            jQuery( obj.conditional_logic_fields ).each( function( index ){
                switch ( obj.conditional_logic_fields[index].field_to_check_type ){
                    case 'text':
                    case 'textarea':
                    case 'number':
                    case 'password':
                    case 'date':
                    case 'conditional_text':
                    case 'unique_value_text':
                      jQuery(".ihc-form-create-edit [name=" + obj.conditional_logic_fields[index].field_to_check + "]").on("blur", function(){
                          var checkValue = jQuery(".ihc-form-create-edit [name=" + obj.conditional_logic_fields[index].field_to_check + "]").val();
                          obj.ihcAjaxCheckFieldCondition( checkValue, '#' + obj.conditional_logic_fields[index].target_parent_id, obj.conditional_logic_fields[index].target_field, obj.conditional_logic_fields[index].show );
                      });
                      break;
                    case 'select':
                      jQuery(".ihc-form-create-edit [name=" + obj.conditional_logic_fields[index].field_to_check + "]").on("change", function(){
                          var checkValue = jQuery(".ihc-form-create-edit [name='" + obj.conditional_logic_fields[index].field_to_check + "']").val();
                          // do something with checkValue
                          obj.ihcAjaxCheckFieldCondition( checkValue, '#' + obj.conditional_logic_fields[index].target_parent_id, obj.conditional_logic_fields[index].target_field, obj.conditional_logic_fields[index].show );
                      });
                      break;
                    case 'multi_select':
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
                    case 'radio':
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

        // conditional text
        if ( obj.conditional_text_fields.length > 0 ){
            jQuery( obj.conditional_text_fields ).each( function( index ){
              jQuery(".ihc-form-create-edit [name=" + obj.conditional_text_fields[index] + "]").on("blur", function(){
                  var checkValue = jQuery(".ihc-form-create-edit [name=" + obj.conditional_text_fields[index] + "]").val();
                  obj.ajaxCheckConditionalText( obj.conditional_text_fields[index] );
              });
            });
        }

        // unique fields
        if ( obj.unique_fields.length > 0 ){
            jQuery( obj.unique_fields ).each( function( index ){
              jQuery(".ihc-form-create-edit [name=" + obj.unique_fields[index] + "]").on("blur", function(){
                  var checkValue = jQuery(".ihc-form-create-edit [name=" + obj.unique_fields[index] + "]").val();
                  obj.ajaxCheckUniqueField( obj.unique_fields[index] );
              });
            });
        }

        // country and state
        if ( jQuery('[name=ihc_country]').length > 0 && jQuery('[name=ihc_state]').length > 0 ){
            jQuery('[name=ihc_country]').on( 'change', function(){
                obj.updateStateField();
            } );
        }

        obj.datePicker();
        obj.uploadFileAndAvatar();
        obj.countrySelect();

    },

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

        jQuery.ajax({
              type : "post",
              url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
              data : {
                         action         : "ihc_ajax_forms_check_one_field",
                         name           : fieldName,
                         value          : val1,
                         second_value   : val2,
                         is_edit        : 1,
              },
              success: function ( response ) {
                	//remove prev notice, if its case
                  var data = JSON.parse( response );
                  if ( typeof window.indeedRegisterErrors === 'undefined' ){
                      window.indeedRegisterErrors = [];
                  }
                	jQuery(target_id + ' .ihc-register-notice').remove();
                	jQuery('.ihc-form-create-edit [name=' + fieldName + ']').removeClass('ihc-input-notice');
                	if ( data.status == 1 ){
                  		// it's all good
                      self.IhcProfileForm.removeElementFromArray( window.indeedRegisterErrors, fieldName );
                	} else {
                  		jQuery( target_id ).append('<div class="ihc-register-notice">' + data.message + '</div>');
                  		jQuery('.ihc-form-create-edit [name='+fieldName+']').addClass('ihc-input-notice');
                      self.IhcProfileForm.addElementToArray( window.indeedRegisterErrors, fieldName );
                  }
              }
        });
    },

    checkAllFieldsBeforeSubmit          : function( obj, evt ){

        var fields_to_send = [];
        var exceptions = jQuery("[name=ihc_exceptionsfields]").val();
      	if (exceptions){
      		var exceptions_arr = exceptions.split(',');
      	}

        for ( var i=0; i<obj.required_fields.length; i++ ){
          //CHECK IF FIELD is in exceptions
          if ( exceptions_arr && exceptions_arr.indexOf( obj.required_fields[i] ) > -1 ){
              continue;
          }

          var is_unique_field = false;

          jQuery('.ihc-form-create-edit [name='+obj.required_fields[i]+']').removeClass('ihc-input-notice');

          var field_type = obj.getFieldTypeByName( obj.required_fields[i] );

          if (field_type=='checkbox' || field_type=='radio'){
            var val1 = obj.getCheckboxRadioValue(field_type, obj.required_fields[i]);
          } else if ( field_type=='multiselect' ){
            val1 = jQuery('.ihc-form-create-edit [name=\'' + obj.required_fields[i] + '[]\']').val();
            if (typeof val1=='object' && val1!=null){
              val1 = val1.join(',');
            }
          } else {
            var val1 = jQuery('.ihc-form-create-edit [name='+obj.required_fields[i]+']').val();
            if (jQuery('.ihc-form-create-edit [name='+obj.required_fields[i]+']').attr('data-search-unique')){
              var is_unique_field = true;
            }
          }

          var val2 = '';
          if (obj.required_fields[i]=='pass2'){
            val2 = jQuery('.ihc-form-create-edit [name=pass1]').val();
          } else if (obj.required_fields[i]=='confirm_email'){
            val2 = jQuery('.ihc-form-create-edit [name=user_email]').val();
          }

          var params_to_send = {name: obj.required_fields[i], value: val1, second_value: val2};
          if (is_unique_field){
            params_to_send.is_unique_field = true;
          }
          fields_to_send.push(params_to_send);
        }

        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
            data : {
                       action             : "ihc_ajax_forms_check_all_fields",
                       fields_obj         : fields_to_send
            },
            success: function ( response ) {

              var responseObject = JSON.parse( response );
              var must_submit = 1;

              if ( jQuery('.ihc-register-notice').length > 0 ){
                  // remove all previous error messages
                  jQuery('.ihc-register-notice').remove();
              }

            	for ( var j=0; j<responseObject.length; j++ ){
                  var field_type = obj.getFieldTypeByName( responseObject[j].name );

                	if (field_type=='radio'){
                		var target_id = jQuery('.ihc-form-create-edit [name='+responseObject[j].name+']').parent().parent().attr('id');
                	} else if (field_type=='checkbox' && responseObject[j].name != 'tos' ){
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

    inArray           : function( needle, haystack ) {
        for ( var i = 0; i < haystack.length; i++ ) {
            if ( haystack[i] == needle ){
               return true;
            }
        }
        return false;
    },

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

    ihcAjaxCheckFieldCondition          : function(check_value, field_id, field_name, show){
       	jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
            data : {
                       action     : "ihc_ajax_profile_edit_check_one_conditional_logic",
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
                       action        : "ihc_ajax_edit_profile_check_unique_field",
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

    ajaxCheckConditionalText            : function( fieldName ){
        var targetId = '#' + jQuery('.ihc-form-create-edit [name='+fieldName+']').parent().attr('id');
      	var value = jQuery('.ihc-form-create-edit [name='+fieldName+']').val();
        if ( value == '' ){
            return;
        }
        jQuery.ajax({
            type : "post",
            url : decodeURI(window.ihc_site_url)+'/wp-admin/admin-ajax.php',
            data : {
                       action        : "ihc_ajax_edit_profile_check_conditional_text_field",
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
		IhcProfileForm.init();
});

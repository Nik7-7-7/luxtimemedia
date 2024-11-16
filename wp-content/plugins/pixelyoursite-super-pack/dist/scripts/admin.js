jQuery(document).ready(function ($) {
    $(".pys-pysselect2").each(function(){$(this).pysselect2({placeholder: $(this).data("placeholder")})})
    $(".pys-condition-pysselect2:not(#pixel_facebook_hide_conditions, #pixel_ga_hide_conditions, #pixel_ads_hide_conditions, #pixel_facebook_hide_url_conditions, #pixel_ga_hide_url_conditions, #pixel_ads_hide_url_conditions)").each(function(){$(this).pysselect2({placeholder: $(this).data("placeholder"), tags: true})})
    $(document).on('click', '.remove-row', function () {
        $(this).closest('.plate').remove();
    });

    $body = $('body');
    $body.on('change','.pixel_info .is_enable',function () {
        updatePixelData($(this).parents('.pixel_info'),'is_enable',this.checked)
    })
    $body.on('change','.pixel_info .pys_ga_use_server_api',function () {
        updatePixelData($(this).parents('.pixel_info'),'use_server_api',this.checked)
        if(this.checked == '1') {
            $(this).parents('.pixel_info').find('.server_access_api_token').removeAttr('disabled')
        }
        else {
            $(this).parents('.pixel_info').find('.server_access_api_token').attr('disabled', 'disabled')
        }
    })

    $body.on('input','.pixel_info .pixel_id'  ,function () {
        updatePixelData($(this).parents('.pixel_info'),'pixel_id',$(this).val())
    })
    $body.on('input','.server_access_api_token'  ,function () {
        updatePixelData($(this).parents('.pixel_info'),'server_access_api_token',$(this).val())
    })

    $body.on('change','.pixel_info .enable_server_container',function () {
        updatePixelData($(this).parents('.pixel_info'),'enable_server_container',this.checked)
    })
    $body.on('input','.server_container_url'  ,function () {
        updatePixelData($(this).parents('.pixel_info'),'server_container_url',$(this).val())
    })
    $body.on('input','.transport_url'  ,function () {
        updatePixelData($(this).parents('.pixel_info'),'transport_url',$(this).val())
    })
    $body.on('change','.send_page_view'  ,function () {

        updatePixelData($(this).parents('.pixel_info'),'send_page_view',this.checked)
    })
    $body.on('change','.first_party_collection'  ,function () {

        updatePixelData($(this).parents('.pixel_info'),'first_party_collection',this.checked)
    })

    $body.on('input','.pixel_info .pixel_ext'  ,function () {

        updatePixelData($(this).parents('.pixel_info'),'ext_'+$(this).data('ext'),$(this).val())
    })
    $body.on('change','.pixel_info .pixel_ext_chekbox'  ,function () {

        updatePixelData($(this).parents('.pixel_info'),'ext_'+$(this).data('ext'),this.checked)
    })

    $body.on('change','.pixel_info .enhanced_conversions_manual_enabled'  ,function () {
        updatePixelData($(this).parents('.pixel_info'),'enhanced_conversions_manual_enabled',this.checked)
    })

    $body.on('change','.pixel_info .is_fire_signal'  ,function () {
        updatePixelData($(this).parents('.pixel_info'),'is_fire_signal',this.checked)
    })

    $body.on('change','.pixel_info .is_fire_woo'  ,function () {
        updatePixelData($(this).parents('.pixel_info'),'is_fire_woo',this.checked)
    })
    $body.on('change','.pixel_info .is_fire_edd'  ,function () {
        updatePixelData($(this).parents('.pixel_info'),'is_fire_edd',this.checked)
    })

    $body.on('change','.pixel_info .is_fire_edd'  ,function () {
        updatePixelData($(this).parents('.pixel_info'),'is_fire_edd',this.checked)
    })
    $body.on('click', '.remove-conditions-row', function () {
        let pixelBlock = $(this).closest('.pixel_info');
        $(this).closest('.pixel_conditions').remove();
        pixelBlock.find('.pixel_conditions').first().trigger('condition_update');
    });
    $body.on('condition_update','.pixel_conditions'  ,function () {
        let items = new Array();

        $(this).parent().find(".pixel_conditions:not(.hidden_pixel_conditions)").each(function () {
            let data = {};
            $(this).find(".condition").each(function () {
                let name = $( this ).data('name');
                data[name] = $( this ).val()
                if(name == "sub_id") {
                    data['sub_id_name'] = $( this ).parent().find('.view_input').text()
                }
            })
            items.push(data)
        })
        if(items.length == 1)
        {
            $(this).parent().find('.remove-conditions-row').addClass('hidden');
        }
        else
        {
            $(this).parent().find('.remove-conditions-row').removeClass('hidden');
        }
        updatePixelData($(this).parents('.pixel_info'),'condition',items)
    })

    $body.on('change','.pixel_lang_check_box input'  ,function () {
        let items = new Array();
        $parent = $(this).parents('.wpml_lags')
        $parent.find("input:checked").each(function () {
            items.push($(this).attr('value'))
        })
        $parentInfo = $(this).parents('.pixel_info');

        if($parentInfo.length > 0) {
            updatePixelData($parentInfo,'wpml_active_lang',items);// multi pixel
        } else {
            updateMainPixelLang($(this).parents('.wpml_lags').find(".pixel_lang"),items); // main pixel
        }

    })

    $body.on('change','.pixel_info .is-hide',function () {
        updatePixelData($(this).parents('.pixel_info'),'is_hide',this.checked)
    })
    $body.on('change', '.hide-conditions' ,function (e) {
        const selectedValues = $(this).val();
        updatePixelData($(this).parents('.pixel_info'),'hide_condition',selectedValues)
    })
    $body.on('change', '.hide-time' ,function (e) {
        updatePixelData($(this).parents('.pixel_info'),'hide_time',$(this).val())
    })
    $body.on('change','.pixel_info .is-hide-url',function () {
        updatePixelData($(this).parents('.pixel_info'),'is_hide_url',this.checked)
    })
    $body.on('change', '.hide-conditions-url' ,function (e) {
        const selectedValues = $(this).val();
        updatePixelData($(this).parents('.pixel_info'),'hide_condition_url',selectedValues)
    })
    function updatePixelData($parent,key,value){

        $input = $parent.find('> input')

        $data = $input.val()
        $data = $data ? JSON.parse($data) : {};
        if(key.lastIndexOf("ext_", 0) === 0) {
            let _key = key.substring(4);
            $data['extensions'] = $data['extensions'] ? $data['extensions'] : {}
            $data['extensions'][_key] = value;
        } else {
            $data[key] = value;
        }

        $input.val(JSON.stringify($data));
    }
    $body.on('click','.pys_superpack_add_conditions_pixel_id',function (e) {
        e.preventDefault();
        let count = $(this).parents('.settings_content').find('.pixel_info').length -1
        const elem = $(this).closest('.pixel_info').find('.hidden_pixel_conditions');
        const lastElement = $(this).closest('.pixel_info').find('.pixel_conditions:last');
        console.log(lastElement)
        var $row = elem.clone()
            .insertAfter(lastElement)
            .removeClass('hidden_pixel_conditions')
            .css('display', 'flex');
        let value = elem.find('select.condition').val();
        let parent = elem;
        addCondition(parent,value)
    });
    $('#pys_superpack_add_facebook_pixel_id').click(function (e) {
        e.preventDefault();
        let count = $(this).parents('.settings_content').find('.pixel_info').length -1
        var $row = $('#pys_superpack_facebook_pixel_id').clone()
            .insertBefore('#pys_superpack_facebook_pixel_id')
            .attr('id', '')
            .css('display', 'block');
        $row.find('#pixel_facebook_is_enable').attr('id','pixel_facebook_is_enable_'+count)
        $row.find("label[for='pixel_facebook_is_enable']").attr('for','pixel_facebook_is_enable_'+count)

        $row.find('#pixel_facebook_is_hide').attr('id','pixel_facebook_is_hide_'+count)
        $row.find("label[for='pixel_facebook_is_hide']").attr('for','pixel_facebook_is_hide_'+count)

        $row.find('#pixel_facebook_is_hide_url').attr('id','pixel_facebook_is_hide_url_'+count)
        $row.find("label[for='pixel_facebook_is_hide_url']").attr('for','pixel_facebook_is_hide_url_'+count)


        $row.find('#pixel_facebook_hide_conditions').attr('id','pixel_facebook_hide_conditions_'+count)

        $('#pixel_facebook_hide_conditions_'+count).pysselect2({placeholder: $('#pixel_facebook_hide_conditions_'+count).data("placeholder"), tags: true});

        $row.find('#pixel_facebook_hide_url_conditions').attr('id','pixel_facebook_hide_url_conditions_'+count)

        $('#pixel_facebook_hide_url_conditions_'+count).pysselect2({placeholder: $('#pixel_facebook_hide_url_conditions_'+count).data("placeholder"), tags: true});
        updatePixelInputValue($row)
    });

    $('#pys_superpack_add_ga_tracking_id').click(function (e) {

        e.preventDefault();
        let count = $(this).parents('.settings_content').find('.pixel_info').length -1
        var $row = $('#pys_superpack_ga_tracking_id').clone();
        $row.find('#pixel_ga_is_enable').attr('id','pixel_ga_is_enable_'+count)
        $row.find("label[for='pixel_ga_is_enable']").attr('for','pixel_ga_is_enable_'+count)
        $row.find('#pys_ga_use_server_api').attr('id','pys_ga_use_server_api_'+count)
        $row.find("label[for='pys_ga_use_server_api']").attr('for','pys_ga_use_server_api_'+count)
        $row.insertBefore('#pys_superpack_ga_tracking_id')
            .attr('id', '')
            .css('display', 'block');

        $row.find('#pixel_ga_is_hide').attr('id','pixel_ga_is_hide_'+count)
        $row.find("label[for='pixel_ga_is_hide']").attr('for','pixel_ga_is_hide_'+count)

        $row.find('#pixel_ga_is_hide_url').attr('id','pixel_ga_is_hide_url_'+count)
        $row.find("label[for='pixel_ga_is_hide_url']").attr('for','pixel_ga_is_hide_url_'+count)


        $row.find('#pixel_ga_hide_conditions').attr('id','pixel_ga_hide_conditions_'+count)

        $('#pixel_ga_hide_conditions_'+count).pysselect2({placeholder: $('#pixel_ga_hide_conditions_'+count).data("placeholder"), tags: true});

        $row.find('#pixel_ga_hide_url_conditions').attr('id','pixel_ga_hide_url_conditions_'+count)

        $('#pixel_ga_hide_url_conditions_'+count).pysselect2({placeholder: $('#pixel_ga_hide_url_conditions_'+count).data("placeholder"), tags: true});

        $row.find('#enable_server_container').attr('id','enable_server_container_'+count);
        $row.find("label[for='enable_server_container']").attr('for','enable_server_container_'+count)

        updatePixelData($row,'use_server_api',$row.find('.pys_ga_use_server_api')[0].checked)
        updatePixelData($row,'server_access_api_token',$row.find('.server_access_api_token').val())
        updatePixelData($row,'enable_server_container',$row.find('.enable_server_container')[0].checked)
        updatePixelData($row,'send_page_view',$row.find('.send_page_view')[0].checked)
        updatePixelData($row,'first_party_collection',$row.find('.first_party_collection')[0].checked)
        updatePixelData($row,'server_container_url',$row.find('.server_container_url').val())
        updatePixelData($row,'transport_url',$row.find('.transport_url').val())

        updatePixelInputValue($row)
    });

    $('#pys_superpack_add_google_ads_id').click(function (e) {

        e.preventDefault();
        let count = $(this).parents('.settings_content').find('.pixel_info').length -1
        var $row = $('#pys_superpack_google_ads_id').clone();
        $row.find('#pixel_ads_is_enable').attr('id','pixel_ads_is_enable_'+count)
        $row.find("label[for='pixel_ads_is_enable']").attr('for','pixel_ads_is_enable_'+count)
        $row.insertBefore('#pys_superpack_google_ads_id')
            .attr('id', '')
            .css('display', 'block');

        $row.find('#enhanced_conversions_manual_enabled').attr('id','enhanced_conversions_manual_enabled_'+count)
        $row.find("label[for='enhanced_conversions_manual_enabled']").attr('for','enhanced_conversions_manual_enabled_'+count)

        $row.find('#pixel_ads_is_hide').attr('id','pixel_ads_is_hide_'+count)
        $row.find("label[for='pixel_ads_is_hide']").attr('for','pixel_ads_is_hide_'+count)

        $row.find('#pixel_ads_is_hide_url').attr('id','pixel_ads_is_hide_url_'+count)
        $row.find("label[for='pixel_ads_is_hide_url']").attr('for','pixel_ads_is_hide_url_'+count)


        $row.find('#pixel_ads_hide_conditions').attr('id','pixel_ads_hide_conditions_'+count)

        $('#pixel_ads_hide_conditions_'+count).pysselect2({placeholder: $('#pixel_ads_hide_conditions_'+count).data("placeholder"), tags: true});

        $row.find('#pixel_ads_hide_url_conditions').attr('id','pixel_ads_hide_url_conditions_'+count)

        $('#pixel_ads_hide_url_conditions_'+count).pysselect2({placeholder: $('#pixel_ads_hide_url_conditions_'+count).data("placeholder"), tags: true});

        updatePixelInputValue($row)

    });

    function updatePixelInputValue($pixel) {
        updatePixelData($pixel,'is_enable',$pixel.find('.is_enable')[0].checked)
        updatePixelData($pixel,'pixel_id',$pixel.find('.pixel_id').val())
        $pixel.find('.pixel_ext').each(function () {
            updatePixelData($pixel,'ext_'+$(this).data('ext'),$(this).val())
        })
        $pixel.find('.pixel_ext_chekbox').each(function () {
            updatePixelData($pixel,'ext_'+$(this).data('ext'),this.checked)
        })

        updatePixelData($pixel,'enhanced_conversions_manual_enabled',$pixel.find('.enhanced_conversions_manual_enabled')[0].checked)


        updatePixelData($pixel,'is_fire_signal',$pixel.find('.is_fire_signal')[0].checked)

        let $isFireWoo = $pixel.find('.is_fire_woo')
        if($isFireWoo.length > 0) {
            updatePixelData($pixel,'is_fire_woo',$isFireWoo[0].checked)
        }
        let $isFireEdd = $pixel.find('.is_fire_edd')
        if($isFireEdd.length > 0) {
            updatePixelData($pixel,'is_fire_edd',$isFireEdd[0].checked)
        }

        let items = new Array();
        let data = {};

        $pixel.find(".pixel_conditions .condition").each(function () {

            let name = $( this ).data('name');
            data[name] = $( this ).val()
            if(name == "sub_id") {
                data['sub_id_name'] = $( this ).parent().find('.view_input').text()
            }

        })
        items.push(data)
        updatePixelData($pixel,'condition',items)


        $parent = $pixel.find('.wpml_lags')
        if($parent.length > 0) {
            let langs = new Array();
            $parent.find("input:checked").each(function () {
                langs.push($(this).attr('value'))
            })
            updatePixelData($pixel,'wpml_active_lang',langs)
        }



    }



    function updateMainPixelLang(langBox,langs) {

        var langval = "";
        if(langs.length === 0) {
            langval = "empty";
        } else {
            langs.forEach(function (item,index) {
                if(index !== 0) {
                    langval += "_";
                }
                langval += item;
            });
        }
        langBox.val(langval);
    }


    // pixel conditions
    $(document).on('change','.pixel_conditions .condition_select',function() {
        let value = $(this).val();
        let parent = $(this).parent();

        $(this).nextAll('.pys-pysselect2').pysselect2( 'destroy' );
        $(this).nextAll('select,div').remove()

        addCondition(parent,value);
    });

    $(document).on('click','.pixel_conditions .condition_search .view_input',function () {
        $(this).toggleClass('open')
    });
    $(document).on('input','.pixel_conditions .condition_search_input',function () {
        let $parent = $(this).parents('.condition_search');
        let condition = conditions[$parent.data('conditional')];
        let ul = $parent.find("ul")
        let data = {
            "action":"pys_filter_condition_autocomplete",
            "query":condition.controls.query,
            "q":$(this).val()
        }
        jQuery.ajax( {
            type: 'POST',
            url: ajaxurl,
            data: data,
            success: function(data){
                if(data.success) {
                    ul.find('.item').remove();
                    data.data.results.forEach(function (item) {
                        ul.append('<li class="item" data-id="'+item.id+'">'+item.text+'</li');
                    });
                }

                console.log("data",data)
            },
        });
    });
    $(document).on('click','.pixel_conditions ul .item',function () {
        let $parent = $(this).parents('.condition_search');
        let input = $parent.find('.input_value');
        let text = $parent.find('.view_input');
        let inputSearch = $parent.find('.condition_search_input');
        text.text($(this).text())
        input.val($(this).data("id"))
        $(this).parents('.pixel_conditions').trigger('condition_update')
        // clear search and close menu
        $('.pixel_conditions .condition_search .view_input').removeClass('open')
        $('.pixel_conditions ul .item').remove()
        inputSearch.val("")
    })


    function addCondition(parent,value,defaultValue = false) {
        let nextCondition = conditions[value];
        if(nextCondition && nextCondition.controls.type == "select_titled") {
            let select = '<select  class="condition_select condition" data-name="'+nextCondition.controls.name +'" >';

            if(value != 'woocommerce' && value != "edd") {
                select +=' <option value="all">'  +nextCondition.all_label + '</option>';
            }
              
            nextCondition.controls.options.forEach(function (option){
                if(option.items.length > 1) {
                    select += '<optgroup label="' + option.title + '">';
                }
                option.items.forEach(function (sub) {
                    select += '<option value="' + sub + '" '+ (defaultValue == sub ? "selected":"") +'>' + conditions[sub].label + '</option>';
                });
                if(option.items.length > 1) {
                    select += '</optgroup>';
                }
            });
            parent.append(select)

        }

        if(nextCondition && nextCondition.controls.type == "select_titled_array") {
            let select = '<select  class="condition_select condition pys-pysselect2" multiple data-name="'+nextCondition.controls.name +'" >';

            nextCondition.controls.options.forEach(function (option){
                if ( typeof defaultValue == 'object' ) {
                    select += '<option value="' + option['item'] + '" '+ (defaultValue.includes(option['item']) ? 'selected="selected"':"") + '">' + option['title'] + '</option>';
                } else {
                    select += '<option value="' + option['item'] + '">' + option['title'] + '</option>';
                }
            });
            parent.append(select);
            parent.find(".pys-pysselect2").each(function(){$(this).pysselect2({multiple: true})})
        }

        if(nextCondition && nextCondition.controls.type == "search") {
            var search = '<div class="condition_search" data-conditional='+value+'>' +
                '<input class="input_value condition" type="hidden" data-name="'+nextCondition.controls.name +'"'+ (defaultValue ? 'value="'+defaultValue.value+'"' : '') +' >' +
                '<div class="view_input">'+(defaultValue ? defaultValue.label : nextCondition.all_label)+'</div>' +
                '<ul><li><input type="text" class="condition_search_input " /></li></ul></div>';

            parent.append(search)

        }

        parent.trigger('condition_update')
    }

    function initConditional() {
        $('.pixel_conditions').each(function () {
            let $parent = $(this).find('.pys_flex_block');
            let params =  $(this).data('params');
            params.forEach(function (item) {
                $parent.find(".condition[data-name='name'] option[value='"+item.name+"']").prop('selected', true);
                if(item.sub_name) {
                    addCondition($parent,item.name,item.sub_name)
                    if(item.sub_name != 'all') {
                        addCondition($parent,item.sub_name,{label:item.sub_id_name,value:item.sub_id})
                    }
                }
                if(item.options) {
                    addCondition($parent,item.name,item.options)
                }
            });
        })
    }

    // load
    initConditional();
});


/**
 * Sets proper language cookie
 *
 * Sends ajax request to get browser and IP language.
 * Sets cookie with current language.
 * Changes cookie when clicking a link pointed to another language.
 *
 */
function TRP_IN_Determine_Language(){
    var _this = this;
    var trpCookie = null;
    var trpHelper = null;

    this.get_lang_from_url = function ( url ) {
        // we remove http or https
        // if the user links to a http link but the abs_home_url is https, we're serving the https one so we don't brake cookies if he doesn't have proper redirects
        var lang = url.replace( /^(http|https):\/\//g, '');
        var abs_home = trp_language_cookie_data[ 'abs_home' ].replace( /^(http|https):\/\//g, '');

        // we have removed the home path from our URL. We're adding a / in case it's the homepage of one of the languages
        // removing / from the front so it's easier for understanding explode()
        lang = trpHelper.ltrim( trpHelper.trailingslashit( lang.replace(abs_home, '') ),'/' );

        // We now have to see if the first part of the string is actually a language slug
        var lang_array = lang.split("/");
        if( lang_array.length < 2 ){
            return trp_language_cookie_data['default_language'];
        }
        // The language code is the first non-empty item in this array depending on abs_home slashes
        for ( var i = 0; i < lang_array.length; i++ ){
            if ( lang_array[i] != undefined && lang_array[i] != '' ) {
                lang = lang_array[i];
                break;
            }
        }
        // the lang slug != actual lang. So we need to do array_search so we don't end up with en instead of en_US
        if( trpHelper.in_array( lang, trp_language_cookie_data['url_slugs']) ){
            return trpHelper.array_search(lang, trp_language_cookie_data['url_slugs'] );
        } else {
            return trp_language_cookie_data['default_language'];
        }

    };

    this.get_current_dom_language = function(){
        var html_language_document = document.getElementById("tp-language");
        var html_language;
        if ( html_language_document ){
            if ( jQuery('#tp-language').attr('data-tp-language') ){
                html_language = jQuery( '#tp-language' ).attr( 'data-tp-language' );
            }else if (jQuery('#tp-language').attr('lang') ){
                html_language = jQuery( '#tp-language' ).attr( 'lang' );
            }
        } else {
            html_language = jQuery('html').attr('lang');
            html_language = html_language.split("-").join("_");
        }

        if ( !trpHelper.in_array( html_language, trp_language_cookie_data['publish_languages'] ) ){
            html_language = "error_determining_language";
        }

        return html_language;
    };


    this.ajax_get_needed_language = function() {
        jQuery.ajax({
            url: trp_language_cookie_data['trp_ald_ajax_url'],
            type: 'post',
            dataType: 'json',
            data: {
                action: 'trp_ald_get_needed_language',
                detection_method: trp_language_cookie_data['detection_method'],
                popup_option: trp_language_cookie_data['popup_option'],
                popup_textarea: trp_language_cookie_data['popup_textarea'],
                popup_type: trp_language_cookie_data['popup_type'],
                popup_textarea_change_button: trp_language_cookie_data['popup_textarea_change_button'],
                popup_textarea_close_button: trp_language_cookie_data['popup_textarea_close_button'],
                publish_languages: trp_language_cookie_data['publish_languages'],
                iso_codes: trp_language_cookie_data['iso_codes'],
                english_name: trp_language_cookie_data['english_name'],
                default_language: trp_language_cookie_data['default_language'],
                is_iphone_user_check: trp_language_cookie_data['is_iphone_user_check']
            },
            success: function( response ) {
                if ( response ) {
                    _this.redirect_if_needed( response );
                }else{
                    _this.redirect_if_needed( _this.get_current_dom_language() );
                }

            },
            error: function( errorThrown ){
                // make current language the needed language
                _this.redirect_if_needed( _this.get_current_dom_language() );
            }
        });
    };

    this.activate_popup = function( response, url_to_redirect ){

        _this.make_ls_clickable();
        _this.ls_make_default_language_preselected( response );

        var text_popup = document.getElementById("trp_ald_popup_text");
        var popup = document.getElementById("trp_ald_modal_container");
        var close = document.getElementById("trp_ald_x_button");

        var popup_change_button = document.getElementById("trp_ald_popup_change_language");
        var popup_close_button_text = document.getElementById("trp_ald_x_button_textarea");
        var close_popup = document.getElementById("trp_ald_x_button_and_textarea");

        popup.style.display= 'block';

        var no_text_popup_select_current_language = document.getElementById('trp_ald_popup_current_language');

        var replace_suggested_language_text = trp_language_cookie_data['popup_textarea'].replace("{{suggested_language}}", trp_language_cookie_data['english_name'][response]);

        text_popup.innerHTML = replace_suggested_language_text;

        popup_change_button.innerHTML = trp_language_cookie_data['popup_textarea_change_button'];

        popup_close_button_text.innerHTML = trp_language_cookie_data['popup_textarea_close_button'];

        close.title = trp_language_cookie_data['popup_textarea_close_button'];
        popup_close_button_text.title = trp_language_cookie_data['popup_textarea_close_button'];
        
        close_popup.onclick = function(){
            popup.style.display = 'none';
            trpCookie.setCookie( trp_language_cookie_data['cookie_name'], _this.get_current_dom_language(), trp_language_cookie_data['cookie_age'], trp_language_cookie_data['cookie_path'] );

        }

        _this.ls_select_language();

        popup_change_button.onclick = function(){
            var selected_language = no_text_popup_select_current_language.getAttribute('data-trp-ald-selected-language');
            trpCookie.setCookie( trp_language_cookie_data['cookie_name'], selected_language, trp_language_cookie_data['cookie_age'], trp_language_cookie_data['cookie_path'] );


        }

    }

    this.activate_hello_bar = function (response, url_to_redirect){

        var no_text_popup = document.getElementById("trp_ald_no_text_popup_template");

        var clone = no_text_popup.content.cloneNode(true);

        var close_button = clone.getElementById('trp_close');
        var no_text_popup_text = clone.getElementById('trp_ald_no_text_popup_text');

        var button_change_language_no_text_popup = clone.getElementById('trp_ald_no_text_popup_change_language');

        var no_text_popup_select_current_language = clone.getElementById('trp_ald_popup_current_language');
        var no_text_popup_x_textarea = clone.getElementById('trp_ald_no_text_popup_x_button_textarea');
        var no_text_popup_duplicate_x_button = clone.getElementById('trp_ald_no_text_popup_x_button');

        var div = document.createElement('div');

        var replace_suggested_language_text = trp_language_cookie_data['popup_textarea'].replace("{{suggested_language}}", trp_language_cookie_data['english_name'][response]);

        no_text_popup_text.innerHTML = replace_suggested_language_text;

        button_change_language_no_text_popup.innerHTML = trp_language_cookie_data['popup_textarea_change_button'];


        button_change_language_no_text_popup.onclick = function() {
            var selected_language = no_text_popup_select_current_language.getAttribute( 'data-trp-ald-selected-language' );
            trpCookie.setCookie( trp_language_cookie_data[ 'cookie_name' ], selected_language, trp_language_cookie_data[ 'cookie_age' ], trp_language_cookie_data[ 'cookie_path' ] );
        }

        no_text_popup_x_textarea.innerHTML = trp_language_cookie_data['popup_textarea_close_button'];

        no_text_popup_x_textarea.title = trp_language_cookie_data['popup_textarea_close_button'];
        close_button.title = trp_language_cookie_data['popup_textarea_close_button'];
        no_text_popup_duplicate_x_button.title = trp_language_cookie_data['popup_textarea_close_button'];

        div.appendChild(clone);
        //html.insertBefore(clone, html.firstChild);

        document.body.insertAdjacentElement('afterbegin', div);
        //document.body.appendChild(clone);

        close_button.onclick = function (){
            trpCookie.setCookie( trp_language_cookie_data['cookie_name'], _this.get_current_dom_language(), trp_language_cookie_data['cookie_age'], trp_language_cookie_data['cookie_path'] );
            document.body.removeChild(div);
        }

        no_text_popup_duplicate_x_button.onclick = function (){
            trpCookie.setCookie( trp_language_cookie_data['cookie_name'], _this.get_current_dom_language(), trp_language_cookie_data['cookie_age'], trp_language_cookie_data['cookie_path'] );
            document.body.removeChild(div);
        }

        no_text_popup_x_textarea.onclick = function (){
            trpCookie.setCookie( trp_language_cookie_data['cookie_name'], _this.get_current_dom_language(), trp_language_cookie_data['cookie_age'], trp_language_cookie_data['cookie_path'] );
            document.body.removeChild(div);
        }

        _this.make_ls_clickable();
        _this.ls_select_language();
        _this.ls_make_default_language_preselected( response );

    }

    this.make_ls_clickable = function (){
        jQuery('.trp_ald_ls_container .trp-ls-shortcode-current-language').click(function () {
            jQuery( '.trp_ald_ls_container .trp-ls-shortcode-current-language' ).addClass('trp-ls-clicked');
            jQuery( '.trp_ald_ls_container .trp-ls-shortcode-language' ).addClass('trp-ls-clicked');
        });

        jQuery('.trp_ald_ls_container .trp-ls-shortcode-language').click(function () {
            jQuery( '.trp_ald_ls_container .trp-ls-shortcode-current-language' ).removeClass('trp-ls-clicked');
            jQuery( '.trp_ald_ls_container .trp-ls-shortcode-language' ).removeClass('trp-ls-clicked');
        });


        jQuery(document).keyup(function(e) {
            if (e.key === "Escape") {
                jQuery( '.trp_ald_ls_container .trp-ls-shortcode-current-language' ).removeClass('trp-ls-clicked');
                jQuery( '.trp_ald_ls_container .trp-ls-shortcode-language' ).removeClass('trp-ls-clicked');
            }
        });

        jQuery(document).on("click", function(event){
            if(!jQuery(event.target).closest(".trp_ald_ls_container .trp-ls-shortcode-current-language").length){
                jQuery( '.trp_ald_ls_container .trp-ls-shortcode-current-language' ).removeClass('trp-ls-clicked');
                jQuery( '.trp_ald_ls_container .trp-ls-shortcode-language' ).removeClass('trp-ls-clicked');
            }
        });
    }

    this.ls_select_language = function(){
        
        var no_text_current_language = document.querySelector('.trp_ald_ls_container .trp-ls-shortcode-current-language');
        var no_text_popup_select_current_language = document.querySelector('.trp_ald_ls_container .trp-ls-shortcode-current-language');
        var popup_change_button = document.getElementById("trp_ald_popup_change_language");
        var button_change_language_no_text_popup = document.getElementById('trp_ald_no_text_popup_change_language');
        jQuery('.trp-ald-popup-select').click(function ( item ) {
            no_text_current_language.innerHTML = item.target.innerHTML;
            no_text_current_language.setAttribute('data-trp-ald-selected-language', item.target.getAttribute('data-trp-ald-selected-language'));
            var selected_language = no_text_popup_select_current_language.getAttribute('data-trp-ald-selected-language');
            _this.set_language_href(selected_language,popup_change_button,button_change_language_no_text_popup);
        });
    }

    this.ls_make_default_language_preselected = function( response ){

        var no_text_current_language = document.querySelector('[special-selector = "trp_ald_popup_current_language"]');
        var all_languages = document.querySelectorAll('.trp_ald_ls_container .trp-ald-popup-select');
        var popup_change_button = document.getElementById("trp_ald_popup_change_language");
        var button_change_language_no_text_popup = document.getElementById('trp_ald_no_text_popup_change_language');
        for ( var i = 0; i< all_languages.length; i++){
            if (all_languages[i].id === response){
                no_text_current_language.innerHTML = all_languages[i].innerHTML;
                no_text_current_language.setAttribute('data-trp-ald-selected-language', response);
                no_text_current_language.setAttribute('id', response);
                var selected_language = no_text_current_language.getAttribute('data-trp-ald-selected-language');
                _this.set_language_href(selected_language, popup_change_button, button_change_language_no_text_popup);
                break;
            }
        }
    }

    this.set_language_href = function ( selected_language, popup_change_button, button_change_language_no_text_popup ){
        if( popup_change_button !== null ){
            popup_change_button.href = _this.get_url_for_lang(selected_language);
        }
        if( button_change_language_no_text_popup !== null ){
            button_change_language_no_text_popup.href = _this.get_url_for_lang(selected_language);
        }
    }

    this.decode_HTML_entities = function(str) {
        let txt = new DOMParser().parseFromString(str, "text/html");
        return txt.documentElement.textContent;
    }

    this.get_url_for_lang = function( language ){

        for( i in trp_language_cookie_data['language_urls'] ){
            if( i === language ) {
                let url_for_language = _this.decode_HTML_entities(trp_language_cookie_data['language_urls'][i]);

                return url_for_language;
            }
        }
        return false;
    };

    this.is_valid_url = function ( url ) {
        if ( typeof url === 'undefined' || url === '' ){
            return false;
        }
        var starting_characters = ['#','?', 'javascript'];
        for (var i = 0; i < starting_characters.length; i++ ){
            if ( url.substring(0, starting_characters[i].length) === starting_characters[i]){
                return false;
            }
        }
        return true;
    };

    this.replace_underscore_with_dash = function (var_replace){
        var_replace = var_replace.toLowerCase();
        var_replace = var_replace.split("_").join("-");

        return var_replace;
    }

    this.is_same_language_code = function (var1, var2){
        var1 = _this.replace_underscore_with_dash(var1);
        var2 = _this.replace_underscore_with_dash(var2);

        if( var1 == var2 || var1 == "error_determining_language" || var2 == "error_determining_language" ){
            return true;
        }
        return false;
    }

    this.is_login_url = function( url ){
        if( url.includes( "wp-login.php" )){
            return true;
        }
        return false;
    }

    this.add_event_handlers = function(){
        jQuery('body').on('click', 'a', function(e) {

            var clicked_url = jQuery(this).attr("href");
            if ( _this.is_valid_url( clicked_url ) && !_this.is_login_url( clicked_url )) {
                var clicked_language = _this.get_lang_from_url(clicked_url);
                var trp_current_language = trpCookie.getCookie(trp_language_cookie_data['cookie_name']);

                if (!(_this.is_same_language_code(trp_current_language, clicked_language))) {
                    trpCookie.setCookie(trp_language_cookie_data['cookie_name'], clicked_language, trp_language_cookie_data['cookie_age'], trp_language_cookie_data['cookie_path']);
                }
            }
        });
    };

    this.check_if_iphone_user = function (){
        
        if( trp_language_cookie_data['is_iphone_user_check'] == false ){
            return false;
        }else{
            if( /iPhone/g.test(navigator.userAgent) ){
                return true;
            }else{
                return false;
            }
        }
    };

    this.redirect_if_needed = function( needed_language ){
        trpCookie.setCookie( trp_language_cookie_data['cookie_name'], needed_language, trp_language_cookie_data['cookie_age'], trp_language_cookie_data['cookie_path'] );
        _this.add_event_handlers();

        if (!(_this.is_same_language_code(_this.get_current_dom_language(), needed_language)) && _this.check_if_iphone_user() == false ){
            url_to_redirect = _this.get_url_for_lang( needed_language );

            if(url_to_redirect != 'undefined' && url_to_redirect!= false ) {
                // redirect to needed language
                if(trp_language_cookie_data['popup_type'] == 'normal_popup' && trp_language_cookie_data['popup_option'] == 'popup'){
                    _this.activate_popup( needed_language, url_to_redirect );
                }else{
                    if(trp_language_cookie_data['popup_type'] == 'hello_bar' && trp_language_cookie_data['popup_option'] == 'popup'){
                        _this.activate_hello_bar(needed_language, url_to_redirect);
                    }else {
                        window.location.replace( url_to_redirect );
                    }
                }
            }
        }
    };

    this.initialize = function (){
        trpCookie = new TRP_IN_Cookie();
        trpHelper = new TRP_IN_Helper();
        if ( ! trpCookie.areCookiesEnabled() ){
            _this.add_event_handlers();
            return;
        }

        var language_from_cookie = trpCookie.getCookie( trp_language_cookie_data['cookie_name'] );
        if ( language_from_cookie && trpHelper.in_array( language_from_cookie, trp_language_cookie_data['publish_languages'] ) ) {
            // if cookie is set, redirect if needed
            _this.redirect_if_needed( language_from_cookie );
        }else{
            // if cookie is not set, send request to find out language and then redirect if needed
            _this.ajax_get_needed_language();
        }
    };

    _this.initialize();
}

/**
 * String manipulation functions
 */
function TRP_IN_Helper(){

    this.trailingslashit = function ( string ){
        string = string.replace(/\/+$/,'');
        string = string + '/';
        return string;
    };

    this.ltrim = function ( string ) {
        var trimmed = string.replace(/^\s+/g, '');
        return trimmed;
    };

    this.in_array = function (needle, haystack) {
        for(var i in haystack ) {
            if(haystack[i] == needle) {
                return true;
            }
        }
        return false;
    };

    this.array_search = function(val, array) {
        if(typeof(array) === 'array' || typeof(array) === 'object') {
            var rekey;
            for(var i in array) {
                if(array[i] == val) {
                    rekey = i;
                    break;
                }
            }
            return rekey;
        }
    };

    /**
     * Update url with query string.
     *
     */
    this.update_query_string = function(key, value, url) {
        if (!url) url = window.location.href;
        var re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
            hash;

        if (re.test(url)) {
            if (typeof value !== 'undefined' && value !== null)
                return url.replace(re, '$1' + key + "=" + value + '$2$3');
            else {
                hash = url.split('#');
                url = hash[0].replace(re, '$1$3').replace(/(&|\?)$/, '');
                if (typeof hash[1] !== 'undefined' && hash[1] !== null)
                    url += '#' + hash[1];
                return url;
            }
        }
        else {
            if (typeof value !== 'undefined' && value !== null ) {
                var separator = url.indexOf('?') !== -1 ? '&' : '?';
                hash = url.split('#');
                url = hash[0] + separator + key + '=' + value;
                if (typeof hash[1] !== 'undefined' && hash[1] !== null)
                    url += '#' + hash[1];
                return url;
            }
            else
                return url;
        }
    };
}

/**
 * Manipulate cookie: set/get/erase
 */
function TRP_IN_Cookie() {
    this.setCookie = function(cname,cvalue,exdays,cpath){
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=" + cpath + ";SameSite=Lax";
    };

    this.getCookie = function(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    };

    this.eraseCookie = function(name) {
        document.cookie = name+'=; Max-Age=-99999999;';
    };

    this.areCookiesEnabled = function(){
        if (navigator.cookieEnabled) return true;

        // set and read cookie
        document.cookie = "cookietest=1";
        var ret = document.cookie.indexOf("cookietest=") != -1;

        // delete cookie
        document.cookie = "cookietest=1; expires=Thu, 01-Jan-1970 00:00:01 GMT";

        return ret;
    }
}


jQuery( function() {
    trpDetermineLanguage = new TRP_IN_Determine_Language();
});

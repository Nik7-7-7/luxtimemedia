jQuery( function(){

    jQuery( 'input[value="trp_nbol_all_languages"]' ).click( function(){
        if( jQuery( this ).is(':checked') ) {
            jQuery( '.trp-nbol-lang-input', jQuery( this ).parent().parent() ).prop('readonly', 'readonly');
        }
        else{
            jQuery( '.trp-nbol-lang-input', jQuery( this ).parent().parent() ).removeAttr('readonly');
        }
    });

    jQuery( '.trp-nbol-lang-input' ).click( function(){
        if( jQuery(this).prop('readonly') ) {
            allLangInput = jQuery('input[value="trp_nbol_all_languages"]', jQuery(this).parent().parent());
            if (allLangInput.is(':checked')) {
                allLangInput.trigger('click');
            }
        }
    });

});
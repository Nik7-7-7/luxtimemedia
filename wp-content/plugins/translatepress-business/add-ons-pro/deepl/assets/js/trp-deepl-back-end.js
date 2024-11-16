jQuery(document).on('trpInitFieldToggler', function() {
    var deeplKey = TRP_Field_Toggler()
        deeplKey.init('.trp-translation-engine', '#trp-deepl-key', 'deepl' )

    var deeplType = TRP_Field_Toggler()
        deeplType.init('.trp-translation-engine', '#trp-deepl-api-type-free', 'deepl' )
})

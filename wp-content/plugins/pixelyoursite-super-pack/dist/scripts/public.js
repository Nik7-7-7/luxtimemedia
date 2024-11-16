/* global pysOptions */


jQuery( document ).ready( function () {
    if (pysOptions && Object.keys( pysOptions.track_dynamic_fields ).length > 0 ) {

        // expires in 30 min
        let expires = new Date( new Date().getTime() + 30 * 60 * 1000 );
        Object.entries( pysOptions.track_dynamic_fields ).forEach( ( item ) => {
            $( document ).on( "blur", 'input[name="' + item[ 1 ] + '"]', function () {
                let value = $( this ).val().trim();
                if ( value.length > 0 ) {
                    Cookies.set( 'pys_dyn_field_' + item[ 1 ], value, { expires: expires } );
                }
            } )
        } )
    }
} )
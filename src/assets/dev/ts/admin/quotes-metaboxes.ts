import * as jQuery from "jquery";

jQuery( function( $ ) {
    // Validate date whenever hour/minute inputs change.
    $( '.dws-qrwc-date-input [name$="_hour"], .dws-qrwc-date-input [name$="_minute"]' ).on( 'change', function() {
        $( '#' + $( this ).attr( 'name' ).replace( '_hour', '' ).replace( '_minute', '' ) ).trigger( 'change' );
    });
} );

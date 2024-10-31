import jQuery from "jquery";

jQuery( function( $ ) {
    $( 'a.button._blank' ).each( function() {
        $( this ).attr( 'target', '_blank' );
    } );
} );

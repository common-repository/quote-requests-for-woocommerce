import * as jQuery from "jquery";
import { Window } from "./interfaces";
declare let window: Window;

jQuery( function( $ ) {
    $( '.variations_form' )
        .on( 'reset_data', function() {
            $( this ).find( '.single_add_to_quote_request_list_button' ).addClass( 'disabled wc-variation-selection-needed' );
        } )
        .on( 'hide_variation', function() {
            $( this ).find( '.single_add_to_quote_request_list_button' ).addClass( 'disabled wc-variation-selection-needed' );
        } )
        .on( 'show_variation', function( event, variation, purchasable ) {
            if ( window.wp.hooks.applyFilters( 'dws_qrwc.add_to_quote_request_list.can_add_variation', purchasable, variation ) ) {
                $( this ).find( '.single_add_to_quote_request_list_button' ).removeClass( 'disabled wc-variation-selection-needed wc-variation-is-unavailable' );
            } else {
                $( this ).find( '.single_add_to_quote_request_list_button' ).addClass( 'disabled wc-variation-is-unavailable' );
            }
        } )
        .on( 'click', '.single_add_to_quote_request_list_button', function( event ) {
            if ( $( this ).is('.disabled') ) {
                event.preventDefault();

                if ( $( this ).is('.wc-variation-is-unavailable') ) {
                    window.alert( window.dws_qrwc_single_add_to_request_list_params.i18n_unavailable_text );
                } else if ( $( this ).is('.wc-variation-selection-needed') ) {
                    window.alert( window.dws_qrwc_single_add_to_request_list_params.i18n_make_a_selection_text );
                }
            }
        } );
} );

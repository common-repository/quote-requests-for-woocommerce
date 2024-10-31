<?php

defined( 'ABSPATH' ) || exit;

/**
 * Displays quote details in a table.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote   $quote      The quote object.
 */
function dws_qrwc_quote_details_table( DWS_Quote $quote ) {
	dws_qrwc_wc_get_template(
		'quote/quote-details.php',
		array(
			'quote' => $quote,
		)
	);
}
add_action( dws_qrwc_get_hook_tag( 'account', 'view_quote' ), 'dws_qrwc_quote_details_table' );
add_action( dws_qrwc_get_hook_tag( 'quote', 'tracking' ), 'dws_qrwc_quote_details_table' );

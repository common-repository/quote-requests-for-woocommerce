<?php

use DeepWebSolutions\WC_Plugins\QuoteRequests\Account\Endpoints\QuotesList;

defined( 'ABSPATH' ) || exit;

/**
 * Returns the list of table columns for the quotes list. We use the same keys as for orders in order for the orders
 * theme CSS to kick in.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  array
 */
function dws_qrwc_get_quotes_account_columns(): array {
	return apply_filters(
		dws_qrwc_get_hook_tag( 'account', array( 'quotes_list', 'columns' ) ),
		array(
			'order-number'  => __( 'Quote', 'quote-requests-for-woocommerce' ),
			'order-date'    => __( 'Date', 'quote-requests-for-woocommerce' ),
			'order-status'  => __( 'Status', 'quote-requests-for-woocommerce' ),
			'order-total'   => __( 'Total', 'quote-requests-for-woocommerce' ),
			'order-actions' => __( 'Actions', 'quote-requests-for-woocommerce' ),
		)
	);
}

/**
 * Returns the list of quote-level actions displayed in the account area.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote   $quote      The quote object.
 *
 * @return  array
 */
function dws_qrwc_get_quote_account_actions( DWS_Quote $quote ): array {
	$actions = array(
		'view' => array(
			'url'  => $quote->get_view_quote_url(),
			'name' => _x( 'View', 'account actions', 'quote-requests-for-woocommerce' ),
		),
	);

	if ( $quote->has_status( 'quote-accepted' ) ) {
		$order = $quote->get_accepted_order();
		if ( ! is_null( $order ) ) {
			$actions['view-order'] = array(
				'url'  => $order->get_view_order_url(),
				'name' => _x( 'View order', 'account actions', 'quote-requests-for-woocommerce' ),
			);
		}
	}
	if ( $quote->has_status( dws_qrwc_get_valid_quote_statuses_for_acceptance( $quote ) ) ) {
		$actions['accept'] = array(
			'url'  => $quote->get_accept_quote_url(),
			'name' => _x( 'Accept', 'account actions', 'quote-requests-for-woocommerce' ),
		);
	}
	if ( $quote->has_status( dws_qrwc_get_valid_quote_statuses_for_rejection( $quote ) ) ) {
		$actions['reject'] = array(
			'url'  => $quote->get_reject_quote_url( wc_get_account_endpoint_url( QuotesList::ENDPOINT ) ),
			'name' => _x( 'Reject', 'account actions', 'quote-requests-for-woocommerce' ),
		);
	}
	if ( $quote->has_status( dws_qrwc_get_valid_quote_statuses_for_cancellation( $quote ) ) ) {
		$actions['cancel'] = array(
			'url'  => $quote->get_cancel_quote_url( wc_get_account_endpoint_url( QuotesList::ENDPOINT ) ),
			'name' => _x( 'Cancel', 'account actions', 'quote-requests-for-woocommerce' ),
		);
	}

	return apply_filters( dws_qrwc_get_hook_tag( 'account', array( 'quotes_list', 'actions' ) ), $actions, $quote );
}

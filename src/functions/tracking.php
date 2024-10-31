<?php

use DeepWebSolutions\WC_Plugins\QuoteRequests\Account\Endpoints\QuotesList;

defined( 'ABSPATH' ) || exit;

/**
 * Returns the quote tracking page object.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  WP_Post|null
 */
function dws_qrwc_get_quote_tracking_page(): ?\WP_Post {
	return dws_qrwc_get_validated_setting( 'tracking-page', 'general' );
}

/**
 * Checks whether a given page (or the current page) contains the quote tracking shortcode.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   WP_Post|null    $page   The page object.
 *
 * @return  bool|null
 */
function dws_qrwc_is_quote_tracking_page( ?\WP_Post $page = null ): ?bool {
	global $post;

	$page = $page ?? $post ?? null;
	if ( \is_null( $page ) ) {
		return null;
	}

	return has_shortcode( $page->post_content, DWS_Quote_Tracking_SC::SHORTCODE );
}

/**
 * Returns the URL to a given tracking page that automatically pre-fills the form fields with the data from a given quote.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote       $quote              The quote object.
 * @param   WP_Post|null    $tracking_page      The quote status page. By default, the one from the settings.
 *
 * @return string|null
 */
function dws_qrwc_prepare_quote_tracking_page_url( DWS_Quote $quote, ?WP_Post $tracking_page = null ): ?string {
	$tracking_page = $tracking_page ?? dws_qrwc_get_quote_tracking_page();
	return is_null( $tracking_page ) ? null : add_query_arg(
		apply_filters(
			dws_qrwc_get_hook_tag( 'quote', array( 'tracking', 'query_arguments' ) ),
			array(
				'quote_id'    => $quote->get_id(),
				'quote_email' => $quote->get_billing_email(),
			),
			$quote,
			$tracking_page
		),
		get_permalink( $tracking_page )
	);
}

/**
 * Returns the URL that the customer of a given quote is recommended to use to check on their request status.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote   $quote      The quote object.
 *
 * @return  string|null
 */
function dws_qrwc_get_recommended_quote_tracking_url( DWS_Quote $quote ): ?string {
	if ( ! empty( $quote->get_customer_id() ) && ! empty( \WC()->query->get_query_vars()[ QuotesList::ENDPOINT ] ) ) {
		$recommended_url = wc_get_account_endpoint_url( QuotesList::ENDPOINT );
	} else {
		$recommended_url = dws_qrwc_prepare_quote_tracking_page_url( $quote );
	}

	return apply_filters( dws_qrwc_get_hook_tag( 'quote', array( 'tracking', 'recommended_url' ) ), $recommended_url, $quote );
}

/**
 * Returns the list of quote-level actions displayed on the quote status checking page.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote       $quote              The quote object.
 * @param   WP_Post|null    $tracking_page      The tracking page.
 *
 * @return  array
 */
function dws_qrwc_get_quote_tracking_actions( DWS_Quote $quote, ?WP_Post $tracking_page = null ): array {
	$actions = array();

	if ( $quote->has_status( dws_qrwc_get_valid_quote_statuses_for_acceptance( $quote ) ) ) {
		$actions['accept'] = array(
			'url'  => $quote->get_accept_quote_url(),
			'name' => _x( 'Accept', 'account actions', 'quote-requests-for-woocommerce' ),
		);
	}
	if ( $quote->has_status( dws_qrwc_get_valid_quote_statuses_for_rejection( $quote ) ) ) {
		$actions['reject'] = array(
			'url'  => $quote->get_reject_quote_url( add_query_arg( 'dws-qrwc-quote-tracking-nonce', wp_create_nonce( 'dws-qrwc-quote_tracking' ), dws_qrwc_prepare_quote_tracking_page_url( $quote, $tracking_page ) ) ),
			'name' => _x( 'Reject', 'account actions', 'quote-requests-for-woocommerce' ),
		);
	}
	if ( $quote->has_status( dws_qrwc_get_valid_quote_statuses_for_cancellation( $quote ) ) ) {
		$actions['cancel'] = array(
			'url'  => $quote->get_cancel_quote_url( add_query_arg( 'dws-qrwc-quote-tracking-nonce', wp_create_nonce( 'dws-qrwc-quote_tracking' ), dws_qrwc_prepare_quote_tracking_page_url( $quote, $tracking_page ) ) ),
			'name' => _x( 'Cancel', 'account actions', 'quote-requests-for-woocommerce' ),
		);
	}

	return apply_filters( dws_qrwc_get_hook_tag( 'quote', array( 'tracking', 'actions' ) ), $actions, $quote );
}

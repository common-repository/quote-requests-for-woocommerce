<?php

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;

defined( 'ABSPATH' ) || exit;

/**
 * Checks whether a given value is somehow directly related to a quote.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   int|DWS_Quote|WP_Post|mixed     $quote      Mixed value to check whether it belongs to a quote.
 *
 * @return  bool
 */
function dws_qrwc_is_quote( $quote ): bool {
	$is_quote = false;

	if ( is_object( $quote ) ) {
		$is_quote = is_a( $quote, DWS_Quote::class ) || ( is_a( $quote, WP_Post::class ) && 'dws_shop_quote' === $quote->post_type );
	} elseif ( is_numeric( $quote ) ) {
		$is_quote = ( 'dws_shop_quote' === get_post_type( $quote ) );
	}

	return apply_filters( dws_qrwc_get_hook_tag( 'is_quote' ), $is_quote, $quote );
}

/**
 * Wrapper around WC's own wc_get_order() function.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   int|WP_Post|DWS_Quote|WC_Order      $the_quote      Post object or post ID of the quote.
 *
 * @return  DWS_Quote|null
 */
function dws_qrwc_get_quote( $the_quote ): ?DWS_Quote {
	if ( $the_quote instanceof WP_Post ) {
		$the_quote = $the_quote->ID;
	}

	$quote = wc_get_order( $the_quote );
	if ( ! dws_qrwc_is_quote( $quote ) ) {
		$quote = null;
	}

	return apply_filters( dws_qrwc_get_hook_tag( 'get_quote' ), $quote, $the_quote );
}

/**
 * Returns a list of valid quote statuses.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   bool    $include_wc     Whether to include the 'wc-' prefix in the slugs or not.
 *
 * @return  array
 */
function dws_qrwc_get_quote_statuses( bool $include_wc = true ): array {
	$statuses = apply_filters(
		dws_qrwc_get_hook_tag( 'quote', 'statuses' ),
		array(
			'wc-quote-request'   => _x( 'New request', 'quote status', 'quote-requests-for-woocommerce' ),
			'wc-quote-waiting'   => _x( 'Waiting on customer', 'quote status', 'quote-requests-for-woocommerce' ),
			'wc-quote-expired'   => _x( 'Expired', 'quote status', 'quote-requests-for-woocommerce' ),
			'wc-quote-rejected'  => _x( 'Rejected by customer', 'quote status', 'quote-requests-for-woocommerce' ),
			'wc-quote-accepted'  => _x( 'Accepted by customer', 'quote status', 'quote-requests-for-woocommerce' ),
			'wc-quote-cancelled' => _x( 'Cancelled', 'quote status', 'quote-requests-for-woocommerce' ),
		)
	);

	return $include_wc ? $statuses
		: array_combine(
			str_replace( 'wc-', '', array_keys( $statuses ) ),
			array_values( $statuses )
		);
}

/**
 * Returns the nice name for a quote status.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $status     Slug of the status to retrieve the name for.
 *
 * @return  string
 */
function dws_qrwc_get_quote_status_name( string $status ): string {
	$statuses = dws_qrwc_get_quote_statuses();
	$status   = Strings::starts_with( $status, 'wc-' ) ? substr( $status, 3 ) : $status;
	return $statuses[ "wc-$status" ] ?? $status;
}

/**
 * Creates a new quote programmatically.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 *
 * @param   array   $args   Additional quote arguments.
 *
 * @return  \DWS_Quote|\WP_Error
 */
function dws_qrwc_create_quote( array $args = array() ) {
	$default_args = array(
		'status'        => null,
		'customer_id'   => null,
		'customer_note' => null,
		'created_via'   => null,
		'cart_hash'     => null,
		'quote_id'      => 0,
	);

	try {
		$args  = wp_parse_args( $args, $default_args );
		$quote = new DWS_Quote( $args['quote_id'] );

		// Update props that were set (not null).
		if ( ! is_null( $args['status'] ) ) {
			$quote->set_status( $args['status'] );
		}

		if ( ! is_null( $args['customer_note'] ) ) {
			$quote->set_customer_note( $args['customer_note'] );
		}

		if ( ! is_null( $args['customer_id'] ) ) {
			$quote->set_customer_id( is_numeric( $args['customer_id'] ) ? absint( $args['customer_id'] ) : 0 );
		}

		if ( ! is_null( $args['created_via'] ) ) {
			$quote->set_created_via( sanitize_text_field( $args['created_via'] ) );
		}

		if ( ! is_null( $args['cart_hash'] ) ) {
			$quote->set_cart_hash( sanitize_text_field( $args['cart_hash'] ) );
		}

		// Set these fields when creating a new order but not when updating an existing order.
		if ( ! $args['quote_id'] ) {
			$quote->set_currency( get_woocommerce_currency() );
			$quote->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
			$quote->set_customer_ip_address( WC_Geolocation::get_ip_address() );
			$quote->set_customer_user_agent( wc_get_user_agent() );
		}

		// Update other quote props set automatically.
		$quote->save();
	} catch ( Exception $exception ) {
		return new WP_Error( 'error', $exception->getMessage() );
	}

	return $quote;
}

/**
 * Creates a new order from an existing given quote.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote   $quote  The quote to duplicate.
 * @param   array       $args   Additional order arguments.
 *
 * @return  \WC_Order|\WP_Error
 */
function dws_qrwc_create_order_from_quote( DWS_Quote $quote, array $args = array() ) {
	$default_args = array(
		'status'        => 'pending',
		'customer_id'   => $quote->get_customer_id(),
		'customer_note' => $quote->get_customer_note(),
		'created_via'   => 'quote',
		'cart_hash'     => $quote->get_cart_hash(),
	);

	$args  = apply_filters( dws_qrwc_get_hook_tag( 'create_order_from_quote_args' ), wp_parse_args( $args, $default_args ), $quote, $args, $default_args );
	$order = wc_create_order( $args );

	if ( ! is_wp_error( $order ) ) {
		try {
			// wc_create_order doesn't let us set currency and tax status, so we do it here.
			$order->set_currency( $quote->get_currency( 'edit' ) );
			$order->set_prices_include_tax( $quote->get_prices_include_tax( 'edit' ) );

			// set the customer billing and shipping info
			$order->set_address( $quote->get_address( 'billing' ), 'billing' );
			$order->set_address( $quote->get_address( 'shipping' ), 'shipping' );

			// these are the same steps performed by WC_Checkout::set_data_from_cart
			$order->add_meta_data( 'is_vat_exempt', $quote->get_meta( 'is_vat_exempt' ) );
			$order->set_shipping_total( $quote->get_shipping_total() );
			$order->set_discount_total( $quote->get_discount_total() );
			$order->set_discount_tax( $quote->get_discount_tax() );
			$order->set_cart_tax( $quote->get_cart_tax() );
			$order->set_shipping_tax( $quote->get_shipping_tax() );
			$order->set_total( $quote->get_total( 'edit' ) );

			foreach ( dws_qrwc_get_quotable_order_items_types( $quote ) as $type ) {
				foreach ( $quote->get_items( $type ) as $order_item ) {
					$order_item = clone $order_item;
					$order_item->set_id( 0 );
					$order->add_item( $order_item );
				}
			}

			$order->add_order_note( sprintf( /* translators: %s: quote number */ __( 'Order created from quote #%s.', 'quote-requests-for-woocommerce' ), $quote->get_quote_number() ) );
			$order->update_meta_data( '_origin_quote_id', $quote->get_id() );
			$order->save();

			$quote->update_meta_data( '_accepted_order_id', $order->get_id() );
			$quote->save_meta_data();

			/**
			 * Triggered after creating a new order from an accepted quote.
			 *
			 * @since   1.0.0
			 * @version 1.0.0
			 */
			do_action( dws_qrwc_get_hook_tag( 'created_order_from_quote' ), $order, $quote );
		} catch ( WC_Data_Exception $exception ) {
			$order = new WP_Error( 'error', $exception->getMessage() );
		}
	}

	return $order;
}

/**
 * Returns the recognized order line item types. Used when copying over line items from the quote to the order.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote|null  $quote  Quote that the query is for. Null for default types.
 *
 * @return  array
 */
function dws_qrwc_get_quotable_order_items_types( ?DWS_Quote $quote = null ): array {
	return apply_filters(
		dws_qrwc_get_hook_tag( 'quote', 'order_items_types' ),
		array( 'line_item', 'fee', 'shipping', 'tax', 'coupon' ),
		$quote
	);
}

/**
 * Returns the list of statuses that will prevent further status changes once reached.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote|null  $quote          Quote that is being checked. Null for default statuses.
 * @param   bool            $include_wc     Whether to include the 'wc-' prefix or not.
 *
 * @return  array
 */
function dws_qrwc_get_quote_finalized_statuses( ?DWS_Quote $quote = null, bool $include_wc = false ): array {
	return array_map(
		function( string $status ) use ( $include_wc ) {
			return $include_wc ? Strings::maybe_prefix( $status, 'wc-' ) : Strings::maybe_unprefix( $status, 'wc-' );
		},
		apply_filters(
			dws_qrwc_get_hook_tag( 'quote', array( 'statuses', 'finalized' ) ),
			array( 'quote-expired', 'quote-accepted', 'quote-cancelled' ),
			$quote
		)
	);
}

/**
 * Returns the list of statuses that a quote can be edited by admins in.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote|null  $quote          Quote that is being checked. Null for default statuses.
 * @param   bool            $include_wc     Whether to include the 'wc-' prefix or not.
 *
 * @return  array
 */
function dws_qrwc_get_valid_quote_is_editable_statuses( ?DWS_Quote $quote = null, bool $include_wc = false ): array {
	return array_map(
		function( string $status ) use ( $include_wc ) {
			return $include_wc ? Strings::maybe_prefix( $status, 'wc-' ) : Strings::maybe_unprefix( $status, 'wc-' );
		},
		apply_filters(
			dws_qrwc_get_hook_tag( 'quote', array( 'statuses', 'for_edit' ) ),
			array( 'quote-request', 'quote-rejected', 'auto-draft' ),
			$quote
		)
	);
}

/**
 * Returns the list of statuses that a quote can be cancelled by the customer in.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote|null  $quote          Quote that is being checked. Null for default statuses.
 * @param   bool            $include_wc     Whether to include the 'wc-' prefix or not.
 *
 * @return  array
 */
function dws_qrwc_get_valid_quote_statuses_for_cancellation( ?DWS_Quote $quote = null, bool $include_wc = false ): array {
	return array_map(
		function( string $status ) use ( $include_wc ) {
			return $include_wc ? Strings::maybe_prefix( $status, 'wc-' ) : Strings::maybe_unprefix( $status, 'wc-' );
		},
		apply_filters(
			dws_qrwc_get_hook_tag( 'quote', array( 'statuses', 'for_cancellation' ) ),
			array( 'quote-request' ),
			$quote
		)
	);
}

/**
 * Cancels a given quote.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote       $quote              The quote object.
 * @param   bool            $customer_action    Whether the cancellation is being done by the customer or not.
 * @param   string|null     $note               Optional admin note to leave on the quote.
 *
 * @return  true|WP_Error
 */
function dws_qrwc_cancel_quote( DWS_Quote $quote, bool $customer_action = true, ?string $note = null ) {
	$can_cancel = true;

	if ( ! $quote->has_status( dws_qrwc_get_valid_quote_statuses_for_cancellation( $quote ) ) ) {
		$can_cancel = new WP_Error( 'dws_invalid_status', __( 'Cannot cancel quote because it\'s in an invalid status.', 'quote-requests-for-woocommerce' ) );
	}
	if ( is_wp_error( apply_filters( dws_qrwc_get_hook_tag( 'quote', array( 'actions', 'can_cancel' ) ), $can_cancel, $quote, $customer_action ) ) ) {
		return $can_cancel;
	}

	$note = $note ?? ( $customer_action ? __( 'Quote request cancelled by customer.', 'quote-requests-for-woocommerce' ) : __( 'Quote request cancelled on behalf of customer.', 'quote-requests-for-woocommerce' ) );

	$quote->update_status( 'quote-cancelled', $note, ! $customer_action );
	$quote->save();

	return true;
}

/**
 * Returns the list of statuses that a quote can be accepted by the customer in.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote|null  $quote          Quote that is being checked. Null for default statuses.
 * @param   bool            $include_wc     Whether to include the 'wc-' prefix or not.
 *
 * @return  array
 */
function dws_qrwc_get_valid_quote_statuses_for_acceptance( ?DWS_Quote $quote = null, bool $include_wc = false ): array {
	return array_map(
		function( string $status ) use ( $include_wc ) {
			return $include_wc ? Strings::maybe_prefix( $status, 'wc-' ) : Strings::maybe_unprefix( $status, 'wc-' );
		},
		apply_filters(
			dws_qrwc_get_hook_tag( 'quote', array( 'statuses', 'for_acceptance' ) ),
			array( 'quote-waiting' ),
			$quote
		)
	);
}

/**
 * Accepts a given quote.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote       $quote              The quote object.
 * @param   bool            $customer_action    Whether the cancellation is being done by the customer or not.
 * @param   string|null     $note               Optional admin note to leave on the quote.
 *
 * @return  WC_Order|WP_Error
 */
function dws_qrwc_accept_quote( DWS_Quote $quote, bool $customer_action = true, ?string $note = null ) {
	$can_accept = true;

	if ( ! $quote->has_status( dws_qrwc_get_valid_quote_statuses_for_acceptance( $quote ) ) ) {
		$can_accept = new WP_Error( 'dws_invalid_status', __( 'Cannot accept quote because it\'s in an invalid status.', 'quote-requests-for-woocommerce' ) );
	}
	if ( is_wp_error( apply_filters( dws_qrwc_get_hook_tag( 'quote', array( 'actions', 'can_accept' ) ), $can_accept, $quote, $customer_action ) ) ) {
		return $can_accept;
	}

	$order = dws_qrwc_create_order_from_quote( $quote );
	if ( is_wp_error( $order ) ) {
		return $order;
	}

	$note = $note ?? ( $customer_action ? __( 'Quote accepted by customer.', 'quote-requests-for-woocommerce' ) : __( 'Quote accepted on behalf of customer.', 'quote-requests-for-woocommerce' ) );

	$quote->set_status( 'quote-accepted', $note, ! $customer_action );
	$quote->save();

	return $order;
}

/**
 * Returns the list of statuses that a quote can be rejected by the customer in.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote|null  $quote          Quote that is being checked. Null for default statuses.
 * @param   bool            $include_wc     Whether to include the 'wc-' prefix or not.
 *
 * @return  array
 */
function dws_qrwc_get_valid_quote_statuses_for_rejection( ?DWS_Quote $quote = null, bool $include_wc = false ): array {
	return array_map(
		function( string $status ) use ( $include_wc ) {
			return $include_wc ? Strings::maybe_prefix( $status, 'wc-' ) : Strings::maybe_unprefix( $status, 'wc-' );
		},
		apply_filters(
			dws_qrwc_get_hook_tag( 'quote', array( 'statuses', 'for_rejection' ) ),
			array( 'quote-waiting' ),
			$quote
		)
	);
}

/**
 * Rejects a given quote.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote       $quote              The quote object.
 * @param   bool            $customer_action    Whether the rejection is being done by the customer or not.
 * @param   string|null     $note               Optional admin note to leave on the quote.
 *
 * @return  true|WP_Error
 */
function dws_qrwc_reject_quote( DWS_Quote $quote, bool $customer_action = true, ?string $note = null ) {
	$can_reject = true;

	if ( ! $quote->has_status( dws_qrwc_get_valid_quote_statuses_for_rejection( $quote ) ) ) {
		$can_reject = new WP_Error( 'dws_invalid_status', __( 'Cannot reject quote because it\'s in an invalid status.', 'quote-requests-for-woocommerce' ) );
	}
	if ( is_wp_error( apply_filters( dws_qrwc_get_hook_tag( 'quote', array( 'actions', 'can_reject' ) ), $can_reject, $quote, $customer_action ) ) ) {
		return $can_reject;
	}

	$note = $note ?? ( $customer_action ? __( 'Quote rejected by customer.', 'quote-requests-for-woocommerce' ) : __( 'Quote rejected on behalf of customer.', 'quote-requests-for-woocommerce' ) );

	$quote->set_status( 'quote-rejected', $note, ! $customer_action );
	$quote->save();

	return true;
}

/**
 * Returns an array of important dates related to a quote.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  array
 */
function dws_qrwc_get_quote_date_types(): array {
	return apply_filters(
		dws_qrwc_get_hook_tag( 'quote', 'date_types' ),
		array(
			'accepted'  => _x( 'Accepted Date', 'table heading', 'quote-requests-for-woocommerce' ),
			'rejected'  => _x( 'Rejected Date', 'table heading', 'quote-requests-for-woocommerce' ),
			'expired'   => _x( 'Expired Date', 'table heading', 'quote-requests-for-woocommerce' ),
			'cancelled' => _x( 'Cancelled Date', 'table heading', 'quote-requests-for-woocommerce' ),
		),
	);
}

/**
 * Checks whether a given date type is valid to be displayed on a given quote.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string      $date_type      One of the array values returned by @see dws_qrwc_get_quote_date_types().
 * @param   DWS_Quote   $quote          The quote object to investigate.
 *
 * @return  bool
 */
function dws_qrwc_should_display_quote_date_type( string $date_type, DWS_Quote $quote ): bool {
	$should_display = true;

	if ( 'accepted' === $date_type && ! $quote->has_status( 'quote-accepted' ) ) {
		$should_display = false;
	} elseif ( 'rejected' === $date_type && ! $quote->has_status( 'quote-rejected' ) ) {
		$should_display = false;
	} elseif ( 'expired' === $date_type && ! $quote->has_status( 'quote-expired' ) ) {
		$should_display = false;
	} elseif ( 'cancelled' === $date_type && ! $quote->has_status( 'quote-cancelled' ) ) {
		$should_display = false;
	}

	return apply_filters(
		dws_qrwc_get_hook_tag( 'quote', array( 'date_types', 'should_display' ) ),
		$should_display,
		$date_type,
		$quote
	);
}

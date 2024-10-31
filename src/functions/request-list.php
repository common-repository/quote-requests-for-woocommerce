<?php

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Booleans;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;

defined( 'ABSPATH' ) || exit;

/**
 * Returns the request list instance object.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  WC_Cart|null
 */
function dws_qrwc_get_request_list(): ?WC_Cart {
	$request_list = dws_qrwc_wc_cart_has_quote_request_items() ? WC()->cart : null;
	return apply_filters( dws_qrwc_get_hook_tag( 'get_customer_request_list' ), $request_list );
}

/**
 * Returns whether a given product can be added to quote requests lists by a given customer.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   int         $product_id     The ID of the product to check.
 * @param   int|null    $user_id        The ID of the user to check. Null for the current user.
 *
 * @return  bool|null
 */
function dws_qrwc_can_add_product_to_request_list( int $product_id, ?int $user_id = null ): ?bool {
	$is_valid_customer = dws_qrwc_is_valid_request_customer( $user_id, $product_id );
	if ( is_null( $is_valid_customer ) ) {
		$can_add_product = null;
	} elseif ( false === $is_valid_customer ) {
		$can_add_product = false;
	} else { // Customer IS valid.
		$product         = wc_get_product( $product_id );
		$can_add_product = apply_filters( dws_qrwc_get_hook_tag( 'customer_request_list', 'can_add_product' ), $product->is_purchasable() && $product->is_in_stock(), $product, $user_id );
	}

	return $can_add_product;
}

/**
 * Returns the text of the add-to-list action.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   int         $product_id     The ID of the product to check for.
 * @param   bool|null   $overridden     Whether the value has been overridden at the product-level or not.
 *
 * @return  string|null
 */
function dws_qrwc_get_add_to_request_list_text( int $product_id, ?bool &$overridden = null ): ?string {
	if ( empty( dws_qrwc_is_valid_request_product( $product_id ) ) ) {
		return null;
	}

	$add_to_list_text = dws_qrwc_get_validated_setting_maybe_merged_with_product_setting( 'add-to-list-text', 'request-lists', $product_id, $overridden );
	return apply_filters( dws_qrwc_get_hook_tag( 'customer_request_list', array( 'add_to_list', 'button_text' ) ), $add_to_list_text, $product_id, $overridden );
}

/**
 * Determines whether the WC cart contains any quote request items or not.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  bool|null
 */
function dws_qrwc_wc_cart_has_quote_request_items(): ?bool {
	$has_quote_items = null;

	if ( isset( WC()->cart ) ) {
		$has_quote_items = false;
		foreach ( WC()->cart->cart_contents as $cart_item ) {
			if ( Booleans::maybe_cast( $cart_item['dws_quote_request_item'] ?? false, false ) ) {
				$has_quote_items = true;
				break;
			}
		}
	}

	return $has_quote_items;
}

/**
 * Retrieves the add-to-list product ID from the PHP input data.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  int|null
 */
function dws_qrwc_get_add_to_request_list_product_id_from_input(): ?int {
	$product_id   = Integers::maybe_cast_input( INPUT_POST, 'add-to-qrwc-list' );
	$product_id ??= Integers::maybe_cast_input( INPUT_POST, 'qrwc_product_id' ); // for AJAX requests
	$product_id ??= Integers::maybe_cast_input( INPUT_GET, 'add-to-qrwc-list' );

	return apply_filters( dws_qrwc_get_hook_tag( 'customer_request_list', array( 'add_to_list', 'input_product_id' ) ), $product_id );
}

/**
 * Returns the list of supported requests list message placeholders.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   WC_Product|null     $product        Product to prepare the placeholders for. If no product is provided, just the placeholder keys are returned.
 * @param   string              $message_id     The message that the placeholders are for.
 *
 * @return  array
 */
function dws_qrwc_get_request_list_messages_placeholders( ?WC_Product $product = null, string $message_id = '' ): array {
	if ( is_null( $product ) ) {
		$placeholders = array( '{product_name}', '{product_name_formatted}', '{product_url}' );
	} else {
		$placeholders = array(
			'{product_name}'           => $product->get_name(),
			'{product_name_formatted}' => $product->get_formatted_name(),
			'{product_url}'            => $product->get_permalink(),
		);
	}

	return apply_filters( dws_qrwc_get_hook_tag( 'request_list_messages', 'placeholders' ), $placeholders, $product, $message_id );
}

/**
 * Returns a formatted message to be displayed to the customer.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $message_id     Internal ID of the message.
 * @param   int     $product_id     The ID of the product the message is for.
 *
 * @return  string
 */
function dws_qrwc_get_request_list_message( string $message_id, int $product_id ): ?string {
	$product = wc_get_product( $product_id );
	if ( empty( $product ) ) {
		return null;
	}

	$message = apply_filters( dws_qrwc_get_hook_tag( 'request_list_messages', 'raw_message' ), dws_qrwc_get_validated_setting( $message_id, 'request-list-messages' ), $message_id, $product_id );
	$message = Strings::replace_placeholders( $message ?? '', dws_qrwc_get_request_list_messages_placeholders( $product, $message_id ) );

	$message = apply_filters( dws_qrwc_get_hook_tag( 'request_list_messages', 'message' ), $message, $message_id, $product );
	return apply_filters( dws_qrwc_get_hook_tag( 'request_list_messages', array( 'message', $message_id ) ), $message, $product );
}

/**
 * Wrapper around WC's 'wc_add_notice' function for displaying requests list messages.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $message_id     The ID of the message to display.
 * @param   int     $product_id     The ID of the product that the message is related to.
 * @param   string  $notice_type    The type of notice to display.
 * @param   array   $data           Optional data to pass on to the notice.
 */
function dws_qrwc_wc_add_request_list_notice( string $message_id, int $product_id, string $notice_type = 'success', array $data = array() ) {
	$message = dws_qrwc_get_request_list_message( $message_id, $product_id );
	if ( ! empty( $message ) ) {
		$notice_type = apply_filters( dws_qrwc_get_hook_tag( 'request_list_messages', 'notice_type' ), $notice_type, $message_id, $product_id );
		$notice_type = apply_filters( dws_qrwc_get_hook_tag( 'request_list_messages', array( 'notice_type', $message_id ) ), $notice_type, $product_id );

		wc_add_notice( $message, $notice_type, $data );
	}
}

/**
 * Returns the translations to use on the cart page when using it as a quote list.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 *
 * @param   string  $text   The text to translate.
 *
 * @return  string|null
 */
function dws_qrwc_get_wc_cart_list_translation( string $text ): ?string {
	$translation = null;

	switch ( $text ) {
		case 'You cannot add another "%s" to your cart.':
			/* translators: %s: product name */
			$translation = __( 'You cannot add another "%s" to your quote request list.', 'quote-requests-for-woocommerce' );
			break;
		case 'You cannot add &quot;%s&quot; to the cart because the product is out of stock.':
			/* translators: %s: product name */
			$translation = __( 'You cannot add &quot;%s&quot; to your quote request list because the product is out of stock.', 'quote-requests-for-woocommerce' );
			break;
		case 'You cannot add that amount of &quot;%1$s&quot; to the cart because there is not enough stock (%2$s remaining).':
			/* translators: %1$s: product name, %2$s: stock amount remaining */
			$translation = __( 'You cannot add that amount of &quot;%1$s&quot; to your quote request list because there is not enough stock (%2$s remaining).', 'quote-requests-for-woocommerce' );
			break;
		case 'You cannot add that amount to the cart &mdash; we have %1$s in stock and you already have %2$s in your cart.':
			/* translators: %1$s: amount in stock, %2$s: amount in list */
			$translation = __( 'You cannot add that amount to your quote request list &mdash; we have %1$s in stock and you already have %2$s in your list.', 'quote-requests-for-woocommerce' );
			break;
		case 'View cart':
			$translation = __( 'View quote request', 'quote-requests-for-woocommerce' );
			break;
		case 'Price':
			$translation = __( 'Projected price', 'quote-requests-for-woocommerce' );
			break;
		case 'Update cart':
			$translation = __( 'Update quote request list', 'quote-requests-for-woocommerce' );
			break;
		case 'Cart updated.':
			$translation = __( 'Quote request list updated.', 'quote-requests-for-woocommerce' );
			break;
		case 'Subtotal':
			$translation = __( 'Projected subtotal', 'quote-requests-for-woocommerce' );
			break;
		case 'Subtotal:':
			$translation = __( 'Projected subtotal:', 'quote-requests-for-woocommerce' );
			break;
		case 'Total':
			$translation = __( 'Projected total', 'quote-requests-for-woocommerce' );
			break;
		case 'Cart totals':
			$translation = __( 'Quote request totals', 'quote-requests-for-woocommerce' );
			break;
		case 'Proceed to checkout':
			$translation = __( 'Proceed to submit quote request', 'quote-requests-for-woocommerce' );
			break;
	}

	return apply_filters( dws_qrwc_get_hook_tag( 'customer_request_list', array( 'translation', 'wc_cart' ) ), $translation, $text );
}

/**
 * Returns the ntranslations to use on the cart page when using it as a quote list.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $single_text    The text to be used if the number is singular.
 * @param   int     $number         The number to compare against to use either the singular or plural form.
 *
 * @return  string|null
 */
function dws_qrwc_get_wc_cart_list_ntranslation( string $single_text, int $number ): ?string {
	$translation = null;

	switch ( $single_text ) {
		case '%s has been added to your cart.':
			/* translators: product name */
			$translation = _n( '%s has been added to your quote request list.', '%s have been added to your quote request list.', $number, 'quote-requests-for-woocommerce' );
			break;
	}

	return apply_filters( dws_qrwc_get_hook_tag( 'customer_request_list', array( 'ntranslation', 'wc_cart' ) ), $translation, $single_text, $number );
}

/**
 * Returns the translations to use on the checkout page when using the cart as a quote list.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $text   The text to translate.
 *
 * @return  string|null
 */
function dws_qrwc_get_wc_cart_list_checkout_translation( string $text ): ?string {
	$translation = null;

	switch ( $text ) {
		case 'Your order':
			$translation = __( 'Your quote request', 'quote-requests-for-woocommerce' );
			break;
		case 'Place order':
		case 'Checkout':
			$translation = __( 'Submit quote request', 'quote-requests-for-woocommerce' );
			break;
	}

	return apply_filters( dws_qrwc_get_hook_tag( 'customer_request_list', array( 'translation', 'wc_checkout' ) ), $translation, $text );
}

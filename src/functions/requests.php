<?php

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\Exceptions\NotFoundException;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Booleans;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Users;

defined( 'ABSPATH' ) || exit;

/**
 * Returns whether quote requests from customers are enabled or not.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  bool
 */
function dws_qrwc_are_requests_enabled(): bool {
	return dws_qrwc_get_validated_setting( 'enabled', 'requests' );
}

/**
 * Returns which product types support customer requests.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string[]
 */
function dws_qrwc_get_supported_request_product_types(): array {
	return apply_filters( dws_qrwc_get_hook_tag( 'customer_request', 'supported_product_types' ), array( 'simple', 'variable' ) );
}

/**
 * Checks whether a given product is a valid quote request product.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   int     $product_id     The product ID.
 *
 * @return  bool|null
 */
function dws_qrwc_is_supported_request_product( int $product_id ): ?bool {
	$product_type = WC_Product_Factory::get_product_type( $product_id );
	if ( false === $product_type ) {
		return null;
	}

	return apply_filters( dws_qrwc_get_hook_tag( 'customer_request', 'is_supported_product' ), in_array( $product_type, dws_qrwc_get_supported_request_product_types(), true ), $product_id );
}

/**
 * Returns whether the given product can be added to quote requests.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   int         $product_id     The ID of the product to check for.
 * @param   bool|null   $overridden     Whether the decision was made globally or at the product level.
 *
 * @return  bool|null
 */
function dws_qrwc_is_valid_request_product( int $product_id, ?bool &$overridden = null ): ?bool {
	if ( false === dws_qrwc_are_requests_enabled() || true !== dws_qrwc_is_supported_request_product( $product_id ) ) {
		return null;
	}

	$cache_key        = "customer_requests/is_valid_product/$product_id";
	$is_valid_product = dws_qrwc_get_cache_value( $cache_key );

	if ( $is_valid_product instanceof NotFoundException ) {
		switch ( dws_qrwc_get_validated_product_setting( 'is-valid-product', $product_id, 'general' ) ) {
			case 'yes':
				$overridden       = true;
				$is_valid_product = true;
				break;
			case 'no':
				$overridden       = true;
				$is_valid_product = false;
				break;
			default: // case 'global'
				$overridden = false;

				$valid_products = dws_qrwc_get_validated_setting( 'valid-products', 'requests' );
				switch ( $valid_products ) {
					case 'all':
						$is_valid_product = true;
						break;
					case 'categories':
						$categories       = dws_qrwc_get_validated_setting( 'valid-products-categories', 'requests' );
						$is_valid_product = has_term( $categories, 'product_cat', $product_id );
						break;
					case 'tags':
						$tags             = dws_qrwc_get_validated_setting( 'valid-products-tags', 'requests' );
						$is_valid_product = has_term( $tags, 'product_tag', $product_id );
						break;
					default:
						$is_valid_product = apply_filters( dws_qrwc_get_hook_tag( 'customer_request', array( 'is_valid_product', $valid_products ) ), false, $product_id, $overridden );
						$is_valid_product = Booleans::maybe_cast( $is_valid_product, false );
				}
		}

		dws_qrwc_set_cache_value( $cache_key, $is_valid_product );
		dws_qrwc_set_cache_value( "$cache_key/overridden", $overridden );
	} else {
		$overridden = dws_qrwc_get_cache_value( "$cache_key/overridden" );
	}

	return apply_filters( dws_qrwc_get_hook_tag( 'customer_request', 'is_valid_product' ), $is_valid_product, $product_id, $overridden );
}

/**
 * Returns whether a given user is allowed to submit quote requests. Optionally, for a given product too.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 *
 * @param   int|null    $user_id        The ID of the user to check for. Null for the current user.
 * @param   int|null    $product_id     The ID of the product to check for. Null for the global settings.
 * @param   bool|null   $overridden     Whether the decision was made globally or at the product level.
 *
 * @return bool|null
 */
function dws_qrwc_is_valid_request_customer( ?int $user_id = null, ?int $product_id = null, ?bool &$overridden = null ): ?bool {
	if ( false === dws_qrwc_are_requests_enabled() || ( ! is_null( $product_id ) && empty( dws_qrwc_is_valid_request_product( $product_id ) ) ) ) {
		return null;
	}

	$user = Users::get( $user_id );
	if ( empty( $user ) ) {
		return null;
	}

	$cache_key         = "customer_requests/is_valid_customer/$user->ID" . ( is_null( $product_id ) ? '' : "/$product_id" );
	$is_valid_customer = dws_qrwc_get_cache_value( $cache_key );

	if ( $is_valid_customer instanceof NotFoundException ) {
		$valid_customers = dws_qrwc_get_validated_setting_maybe_merged_with_product_setting( 'valid-customers', 'requests', $product_id ?? 0, $overridden );
		switch ( $valid_customers ) {
			case 'all':
				$is_valid_customer = true;
				break;
			case 'logged-out':
				$is_valid_customer = ( false === $user->exists() );
				break;
			case 'logged-in':
				$is_valid_customer = ( true === $user->exists() );
				break;
			default:
				$is_valid_customer = apply_filters( dws_qrwc_get_hook_tag( 'customer_request', array( 'is_valid_customer', $valid_customers ) ), false, $user->ID, $product_id, $overridden );
				$is_valid_customer = Booleans::maybe_cast( $is_valid_customer, false );
		}

		dws_qrwc_set_cache_value( $cache_key, $is_valid_customer );
		dws_qrwc_set_cache_value( "$cache_key/overridden", $overridden );
	} else {
		$overridden = dws_qrwc_get_cache_value( "$cache_key/overridden" );
	}

	return apply_filters( dws_qrwc_get_hook_tag( 'customer_request', 'is_valid_customer' ), $is_valid_customer, $user->ID, $product_id, $overridden );
}

/**
 * Returns whether shipping fields for customer quote requests should be disabled or not.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  bool
 */
function dws_qrwc_should_disable_request_shipping_fields(): bool {
	$disable_fields = dws_qrwc_get_validated_setting( 'disable-shipping-fields', 'requests' );
	return apply_filters( dws_qrwc_get_hook_tag( 'customer_request', array( 'should_disable', 'shipping_fields' ) ), $disable_fields );
}

/**
 * Returns the 'price subject to change' disclaimer text.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $context    The context that the message will be displayed in. Used to filter a different message.
 *
 * @return  string
 */
function dws_qrwc_get_price_subject_to_change_request_message( string $context ): string {
	$message = dws_qrwc_get_validated_setting( 'price-subject-to-change', 'request-messages' );
	return apply_filters( dws_qrwc_get_hook_tag( 'customer_request_messages', array( 'message', 'price-subject-to-change' ) ), $message, $context );
}

/**
 * Returns whether the 'price subject to change' disclaimer should be outputted for a given quote.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   DWS_Quote   $quote  The quote object.
 *
 * @return  bool
 */
function dws_qrwc_should_display_request_price_disclaimer( DWS_Quote $quote ): bool {
	return apply_filters( dws_qrwc_get_hook_tag( 'customer_request', array( 'should_display', 'price_disclaimer' ) ), $quote->is_editable(), $quote );
}

/**
 * Returns the translations to use on the order received endpoint when used to display a quote request.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string          $text   The text to translate.
 * @param   DWS_Quote|null  $quote  Optional quote object for the filter.
 *
 * @return  string|null
 */
function dws_qrwc_get_wc_order_received_translation( string $text, ?DWS_Quote $quote = null ): ?string {
	$translation = null;

	switch ( $text ) {
		case 'Order received':
			$translation = __( 'Quote request received', 'quote-requests-for-woocommerce' );
			break;
		case 'Thank you. Your order has been received.':
			$translation = __( 'Thank you. Your quote request has been received.', 'quote-requests-for-woocommerce' );
			break;
		case 'Order number:':
			$translation = __( 'Quote request number:', 'quote-requests-for-woocommerce' );
			break;
		case 'Order details':
			$translation = __( 'Quote request details', 'quote-requests-for-woocommerce' );
			break;
	}

	return apply_filters( dws_qrwc_get_hook_tag( 'customer_request', array( 'translation', 'wc_order_received' ) ), $translation, $text, $quote );
}

/**
 * Returns the translations to use inside transactional WC emails when used to inform about a quote request.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string          $text   The text to translate.
 * @param   DWS_Quote|null  $quote  Optional quote object for the filter.
 *
 * @return  string|null
 */
function dws_qrwc_get_wc_order_email_translation( string $text, ?DWS_Quote $quote = null ): ?string {
	$translation = null;

	switch ( $text ) {
		case '[Order #%s]':
			/* translators: %s: quote request number */
			$translation = \__( '[Quote Request #%s]', 'quote-requests-for-woocommerce' );
			break;
	}

	return apply_filters( dws_qrwc_get_hook_tag( 'customer_request', array( 'translation', 'wc_email' ) ), $translation, $text, $quote );
}

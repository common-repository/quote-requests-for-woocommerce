<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\RequestLists\CartList;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles the creation of the quote request based on cart contents.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class CheckoutCartList extends AbstractPluginFunctionality {
	// region TRAITS

	use SetupHooksTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( 'the_title', $this, 'maybe_filter_checkout_page_title', 99, 2 );
		$hooks_service->add_filter( 'gettext_woocommerce', $this, 'maybe_filter_translations', 99, 2 );

		$hooks_service->add_filter( 'woocommerce_cart_needs_payment', $this, 'maybe_disable_payment', 999 );
		$hooks_service->add_filter( 'woocommerce_cart_needs_shipping_address', $this, 'maybe_disable_shipping_fields', 99 );

		$hooks_service->add_filter( 'woocommerce_new_order_data', $this, 'maybe_filter_new_order_type', 999 );
	}

	// endregion

	// region HOOKS

	/**
	 * Adjust the names of the checkout page when they are used for quote request products.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $title      The current page's title.
	 * @param   int     $post_id    The ID of the post that the title is for.
	 *
	 * @return  string
	 */
	public function maybe_filter_checkout_page_title( string $title, int $post_id ): string {
		if ( true === dws_qrwc_wc_cart_has_quote_request_items() && \wc_get_page_id( 'checkout' ) === $post_id && ! \is_checkout_pay_page() ) {
			$title = \__( 'Submit quote request', 'quote-requests-for-woocommerce' );
		}

		return $title;
	}

	/**
	 * Replace certain WC translations on the checkout page when using the cart as a request list.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 *
	 * @param   string  $translation    Translated text.
	 * @param   string  $text           Original text.
	 *
	 * @return  string
	 */
	public function maybe_filter_translations( string $translation, string $text ): string {
		if ( true === dws_qrwc_wc_cart_has_quote_request_items() ) {
			$translation = dws_qrwc_get_wc_cart_list_checkout_translation( $text ) ?? $translation;
		}

		return $translation;
	}

	/**
	 * Removes the payment gateways from checkout if the cart contains quote request items.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $needs_payment      Whether the cart contents require payment or not.
	 *
	 * @return  bool
	 */
	public function maybe_disable_payment( bool $needs_payment ): bool {
		if ( true === dws_qrwc_wc_cart_has_quote_request_items() ) {
			$needs_payment = false;
		}

		return $needs_payment;
	}

	/**
	 * Attempts to disable the checkout shipping fields by filtering the cart content's 'needs shipping' value.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $needs_shipping     Whether the cart contents require shipping, and thus a shipping address.
	 *
	 * @return  bool
	 */
	public function maybe_disable_shipping_fields( bool $needs_shipping ): bool {
		if ( true === dws_qrwc_should_disable_request_shipping_fields() && true === dws_qrwc_wc_cart_has_quote_request_items() ) {
			$needs_shipping = false;
		}

		return $needs_shipping;
	}

	/**
	 * Saves new orders as quotes in the database if the cart contains quote request items.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $order_data     Data passed on to wp_insert_post.
	 *
	 * @return  array
	 */
	public function maybe_filter_new_order_type( array $order_data ): array {
		if ( true === dws_qrwc_wc_cart_has_quote_request_items() && \is_checkout() && ! \is_checkout_pay_page() ) {
			$order_data['post_type'] = 'dws_shop_quote';
		}

		return $order_data;
	}

	// endregion
}

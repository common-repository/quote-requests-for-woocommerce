<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\RequestLists\CartList;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\States\Activeable\ActiveLocalTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Booleans;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Request;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles the hiding of quote product prices on the cart and checkout pages.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class HideItemPricesCartList extends AbstractPluginFunctionality {
	// region TRAITS

	use ActiveLocalTrait;
	use SetupHooksTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function is_active_local(): bool {
		return false === Request::is_type( 'admin' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( 'woocommerce_widget_cart_item_quantity', $this, 'maybe_hide_widget_cart_item_price', 99, 2 );
		$hooks_service->add_action( 'woocommerce_widget_shopping_cart_total', $this, 'maybe_hide_cart_widget_subtotal', 0, 2 ); // priority must be lower than 10
		$hooks_service->add_action( 'woocommerce_widget_shopping_cart_total', $this, 'maybe_output_cart_widget_subtotal', 10, 2 ); // same priority as default WC

		$hooks_service->add_filter( 'woocommerce_cart_item_price', $this, 'maybe_hide_cart_item_price', 99, 2 );
		$hooks_service->add_filter( 'woocommerce_cart_item_subtotal', $this, 'maybe_hide_cart_item_price', 99, 2 );

		$hooks_service->add_filter( 'woocommerce_cart_subtotal', $this, 'maybe_hide_cart_totals_html', 99 );
		$hooks_service->add_filter( 'woocommerce_cart_totals_fee_html', $this, 'maybe_hide_cart_totals_html', 99 );
		$hooks_service->add_filter( 'woocommerce_cart_totals_taxes_total_html', $this, 'maybe_hide_cart_totals_html', 99 );
		$hooks_service->add_filter( 'woocommerce_cart_totals_order_total_html', $this, 'maybe_hide_cart_totals_html', 99 );

		$hooks_service->add_action( 'woocommerce_before_cart', $this, 'maybe_hide_price_columns_and_totals' );
		$hooks_service->add_filter( dws_qrwc_get_component_hook_tag( 'cart-request-list-price-disclaimer', 'should_output' ), $this, 'maybe_prevent_price_disclaimer_output' );
	}

	// endregion

	// region HOOKS

	/**
	 * Replaces the default cart widget quantity field in order to hide the product price.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $quantity   Cart widget formatted quantity.
	 * @param   array   $cart_item  The cart item data.
	 *
	 * @return  string
	 */
	public function maybe_hide_widget_cart_item_price( string $quantity, array $cart_item ): string {
		if ( Booleans::maybe_cast( $cart_item['dws_quote_request_item'] ?? false, false ) && true === dws_qrwc_is_request_product_price_hidden( $cart_item['product_id'] ) ) {
			$quantity = \sprintf( /* translators: %s: quantity */ \__( 'Quantity: %s', 'quote-requests-for-woocommerce' ), $cart_item['quantity'] );
			$quantity = '<span class="quantity">' . $quantity . '</span>';
		}

		return $quantity;
	}

	/**
	 * If at least one of the quote cart items has a hidden price, remove displaying the subtotal in the cart widget.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function maybe_hide_cart_widget_subtotal() {
		if ( true === dws_qrwc_request_list_has_hidden_price_items() ) {
			\remove_action( 'woocommerce_widget_shopping_cart_total', 'woocommerce_widget_shopping_cart_subtotal' );
		}
	}

	/**
	 * If the cart has quote items with hidden prices, output a more descriptive message in lieu of the standard
	 * subtotal amount.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function maybe_output_cart_widget_subtotal() {
		if ( isset( \WC()->cart ) && true === dws_qrwc_request_list_has_hidden_price_items() ) {
			if ( false === \has_action( 'woocommerce_widget_shopping_cart_total', 'woocommerce_widget_shopping_cart_subtotal' ) ) {
				echo '<strong>' . \esc_html_x( 'Subtotal:', 'cart widget', 'quote-requests-for-woocommerce' ) . '</strong><br/>';
				echo \wp_kses_post( dws_qrwc_get_hidden_product_price_request_message( 'cart widget' ) );
			}
		}
	}

	/**
	 * Maybe hide the price in the cart.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $price      Formatted price.
	 * @param   array   $cart_item  The cart item data.
	 *
	 * @return  string
	 */
	public function maybe_hide_cart_item_price( string $price, array $cart_item ): string {
		if ( Booleans::maybe_cast( $cart_item['dws_quote_request_item'] ?? false, false ) && true === dws_qrwc_is_request_product_price_hidden( $cart_item['product_id'] ) ) {
			if ( \is_cart() ) {
				$text = dws_qrwc_get_hidden_product_price_request_message( 'cart' );
			} elseif ( \is_checkout() ) {
				$text = dws_qrwc_get_hidden_product_price_request_message( 'checkout' );
			} else {
				$context = \apply_filters( $this->get_hook_tag( 'context' ), 'other' );
				$text    = dws_qrwc_get_hidden_product_price_request_message( $context );
			}

			$price = \wp_kses_post( $text );
		}

		return $price;
	}

	/**
	 * If the cart contains quote products with hidden prices, replaces the price HTML with a notice about when the
	 * price will become available.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $html   The current HTML to output.
	 *
	 * @return  string
	 */
	public function maybe_hide_cart_totals_html( string $html ): string {
		if ( true === dws_qrwc_request_list_has_hidden_price_items() ) {
			$html = \wp_kses_post( dws_qrwc_get_hidden_product_price_request_message( 'cart totals' ) );
			if ( \doing_filter( 'woocommerce_cart_subtotal' ) && ! ( \is_cart() || \is_checkout() ) ) {
				$html = '';
			}
		}

		return $html;
	}

	/**
	 * If the cart contains exclusively quote request items with hidden prices, then this CSS should remove the price
	 * and subtotal columns, as well as all the cart totals. They don't make sense in this scenario.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function maybe_hide_price_columns_and_totals() {
		if ( false === dws_qrwc_request_list_has_visible_price_request() ) {
			?>

			<!--suppress CssUnusedSymbol -->
			<style type="text/css">
				th.product-price, th.product-subtotal,
				td.product-price, td.product-subtotal {
					display: none !important;
				}

				th.product-name, td.product-name {
					width: 99%;
				}

				.cart_totals h2,
				.cart_totals table {
					display: none !important;
				}
			</style>

			<?php
		}
	}

	/**
	 * If all the products in the cart have hidden prices, do not display the price disclaimer since it makes no sense.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $should_output      Whether the price change disclaimer should be outputted or not.
	 *
	 * @return  bool
	 */
	public function maybe_prevent_price_disclaimer_output( bool $should_output ): bool {
		if ( true === $should_output ) {
			$should_output = Booleans::maybe_cast( dws_qrwc_request_list_has_visible_price_request(), false );
		}

		return $should_output;
	}

	// endregion
}

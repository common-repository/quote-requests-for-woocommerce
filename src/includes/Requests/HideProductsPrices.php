<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Requests;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Booleans;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Request;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles the hiding of request product prices.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class HideProductsPrices extends AbstractPluginFunctionality {
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
		$hooks_service->add_action( 'woocommerce_new_order_item', $this, 'store_hidden_status_as_meta', 10, 3 );
		$hooks_service->add_filter( 'woocommerce_hidden_order_itemmeta', $this, 'filter_hidden_order_item_meta_keys' );

		if ( true !== Request::is_type( 'admin' ) ) {
			$hooks_service->add_action( 'woocommerce_before_shop_loop_item_title', $this, 'maybe_remove_loop_sale_flash', 0 ); // priority must be lower than 10
			$hooks_service->add_action( 'woocommerce_before_single_product_summary', $this, 'maybe_remove_single_product_sale_flash', 0 ); // priority must be lower than 10

			$hooks_service->add_filter( 'woocommerce_get_price_html', $this, 'maybe_hide_product_price_html', 99, 2 );
			$hooks_service->add_filter( 'woocommerce_variable_price_html', $this, 'maybe_hide_product_price_html', 99, 2 );
			$hooks_service->add_filter( 'woocommerce_variable_sale_price_html', $this, 'maybe_hide_product_price_html', 99, 2 );

			$hooks_service->add_filter( 'woocommerce_order_formatted_line_subtotal', $this, 'maybe_hide_quote_formatted_line_subtotal', 999, 3 );
			$hooks_service->add_filter( 'woocommerce_get_order_item_totals', $this, 'maybe_hide_quote_item_totals_rows', 999, 2 );
			$hooks_service->add_filter( 'woocommerce_get_formatted_order_total', $this, 'maybe_hide_quote_formatted_total', 999, 2 );
			$hooks_service->add_filter( dws_qrwc_get_hook_tag( 'account', array( 'quotes_list', 'columns', 'quote_total' ) ), $this, 'maybe_hide_quote_formatted_total', 999, 2 );

			$hooks_service->add_filter( dws_qrwc_get_component_hook_tag( 'requests-price-disclaimer', 'should_output' ), $this, 'maybe_prevent_price_disclaimer_output', 10, 2 );
		}
	}

	// endregion

	// region HOOKS

	/**
	 * Stores the information about whether the product price is hidden on the line item itself to prevent issues if
	 * the product is trashed.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param   int             $item_id    The ID of the order item.
	 * @param   \WC_Order_Item  $item       The newly created order item object.
	 * @param   int             $order_id   The ID of the order that the item belongs to.
	 */
	public function store_hidden_status_as_meta( int $item_id, \WC_Order_Item $item, int $order_id ) {
		if ( $item instanceof \WC_Order_Item_Product && true === dws_qrwc_is_quote( $order_id ) ) {
			$has_hidden_price = dws_qrwc_is_request_product_price_hidden( $item->get_variation_id() ?: $item->get_product_id() );
			$item->update_meta_data( '_dws_qrwc_hide_price', Booleans::to_string( $has_hidden_price ?? false ) );
			$item->save_meta_data();
		}
	}

	/**
	 * Hides our own meta key when viewing the quote as an admin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $hidden_meta    The meta keys to hide.
	 *
	 * @return  array
	 */
	public function filter_hidden_order_item_meta_keys( array $hidden_meta ): array {
		$hidden_meta[] = '_dws_qrwc_hide_price';
		return $hidden_meta;
	}

	/**
	 * Sale flashes don't make sense when the price is hidden, so we try to remove them in product loops.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function maybe_remove_loop_sale_flash() {
		global $product;

		if ( true === dws_qrwc_is_request_product_price_hidden( $product->get_id() ) ) {
			\remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash' );
			\remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 6 ); // StoreFront theme
		}
	}

	/**
	 * Sale flashes don't make sense when the price is hidden, so we try to remove them on product pages.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function maybe_remove_single_product_sale_flash() {
		global $product;

		if ( true === dws_qrwc_is_request_product_price_hidden( $product->get_id() ) ) {
			\remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash' );
		}
	}

	/**
	 * Maybe removes the product's price from the product page and products grid.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string          $price      Price to be displayed.
	 * @param   \WC_Product     $product    The product that the price belongs to.
	 *
	 * @return   string
	 */
	public function maybe_hide_product_price_html( string $price, \WC_Product $product ): string {
		if ( true === dws_qrwc_is_request_product_price_hidden( $product->get_parent_id() ?: $product->get_id() ) ) {
			$price = '';
		}

		return $price;
	}

	/**
	 * If the quote request does not have visible prices yet, hide the subtotals for products with hidden prices.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string          $formatted_subtotal     The formatted subtotal HTML output.
	 * @param   \WC_Order_Item  $item                   The order item that the subtotal is for.
	 * @param   \WC_Order       $order                  The order object.
	 *
	 * @return  string
	 */
	public function maybe_hide_quote_formatted_line_subtotal( string $formatted_subtotal, \WC_Order_Item $item, \WC_Order $order ): string {
		if ( $item instanceof \WC_Order_Item_Product && true === dws_qrwc_is_quote( $order ) && true !== dws_qrwc_quote_has_visible_hidden_prices( $order ) ) {
			if ( false !== dws_qrwc_is_request_product_item_price_hidden( $item ) ) {
				$message            = $order->has_status( 'quote-cancelled' ) ? '&mdash;' : dws_qrwc_get_hidden_product_price_request_message( 'line item subtotal' );
				$formatted_subtotal = \wp_kses_post( $message );
			}
		}

		return $formatted_subtotal;
	}

	/**
	 * If the quote request does not have visible prices yet, hide all other totals if there are any products with hidden prices.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array       $total_rows     All the totals rows to output.
	 * @param   \WC_Order   $order          The order object.
	 *
	 * @return  array
	 */
	public function maybe_hide_quote_item_totals_rows( array $total_rows, \WC_Order $order ): array {
		if ( true === dws_qrwc_is_quote( $order ) && false !== dws_qrwc_request_has_hidden_price_product_items( $order ) ) {
			$total_rows = array(
				'order_total' => $total_rows['order_total'],
			);
		}

		return $total_rows;
	}

	/**
	 * If the quote request does not have visible prices yet, hide the total if there are any products with hidden prices.
	 *
	 * @param   string      $formatted_total    The formatted total HTML output.
	 * @param   \WC_Order   $order              The order object.
	 *
	 * @return  string
	 */
	public function maybe_hide_quote_formatted_total( string $formatted_total, \WC_Order $order ): string {
		if ( true === dws_qrwc_is_quote( $order ) && false !== dws_qrwc_request_has_hidden_price_product_items( $order ) ) {
			$message         = $order->has_status( 'quote-cancelled' ) ? '&mdash;' : dws_qrwc_get_hidden_product_price_request_message( 'quote total' );
			$formatted_total = \wp_kses_post( $message );
		}

		return $formatted_total;
	}

	/**
	 * If all the products in the quote request have hidden prices, do not display the price disclaimer since it makes no sense.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool        $should_output      Whether the price change disclaimer should be outputted or not.
	 * @param   \DWS_Quote  $quote              The quote object being decided on.
	 *
	 * @return  bool
	 */
	public function maybe_prevent_price_disclaimer_output( bool $should_output, \DWS_Quote $quote ): bool {
		if ( true === $should_output ) {
			$should_output = Booleans::maybe_cast( dws_qrwc_request_has_visible_price_product_items( $quote ), false );
		}

		return $should_output;
	}

	// endregion
}

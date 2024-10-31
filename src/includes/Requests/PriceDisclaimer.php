<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Requests;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\States\Activeable\ActiveLocalTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Request;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles the output of a disclaimer about prices being subject to change when viewing a quote request on the frontend and emails.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class PriceDisclaimer extends AbstractPluginFunctionality {
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
		return false === Request::is_type( 'admin' )
			&& false === empty( dws_qrwc_get_price_subject_to_change_request_message( 'quote' ) );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( 'woocommerce_order_formatted_line_subtotal', $this, 'maybe_append_quote_formatted_line_subtotal_asterisk', 999, 3 );
		$hooks_service->add_filter( 'woocommerce_get_order_item_totals', $this, 'maybe_append_quote_item_totals_asterisk', 999, 2 );
		$hooks_service->add_filter( 'woocommerce_get_formatted_order_total', $this, 'maybe_append_quote_formatted_total_asterisk', 999, 2 );

		$hooks_service->add_action( 'woocommerce_email_after_order_table', $this, 'maybe_output_price_disclaimer' );
		$hooks_service->add_action( dws_qrwc_get_hook_tag( 'quote_details', 'after_quote_table' ), $this, 'maybe_output_price_disclaimer' );
	}

	// endregion

	// region HOOKS

	/**
	 * Maybe appends an asterisk to quote line item prices in order to point to them being subject to change.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string          $formatted_subtotal     Formatted price.
	 * @param   \WC_Order_Item  $item                   The order item that the subtotal is for.
	 * @param   \WC_Order       $order                  The order object.
	 *
	 * @return  string
	 */
	public function maybe_append_quote_formatted_line_subtotal_asterisk( string $formatted_subtotal, \WC_Order_Item $item, \WC_Order $order ): string {
		if ( $item instanceof \WC_Order_Item_Product && true === dws_qrwc_is_quote( $order ) && true === dws_qrwc_should_display_request_price_disclaimer( $order ) ) {
			$formatted_subtotal .= ' <span class="dws-qrwc-price-disclaimer-asterisk">*</span>';
		}

		return $formatted_subtotal;
	}

	/**
	 * Maybe appends an asterisk to quotes totals in order to point to them being subject to change.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array           $total_rows     All the totals rows to output.
	 * @param   \WC_Order       $order          The order object.
	 *
	 * @return  array
	 */
	public function maybe_append_quote_item_totals_asterisk( array $total_rows, \WC_Order $order ): array {
		if ( true === dws_qrwc_is_quote( $order ) && true === dws_qrwc_should_display_request_price_disclaimer( $order ) ) {
			foreach ( $total_rows as $key => &$total ) {
				if ( 'order_total' !== $key ) {
					$total['value'] .= ' <span class="dws-qrwc-price-disclaimer-asterisk">*</span>';
				}
			}
		}

		return $total_rows;
	}

	/**
	 * Maybe appends an asterisk to the quote total price in order to point to it being subject to change.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string      $formatted_total    Formatted price.
	 * @param   \WC_Order   $order              The order object.
	 *
	 * @return  string
	 */
	public function maybe_append_quote_formatted_total_asterisk( string $formatted_total, \WC_Order $order ): string {
		if ( true === dws_qrwc_is_quote( $order ) && true === dws_qrwc_should_display_request_price_disclaimer( $order ) ) {
			$formatted_total .= ' <span class="dws-qrwc-price-disclaimer-asterisk">*</span>';
		}

		return $formatted_total;
	}

	/**
	 * Outputs a message about prices being subject to change.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   \WC_Order   $order  The quote object the message relates to.
	 */
	public function maybe_output_price_disclaimer( \WC_Order $order ) {
		if ( true === dws_qrwc_is_quote( $order ) ) {
			if ( \apply_filters( $this->get_hook_tag( 'should_output' ), true === dws_qrwc_should_display_request_price_disclaimer( $order ), $order ) ) {
				echo '<div class="clear"></div>';
				echo '* ' . \wp_kses_post( dws_qrwc_get_price_subject_to_change_request_message( 'quote' ) );
			}
		}
	}

	// endregion
}

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
 * Handles the output of a disclaimer about prices being subject to change before quote is finished on the cart and
 * checkout pages.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class PriceDisclaimerCartList extends AbstractPluginFunctionality {
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
			&& false === empty( dws_qrwc_get_price_subject_to_change_request_message( 'cart' ) );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( 'woocommerce_cart_item_price', $this, 'maybe_append_cart_item_price_asterisk', 99, 2 );
		$hooks_service->add_filter( 'woocommerce_cart_item_subtotal', $this, 'maybe_append_cart_item_price_asterisk', 99, 2 );

		$hooks_service->add_filter( 'woocommerce_cart_subtotal', $this, 'maybe_append_cart_totals_html_asterisk', 99 );
		$hooks_service->add_filter( 'woocommerce_cart_totals_fee_html', $this, 'maybe_append_cart_totals_html_asterisk', 99 );
		$hooks_service->add_filter( 'woocommerce_cart_totals_taxes_total_html', $this, 'maybe_append_cart_totals_html_asterisk', 99 );
		$hooks_service->add_filter( 'woocommerce_cart_totals_order_total_html', $this, 'maybe_append_cart_totals_html_asterisk', 99 );

		$hooks_service->add_action( 'woocommerce_after_cart_totals', $this, 'maybe_output_price_disclaimer' );
		$hooks_service->add_action( 'woocommerce_review_order_after_payment', $this, 'maybe_output_price_disclaimer' );
	}

	// endregion

	// region HOOKS

	/**
	 * Maybe appends an asterisk to cart prices in order to point to them being subject to change.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $price      Formatted price.
	 * @param   array   $cart_item  The cart item data.
	 *
	 * @return  string
	 */
	public function maybe_append_cart_item_price_asterisk( string $price, array $cart_item ): string {
		if ( Booleans::maybe_cast( $cart_item['dws_quote_request_item'] ?? false, false ) && ( \is_cart() || \is_checkout() ) ) {
			$price .= ' <span class="dws-qrwc-price-disclaimer-asterisk">*</span>';
		}

		return $price;
	}

	/**
	 * Maybe appends an asterisk to cart totals in order to point to them being subject to change.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $html   The current HTML to output.
	 *
	 * @return  string
	 */
	public function maybe_append_cart_totals_html_asterisk( string $html ): string {
		if ( true === dws_qrwc_wc_cart_has_quote_request_items() && ( \is_cart() || \is_checkout() ) ) {
			$html .= ' <span class="dws-qrwc-price-disclaimer-asterisk">*</span>';
		}

		return $html;
	}

	/**
	 * Outputs a message about prices being subject to change.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function maybe_output_price_disclaimer() {
		if ( \apply_filters( $this->get_hook_tag( 'should_output' ), true === dws_qrwc_wc_cart_has_quote_request_items() ) ) {
			echo '<div class="clear"></div>';
			echo '* ' . \wp_kses_post( dws_qrwc_get_price_subject_to_change_request_message( 'cart' ) );
		}
	}

	// endregion
}

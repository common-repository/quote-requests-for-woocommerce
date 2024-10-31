<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\RequestLists;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\States\Activeable\ActiveLocalTrait ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService ;
\defined( 'ABSPATH' ) || exit;
/**
 * Logical node for grouping all the functionalities related to a cart-based quote list.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class CartList extends AbstractPluginFunctionality
{
    // region TRAITS
    use  ActiveLocalTrait ;
    use  SetupHooksTrait ;
    // endregion
    // region INHERITED METHODS
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function is_active_local() : bool
    {
        $is_active = true;
        return $is_active;
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function get_di_container_children() : array
    {
        $children = array( CartList\AddToCartList::class, CartList\PriceDisclaimerCartList::class, CartList\CheckoutCartList::class );
        return $children;
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function register_hooks( HooksService $hooks_service ) : void
    {
        $hooks_service->add_filter(
            'the_title',
            $this,
            'maybe_filter_cart_page_title',
            99,
            2
        );
        $hooks_service->add_filter(
            'gettext_woocommerce',
            $this,
            'maybe_filter_translations',
            99,
            2
        );
        $hooks_service->add_filter(
            'woocommerce_coupons_enabled',
            $this,
            'maybe_disable_coupons',
            999
        );
        $hooks_service->add_filter(
            'woocommerce_cart_ready_to_calc_shipping',
            $this,
            'maybe_do_not_show_shipping',
            999
        );
        $hooks_service->add_filter(
            'pre_option_woocommerce_enable_shipping_calc',
            $this,
            'maybe_disable_shipping_calc',
            999
        );
        $hooks_service->add_action(
            'woocommerce_cart_collaterals',
            $this,
            'maybe_remove_cart_collaterals',
            9
        );
        // priority must be lower than 10
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
    public function maybe_filter_cart_page_title( string $title, int $post_id ) : string
    {
        if ( true === dws_qrwc_wc_cart_has_quote_request_items() && \wc_get_page_id( 'cart' ) === $post_id ) {
            $title = \__( 'Quote request list', 'quote-requests-for-woocommerce' );
        }
        return $title;
    }
    
    /**
     * Replace certain WC translations on the cart page when using the cart as a request list.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   string  $translation    Translated text.
     * @param   string  $text           Original text.
     *
     * @return  string
     */
    public function maybe_filter_translations( string $translation, string $text ) : string
    {
        if ( true === dws_qrwc_wc_cart_has_quote_request_items() ) {
            $translation = dws_qrwc_get_wc_cart_list_translation( $text ) ?? $translation;
        }
        return $translation;
    }
    
    /**
     * Disables coupons on the cart and checkout page if they are being used for a quote request.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   bool    $coupons_enabled    Whether coupons are enabled or not.
     *
     * @return  bool
     */
    public function maybe_disable_coupons( bool $coupons_enabled ) : bool
    {
        if ( true === dws_qrwc_wc_cart_has_quote_request_items() ) {
            $coupons_enabled = false;
        }
        return $coupons_enabled;
    }
    
    /**
     * Disables the display shipping options display for quote requests.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   bool    $show_shipping      Whether to show shipping information or not.
     *
     * @return  bool
     */
    public function maybe_do_not_show_shipping( bool $show_shipping ) : bool
    {
        if ( true === dws_qrwc_wc_cart_has_quote_request_items() ) {
            $show_shipping = false;
        }
        return $show_shipping;
    }
    
    /**
     * Disables the shipping calculator for quote requests.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   mixed   $option_value   The filtered option value. Default false.
     *
     * @return mixed|string
     */
    public function maybe_disable_shipping_calc( $option_value )
    {
        if ( true === dws_qrwc_wc_cart_has_quote_request_items() ) {
            $option_value = 'no';
        }
        return $option_value;
    }
    
    /**
     * Remove potentially problematic cart elements when quote items are present.
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function maybe_remove_cart_collaterals()
    {
        if ( true !== dws_qrwc_wc_cart_has_quote_request_items() ) {
            return;
        }
        \remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
        \remove_action( 'woocommerce_proceed_to_checkout', 'wc_get_pay_buttons' );
    }

}
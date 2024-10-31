<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\RequestLists\CartList;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Booleans ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Hooks ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService ;
\defined( 'ABSPATH' ) || exit;
/**
 * Handles the add-to-cart action.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class AddToCartList extends AbstractPluginFunctionality
{
    // region TRAITS
    use  SetupHooksTrait ;
    // endregion
    // region INHERITED METHODS
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function register_hooks( HooksService $hooks_service ) : void
    {
        $hooks_service->add_filter(
            'woocommerce_pre_remove_cart_item_from_session',
            $this,
            'validate_session_cart_item',
            999,
            3
        );
        $hooks_service->add_filter(
            'woocommerce_add_to_cart_validation',
            $this,
            'validate_add_to_cart_list_request',
            999,
            2
        );
        $hooks_service->add_filter(
            'woocommerce_add_to_cart_validation',
            $this,
            'validate_cart_contents',
            999,
            2
        );
        $hooks_service->add_filter(
            'woocommerce_add_cart_item_data',
            $this,
            'add_request_cart_item_data',
            999,
            3
        );
        $hooks_service->add_action(
            'woocommerce_check_cart_items',
            $this,
            'check_cart_items_validity',
            0
        );
        // priority must be lower than 1 since WC_Cart does its check on that
        $hooks_service->add_filter(
            'ngettext_woocommerce',
            $this,
            'maybe_filter_ntranslations',
            99,
            4
        );
    }
    
    // endregion
    // region HOOKS
    /**
     * Validates whether the quote items should be removed or not from the cart upon loading them from session.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   bool    $remove_from_cart   Whether the product should be removed from cart or not.
     * @param   string  $cart_key           The cart item key.
     * @param   array   $values             The cart item data.
     *
     * @return  bool
     */
    public function validate_session_cart_item( bool $remove_from_cart, string $cart_key, array $values ) : bool
    {
        if ( Booleans::maybe_cast( $values['dws_quote_request_item'] ?? false, false ) ) {
            
            if ( true !== dws_qrwc_can_add_product_to_request_list( $values['product_id'] ) ) {
                $remove_from_cart = true;
                dws_qrwc_wc_add_request_list_notice( 'removed-invalid-product-from-list', $values['product_id'], 'error' );
                \do_action( $this->get_hook_tag( 'remove_cart_item_from_session' ), $cart_key, $values );
            }
        
        }
        return $remove_from_cart;
    }
    
    /**
     * Before adding the product to cart, validate that requests are enabled and that the customer and product are valid.
     * This should ensure that no one exploits the endpoints to add ineligible products to their cart.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   bool    $passes_validation      Whether the product passes validation or not.
     * @param   int     $product_id             The ID of the product being checked.
     *
     * @return  bool
     */
    public function validate_add_to_cart_list_request( bool $passes_validation, int $product_id ) : bool
    {
        
        if ( $passes_validation ) {
            $list_product_id = dws_qrwc_get_add_to_request_list_product_id_from_input();
            $is_list_product = $product_id === $list_product_id;
            
            if ( $is_list_product && true !== dws_qrwc_can_add_product_to_request_list( $product_id ) ) {
                $passes_validation = false;
                dws_qrwc_wc_add_request_list_notice( 'removed-invalid-product-from-list', $product_id, 'error' );
            }
        
        }
        
        return $passes_validation;
    }
    
    /**
     * Before adding the product to cart, validate that the cart contains the same type of products -- request or non-request items.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   bool    $passes_validation      Whether the product passes validation or not.
     * @param   int     $product_id             The ID of the product being checked.
     *
     * @return  bool
     */
    public function validate_cart_contents( bool $passes_validation, int $product_id ) : bool
    {
        
        if ( $passes_validation ) {
            $list_product_id = dws_qrwc_get_add_to_request_list_product_id_from_input();
            $is_list_product = $product_id === $list_product_id;
            
            if ( \count( \WC()->cart->cart_contents ) > 0 ) {
                $cart_is_request_list = dws_qrwc_wc_cart_has_quote_request_items();
                
                if ( $is_list_product && !$cart_is_request_list ) {
                    $passes_validation = false;
                    dws_qrwc_wc_add_request_list_notice( 'cannot-add-product-to-shopping-cart', $product_id, 'notice' );
                } elseif ( !$is_list_product && $cart_is_request_list ) {
                    $passes_validation = false;
                    dws_qrwc_wc_add_request_list_notice( 'cannot-add-product-to-request-cart', $product_id, 'notice' );
                }
            
            }
        
        }
        
        return $passes_validation;
    }
    
    /**
     * Adds custom data to cart items to identify them as being a quote item.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   array   $cart_item_data     The data associated with the cart item.
     * @param   int     $product_id         The ID of the product being added to cart.
     * @param   int     $variation_id       The ID of the variation being added to cart.
     *
     * @return  array
     */
    public function add_request_cart_item_data( array $cart_item_data, int $product_id, int $variation_id ) : array
    {
        $list_product_id = dws_qrwc_get_add_to_request_list_product_id_from_input();
        if ( $list_product_id === $product_id ) {
            $cart_item_data['dws_quote_request_item'] = Booleans::to_string( true );
        }
        return $cart_item_data;
    }
    
    /**
     * Attempts to bypass the WC Cart items validity check for quote request items, if they are deemed valid.
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function check_cart_items_validity()
    {
        foreach ( \WC()->cart->get_cart() as $cart_item ) {
            if ( Booleans::maybe_cast( $cart_item['dws_quote_request_item'] ?? false, false ) ) {
            }
        }
    }
    
    /**
     * Replace certain WC translations related to adding to the cart when using the cart as a request list.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param   string  $translation    Translated text.
     * @param   string  $single         The text to be used if the number is singular.
     * @param   string  $plural         The text to be used if the number is plural.
     * @param   int     $number         The number to compare against to use either the singular or plural form.
     *
     * @return  string
     */
    public function maybe_filter_ntranslations(
        string $translation,
        string $single,
        string $plural,
        int $number
    ) : string
    {
        if ( isset( \WC()->cart ) && true === dws_qrwc_wc_cart_has_quote_request_items() ) {
            $translation = dws_qrwc_get_wc_cart_list_ntranslation( $single, $number ) ?? $translation;
        }
        return $translation;
    }

}
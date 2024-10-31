<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\RequestLists;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Assets ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\AssetsHelpersTrait ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Booleans ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService ;
\defined( 'ABSPATH' ) || exit;
/**
 * Handles the output of the add-to-request-list action.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class AddToListButton extends AbstractPluginFunctionality
{
    // region TRAITS
    use  AssetsHelpersTrait ;
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
        $hooks_service->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts' );
        $hooks_service->add_action(
            'woocommerce_simple_add_to_cart',
            $this,
            'maybe_force_output_simple_add_to_cart_form',
            30
        );
        // Same priority as WC core.
        $hooks_service->add_action( 'woocommerce_after_add_to_cart_button', $this, 'output_single_product_add_to_list_button' );
        $hooks_service->add_filter(
            'woocommerce_loop_add_to_cart_link',
            $this,
            'output_loop_add_to_list_link',
            10,
            3
        );
    }
    
    // endregion
    // region HOOKS
    /**
     * Enqueues scripts related to the 'add-to-list' action.
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function enqueue_scripts()
    {
        $plugin = $this->get_plugin();
        
        if ( \is_product() ) {
            $minified_path = Assets::maybe_get_minified_path( $plugin::get_plugin_assets_url() . 'dist/js/single-add-to-request-list.js', 'DWS_QRWC_SCRIPT_DEBUG' );
            \wp_enqueue_script(
                $this->get_asset_handle( 'single' ),
                $minified_path,
                array( 'jquery', 'wp-hooks' ),
                Assets::maybe_get_mtime_version( $minified_path, $plugin->get_plugin_version() ),
                true
            );
            $data = array(
                'i18n_make_a_selection_text' => \esc_attr__( 'Please select some product options before adding this product to your quote request list.', 'quote-requests-for-woocommerce' ),
                'i18n_unavailable_text'      => \esc_attr__( 'Sorry, this product is unavailable. Please choose a different combination.', 'quote-requests-for-woocommerce' ),
            );
            \wp_localize_script( $this->get_asset_handle( 'single' ), 'dws_qrwc_single_add_to_request_list_params', \apply_filters( $this->get_hook_tag( 'script_data', 'single' ), $data ) );
        } elseif ( dws_qrwc_fs()->can_use_premium_code__premium_only() ) {
            
            if ( \is_shop() || \is_product_taxonomy() ) {
                $minified_path = Assets::maybe_get_minified_path( $plugin::get_plugin_assets_url() . 'dist/js/premium/loop-add-to-request-list.js', 'DWS_QRWC_SCRIPT_DEBUG' );
                \wp_enqueue_script(
                    $this->get_asset_handle( 'loop' ),
                    $minified_path,
                    array( 'jquery' ),
                    Assets::maybe_get_mtime_version( $minified_path, $plugin->get_plugin_version() ),
                    true
                );
                \wp_localize_script( $this->get_asset_handle( 'loop' ), 'dws_qrwc_loop_add_to_request_list_params', \apply_filters( $this->get_hook_tag( 'script_data', 'loop' ), array(
                    'list_type'      => dws_qrwc_get_request_list_type(),
                    'i18n_view_cart' => dws_qrwc_get_wc_cart_list_translation( 'View cart' ),
                ) ) );
            }
        
        }
    
    }
    
    /**
     * Simple products need to be both purchasable and in-stock for the 'add-to-cart' form to be outputted. We piggy-back
     * said form for the 'add-to-list' functionality, so we need to force it to appear on all valid products for all valid
     * customers.
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function maybe_force_output_simple_add_to_cart_form()
    {
        global  $product ;
        
        if ( false === $product->is_in_stock() && true === dws_qrwc_can_add_product_to_request_list( $product->get_id() ) ) {
            \add_filter( 'woocommerce_product_is_in_stock', '__return_true', 999 );
            \add_filter( 'woocommerce_get_stock_html', '__return_empty_string', 999 );
            \woocommerce_simple_add_to_cart();
            \remove_filter( 'woocommerce_product_is_in_stock', '__return_true', 999 );
            \remove_filter( 'woocommerce_get_stock_html', '__return_empty_string', 999 );
        }
    
    }
    
    /**
     * Outputs the 'add-to-list' action button on supported products for valid customers.
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function output_single_product_add_to_list_button()
    {
        global  $product ;
        if ( true !== dws_qrwc_can_add_product_to_request_list( $product->get_id() ) ) {
            return;
        }
        \do_action( $this->get_hook_tag( 'before_single_product_output' ), $product );
        dws_qrwc_wc_get_template( "single-product/add-to-list/{$product->get_type()}.php", array(
            'product'     => $product,
            'button_text' => dws_qrwc_get_add_to_request_list_text( $product->get_id() ),
        ) );
        \do_action( $this->get_hook_tag( 'after_single_product_output' ), $product );
    }
    
    /**
     * Outputs the 'add-to-list' action button in loops. Only simple products are supported by default.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   string          $add_to_cart_link   The add-to-cart loop button.
     * @param   \WC_Product     $product            The product the button is for.
     * @param   array           $args               Additional arguments passed to the template.
     *
     * @return  string
     */
    public function output_loop_add_to_list_link( string $add_to_cart_link, \WC_Product $product, array $args ) : string
    {
        $product_types = \apply_filters( $this->get_hook_tag( 'supported_loop_product_types' ), 'simple' );
        
        if ( $product->is_type( $product_types ) && true === dws_qrwc_can_add_product_to_request_list( $product->get_id() ) ) {
            $args = \array_merge( $args, array(
                'add_to_list_url' => \add_query_arg( array(
                'add-to-cart'      => $product->get_id(),
                'add-to-qrwc-list' => $product->get_id(),
            ), $product->add_to_cart_url() ),
                'button_text'     => dws_qrwc_get_add_to_request_list_text( $product->get_id() ),
            ) );
            $args['class'] .= ' add_to_cart_button add_to_quote_request_list_button';
            
            if ( $product->supports( 'ajax_add_to_cart' ) ) {
                $args['attributes']['data-qrwc_product_id'] = $product->get_id();
                if ( !$product->is_in_stock() ) {
                    $args['class'] .= ' ajax_add_to_cart';
                }
            }
            
            $add_to_cart_link .= \apply_filters(
                $this->get_hook_tag( 'loop_add_to_list_link' ),
                dws_qrwc_wc_get_template_html( 'loop/add-to-list.php', \apply_filters( $this->get_hook_tag( 'loop_add_to_list_args' ), $args, $product ) ),
                $product,
                $args
            );
        }
        
        return $add_to_cart_link;
    }

}
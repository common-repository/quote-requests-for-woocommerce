<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Assets ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\AssetsHelpersTrait ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Caching\Actions\InitializeCachingServiceTrait ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Caching\CachingServiceAwareInterface ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\Functionalities\WC_AbstractValidatedProductSettingsTabFunctionality ;
\defined( 'ABSPATH' ) || exit;
/**
 * Handles the output of a product-level settings panel.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class ProductSettings extends WC_AbstractValidatedProductSettingsTabFunctionality implements  CachingServiceAwareInterface 
{
    // region TRAITS
    use  AssetsHelpersTrait ;
    use  InitializeCachingServiceTrait ;
    // endregion
    // region INHERITED METHODS
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function get_di_container_children() : array
    {
        $children = array( Settings\Products\GeneralProductSettings::class );
        $children[] = Settings\Products\UserInterfaceProductSettings::class;
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
        parent::register_hooks( $hooks_service );
        $hooks_service->add_action( 'admin_print_scripts-post.php', $this, 'enqueue_scripts' );
        $hooks_service->add_action( 'admin_print_scripts-post-new.php', $this, 'enqueue_scripts' );
        $hooks_service->add_action(
            'woocommerce_process_product_meta',
            $this,
            'maybe_clear_cache',
            99
        );
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function is_supported_product( int $product_id ) : ?bool
    {
        if ( true !== dws_qrwc_are_requests_enabled() ) {
            return null;
        }
        return dws_qrwc_is_supported_request_product( $product_id );
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function get_meta_key_prefix() : string
    {
        return '_dws-qrwc_';
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function get_tab_slug() : string
    {
        return 'dws_quote_requests';
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function get_tab_title() : string
    {
        return \__( 'Quote Requests', 'quote-requests-for-woocommerce' );
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function get_tab_classes() : array
    {
        return \array_map( function ( string $type ) {
            return "show_if_{$type}";
        }, dws_qrwc_get_supported_request_product_types() );
    }
    
    // endregion
    // region HOOKS
    /**
     * Enqueues the conditional logic script.
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function enqueue_scripts()
    {
        if ( false === \in_array( \get_current_screen()->id, array( 'product', 'edit-product' ), true ) ) {
            return;
        }
        $plugin = $this->get_plugin();
        $minified_path = Assets::maybe_get_minified_path( $plugin::get_plugin_assets_url() . 'dist/js/admin/requests-product-settings.js', 'DWS_QRWC_SCRIPT_DEBUG' );
        \wp_enqueue_script(
            $this->get_asset_handle(),
            $minified_path,
            array( 'jquery', 'wp-util' ),
            Assets::maybe_get_mtime_version( $minified_path, $plugin->get_plugin_version() ),
            true
        );
    }
    
    /**
     * Clear the cache whenever a product is saved if requests are enabled.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   int     $post_id    The ID of the product being currently saved.
     */
    public function maybe_clear_cache( int $post_id )
    {
        
        if ( true === $this->is_supported_product( $post_id ) ) {
            $this->get_caching_service()->delete_all_values( 'object' );
            $this->get_caching_service()->delete_all_values( 'transient' );
        }
    
    }

}
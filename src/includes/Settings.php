<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Caching\Actions\InitializeCachingServiceTrait ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Caching\CachingServiceAwareInterface ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\Functionalities\WC_AbstractValidatedOptionsTabFunctionality ;
\defined( 'ABSPATH' ) || exit;
/**
 * Registers the plugin's settings with WC.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class Settings extends WC_AbstractValidatedOptionsTabFunctionality implements  CachingServiceAwareInterface 
{
    // region TRAITS
    use  InitializeCachingServiceTrait ;
    use  SetupHooksTrait ;
    // endregion
    // region INHERITED METHODS
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    protected function get_di_container_children() : array
    {
        $children = array( Settings\GeneralSettingsSection::class, Settings\RequestsSettingsSection::class );
        if ( !empty(dws_qrwc_component( Settings\IntegrationsSettingsSection::class )->get_di_container_children()) ) {
            $children[] = Settings\IntegrationsSettingsSection::class;
        }
        return $children;
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function get_options_name_prefix() : string
    {
        return 'dws-qrwc_';
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function get_page_slug() : string
    {
        return 'dws-quotes';
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function get_page_title() : string
    {
        return \_x( 'Quotes', 'settings', 'quote-requests-for-woocommerce' );
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function register_hooks( HooksService $hooks_service ) : void
    {
        $hooks_service->add_action( 'woocommerce_settings_saved', $this, 'maybe_clear_cache' );
    }
    
    // endregion
    // region HOOKS
    /**
     * Clear the cache whenever this plugin's settings are saved.
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function maybe_clear_cache()
    {
        global  $current_tab ;
        
        if ( $this->get_page_slug() === $current_tab ) {
            $this->get_caching_service()->delete_all_values( 'object' );
            $this->get_caching_service()->delete_all_values( 'transient' );
        }
    
    }

}
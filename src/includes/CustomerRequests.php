<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\States\Activeable\ActiveLocalTrait ;
\defined( 'ABSPATH' ) || exit;
/**
 * Logical node for grouping all functionalities that enable customers to submit quote requests.
 *
 * By grouping them here, all of these functionalities are automatically disabled whenever requests are disabled
 * in the plugin's settings.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class CustomerRequests extends AbstractPluginFunctionality
{
    // region TRAITS
    use  ActiveLocalTrait ;
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
        return dws_qrwc_are_requests_enabled();
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    protected function get_di_container_children() : array
    {
        $children = array( Requests\PriceDisclaimer::class, RequestLists\AddToListButton::class, RequestLists\CartList::class );
        return $children;
    }

}
<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality ;
\defined( 'ABSPATH' ) || exit;
/**
 * Logical node for all integration functionalities.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
final class Integrations extends AbstractPluginFunctionality
{
    // region INHERITED METHODS
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    protected function get_di_container_children() : array
    {
        $children = array( Integrations\Plugins\LinkedOrdersForWC::class );
        return $children;
    }

}
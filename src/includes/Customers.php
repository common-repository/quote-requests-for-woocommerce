<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality ;
\defined( 'ABSPATH' ) || exit;
/**
 * Logical node for all customer-facing functionalities.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
final class Customers extends AbstractPluginFunctionality
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
        $children = array( Account\Actions::class, Account\Endpoints::class, CustomerRequests::class );
        return $children;
    }

}
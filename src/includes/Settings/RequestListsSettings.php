<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Settings;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Arrays ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationTypesEnum ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\Functionalities\WC_AbstractValidatedOptionsGroupFunctionality ;
\defined( 'ABSPATH' ) || exit;
/**
 * Handles the registration of the customer requests settings.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class RequestListsSettings extends WC_AbstractValidatedOptionsGroupFunctionality
{
    // region INHERITED METHODS
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function get_group_title() : string
    {
        return \__( 'Request Lists', 'quote-requests-for-woocommerce' );
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    protected function get_group_fields_helper() : array
    {
        
        if ( true !== dws_qrwc_are_requests_enabled() ) {
            return array();
            // Disables the group output completely.
        }
        
        $fields = array(
            'add-to-list-text' => array(
            'title'   => \__( 'Text displayed inside the add-to-list button', 'quote-requests-for-woocommerce' ),
            'type'    => 'text',
            'default' => $this->get_default_value( 'add-to-list-text' ),
        ),
        );
        return $fields;
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function handle_conditional_logic( array $options ) : array
    {
        if ( true === $this->get_validated_option_value( 'use-cart' ) ) {
            unset( $options['list-page'] );
        }
        return $options;
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function validate_option_value_helper( $value, string $field_id )
    {
        switch ( $field_id ) {
            case 'add-to-list-text':
                $value = ( $value ?: $this->get_default_value( $field_id ) );
                $value = $this->validate_value( $value, $field_id, ValidationTypesEnum::STRING );
                break;
        }
        return $value;
    }

}
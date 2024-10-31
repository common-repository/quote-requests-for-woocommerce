<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Settings;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationTypesEnum ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\Functionalities\WC_AbstractValidatedOptionsGroupFunctionality ;
\defined( 'ABSPATH' ) || exit;
/**
 * Handles the registration of the customer requests messages settings.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class RequestMessagesSettings extends WC_AbstractValidatedOptionsGroupFunctionality
{
    // region INHERITED METHODS
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function register_hooks( HooksService $hooks_service ) : void
    {
        parent::register_hooks( $hooks_service );
        $hooks_service->add_filter(
            'woocommerce_admin_settings_sanitize_option',
            $this,
            'sanitize_values',
            10,
            3
        );
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    public function get_group_title() : string
    {
        return \__( 'Request Messages', 'quote-requests-for-woocommerce' );
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
            'price-subject-to-change' => array(
            'title'    => \__( 'Price subject to change disclaimer', 'quote-requests-for-woocommerce' ),
            'type'     => 'text',
            'default'  => $this->get_default_value( 'price-subject-to-change' ),
            'desc_tip' => \__( 'Outputted on the request list, account area, tracking page, and emails.', 'quote-requests-for-woocommerce' ) . '&nbsp;' . \__( 'Leave empty to disable.', 'quote-requests-for-woocommerce' ),
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
    protected function validate_option_value_helper( $value, string $field_id )
    {
        switch ( $field_id ) {
            case 'price-subject-to-change':
                $value = $this->validate_value( $value, $field_id, ValidationTypesEnum::STRING );
                break;
        }
        return $value;
    }
    
    // endregion
    // region HOOKS
    /**
     * Allows basic HTML inside the fields defined by this class.
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @param   mixed   $value          The sanitized value.
     * @param   array   $option         The field definition.
     * @param   mixed   $raw_value      The raw value.
     *
     * @return  mixed
     */
    public function sanitize_values( $value, array $option, $raw_value )
    {
        
        if ( 'text' === $option['type'] && \in_array( Strings::maybe_unprefix( $option['id'], 'dws-qrwc_request-messages_' ), \array_keys( $this->get_group_fields() ), true ) ) {
            include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
            $value = \wc_kses_notice( \trim( $raw_value ) );
        }
        
        return $value;
    }

}
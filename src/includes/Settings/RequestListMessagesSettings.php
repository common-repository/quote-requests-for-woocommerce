<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Settings;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Arrays ;
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
class RequestListMessagesSettings extends WC_AbstractValidatedOptionsGroupFunctionality
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
        return \__( 'Request List Messages', 'quote-requests-for-woocommerce' );
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
            'cannot-add-product-to-shopping-cart' => array(
            'title'    => \__( 'Cannot add to cart because cart is used for shopping', 'quote-requests-for-woocommerce' ),
            'type'     => 'text',
            'default'  => $this->get_default_value( 'cannot-add-product-to-shopping-cart' ),
            'desc_tip' => \__( 'Shown if the customer tries to add a product to their quote request list cart while the cart contains non-request items.', 'quote-requests-for-woocommerce' ),
        ),
            'cannot-add-product-to-request-cart'  => array(
            'title'    => \__( 'Cannot add to cart because cart is used as request list', 'quote-requests-for-woocommerce' ),
            'type'     => 'text',
            'default'  => $this->get_default_value( 'cannot-add-product-to-request-cart' ),
            'desc_tip' => \__( 'Shown if the customer tries to add a product to their cart while the cart is being used as a quote request list.', 'quote-requests-for-woocommerce' ),
        ),
            'removed-invalid-product-from-list'   => array(
            'title'    => \__( 'Removed from request list if ineligible', 'quote-requests-for-woocommerce' ),
            'type'     => 'text',
            'default'  => $this->get_default_value( 'removed-invalid-product-from-list' ),
            'desc_tip' => \__( 'Shown if a product added to the request list is no longer available or if the customer is no longer allowed to submit requests for it.', 'quote-requests-for-woocommerce' ),
        ),
        );
        \array_walk( $fields, function ( array &$field, string $message_id ) {
            $field['desc'] = \wp_sprintf(
                /* translators: %l: list of available placeholders. */
                \__( 'Available placeholders: %l.', 'quote-requests-for-woocommerce' ),
                dws_qrwc_get_request_list_messages_placeholders( null, $message_id )
            );
        } );
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
        return $options;
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    protected function validate_option_value_helper( $value, string $field_id )
    {
        $value = $this->validate_value( $value, $field_id, ValidationTypesEnum::STRING );
        return ( $value ?: $this->get_default_value( $field_id ) );
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
        
        if ( 'text' === $option['type'] && \in_array( Strings::maybe_unprefix( $option['id'], 'dws-qrwc_request-list-messages_' ), \array_keys( $this->get_group_fields() ), true ) ) {
            include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
            $value = \wc_kses_notice( \trim( $raw_value ) );
        }
        
        return $value;
    }

}
<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Settings;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Arrays ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings ;
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
class RequestsSettings extends WC_AbstractValidatedOptionsGroupFunctionality
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
        return \__( 'Customer Requests', 'quote-requests-for-woocommerce' );
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function get_group_fields_helper() : array
    {
        $fields = array(
            'enabled'                   => array(
            'title'    => \__( 'Enable quote requests from customers?', 'quote-requests-for-woocommerce' ),
            'type'     => 'select',
            'default'  => $this->get_default_value( 'enabled' ),
            'options'  => $this->get_supported_options_trait( 'boolean' ),
            'desc_tip' => \__( 'If disabled it will only be possible to create quote requests manually via the admin panel. Enabling this option will let you configure products that customers of your site can create quote requests for themselves.', 'quote-requests-for-woocommerce' ),
        ),
            'valid-customers'           => array(
            'title'    => \__( 'Allow quote requests to be made by', 'quote-requests-for-woocommerce' ),
            'type'     => 'select',
            'default'  => $this->get_default_value( 'valid-customers' ),
            'options'  => $this->get_supported_options( 'valid-customers' ),
            'desc_tip' => \__( 'Choose whether to allow quote requests for all customers or whether to restrict this functionality only to select ones.', 'quote-requests-for-woocommerce' ),
        ),
            'valid-products'            => array(
            'name'     => \__( 'Allow quote requests for', 'quote-requests-for-woocommerce' ),
            'type'     => 'select',
            'default'  => $this->get_default_value( 'valid-products' ),
            'options'  => $this->get_supported_options( 'valid-products' ),
            'desc_tip' => \__( 'Choose whether to allow all products to be added to quote requests or to restrict this functionality only to select ones.', 'quote-requests-for-woocommerce' ),
        ),
            'valid-products-categories' => array(
            'name'     => \__( 'Allowed products categories', 'quote-requests-for-woocommerce' ),
            'type'     => 'multiselect',
            'class'    => 'wc-enhanced-select',
            'default'  => $this->get_default_value( 'valid-products-categories' ),
            'options'  => $this->get_supported_options( 'valid-products-categories' ),
            'desc_tip' => \__( 'Choose the product categories that make a support product eligible for quote requests.', 'quote-requests-for-woocommerce' ),
        ),
            'valid-products-tags'       => array(
            'name'     => \__( 'Allowed products tags', 'quote-requests-for-woocommerce' ),
            'type'     => 'multiselect',
            'class'    => 'wc-enhanced-select',
            'default'  => $this->get_default_value( 'valid-products-tags' ),
            'options'  => $this->get_supported_options( 'valid-products-tags' ),
            'desc_tip' => \__( 'Choose the product tags that make a support product eligible for quote requests.', 'quote-requests-for-woocommerce' ),
        ),
            'disable-shipping-fields'   => array(
            'title'    => \__( 'Disable shipping fields?', 'quote-requests-for-woocommerce' ),
            'type'     => 'select',
            'default'  => $this->get_default_value( 'disable-shipping-fields' ),
            'options'  => $this->get_supported_options_trait( 'boolean' ),
            'desc_tip' => \__( 'If enabled, customers will not be able to provide a shipping address for their requests.', 'quote-requests-for-woocommerce' ),
        ),
        );
        return $fields;
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function handle_conditional_logic( array $options ) : array
    {
        
        if ( false === $this->get_validated_option_value( 'enabled' ) ) {
            $options = array(
                'enabled' => $options['enabled'],
            );
        } else {
            $valid_products = $this->get_validated_option_value( 'valid-products' );
            if ( 'categories' !== $valid_products ) {
                unset( $options['valid-products-categories'] );
            }
            if ( 'tags' !== $valid_products ) {
                unset( $options['valid-products-tags'] );
            }
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
            case 'enabled':
            case 'disable-shipping-fields':
                $value = $this->validate_value( $value, $field_id, ValidationTypesEnum::BOOLEAN );
                break;
            case 'valid-customers':
            case 'valid-products':
                $value = $this->validate_allowed_value(
                    $value,
                    $field_id,
                    $field_id,
                    ValidationTypesEnum::STRING
                );
                break;
            case 'valid-products-categories':
            case 'valid-products-tags':
                $value = \array_map( function ( string $tax_id ) {
                    return Integers::maybe_cast( $tax_id );
                }, Arrays::validate( $value, array() ) );
                $value = $this->validate_allowed_value(
                    $value,
                    $field_id,
                    $field_id,
                    ValidationTypesEnum::ARRAY
                );
                break;
        }
        return $value;
    }

}
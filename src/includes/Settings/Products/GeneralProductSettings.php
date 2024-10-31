<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Settings\Products;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationTypesEnum ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\Functionalities\WC_AbstractValidatedProductSettingsGroupFunctionality ;
\defined( 'ABSPATH' ) || exit;
/**
 * Registers the general settings with supported WC products.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class GeneralProductSettings extends WC_AbstractValidatedProductSettingsGroupFunctionality
{
    // region INHERITED METHODS
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    protected function validate_field_value_helper( $value, string $field_id, ?int $product_id = null )
    {
        switch ( $field_id ) {
            case 'is-valid-product':
            case 'valid-customers':
                $value = $this->validate_allowed_value(
                    $value,
                    $field_id,
                    $field_id,
                    ValidationTypesEnum::STRING
                );
                break;
        }
        return $value;
    }
    
    /**
     * {@inheritDoc}
     *
     * @since   1.0.0
     * @version 1.0.0
     */
    protected function get_group_fields_helper() : array
    {
        $fields = array(
            'is-valid-product' => array(
            'label'       => \__( 'Is allowed product?', 'quote-requests-for-woocommerce' ),
            'type'        => 'select',
            'options'     => $this->get_supported_options( 'is-valid-product' ),
            'desc_tip'    => true,
            'description' => \__( 'Choose whether this product can be added by customers to quote requests or not.', 'quote-requests-for-woocommerce' ),
        ),
            'valid-customers'  => array(
            'label'       => \__( 'Allowed customers', 'quote-requests-for-woocommerce' ),
            'type'        => 'select',
            'options'     => $this->get_supported_options( 'valid-customers' ),
            'desc_tip'    => true,
            'description' => \__( 'Choose which customers can create quote requests containing this product.', 'quote-requests-for-woocommerce' ),
        ),
        );
        return $fields;
    }

}
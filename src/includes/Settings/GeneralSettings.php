<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Settings;

use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationTypesEnum ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\Functionalities\WC_AbstractValidatedOptionsGroupFunctionality ;
\defined( 'ABSPATH' ) || exit;
/**
 * Registers the general settings with WC.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class GeneralSettings extends WC_AbstractValidatedOptionsGroupFunctionality
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
        return \_x( 'General', 'settings section title', 'quote-requests-for-woocommerce' );
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
            'tracking-page' => array(
            'title'    => \__( 'Quote tracking page', 'quote-requests-for-woocommerce' ),
            'type'     => 'single_select_page',
            'default'  => '',
            'desc_tip' => \sprintf(
            /* translators: %s: shortcode tag */
            \__( 'Page contents: [%s]', 'quote-requests-for-woocommerce' ),
            \DWS_Quote_Tracking_SC::SHORTCODE
        ),
            'class'    => 'wc-enhanced-select-nostd',
            'args'     => array(
            'exclude' => array(
            \wc_get_page_id( 'shop' ),
            \wc_get_page_id( 'cart' ),
            \wc_get_page_id( 'checkout' ),
            \wc_get_page_id( 'myaccount' )
        ),
        ),
            'autoload' => false,
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
            case 'tracking-page':
                $value = Integers::maybe_cast( $value );
                $value = ( !\is_null( $value ) ? \get_post( $value ) : $value );
                break;
        }
        return $value;
    }

}
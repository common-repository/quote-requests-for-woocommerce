<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Settings\Integrations;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationTypesEnum;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\Functionalities\WC_AbstractValidatedOptionsGroupFunctionality;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the LOWC settings group.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class LinkedOrdersForWCSettings extends WC_AbstractValidatedOptionsGroupFunctionality {
	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_group_title(): string {
		return dws_qrwc_component( 'linked-orders-for-wc-plugin-integration' )->get_dependent_plugin()['fallback_name'];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function get_group_fields_helper(): array {
		return array(
			'allow-quotes-linking'           => array(
				'title'    => \__( 'Enable the linking of quotes?', 'quote-requests-for-woocommerce' ),
				'type'     => 'select',
				'default'  => $this->get_default_value( 'allow-quotes-linking' ),
				'options'  => $this->get_supported_options_trait( 'boolean' ),
				'desc_tip' => \__( 'If enabled, you\'ll be able to create linked quotes the same way that you can create linked orders.', 'quote-requests-for-woocommerce' ),
			),
			'allow-quotes-as-order-children' => array(
				'title'    => \__( 'Enable linking quotes to orders?', 'quote-requests-for-woocommerce' ),
				'type'     => 'select',
				'default'  => $this->get_default_value( 'allow-quotes-as-order-children' ),
				'options'  => $this->get_supported_options_trait( 'boolean' ),
				'desc_tip' => \__( 'If enabled, you\'ll be able to create quotes as order children. The order generated by accepting such a quote will be linked back as a child to the quote\'s parent order.', 'quote-requests-for-woocommerce' ),
			),
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function validate_option_value_helper( $value, string $field_id ) {
		switch ( $field_id ) {
			case 'allow-quotes-linking':
			case 'allow-quotes-as-order-children':
				$value = $this->validate_value( $value, $field_id, ValidationTypesEnum::BOOLEAN );
				break;
		}

		return $value;
	}

	// endregion
}
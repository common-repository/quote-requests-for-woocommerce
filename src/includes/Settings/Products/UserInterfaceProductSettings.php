<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Settings\Products;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\Functionalities\WC_AbstractValidatedProductSettingsGroupFunctionality;

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the UI settings with supported WC products.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class UserInterfaceProductSettings extends WC_AbstractValidatedProductSettingsGroupFunctionality {
	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function save_group_fields( \WC_Product $product ) {
		parent::save_group_fields( $product );

		$add_text_meta_key = $this->generate_meta_key( 'add-to-list-text' );
		if ( 'global' === $product->get_meta( $add_text_meta_key ) ) {
			$product->update_meta_data( $add_text_meta_key, '' );
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function validate_field_value_helper( $value, string $field_id, ?int $product_id = null ) {
		switch ( $field_id ) {
			case 'add-to-list-text':
				$value = empty( $value ) ? 'global' : $value;
				$value = Strings::validate( $value, 'global' );
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
	protected function get_group_fields_helper(): array {
		return array(
			'add-to-list-text' => array(
				'label'       => \__( 'Add to list text', 'quote-requests-for-woocommerce' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => \__( 'Leave empty to use the global default.', 'quote-requests-for-woocommerce' ),
			),
		);
	}

	// endregion
}

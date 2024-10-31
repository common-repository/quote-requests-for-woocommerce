<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Settings;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\Functionalities\WC_AbstractValidatedOptionsSectionFunctionality;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles the registration of the integrations settings section.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class IntegrationsSettingsSection extends WC_AbstractValidatedOptionsSectionFunctionality {
	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function get_di_container_children(): array {
		$children = array();

		if ( false === dws_qrwc_component( 'linked-orders-for-wc-plugin-integration' )->is_disabled() ) {
			$children[] = Integrations\LinkedOrdersForWCSettings::class;
		}

		return $children;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_options_name_prefix(): string {
		return $this->get_parent()->get_options_name_prefix();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_page_slug(): string {
		return 'integrations';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_page_title(): string {
		return \_x( 'Integrations', 'settings section title', 'quote-requests-for-woocommerce' );
	}

	// endregion
}

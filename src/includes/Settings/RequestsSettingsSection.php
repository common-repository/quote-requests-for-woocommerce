<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Settings;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\WooCommerce\Settings\Functionalities\WC_AbstractValidatedOptionsSectionFunctionality;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles the registration of the requests settings section.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class RequestsSettingsSection extends WC_AbstractValidatedOptionsSectionFunctionality {
	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function get_di_container_children(): array {
		return array(
			RequestsSettings::class,
			RequestMessagesSettings::class,
			RequestListsSettings::class,
			RequestListMessagesSettings::class,
		);
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
		return 'requests';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_page_title(): string {
		return \_x( 'Customer Requests', 'settings section title', 'quote-requests-for-woocommerce' );
	}

	// endregion
}

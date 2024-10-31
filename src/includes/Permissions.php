<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Collection of permissions used by the plugin.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\QuoteRequests
 */
class Permissions extends AbstractPluginFunctionality {
	// region TRAITS

	use SetupHooksTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function get_di_container_children(): array {
		return array();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( 'user_has_cap', $this, 'user_has_cap', 99, 3 );
	}

	// endregion

	// region HOOKS

	/**
	 * Dynamically grants permissions to users.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool[]      $all_caps   Boolean array defining whether the user has the capability or not.
	 * @param   string[]    $caps       Required primitive capabilities for the requested capability.
	 * @param   array       $args       Arguments that accompany the requested capability check.
	 *
	 * @return  array
	 */
	public function user_has_cap( array $all_caps, array $caps, array $args ): array {
		if ( isset( $caps[0] ) ) {
			switch ( $caps[0] ) {
				case 'view_dws_quote':
				case 'accept_dws_quote':
				case 'reject_dws_quote':
				case 'cancel_dws_quote':
					$user_id = \intval( $args[1] );
					$quote   = dws_qrwc_get_quote( $args[2] );

					if ( $quote && $user_id === $quote->get_customer_id() ) {
						$all_caps[ $caps[0] ] = true;
					}

					break;
			}
		}

		return $all_caps;
	}

	// endregion
}

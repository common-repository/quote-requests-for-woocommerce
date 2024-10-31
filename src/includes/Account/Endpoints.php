<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Account;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Arrays;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles the registration of the new my-account endpoints.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class Endpoints extends AbstractPluginFunctionality {
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
		return array( Endpoints\QuotesList::class, Endpoints\ViewQuote::class );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( 'woocommerce_get_settings_advanced', $this, 'insert_advanced_settings', 10, 2 );
		$hooks_service->add_filter( 'woocommerce_get_query_vars', $this, 'register_endpoints' );
	}

	// endregion

	// region HOOKS

	/**
	 * Inserts options for manipulating the endpoints URLs into WC's advanced settings.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $advanced_settings  Configuration of WC's advanced settings.
	 * @param   string  $section            Slug of the advanced settings section being rendered.
	 *
	 * @return  array
	 */
	public function insert_advanced_settings( array $advanced_settings, string $section ): array {
		if ( '' === $section ) {
			$quotes_endpoints = \array_map(
				function ( AbstractEndpoint $child ) {
					return array(
						'title'    => $child->get_endpoint_title(),
						'desc'     => $child->get_endpoint_description(),
						'id'       => 'woocommerce_myaccount_' . $child::ENDPOINT . '_endpoint',
						'type'     => 'text',
						'default'  => $child::ENDPOINT,
						'desc_tip' => true,
					);
				},
				$this->get_children()
			);

			$values = Arrays::search_values(
				$advanced_settings,
				'woocommerce_myaccount_view_order_endpoint',
				function( array $entry ) {
					return $entry['id'] ?? '';
				}
			);
			if ( \count( $values ) > 0 ) {
				$advanced_settings = Arrays::insert_after( $advanced_settings, \array_keys( $values )[0], $quotes_endpoints );
			}
		}

		return $advanced_settings;
	}

	/**
	 * Registers new endpoint query vars with WP.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $query_vars     List of query vars.
	 *
	 * @return  array
	 */
	public function register_endpoints( array $query_vars ): array {
		foreach ( $this->get_children() as $child ) {
			$query_vars[ $child::ENDPOINT ] = \get_option( 'woocommerce_myaccount_' . $child::ENDPOINT . '_endpoint', $child::ENDPOINT );
		}

		return $query_vars;
	}

	// endregion
}

<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Account;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Encapsulates the most needed functionality of a my-account endpoint.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
abstract class AbstractEndpoint extends AbstractPluginFunctionality {
	// region TRAITS

	use SetupHooksTrait;

	// endregion

	// region FIELDS AND CONSTANTS

	/**
	 * Internal slug of the my-account endpoint.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string
	 */
	public const ENDPOINT = '';

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		if ( empty( static::ENDPOINT ) || \is_admin() ) {
			return;
		}

		$hooks_service->add_filter( 'the_title', $this, 'filter_endpoint_title', 11 );
		$hooks_service->add_filter( 'woocommerce_endpoint_' . static::ENDPOINT . '_title', $this, 'filter_endpoint_title', 10, 2 );
		$hooks_service->add_action( 'woocommerce_account_' . static::ENDPOINT . '_endpoint', $this, 'output_endpoint_content' );
	}

	// endregion

	// region METHODS

	/**
	 * Returns whether the current query is for the current endpoint.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function is_query(): bool {
		return apply_filters(
			$this->get_hook_tag( 'is_query' ),
			\is_main_query() && \is_page() && isset( $GLOBALS['wp']->query_vars[ static::ENDPOINT ] )
		);
	}

	/**
	 * Returns the title of the endpoint.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	abstract public function get_endpoint_title(): string;

	/**
	 * Returns the description of the endpoint.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	abstract public function get_endpoint_description(): string;

	/**
	 * Outputs the content of the current endpoint.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	abstract public function output_endpoint_content(): void;

	// endregion

	// region HOOKS

	/**
	 * Changes the page title on registered endpoints.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string          $title      Currently set title.
	 * @param   string|null     $endpoint   My account endpoint, if set.
	 *
	 * @return  string
	 */
	public function filter_endpoint_title( string $title, ?string $endpoint = null ): string {
		if ( ! \is_null( $endpoint ) ) {
			if ( static::ENDPOINT === $endpoint ) {
				$title = $this->get_endpoint_title();
			}
		} elseif ( \in_the_loop() && \is_account_page() && $this->is_query() ) {
			$title = $this->get_endpoint_title();

			// unhook after returning our title to prevent the filter from overriding other stuff
			\remove_filter( 'the_title', array( $this, __FUNCTION__ ), 11 );
		}

		return $title;
	}

	// endregion
}
